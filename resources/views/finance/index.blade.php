@extends('layouts.app')
@section('title', 'Finance')
@section('breadcrumb', 'Finance')
@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stat-card title="Revenue" :value="\App\Support\Money::format($stats['revenue'])" icon="banknotes" color="green" />
    <x-stat-card title="Outstanding" :value="\App\Support\Money::format($stats['outstanding'])" icon="hourglass" color="amber" />
    <x-stat-card title="Paid This Month" :value="\App\Support\Money::format($stats['paid_this_month'])" icon="banknotes" color="indigo" />
    <x-stat-card title="Overdue Invoices" value="{{ $stats['overdue_count'] }}" icon="exclamation-triangle" color="{{ $stats['overdue_count'] > 0 ? 'red' : 'green' }}" />
</div>

<div class="grid lg:grid-cols-3 gap-6 mt-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card title="Revenue (6 months)" icon="chart-bar">
            <div class="money-chart"><canvas id="revChart" height="90"></canvas></div>
        </x-card>

        <x-card title="Recent invoices" icon="receipt">
            <div class="divide-y divide-gray-50">
                @forelse ($invoices as $invoice)
                    <a href="{{ route('finance.invoices.show', $invoice) }}" class="flex items-center gap-3 py-2.5 hover:bg-gray-50 px-2 rounded-lg">
                        <span class="text-sm font-medium text-indigo-600">{{ $invoice->invoice_number }}</span>
                        <span class="text-sm text-gray-800 flex-1">{{ $invoice->client?->company_name }}</span>
                        <x-status-badge :status="$invoice->status" type="invoice" />
                        <x-bb-status :invoice="$invoice" />
                        <span class="text-sm font-medium"><x-money :value="$invoice->total_amount" /></span>
                    </a>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No invoices yet.</p>
                @endforelse
            </div>
            <div class="flex gap-2 mt-3">
                <a href="{{ route('finance.invoices.index') }}" class="text-xs text-indigo-600">All invoices →</a>
                <a href="{{ route('finance.invoices.create') }}" class="text-xs text-indigo-600 ml-auto">+ New invoice</a>
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card title="Quick actions" icon="bolt">
            <div class="space-y-2 text-sm">
                <a href="{{ route('finance.invoices.create') }}" class="block px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg">+ Create invoice</a>
                <a href="{{ route('finance.expenses.index') }}" class="block px-3 py-2 bg-gray-50 text-gray-700 rounded-lg">Record expense</a>
                <a href="{{ route('finance.profitability') }}" class="block px-3 py-2 bg-gray-50 text-gray-700 rounded-lg">View profitability</a>
                <a href="{{ route('finance.invoices.sync-all') }}" class="block px-3 py-2 bg-gray-50 text-gray-700 rounded-lg" onclick="event.preventDefault(); document.getElementById('sync-all-form').submit();">⟳ Sync invoice statuses</a>
                <form id="sync-all-form" method="POST" action="{{ route('finance.invoices.sync-all') }}" class="hidden">@csrf</form>
            </div>
        </x-card>
        <x-card title="Expenses this month" icon="banknotes">
            <div class="text-2xl font-bold text-gray-900"><x-money :value="$stats['expenses_this_month']" /></div>
            <a href="{{ route('finance.expenses.index') }}" class="text-xs text-indigo-600 mt-2 inline-block">View expenses →</a>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('revChart'), {
    type: 'bar',
    data: {
        labels: @json($revenueByMonth->keys()),
        datasets: [{ label: 'Revenue (₹)', data: @json($revenueByMonth->values()), backgroundColor: '#10b981', borderRadius: 6 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
