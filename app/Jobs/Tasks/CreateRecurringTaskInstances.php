<?php

namespace App\Jobs\Tasks;

use App\Models\Task;
use App\Models\Tenant;
use App\Scopes\TenantScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Daily 06:00: creates the next instance for recurring tasks whose
 * next_recurrence_date has arrived.
 */
class CreateRecurringTaskInstances implements ShouldQueue
{
 use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

 public function handle(): void
 {
 $dueRecurring = Task::withoutGlobalScopes()
 ->with('client', 'project')
 ->where('is_recurring', true)
 ->whereNull('deleted_at')
 ->whereNotNull('next_recurrence_date')
 ->where('next_recurrence_date', '<=', now()->toDateString())
 ->get();

 foreach ($dueRecurring as $parent) {
 if ($parent->recurrence_ends_at && $parent->recurrence_ends_at->lt(now()->toDateString())) {
 continue;
 }

 $nextDate = $parent->next_recurrence_date;

 // Create the child instance for this occurrence.
 $child = Task::withoutGlobalScopes()->create([
 'tenant_id' => $parent->tenant_id,
 'client_id' => $parent->client_id,
 'project_id' => $parent->project_id,
 'parent_task_id' => $parent->parent_task_id,
 'title' => $parent->title,
 'description' => $parent->description,
 'status' => 'todo',
 'priority' => $parent->priority,
 'service_type' => $parent->service_type,
 'task_type' => 'recurring',
 'assigned_to' => $parent->assigned_to,
 'created_by' => $parent->created_by,
 'due_date' => $nextDate,
 'estimated_hours' => $parent->estimated_hours,
 'is_recurring' => false,
 'parent_recurring_task_id' => $parent->id,
 'order_index' => $parent->order_index,
 ]);

 // Advance the parent's next occurrence.
 $parent->next_recurrence_date = $this->nextOccurrence($parent, $nextDate);
 $parent->save();

 logger()->info('Recurring task instance created', [
 'parent_task_id' => $parent->id,
 'task_id' => $child->id,
 'due_date' => $nextDate,
 ]);
 }
 }

 protected function nextOccurrence(Task $task, \Carbon\CarbonInterface $from): ?\Carbon\CarbonInterface
 {
 $date = $from->copy();
 $interval = max(1, (int) $task->recurrence_interval);

 $next = match ($task->recurrence_type) {
 'daily' => $date->addDays($interval),
 'weekly' => $date->addWeeks($interval),
 'biweekly' => $date->addWeeks($interval * 2),
 'monthly' => $date->addMonths($interval),
 'custom' => $this->nextCustomDay($task, $date),
 default => null,
 };

 if ($next && $task->recurrence_ends_at && $next->gt($task->recurrence_ends_at)) {
 return null;
 }

 return $next;
 }

 protected function nextCustomDay(Task $task, \Carbon\CarbonInterface $from): ?\Carbon\CarbonInterface
 {
 $days = collect($task->recurrence_days ?? [])->map(fn ($d) => (int) $d);

 if ($days->isEmpty()) {
 return $from->addWeek();
 }

 for ($i = 1; $i <= 14; $i++) {
 $candidate = $from->copy()->addDays($i);
 if ($days->contains((int) $candidate->dayOfWeek)) {
 return $candidate;
 }
 }

 return $from->addWeek();
 }
}
