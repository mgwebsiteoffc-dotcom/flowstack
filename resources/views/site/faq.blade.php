@extends('layouts.site')
@section('title', 'FAQ')
@php
    $faqs = [
        ['Is there a free trial?','Yes - every new workspace gets a full 14-day free trial with all features, no credit card required.'],
        ['Can I import my existing clients and tasks?','Yes. You can add clients, projects and tasks manually, and leads flow in automatically from Lead365.'],
        ['Does it work with BikriBook and Razorpay?','Yes - invoices sync to BikriBook, and plan payments are handled securely via Razorpay.'],
        ['Is my data secure?','Each agency gets fully isolated data with role-based access, encrypted API keys and audited activity logs.'],
        ['Can clients log in?','Yes - the client portal lets your clients approve deliverables, view reports, download invoices and submit requests.'],
        ['What happens when my trial ends?','Your workspace stays for 30 days while you decide. If you upgrade, nothing is lost.'],
        ['Do you offer onboarding help?','Yes - every plan includes setup guidance and our team is available to help you get started.'],
        ['Can I switch plans later?','Anytime. Upgrade or downgrade from Settings → Subscription; the price adjusts to your billing cycle.'],
    ];
    $seo = [
        'title' => 'FAQ — Task365',
        'description' => 'Answers about trials, data security, integrations, client portals and pricing.',
        'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs)]],
    ];
@endphp
@section('content')

<header class="bg-gradient-to-b from-indigo-50/60 to-white">
    <div class="max-w-7xl mx-auto px-6 pt-16 pb-12 text-center">
        <div class="inline-flex items-center gap-2 bg-white border border-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm mb-6">
            <span class="w-2 h-2 rounded-full bg-green-500"></span> Everything you need to know
        </div>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Frequently asked questions</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">Answers about trials, billing, security, integrations and the client portal.</p>
        <div class="mt-8 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200">Start free trial</a>
            <a href="{{ route('contact') }}" class="px-7 py-3.5 rounded-xl border border-gray-300 font-semibold hover:bg-gray-50">Ask a question</a>
        </div>
    </div>
</header>

<!-- FAQ accordion -->
<section class="max-w-3xl mx-auto px-6 py-16">
    <div x-data="{ open: 0 }" class="space-y-3">
        @foreach ($faqs as $faq)
            <div class="rounded-xl border border-gray-100 overflow-hidden hover:border-indigo-100 transition">
                <button @click="open = open === {{ $loop->index + 1 }} ? 0 : {{ $loop->index + 1 }}" class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-sm">
                    {{ $faq[0] }}
                    <span class="text-indigo-500" x-show="open !== {{ $loop->index + 1 }}">+</span>
                    <span class="text-indigo-500" x-show="open === {{ $loop->index + 1 }}" x-cloak>−</span>
                </button>
                <div x-show="open === {{ $loop->index + 1 }}" x-cloak class="px-5 pb-4 text-sm text-gray-500 leading-relaxed">{{ $faq[1] }}</div>
            </div>
        @endforeach
    </div>
</section>

<!-- Quick answers band -->
<section class="bg-gray-50/70 py-16">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="text-3xl font-black text-center mb-10">Quick answers</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ([
                ['clock','Setup time','15 minutes to a working workspace.'],
                ['credit-card','Pricing','From ₹2,999/mo. Free 14-day trial.'],
                ['shield-check','Security','Isolated data, encrypted keys, audit logs.'],
                ['envelope','Support','Email support within one business day.'],
            ] as [$icon,$title,$desc])
                <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3"><x-icon :name="$icon" class="w-5 h-5" /></div>
                    <h3 class="font-bold text-sm">{{ $title }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA -->
<section class="max-w-7xl mx-auto px-6 py-20">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-14 text-center text-white">
        <h2 class="text-3xl font-black">Still have questions?</h2>
        <p class="text-indigo-100 mt-2">Book a 20-minute demo or start your free trial.</p>
        <div class="mt-7 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('register') }}" class="bg-white text-indigo-700 px-8 py-3.5 rounded-xl font-bold hover:bg-indigo-50 shadow-xl">Start free trial</a>
            <a href="{{ route('contact') }}" class="border border-white/40 text-white px-8 py-3.5 rounded-xl font-semibold hover:bg-white/10">Contact us</a>
        </div>
    </div>
</section>

@endsection
