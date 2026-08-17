<?php

namespace App\Jobs\BikriBook;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Scheduled every 6 hours: checks payment status on BikriBook for every sent
 * or overdue invoice of every tenant with BikriBook configured.
 */
class SyncAllTenantsInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $tenants = Tenant::query()
            ->whereNotNull('bikribook_api_key')
            ->where('is_active', true)
            ->get();

        foreach ($tenants as $tenant) {
            $invoices = Invoice::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->whereNotNull('bikribook_invoice_id')
                ->whereIn('status', ['sent', 'overdue'])
                ->pluck('id');

            foreach ($invoices as $invoiceId) {
                SyncInvoiceStatusFromBikriBook::dispatch($invoiceId);
            }
        }
    }
}
