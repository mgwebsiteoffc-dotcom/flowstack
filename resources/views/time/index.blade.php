@extends('layouts.app')
@section('title', 'Time tracking')
@section('breadcrumb', 'Time')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex gap-2 text-sm">
        <a href="{{ route('time.index') }}" class="px-3 py-1.5 rounded-lg bg-gray-900 text-white">Log</a>
        <a href="{{ route('time.my') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">My timesheet</a>
        @if (auth()->user()->canAccessFinance())
            <a href="{{ route('time.all') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">All time</a>
        @endif
    </div>
    @if ($running)
        <div class="flex items-center gap-2 bg-red-50 text-red-700 rounded-full px-4 py-2 text-sm">
            <span class="animate-pulse">●</span> Running: {{ $running->task?->title ?? 'Task #'.$running->task_id }} · started {{ $running->started_at->diffForHumans() }}
            <form method="POST" action="{{ route('time.stop') }}">@csrf
                <button class="font-bold">■ Stop</button>
            </form>
        </div>
    @endif
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <x-card title="Manual time log" icon="✍️">
        <form method="POST" action="{{ route('time.store') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-3 gap-3">
                <input type="date" name="date" value="{{ now()->toDateString() }}" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="time" name="start_time" value="10:00" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="time" name="end_time" value="11:00" required class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <select name="task_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Task (optional)</option>
                @foreach ($tasks as $task)
                    <option value="{{ $task->id }}">#{{ $task->id }} · {{ Str::limit($task->title, 50) }}</option>
                @endforeach
            </select>
            <select name="client_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">Client (optional)</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                @endforeach
            </select>
            <input type="text" name="description" placeholder="What did you work on?" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_billable" value="1" checked class="rounded"> Billable</label>
            <button class="w-full bg-indigo-600 text-white rounded-lg py-2 text-sm">Log time</button>
        </form>
    </x-card>

    <x-card title="Recent entries" icon="🕓">
        <div class="divide-y divide-gray-50">
            @forelse ($entries as $entry)
                <div class="flex items-center gap-3 py-2.5">
                    <div class="text-sm font-medium text-gray-800 w-16">{{ round($entry->duration_minutes / 60, 2) }}h</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-gray-700 truncate">{{ $entry->task?->title ?? $entry->description ?? 'Manual entry' }}</div>
                        <div class="text-xs text-gray-400">{{ $entry->client?->company_name }} · {{ $entry->started_at?->format('d M H:i') }}</div>
                    </div>
                    <span class="text-xs {{ $entry->is_billable ? 'text-green-600' : 'text-gray-400' }}">{{ $entry->is_billable ? 'Billable' : 'Non-bill' }}</span>
                    <form method="POST" action="{{ route('time.destroy', $entry) }}" onsubmit="return confirm('Delete this entry?')">@csrf @method('DELETE')
                        <button class="text-red-400 text-xs">✕</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">No time entries yet. Use the timer in the top bar or log manually.</p>
            @endforelse
        </div>
    </x-card>
</div>
@endsection
