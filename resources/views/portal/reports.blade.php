@extends('layouts.portal')
@section('title', 'Reports')
@section('content')
<h1 class="text-xl font-bold text-gray-900 mb-5">Reports</h1>
<div class="bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
    @forelse ($reports as $report)
        <div class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50">
            <div class="flex-1 min-w-0">
                <a href="{{ route('portal.reports.show', $report) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">{{ $report->title }}</a>
                <div class="text-xs text-gray-400 mt-0.5">{{ ucfirst($report->report_type) }} · {{ $report->period_start->format('d M Y') }} – {{ $report->period_end->format('d M Y') }}</div>
            </div>
            <a href="{{ route('portal.reports.download', $report) }}" class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg"><x-icon name="arrow-down-tray" class="w-4 h-4 inline-block" /> PDF</a>
        </div>
    @empty
        <div class="py-12 text-center text-sm text-gray-400">No reports shared with you yet.</div>
    @endforelse
</div>
<x-pagination :paginator="$reports" />
@endsection
