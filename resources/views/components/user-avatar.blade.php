@props(['user', 'size' => 'sm'])
@php
    $sizes = ['sm' => 'w-8 h-8 text-xs', 'md' => 'w-10 h-10 text-sm', 'lg' => 'w-14 h-14 text-lg'];
    $class = $sizes[$size] ?? $sizes['sm'];
    $colors = ['bg-indigo-500', 'bg-green-500', 'bg-purple-500', 'bg-amber-500', 'bg-pink-500', 'bg-blue-500'];
    $color = $colors[($user->id ?? 0) % count($colors)];
@endphp
@if ($user && $user->avatar_url)
    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="{{ $class }} rounded-full object-cover shrink-0">
@else
    <div class="{{ $class }} {{ $color }} rounded-full flex items-center justify-center text-white font-semibold shrink-0" title="{{ $user?->name }}">
        {{ $user ? $user->initials : '?' }}
    </div>
@endif
