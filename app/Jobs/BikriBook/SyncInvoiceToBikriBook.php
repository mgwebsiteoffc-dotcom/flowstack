<?php

namespace App\Jobs\BikriBook;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Scopes\TenantScope;
use App\Services\BikriBookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sync one invoice to BikriBook. Retry 3 times with 5 minute delay between
 * attempts (backoff array). BikriBook failures NEVER delete or block the local
 * invoice - it stays saved with bikribook_sync_status=failed for a manual retry.
 */
class SyncInvoiceToBikriBook implements ShouldQueue
{
 use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

 public int $tries = 3;
 public array $backoff = [300, 300, 300];

 public function __construct(public int $invoiceId)
 {
 }

 public function handle(): void
 {
 $invoice = Invoice::withoutGlobalScopes()->with('items', 'client')->find($this->invoiceId);

 if (! $invoice) {
 return;
 }

 $tenant = Tenant::find($invoice->tenant_id);

 if (! $tenant || ! $tenant->bikribook_api_key) {
 $invoice->bikribook_sync_status = 'failed';
 $invoice->save();

 return;
 }

 TenantScope::setCurrent($tenant->id);

 (new BikriBookService($tenant))->createInvoice($invoice);
 }

 public function failed(\Throwable $e): void
 {
 Invoice::withoutGlobalScopes()->where('id', $this->invoiceId)->update([
 'bikribook_sync_status' => 'failed',
 ]);

 logger()->error('SyncInvoiceToBikriBook failed permanently', [
 'invoice_id' => $this->invoiceId,
 'error' => $e->getMessage(),
 ]);
 }
}
