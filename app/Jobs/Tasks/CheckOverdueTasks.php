<?php

namespace App\Jobs\Tasks;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Scopes\TenantScope;
use App\Services\AutomationService;
use App\Services\ChannelNotificationService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Daily 07:00: marks tasks as overdue (status stays, automation + email fire).
 */
class CheckOverdueTasks implements ShouldQueue
{
 use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

 public function handle(NotificationService $notifications, AutomationService $automation): void
 {
 $tenantIds = Tenant::query()->where('is_active', true)->pluck('id');

 foreach ($tenantIds as $tenantId) {
 TenantScope::setCurrent($tenantId);
 $tenant = Tenant::find($tenantId);

 $overdueTasks = Task::withoutGlobalScopes()
 ->with('assignee')
 ->where('tenant_id', $tenantId)
 ->where('due_date', '<', now()->toDateString())
 ->whereNotIn('status', ['done', 'cancelled'])
 ->get();

 foreach ($overdueTasks as $task) {
 // Automation rules: task.overdue
 $automation->processEvent('task.overdue', $task, $tenant);

 // Email the assignee (once per day via this job).
 if ($task->assignee) {
 $notifications->sendEmail(
 $task->assignee,
 'Task overdue: '.$task->title,
 'Your task "'.$task->title.'" was due on '.$task->due_date?->toFormattedDateString().'. Please update it: '.route('tasks.show', $task->id),
 'task_overdue'
 );
 }
 }
 }

 TenantScope::forget();
 }
}
