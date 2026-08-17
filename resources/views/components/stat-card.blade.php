@props(['title', 'value', 'change' => null, 'color' => 'indigo', 'icon' => ''])
@php
    $colors = [
        'indigo' => 'bg-indigo-50 text-indigo-700',
        'green' => 'bg-green-50 text-green-700',
        'red' => 'bg-red-50 text-red-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'blue' => 'bg-blue-50 text-blue-700',
        'purple' => 'bg-purple-50 text-purple-700',
    ][$color] ?? 'bg-gray-50 text-gray-700';
@endphp
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-start justify-between">
        <div>
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $title }}</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ $value }}</div>
            @if ($change !== null)
                <div class="text-xs mt-1 {{ str_starts_with((string) $change, '-') ? 'text-red-600' : 'text-green-600' }}">{{ $change }}</div>
            @endif
        </div>
        @if ($icon)
            <div class="w-10 h-10 rounded-lg {{ $colors }} flex items-center justify-center text-xl">{{ $icon }}</div>
        @endif
    </div>
</div>
