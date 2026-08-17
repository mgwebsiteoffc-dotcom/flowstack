@extends('layouts.portal')
@section('title', $report->title)
@section('content')
<a href="{{ route('portal.reports') }}" class="text-sm text-indigo-600">← All reports</a>
<div class="bg-white rounded-xl border border-gray-100 p-8 mt-3">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $report->title }}</h1>
            <p class="text-sm text-gray-400 mt-1">{{ ucfirst($report->report_type) }} · {{ $report->period_start->format('d M Y') }} – {{ $report->period_end->format('d M Y') }}</p>
        </div>
        <a href="{{ route('portal.reports.download', $report) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm"><x-icon name="arrow-down-tray" class="w-4 h-4 inline-block" /> Download PDF</a>
    </div>

    @forelse (($report->data['sections'] ?? []) as $key => $section)
        <h3 class="font-semibold text-gray-900 mt-8 mb-3">@php $sectionIcons = ['paid_advertising' => 'chart-bar', 'shopify' => 'shopping-bag', 'social_media' => 'device-phone-mobile', 'website' => 'globe-alt']; @endphp
        <h3 class="font-semibold text-gray-900 mt-8 mb-3"><x-icon :name="$sectionIcons[$key] ?? 'chart-bar'" class="w-4 h-4 inline-block" /> {{ ['paid_advertising' => 'Paid Advertising', 'shopify' => 'Shopify', 'social_media' => 'Social Media', 'website' => 'Website'][$key] ?? $key }}</h3>>
        <table class="w-full text-sm border border-gray-100 rounded-lg overflow-hidden">
            <tbody class="divide-y divide-gray-50">
                @foreach ($section as $metric => $value)
                    <tr>
                        <td class="px-4 py-2.5 text-gray-600 capitalize">{{ str_replace('_', ' ', $metric) }}</td>
                        <td class="px-4 py-2.5 text-right font-medium">{{ number_format((float) $value, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p class="text-sm text-gray-400 text-center py-8">No metrics in this report.</p>
    @endforelse

    @if ($report->insights)
        <h3 class="font-semibold text-gray-900 mt-8">What worked</h3>
        <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">{{ $report->insights }}</p>
    @endif
    @if ($report->recommendations)
        <h3 class="font-semibold text-gray-900 mt-6">Areas to improve</h3>
        <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">{{ $report->recommendations }}</p>
    @endif
    @if ($report->next_priorities)
        <h3 class="font-semibold text-gray-900 mt-6">Next priorities</h3>
        <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">{{ $report->next_priorities }}</p>
    @endif
</div>

<div class="bg-white rounded-xl border border-gray-100 p-5 mt-6">
    <h3 class="font-semibold text-gray-900 mb-3">Send a comment</h3>
    <form method="POST" action="{{ route('portal.reports.comment', $report) }}">
        @csrf
        <textarea name="comment" rows="3" placeholder="Your feedback on this report…" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required></textarea>
        <button class="mt-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Submit comment</button>
    </form>
</div>
@endsection
