@extends('layouts.site')
@section('title', 'Features')
@php
    $seo = [
        'title' => 'Features — Task365',
        'description' => 'Explore every Task365 feature: client management, projects & tasks, leads & CRM, finance & invoicing, reporting and automation.',
        'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => 'Task365 features', 'url' => url()->current(), 'isPartOf' => ['@type' => 'WebSite', 'name' => config('app.name'), 'url' => url('/')]]],
    ];
@endphp
@section('content')

<!-- Hero (We360 style: badge + headline + CTA + chips) -->
<header class="bg-gradient-to-b from-indigo-50/60 to-white">
    <div class="max-w-7xl mx-auto px-6 pt-16 pb-12 text-center">
        <div class="inline-flex items-center gap-2 bg-white border border-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm mb-6">
            <span class="w-2 h-2 rounded-full bg-green-500"></span> Six superpowers in one platform
        </div>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight">One platform. Six productivity superpowers.</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">Pick the capability your agency needs today — or the one you'll need next quarter. Everything is included in every plan.</p>
        <div class="mt-8 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200">Start free trial</a>
            <a href="{{ route('contact') }}" class="px-7 py-3.5 rounded-xl border border-gray-300 font-semibold hover:bg-gray-50">Book a demo</a>
        </div>
        <div class="mt-5 flex items-center justify-center gap-5 text-xs text-gray-400 flex-wrap">
            <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg> Free 14-day trial</span>
            <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg> 15-min setup</span>
            <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg> No credit card</span>
        </div>
    </div>
</header>

<!-- Feature cards (We360 6-superpower grid) -->
<section class="max-w-7xl mx-auto px-6 py-16 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach ($all as $slug => $feature)
        <a href="{{ route('site.feature', $slug) }}" class="group rounded-2xl border border-gray-100 p-7 hover:shadow-xl hover:border-indigo-100 transition">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-5 group-hover:bg-indigo-600 group-hover:text-white transition">
                <x-icon :name="$feature['icon']" class="w-6 h-6" />
            </div>
            <h2 class="font-bold text-lg">{{ $feature['title'] }}</h2>
            <p class="text-sm text-gray-500 mt-2 leading-relaxed">{{ $feature['excerpt'] }}</p>
            <ul class="mt-4 space-y-1.5">
                @foreach (array_slice($feature['bullets'], 0, 3) as $b)
                    <li class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-green-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ $b }}
                    </li>
                @endforeach
            </ul>
            <span class="inline-flex items-center gap-1 text-sm text-indigo-600 mt-5 font-semibold">Learn more
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        </a>
    @endforeach
</section>

<!-- Stats band -->
<section class="bg-indigo-600 py-14">
    <div class="max-w-6xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-white">
        @foreach ([['100+','Agencies running'],['120,000+','Tasks delivered'],['21+','Integrations & tools'],['98%','Client retention']] as [$num,$label])
            <div>
                <div class="text-4xl font-black">{{ $num }}</div>
                <div class="text-indigo-200 mt-1 text-sm">{{ $label }}</div>
            </div>
        @endforeach
    </div>
</section>

<!-- How it works -->
<section class="max-w-7xl mx-auto px-6 py-20">
    <div class="text-center max-w-2xl mx-auto mb-12">
        <h2 class="text-3xl sm:text-4xl font-black">Every feature, working together</h2>
        <p class="text-gray-500 mt-3">The six modules share one database, one login and one workflow — no exports, no re-typing.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
        @foreach ([
            ['link','Connected','A lead becomes a client, a client gets a project, a project creates invoices. Everything links automatically.'],
            ['bolt','Automated','Recurring tasks, overdue alerts, invoice reminders and contract warnings run on their own.'],
            ['chart-bar','Measured','Every module feeds the dashboard: revenue, workload, pipeline value and profitability.'],
        ] as [$icon,$title,$desc])
            <div class="bg-white rounded-2xl border border-gray-100 p-7 text-center">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4"><x-icon :name="$icon" class="w-6 h-6" /></div>
                <h3 class="font-bold">{{ $title }}</h3>
                <p class="text-sm text-gray-500 mt-1.5">{{ $desc }}</p>
            </div>
        @endforeach
    </div>
</section>

<!-- Testimonials -->
<section class="bg-gray-50/70 py-20">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="text-3xl font-black text-center mb-10">Agencies that switched</h2>
        <div class="grid md:grid-cols-3 gap-5">
            @foreach ([
                ['Aarav S.','Founder, UrbanKart Agency','We replaced 4 tools and a dozen Excel sheets. The ROI was obvious within the first month.'],
                ['Priya N.','Ops Manager, Bloom Digital','The client portal alone is worth the subscription — approvals went from days to hours.'],
                ['Rohan M.','Account Manager, Northstar','Profitability per client finally makes sense. I know our margins before every renewal call.'],
            ] as [$name,$role,$quote])
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <div class="flex gap-0.5 text-amber-400 mb-3">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.5a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                        @endfor
                    </div>
                    <p class="text-sm text-gray-600">"{{ $quote }}"</p>
                    <div class="mt-4 flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center text-xs">{{ strtoupper(substr($name, 0, 1)) }}</div>
                        <div><div class="text-sm font-semibold">{{ $name }}</div><div class="text-xs text-gray-400">{{ $role }}</div></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA -->
<section class="max-w-7xl mx-auto px-6 py-20">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-14 text-center text-white">
        <h2 class="text-3xl font-black">Try all six superpowers free</h2>
        <p class="text-indigo-100 mt-2">Every feature, every plan, free for 14 days.</p>
        <a href="{{ route('register') }}" class="inline-block mt-7 bg-white text-indigo-700 px-8 py-3.5 rounded-xl font-bold hover:bg-indigo-50 shadow-xl">Start free trial</a>
    </div>
</section>

@endsection
