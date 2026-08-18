@props(['priority'])
@php
    $map = [
        'urgent' => 'bg-red-100 text-red-700',
        'high' => 'bg-orange-100 text-orange-700',
        'medium' => 'bg-amber-100 text-amber-700',
        'low' => 'bg-gray-100 text-gray-600',
    ];
    $class = $map[$priority] ?? 'bg-gray-100 text-gray-600';
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class }}">{{ ucfirst($priority) }}</span>
