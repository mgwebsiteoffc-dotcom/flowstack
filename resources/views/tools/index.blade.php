@extends('layouts.site')
@section('title', 'Free tools for agencies')
@php
    $seo = ['title' => 'Free agency tools — Agency OS', 'description' => 'Free calculators and generators for agencies: retainer calculator, invoice due-date calculator and proposal value calculator.'];
@endphp
@section('content')

<header class="bg-gradient-to-b from-indigo-50/60 to-white">
    <div class="max-w-7xl mx-auto px-6 pt-16 pb-12 text-center">
        <div class="inline-flex items-center gap-2 bg-white border border-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm mb-6">
            <span class="w-2 h-2 rounded-full bg-green-500"></span> 100% free · No signup required
        </div>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Free tools for agencies</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">Quick calculators and generators that save you time every week.</p>
    </div>
</header>

<section class="max-w-6xl mx-auto px-6 py-16 grid md:grid-cols-3 gap-6">
    @foreach ([
        ['banknotes','Retainer Calculator','Work out the right monthly retainer for a client based on team hours and margin target.','tools.retainer'],
        ['calendar','Invoice Due-Date Calculator','Calculate due dates, overdue days and GST totals for any invoice instantly.','tools.invoice-due'],
        ['gem','Proposal Value Calculator','Price a proposal from services, hours and markup — before you send it.','tools.proposal-value'],
    ] as [$icon,$title,$desc,$route])
        <a href="{{ route($route) }}" class="group rounded-2xl border border-gray-100 p-7 hover:shadow-xl hover:border-indigo-100 hover:-translate-y-1 transition-all duration-300">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-5 group-hover:bg-indigo-600 group-hover:text-white transition"><x-icon :name="$icon" class="w-6 h-6" /></div>
            <h2 class="font-bold text-lg">{{ $title }}</h2>
            <p class="text-sm text-gray-500 mt-1.5">{{ $desc }}</p>
            <span class="inline-flex items-center gap-1 text-sm text-indigo-600 mt-4 font-semibold">Open tool
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        </a>
    @endforeach
</section>

<!-- CTA -->
<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-12 text-center text-white">
        <h2 class="text-2xl font-black">Loved a tool? The full platform is free for 14 days.</h2>
        <a href="{{ route('register') }}" class="inline-block mt-5 bg-white text-indigo-700 px-7 py-3 rounded-xl font-bold hover:bg-indigo-50">Start free trial</a>
    </div>
</section>

@endsection
