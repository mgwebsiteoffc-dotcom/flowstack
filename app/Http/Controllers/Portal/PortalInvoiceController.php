<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\BikriBookService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PortalInvoiceController extends Controller
{
    public function index()
    {
        $client = auth('portal')->user()->client;

        $invoices = $client->invoices()
            ->where('status', '!=', 'draft')
            ->latest()
            ->paginate(15);

        return view('portal.invoices', compact('invoices'));
    }

    public function download(Invoice $invoice)
    {
        $client = auth('portal')->user()->client;

        if ($invoice->client_id !== $client->id) {
            abort(403);
        }

        $invoice->update(['viewed_at' => now()]);

        $content = null;

        $tenant = \App\Models\Tenant::find($invoice->tenant_id);
        if ($invoice->bikribook_pdf_url && $tenant?->bikribook_api_key) {
            $content = (new BikriBookService($tenant))->downloadPdf($invoice);
        }

        if ($content === null) {
            try {
                $content = Pdf::loadView('finance.invoice-pdf', ['invoice' => $invoice->load('items', 'client', 'tenant')])->output();
            } catch (\Throwable $e) {
                logger()->error('Portal invoice PDF failed', ['invoice' => $invoice->id, 'error' => $e->getMessage()]);

                return back()->with('error', 'Could not generate the PDF right now. Please try again.');
            }
        }

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$invoice->invoice_number.'.pdf"',
        ]);
    }
}
