@extends('layouts.app')
@section('title', 'Leads')
@section('breadcrumb', 'Leads')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex gap-2 text-sm">
        <a href="{{ route('leads.pipeline') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Pipeline</a>
        <a href="{{ route('leads.index') }}" class="px-3 py-1.5 rounded-lg bg-gray-900 text-white">List</a>
        <a href="{{ route('leads.analytics') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Analytics</a>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('leads.export') }}" class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 text-sm"><x-icon name="arrow-down-tray" class="w-4 h-4 inline-block" /> Export</a>
        <a href="{{ route('leads.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New lead</a>
    </div>
</div>

<form method="GET" class="flex flex-wrap gap-2 text-sm mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search leads…" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm flex-1 min-w-[160px]">
    <select name="status" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
        <option value="">All statuses</option>
        @foreach (\App\Models\Lead::STATUSES as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <select name="source_type" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
        <option value="">All sources</option>
        @foreach (\App\Models\Lead::SOURCE_TYPES as $s)
            <option value="{{ $s }}" {{ request('source_type') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
        @endforeach
    </select>
    <select name="assigned_to" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
        <option value="">Anyone</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
        @endforeach
    </select>
    <button class="bg-gray-800 text-white px-4 py-1.5 rounded-lg">Filter</button>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
            <tr><th class="px-4 py-3">Lead</th><th class="px-4 py-3">Source</th><th class="px-4 py-3">Stage</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Value</th><th class="px-4 py-3">Assignee</th><th class="px-4 py-3">Created</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($leads as $lead)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('leads.show', $lead) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $lead->contact_name }}</a>
                        <div class="text-xs text-gray-400">{{ $lead->company_name }}</div>
                    </td>
                    <td class="px-4 py-3"><x-source-badge :source="$lead->source_type" /></td>
                    <td class="px-4 py-3 text-gray-600">{{ $lead->current_stage ?? '—' }}</td>
                    <td class="px-4 py-3"><x-status-badge :status="$lead->status" type="lead" /></td>
                    <td class="px-4 py-3 font-medium">₹{{ number_format($lead->estimated_value ?? 0) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $lead->assignee?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $lead->created_at?->format('d M') }}</td>
                </tr>
            @empty
                <tr><td colspan="7"><x-empty-state icon="target" title="No leads found" message="Create a lead manually or connect Lead365 to receive them automatically." :action="route('leads.create')" actionLabel="New lead" /></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-pagination :paginator="$leads" />
@endsection
