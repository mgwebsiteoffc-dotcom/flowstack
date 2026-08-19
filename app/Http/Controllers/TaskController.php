<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklist;
use App\Models\TaskChecklistItem;
use App\Models\TaskComment;
use App\Models\TaskWatcher;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\AiTaskGeneratorService;
use App\Services\AutomationService;
use App\Services\ChannelNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
 public function __construct()
 {
 $this->authorizeResource(Task::class, 'task');
 }

 public function index(Request $request)
 {
 $user = auth()->user();

 $query = Task::with('client', 'project', 'assignee', 'creator');

 $this->scopeTasksForUser($query, $user);

 if ($status = $request->input('status')) {
 $query->where('status', $status);
 }

 if ($priority = $request->input('priority')) {
 $query->where('priority', $priority);
 }

 if ($assigneeId = $request->input('assigned_to')) {
 $query->where('assigned_to', $assigneeId);
 }

 if ($clientId = $request->input('client_id')) {
 $query->where('client_id', $clientId);
 }

 if ($projectId = $request->input('project_id')) {
 $query->where('project_id', $projectId);
 }

 if ($search = $request->input('search')) {
 $query->where('title', 'like', "%{$search}%");
 }

 $tasks = $query->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
 ->orderBy('due_date')
 ->paginate(config('tenancy.pagination_size'))
 ->withQueryString();

 $clients = $this->visibleClients($user);
 $users = User::where('is_active', true)->orderBy('name')->get();

 return view('tasks.index', compact('tasks', 'clients', 'users'));
 }

 public function board()
 {
 $user = auth()->user();

 $query = Task::with('assignee', 'client')->whereNull('parent_task_id');

 $this->scopeTasksForUser($query, $user);

 if ($projectId = request()->input('project_id')) {
 $query->where('project_id', $projectId);
 }

 $tasks = $query->orderBy('order_index')->get()->groupBy('status');
 $projects = Project::with('client')->orderBy('name')->get();

 return view('tasks.board', compact('tasks', 'projects'));
 }

 public function myTasks()
 {
 $user = auth()->user();

 $base = fn ($q) => $q->where('assigned_to', $user->id)
 ->with('client', 'project')
 ->whereNull('parent_task_id');

 $overdue = (clone $base)->__invoke(Task::query())->overdue()->orderBy('due_date')->get();
 $today = (clone $base)->__invoke(Task::query())->dueToday()->orderBy('priority')->get();
 $thisWeek = Task::where('assigned_to', $user->id)
 ->whereBetween('due_date', [now()->addDay()->toDateString(), now()->addDays(7)->toDateString()])
 ->whereNotIn('status', ['done', 'cancelled'])
 ->with('client', 'project')
 ->whereNull('parent_task_id')
 ->orderBy('due_date')
 ->get();
 $later = Task::where('assigned_to', $user->id)
 ->where(function ($q) {
 $q->where('due_date', '>', now()->addDays(7)->toDateString())->orWhereNull('due_date');
 })
 ->whereNotIn('status', ['done', 'cancelled'])
 ->with('client', 'project')
 ->whereNull('parent_task_id')
 ->orderBy('due_date')
 ->get();

 return view('tasks.my-tasks', compact('overdue', 'today', 'thisWeek', 'later'));
 }

 public function calendar()
 {
 $user = auth()->user();

 $query = Task::with('assignee', 'client')->whereNotNull('due_date')->whereNull('parent_task_id');
 $this->scopeTasksForUser($query, $user);

 $tasks = $query->get(['id', 'title', 'due_date', 'status', 'priority']);

 return view('tasks.calendar', compact('tasks'));
 }

 public function create()
 {
 $user = auth()->user();
 $clients = $this->visibleClients($user);
 $users = User::where('is_active', true)->orderBy('name')->get();
 $projects = Project::with('client')->orderBy('name')->get();

 return view('tasks.create', compact('clients', 'users', 'projects'));
 }

    /**
     * Personal daily to-do list: pending (due today or overdue) vs completed.
     */
    public function today(Request $request)
    {
        $user = auth()->user();

        $date = now()->toDateString();
        if ($input = $request->input('date')) {
            try {
                $date = \Illuminate\Support\Carbon::parse($input)->toDateString();
            } catch (\Throwable) {
                $date = now()->toDateString();
            }
        }

        $base = fn ($q) => $q->where(function ($w) use ($user) {
            $w->where('assigned_to', $user->id)->orWhere('created_by', $user->id);
        })
            ->whereNull('parent_task_id')
            ->with('client', 'project');

        $pending = $base->__invoke(Task::query())
            ->whereDate('due_date', '<=', $date)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->get();

        $completed = $base->__invoke(Task::query())
            ->where('status', 'done')
            ->whereDate('completed_at', $date)
            ->orderByDesc('completed_at')
            ->get();

        $configured = app(AiTaskGeneratorService::class)->isConfigured();

        return view('tasks.today', compact('pending', 'completed', 'date', 'configured'));
    }

    /**
     * Quick-add a personal to-do (due today by default).
     */
    public function storeTodo(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
        ]);

        Task::create([
            'tenant_id' => \App\Support\CurrentTenant::id(),
            'title' => $validated['title'],
            'status' => 'todo',
            'task_type' => 'internal',
            'priority' => 'medium',
            'assigned_to' => auth()->id(),
            'created_by' => auth()->id(),
            'due_date' => $validated['date'] ?? now()->toDateString(),
            'order_index' => Task::max('order_index') + 1,
        ]);

        return redirect()->route('tasks.today')->with('success', 'To-do added.');
    }

    /**
     * Generate today's to-do list from a sentence/paragraph via AI.
     */
    public function aiGenerateDaily(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'paragraph' => ['required', 'string', 'max:10000'],
        ]);

        try {
            $tasks = app(AiTaskGeneratorService::class)->generateDailyTasks($validated['paragraph']);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $tenantId = \App\Support\CurrentTenant::id();
        $createdBy = auth()->id();
        $nextOrder = Task::max('order_index') ?? 0;
        $created = 0;

        foreach ($tasks as $task) {
            Task::create([
                'tenant_id' => $tenantId,
                'title' => $task['title'],
                'description' => $task['description'] ?? null,
                'start_date' => $task['start_date'] ?? now()->toDateString(),
                'due_date' => $task['due_date'] ?? now()->toDateString(),
                'priority' => $task['priority'] ?? 'medium',
                'status' => 'todo',
                'task_type' => 'internal',
                'assigned_to' => $createdBy,
                'created_by' => $createdBy,
                'order_index' => ++$nextOrder,
            ]);
            $created++;
        }

        return redirect()->route('tasks.today')->with('success', $created.' to-do(s) added to today.');
    }

    /**
     * Toggle a task between pending and done (sets/clears completed_at).
     */
    public function toggleDone(Task $task)
    {
        $this->authorize('update', $task);

        $done = $task->status !== 'done';

        $task->update([
            'status' => $done ? 'done' : 'todo',
            'completed_at' => $done ? now() : null,
        ]);

        ActivityLog::record('task.status_changed', $task, ['status' => $done ? 'todo' : 'done'], ['status' => $task->status]);
        app(AutomationService::class)->processEvent('task.status_changed', $task, \App\Support\CurrentTenant::get());

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'done' => $done, 'status' => $task->status]);
        }

        return back();
    }

    /**
     * AI task generator: input box + (optional) generated draft preview.
     */
    public function ai(Request $request)
    {
        $this->authorize('create', Task::class);

        if ($request->boolean('reset')) {
            session()->forget(['ai_paragraph', 'ai_draft_tasks']);

            return redirect()->route('tasks.ai');
        }

        $clients = $this->visibleClients(auth()->user());

        return view('tasks.ai', [
            'clients' => $clients,
            'paragraph' => session('ai_paragraph', ''),
            'draft' => session('ai_draft_tasks', []),
            'configured' => app(AiTaskGeneratorService::class)->isConfigured(),
        ]);
    }

    /**
     * Send the paragraph to the AI and stash the parsed task drafts for review.
     */
    public function aiGenerate(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'paragraph' => ['required', 'string', 'max:10000'],
        ]);

        try {
            $tasks = app(AiTaskGeneratorService::class)->generateTasks($validated['paragraph']);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // Resolve any client names the model guessed into real client ids.
        foreach ($tasks as $i => $task) {
            $tasks[$i]['client_id'] = $this->resolveClientId($task['client'] ?? null);
        }

        session()->put('ai_paragraph', $validated['paragraph']);
        session()->put('ai_draft_tasks', $tasks);

        return redirect()->route('tasks.ai')->with('success', count($tasks).' task(s) generated. Review and confirm below.');
    }

    /**
     * Create the confirmed tasks from the (editable) draft preview.
     */
    public function aiStore(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'tasks' => ['required', 'array', 'min:1', 'max:50'],
            'tasks.*.title' => ['required', 'string', 'max:255'],
            'tasks.*.description' => ['nullable', 'string', 'max:5000'],
            'tasks.*.start_date' => ['nullable', 'date'],
            'tasks.*.due_date' => ['nullable', 'date'],
            'tasks.*.client_id' => ['nullable', 'exists:clients,id'],
            'tasks.*.priority' => ['nullable', Rule::in(Task::PRIORITIES)],
            'tasks.*.estimated_hours' => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ]);

        $tenantId = \App\Support\CurrentTenant::id();
        $createdBy = auth()->id();
        $nextOrder = Task::max('order_index') ?? 0;
        $created = 0;

        foreach ($validated['tasks'] as $task) {
            $model = Task::create([
                'tenant_id' => $tenantId,
                'client_id' => $task['client_id'] ?? null,
                'title' => $task['title'],
                'description' => $task['description'] ?? null,
                'start_date' => $task['start_date'] ?? null,
                'due_date' => $task['due_date'] ?? null,
                'priority' => $task['priority'] ?? 'medium',
                'estimated_hours' => $task['estimated_hours'] ?? null,
                'status' => 'todo',
                'task_type' => 'one_time',
                'assigned_to' => null,
                'created_by' => $createdBy,
                'order_index' => ++$nextOrder,
            ]);

            $watcherIds = collect([$createdBy])->filter()->unique();
            foreach ($watcherIds as $userId) {
                TaskWatcher::firstOrCreate(['task_id' => $model->id, 'user_id' => $userId]);
            }

            ActivityLog::record('task.created', $model, null, ['title' => $model->title]);
            $created++;
        }

        session()->forget(['ai_paragraph', 'ai_draft_tasks']);

        return redirect()->route('tasks.index')->with('success', $created.' task(s) created from your paragraph.');
    }

    /**
     * Best-effort match of a client name (from the AI) to a visible client id.
     */
    protected function resolveClientId(?string $name): ?int
    {
        if (! $name) {
            return null;
        }

        $name = trim($name);
        $clients = $this->visibleClients(auth()->user());

        $exact = $clients->first(fn ($c) => strtolower((string) $c->company_name) === strtolower($name));
        if ($exact) {
            return $exact->id;
        }

        $contains = $clients->first(
            fn ($c) => stripos((string) $c->company_name, $name) !== false
                || stripos($name, (string) $c->company_name) !== false
        );

        return $contains?->id;
    }

    public function store(TaskRequest $request)
    {
 $data = $request->validated();
 $data['tenant_id'] = \App\Support\CurrentTenant::id();
 $data['created_by'] = auth()->id();
 $data['order_index'] = Task::max('order_index') + 1;

 if (empty($data['status'])) {
 $data['status'] = 'todo';
 }

 $task = Task::create($data);

 ActivityLog::record('task.created', $task, null, ['title' => $task->title]);

 app(AutomationService::class)->processEvent('task.created', $task, \App\Support\CurrentTenant::get());

 // Watchers: creator + assignee.
 $watcherIds = collect([$data['created_by'], $data['assigned_to'] ?? null])->filter()->unique();
 foreach ($watcherIds as $userId) {
 TaskWatcher::firstOrCreate(['task_id' => $task->id, 'user_id' => $userId]);
 }

 if ($request->input('return') === 'board') {
 return redirect()->route('tasks.board', ['project_id' => $task->project_id])->with('success', 'Task created.');
 }

 return redirect()->route('tasks.show', $task)->with('success', 'Task created.');
 }

 public function show(Task $task)
 {
 $task->load([
 'client',
 'project',
 'assignee',
 'creator',
 'approver',
 'parentTask',
 'subtasks.assignee',
 'comments.user',
 'attachments.uploader',
 'checklists.items.completedBy',
 'tags',
 'watchers',
 'timeEntries.user',
 ]);

 $users = User::where('is_active', true)->orderBy('name')->get();
 $projects = Project::with('client')->orderBy('name')->get();
 $clients = $this->visibleClients(auth()->user());
 $runningEntry = TimeEntry::where('user_id', auth()->id())->where('is_running', true)->first();

 return view('tasks.show', compact('task', 'users', 'projects', 'clients', 'runningEntry'));
 }

 public function update(TaskRequest $request, Task $task)
 {
 $old = $task->only(['title', 'status', 'priority', 'assigned_to', 'due_date']);
 $task->update($request->validated());

 ActivityLog::record('task.updated', $task, $old, $task->only(['title', 'status', 'priority', 'assigned_to', 'due_date']));

 if (($old['status'] ?? null) !== $task->status) {
 app(AutomationService::class)->processEvent('task.status_changed', $task, \App\Support\CurrentTenant::get());
 }

 // Keep the Google Calendar event (and Meet link) in sync.
 if ($task->due_date) {
 \App\Jobs\Tasks\SyncTaskToCalendar::dispatch($task->id);
 }

 if (($old['assigned_to'] ?? null) !== $task->assigned_to && $task->assigned_to) {
 app(AutomationService::class)->processEvent('task.assigned', $task, \App\Support\CurrentTenant::get());

 $assignee = User::find($task->assigned_to);
 if ($assignee) {
 $notifications = app(\App\Services\NotificationService::class);
 $notifications->notifyUser($assignee, 'Task assigned to you', $task->title, 'tasks.show', ['task' => $task->id]);
 $notifications->sendEmail($assignee, 'Task assigned to you: '.$task->title, 'You have been assigned: '.$task->title.'\n\n'.route('tasks.show', $task->id), 'task_assigned');
 }
 }

 return back()->with('success', 'Task updated.');
 }

 public function destroy(Task $task)
 {
 \App\Jobs\Tasks\DeleteTaskCalendarEvent::dispatch($task->id);

 $task->delete();
 ActivityLog::record('task.deleted', $task);

 return redirect()->route('tasks.index')->with('success', 'Task deleted.');
 }

 /**
  * Generate (or regenerate) the Google Meet link for a task.
  */
 public function generateMeet(Task $task)
 {
 $this->authorize('update', $task);

 \App\Jobs\Tasks\SyncTaskToCalendar::dispatch($task->id);

 return back()->with('success', 'Google Meet link is being generated. Refresh in a few seconds.');
 }

 public function updateStatus(Request $request, Task $task)
 {
        $validated = $request->validate([
            'status' => ['nullable', 'in:'.implode(',', Task::STATUSES)],
            'priority' => ['nullable', 'in:'.implode(',', Task::PRIORITIES)],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
        ]);

        $old = $task->only(['status', 'priority', 'assigned_to', 'start_date', 'due_date']);
        $updates = array_filter([
            'status' => $validated['status'] ?? null,
            'priority' => $validated['priority'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
        ], fn ($v) => $v !== null);

        // Keep completion time in sync whenever the status is changed.
        if (array_key_exists('status', $updates)) {
            $updates['completed_at'] = $updates['status'] === 'done' ? now() : null;
        }

        $task->update($updates);

 // If the parent recurring task is done, schedule its next instance.
 if ($task->is_recurring && ($validated['status'] ?? null) === 'done' && $task->next_recurrence_date) {
 \App\Jobs\Tasks\CreateRecurringTaskInstances::dispatchSync();
 }

 ActivityLog::record('task.status_changed', $task, $old, $task->only(['status', 'priority', 'assigned_to', 'due_date']));
 app(AutomationService::class)->processEvent('task.status_changed', $task, \App\Support\CurrentTenant::get());

 if ($request->expectsJson()) {
 return response()->json(['ok' => true, 'status' => $task->status]);
 }

 return back()->with('success', 'Status updated.');
 }

 public function reorderBoard(Request $request)
 {
 $validated = $request->validate([
 'ordered_ids' => ['required', 'array'],
 'ordered_ids.*' => ['integer'],
 'status' => ['required', 'in:'.implode(',', Task::STATUSES)],
 ]);

 foreach ($validated['ordered_ids'] as $index => $taskId) {
 Task::where('id', $taskId)->update([
 'status' => $validated['status'],
 'order_index' => $index,
 'completed_at' => $validated['status'] === 'done' ? now() : null,
 ]);
 }

 return response()->json(['ok' => true]);
 }

 public function storeComment(Request $request, Task $task)
 {
 $validated = $request->validate(['comment' => ['required', 'string']]);

 $comment = TaskComment::create([
 'tenant_id' => $task->tenant_id,
 'task_id' => $task->id,
 'user_id' => auth()->id(),
 'comment' => $validated['comment'],
 ]);

 // Notify watchers (except the commenter).
 $watchers = $task->watchers()->where('users.id', '!=', auth()->id())->get();
 foreach ($watchers as $watcher) {
 $notifications = app(\App\Services\NotificationService::class);
 $notifications->notifyUser($watcher, 'New comment on '.$task->title, Str::limit($validated['comment'], 120), 'tasks.show', ['task' => $task->id]);
 $notifications->sendEmail($watcher, 'New comment on '.$task->title, $task->title."\n\n".Str::limit($validated['comment'], 500)."\n\n".route('tasks.show', $task->id), 'task_comment');
 }

 return back()->with('success', 'Comment added.');
 }

 public function storeChecklist(Request $request, Task $task)
 {
 $validated = $request->validate(['title' => ['required', 'string', 'max:255']]);

 TaskChecklist::create([
 'tenant_id' => $task->tenant_id,
 'task_id' => $task->id,
 'title' => $validated['title'],
 'order_index' => $task->checklists()->count(),
 ]);

 return back()->with('success', 'Checklist added.');
 }

 public function toggleChecklistItem(Request $request, TaskChecklistItem $item)
 {
 $item->update([
 'is_completed' => ! $item->is_completed,
 'completed_by' => $item->is_completed ? auth()->id() : null,
 'completed_at' => $item->is_completed ? now() : null,
 ]);

 return back();
 }

 public function storeAttachment(Request $request, Task $task)
 {
 $request->validate([
 'attachment' => ['required', 'file', 'max:10240'],
 ]);

 $file = $request->file('attachment');
 $path = $file->store('tenants/'.$task->tenant_id.'/tasks/'.$task->id, 'tenant');

 TaskAttachment::create([
 'tenant_id' => $task->tenant_id,
 'task_id' => $task->id,
 'file_name' => $file->getClientOriginalName(),
 'file_path' => $path,
 'file_size' => $file->getSize(),
 'mime_type' => $file->getMimeType(),
 'uploaded_by' => auth()->id(),
 ]);

 return back()->with('success', 'Attachment uploaded.');
 }

 public function downloadAttachment(Task $task, TaskAttachment $attachment)
 {
 $this->authorize('view', $task);

 if (! Storage::disk('tenant')->exists($attachment->file_path)) {
 abort(404, 'File no longer exists.');
 }

 return Storage::disk('tenant')->download($attachment->file_path, $attachment->file_name);
 }

 public function destroyAttachment(Task $task, TaskAttachment $attachment)
 {
 Storage::disk('tenant')->delete($attachment->file_path);
 $attachment->delete();

 return back()->with('success', 'Attachment removed.');
 }

 public function storeSubtask(Request $request, Task $task)
 {
 $validated = $request->validate([
 'title' => ['required', 'string', 'max:255'],
 'assigned_to' => ['nullable', 'exists:users,id'],
 'due_date' => ['nullable', 'date'],
 ]);

 Task::create($validated + [
 'tenant_id' => $task->tenant_id,
 'client_id' => $task->client_id,
 'project_id' => $task->project_id,
 'parent_task_id' => $task->id,
 'status' => 'todo',
 'priority' => $task->priority,
 'service_type' => $task->service_type,
 'task_type' => 'one_time',
 'created_by' => auth()->id(),
 'order_index' => $task->subtasks()->count(),
 ]);

 return back()->with('success', 'Subtask added.');
 }

 /**
 * Submit a task deliverable for client approval (creates a ClientApproval
 * visible in the client portal; sets the task to waiting_approval).
 */
 public function submitApproval(Request $request, Task $task)
 {
 $this->authorize('update', $task);

 if (! $task->client_id) {
 return back()->with('error', 'This task has no client - approvals require a client.');
 }

 $validated = $request->validate([
 'title' => ['required', 'string', 'max:255'],
 'description' => ['nullable', 'string', 'max:5000'],
 'files' => ['nullable', 'array', 'max:5'],
 'files.*' => ['file', 'max:10240'],
 ]);

 $paths = [];

 foreach ($request->file('files', []) as $file) {
 $paths[] = $file->store('tenants/'.$task->tenant_id.'/approvals/'.$task->id, 'tenant');
 }

 \App\Models\ClientApproval::create([
 'tenant_id' => $task->tenant_id,
 'client_id' => $task->client_id,
 'task_id' => $task->id,
 'title' => $validated['title'],
 'description' => $validated['description'],
 'file_paths' => $paths ?: null,
 'status' => 'pending',
 'submitted_by' => auth()->id(),
 ]);

 $task->update([
 'approval_status' => 'pending',
 'status' => 'waiting_approval',
 ]);

 ActivityLog::record('task.submitted_for_approval', $task, null, ['title' => $validated['title']]);

 return back()->with('success', 'Deliverable submitted for client approval.');
 }

 public function toggleWatcher(Task $task)
 {
 $existing = TaskWatcher::where('task_id', $task->id)->where('user_id', auth()->id())->first();

 if ($existing) {
 $existing->delete();
 } else {
 TaskWatcher::create(['task_id' => $task->id, 'user_id' => auth()->id()]);
 }

 return back();
 }

 /**
 * Role-aware task scoping:
 * - account_manager: tasks under their clients
 * - specialist: tasks assigned to them or created by them
 */
 protected function scopeTasksForUser($query, User $user): void
 {
 if ($user->isAccountManager()) {
 $query->where(function ($q) use ($user) {
 $q->whereHas('client', fn ($c) => $c->where('account_manager_id', $user->id))
 ->orWhereNull('client_id');
 });

 return;
 }

 if ($user->isSpecialist()) {
 $query->where(function ($q) use ($user) {
 $q->where('assigned_to', $user->id)
 ->orWhere('created_by', $user->id)
 ->orWhereHas('project.members', fn ($m) => $m->where('user_id', $user->id));
 });
 }
 }

 protected function visibleClients(User $user)
 {
 $query = Client::query();

 if ($user->isAccountManager()) {
 $query->where('account_manager_id', $user->id);
 }

 return $query->orderBy('company_name')->get();
 }
}
