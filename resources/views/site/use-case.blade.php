@extends('layouts.site')
@section('title', $case['title'])
@php
    $seo = ['title' => $case['title'].' — Agency OS', 'description' => $case['blurb']];
@endphp
@section('content')
<header class="bg-gradient-to-b from-purple-50/70 to-white">
    <div class="max-w-4xl mx-auto px-6 pt-16 pb-12 text-center">
        <div class="w-14 h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center mx-auto mb-5"><x-icon :name="$case['icon']" class="w-7 h-7" /></div>
        <h1 class="text-4xl font-black tracking-tight">{{ $case['title'] }}</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">{{ $case['blurb'] }}</p>
        <div class="mt-7 flex items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-7 py-3 rounded-xl font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200">Start free trial</a>
            <a href="{{ route('contact') }}" class="px-7 py-3 rounded-xl border border-gray-300 font-semibold hover:bg-gray-50">Talk to us</a>
        </div>
    </div>
</header>
<section class="max-w-4xl mx-auto px-6 pb-20">
    <h2 class="text-2xl font-black mb-6">How {{ $case['title'] }} win with Agency OS</h2>
    <div class="space-y-4">
        @foreach ([
            'Run the whole agency from one dashboard — clients, projects, tasks, invoices and reports.',
            'Give every client a branded portal for approvals, requests, reports and invoices.',
            'Automate follow-ups: overdue tasks, meta leads, invoice reminders, contract renewals.',
            'Know your real margins per client with team cost and tools cost built in.',
        ] as $point)
            <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-4">
                <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <span class="text-sm text-gray-700 font-medium">{{ $point }}</span>
            </div>
        @endforeach
    </div>
</section>
@endsection
