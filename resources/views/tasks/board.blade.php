@extends('layouts.app')
@section('title', 'Task board')
@section('breadcrumb', 'Tasks / Board')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex gap-2 text-sm">
        <a href="{{ route('tasks.index') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">List</a>
        <a href="{{ route('tasks.board') }}" class="px-3 py-1.5 rounded-lg bg-gray-900 text-white">Board</a>
        <a href="{{ route('tasks.my-tasks') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">My tasks</a>
        <a href="{{ route('tasks.calendar') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Calendar</a>
    </div>
    <form method="GET" class="text-sm">
        <select name="project_id" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All projects</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }} ({{ $project->client?->company_name }})</option>
            @endforeach
        </select>
    </form>
</div>

<div x-data="board()" x-init="init()" class="flex gap-4 overflow-x-auto pb-4">
    @foreach (\App\Models\Task::STATUSES as $status)
        <div class="w-64 shrink-0 bg-gray-200/60 rounded-xl p-3">
            <div class="flex items-center justify-between px-1 mb-2">
                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ str_replace('_', ' ', $status) }}</span>
                <span class="text-xs bg-white rounded-full px-2 py-0.5 text-gray-500">{{ $tasks->get($status)?->count() ?? 0 }}</span>
            </div>
            <div data-status="{{ $status }}" class="board-column space-y-2 min-h-[120px]">
                @foreach ($tasks[$status] ?? [] as $task)
                    <div data-task-id="{{ $task->id }}"
                         class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 cursor-grab hover:border-indigo-300 transition">
                        <a href="{{ route('tasks.show', $task) }}" class="text-sm font-medium text-gray-800 hover:text-indigo-600 block">{{ $task->title }}</a>
                        <div class="flex items-center justify-between mt-2">
                            <x-priority-badge :priority="$task->priority" />
                            @if ($task->assignee)<span class="text-xs text-gray-400">{{ $task->assignee->name }}</span>@endif
                        </div>
                        <div class="text-xs text-gray-400 mt-1 flex items-center justify-between">
                            <span>{{ $task->client?->company_name }}</span>
                            @if ($task->due_date)<span class="{{ $task->isOverdue() ? 'text-red-500 font-medium' : '' }}">{{ $task->due_date->format('d M') }}</span>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<div class="text-xs text-gray-400 mt-3"><x-icon name="light-bulb" class="w-4 h-4 inline-block" /> Drag cards between columns to update status. <a href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" class="underline">SortableJS</a> powers drag & drop.</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
function board() {
    return {
        init() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            document.querySelectorAll('.board-column').forEach(col => {
                new Sortable(col, {
                    group: 'tasks',
                    animation: 150,
                    onEnd: async (evt) => {
                        const orderedIds = [];
                        evt.to.querySelectorAll('[data-task-id]').forEach(el => orderedIds.push(el.dataset.taskId));
                        const status = evt.to.dataset.status;
                        await fetch('{{ route('tasks.board.reorder') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({ ordered_ids: orderedIds, status })
                        });
                    }
                });
            });
        }
    }
}
</script>
@endpush
