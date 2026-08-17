@extends('layouts.app')
@section('title', 'My Dashboard')
@section('breadcrumb', 'My Dashboard')
@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stat-card title="My Open Tasks" value="{{ $stats['my_tasks'] }}" icon="check-circle" color="indigo" />
    <x-stat-card title="Overdue" value="{{ $stats['overdue'] }}" icon="clock" color="{{ $stats['overdue'] > 0 ? 'red' : 'green' }}" />
    <x-stat-card title="Due Today" value="{{ $stats['due_today'] }}" icon="calendar" color="blue" />
    <x-stat-card title="Hours This Week" value="{{ $stats['hours_this_week'] }}" icon="clock" color="purple" />
</div>

<div class="mt-6">
    <x-card title="My Tasks" icon="check-circle">
        @forelse ($tasks as $task)
            <a href="{{ route('tasks.show', $task) }}" class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 rounded-lg px-2">
                <x-status-badge :status="$task->status" type="task" />
                <span class="text-sm font-medium text-gray-800 flex-1 truncate">{{ $task->title }}</span>
                @if ($task->isOverdue())<span class="text-xs text-red-500 font-medium">Overdue</span>@endif
                <span class="text-xs text-gray-400">{{ $task->client?->company_name }}</span>
                <span class="text-xs text-gray-400">{{ $task->due_date?->format('d M') }}</span>
            </a>
        @empty
            <x-empty-state icon="check-circle" title="No tasks assigned" message="Tasks assigned to you will show up here." :action="route('tasks.index')" actionLabel="Browse tasks" />
        @endforelse
    </x-card>
</div>
@endsection
