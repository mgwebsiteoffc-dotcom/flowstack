@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'My Dashboard')
@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stat-card title="My Clients" value="{{ $stats['clients'] }}" icon="users" color="indigo" />
    <x-stat-card title="Open Tasks" value="{{ $stats['open_tasks'] }}" icon="check-circle" color="blue" />
    <x-stat-card title="Pending Approvals" value="{{ $stats['pending_approvals'] }}" icon="hourglass" color="amber" />
    <x-stat-card title="Reports in Draft" value="{{ $stats['reports_due'] }}" icon="chart-bar" color="purple" />
</div>

<div class="grid lg:grid-cols-2 gap-6 mt-6">
    <x-card title="My Clients" icon="users">
        @forelse ($clients as $client)
            <a href="{{ route('clients.show', $client) }}" class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 rounded-lg px-2">
                <x-health-badge :score="$client->health_score" />
                <span class="text-sm font-medium text-gray-800 flex-1">{{ $client->company_name }}</span>
                <span class="text-xs text-gray-400"><x-financial>₹{{ number_format($client->monthly_retainer ?? 0) }}/mo</x-financial></span>
            </a>
        @empty
            <x-empty-state icon="users" title="No clients assigned" message="Clients assigned to you will appear here." />
        @endforelse
    </x-card>

    <x-card title="Tasks Due Today" icon="calendar">
        @forelse ($tasksDueToday as $task)
            <a href="{{ route('tasks.show', $task) }}" class="flex items-center gap-2 py-2 border-b border-gray-50 last:border-0">
                <x-priority-badge :priority="$task->priority" />
                <span class="text-sm text-gray-800 truncate flex-1">{{ $task->title }}</span>
                <span class="text-xs text-gray-400">{{ $task->client?->company_name }}</span>
            </a>
        @empty
            <p class="text-sm text-gray-400 py-3 text-center">No tasks due today <x-icon name="sparkles" class="w-4 h-4 inline-block" /></p>
        @endforelse
    </x-card>
</div>
@endsection
