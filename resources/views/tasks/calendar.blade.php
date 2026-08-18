@extends('layouts.app')
@section('title', 'Task calendar')
@section('breadcrumb', 'Tasks / Calendar')
@section('content')
<div class="flex gap-2 text-sm mb-6">
    <a href="{{ route('tasks.index') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">List</a>
    <a href="{{ route('tasks.board') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Board</a>
    <a href="{{ route('tasks.my-tasks') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">My tasks</a>
    <a href="{{ route('tasks.calendar') }}" class="px-3 py-1.5 rounded-lg bg-gray-900 text-white">Calendar</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="text-sm text-gray-400 mb-4">Tasks with due dates · next 6 weeks</div>
    <div class="grid grid-cols-7 gap-1.5 text-center">
        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d)
            <div class="text-[10px] font-semibold text-gray-400 uppercase py-1">{{ $d }}</div>
        @endforeach
        @php
            $start = now()->startOfWeek()->subWeeks(1);
            $byDate = $tasks->groupBy(fn ($t) => $t->due_date?->toDateString());
        @endphp
        @for ($i = 0; $i < 42; $i++)
            @php
                $date = $start->copy()->addDays($i);
                $dayTasks = $byDate[$date->toDateString()] ?? collect();
            @endphp
            <div class="min-h-[72px] rounded-lg border p-1 {{ $date->isToday() ? 'border-indigo-500 bg-indigo-50' : 'border-gray-100' }} {{ $date->month !== now()->month ? 'opacity-40' : '' }}">
                <div class="text-[10px] text-gray-400">{{ $date->day }}</div>
                @foreach ($dayTasks->take(3) as $task)
                    <a href="{{ route('tasks.show', $task) }}" title="{{ $task->title }}"
                       class="block text-[9px] truncate rounded px-1 py-0.5 mb-0.5 {{ $task->status === 'done' ? 'bg-green-100 text-green-700 line-through' : ($task->isOverdue() ? 'bg-red-100 text-red-700' : 'bg-indigo-100 text-indigo-700') }}">
                        {{ $task->title }}
                    </a>
                @endforeach
                @if ($dayTasks->count() > 3)<div class="text-[9px] text-gray-400">+{{ $dayTasks->count() - 3 }} more</div>@endif
            </div>
        @endfor
    </div>
</div>
@endsection
