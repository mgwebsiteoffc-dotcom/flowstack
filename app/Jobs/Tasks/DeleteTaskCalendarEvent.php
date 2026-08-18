<?php

namespace App\Jobs\Tasks;

use App\Models\Task;
use App\Models\Tenant;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Removes the Google Calendar event for a deleted task.
 */
class DeleteTaskCalendarEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $taskId)
    {
    }

    public function handle(): void
    {
        $task = Task::withoutGlobalScopes()->find($this->taskId);

        if (! $task || ! $task->calendar_event_id) {
            return;
        }

        $tenant = Tenant::find($task->tenant_id);

        if (! $tenant) {
            return;
        }

        try {
            (new GoogleCalendarService($tenant))->deleteEvent($task->calendar_event_id);
        } catch (\Throwable $e) {
            GoogleCalendarService::logFailure($e, 'task #'.$task->id.' delete event');
        }
    }
}
