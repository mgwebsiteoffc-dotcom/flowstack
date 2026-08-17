@extends('layouts.app')
@section('title', 'Lead pipeline')
@section('breadcrumb', 'Leads / Pipeline')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex gap-2 text-sm">
        <a href="{{ route('leads.pipeline') }}" class="px-3 py-1.5 rounded-lg bg-gray-900 text-white">Pipeline</a>
        <a href="{{ route('leads.index') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">List</a>
        <a href="{{ route('leads.analytics') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Analytics</a>
    </div>
    <a href="{{ route('leads.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New lead</a>
</div>

<div class="flex gap-4 overflow-x-auto pb-4">
    @foreach ($stages as $stage)
        <div class="w-72 shrink-0 bg-gray-200/60 rounded-xl p-3">
            <div class="flex items-center justify-between px-1 mb-2">
                <span class="text-xs font-semibold text-gray-600 uppercase flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full" style="background: {{ $stage->color }}"></span>{{ $stage->name }}
                </span>
                <span class="text-xs bg-white rounded-full px-2 py-0.5 text-gray-500">{{ $stage->leads_count }}</span>
            </div>
            <div class="space-y-2 min-h-[100px]">
                @forelse ($stage->leads as $lead)
                    <a href="{{ route('leads.show', $lead) }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 block hover:border-indigo-300 transition">
                        <div class="text-sm font-medium text-gray-800">{{ $lead->contact_name }}</div>
                        <div class="text-xs text-gray-400">{{ $lead->company_name }}</div>
                        <div class="flex items-center justify-between mt-2">
                            <x-source-badge :source="$lead->source_type" />
                            <span class="text-xs font-semibold text-gray-700">₹{{ number_format($lead->estimated_value ?? 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            @if ($lead->assignee)<span class="text-[10px] text-gray-400">👤 {{ $lead->assignee->name }}</span>@endif
                            <span class="text-[10px] text-gray-400">{{ $lead->daysInStage() }}d in stage</span>
                        </div>
                    </a>
                @empty
                    <p class="text-xs text-gray-400 text-center py-4">No leads</p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
@endsection
