<?php

namespace App\Services;

use App\Jobs\Notifications\DispatchChannelNotification;
use App\Models\Setting;
use App\Models\Tenant;

/**
 * Dispatches team-wide channel notifications (Slack + Microsoft Teams) for
 * key events, honoring per-tenant webhook URLs and event toggles.
 */
class ChannelNotificationService
{
    public const EVENTS = [
        'lead_new' => 'New lead',
        'lead_won' => 'Lead won',
        'lead_lost' => 'Lead lost',
        'task_assigned' => 'Task assigned',
        'task_overdue' => 'Task overdue',
        'invoice_paid' => 'Invoice paid',
        'invoice_overdue' => 'Invoice overdue',
        'contract_expiring' => 'Contract expiring',
        'client_request' => 'New client request',
        'report_shared' => 'Report shared',
    ];

    /**
     * Notify Slack and/or Teams for the given event (queued, never blocks).
     */
    public function notify(Tenant $tenant, string $event, string $title, string $message, string $color = '#4F46E5'): void
    {
        if (! isset(self::EVENTS[$event])) {
            return;
        }

        // Event toggles: null = all on; '[]' = none; otherwise allowed list.
        $enabled = Setting::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('key', 'channel_notify_events')
            ->value('value');

        if ($enabled !== null) {
            $allowed = json_decode($enabled, true) ?: [];

            if (! in_array($event, $allowed, true)) {
                return;
            }
        }

        $slackUrl = Setting::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)->where('key', 'slack_webhook_url')->value('value');

        if ($slackUrl) {
            DispatchChannelNotification::dispatch('slack', $slackUrl, $title, $message, $color);
        }

        $teamsUrl = Setting::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)->where('key', 'teams_webhook_url')->value('value');

        if ($teamsUrl) {
            DispatchChannelNotification::dispatch('teams', $teamsUrl, $title, $message, $color);
        }
    }
}
