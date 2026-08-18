@extends('layouts.site')
@section('title', 'Integrations')
@php
    $seo = [
        'title' => 'Integrations — Task365',
        'description' => 'Task365 connects with Lead365 for leads, BikriBook for invoicing, Razorpay for payments, Slack, Microsoft Teams, Google Calendar + Meet and more.',
        'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => 'Task365 integrations', 'url' => url()->current()]],
    ];
@endphp
@section('content')

<!-- Hero -->
<header class="bg-gradient-to-b from-indigo-50/60 to-white">
    <div class="max-w-7xl mx-auto px-6 pt-16 pb-12 text-center">
        <div class="inline-flex items-center gap-2 bg-white border border-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm mb-6">
            <span class="w-2 h-2 rounded-full bg-green-500"></span> 21+ integrations & growing
        </div>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Plug into your existing stack</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">Leads, invoices, payments, calendars and team chat — connected automatically. No code, no IT team.</p>
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

<!-- Integration logo grid (We360/5day style) -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
        @foreach ([
            ['link','Lead365','Leads'],
            ['receipt','BikriBook','Invoicing'],
            ['credit-card','Razorpay','Payments'],
            ['chat-bubble-left-right','Slack','Chat'],
            ['users','Microsoft Teams','Chat'],
            ['calendar','Google Calendar','Sync'],
            ['video-camera','Google Meet','Meetings'],
            ['device-phone-mobile','Meta Ads','Leads'],
            ['envelope','Email','Notify'],
            ['bolt','Webhooks','Custom'],
            ['clock','Scheduler','Automation'],
            ['document-text','Zapier-style','Coming'],
        ] as [$icon,$name,$tag])
            <div class="rounded-2xl border border-gray-100 p-5 text-center hover:shadow-lg transition">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3"><x-icon :name="$icon" class="w-5 h-5" /></div>
                <div class="font-bold text-sm">{{ $name }}</div>
                <div class="text-xs text-gray-400">{{ $tag }}</div>
            </div>
        @endforeach
    </div>
</section>

<!-- Deep-dive integration blocks (alternating) -->
<section class="max-w-7xl mx-auto px-6 pb-16 space-y-16">
    @foreach ([
        ['link','Lead365 — leads on autopilot','Lead365 sends leads, stage changes, wins and losses to your pipeline in real time. Nine webhook events are handled automatically.',
         ['lead.created → pipeline auto-entry','Stage changes tracked with history','Won leads flagged for conversion','Meta & form leads auto-assigned'], 'bg-purple-50'],
        ['receipt','BikriBook — invoices that get paid','Create GST invoices locally first, sync to BikriBook, send to clients and track payments — with a 6-hour status check.',
         ['GST invoices with auto-numbering','One-click sync with retry & logs','Send-to-client email from BikriBook','Payment status + admin alerts'], 'bg-green-50'],
        ['calendar','Google Calendar + Meet — meetings that happen','Sync dated tasks to Google Calendar as events with an automatic Google Meet link on every deliverable.',
         ['OAuth 2.0 connect, tokens encrypted','Tasks → 10 AM calendar events','Automatic Meet conference links','Upcoming events on the dashboard'], 'bg-blue-50'],
        ['chat-bubble-left-right','Slack & Teams — updates where your team lives','Push new leads, task overdues, invoice payments and client requests to your Slack or Teams channel.',
         ['Incoming webhooks, no app needed','10 event types with toggles','Adaptive Cards on Teams','Test buttons on the settings page'], 'bg-amber-50'],
    ] as [$icon,$title,$desc,$bullets,$tint])
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div class="{{ $loop->even ? 'lg:order-2' : '' }}">
                <div class="w-12 h-12 rounded-xl {{ $tint }} text-indigo-600 flex items-center justify-center mb-5"><x-icon :name="$icon" class="w-6 h-6" /></div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight">{{ $title }}</h2>
                <p class="text-gray-500 mt-3 text-lg">{{ $desc }}</p>
                <ul class="mt-5 space-y-2.5">
                    @foreach ($bullets as $b)
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            <span class="text-gray-700 font-medium">{{ $b }}</span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 text-indigo-600 font-semibold mt-6 hover:underline">Connect yours
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>
            <div class="{{ $loop->even ? 'lg:order-1' : '' }}">
                <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-gray-50 to-white p-6 shadow-lg shadow-gray-100">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                        <span class="ml-2 text-[10px] text-gray-400">Connected</span>
                        <span class="ml-auto text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">● Live</span>
                    </div>
                    <div class="space-y-2.5">
                        @foreach (array_slice($bullets, 0, 2) as $i => $b)
                            <div class="flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2.5 bg-white">
                                <span class="w-2 h-2 rounded-full {{ $i === 0 ? 'bg-indigo-500' : 'bg-green-500' }}"></span>
                                <span class="text-xs text-gray-700">{{ $b }}</span>
                            </div>
                        @endforeach
                        <div class="rounded-lg border border-gray-100 px-3 py-2.5 bg-white flex items-center gap-2">
                            <div class="flex-1 space-y-1.5">
                                <div class="h-1.5 bg-gray-100 rounded-full w-full"></div>
                                <div class="h-1.5 bg-gray-100 rounded-full w-3/5"></div>
                            </div>
                            <span class="text-[10px] text-green-600 font-semibold">Synced</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</section>

<!-- FAQ -->
<section class="max-w-3xl mx-auto px-6 pb-16">
    <h2 class="text-3xl font-black text-center mb-10">Integration questions</h2>
    <div x-data="{ open: 0 }" class="space-y-3">
        @foreach ([
            ['How do I connect Lead365?','Paste your webhook URL into Lead365, select the events, and leads flow into your pipeline automatically. The webhook log shows every event received.'],
            ['How does BikriBook sync work?','Invoices are saved locally first, then synced with one click. Payment status is checked every 6 hours automatically.'],
            ['Do I need a Google Workspace account?','Any Google account works. You authorize once via OAuth and tasks sync to your primary calendar.'],
            ['Can I turn off channel notifications?','Yes — choose exactly which events go to Slack/Teams, or disable them entirely.'],
            ['Are my integration credentials safe?','Yes. Google tokens are encrypted at rest, and webhook URLs are stored per-tenant with role-based settings access.'],
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

<!-- CTA -->
<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-14 text-center text-white">
        <h2 class="text-3xl font-black">Connect your stack in minutes</h2>
        <p class="text-indigo-100 mt-2">Start free — every integration included.</p>
        <a href="{{ route('register') }}" class="inline-block mt-7 bg-white text-indigo-700 px-8 py-3.5 rounded-xl font-bold hover:bg-indigo-50 shadow-xl">Start free trial</a>
    </div>
</section>

@endsection
