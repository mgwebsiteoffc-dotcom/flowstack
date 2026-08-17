@extends('layouts.app')
@section('title', 'Reports')
@section('breadcrumb', 'Reports')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <form method="GET" class="flex flex-wrap gap-2 text-sm">
        <select name="client_id" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All clients</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All statuses</option>
            @foreach (['draft', 'final', 'shared'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="bg-gray-800 text-white px-4 py-1.5 rounded-lg">Filter</button>
    </form>
    <a href="{{ route('reports.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New report</a>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($reports as $report)
        <a href="{{ route('reports.show', $report) }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 transition">
            <div class="flex items-start justify-between gap-2">
                <h3 class="font-semibold text-gray-900">{{ $report->title }}</h3>
                <x-status-badge :status="$report->status" type="invoice" />
            </div>
            <div class="text-xs text-gray-400 mt-1">{{ $report->client?->company_name }} · {{ ucfirst($report->report_type) }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $report->period_start->format('d M') }} – {{ $report->period_end->format('d M') }}</div>
            <div class="text-xs text-gray-400 mt-1">By {{ $report->creator?->name }}</div>
        </a>
    @empty
        <div class="sm:col-span-3"><x-empty-state icon="📈" title="No reports" message="Build your first client report." :action="route('reports.create')" actionLabel="New report" /></div>
    @endforelse
</div>
<x-pagination :paginator="$reports" />
@endsection
