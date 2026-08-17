@props(['source'])
@php
    $map = [
        'meta_ads' => ['bg-orange-100 text-orange-700', '⚡'],
        'form_submission' => ['bg-blue-100 text-blue-700', '📝'],
        'lead365' => ['bg-purple-100 text-purple-700', '🔗'],
        'manual' => ['bg-gray-100 text-gray-600', '✍️'],
    ];
    [$class, $icon] = $map[$source] ?? ['bg-gray-100 text-gray-600', '•'];
    $label = [
        'meta_ads' => 'Meta Ads', 'form_submission' => 'Form',
        'lead365' => 'Lead365', 'manual' => 'Manual',
    ][$source] ?? $source;
@endphp
<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class }}"><span>{{ $icon }}</span>{{ $label }}</span>
