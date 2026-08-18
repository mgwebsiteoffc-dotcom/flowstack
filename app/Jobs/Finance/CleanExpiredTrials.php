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

 // 1b. Warn once (15 days before deletion) so the owner can export/upgrade.
 Tenant::query()
 ->where('is_trial', true)
 ->where('is_active', false)
 ->where('trial_ends_at', '<', now()->subDays($graceDays + 15))
 ->get()
 ->each(function ($tenant) {
 $settings = $tenant->settings ?? [];

 if (! empty($settings['deletion_warned_at'])) {
 return;
 }

 try {
 \Illuminate\Support\Facades\Mail::to($tenant->email)->queue(new \App\Mail\AgencyMail(
 'Your Task365 trial data will be deleted soon',
 'Your '.$tenant->name.' trial ended '.$tenant->trial_ends_at->toFormattedDateString().'. All workspace data will be permanently deleted in 15 days unless you upgrade.\n\nUpgrade here: '.url('/upgrade'),
 []
 ));

 $settings['deletion_warned_at'] = now()->toDateTimeString();
 $tenant->settings = $settings;
 $tenant->save();
 } catch (\Throwable $e) {
 logger()->warning('Could not send trial deletion warning', ['tenant_id' => $tenant->id, 'error' => $e->getMessage()]);
 }
 });

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
