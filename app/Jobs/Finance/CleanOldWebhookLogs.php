<?php

namespace App\Jobs\Finance;

use App\Models\WebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Weekly: keeps webhook logs for the configured retention period (default 90 days).
 */
class CleanOldWebhookLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $days = (int) config('tenancy.webhook_log_retention_days', 90);

        WebhookLog::withoutGlobalScopes()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
