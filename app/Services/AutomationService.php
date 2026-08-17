<?php

namespace App\Services;

use App\Models\AutomationDelay;
use App\Models\AutomationLog;
use App\Scopes\TenantScope;
use App\Models\AutomationRule;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The automation rules engine: processEvent() finds matching active rules for
 * a tenant + event, evaluates conditions, executes actions and logs everything
 * to automation_logs. Never throws - automation must not break the main flow.
 */
class AutomationService
{
 public function __construct(
 protected NotificationService $notifications
 ) {
 }

 /**
 * @param array<string, mixed> $extraContext
 */
 public function processEvent(string $event, Model $model, Tenant $tenant, array $extraContext = []): void
 {
 $rules = AutomationRule::withoutGlobalScopes()
 ->where('tenant_id', $tenant->id)
 ->where('is_active', true)
 ->where('trigger_event', $event)
 ->get();

 foreach ($rules as $rule) {
 $context = $this->buildContext($model, $tenant, $extraContext);

 try {
 if (! $this->conditionsPass($rule, $context)) {
 $this->logRule($rule, $context, 'skipped', null, 'Condition did not match');
 continue;
 }

 if ($rule->trigger_delay_hours > 0) {
 // Schedule the execution for later (run by CheckAutomationDelays).
 $delayContext = $context;
 unset($delayContext['model']); // models are not JSON-serialisable

 AutomationDelay::withoutGlobalScopes()->create([
 'tenant_id' => $tenant->id,
 'rule_id' => $rule->id,
 'event' => $event,
 'model_class' => get_class($model),
 'model_id' => $model->getKey(),
 'context' => $delayContext,
 'run_at' => now()->addHours((int) $rule->trigger_delay_hours),
 ]);

 $this->logRule($rule, $context, 'skipped', null, 'Delayed '.$rule->trigger_delay_hours.'h - scheduled for '.now()->addHours((int) $rule->trigger_delay_hours)->toDateTimeString());
 continue;
 }

 $this->runRule($rule, $model, $tenant, $context);
 } catch (\Throwable $e) {
 logger()->error('Automation rule failed', [
 'rule_id' => $rule->id,
 'event' => $event,
 'error' => $e->getMessage(),
 ]);
 $this->logRule($rule, $context ?? [], 'failed', null, substr($e->getMessage(), 0, 2000));
 }
 }
 }

 /**
 * Execute a rule whose delay has elapsed (called by CheckAutomationDelays).
 * The subject model is re-fetched and conditions re-evaluated so stale
 * subjects never trigger actions.
 */
 public function runDelayed(AutomationDelay $delay): void
 {
 $rule = $delay->rule;
 $tenant = Tenant::find($delay->tenant_id);

 if (! $rule || ! $tenant) {
 $delay->delete();

 return;
 }

 if (! $rule->is_active) {
 $delay->delete();

 return;
 }

 $class = $delay->model_class;
 $model = is_subclass_of($class, Model::class)
 ? $class::withoutGlobalScopes()->find($delay->model_id)
 : null;

 if (! $model) {
 // Subject was deleted - nothing to act on.
 $delay->delete();

 return;
 }

 TenantScope::setCurrent($tenant->id);

 $context = $this->buildContext($model, $tenant, (array) ($delay->context ?? []));

 try {
 if (! $this->conditionsPass($rule, $context)) {
 $this->logRule($rule, $context, 'skipped', null, 'Condition did not match at execution time');

 $delay->delete();

 return;
 }

 $this->runRule($rule, $model, $tenant, $context);
 } catch (\Throwable $e) {
 logger()->error('Delayed automation rule failed', [
 'delay_id' => $delay->id,
 'rule_id' => $rule->id,
 'error' => $e->getMessage(),
 ]);
 $this->logRule($rule, $context, 'failed', null, substr($e->getMessage(), 0, 2000));
 } finally {
 $delay->delete();
 }
 }

 /**
 * Execute a rule's actions, log the run and bump counters.
 *
 * @param array<string, mixed> $context
 */
 protected function runRule(AutomationRule $rule, Model $model, Tenant $tenant, array $context): void
 {
 $actionsTaken = $this->executeActions($rule, $context, $tenant);
 $this->logRule($rule, $context, 'success', $actionsTaken);

 $rule->last_run_at = now();
 $rule->run_count++;
 $rule->save();
 }

 /**
 * @return array<string, mixed>
 */
 protected function buildContext(Model $model, Tenant $tenant, array $extra): array
 {
 $context = $extra;
 $context['tenant_id'] = $tenant->id;

 if ($model instanceof Task) {
 $context['task_id'] = $model->id;
 $context['client_id'] = $model->client_id;
 $context['service_type'] = $model->service_type;
 $context['priority'] = $model->priority;
 $context['assigned_to'] = $model->assigned_to;
 $context['status'] = $model->status;
 $context['task_title'] = $model->title;
 $context['due_date'] = $model->due_date?->toDateString();
 $context['model'] = $model;
 } elseif ($model instanceof Lead) {
 $context['lead_id'] = $model->id;
 $context['client_id'] = $model->converted_to_client_id;
 $context['assigned_to'] = $model->assigned_to;
 $context['lead_source'] = $model->lead_source;
 $context['estimated_value'] = (float) $model->estimated_value;
 $context['stage'] = $model->current_stage;
 $context['lead_name'] = $model->contact_name;
 $context['status'] = $model->status;
 $context['model'] = $model;
 } elseif ($model instanceof Client) {
 $context['client_id'] = $model->id;
 $context['service_type'] = $model->services()->value('service_type');
 $context['status'] = $model->status;
 $context['model'] = $model;
 } elseif ($model instanceof \App\Models\Invoice) {
 $context['invoice_id'] = $model->id;
 $context['client_id'] = $model->client_id;
 $context['status'] = $model->status;
 $context['total_amount'] = (float) $model->total_amount;
 $context['model'] = $model;
 }

 return $context;
 }

 /**
 * @param array<string, mixed> $context
 */
 protected function conditionsPass(AutomationRule $rule, array $context): bool
 {
 $conditions = $rule->conditions ?? [];

 if (empty($conditions)) {
 return true;
 }

 foreach ($conditions as $condition) {
 $field = $condition['field'] ?? null;
 $operator = $condition['operator'] ?? 'equals';
 $value = $condition['value'] ?? null;

 if (! array_key_exists($field, $context)) {
 return false;
 }

 $actual = $context[$field];

 if (! $this->compare($actual, $operator, $value)) {
 return false;
 }
 }

 return true;
 }

 protected function compare($actual, string $operator, $expected): bool
 {
 return match ($operator) {
 'equals' => (string) $actual === (string) $expected,
 'not_equals' => (string) $actual !== (string) $expected,
 'contains' => is_string($actual) && str_contains($actual, (string) $expected),
 'greater_than' => (float) $actual > (float) $expected,
 'less_than' => (float) $actual < (float) $expected,
 'in' => is_array($expected) ? in_array((string) $actual, array_map('strval', $expected), true) : str_contains((string) $expected, (string) $actual),
 'is_null' => $actual === null || $actual === '',
 'not_null' => $actual !== null && $actual !== '',
 default => false,
 };
 }

 /**
 * @param array<string, mixed> $context
 * @return array<int, array<string, mixed>>
 */
 protected function executeActions(AutomationRule $rule, array $context, Tenant $tenant): array
 {
 $taken = [];

 foreach ($rule->actions ?? [] as $action) {
 $type = $action['type'] ?? null;
 $params = $action['params'] ?? [];

 $result = match ($type) {
 'send_notification' => $this->actionSendNotification($params, $context, $tenant),
 'send_email' => $this->actionSendEmail($params, $context, $tenant),
 'create_task' => $this->actionCreateTask($params, $context, $tenant),
 'change_task_status' => $this->actionChangeTaskStatus($params, $context),
 'assign_task' => $this->actionAssignTask($params, $context, $tenant),
 'create_project_from_template' => $this->actionCreateProjectFromTemplate($params, $context, $tenant),
 'post_comment' => $this->actionPostComment($params, $context),
 'log_activity' => $this->actionLogActivity($params, $context, $tenant),
 default => null,
 };

 if ($result !== null) {
 $taken[] = ['type' => $type, 'result' => $result];
 }
 }

 return $taken;
 }

 protected function resolveTargetUsers(array $params, array $context, Tenant $tenant): \Illuminate\Support\Collection
 {
 $target = $params['target'] ?? 'assigned_to';
 $users = collect();

 if ($target === 'assigned_to' && ! empty($context['assigned_to'])) {
 $users->push(User::withoutGlobalScopes()->find($context['assigned_to']));
 } elseif ($target === 'client_account_manager' && ! empty($context['client_id'])) {
 $client = Client::withoutGlobalScopes()->find($context['client_id']);
 if ($client?->account_manager_id) {
 $users->push(User::withoutGlobalScopes()->find($client->account_manager_id));
 }
 } elseif ($target === 'role') {
 $users = User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', $params['role'] ?? 'admin')->get();
 } elseif ($target === 'user' && ! empty($params['user_id'])) {
 $users->push(User::withoutGlobalScopes()->find($params['user_id']));
 } elseif ($target === 'creator' && ! empty($context['model']) && method_exists($context['model'], 'created_by')) {
 $users->push(User::withoutGlobalScopes()->find($context['model']->created_by));
 }

 return $users->filter()->unique('id')->values();
 }

 protected function actionSendNotification(array $params, array $context, Tenant $tenant): string
 {
 $message = $this->interpolate($params['message'] ?? 'Automation notification', $context);

 $this->resolveTargetUsers($params, $context, $tenant)->each(function ($user) use ($message, $context) {
 $this->notifications->notifyUser(
 $user,
 $message,
 $context['task_title'] ?? $context['lead_name'] ?? 'Automation',
 'dashboard'
 );
 });

 return 'notified';
 }

 protected function actionSendEmail(array $params, array $context, Tenant $tenant): string
 {
 $subject = $this->interpolate($params['subject'] ?? 'Notification from your agency', $context);
 $message = $this->interpolate($params['message'] ?? '', $context);

 $this->resolveTargetUsers($params, $context, $tenant)->each(function ($user) use ($subject, $message) {
 $this->notifications->sendEmail($user, $subject, $message);
 });

 return 'emailed';
 }

 protected function actionCreateTask(array $params, array $context, Tenant $tenant): string
 {
 $title = $this->interpolate($params['title'] ?? 'New task', $context);
 $assigneeId = $params['assignee'] === 'assigned_to' ? ($context['assigned_to'] ?? null) : ($params['assignee_user_id'] ?? null);
 $dueDays = (int) ($params['due_days'] ?? 0);

 $creator = User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'admin')->orderBy('id')->first();

 $task = Task::withoutGlobalScopes()->create([
 'tenant_id' => $tenant->id,
 'client_id' => $context['client_id'] ?? null,
 'title' => $title,
 'description' => $this->interpolate($params['description'] ?? '', $context) ?: null,
 'status' => $params['status'] ?? 'todo',
 'priority' => $params['priority'] ?? 'medium',
 'service_type' => $context['service_type'] ?? null,
 'task_type' => 'one_time',
 'assigned_to' => $assigneeId,
 'created_by' => $creator?->id,
 'due_date' => $dueDays > 0 ? now()->addDays($dueDays)->toDateString() : ($dueDays === 0 ? now()->toDateString() : null),
 ]);

 return 'task#'.$task->id;
 }

 protected function actionChangeTaskStatus(array $params, array $context): string
 {
 $taskId = $context['task_id'] ?? null;
 if (! $taskId) {
 return 'no-task';
 }

 $task = Task::withoutGlobalScopes()->find($taskId);
 if ($task) {
 $task->status = $params['status'] ?? 'done';
 $task->save();
 }

 return 'status-changed';
 }

 protected function actionAssignTask(array $params, array $context, Tenant $tenant): string
 {
 $taskId = $context['task_id'] ?? null;
 $userId = $params['user_id'] ?? $context['assigned_to'] ?? null;

 if (! $taskId || ! $userId) {
 return 'noop';
 }

 $task = Task::withoutGlobalScopes()->find($taskId);
 if ($task) {
 $task->assigned_to = $userId;
 $task->save();
 }

 return 'assigned';
 }

 protected function actionCreateProjectFromTemplate(array $params, array $context, Tenant $tenant): string
 {
 $templateId = $params['template_id'] ?? null;
 $clientId = $context['client_id'] ?? null;

 if (! $templateId || ! $clientId) {
 return 'missing-template-or-client';
 }

 $template = \App\Models\ProjectTemplate::withoutGlobalScopes()
 ->where('tenant_id', $tenant->id)->find($templateId);

 if (! $template) {
 return 'template-not-found';
 }

 $creator = User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'admin')->orderBy('id')->first();

 $project = \App\Models\Project::withoutGlobalScopes()->create([
 'tenant_id' => $tenant->id,
 'client_id' => $clientId,
 'name' => $this->interpolate($params['name'] ?? $template->name, $context),
 'description' => $template->description,
 'status' => 'active',
 'service_type' => $template->service_type,
 'template_id' => $template->id,
 'created_by' => $creator?->id,
 ]);

 // Seed tasks from the template.
 foreach ($template->templateTasks as $index => $templateTask) {
 Task::withoutGlobalScopes()->create([
 'tenant_id' => $tenant->id,
 'client_id' => $clientId,
 'project_id' => $project->id,
 'title' => $templateTask->title,
 'description' => $templateTask->description,
 'status' => 'todo',
 'priority' => $templateTask->default_priority,
 'service_type' => $template->service_type,
 'task_type' => $templateTask->task_type,
 'recurrence_type' => $templateTask->recurrence_type,
 'estimated_hours' => $templateTask->estimated_hours,
 'created_by' => $creator?->id,
 'order_index' => $templateTask->order_index,
 ]);
 }

 return 'project#'.$project->id;
 }

 protected function actionPostComment(array $params, array $context): string
 {
 $taskId = $context['task_id'] ?? null;
 if (! $taskId) {
 return 'no-task';
 }

 $text = $this->interpolate($params['text'] ?? '', $context);
 $actor = User::withoutGlobalScopes()->where('tenant_id', $context['tenant_id'] ?? 0)->where('role', 'admin')->orderBy('id')->first();

 \App\Models\TaskComment::withoutGlobalScopes()->create([
 'tenant_id' => $context['tenant_id'] ?? 0,
 'task_id' => $taskId,
 'user_id' => $actor?->id,
 'comment' => $text,
 ]);

 return 'commented';
 }

 protected function actionLogActivity(array $params, array $context, Tenant $tenant): string
 {
 \App\Models\ActivityLog::withoutGlobalScopes()->create([
 'tenant_id' => $tenant->id,
 'user_id' => null,
 'action' => $this->interpolate($params['message'] ?? 'Automation activity', $context),
 'model_type' => isset($context['model']) ? get_class($context['model']) : null,
 'model_id' => $context['model']->getKey() ?? null,
 'created_at' => now(),
 ]);

 return 'logged';
 }

 /**
 * Replace [variables] in a string with context values.
 *
 * @param array<string, mixed> $context
 */
 protected function interpolate(string $text, array $context): string
 {
 return preg_replace_callback('/\[([a-z_]+)\]/i', function ($m) use ($context) {
 $key = $m[1];

 if ($key === 'lead_name') {
 return (string) ($context['lead_name'] ?? '');
 }

 if ($key === 'task_title') {
 return (string) ($context['task_title'] ?? '');
 }

 return (string) ($context[$key] ?? '');
 }, $text) ?? $text;
 }

 /**
 * @param array<string, mixed> $context
 * @param array<int, mixed>|null $actionsTaken
 */
 protected function logRule(AutomationRule $rule, array $context, string $status, ?array $actionsTaken, ?string $error = null): void
 {
 try {
 AutomationLog::withoutGlobalScopes()->create([
 'tenant_id' => $rule->tenant_id,
 'rule_id' => $rule->id,
 'trigger_data' => $context,
 'status' => $status,
 'actions_taken' => $actionsTaken,
 'error_message' => $error,
 'created_at' => now(),
 ]);
 } catch (\Throwable $e) {
 logger()->error('Could not write automation log', ['error' => $e->getMessage()]);
 }
 }
}
