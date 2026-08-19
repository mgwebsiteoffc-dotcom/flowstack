@extends('layouts.app')
@section('title', 'Today')
@section('breadcrumb', 'Tasks / Today')
@section('content')
@php
    $total = $pending->count() + $completed->count();
    $percent = $total > 0 ? (int) round($completed->count() * 100 / $total) : 0;
    $isToday = $date === now()->toDateString();
@endphp

<div class="flex gap-2 text-sm mb-6 flex-wrap items-center justify-between">
    <div class="flex gap-2 text-sm">
        <a href="{{ route('tasks.index') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">List</a>
        <a href="{{ route('tasks.board') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Board</a>
        <a href="{{ route('tasks.today') }}" class="px-3 py-1.5 rounded-lg bg-gray-900 text-white">Today</a>
        <a href="{{ route('tasks.my-tasks') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">My tasks</a>
        <a href="{{ route('tasks.calendar') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Calendar</a>
    </div>
    <div class="flex items-center gap-1 text-sm">
        <a href="{{ route('tasks.today', ['date' => \Illuminate\Support\Carbon::parse($date)->subDay()->toDateString()]) }}" class="px-2 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"><x-icon name="arrow-left" class="w-4 h-4" /></a>
        <form method="GET" class="inline-flex">
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-2 py-1 text-sm">
        </form>
        <a href="{{ route('tasks.today', ['date' => \Illuminate\Support\Carbon::parse($date)->addDay()->toDateString()]) }}" class="px-2 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"><x-icon name="arrow-right" class="w-4 h-4" /></a>
        @unless ($isToday)
            <a href="{{ route('tasks.today') }}" class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 text-xs hover:bg-gray-50">Today</a>
        @endunless
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Progress -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2"><x-icon name="chart-bar" class="w-5 h-5 text-indigo-600" /> {{ \Illuminate\Support\Carbon::parse($date)->format('l, d M Y') }}</h2>
                <span class="text-sm text-gray-500">{{ $completed->count() }} of {{ $total }} done</span>
            </div>
            <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-green-500 rounded-full transition-all" style="width: {{ $percent }}%"></div>
            </div>
        </div>

        <!-- Pending -->
        <x-card title="Pending" icon="circle" :padding="false">
            <div class="divide-y divide-gray-50">
                @forelse ($pending as $task)
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
                        <form method="POST" action="{{ route('tasks.done', $task) }}">@csrf
                            <button title="Mark done" class="w-5 h-5 rounded-full border-2 border-gray-300 hover:border-green-500 hover:bg-green-50 flex items-center justify-center text-white transition"></button>
                        </form>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('tasks.show', $task) }}" class="text-sm text-gray-800 hover:text-indigo-600 block truncate">{{ $task->title }}</a>
                            <div class="text-xs text-gray-400 flex items-center gap-2 mt-0.5">
                                @if ($task->due_date)<span class="{{ $task->isOverdue() ? 'text-red-500 font-medium' : '' }}">{{ $task->due_date->format('d M') }}</span>@endif
                                @if ($task->client)<span>· {{ $task->client->company_name }}</span>@endif
                                <x-priority-badge :priority="$task->priority" />
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-8">Nothing pending 🎉</p>
                @endforelse
            </div>
        </x-card>

        <!-- Completed -->
        <x-card title="Completed" icon="check-circle" :padding="false">
            <div class="divide-y divide-gray-50">
                @forelse ($completed as $task)
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
                        <form method="POST" action="{{ route('tasks.done', $task) }}">@csrf
                            <button title="Reopen" class="w-5 h-5 rounded-full border-2 border-green-500 bg-green-500 flex items-center justify-center text-white hover:bg-green-600 transition"><x-icon name="check" class="w-3 h-3" /></button>
                        </form>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('tasks.show', $task) }}" class="text-sm text-gray-400 line-through hover:text-indigo-600 block truncate">{{ $task->title }}</a>
                            <div class="text-xs text-gray-400 mt-0.5">
                                @if ($task->completed_at)Completed {{ $task->completed_at->format('H:i') }}@endif
                                @if ($task->client)· {{ $task->client->company_name }}@endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-8">Nothing completed yet</p>
                @endforelse
            </div>
        </x-card>
    </div>

    <!-- Side: add / generate -->
    <div class="space-y-6">
        <x-card title="Quick add" icon="plus">
            <form method="POST" action="{{ route('tasks.today.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <textarea name="title" rows="2" required placeholder="What needs to be done?" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                <button class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Add to-do</button>
            </form>
        </x-card>

        <x-card title="AI generate" icon="sparkles">
            @unless ($configured)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-3 py-2 mb-3 text-xs">Add <code class="font-mono">OPENROUTER_API_KEY</code> to <code class="font-mono">.env</code> to enable AI.</div>
            @endunless
            <form method="POST" action="{{ route('tasks.today.generate') }}" class="space-y-3" x-data="{ loading: false }" @submit="loading = true">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <textarea name="paragraph" rows="4" required placeholder="e.g. Prepare client report, review social media posts, call Acme about renewals" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <button type="submit" :disabled="loading" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-60 inline-flex items-center justify-center gap-2">
                    <x-icon name="sparkles" class="w-4 h-4" />
                    <span x-text="loading ? 'Generating…' : 'Generate today\'s list'"></span>
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-3">Describe your day and the AI will split it into to-dos dated today.</p>
        </x-card>
    </div>
</div>
@endsection
