@extends('layouts.app')
@section('title', 'Lead analytics')
@section('breadcrumb', 'Leads / Analytics')
@section('content')
<div class="flex gap-2 text-sm mb-6">
    <a href="{{ route('leads.pipeline') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Pipeline</a>
    <a href="{{ route('leads.index') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">List</a>
    <a href="{{ route('leads.analytics') }}" class="px-3 py-1.5 rounded-lg bg-gray-900 text-white">Analytics</a>
</div>

<div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
    <x-stat-card title="Total Leads" value="{{ $totalLeads }}" icon="target" color="indigo" />
    <x-stat-card title="Won" value="{{ $wonLeads }} ({{ $winRate }}%)" icon="trophy" color="green" />
    <x-stat-card title="Lost" value="{{ $lostLeads }}" icon="no-symbol" color="red" />
    <x-stat-card title="Avg Deal Value" :value="\App\Support\Money::format($avgDealValue)" icon="gem" color="purple" />
    <x-stat-card title="Pipeline Value" :value="\App\Support\Money::format($pipelineValue)" icon="banknotes" color="amber" />
</div>

<div class="grid lg:grid-cols-2 gap-6 mt-6">
    <x-card title="Leads by source" icon="chart-bar">
        <canvas id="sourceChart" height="120"></canvas>
    </x-card>
    <x-card title="Leads by stage" icon="view-columns">
        <canvas id="stageChart" height="120"></canvas>
    </x-card>
    <x-card title="Leads over time (6 months)" icon="chart-bar">
        <canvas id="overTimeChart" height="120"></canvas>
    </x-card>
    <x-card title="Conversion rate by source" icon="target">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-gray-500 uppercase"><tr><th class="py-2">Source</th><th class="py-2 text-right">Leads</th><th class="py-2 text-right">Won</th><th class="py-2 text-right">Win rate</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($bySource as $row)
                    <tr>
                        <td class="py-2 capitalize">{{ str_replace('_', ' ', $row->source_type) }}</td>
                        <td class="py-2 text-right">{{ $row->total }}</td>
                        <td class="py-2 text-right">{{ $row->won }}</td>
                        <td class="py-2 text-right font-medium">{{ $row->total > 0 ? round($row->won * 100 / $row->total, 1).'%' : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4 text-sm font-medium text-gray-700">Monthly won value</div>
        <div class="flex flex-wrap gap-2 mt-2">
            @foreach ($monthlyWonValue as $month => $value)
                <span class="text-xs bg-green-50 text-green-700 rounded-full px-3 py-1">{{ $month }}: <x-money :value="$value" /></span>
            @endforeach
        </div>
    </x-card>
</div>

<div class="mt-6">
    <x-card title="Recent leads" icon="target">
        <div class="divide-y divide-gray-50">
            @forelse ($leads as $lead)
                <a href="{{ route('leads.show', $lead) }}" class="flex items-center gap-3 py-2.5 hover:bg-gray-50 px-2 rounded-lg">
                    <x-source-badge :source="$lead->source_type" />
                    <span class="text-sm text-gray-800 flex-1">{{ $lead->contact_name }}</span>
                    <span class="text-xs text-gray-400">{{ $lead->company_name }}</span>
                    <x-status-badge :status="$lead->status" type="lead" />
                    <span class="text-xs font-medium"><x-money :value="$lead->estimated_value ?? 0" /></span>
                </a>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">No leads yet.</p>
            @endforelse
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('sourceChart'), {
    type: 'pie',
    data: {
        labels: @json($bySource->pluck('source_type')->map(fn ($s) => ucwords(str_replace('_', ' ', $s)))),
        datasets: [{ data: @json($bySource->pluck('total')), backgroundColor: ['#f97316', '#3b82f6', '#8b5cf6', '#6b7280'] }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('stageChart'), {
    type: 'bar',
    data: {
        labels: @json($byStage->pluck('name')),
        datasets: [{ label: 'Leads', data: @json($byStage->pluck('leads_count')), backgroundColor: @json($byStage->pluck('color')) }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('overTimeChart'), {
    type: 'line',
    data: {
        labels: @json($overTime->keys()),
        datasets: [{ label: 'New leads', data: @json($overTime->values()), borderColor: '#6366f1', tension: 0.3, fill: true, backgroundColor: 'rgba(99,102,241,0.1)' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
@endpush
