@extends('layouts.site')
@section('title', 'Features')
@php
    $seo = [
        'title' => 'Features — Agency OS',
        'description' => 'Explore every Agency OS feature: client management, projects & tasks, leads & CRM, finance & invoicing, reporting and automation.',
        'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => 'Agency OS features', 'url' => url()->current(), 'isPartOf' => ['@type' => 'WebSite', 'name' => config('app.name'), 'url' => url('/')]]],
    ];
@endphp
@section('content')
<header class="max-w-4xl mx-auto px-6 pt-16 pb-10 text-center">
    <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Every feature your agency needs</h1>
    <p class="text-gray-500 mt-4 text-lg">One platform for clients, projects, leads, invoices, reports and your client portal.</p>
</header>

<section class="max-w-7xl mx-auto px-6 pb-20 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach ($all as $slug => $feature)
        <a href="{{ route('site.feature', $slug) }}" class="group rounded-2xl border border-gray-100 p-7 hover:shadow-lg hover:border-indigo-100 transition">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition">
                <x-icon :name="$feature['icon']" class="w-5 h-5" />
            </div>
            <h2 class="font-bold text-lg">{{ $feature['title'] }}</h2>
            <p class="text-sm text-gray-500 mt-1.5">{{ $feature['excerpt'] }}</p>
            <span class="inline-flex items-center gap-1 text-sm text-indigo-600 mt-4 font-medium">Learn more
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        </a>
    @endforeach
</section>

<section class="max-w-5xl mx-auto px-6 pb-20 text-center">
    <h2 class="text-3xl font-black">Ready to see it in action?</h2>
    <p class="text-gray-500 mt-2">Start your free 14-day trial — no credit card required.</p>
    <a href="{{ route('register') }}" class="inline-block mt-6 bg-indigo-600 text-white px-8 py-3.5 rounded-xl font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200">Start free trial</a>
</section>
@endsection
