@extends('layouts.site')
@section('title', 'About us')
@php
    $seo = ['title' => 'About Task365', 'description' => 'We build the operating system that helps modern agencies run clients, projects, leads, invoices and reports from one place.'];
@endphp
@section('content')

<header class="bg-gradient-to-b from-indigo-50/60 to-white">
    <div class="max-w-7xl mx-auto px-6 pt-16 pb-12 text-center">
        <div class="inline-flex items-center gap-2 bg-white border border-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm mb-6">
            <span class="w-2 h-2 rounded-full bg-green-500"></span> Built by operators, for operators
        </div>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight">We exist to end agency chaos</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">Agencies run on spreadsheets, WhatsApp and a dozen disconnected tools. We built one operating system to replace them all.</p>
        <div class="mt-8 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200">Start free trial</a>
            <a href="{{ route('contact') }}" class="px-7 py-3.5 rounded-xl border border-gray-300 font-semibold hover:bg-gray-50">Talk to us</a>
        </div>
    </div>
</header>

<!-- Mission cards -->
<section class="max-w-6xl mx-auto px-6 py-16 grid md:grid-cols-3 gap-6">
    @foreach ([
        ['flag','Our mission','Give every agency a single source of truth for clients, work, money and communication.'],
        ['eye','Our focus','Obsessive attention to the real workflow of agencies: onboarding, delivery, approvals, billing.'],
        ['users','Our team','Operators and builders who have run agencies and know what "smooth" actually means.'],
    ] as [$icon,$title,$desc])
        <div class="rounded-2xl border border-gray-100 p-7 text-center hover:shadow-lg transition">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4"><x-icon :name="$icon" class="w-6 h-6" /></div>
            <h3 class="font-bold">{{ $title }}</h3>
            <p class="text-sm text-gray-500 mt-1.5">{{ $desc }}</p>
        </div>
    @endforeach
</section>

<!-- Story / values (alternating) -->
<section class="bg-gray-50/70 py-16">
    <div class="max-w-6xl mx-auto px-6 space-y-12">
        @foreach ([
            ['The problem','Every agency we talked to ran the same way: a client spreadsheet, a task list in chat, invoices sent by email, reports built by hand. Status meetings ate Fridays. Nobody had one source of truth.'],
            ['The idea','What if a single workspace held the client, their contract, their projects, their tasks, their invoices and their portal? What if the pipeline fed the projects and the projects fed the invoices?'],
            ['The build','We took the best of CRMs, project tools and invoicing apps and rebuilt them specifically for agencies - with onboarding checklists, health scores, retainers and client portals as first-class citizens.'],
        ] as [$title,$text])
            <div class="grid lg:grid-cols-3 gap-6 items-center">
                <div class="lg:col-span-1">
                    <div class="text-xs font-semibold text-indigo-600 uppercase tracking-wide">Our story</div>
                    <h2 class="text-2xl font-black mt-1">{{ $title }}</h2>
                </div>
                <div class="lg:col-span-2">
                    <p class="text-gray-600 leading-relaxed">{{ $text }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Stats -->
<section class="max-w-6xl mx-auto px-6 py-16">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        @foreach ([['100+','Agencies running'],['120,000+','Tasks delivered'],['21+','Integrations & tools'],['98%','Client retention']] as [$num,$label])
            <div>
                <div class="text-4xl font-black text-indigo-600">{{ $num }}</div>
                <div class="text-gray-400 mt-1 text-sm">{{ $label }}</div>
            </div>
        @endforeach
    </div>
</section>

<!-- Values -->
<section class="max-w-6xl mx-auto px-6 pb-16">
    <h2 class="text-3xl font-black text-center mb-10">What we value</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach ([
            ['bolt','Speed','Setup in 15 minutes. Reports in minutes. No busywork.'],
            ['shield-check','Security','Isolated data, encrypted credentials, audit logs.'],
            ['users','Simplicity','If it needs a manual, we redesign it.'],
            ['hand-thumb-up','Honesty','Transparent pricing, no lock-in, cancel anytime.'],
        ] as [$icon,$title,$desc])
            <div class="rounded-2xl border border-gray-100 p-6">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3"><x-icon :name="$icon" class="w-5 h-5" /></div>
                <h3 class="font-bold text-sm">{{ $title }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $desc }}</p>
            </div>
        @endforeach
    </div>
</section>

<!-- CTA -->
<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-14 text-center text-white">
        <h2 class="text-3xl font-black">Come run your agency on Task365</h2>
        <p class="text-indigo-100 mt-2">Start free for 14 days - no credit card required.</p>
        <a href="{{ route('register') }}" class="inline-block mt-7 bg-white text-indigo-700 px-8 py-3.5 rounded-xl font-bold hover:bg-indigo-50 shadow-xl">Start free trial</a>
    </div>
</section>

@endsection
