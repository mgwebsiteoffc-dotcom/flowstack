@extends('layouts.app')
@section('title', $report->title)
@section('breadcrumb', 'Reports / '.$report->title)
@section('content')
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ $report->title }}</h1>
        <div class="flex items-center gap-2 mt-1 text-sm text-gray-500">
            <a href="{{ route('clients.show', $report->client) }}" class="text-indigo-600">{{ $report->client?->company_name }}</a>
            <x-status-badge :status="$report->status" type="invoice" />
            <span>{{ ucfirst($report->report_type) }} · {{ $report->period_start->format('d M Y') }} – {{ $report->period_end->format('d M Y') }}</span>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.pdf', $report) }}" class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-700"><x-icon name="arrow-down-tray" class="w-4 h-4 inline-block" /> PDF</a>
        @if ($report->status !== 'shared')
            <form method="POST" action="{{ route('reports.share', $report) }}">@csrf
                <button class="px-3 py-1.5 text-sm rounded-lg bg-green-600 text-white">Share with client</button>
            </form>
        @endif
        <a href="{{ route('reports.edit', $report) }}" class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-700">Edit</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        @forelse (($report->data['sections'] ?? []) as $key => $section)
            <x-card :title="['paid_advertising' => 'chart-bar Paid Advertising', 'shopify' => 'shopping-bag Shopify', 'social_media' => 'device-phone-mobile Social Media', 'website' => 'globe-alt Website'][$key] ?? $key" :padding="false">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($section as $metric => $value)
                            <tr>
                                <td class="px-5 py-2.5 text-gray-600 capitalize">{{ str_replace('_', ' ', $metric) }}</td>
                                <td class="px-5 py-2.5 text-right font-medium">{{ number_format((float) $value, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-card>
        @empty
            <x-card title="Metrics" icon="chart-bar">
                <p class="text-sm text-gray-400 text-center py-4">No metric sections recorded.</p>
            </x-card>
        @endforelse
    </div>

    <div class="space-y-6">
        @if ($report->insights)
            <x-card title="What worked" icon="light-bulb">
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $report->insights }}</p>
            </x-card>
        @endif
        @if ($report->recommendations)
            <x-card title="Areas to improve" icon="wrench">
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $report->recommendations }}</p>
            </x-card>
        @endif
        @if ($report->next_priorities)
            <x-card title="Next priorities" icon="target">
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $report->next_priorities }}</p>
            </x-card>
        @endif
        <x-card title="Info" icon="ℹ">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-400">Created by</dt><dd>{{ $report->creator?->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Created</dt><dd>{{ $report->created_at->format('d M Y') }}</dd></div>
                @if ($report->shared_at)
                    <div class="flex justify-between"><dt class="text-gray-400">Shared</dt><dd>{{ $report->shared_at->format('d M Y') }}</dd></div>
                @endif
            </dl>
        </x-card>
    </div>
</div>
@endsection
