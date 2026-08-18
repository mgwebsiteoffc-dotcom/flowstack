@extends('layouts.site')
@section('title', $case['title'])
@php
    $seo = [
        'title' => $case['title'].' — Agency OS',
        'description' => $case['blurb'],
        'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $case['title'], 'description' => $case['blurb'], 'url' => url()->current()]],
    ];
@endphp
@section('content')

<!-- Hero -->
<header class="bg-gradient-to-b from-purple-50/70 to-white">
    <div class="max-w-4xl mx-auto px-6 pt-16 pb-12 text-center">
        <div class="w-14 h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center mx-auto mb-5"><x-icon :name="$case['icon']" class="w-7 h-7" /></div>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight">{{ $case['title'] }}</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">{{ $case['intro'] }}</p>
        <div class="mt-7 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-7 py-3 rounded-xl font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200">Start free trial</a>
            <a href="{{ route('contact') }}" class="px-7 py-3 rounded-xl border border-gray-300 font-semibold hover:bg-gray-50">Book a demo</a>
        </div>
    </div>
</header>

<!-- Challenges vs Solutions -->
<section class="max-w-6xl mx-auto px-6 py-16">
    <div class="grid md:grid-cols-2 gap-8">
        <div>
            <h2 class="text-2xl font-black mb-5 flex items-center gap-2"><span class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center"><x-icon name="x-mark" class="w-4 h-4" /></span>The old way</h2>
            <div class="space-y-3">
                @foreach ($case['challenges'] as $challenge)
                    <div class="flex items-start gap-3 bg-red-50/50 rounded-xl p-4">
                        <span class="text-red-400 mt-0.5 shrink-0"><x-icon name="x-mark" class="w-4 h-4" /></span>
                        <span class="text-sm text-gray-700 font-medium">{{ $challenge }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div>
            <h2 class="text-2xl font-black mb-5 flex items-center gap-2"><span class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center"><x-icon name="check" class="w-4 h-4" /></span>With Agency OS</h2>
            <div class="space-y-3">
                @foreach ($case['solutions'] as $solution)
                    <div class="flex items-start gap-3 bg-green-50/50 rounded-xl p-4">
                        <span class="text-green-500 mt-0.5 shrink-0"><x-icon name="check" class="w-4 h-4" /></span>
                        <span class="text-sm text-gray-700 font-medium">{{ $solution }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Features you will use -->
<section class="bg-gray-50/70 py-16">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-black">Features {{ $case['title'] }} rely on most</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($case['features'] as $fslug)
                @php $f = $all[$fslug] ?? null; @endphp
                @if ($f)
                    <a href="{{ route('site.feature', $fslug) }}" class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-lg transition group">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3 group-hover:bg-indigo-600 group-hover:text-white transition"><x-icon :name="$f['icon']" class="w-5 h-5" /></div>
                        <h3 class="font-bold text-sm">{{ $f['title'] }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ $f['excerpt'] }}</p>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="max-w-3xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-black text-center mb-10">Frequently asked questions</h2>
    <div x-data="{ open: 0 }" class="space-y-3">
        @foreach ($case['faq'] as $faqItem)
            <div class="rounded-xl border border-gray-100 overflow-hidden">
                <button @click="open = open === {{ $loop->index + 1 }} ? 0 : {{ $loop->index + 1 }}" class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-sm">
                    {{ $faqItem[0] }}
                    <span x-show="open !== {{ $loop->index + 1 }}">+</span>
                    <span x-show="open === {{ $loop->index + 1 }}" x-cloak>−</span>
                </button>
                <div x-show="open === {{ $loop->index + 1 }}" x-cloak class="px-5 pb-4 text-sm text-gray-500">{{ $faqItem[1] }}</div>
            </div>
        @endforeach
    </div>
</section>

<!-- CTA -->
<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-purple-600 to-indigo-600 px-8 py-14 text-center text-white">
        <h2 class="text-3xl font-black">See how it works for your agency</h2>
        <p class="text-purple-100 mt-2">Start your free 14-day trial and set up your workspace in minutes.</p>
        <a href="{{ route('register') }}" class="inline-block mt-7 bg-white text-indigo-700 px-8 py-3.5 rounded-xl font-bold hover:bg-indigo-50 shadow-xl">Start free trial</a>
    </div>
</section>

@endsection
