@extends('layouts.super-admin')
@section('title', 'Tenants')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-white">Tenants</h1>
    <form method="GET" class="flex gap-2 text-sm">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search…" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500">
        <select name="status" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
            <option value="">All status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <select name="plan_id" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
            <option value="">All plans</option>
            @foreach ($plans as $plan)
                <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
            @endforeach
        </select>
        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg">Filter</button>
    </form>
</div>

<div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-950 text-left text-xs text-gray-500 uppercase">
            <tr><th class="px-4 py-3">Tenant</th><th class="px-4 py-3">Plan</th><th class="px-4 py-3 text-right">Users</th><th class="px-4 py-3 text-right">Clients</th><th class="px-4 py-3">Trial</th><th class="px-4 py-3">Status</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse ($tenants as $tenant)
                <tr class="hover:bg-gray-800/50">
                    <td class="px-4 py-3">
                        <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="text-gray-200 font-medium hover:text-indigo-400">{{ $tenant->name }}</a>
                        <div class="text-xs text-gray-500">{{ $tenant->slug }}.{{ config('tenancy.tenant_domain') }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-300">{{ $tenant->plan?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-gray-300">{{ $tenant->users_count }}</td>
                    <td class="px-4 py-3 text-right text-gray-300">{{ $tenant->clients_count }}</td>
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $tenant->is_trial ? ($tenant->trial_ends_at?->format('d M') ?? '—') : 'No' }}</td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full {{ $tenant->is_active ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">{{ $tenant->is_active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-10 text-center text-gray-500">No tenants found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $tenants->links() }}</div>
@endsection
