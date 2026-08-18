@extends('layouts.app')
@section('title', 'Profitability')
@section('breadcrumb', 'Finance / Profitability')
@section('content')
<div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-gray-900">Profitability</h2>
    <form method="GET" class="text-sm">
        <input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
            <tr><th class="px-4 py-3">Client</th><th class="px-4 py-3 text-right">Revenue</th><th class="px-4 py-3 text-right">Team Cost</th><th class="px-4 py-3 text-right">Tools Cost</th><th class="px-4 py-3 text-right">Profit</th><th class="px-4 py-3 text-right">Margin</th><th class="px-4 py-3">Status</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $row->client->company_name }}</td>
                    <td class="px-4 py-3 text-right"><x-money :value="$row->revenue" /></td>
                    <td class="px-4 py-3 text-right text-gray-600">-<x-money :value="$row->team_cost" /></td>
                    <td class="px-4 py-3 text-right text-gray-600">-<x-money :value="$row->tools_cost" /></td>
                    <td class="px-4 py-3 text-right font-medium {{ $row->profit < 0 ? 'text-red-600' : 'text-gray-900' }}"><x-money :value="$row->profit" /></td>
                    <td class="px-4 py-3 text-right font-bold {{ $row->status === 'green' ? 'text-green-600' : ($row->status === 'yellow' ? 'text-amber-600' : 'text-red-600') }}">{{ $row->margin }}%</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full {{ $row->status === 'green' ? 'bg-green-500' : ($row->status === 'yellow' ? 'bg-amber-400' : 'bg-red-500') }}"></span>
                            {{ $row->status === 'green' ? 'Healthy' : ($row->status === 'yellow' ? 'Watch' : 'At risk') }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center text-sm text-gray-400">No clients with revenue this month.</td></tr>
            @endforelse
            <tr class="bg-gray-50 font-bold">
                <td class="px-4 py-3">Agency total</td>
                <td class="px-4 py-3 text-right"><x-money :value="$totals->revenue" /></td>
                <td class="px-4 py-3 text-right">-<x-money :value="$totals->team_cost" /></td>
                <td class="px-4 py-3 text-right">-<x-money :value="$totals->tools_cost" /></td>
                <td class="px-4 py-3 text-right"><x-money :value="$totals->profit" /></td>
                <td class="px-4 py-3 text-right">{{ $totals->margin }}%</td>
                <td class="px-4 py-3"></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <x-card title="Monthly trend (revenue vs cost)" icon="chart-bar">
        <div class="money-chart"><canvas id="trendChart" height="120"></canvas></div>
    </x-card>
    <x-card title="Team cost this month" icon="users">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-gray-500 uppercase"><tr><th class="py-2">Member</th><th class="py-2 text-right">Hours</th><th class="py-2 text-right">Cost</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($teamMembers as $member)
                    @php
                        $entries = $member->timeEntries->filter(fn ($t) => $t->started_at && $t->started_at->between($month->copy()->startOfMonth(), $month->copy()->endOfMonth()));
                        $hours = round($entries->sum('duration_minutes') / 60, 1);
                        $cost = round($hours * (float) $member->hourly_cost, 2);
                    @endphp
                    <tr>
                        <td class="py-2">{{ $member->name }}</td>
                        <td class="py-2 text-right">{{ $hours }}h</td>
                        <td class="py-2 text-right"><x-money :value="$cost" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($trend->keys()),
        datasets: [
            { label: 'Revenue', data: @json($trend->pluck('revenue')), borderColor: '#10b981', tension: 0.3 },
            { label: 'Cost', data: @json($trend->pluck('cost')), borderColor: '#ef4444', tension: 0.3 },
            { label: 'Profit', data: @json($trend->pluck('profit')), borderColor: '#6366f1', tension: 0.3 }
        ]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
