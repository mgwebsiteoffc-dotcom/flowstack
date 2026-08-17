<?php

namespace App\Jobs\Finance;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Daily: marks expired trials as inactive after the grace period and
 * permanently deletes tenant data that has been inactive beyond grace + 30 days.
 */
class CleanExpiredTrials implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $graceDays = (int) config('tenancy.trial_grace_days', 30);

        // 1. Deactivate tenants whose trial expired beyond the grace period.
        Tenant::query()
            ->where('is_trial', true)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now()->subDays($graceDays))
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // 2. Delete trial tenants that stayed inactive past grace + 30 days.
        $doomed = Tenant::query()
            ->where('is_trial', true)
            ->where('is_active', false)
            ->where('trial_ends_at', '<', now()->subDays($graceDays + 30))
            ->get();

        foreach ($doomed as $tenant) {
            logger()->warning('Deleting expired trial tenant data', ['tenant_id' => $tenant->id, 'slug' => $tenant->slug]);
            $tenant->delete();
        }
    }
}
