@props(['score'])
@php
    $map = ['green' => 'bg-green-500', 'yellow' => 'bg-amber-400', 'red' => 'bg-red-500'];
    $label = ['green' => 'Healthy', 'yellow' => 'At Risk', 'red' => 'Critical'][$score] ?? $score;
@endphp
<span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700">
    <span class="w-2.5 h-2.5 rounded-full {{ $map[$score] ?? 'bg-gray-400' }}"></span>{{ $label }}
</span>
