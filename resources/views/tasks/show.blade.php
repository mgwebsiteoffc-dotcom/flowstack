@extends('layouts.app')
@section('title', $task->title)
@section('breadcrumb', 'Tasks / '.Str::limit($task->title, 40))
@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Title + quick actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <h1 class="text-lg font-bold text-gray-900" x-data="{ editing: false, title: @json($task->title) }">
                    <template x-if="!editing"><span @dblclick="editing = true" x-text="title"></span></template>
                    <template x-if="editing">
                        <form method="POST" action="{{ route('tasks.update', $task) }}" @submit="editing = false" class="flex gap-2">
                            @csrf @method('PATCH')
                            <input type="text" name="title" x-model="title" class="border rounded-lg px-2 py-1 text-base w-full">
                            <button class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-sm">Save</button>
                        </form>
                    </template>
                </h1>
                <div class="flex items-center gap-2">
                    @if ($runningEntry)
                        <span class="text-xs bg-red-50 text-red-700 rounded-full px-3 py-1.5"><x-icon name="clock" class="w-3 h-3 inline-block" /> Timer running on {{ $runningEntry->task_id === $task->id ? 'this task' : '#'.$runningEntry->task_id }}</span>
                    @else
                        <form method="POST" action="{{ route('time.start') }}">@csrf
                            <input type="hidden" name="task_id" value="{{ $task->id }}">
                            <button class="text-xs bg-indigo-50 text-indigo-700 rounded-full px-3 py-1.5 hover:bg-indigo-100"><x-icon name="play" class="w-4 h-4 inline-block" /> Start timer</button>
                        </form>
                    @endif
                    <x-confirm-delete :action="route('tasks.destroy', $task)" message="Delete this task? Subtasks and comments will also be removed.">
                        <x-slot:trigger><span class="text-xs bg-red-50 text-red-600 rounded-full px-3 py-1.5 cursor-pointer">Delete</span></x-slot:trigger>
                    </x-confirm-delete>
                </div>
            </div>

            <form method="POST" action="{{ route('tasks.status', $task) }}" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                @csrf
                <select name="status" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm" onchange="this.form.submit()">
                    @foreach (\App\Models\Task::STATUSES as $s)
                        <option value="{{ $s }}" {{ $task->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <select name="priority" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm" onchange="this.form.submit()">
                    @foreach (\App\Models\Task::PRIORITIES as $p)
                        <option value="{{ $p }}" {{ $task->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
                <select name="assigned_to" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm" onchange="this.form.submit()">
                    <option value="">Unassigned</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ $task->assigned_to === $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="due_date" value="{{ $task->due_date?->toDateString() }}" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm" onchange="this.form.submit()">
            </form>

            <div class="flex flex-wrap gap-2 mt-4 text-xs text-gray-500">
                <span class="bg-gray-100 rounded-full px-2.5 py-1">Client: {{ $task->client?->company_name ?? '—' }}</span>
                <span class="bg-gray-100 rounded-full px-2.5 py-1">Project: {{ $task->project?->name ?? '—' }}</span>
                <span class="bg-gray-100 rounded-full px-2.5 py-1">Est: {{ $task->estimated_hours ?? 0 }}h · Actual: {{ $task->actual_hours ?? $task->loggedMinutes() / 60 }}h</span>
                <span class="bg-gray-100 rounded-full px-2.5 py-1">Created by {{ $task->creator?->name }}</span>
                @if ($task->approval_status !== 'not_needed')
                    <span class="rounded-full px-2.5 py-1 {{ $task->approval_status === 'approved' ? 'bg-green-100 text-green-700' : ($task->approval_status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                        Approval: {{ str_replace('_', ' ', $task->approval_status) }}
                    </span>
                @endif
                <form method="POST" action="{{ route('tasks.watchers.toggle', $task) }}">@csrf
                    <button class="rounded-full px-2.5 py-1 {{ $task->watchers->contains('id', auth()->id()) ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 hover:bg-gray-200' }}">
                        <x-icon name="eye" class="w-4 h-4 inline-block" /> {{ $task->watchers->contains('id', auth()->id()) ? 'Watching' : 'Watch' }} ({{ $task->watchers->count() }})
                    </button>
                </form>
            </div>

            @if ($task->description)
                <div class="mt-4 text-sm text-gray-700 whitespace-pre-line bg-gray-50 rounded-lg p-4">{{ $task->description }}</div>
            @endif
        </div>

        <!-- Subtasks -->
        <x-card title="Subtasks" icon="puzzle-piece">
            @forelse ($task->subtasks as $sub)
                <div class="flex items-center gap-2 py-2 border-b border-gray-50 last:border-0">
                    <x-status-badge :status="$sub->status" />
                    <a href="{{ route('tasks.show', $sub) }}" class="text-sm text-gray-800 flex-1">{{ $sub->title }}</a>
                    <span class="text-xs text-gray-400">{{ $sub->assignee?->name }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-2">No subtasks</p>
            @endforelse
            <form method="POST" action="{{ route('tasks.subtasks.store', $task) }}" class="flex gap-2 mt-3">
                @csrf
                <input type="text" name="title" placeholder="Subtask title…" required class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                <select name="assigned_to" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                    <option value="">Unassigned</option>
                    @foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach
                </select>
                <button class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm">Add</button>
            </form>
        </x-card>

        <!-- Checklists -->
        <x-card title="Checklists" icon="clipboard">
            @foreach ($task->checklists as $checklist)
                <div class="mb-4">
                    <div class="font-medium text-sm text-gray-800 mb-1.5">{{ $checklist->title }}</div>
                    @foreach ($checklist->items as $item)
                        <form method="POST" action="{{ route('tasks.checklist-items.toggle', $item) }}" class="flex items-center gap-2 py-1">
                            @csrf
                            <button class="w-4 h-4 rounded border-2 {{ $item->is_completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300' }} text-[10px] flex items-center justify-center">{{ $item->is_completed ? '' : '' }}</button>
                            <span class="text-sm {{ $item->is_completed ? 'text-gray-400 line-through' : 'text-gray-700' }}">{{ $item->title }}</span>
                        </form>
                    @endforeach
                </div>
            @endforeach
            <form method="POST" action="{{ route('tasks.checklists.store', $task) }}" class="flex gap-2">
                @csrf
                <input type="text" name="title" placeholder="New checklist…" required class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                <button class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm">Add</button>
            </form>
        </x-card>


    </div>

    <div class="space-y-6">
        <!-- Comments -->
        <x-card title="Comments ({{ $task->comments->count() }})" icon="chat-bubble-left-right">
            <div class="space-y-3 mb-4 max-h-96 overflow-y-auto">
                @forelse ($task->comments as $comment)
                    <div class="flex gap-2.5">
                        <x-user-avatar :user="$comment->user" size="sm" />
                        <div class="bg-gray-50 rounded-xl rounded-tl-none px-3 py-2 flex-1">
                            <div class="text-xs text-gray-500"><span class="font-medium text-gray-800">{{ $comment->user?->name }}</span> · {{ $comment->created_at->diffForHumans() }}</div>
                            <div class="text-sm text-gray-700 mt-0.5 whitespace-pre-line">{{ $comment->comment }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-2">No comments yet</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('tasks.comments.store', $task) }}">
                @csrf
                <textarea name="comment" rows="2" placeholder="Write a comment…" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <button class="mt-2 bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm">Comment</button>
            </form>
        </x-card>

        <!-- Meta -->
        <x-card title="Details" icon="ℹ">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-400">Status</dt><dd><x-status-badge :status="$task->status" /></dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Priority</dt><dd><x-priority-badge :priority="$task->priority" /></dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Assignee</dt><dd class="text-gray-800">{{ $task->assignee?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Due date</dt><dd class="{{ $task->isOverdue() ? 'text-red-600 font-medium' : '' }}">{{ $task->due_date?->format('d M Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Created</dt><dd>{{ $task->created_at->format('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Updated</dt><dd>{{ $task->updated_at->diffForHumans() }}</dd></div>
            </dl>
        </x-card>
        <!-- Attachments -->
        <x-card title="Attachments" icon="paper-clip">
            @forelse ($task->attachments as $attachment)
                <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                    <span class="text-xl"><x-icon name="document" class="w-4 h-4 inline-block" /></span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-gray-800 truncate">{{ $attachment->file_name }}</div>
                        <div class="text-xs text-gray-400">{{ round($attachment->file_size / 1024, 1) }} KB · {{ $attachment->uploader?->name }}</div>
                    </div>
                    <a href="{{ route('tasks.attachments.download', [$task, $attachment]) }}" class="text-xs text-indigo-600">Download</a>
                    <form method="POST" action="{{ route('tasks.attachments.destroy', [$task, $attachment]) }}">@csrf @method('DELETE')
                        <button class="text-xs text-red-400"><x-icon name="x-mark" class="w-3 h-3" /></button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-2">No attachments</p>
            @endforelse
            <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data" class="flex gap-2 mt-3">
                @csrf
                <input type="file" name="attachment" required class="text-sm flex-1">
                <button class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm">Upload</button>
            </form>
        </x-card>

        <!-- Time log -->
        <x-card title="Time log" icon="clock">
            @forelse ($task->timeEntries as $entry)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0 text-sm">
                    <div>
                        <span class="text-gray-800 font-medium">{{ round($entry->duration_minutes / 60, 2) }}h</span>
                        <span class="text-gray-400 text-xs"> · {{ $entry->user?->name }} · {{ $entry->started_at?->format('d M H:i') }}</span>
                    </div>
                    <span class="text-xs {{ $entry->is_billable ? 'text-green-600' : 'text-gray-400' }}">{{ $entry->is_billable ? 'Billable' : 'Non-billable' }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-2">No time logged</p>
            @endforelse
            <div class="text-xs text-gray-400 mt-2">Total: {{ round($task->loggedMinutes() / 60, 2) }}h</div>
        </x-card>
    </div>
</div>
@endsection
