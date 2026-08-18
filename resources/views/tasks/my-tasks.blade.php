@extends('layouts.app')
@section('title', 'My tasks')
@section('breadcrumb', 'Tasks / My tasks')
@section('content')
<div class="flex gap-2 text-sm mb-6">
    <a href="{{ route('tasks.index') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">List</a>
    <a href="{{ route('tasks.board') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Board</a>
    <a href="{{ route('tasks.my-tasks') }}" class="px-3 py-1.5 rounded-lg bg-gray-900 text-white">My tasks</a>
    <a href="{{ route('tasks.calendar') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Calendar</a>
</div>

@foreach ([['overdue', 'Overdue', 'border-red-200', 'circle'], ['today', 'Today', 'border-indigo-200', 'calendar'], ['thisWeek', 'This week', 'border-gray-200', 'calendar-days'], ['later', 'Later', 'border-gray-200', 'sun']] as [$key, $label, $border, $labelIcon])
    <x-card :title="$label" :icon="$labelIcon" :padding="false">
        <div class="divide-y divide-gray-50">
            @forelse ($$key as $task)
                <a href="{{ route('tasks.show', $task) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
                    <x-status-badge :status="$task->status" />
                    <span class="text-sm text-gray-800 flex-1 truncate">{{ $task->title }}</span>
                    <span class="text-xs text-gray-400">{{ $task->client?->company_name }}</span>
                    <span class="text-xs text-gray-400">{{ $task->due_date?->format('d M') }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">Nothing here <x-icon name="sparkles" class="w-4 h-4 inline-block" /></p>
            @endforelse
        </div>
    </x-card>
@endforeach
@endsection
