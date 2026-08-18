@extends('layouts.site')
@section('title', $feature['title'])
@php
    $seo = [
        'title' => $feature['title'].' — Task365',
        'description' => $feature['meta_description'] ?? $feature['excerpt'],
        'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $feature['title'], 'description' => $feature['excerpt'], 'url' => url()->current(), 'isPartOf' => ['@type' => 'WebSite', 'name' => config('app.name'), 'url' => url('/')]]],
    ];
@endphp
@section('content')

<!-- HERO -->
<header class="relative overflow-hidden bg-gradient-to-b from-indigo-50/60 to-white">
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl"></div>
    <div class="max-w-4xl mx-auto px-6 pt-16 pb-14 text-center relative">
        <div class="inline-flex items-center gap-2 bg-white border border-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm mb-6">
            <span class="w-2 h-2 rounded-full bg-green-500"></span> {{ $feature['badge'] ?? 'Task365 Feature' }}
        </div>
        <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center mx-auto mb-6 shadow-lg shadow-indigo-200"><x-icon :name="$feature['icon']" class="w-7 h-7" /></div>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight leading-tight">{{ $feature['title'] }}</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto leading-relaxed">{{ $feature['description'] }}</p>
        <div class="mt-8 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200">Start free trial</a>
            <a href="{{ route('contact') }}" class="px-7 py-3.5 rounded-xl border border-gray-300 font-semibold hover:bg-gray-50">Book a demo</a>
        </div>
        <div class="mt-5 flex items-center justify-center gap-5 text-xs text-gray-400 flex-wrap">
            <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg> Free 14-day trial</span>
            <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg> 15-min setup</span>
            <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg> No credit card</span>
        </div>
    </div>
</header>

<!-- KEY BENEFITS (animated) -->
<section class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-center mb-10">
        <h2 class="text-3xl sm:text-4xl font-black">Why agencies choose {{ $feature['title'] }}</h2>
        <p class="text-gray-500 mt-2">Everything you need to run this part of your agency — without the busywork.</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($feature['bullets'] as $bullet)
            <div class="group rounded-2xl border border-gray-100 p-6 hover:shadow-xl hover:border-indigo-100 hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center mb-4 group-hover:bg-green-600 group-hover:text-white transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="font-bold text-sm">{{ $bullet }}</h3>
            </div>
        @endforeach
    </div>
</section>

<!-- STATS BAND -->
@if (! empty($feature['stats']))
    <section class="bg-indigo-600 py-14">
        <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-3 gap-8 text-center text-white">
            @foreach ($feature['stats'] as $stat)
                <div class="opacity-90">
                    <div class="text-3xl font-black">{{ $stat }}</div>
                </div>
            @endforeach
        </div>
    </section>
@endif

<!-- SCREENSHOT / USE-CASE SECTION -->
<section class="max-w-7xl mx-auto px-6 py-16 grid lg:grid-cols-2 gap-12 items-center">
    <div>
        <h2 class="text-2xl sm:text-3xl font-black tracking-tight">{{ $feature['section_title'] ?? 'How it works in practice' }}</h2>
        <p class="text-gray-500 mt-3 leading-relaxed">{{ $feature['section_text'] ?? $feature['description'] }}</p>
        <div class="mt-6 space-y-3">
            @foreach (($feature['section_points'] ?? array_slice($feature['bullets'], 0, 3)) as $point)
                <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-4">
                    <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    <span class="text-sm text-gray-700 font-medium">{{ $point }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-gray-50 to-white p-6 shadow-lg shadow-gray-100">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
            <span class="ml-2 text-[10px] text-gray-400">{{ $feature['title'] }} · Task365</span>
        </div>
        <div class="space-y-2.5">
            @foreach (array_slice($feature['bullets'], 0, 4) as $i => $b)
                <div class="flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2.5 bg-white">
                    <span class="w-2 h-2 rounded-full {{ $i % 3 === 0 ? 'bg-indigo-500' : ($i % 3 === 1 ? 'bg-green-500' : 'bg-amber-400') }}"></span>
                    <span class="text-xs text-gray-700">{{ $b }}</span>
                </div>
            @endforeach
            <div class="rounded-lg border border-gray-100 px-3 py-2.5 bg-white flex items-center gap-2">
                <div class="flex-1 space-y-1.5">
                    <div class="h-1.5 bg-gray-100 rounded-full w-full"></div>
                    <div class="h-1.5 bg-gray-100 rounded-full w-4/5"></div>
                </div>
                <span class="text-[10px] text-green-600 font-semibold">Live</span>
            </div>
        </div>
    </div>
</section>

<!-- INTEGRATIONS STRIP -->
<section class="bg-gray-50/70 py-14">
    <div class="max-w-6xl mx-auto px-6 text-center">
        <h2 class="text-2xl font-black mb-2">Works with your stack</h2>
        <p class="text-gray-500 mb-8 text-sm">Lead365 · BikriBook · Razorpay · Slack · Teams · Google Calendar · Meet · Meta Ads</p>
        <div class="flex flex-wrap justify-center gap-3">
            @foreach (['link','receipt','credit-card','chat-bubble-left-right','users','calendar','video-camera','device-phone-mobile'] as $icon)
                <span class="w-12 h-12 rounded-xl bg-white border border-gray-100 text-indigo-600 flex items-center justify-center hover:shadow-md transition"><x-icon :name="$icon" class="w-5 h-5" /></span>
            @endforeach
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="max-w-3xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-black text-center mb-10">Frequently asked questions</h2>
    <div x-data="{ open: 0 }" class="space-y-3">
        @foreach ($feature['faq'] as $faqItem)
            <div class="rounded-xl border border-gray-100 overflow-hidden">
                <button @click="open = open === {{ $loop->index + 1 }} ? 0 : {{ $loop->index + 1 }}" class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-sm">
                    {{ $faqItem[0] }}
                    <span class="text-indigo-500" x-show="open !== {{ $loop->index + 1 }}">+</span>
                    <span class="text-indigo-500" x-show="open === {{ $loop->index + 1 }}" x-cloak>−</span>
                </button>
                <div x-show="open === {{ $loop->index + 1 }}" x-cloak class="px-5 pb-4 text-sm text-gray-500 leading-relaxed">{{ $faqItem[1] }}</div>
            </div>
        @endforeach
    </div>
</section>

<!-- RELATED FEATURES -->
<section class="max-w-6xl mx-auto px-6 pb-16">
    <h2 class="text-2xl font-black mb-8 text-center">Explore more features</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($all as $fslug => $other)
            @if ($fslug !== $current)
                <a href="{{ route('site.feature', $fslug) }}" class="rounded-2xl border border-gray-100 p-5 hover:border-indigo-200 hover:shadow-lg transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition"><x-icon :name="$other['icon']" class="w-5 h-5" /></div>
                        <span class="font-bold text-sm">{{ $other['title'] }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ $other['excerpt'] }}</p>
                </a>
            @endif
        @endforeach
    </div>
</section>

<!-- CTA -->
<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-14 text-center text-white">
        <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/10 rounded-full blur-2xl"></div>
        <h2 class="text-3xl font-black relative">Try {{ $feature['title'] }} free for 14 days</h2>
        <p class="text-indigo-100 mt-2 relative">Every feature, every plan, no credit card required.</p>
        <a href="{{ route('register') }}" class="relative inline-block mt-7 bg-white text-indigo-700 px-8 py-3.5 rounded-xl font-bold hover:bg-indigo-50 shadow-xl">Start free trial</a>
    </div>
</section>

@endsection
