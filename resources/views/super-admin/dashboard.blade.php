@extends('layouts.super-admin')
@section('title', 'Dashboard')
@section('content')
<h1 class="text-xl font-bold text-white mb-6">Platform overview</h1>
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    @foreach ([
        ['Total tenants', $stats['total_tenants'], 'building-office'],
        ['Active', $stats['active_tenants'], 'check-circle'],
        ['Trials', $stats['trials'], 'hourglass'],
        ['MRR', '₹'.number_format($stats['mrr']), 'banknotes'],
        ['New signups (month)', $stats['new_signups_month'], 'rocket-launch'],
        ['Churn (month)', $stats['churn_month'], 'arrow-trending-down'],
    ] as [$label, $value, $icon])
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <div class="mb-2"><x-icon :name="$icon" class="w-7 h-7 text-gray-400" /></div>
            <div class="text-xl font-bold text-white">{{ $value }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $label }}</div>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <h3 class="font-semibold text-white mb-4">Recent tenants</h3>
        <div class="divide-y divide-gray-800">
            @foreach ($tenants as $tenant)
                <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="flex items-center gap-3 py-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($tenant->name, 0, 1)) }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-gray-200 truncate">{{ $tenant->name }}</div>
                        <div class="text-xs text-gray-500">{{ $tenant->slug }}.{{ config('tenancy.tenant_domain') }} · {{ $tenant->plan?->name ?? 'Trial' }}</div>
                    </div>
                    <span class="text-xs {{ $tenant->is_active ? 'text-green-400' : 'text-red-400' }}">{{ $tenant->is_active ? 'Active' : 'Inactive' }}</span>
                </a>
            @endforeach
        </div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <h3 class="font-semibold text-white mb-4">Recent payments</h3>
        <div class="divide-y divide-gray-800">
            @foreach ($recentPayments as $payment)
                <div class="flex items-center gap-3 py-2.5">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-gray-200 truncate">{{ $payment->tenant?->name }}</div>
                        <div class="text-xs text-gray-500">{{ $payment->paid_at?->format('d M Y H:i') }}</div>
                    </div>
                    <div class="text-sm font-bold text-green-400">₹{{ number_format($payment->amount) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
