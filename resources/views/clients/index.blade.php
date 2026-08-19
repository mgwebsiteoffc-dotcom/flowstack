@extends('layouts.app')
@section('title', 'Clients')
@section('breadcrumb', 'Clients')
@section('content')
<div class="flex items-center justify-between mb-5">
    <form method="GET" class="flex flex-wrap gap-2 text-sm flex-1 max-w-3xl">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by company name…"
               class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm flex-1 min-w-[180px]">
        <select name="status" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All statuses</option>
            @foreach (['active', 'inactive', 'onboarding', 'offboarding'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="health_score" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All health</option>
            @foreach (['green', 'yellow', 'red'] as $h)
                <option value="{{ $h }}" {{ request('health_score') === $h ? 'selected' : '' }}>{{ ucfirst($h) }}</option>
            @endforeach
        </select>
        <select name="account_manager_id" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All AMs</option>
            @foreach ($accountManagers as $am)
                <option value="{{ $am->id }}" {{ request('account_manager_id') == $am->id ? 'selected' : '' }}>{{ $am->name }}</option>
            @endforeach
        </select>
        <select name="service" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All services</option>
            @foreach (\App\Models\ClientService::TYPES as $key => $label)
                <option value="{{ $key }}" {{ request('service') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button class="bg-gray-800 text-white px-4 py-1.5 rounded-lg">Filter</button>
    </form>
    <a href="{{ route('clients.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap ml-3">+ Add client</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3">Client</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Health</th>
                <th class="px-4 py-3">Services</th>
                <th class="px-4 py-3">Account Manager</th>
                @if (auth()->user()->canViewFinancials())<th class="px-4 py-3 text-right">Retainer</th>@endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($clients as $client)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('clients.show', $client) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $client->company_name }}</a>
                        <div class="text-xs text-gray-400">{{ $client->city }}{{ $client->city && $client->country ? ', ' : '' }}{{ $client->country }}</div>
                    </td>
                    <td class="px-4 py-3"><x-status-badge :status="$client->status" type="client" /></td>
                    <td class="px-4 py-3"><x-health-badge :score="$client->health_score" /></td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach ($client->services as $service)
                                <span class="text-[10px] bg-indigo-50 text-indigo-700 rounded-full px-2 py-0.5">{{ $service->type_label }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $client->accountManager?->name ?? '—' }}</td>
                    @if (auth()->user()->canViewFinancials())<td class="px-4 py-3 text-right font-medium">₹{{ number_format($client->monthly_retainer ?? 0) }}</td>@endif
                </tr>
            @empty
                <tr><td colspan="6">
                    <x-empty-state icon="users" title="No clients found" message="Add your first client to start managing their projects, tasks and invoices." :action="route('clients.create')" actionLabel="Add client" />
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-pagination :paginator="$clients" />
@endsection
