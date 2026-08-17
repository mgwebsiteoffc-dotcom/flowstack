@extends('layouts.app')
@section('title', 'Subscription')
@section('breadcrumb', 'Settings / Subscription')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'subscription'])</div>
    <div class="lg:col-span-3 space-y-6">
        <div class="grid sm:grid-cols-3 gap-4">
            <x-stat-card title="Users used" value="{{ $usage['users'] }} / {{ $tenant->max_users ?? '∞' }}" icon="👥" color="indigo" />
            <x-stat-card title="Clients used" value="{{ $usage['clients'] }} / {{ $tenant->max_clients ?? '∞' }}" icon="🤝" color="blue" />
            <x-stat-card title="Storage" value="{{ $usage['storage_gb'] }} GB" icon="💾" color="purple" />
        </div>

        <x-card title="Current plan" icon="💳">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="font-bold text-gray-900 text-lg">{{ $tenant->plan?->name ?? ($tenant->is_trial ? 'Free Trial' : '—') }}</div>
                    <div class="text-sm text-gray-500">
                        @if ($tenant->is_trial)
                            Trial ends {{ $tenant->trial_ends_at?->format('d M Y') }} ({{ $tenant->trialDaysRemaining() }} days left)
                        @else
                            {{ $tenant->plan?->price_monthly ? '₹'.number_format($tenant->plan->price_monthly).'/month' : '' }} · expires {{ $tenant->plan_expires_at?->format('d M Y') }}
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('upgrade') }}" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium">
                        {{ $tenant->is_trial ? 'Upgrade now' : 'Change plan' }}
                    </a>
                    @if ($subscriptions->contains('status', 'active'))
                        <form method="POST" action="{{ route('subscription.cancel') }}" onsubmit="return confirm('Cancel your subscription? Access continues until the end of the paid period.')">
                            @csrf
                            <button class="px-5 py-2 rounded-lg bg-white border border-red-200 text-red-600 text-sm">Cancel subscription</button>
                        </form>
                    @endif
                </div>
            </div>
        </x-card>

        <x-card title="Billing history" icon="🧾" :padding="false">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Plan</th><th class="px-4 py-3">Cycle</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($subscriptions as $subscription)
                        <tr>
                            <td class="px-4 py-3 text-xs">{{ $subscription->started_at?->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $subscription->plan?->name ?? 'Trial' }}</td>
                            <td class="px-4 py-3 capitalize">{{ $subscription->billing_cycle }}</td>
                            <td class="px-4 py-3 text-right">₹{{ number_format($subscription->amount) }}</td>
                            <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 capitalize">{{ $subscription->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-sm text-gray-400">No subscriptions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>

        <x-card title="Payments" icon="💵" :padding="false">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-3">Paid at</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Gateway</th><th class="px-4 py-3">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-4 py-3 text-xs">{{ $payment->paid_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-right">₹{{ number_format($payment->amount) }}</td>
                            <td class="px-4 py-3 text-xs">{{ $payment->gateway_payment_id }}</td>
                            <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 capitalize">{{ $payment->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-sm text-gray-400">No payments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>
</div>
@endsection
