@extends('layouts.site')
@section('title', $tool['title'])
@php
    $seo = [
        'title' => $tool['seo_title'],
        'description' => $tool['meta_description'],
        'jsonLd' => array_values(array_filter([
            ['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $tool['title'], 'description' => $tool['meta_description'], 'url' => url()->current()],
            ! empty($tool['howto']) ? ['@context' => 'https://schema.org', '@type' => 'HowTo', 'name' => $tool['title'], 'step' => array_map(fn ($s, $i) => ['@type' => 'HowToStep', 'position' => $i + 1, 'text' => $s], $tool['howto'], array_keys($tool['howto']))] : null,
            ! empty($tool['faq']) ? ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $tool['faq'])] : null,
        ])),
    ];
@endphp
@section('content')

<!-- Hero -->
<header class="bg-gradient-to-b from-indigo-50/60 to-white">
    <div class="max-w-4xl mx-auto px-6 pt-14 pb-10 text-center">
        <div class="inline-flex items-center gap-2 bg-white border border-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm mb-6">
            <span class="w-2 h-2 rounded-full bg-green-500"></span> 100% Free · No signup required
        </div>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight">{{ $tool['h1'] ?? $tool['title'] }}</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto leading-relaxed">{{ $tool['sub'] }}</p>
        <div class="mt-6 flex items-center justify-center gap-2 flex-wrap">
            @foreach ($tool['keywords'] ?? [] as $kw)
                <span class="text-xs bg-white border border-gray-200 text-gray-500 px-3 py-1 rounded-full">{{ $kw }}</span>
            @endforeach
        </div>
    </div>
</header>

<!-- Tool -->
<section class="max-w-3xl mx-auto px-6 pb-12">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-lg shadow-gray-100 p-8">
        @yield('content')
    </div>
</section>

<!-- How-to -->
@if (! empty($tool['howto']))
    <section class="max-w-3xl mx-auto px-6 pb-12">
        <h2 class="text-2xl font-black mb-6">How to use the {{ $tool['title'] }}</h2>
        <ol class="space-y-4">
            @foreach ($tool['howto'] as $i => $step)
                <li class="flex items-start gap-4 bg-gray-50 rounded-2xl p-5">
                    <span class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-sm shrink-0">{{ $i + 1 }}</span>
                    <span class="text-gray-700 font-medium leading-relaxed">{{ $step }}</span>
                </li>
            @endforeach
        </ol>
    </section>
@endif

<!-- Body content (SEO copy) -->
@if (! empty($tool['content']))
    <section class="max-w-3xl mx-auto px-6 pb-12">
        {!! $tool['content'] !!}
    </section>
@endif

<!-- FAQ -->
@if (! empty($tool['faq']))
    <section class="max-w-3xl mx-auto px-6 pb-12">
        <h2 class="text-3xl font-black text-center mb-10">{{ $tool['title'] }} — FAQ</h2>
        <div x-data="{ open: 0 }" class="space-y-3">
            @foreach ($tool['faq'] as $faqItem)
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
@endif

<!-- Other tools -->
<section class="max-w-6xl mx-auto px-6 pb-12">
    <h2 class="text-2xl font-black mb-6 text-center">More free tools</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ([
            ['tools.retainer','banknotes','Retainer Calculator','Price monthly retainers from hours, cost and margin.'],
            ['tools.invoice-due','calendar','Invoice Due Date & GST','Due dates, GST totals and overdue status.'],
            ['tools.proposal-value','gem','Proposal Value Calculator','Price projects from services and hours.'],
            ['tools.lead-value','target','Lead Value Calculator','Estimate what a lead is worth to your agency.'],
            ['tools.agency-margin','chart-bar','Agency Margin Calculator','Work out margin from revenue and costs.'],
            ['tools.project-quote','document-text','Project Quote Generator','Generate a quick client quote.'],
            ['tools.profit-margin','banknotes','Profit Margin Calculator','Profit and margin from cost and price.'],
        ] as [$route,$icon,$name,$desc])
            <a href="{{ route($route) }}" class="rounded-2xl border border-gray-100 p-5 hover:border-indigo-200 hover:shadow-lg transition">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center"><x-icon :name="$icon" class="w-4 h-4" /></div>
                    <span class="font-bold text-sm">{{ $name }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-1.5">{{ $desc }}</p>
            </a>
        @endforeach
    </div>
</section>

<!-- CTA -->
<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-12 text-center text-white">
        <h2 class="text-2xl font-black">Loved this tool? Run your whole agency free for 14 days.</h2>
        <p class="text-indigo-100 mt-2">Clients, tasks, invoices, reporting and a client portal — all included.</p>
        <a href="{{ route('register') }}" class="inline-block mt-5 bg-white text-indigo-700 px-7 py-3 rounded-xl font-bold hover:bg-indigo-50">Start free trial</a>
    </div>
</section>

@endsection
