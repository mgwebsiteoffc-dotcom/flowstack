<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Jobs\BikriBook\SyncAllTenantsInvoices;
use App\Jobs\BikriBook\SyncInvoiceStatusFromBikriBook;
use App\Jobs\BikriBook\SyncInvoiceToBikriBook;
use App\Models\ActivityLog;
use App\Models\BikriBookSyncLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Setting;
use App\Models\Tenant;
use App\Services\BikriBookService;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Invoice::with('client', 'items');

        if ($user->isAccountManager()) {
            $query->whereHas('client', fn ($q) => $q->where('account_manager_id', $user->id));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($clientId = $request->input('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($bb = $request->input('bikribook_sync_status')) {
            $query->where('bikribook_sync_status', $bb);
        }

        if ($search = $request->input('search')) {
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        $invoices = $query->latest()->paginate(config('tenancy.pagination_size'))->withQueryString();
        $clients = $this->visibleClients();

        return view('finance.invoices.index', compact('invoices', 'clients'));
    }

    public function create()
    {
        $clients = $this->visibleClients();
        $defaults = $this->defaults();

        return view('finance.invoices.create', compact('clients', 'defaults'));
    }

    /**
     * Save the invoice locally FIRST (draft). BikriBook sync only happens
     * when the user explicitly clicks "Sync to BikriBook" - a BikriBook
     * failure can never block invoice creation (FALLBACK RULE).
     */
    public function store(InvoiceRequest $request)
    {
        $validated = $request->validated();

        $tenant = app('currentTenant');
        $invoiceNumber = $this->nextInvoiceNumber();

        $items = $this->processItems($request->input('items', []));

        $subtotal = round(array_sum(array_column($items, 'line_total')), 2);

        [$discountAmount, $taxAmount, $total] = $this->calculateTotals($subtotal, (float) $validated['tax_rate'], $request);

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'client_id' => $validated['client_id'],
            'invoice_number' => $invoiceNumber,
            'status' => 'draft',
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'tax_rate' => $validated['tax_rate'],
            'tax_amount' => $taxAmount,
            'discount_type' => $request->input('discount_type') ?: null,
            'discount_value' => $request->input('discount_value', 0),
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
            'currency' => $validated['currency'] ?? 'INR',
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($items as $index => $item) {
            InvoiceItem::create([
                'tenant_id' => $tenant->id,
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $item['tax_amount'],
                'total' => $item['total'],
                'order_index' => $index,
            ]);
        }

        // Persist the sequence so numbers never repeat.
        Setting::set('invoice_sequence', (int) Setting::get('invoice_sequence', (int) Setting::get('invoice_start_number', 1000)) + 1);

        ActivityLog::record('invoice.created', $invoice, null, ['invoice_number' => $invoice->invoice_number]);

        app(\App\Services\AutomationService::class)->processEvent('invoice.created', $invoice, $tenant);

        if ($request->input('action') === 'save_and_sync') {
            SyncInvoiceToBikriBook::dispatch($invoice->id);

            return redirect()->route('finance.invoices.show', $invoice)->with('success', 'Invoice saved and queued for BikriBook sync.');
        }

        return redirect()->route('finance.invoices.show', $invoice)->with('success', 'Invoice saved as draft.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client.contacts', 'items', 'creator', 'bikribookSyncLogs']);

        return view('finance.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $clients = $this->visibleClients();
        $defaults = $this->defaults();

        return view('finance.invoices.edit', compact('invoice', 'clients', 'defaults'));
    }

    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        $validated = $request->validated();

        $items = $this->processItems($request->input('items', []));
        $subtotal = round(array_sum(array_column($items, 'line_total')), 2);
        [$discountAmount, $taxAmount, $total] = $this->calculateTotals($subtotal, (float) $validated['tax_rate'], $request);

        $invoice->update([
            'client_id' => $validated['client_id'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'tax_rate' => $validated['tax_rate'],
            'tax_amount' => $taxAmount,
            'discount_type' => $request->input('discount_type') ?: null,
            'discount_value' => $request->input('discount_value', 0),
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
            'currency' => $validated['currency'] ?? 'INR',
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
        ]);

        // If it was already synced to BikriBook, mark it for re-sync.
        if ($invoice->bikribook_invoice_id) {
            $invoice->update(['bikribook_sync_status' => 'not_synced']);
        }

        $invoice->items()->delete();
        foreach ($items as $index => $item) {
            InvoiceItem::create([
                'tenant_id' => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $item['tax_amount'],
                'total' => $item['total'],
                'order_index' => $index,
            ]);
        }

        ActivityLog::record('invoice.updated', $invoice, null, ['invoice_number' => $invoice->invoice_number]);

        return redirect()->route('finance.invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        ActivityLog::record('invoice.deleted', $invoice);

        return redirect()->route('finance.invoices.index')->with('success', 'Invoice deleted.');
    }

    /**
     * PDF: prefer BikriBook's PDF, fall back to local DomPDF.
     */
    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load('items', 'client', 'tenant');

        $content = null;

        if ($invoice->bikribook_pdf_url) {
            $tenant = $invoice->tenant;
            if ($tenant?->bikribook_api_key) {
                $content = (new BikriBookService($tenant))->downloadPdf($invoice);
            }
        }

        if ($content === null) {
            try {
                $pdf = Pdf::loadView('finance.invoice-pdf', ['invoice' => $invoice]);
                $content = $pdf->output();
            } catch (\Throwable $e) {
                logger()->error('Invoice PDF generation failed', ['invoice' => $invoice->id, 'error' => $e->getMessage()]);

                return back()->with('error', 'Could not generate the PDF right now. Please try again.');
            }
        }

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$invoice->invoice_number.'.pdf"',
        ]);
    }

    /**
     * "Send to Client" via BikriBook (BB emails the client). Falls back to a
     * local email when BikriBook is not configured.
     */
    public function send(Request $request, Invoice $invoice)
    {
        $tenant = app('currentTenant');

        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Cancelled invoices cannot be sent.');
        }

        if ($tenant->bikribook_api_key && $invoice->bikribook_invoice_id) {
            $sent = (new BikriBookService($tenant))->sendInvoice($invoice->bikribook_invoice_id, $invoice);

            if (! $sent) {
                return back()->with('error', 'BikriBook could not send the invoice. Please try again or retry the sync.');
            }
        } else {
            // Local fallback: email the PDF to the billing contact.
            $billing = $invoice->client?->billingContact;
            $email = $billing?->email ?? $invoice->client?->contacts()->value('email');

            if ($email) {
                $content = Pdf::loadView('finance.invoice-pdf', ['invoice' => $invoice->load('items', 'client')])->output();

                app(NotificationService::class)->sendEmailToAddress(
                    $email,
                    $billing?->name,
                    'Invoice '.$invoice->invoice_number.' from '.$tenant->name,
                    'Please find your invoice attached.',
                    []
                );
            }

            $invoice->update(['status' => 'sent', 'sent_at' => now()]);
        }

        ActivityLog::record('invoice.sent', $invoice);

        return back()->with('success', 'Invoice sent to client.');
    }

    public function sync(Request $request, Invoice $invoice)
    {
        $this->authorize('syncToBikriBook', $invoice);

        if (! app('currentTenant')->bikribook_api_key) {
            return back()->with('error', 'Configure your BikriBook API key in Settings → Integrations first.');
        }

        if ($invoice->bikribook_invoice_id) {
            // Re-sync status instead of creating a duplicate.
            SyncInvoiceStatusFromBikriBook::dispatch($invoice->id);

            return back()->with('success', 'Payment status sync queued.');
        }

        SyncInvoiceToBikriBook::dispatch($invoice->id);

        return back()->with('success', 'Sync to BikriBook queued. Check back in a moment.');
    }

    public function syncAll()
    {
        $this->authorize('viewAny', Invoice::class);

        $tenant = app('currentTenant');

        $invoiceIds = Invoice::where('tenant_id', $tenant->id)
            ->whereNotNull('bikribook_invoice_id')
            ->whereIn('status', ['sent', 'overdue'])
            ->pluck('id');

        foreach ($invoiceIds as $invoiceId) {
            SyncInvoiceStatusFromBikriBook::dispatch($invoiceId);
        }

        return back()->with('success', 'Status sync queued for '.$invoiceIds->count().' invoice(s).');
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $this->authorize('markPaid', $invoice);

        $validated = $request->validate([
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'payment_date' => ['nullable', 'date'],
        ]);

        $invoice->update([
            'status' => 'paid',
            'paid_amount' => $validated['paid_amount'] ?? $invoice->total_amount,
            'payment_method' => $validated['payment_method'],
            'payment_date' => $validated['payment_date'] ? now()->parse($validated['payment_date']) : now(),
        ]);

        ActivityLog::record('invoice.marked_paid', $invoice, null, ['amount' => $invoice->paid_amount]);

        return back()->with('success', 'Invoice marked as paid.');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function visibleClients()
    {
        $query = Client::query();

        if (auth()->user()->isAccountManager()) {
            $query->where('account_manager_id', auth()->id());
        }

        return $query->orderBy('company_name')->get();
    }

    protected function defaults(): array
    {
        $tenant = app('currentTenant');
        $settings = $tenant->settings ?? [];

        return [
            'tax_rate' => $settings['tax_rate'] ?? 18,
            'currency' => $settings['currency'] ?? 'INR',
            'payment_terms_days' => $settings['payment_terms_days'] ?? 15,
            'bank_name' => $settings['bank_name'] ?? null,
            'bank_account_number' => $settings['bank_account_number'] ?? null,
            'bank_ifsc' => $settings['bank_ifsc'] ?? null,
            'bank_beneficiary' => $settings['bank_beneficiary'] ?? null,
            'invoice_footer' => $settings['invoice_footer'] ?? null,
        ];
    }

    protected function nextInvoiceNumber(): string
    {
        $tenant = app('currentTenant');
        $settings = $tenant->settings ?? [];
        $prefix = $settings['invoice_prefix'] ?? 'INV';
        $start = (int) ($settings['invoice_start_number'] ?? 1000);

        $sequence = (int) Setting::get('invoice_sequence', $start - 1) + 1;

        return $prefix.'-'.now()->year.'-'.$sequence;
    }

    /**
     * Normalise + validate line items and compute per-item tax/total.
     *
     * @param  array<int, array<string, mixed>>  $rawItems
     * @return array<int, array<string, mixed>>
     */
    protected function processItems(array $rawItems): array
    {
        $items = [];

        foreach ($rawItems as $raw) {
            $description = trim((string) ($raw['description'] ?? ''));
            $quantity = (float) ($raw['quantity'] ?? 1);
            $unitPrice = (float) ($raw['unit_price'] ?? 0);
            $taxRate = (float) ($raw['tax_rate'] ?? 0);

            if ($description === '' || $quantity <= 0 || $unitPrice < 0) {
                continue;
            }

            $lineTotal = round($quantity * $unitPrice, 2);
            $taxAmount = round($lineTotal * $taxRate / 100, 2);

            $items[] = [
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal,
                'total' => round($lineTotal + $taxAmount, 2),
            ];
        }

        return $items;
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    protected function calculateTotals(float $subtotal, float $taxRate, Request $request): array
    {
        $discountType = $request->input('discount_type');
        $discountValue = (float) $request->input('discount_value', 0);

        $discountAmount = match ($discountType) {
            'percentage' => round($subtotal * $discountValue / 100, 2),
            'fixed' => min($discountValue, $subtotal),
            default => 0.0,
        };

        $taxable = max(0, $subtotal - $discountAmount);
        $taxAmount = round($taxable * $taxRate / 100, 2);
        $total = round($taxable + $taxAmount, 2);

        return [$discountAmount, $taxAmount, $total];
    }
}
