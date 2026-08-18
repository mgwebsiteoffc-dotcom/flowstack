@extends('layouts.site')
@section('title', $feature['title'])
@php
    $seo = [
        'title' => $feature['title'].' — Agency OS',
        'description' => $feature['excerpt'],
        'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $feature['title'], 'description' => $feature['excerpt'], 'url' => url()->current(), 'isPartOf' => ['@type' => 'WebSite', 'name' => config('app.name'), 'url' => url('/')]]],
    ];
@endphp
@section('content')
<header class="bg-gradient-to-b from-indigo-50/70 to-white">
    <div class="max-w-4xl mx-auto px-6 pt-16 pb-12 text-center">
        <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center mx-auto mb-5"><x-icon :name="$feature['icon']" class="w-7 h-7" /></div>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight">{{ $feature['title'] }}</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">{{ $feature['description'] }}</p>
        <div class="mt-7 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-7 py-3 rounded-xl font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200">Start free trial</a>
            <a href="{{ route('contact') }}" class="px-7 py-3 rounded-xl border border-gray-300 font-semibold hover:bg-gray-50">Book a demo</a>
        </div>
    </div>
</header>

<section class="max-w-4xl mx-auto px-6 py-16">
    <h2 class="text-2xl font-black mb-6">What you get</h2>
    <div class="grid sm:grid-cols-2 gap-4">
        @foreach ($feature['bullets'] as $bullet)
            <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-4">
                <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="text-sm text-gray-700 font-medium">{{ $bullet }}</span>
            </div>
        @endforeach
    </div>
</section>

<section class="max-w-5xl mx-auto px-6 pb-20">
    <div class="grid sm:grid-cols-3 gap-4">
        @foreach ($all as $slug => $other)
            @if ($slug !== $slug)
                <a href="{{ route('site.feature', $slug) }}" class="rounded-xl border border-gray-100 p-4 hover:border-indigo-200 transition">
                    <div class="flex items-center gap-2">
                        <x-icon :name="$other['icon']" class="w-4 h-4 text-indigo-600" />
                        <span class="text-sm font-semibold">{{ $other['title'] }}</span>
                    </div>
                    <span class="text-xs text-gray-400 mt-1 block">Explore</span>
                </a>
            @endif
        @endforeach
    </div>
</section>
@endsection
