@extends('layouts.site')
@section('title', 'Pricing')
@php
    $seo = [
        'title' => 'Pricing — Task365',
        'description' => 'Simple, honest pricing for agencies. Starter ₹2,999/mo, Professional ₹6,999/mo, Enterprise ₹14,999/mo. Start your 14-day free trial - no credit card required.',
        'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'Product', 'name' => 'Task365', 'description' => 'All-in-one agency management platform', 'offers' => array_map(fn ($p) => ['@type' => 'Offer', 'name' => $p->name, 'price' => (float) $p->price_monthly, 'priceCurrency' => 'INR', 'availability' => 'https://schema.org/InStock'], $plans->all())]],
    ];
@endphp
@section('content')

<!-- Hero -->
<header class="bg-gradient-to-b from-indigo-50/70 to-white">
    <div class="max-w-4xl mx-auto px-6 pt-16 pb-10 text-center">
        <div class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 text-xs font-medium px-3 py-1 rounded-full mb-6">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Start free · No credit card required
        </div>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Pricing that grows with your agency</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">Every plan includes the full feature set — clients, projects, tasks, leads, invoicing, reporting, client portal and integrations. Upgrade as your team grows.</p>

        <div x-data="{ yearly: false }" class="flex justify-center mt-8">
            <div class="bg-white rounded-full border border-gray-200 p-1 flex text-sm shadow-sm">
                <button @click="yearly = false" :class="!yearly ? 'bg-indigo-600 text-white' : 'text-gray-500'" class="px-5 py-2 rounded-full font-medium">Monthly</button>
                <button @click="yearly = true" :class="yearly ? 'bg-indigo-600 text-white' : 'text-gray-500'" class="px-5 py-2 rounded-full font-medium">Yearly <span class="text-xs opacity-80">2 months free</span></button>
            </div>
        </div>
    </div>
</header>

<!-- Plans -->
<section class="max-w-6xl mx-auto px-6 pb-16">
    <div class="grid md:grid-cols-3 gap-6 items-stretch">
        @foreach ($plans as $plan)
            <div class="flex flex-col rounded-2xl border p-7 bg-white {{ $plan->slug === 'professional' ? 'border-indigo-600 ring-2 ring-indigo-600 relative shadow-xl shadow-indigo-100' : 'border-gray-200' }}">
                @if ($plan->slug === 'professional')
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs font-semibold px-3 py-1 rounded-full whitespace-nowrap">Most popular</span>
                @endif
                <h3 class="font-bold text-gray-900 text-lg">{{ $plan->name }}</h3>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $plan->slug === 'starter' ? 'For solo operators & small teams' : ($plan->slug === 'professional' ? 'For growing agencies' : 'For scaling agencies') }}
                </p>
                <div class="mt-4 flex items-baseline gap-1">
                    <span class="text-4xl font-black" x-text="'₹' + (yearly ? {{ $plan->price_yearly }} : {{ $plan->price_monthly }}).toLocaleString('en-IN')"></span>
                    <span class="text-sm text-gray-400" x-text="yearly ? '/year' : '/month'"></span>
                </div>
                <p class="text-xs text-gray-400 mt-1">billed {{ $plan->slug === 'enterprise' ? 'monthly or yearly' : 'monthly, cancel anytime' }}</p>

                <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs">
                    <div class="bg-gray-50 rounded-lg py-2"><span class="font-bold text-gray-800">{{ $plan->max_users ?: '∞' }}</span><div class="text-gray-400">users</div></div>
                    <div class="bg-gray-50 rounded-lg py-2"><span class="font-bold text-gray-800">{{ $plan->max_clients ?: '∞' }}</span><div class="text-gray-400">clients</div></div>
                </div>

                <ul class="mt-5 space-y-2.5 text-sm text-gray-600 flex-1">
                    @foreach (($plan->features ?? []) as $feature)
                        <li class="flex gap-2.5">
                            <svg class="w-4 h-4 text-green-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('register') }}" class="mt-6 block text-center rounded-xl py-3 text-sm font-semibold {{ $plan->slug === 'professional' ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                    Start free trial
                </a>
            </div>
        @endforeach
    </div>
    <p class="text-center text-xs text-gray-400 mt-5">All prices in INR, exclusive of GST. Every plan starts with a free 14-day trial — no credit card required.</p>
</section>

<!-- Included in every plan -->
<section class="bg-gray-50/70 py-16">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-black">Included in every plan</h2>
            <p class="text-gray-500 mt-2">No hidden add-ons. Every plan has the complete feature set.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ([
                ['users','Unlimited team workflows','Role-based access, kanban boards, recurring tasks, time tracking and workload views.'],
                ['target','Leads & proposals','Lead365 sync, Meta Ads capture, pipeline, proposals with PDF and email.'],
                ['banknotes','Finance suite','GST invoices, BikriBook sync, expenses, profitability and Razorpay billing.'],
                ['building-office','Client portal','Approvals, requests, shared reports, invoices and files for every client.'],
                ['chart-bar','Reporting','Weekly/monthly reports with auto-calculated metrics and branded PDFs.'],
                ['bolt','Automation','No-code rules for overdue tasks, leads, invoices and contracts.'],
                ['link','Integrations','Slack, Microsoft Teams, Google Calendar + Meet, Lead365, BikriBook.'],
                ['envelope','Email & digests','Assignments, reminders, daily digest and weekly manager summary.'],
            ] as [$icon,$title,$desc])
                <div class="bg-white rounded-2xl border border-gray-100 p-5">
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3"><x-icon :name="$icon" class="w-4.5 h-4.5" /></div>
                    <h3 class="font-bold text-sm">{{ $title }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Comparison table -->
<section class="max-w-5xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-black text-center mb-10">Compare plans</h2>
    <div class="overflow-x-auto rounded-2xl border border-gray-100">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-4 text-left font-semibold text-gray-700">Feature</th>
                    @foreach ($plans as $plan)
                        <th class="px-5 py-4 text-center font-bold {{ $plan->slug === 'professional' ? 'text-indigo-600' : 'text-gray-700' }}">{{ $plan->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ([
                    ['Users', fn ($p) => $p->max_users ? 'Up to '.$p->max_users : 'Unlimited'],
                    ['Clients', fn ($p) => $p->max_clients ? 'Up to '.$p->max_clients : 'Unlimited'],
                    ['Storage', fn ($p) => $p->max_storage_gb ? $p->max_storage_gb.' GB' : '500 GB'],
                    ['Projects & tasks', fn () => 'Included'],
                    ['Kanban board', fn () => 'Included'],
                    ['Recurring tasks', fn () => 'Included'],
                    ['Lead pipeline', fn () => 'Included'],
                    ['Proposals', fn () => 'Included'],
                    ['Invoicing + BikriBook', fn () => 'Included'],
                    ['Client portal', fn () => 'Included'],
                    ['Reporting', fn () => 'Included'],
                    ['Automation rules', fn () => 'Included'],
                    ['Slack / Teams / Google Meet', fn () => 'Included'],
                    ['Priority support', fn ($p) => $p->slug === 'enterprise' ? 'Included' : '—'],
                ] as [$label, $fn])
                    <tr class="hover:bg-gray-50/60">
                        <td class="px-5 py-3 font-medium text-gray-700">{{ $label }}</td>
                        @foreach ($plans as $plan)
                            <td class="px-5 py-3 text-center {{ $plan->slug === 'professional' ? 'bg-indigo-50/40' : '' }}">
                                @if ($fn($plan) === 'Included')
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                @elseif ($fn($plan) === '—')
                                    <span class="text-gray-300">—</span>
                                @else
                                    <span class="text-gray-700 font-medium">{{ $fn($plan) }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<!-- Testimonials -->
<section class="bg-gray-50/70 py-16">
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

<!-- Pricing FAQ -->
<section class="max-w-3xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-black text-center mb-10">Pricing questions</h2>
    <div x-data="{ open: 0 }" class="space-y-3">
        @foreach ([
            ['Is there really a free trial?','Yes — 14 full days with every feature, no credit card required. When it ends, choose a plan or your workspace stays available for 30 days while you decide.'],
            ['What happens after my trial?','You pick a plan and pay via Razorpay (cards/UPI/netbanking). Your data is never deleted — upgrade and everything is exactly where you left it.'],
            ['Can I switch plans later?','Anytime. Upgrade or downgrade from Settings → Subscription; the price adjusts to your billing cycle.'],
            ['Are there setup or onboarding fees?','No. Every plan includes the full feature set and our team is available to help you get set up.'],
            ['Do you offer discounts for annual billing?','Yes — paying yearly gives you 2 months free on every plan.'],
            ['Can I cancel anytime?','Yes — cancel from Settings → Subscription. You keep access until the end of the paid period.'],
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
        <h2 class="text-3xl font-black">Try Task365 free for 14 days</h2>
        <p class="text-indigo-100 mt-2">Set up your workspace in minutes. No credit card required.</p>
        <a href="{{ route('register') }}" class="inline-block mt-7 bg-white text-indigo-700 px-8 py-3.5 rounded-xl font-bold hover:bg-indigo-50 shadow-xl">Start free trial</a>
    </div>
</section>

@endsection
