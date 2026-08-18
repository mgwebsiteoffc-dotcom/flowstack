<?php

namespace App\Jobs\Tasks;

use App\Models\IntegrationToken;
use App\Models\Setting;
use App\Models\Task;
use App\Models\Tenant;
use App\Scopes\TenantScope;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Creates/updates a Google Calendar event (with Google Meet) for a task with
 * a due date, when the tenant has connected Google Calendar and enabled task
 * sync. Never crashes the caller - failures are logged.
 */
class SyncTaskToCalendar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public int $taskId)
    {
    }

    public function handle(): void
    {
        $task = Task::withoutGlobalScopes()->with('client')->find($this->taskId);

        if (! $task || ! $task->due_date) {
            return;
        }

        $tenant = Tenant::find($task->tenant_id);

        if (! $tenant) {
            return;
        }

        TenantScope::setCurrent($tenant->id);

        // Toggle: google_calendar_sync_tasks must be on.
        $enabled = Setting::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('key', 'google_calendar_sync_tasks')
            ->value('value');

        if ((int) $enabled !== 1) {
            return;
        }

        $connected = IntegrationToken::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('provider', GoogleCalendarService::PROVIDER)
            ->exists();

        if (! $connected) {
            return;
        }

        try {
            $service = new GoogleCalendarService($tenant);

            $start = $task->due_date->copy()->setTime(10, 0);
            $end = $start->copy()->addHour();
            $description = trim(implode("\n", array_filter([
                $task->client?->company_name,
                $task->description,
                route('tasks.show', $task->id),
            ])));

            if ($task->calendar_event_id) {
                $service->updateEvent($task->calendar_event_id, $task->title, $start, $end, $description);
            } else {
                $result = $service->createEvent($task->title, $start, $end, $description, true);

                $task->calendar_event_id = $result['event_id'];
                $task->meet_link = $result['hangout_link'] ?? null;
                $task->save();
            }
        } catch (\Throwable $e) {
            GoogleCalendarService::logFailure($e, 'task #'.$task->id.' sync');
        }
    }
}
