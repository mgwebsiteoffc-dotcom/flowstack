@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')
@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stat-card title="Monthly Revenue" value="₹{{ number_format($stats['monthly_revenue']) }}" icon="banknotes" color="green" />
    <x-stat-card title="Active Clients" value="{{ $stats['active_clients'] }}" icon="users" color="indigo" />
    <x-stat-card title="Overdue Tasks" value="{{ $stats['overdue_tasks'] }}" icon="clock" color="{{ $stats['overdue_tasks'] > 0 ? 'red' : 'green' }}" />
    <x-stat-card title="Pipeline Value" value="₹{{ number_format($stats['pipeline_value']) }}" icon="target" color="purple" />
</div>

<div class="grid lg:grid-cols-3 gap-6 mt-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card title="Revenue (6 months)" icon="chart-bar">
            <canvas id="revenueChart" height="90"></canvas>
        </x-card>

        <x-card title="Client Health" icon="pulse">
            @forelse ($clients as $client)
                <a href="{{ route('clients.show', $client) }}" class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 rounded-lg px-2">
                    <x-health-badge :score="$client->health_score" />
                    <span class="text-sm font-medium text-gray-800 flex-1">{{ $client->company_name }}</span>
                    <span class="text-xs text-gray-400">{{ $client->accountManager?->name }}</span>
                    <span class="text-xs font-medium">₹{{ number_format($client->monthly_retainer ?? 0) }}</span>
                </a>
            @empty
                <x-empty-state icon="users" title="No clients yet" message="Add your first client to get started." :action="route('clients.create')" actionLabel="Add client" />
            @endforelse
        </x-card>

        <x-card title="Recent Activity" icon="clock">
            @forelse ($recentActivity as $log)
                <div class="flex items-start gap-3 py-2 border-b border-gray-50 last:border-0">
                    <x-user-avatar :user="$log->user" size="sm" />
                    <div class="min-w-0">
                        <div class="text-sm text-gray-800">{{ $log->action }}</div>
                        <div class="text-xs text-gray-400">{{ $log->created_at?->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-4 text-center">No activity yet.</p>
            @endforelse
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card title="Team Workload" icon="users">
            @foreach ($teamWorkload as $member)
                <div class="mb-3">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-700 font-medium">{{ $member->name }}</span>
                        <span class="text-gray-400">{{ $member->assigned_tasks_count }} open</span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full">
                        <div class="h-1.5 bg-indigo-500 rounded-full" style="width: {{ min(100, $member->assigned_tasks_count * 10) }}%"></div>
                    </div>
                </div>
            @endforeach
        </x-card>

        <x-card title="Tasks Due Today" icon="calendar">
            @forelse ($tasksDueToday as $task)
                <a href="{{ route('tasks.show', $task) }}" class="flex items-center gap-2 py-2 border-b border-gray-50 last:border-0">
                    <x-priority-badge :priority="$task->priority" />
                    <span class="text-sm text-gray-800 truncate flex-1">{{ $task->title }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-400 py-3 text-center">All clear today <x-icon name="sparkles" class="w-4 h-4 inline-block" /></p>
            @endforelse
        </x-card>

        <x-card title="Upcoming Deadlines" icon="hourglass">
            @forelse ($upcomingDeadlines as $task)
                <a href="{{ route('tasks.show', $task) }}" class="flex items-center gap-2 py-2 border-b border-gray-50 last:border-0">
                    <span class="text-xs bg-gray-100 rounded px-1.5 py-0.5 text-gray-600">{{ $task->due_date?->format('d M') }}</span>
                    <span class="text-sm text-gray-800 truncate flex-1">{{ $task->title }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-400 py-3 text-center">No upcoming deadlines.</p>
            @endforelse
        </x-card>

        @if ($calendarConnected)
            <x-card title="Upcoming Google Calendar" icon="calendar">
                @forelse ($upcomingEvents as $event)
                    <div class="py-2 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-blue-50 text-blue-700 rounded px-1.5 py-0.5 shrink-0">{{ $event['start'] ? $event['start']->format('d M H:i') : 'All day' }}</span>
                            <span class="text-sm text-gray-800 truncate flex-1">{{ $event['summary'] }}</span>
                        </div>
                        <div class="flex items-center gap-3 mt-0.5">
                            @if ($event['hangout'])
                                <a href="{{ $event['hangout'] }}" target="_blank" class="text-xs text-green-600 hover:underline">Join Meet</a>
                            @endif
                            @if ($event['html_link'])
                                <a href="{{ $event['html_link'] }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Calendar</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-3">No upcoming events.</p>
                @endforelse
            </x-card>
        @endif

        <x-card title="Lead Pipeline" icon="target">
            @foreach ($pipeline as $stage)
                <div class="flex justify-between text-sm py-1.5">
                    <span class="text-gray-600 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" style="background: {{ $stage->color }}"></span>{{ $stage->name }}
                    </span>
                    <span class="font-medium text-gray-800">{{ $stage->leads_count }}</span>
                </div>
            @endforeach
            <a href="{{ route('leads.pipeline') }}" class="text-xs text-indigo-600 mt-2 inline-block">View pipeline →</a>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: @json($stats['revenue_by_month']->keys()),
        datasets: [{
            label: 'Revenue (₹)',
            data: @json($stats['revenue_by_month']->values()),
            backgroundColor: '#6366f1',
            borderRadius: 6,
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
@endpush
