@extends('layouts.app')
@section('title', 'Tasks')
@section('breadcrumb', 'Tasks')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex gap-2 text-sm">
        <a href="{{ route('tasks.index') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('tasks.index') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">List</a>
        <a href="{{ route('tasks.board') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('tasks.board') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">Board</a>
        <a href="{{ route('tasks.my-tasks') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('tasks.my-tasks') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">My tasks</a>
        <a href="{{ route('tasks.calendar') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('tasks.calendar') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">Calendar</a>
    </div>
    <a href="{{ route('tasks.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New task</a>
</div>

<form method="GET" class="flex flex-wrap gap-2 text-sm mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks…" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm flex-1 min-w-[160px]">
    <select name="status" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
        <option value="">All statuses</option>
        @foreach (\App\Models\Task::STATUSES as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
        @endforeach
    </select>
    <select name="priority" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
        <option value="">All priorities</option>
        @foreach (\App\Models\Task::PRIORITIES as $p)
            <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
        @endforeach
    </select>
    <select name="client_id" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
        <option value="">All clients</option>
        @foreach ($clients as $client)
            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
        @endforeach
    </select>
    <select name="assigned_to" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
        <option value="">Anyone</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
        @endforeach
    </select>
    <button class="bg-gray-800 text-white px-4 py-1.5 rounded-lg">Filter</button>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3">Task</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Priority</th>
                <th class="px-4 py-3">Client / Project</th><th class="px-4 py-3">Assignee</th><th class="px-4 py-3">Due</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($tasks as $task)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('tasks.show', $task) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $task->title }}</a>
                        @if ($task->is_recurring)<span class="text-[10px] bg-purple-100 text-purple-700 rounded px-1.5 py-0.5"><x-icon name="arrow-path" class="w-4 h-4 inline-block" /> recurring</span>@endif
                    </td>
                    <td class="px-4 py-3"><x-status-badge :status="$task->status" /></td>
                    <td class="px-4 py-3"><x-priority-badge :priority="$task->priority" /></td>
                    <td class="px-4 py-3">
                        <div class="text-gray-800">{{ $task->client?->company_name ?? '—' }}</div>
                        <div class="text-xs text-gray-400">{{ $task->project?->name }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $task->assignee?->name ?? '—' }}</td>
                    <td class="px-4 py-3 {{ $task->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                        {{ $task->due_date?->format('d M') ?? '—' }}
                        @if ($task->isOverdue())<span class="block text-[10px]">overdue</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty-state icon="check-circle" title="No tasks found" message="Adjust your filters or create a new task." :action="route('tasks.create')" actionLabel="New task" /></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-pagination :paginator="$tasks" />
@endsection
