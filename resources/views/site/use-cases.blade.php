@extends('layouts.site')
@section('title', 'Use cases')
@php
    $seo = [
        'title' => 'Use cases — Task365',
        'description' => 'Task365 works for digital marketing agencies, creative studios, web developers, consultants, SaaS agencies and freelancers.',
        'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => 'Task365 use cases', 'url' => url()->current()]],
    ];
@endphp
@section('content')
<header class="max-w-4xl mx-auto px-6 pt-16 pb-10 text-center">
    <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Built for every kind of agency</h1>
    <p class="text-gray-500 mt-4 text-lg">From marketing studios to solo consultants — one operating system.</p>
</header>

<section class="max-w-7xl mx-auto px-6 pb-20 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach ($cases as $slug => $case)
        <a href="{{ route('site.use-case', $slug) }}" class="group rounded-2xl border border-gray-100 p-7 hover:shadow-lg hover:border-indigo-100 transition">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:bg-purple-600 group-hover:text-white transition">
                <x-icon :name="$case['icon']" class="w-5 h-5" />
            </div>
            <h2 class="font-bold">{{ $case['title'] }}</h2>
            <p class="text-sm text-gray-500 mt-1.5">{{ $case['blurb'] }}</p>
            <span class="inline-flex items-center gap-1 text-sm text-indigo-600 mt-4 font-medium">Learn more
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        </a>
    @endforeach
</section>
@endsection
