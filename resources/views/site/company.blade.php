@extends('layouts.site')
@section('title', 'About us')
@php
    $seo = ['title' => 'About Agency OS', 'description' => 'We build the operating system that helps modern agencies run clients, projects, leads, invoices and reports from one place.'];
@endphp
@section('content')
<header class="max-w-4xl mx-auto px-6 pt-16 pb-10 text-center">
    <h1 class="text-4xl sm:text-5xl font-black tracking-tight">We exist to end agency chaos</h1>
    <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">Agencies run on spreadsheets, WhatsApp and a dozen disconnected tools. We built one operating system to replace them all.</p>
</header>

<section class="max-w-5xl mx-auto px-6 pb-20 grid md:grid-cols-3 gap-6">
    @foreach ([
        ['flag','Our mission','Give every agency a single source of truth for clients, work, money and communication.'],
        ['eye','Our focus','Obsessive attention to the real workflow of agencies: onboarding, delivery, approvals, billing.'],
        ['users','Our team','Operators and builders who have run agencies and know what "smooth" actually means.'],
    ] as [$icon,$title,$desc])
        <div class="rounded-2xl border border-gray-100 p-7 text-center">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4"><x-icon :name="$icon" class="w-6 h-6" /></div>
            <h3 class="font-bold">{{ $title }}</h3>
            <p class="text-sm text-gray-500 mt-1.5">{{ $desc }}</p>
        </div>
    @endforeach
</section>
@endsection
