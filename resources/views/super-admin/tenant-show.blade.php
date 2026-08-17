@extends('layouts.super-admin')
@section('title', $tenant->name)
@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <a href="{{ route('super-admin.tenants.index') }}" class="text-xs text-gray-500 hover:text-gray-300">← All tenants</a>
        <h1 class="text-xl font-bold text-white mt-1">{{ $tenant->name }}</h1>
        <p class="text-xs text-gray-500">{{ $tenant->slug }}.{{ config('tenancy.tenant_domain') }} · {{ $tenant->email }}</p>
    </div>
    <div class="flex gap-2">
        <form method="POST" action="{{ route('super-admin.tenants.extend-trial', $tenant) }}" class="flex items-center gap-1">
            @csrf
            <input type="number" name="days" value="14" min="1" max="365" class="w-16 bg-gray-900 border border-gray-700 rounded-lg px-2 py-1.5 text-xs text-white">
            <button class="text-xs bg-amber-600 text-white px-3 py-1.5 rounded-lg">Extend trial</button>
        </form>
        <form method="POST" action="{{ route('super-admin.tenants.impersonate', $tenant) }}">@csrf
            <button class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg">Impersonate →</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ([['Users', $usage['users']], ['Clients', $usage['clients']], ['Invoices', $usage['invoices']], ['Leads', $usage['leads']]] as [$label, $value])
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-4">
            <div class="text-xl font-bold text-white">{{ $value }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $label }}</div>
        </div>
    @endforeach
</div>

<form method="POST" action="{{ route('super-admin.tenants.update', $tenant) }}" class="bg-gray-900 rounded-xl border border-gray-800 p-5 space-y-4">
    @csrf
    @method('PATCH')
    <h3 class="font-semibold text-white">Edit tenant</h3>
    <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-xs text-gray-500 mb-1">Name</label>
            <input type="text" name="name" value="{{ $tenant->name }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Email</label>
            <input type="email" name="email" value="{{ $tenant->email }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Plan</label>
            <select name="plan_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
                <option value="">—</option>
                @foreach (\App\Models\Plan::all() as $plan)
                    <option value="{{ $plan->id }}" {{ $tenant->plan_id === $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select></div>
        <div><label class="block text-xs text-gray-500 mb-1">Max users</label>
            <input type="number" name="max_users" value="{{ $tenant->max_users }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Max clients</label>
            <input type="number" name="max_clients" value="{{ $tenant->max_clients }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white"></div>
        <div><label class="block text-xs text-gray-500 mb-1">Status</label>
            <select name="is_active" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
                <option value="1" {{ $tenant->is_active ? 'selected' : '' }}>Active</option>
                <option value="0" {{ ! $tenant->is_active ? 'selected' : '' }}>Inactive</option>
            </select></div>
    </div>
    <button class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm">Save</button>
</form>

<div class="grid lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <h3 class="font-semibold text-white mb-4">Subscription history</h3>
        <div class="divide-y divide-gray-800">
            @forelse ($tenant->subscriptions as $subscription)
                <div class="py-2.5 text-sm">
                    <div class="text-gray-200">{{ $subscription->plan?->name ?? 'Trial' }} · <span class="capitalize">{{ $subscription->status }}</span></div>
                    <div class="text-xs text-gray-500">{{ $subscription->started_at?->format('d M Y') }} – {{ $subscription->expires_at?->format('d M Y') ?? '∞' }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500 py-3">No subscriptions.</p>
            @endforelse
        </div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <h3 class="font-semibold text-white mb-4">Payments</h3>
        <div class="divide-y divide-gray-800">
            @forelse ($tenant->subscriptionPayments as $payment)
                <div class="flex justify-between py-2.5 text-sm">
                    <span class="text-gray-300">{{ $payment->paid_at?->format('d M Y') }}</span>
                    <span class="text-green-400 font-bold">₹{{ number_format($payment->amount) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500 py-3">No payments.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
