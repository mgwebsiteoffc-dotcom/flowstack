<?php

namespace App\Jobs\Finance;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Scopes\TenantScope;
use App\Services\AutomationService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Daily 07:00: flips sent invoices past due_date to overdue, fires
 * invoice.overdue automation and emails the client billing contact.
 */
class CheckOverdueInvoices implements ShouldQueue
{
 use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

 public function handle(NotificationService $notifications, AutomationService $automation): void
 {
 $tenantIds = Tenant::query()->where('is_active', true)->pluck('id');

 foreach ($tenantIds as $tenantId) {
 TenantScope::setCurrent($tenantId);
 $tenant = Tenant::find($tenantId);

 $invoices = Invoice::withoutGlobalScopes()
 ->with('client.contacts')
 ->where('tenant_id', $tenantId)
 ->where('status', 'sent')
 ->where('due_date', '<', now()->toDateString())
 ->get();

 foreach ($invoices as $invoice) {
 $invoice->status = 'overdue';
 $invoice->save();

 $automation->processEvent('invoice.overdue', $invoice, $tenant);

 // Alert the team too (spec: "invoice overdue" email notification).
 $notifications->emailRole(
 $tenant,
 ['admin', 'ops_manager'],
 'exclamation-triangle Invoice overdue: '.$invoice->invoice_number,
 $invoice->client?->company_name.' — '.$invoice->total_amount.' '.$invoice->currency.' was due on '.$invoice->due_date->toFormattedDateString().'.',
 'invoice_overdue'
 );

 $billing = $invoice->client?->contacts->firstWhere('is_billing_contact', true);
 $email = $billing?->email ?? $invoice->client?->contacts->first()?->email;

 if ($email) {
 $notifications->sendEmailToAddress(
 $email,
 $billing?->name,
 'Payment overdue: '.$invoice->invoice_number,
 'Hi '.($billing?->name ?? 'there').",\n\nInvoice ".$invoice->invoice_number.' for '.$invoice->total_amount.' '.$invoice->currency.' was due on '.$invoice->due_date->toFormattedDateString().".\n\nPlease arrange payment at your earliest convenience.",
 ['invoice_id' => $invoice->id]
 );
 }
 }
 }

 TenantScope::forget();
 }
}
