@extends('layouts.site')
@section('title', 'FAQ')
@php
    $seo = [
        'title' => 'FAQ — Agency OS',
        'description' => 'Answers about trials, data security, integrations, client portals and pricing.',
        'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], [
            ['Is there a free trial?','Yes - every new workspace gets a full 14-day free trial with all features, no credit card required.'],
            ['Can I import my existing clients and tasks?','Yes. You can add clients, projects and tasks manually, and leads flow in automatically from Lead365.'],
            ['Does it work with BikriBook and Razorpay?','Yes - invoices sync to BikriBook, and plan payments are handled securely via Razorpay.'],
            ['Is my data secure?','Each agency gets fully isolated data with role-based access, encrypted API keys and audited activity logs.'],
            ['Can clients log in?','Yes - the client portal lets your clients approve deliverables, view reports, download invoices and submit requests.'],
        ])]],
    ];
@endphp
@section('content')
<header class="max-w-4xl mx-auto px-6 pt-16 pb-10 text-center">
    <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Frequently asked questions</h1>
    <p class="text-gray-500 mt-4 text-lg">Everything you need to know before starting.</p>
</header>

<section class="max-w-3xl mx-auto px-6 pb-20">
    <div x-data="{ open: 0 }" class="space-y-3">
        @foreach ([
            ['Is there a free trial?','Yes - every new workspace gets a full 14-day free trial with all features, no credit card required.'],
            ['Can I import my existing clients and tasks?','Yes. You can add clients, projects and tasks manually, and leads flow in automatically from Lead365.'],
            ['Does it work with BikriBook and Razorpay?','Yes - invoices sync to BikriBook, and plan payments are handled securely via Razorpay.'],
            ['Is my data secure?','Each agency gets fully isolated data with role-based access, encrypted API keys and audited activity logs.'],
            ['Can clients log in?','Yes - the client portal lets your clients approve deliverables, view reports, download invoices and submit requests.'],
            ['What happens when my trial ends?','Your workspace stays for 30 days while you upgrade. If you upgrade, nothing is lost.'],
        ] as [$q,$a])
            <div class="rounded-xl border border-gray-100 overflow-hidden">
                <button @click="open = open === {{ $loop->index + 1 }} ? 0 : {{ $loop->index + 1 }}" class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-sm">
                    {{ $q }}
                    <span x-show="open !== {{ $loop->index + 1 }}">+</span>
                    <span x-show="open === {{ $loop->index + 1 }}" x-cloak>−</span>
                </button>
                <div x-show="open === {{ $loop->index + 1 }}" x-cloak class="px-5 pb-4 text-sm text-gray-500">{{ $a }}</div>
            </div>
        @endforeach
    </div>
</section>
@endsection
