<?php

namespace App\Jobs\Finance;

use App\Models\Client;
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
 * Monthly on the 1st: alerts admins about contracts expiring within 30 days.
 */
class CheckContractRenewals implements ShouldQueue
{
 use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

 public function handle(NotificationService $notifications, AutomationService $automation): void
 {
 $tenantIds = Tenant::query()->where('is_active', true)->pluck('id');

 foreach ($tenantIds as $tenantId) {
 TenantScope::setCurrent($tenantId);
 $tenant = Tenant::find($tenantId);

 $expiring = Client::withoutGlobalScopes()
 ->with('accountManager')
 ->where('tenant_id', $tenantId)
 ->whereNotNull('contract_end_date')
 ->where('contract_end_date', '>=', now()->toDateString())
 ->where('contract_end_date', '<=', now()->addDays(30)->toDateString())
 ->get();

 foreach ($expiring as $client) {
 $automation->processEvent('contract.expiring', $client, $tenant);

 $notifications->notifyRole(
 $tenant,
 ['admin', 'ops_manager'],
 'exclamation-triangle Contract expiring: '.$client->company_name,
 'Contract ends '.$client->contract_end_date->toFormattedDateString().' - start the renewal conversation.',
 'clients.show',
 ['client' => $client->id]
 );

 $notifications->emailRole(
 $tenant,
 ['admin', 'ops_manager'],
 'exclamation-triangle Contract expiring: '.$client->company_name,
 'The contract for '.$client->company_name.' ends on '.$client->contract_end_date->toFormattedDateString().'. Start the renewal conversation.',
 'contract_expiring'
 );
 }
 }

 TenantScope::forget();
 }
}
