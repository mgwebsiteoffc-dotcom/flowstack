<?php

namespace App\Http\Controllers;

use App\Mail\AgencyMail;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\ProposalItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Proposal::with('client', 'lead');

        if ($user->isAccountManager()) {
            $query->whereHas('client', fn ($q) => $q->where('account_manager_id', $user->id));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($clientId = $request->input('client_id')) {
            $query->where('client_id', $clientId);
        }

        $proposals = $query->latest()->paginate(config('tenancy.pagination_size'))->withQueryString();
        $clients = $this->visibleClients();

        return view('proposals.index', compact('proposals', 'clients'));
    }

    /**
     * New proposal - optionally pre-filled from a lead (lead_id query param).
     */
    public function create(Request $request)
    {
        $clients = $this->visibleClients();
        $lead = $request->integer('lead_id') ? Lead::find($request->integer('lead_id')) : null;

        return view('proposals.create', compact('clients', 'lead'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'title' => ['required', 'string', 'max:255'],
            'valid_until' => ['nullable', 'date'],
            'currency' => ['nullable', 'string', 'size:3'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'discount_type' => ['nullable', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:1000'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $tenant = \App\Support\CurrentTenant::get();

        // Proposal number: PRO-{YEAR}-{SEQ}
        $seq = (int) \App\Models\Setting::get('proposal_sequence', 1000) + 1;
        \App\Models\Setting::set('proposal_sequence', $seq);

        $items = [];
        foreach ($validated['items'] as $raw) {
            $qty = (float) $raw['quantity'];
            $price = (float) $raw['unit_price'];
            $items[] = [
                'description' => trim($raw['description']),
                'quantity' => $qty,
                'unit_price' => $price,
                'total' => round($qty * $price, 2),
            ];
        }

        $subtotal = round(array_sum(array_column($items, 'total')), 2);
        $discountType = $validated['discount_type'] ?? null;
        $discountValue = (float) ($validated['discount_value'] ?? 0);

        $discountAmount = match ($discountType) {
            'percentage' => round($subtotal * $discountValue / 100, 2),
            'fixed' => min($discountValue, $subtotal),
            default => 0.0,
        };

        $taxable = max(0, $subtotal - $discountAmount);
        $taxAmount = round($taxable * (float) $validated['tax_rate'] / 100, 2);
        $total = round($taxable + $taxAmount, 2);

        $proposal = Proposal::create([
            'tenant_id' => $tenant->id,
            'client_id' => $validated['client_id'],
            'lead_id' => $validated['lead_id'] ?? null,
            'proposal_number' => 'PRO-'.now()->year.'-'.$seq,
            'title' => $validated['title'],
            'status' => 'draft',
            'valid_until' => $validated['valid_until'] ?? null,
            'subtotal' => $subtotal,
            'tax_rate' => $validated['tax_rate'],
            'tax_amount' => $taxAmount,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
            'currency' => $validated['currency'] ?? 'INR',
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($items as $index => $item) {
            ProposalItem::create($item + [
                'tenant_id' => $tenant->id,
                'proposal_id' => $proposal->id,
                'order_index' => $index,
            ]);
        }

        ActivityLog::record('proposal.created', $proposal, null, ['proposal_number' => $proposal->proposal_number]);

        return redirect()->route('proposals.show', $proposal)->with('success', 'Proposal created as draft.');
    }

    public function show(Proposal $proposal)
    {
        $proposal->load('client', 'lead', 'items', 'creator');

        return view('proposals.show', compact('proposal'));
    }

    public function pdf(Proposal $proposal)
    {
        $proposal->load('items', 'client', 'tenant');

        try {
            $pdf = Pdf::loadView('proposals.pdf', ['proposal' => $proposal]);

            return $pdf->download('proposal-'.$proposal->proposal_number.'.pdf');
        } catch (\Throwable $e) {
            logger()->error('Proposal PDF failed', ['proposal' => $proposal->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Could not generate the proposal PDF right now.');
        }
    }

    /**
     * Email the proposal PDF to the client billing contact.
     */
    public function send(Request $request, Proposal $proposal)
    {
        $billing = $proposal->client?->billingContact;
        $email = $billing?->email ?? $proposal->client?->contacts()->value('email');

        if (! $email) {
            return back()->with('error', 'This client has no contact email on file.');
        }

        try {
            $content = Pdf::loadView('proposals.pdf', ['proposal' => $proposal->load('items', 'client', 'tenant')])->output();

            Mail::send([], [], function ($message) use ($content, $email, $billing, $proposal) {
                $message->to($email, $billing?->name)
                    ->subject('Proposal '.$proposal->proposal_number.' from '.\App\Support\CurrentTenant::get()?->name ?? config('app.name'))
                    ->html('<p>Hi '.($billing?->name ?? 'there').',</p><p>Please find attached our proposal <strong>'.$proposal->proposal_number.'</strong> ('.$proposal->title.').</p><p>We look forward to working with you.</p>')
                    ->attachData($content, 'proposal-'.$proposal->proposal_number.'.pdf', ['mime' => 'application/pdf']);
            });

            $proposal->update(['status' => 'sent', 'sent_at' => now()]);

            ActivityLog::record('proposal.sent', $proposal);

            return back()->with('success', 'Proposal sent to '.$email.'.');
        } catch (\Throwable $e) {
            logger()->error('Proposal email failed', ['proposal' => $proposal->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Could not send the proposal email. Please try again.');
        }
    }

    public function updateStatus(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', Proposal::STATUSES)],
        ]);

        $proposal->update(['status' => $validated['status']]);

        ActivityLog::record('proposal.status_changed', $proposal, null, ['status' => $validated['status']]);

        return back()->with('success', 'Proposal marked as '.$validated['status'].'.');
    }

    public function destroy(Proposal $proposal)
    {
        $proposal->delete();
        ActivityLog::record('proposal.deleted', $proposal);

        return redirect()->route('proposals.index')->with('success', 'Proposal deleted.');
    }

    protected function visibleClients()
    {
        $query = Client::query();

        if (auth()->user()->isAccountManager()) {
            $query->where('account_manager_id', auth()->id());
        }

        return $query->orderBy('company_name')->get();
    }
}
