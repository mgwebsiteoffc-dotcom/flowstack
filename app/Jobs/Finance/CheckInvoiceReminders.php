<?php

namespace App\Jobs\Finance;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Scopes\TenantScope;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Daily: emails the client billing contact a payment reminder for invoices
 * due in exactly 3 days (status: sent).
 */
class CheckInvoiceReminders implements ShouldQueue
{
 use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

 public function handle(NotificationService $notifications): void
 {
 $tenantIds = Tenant::query()->where('is_active', true)->pluck('id');

 foreach ($tenantIds as $tenantId) {
 TenantScope::setCurrent($tenantId);
 $tenant = Tenant::find($tenantId);

 $invoices = Invoice::withoutGlobalScopes()
 ->with('client.contacts')
 ->where('tenant_id', $tenantId)
 ->where('status', 'sent')
 ->where('due_date', now()->addDays(3)->toDateString())
 ->get();

 foreach ($invoices as $invoice) {
 $billing = $invoice->client?->contacts->firstWhere('is_billing_contact', true);
 $email = $billing?->email ?? $invoice->client?->contacts->first()?->email;

 if (! $email) {
 continue;
 }

 $notifications->sendEmailToAddress(
 $email,
 $billing?->name,
 'Payment reminder: '.$invoice->invoice_number.' due in 3 days',
 'Hi '.($billing?->name ?? 'there').",\n\nInvoice ".$invoice->invoice_number.' for '.$invoice->total_amount.' '.$invoice->currency.' is due on '.$invoice->due_date->toFormattedDateString().".\n\nPlease arrange payment before the due date.",
 ['invoice_id' => $invoice->id]
 );
 }
 }

 TenantScope::forget();
 }
}
