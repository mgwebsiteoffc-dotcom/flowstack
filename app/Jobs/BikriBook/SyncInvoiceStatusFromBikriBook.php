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

class SyncInvoiceStatusFromBikriBook implements ShouldQueue
{
 use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

 public int $tries = 2;

 public function __construct(public int $invoiceId)
 {
 }

 public function handle(): void
 {
 $invoice = Invoice::withoutGlobalScopes()->find($this->invoiceId);

 if (! $invoice || ! $invoice->bikribook_invoice_id) {
 return;
 }

 $tenant = Tenant::find($invoice->tenant_id);

 if (! $tenant || ! $tenant->bikribook_api_key) {
 return;
 }

 TenantScope::setCurrent($tenant->id);

 (new BikriBookService($tenant))->syncInvoiceStatus($invoice);
 }
}
