<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <x-brand-head />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <x-seo
        title="Agency OS — Client, Project & Lead Management for Agencies"
        description="Run your agency on one operating system: clients, projects, tasks, leads, proposals, invoicing, reporting and a client portal. Start your 14-day free trial."
        :jsonLd="[
            ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'Agency OS', 'url' => url('/'), 'logo' => url('/favicon.svg')],
            ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => 'Agency OS', 'url' => url('/'), 'potentialAction' => ['@type' => 'SearchAction', 'target' => url('/blog?q={search_term_string}'), 'query-input' => 'required name=search_term_string']],
        ]"
    />
    <x-tracking placement="head" />
</head>
<body class="bg-white text-gray-900 antialiased">
    <x-tracking placement="body" />

    <!-- Top trust strip (We360: "TRUSTED BY 10,000+ GLOBAL TEAMS | SOC2 CERTIFIED") -->
    <div class="bg-gray-900 text-white text-center text-[11px] tracking-wide py-2 px-4">
        <span class="font-semibold text-indigo-300">TRUSTED BY 100+ GROWING AGENCIES</span>
        <span class="mx-2 text-gray-500">|</span>
        <span>14-DAY FREE TRIAL</span>
        <span class="mx-2 text-gray-500">|</span>
        <span>NO CREDIT CARD</span>
        <span class="mx-2 text-gray-500">|</span>
        <span>SOC2-STYLE SECURITY</span>
    </div>

    <!-- Nav -->
    <nav class="sticky top-0 z-40 bg-white/85 backdrop-blur border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight">Agency<span class="text-indigo-600">OS</span></a>
            <div class="hidden lg:flex items-center gap-6 text-sm text-gray-600">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="hover:text-gray-900 flex items-center gap-1">Product
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" class="absolute left-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-100 py-2">
                        <a href="{{ route('site.features') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 font-medium">All features</a>
                        @foreach (['client-management','project-tasks','leads-crm','finance-invoicing','reporting','automation'] as $slug)
                            <a href="{{ route('site.feature', $slug) }}" class="block px-4 py-2 text-sm hover:bg-gray-50">{{ ucwords(str_replace('-', ' ', $slug)) }}</a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('site.use-cases') }}" class="hover:text-gray-900">Industries</a>
                <a href="{{ route('site.integrations') }}" class="hover:text-gray-900">Integrations</a>
                <a href="{{ route('pricing') }}" class="hover:text-gray-900">Pricing</a>
                <a href="{{ route('site.resources') }}" class="hover:text-gray-900">Resources</a>
                <a href="{{ route('blog.index') }}" class="hover:text-gray-900">Blog</a>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 font-medium hidden sm:block">Log in</a>
                <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Start for free</a>
            </div>
        </div>
    </nav>

    <!-- HERO (We360: headline + subtext + [Book A demo][Start Free Trial] + chips + big product screenshot) -->
    <header class="relative overflow-hidden bg-gradient-to-b from-indigo-50/50 via-white to-white">
        <div class="max-w-7xl mx-auto px-6 pt-16 pb-14 grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex -space-x-2">
                        <span class="w-9 h-9 rounded-full bg-indigo-100 border-2 border-white flex items-center justify-center text-[10px] font-bold text-indigo-600">AS</span>
                        <span class="w-9 h-9 rounded-full bg-purple-100 border-2 border-white flex items-center justify-center text-[10px] font-bold text-purple-600">PN</span>
                        <span class="w-9 h-9 rounded-full bg-green-100 border-2 border-white flex items-center justify-center text-[10px] font-bold text-green-600">RM</span>
                    </div>
                    <span class="text-xs text-gray-400 font-medium">+100 Agencies</span>
                </div>
                <h1 class="text-4xl sm:text-5xl xl:text-6xl font-black tracking-tight leading-[1.05]">
                    {{ config('brand.hero_headline_1') }}<br>
                    <span class="text-indigo-600">{{ config('brand.hero_headline_2') }}</span>
                </h1>
                <p class="text-lg text-gray-500 mt-5 max-w-lg leading-relaxed">
                    {{ config('brand.hero_sub') }}
                </p>
                <div class="mt-8 flex items-center gap-4 flex-wrap">
                    <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200">Start Free Trial</a>
                    <a href="{{ route('contact') }}" class="px-7 py-3.5 rounded-xl border border-gray-300 font-semibold hover:bg-gray-50">Book A Demo</a>
                </div>
                <div class="mt-6 flex items-center gap-5 text-xs text-gray-500 flex-wrap">
                    <span class="font-medium">Free 14-Day Trial</span><span class="text-gray-300">|</span>
                    <span class="font-medium">15 Mins Setup</span><span class="text-gray-300">|</span>
                    <span class="font-medium">No Credit-card</span>
                </div>
            </div>

            @php $dash = \App\Support\Brand::screenshot('dashboard'); @endphp
            <!-- Big product screenshot: real screenshot when uploaded, else CSS mockup -->
            <div class="relative">
            @if ($dash)
                <img src="{{ $dash }}" alt="Agency OS dashboard" class="rounded-2xl shadow-2xl shadow-indigo-200/50 border border-gray-100 w-full">
            @else
            <!-- Big product screenshot (We360 hero image) -->
            <div class="relative">
                <div class="bg-white rounded-2xl shadow-2xl shadow-indigo-200/50 border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2.5 flex items-center gap-1.5 border-b">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                        <span class="ml-3 text-[10px] text-gray-400">app.your-agency.yoursaas.com/dashboard</span>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-bold">Agency Dashboard</div>
                            <div class="flex gap-2">
                                <span class="text-[10px] bg-green-50 text-green-700 px-2 py-0.5 rounded-full font-semibold">12 active</span>
                                <span class="text-[10px] bg-red-50 text-red-700 px-2 py-0.5 rounded-full font-semibold">2 overdue</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-4 gap-3">
                            @foreach ([['Revenue','₹8.4L','text-green-600'],['Clients','24','text-indigo-600'],['Open Tasks','58','text-amber-600'],['Pipeline','₹12.9L','text-purple-600']] as [$l,$v,$c])
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <div class="text-[9px] text-gray-400">{{ $l }}</div>
                                    <div class="text-sm font-bold {{ $c }}">{{ $v }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="rounded-lg border border-gray-100 p-3">
                            <div class="flex justify-between text-[10px] text-gray-400 mb-2"><span>Revenue (6 months)</span><span class="text-indigo-600 font-semibold">+32%</span></div>
                            <div class="flex items-end gap-1.5 h-20">
                                @foreach ([35, 45, 40, 60, 75, 90] as $h)
                                    <div class="flex-1 rounded-t bg-indigo-500/20" style="height: {{ $h }}%"></div>
                                @endforeach
                                <div class="flex-1 rounded-t bg-indigo-600" style="height: 100%"></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-lg border border-gray-100 p-2.5">
                                <div class="text-[9px] text-gray-400 mb-1.5">Client health</div>
                                <div class="space-y-1">
                                    @foreach ([['bg-green-500','UrbanKart','Healthy'],['bg-amber-400','WellNest','Watch'],['bg-green-500','FoodieExpress','Healthy']] as [$c,$n,$s])
                                        <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full {{ $c }}"></span><span class="text-[9px] text-gray-600 flex-1">{{ $n }}</span><span class="text-[8px] text-gray-400">{{ $s }}</span></div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-100 p-2.5">
                                <div class="text-[9px] text-gray-400 mb-1.5">Team workload</div>
                                <div class="space-y-1.5">
                                    @foreach ([['Sneha','80'],['Rohan','55'],['Priya','35']] as [$n,$w])
                                        <div><div class="flex justify-between text-[8px] text-gray-500"><span>{{ $n }}</span><span>{{ $w }}%</span></div><div class="h-1 bg-gray-100 rounded-full"><div class="h-1 bg-indigo-500 rounded-full" style="width: {{ $w }}%"></div></div></div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-5 -left-5 bg-white rounded-xl shadow-xl border border-gray-100 px-4 py-3 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <div><div class="text-xs font-semibold">Proposal accepted</div><div class="text-[10px] text-gray-400">Just now</div></div>
                </div>
            </div>

            @endif
            </div>        </div>
    </header>

    <!-- LOGO BAR (We360: "Trusted by leading workforce teams across the globe") -->
    <section class="border-y border-gray-100 bg-gray-50/60">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <p class="text-center text-xs text-gray-400 uppercase tracking-widest mb-5">Trusted by leading agencies across the globe</p>
            <div class="flex flex-wrap justify-center gap-x-12 gap-y-4 text-gray-300 font-bold">
                @foreach (['UrbanKart','WellNest','FoodieExpress','Bloom Digital','Northstar Media','Peak & Co'] as $logo)
                    <span class="text-lg select-none">{{ $logo }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ONE PLATFORM. SIX SUPERPOWERS (We360 exact section) -->
    <section class="max-w-7xl mx-auto px-6 py-20">
        <div class="flex items-end justify-between mb-12 flex-wrap gap-4">
            <div class="max-w-xl">
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">One trusted platform.<br>Six agency superpowers</h2>
                <p class="text-gray-500 mt-3">Pick the capability your agency needs today or the one they'll need next quarter.</p>
            </div>
            <a href="{{ route('site.features') }}" class="inline-flex items-center gap-1.5 text-indigo-600 font-semibold hover:underline shrink-0">
                See All Features
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>

        @php
            $superpowers = [
                ['client-management','Client Hub','Onboard, track and retain clients with health scores, retainers, contracts and a branded client portal — every client relationship decoded.','bg-indigo-50'],
                ['project-tasks','Task Engine','Kanban boards, recurring tasks that create themselves, checklists, approvals and workload views — delivery without status meetings.','bg-purple-50'],
                ['leads-crm','Lead Pipeline','Leads from Lead365 and Meta Ads flow through your pipeline automatically, with proposals and one-click conversion.','bg-green-50'],
                ['finance-invoicing','Finance & Invoicing','GST invoices, BikriBook sync, expenses and per-client profitability — get paid on time, every time.','bg-amber-50'],
                ['reporting','Reporting','Weekly and monthly client reports with auto-calculated metrics and branded PDFs — built in minutes.','bg-blue-50'],
                ['automation','Automation','No-code rules that notify, create tasks and follow up automatically — your agency runs itself.','bg-rose-50'],
            ];
        @endphp

        <div class="space-y-14">
            @foreach ($superpowers as $i => [$slug, $title, $desc, $tint])
                <div class="grid lg:grid-cols-2 gap-10 items-center">
                    <div class="{{ $i % 2 === 1 ? 'lg:order-2' : '' }}">
                        <div class="w-12 h-12 rounded-xl {{ $tint }} text-indigo-600 flex items-center justify-center mb-5"><x-icon :name="$slug === 'client-management' ? 'users' : ($slug === 'project-tasks' ? 'check-circle' : ($slug === 'leads-crm' ? 'target' : ($slug === 'finance-invoicing' ? 'banknotes' : ($slug === 'reporting' ? 'chart-bar' : 'bolt'))))" class="w-6 h-6" /></div>
                        <h3 class="text-2xl font-black">{{ $title }}</h3>
                        <p class="text-gray-500 mt-3 leading-relaxed">{{ $desc }}</p>
                        <a href="{{ route('site.feature', $slug) }}" class="inline-flex items-center gap-1.5 text-indigo-600 font-semibold mt-5 hover:underline">Read More
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </div>
                    <div class="{{ $i % 2 === 1 ? 'lg:order-1' : '' }}">
                        @php
                            $shotKey = match ($slug) {
                                'client-management' => 'clients',
                                'project-tasks' => 'tasks',
                                'leads-crm' => 'leads',
                                'finance-invoicing' => 'finance',
                                default => 'dashboard',
                            };
                            $shot = \App\Support\Brand::screenshot($shotKey);
                        @endphp
                        @if ($shot)
                            <img src="{{ $shot }}" alt="{{ $title }} screenshot" class="rounded-2xl border border-gray-100 shadow-lg shadow-gray-100 w-full">
                        @else
                        <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-gray-50 to-white p-6 shadow-lg shadow-gray-100">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                                <span class="ml-2 text-[10px] text-gray-400">{{ $title }}</span>
                            </div>
                            <div class="space-y-2.5">
                                @foreach (array_slice([
                                    'client-management' => ['20-step onboarding checklists','Health scores with reasons','Client portal approvals & requests'],
                                    'project-tasks' => ['Drag-and-drop kanban board','Recurring tasks with auto-instances','Subtasks, checklists & attachments'],
                                    'leads-crm' => ['Lead365 webhook sync (9 events)','Meta Ads & form capture','Proposals with PDF & email'],
                                    'finance-invoicing' => ['GST invoices with auto-numbering','BikriBook sync + 6-hour payment check','Profitability with margin colours'],
                                    'reporting' => ['4-step report builder','Auto-calculated CTR/ROAS/AOV','Branded PDFs in one click'],
                                    'automation' => ['14 trigger events','8 action types','5 rules pre-built'],
                                ][$slug] ?? [], 0, 3) as $b)
                                    <div class="flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2.5 bg-white">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        <span class="text-xs text-gray-700">{{ $b }}</span>
                                    </div>
                                @endforeach
                                <div class="rounded-lg border border-gray-100 px-3 py-2.5 bg-white flex items-center gap-2">
                                    <div class="flex-1 space-y-1.5">
                                        <div class="h-1.5 bg-gray-100 rounded-full w-full"></div>
                                        <div class="h-1.5 bg-gray-100 rounded-full w-4/5"></div>
                                    </div>
                                    <span class="text-[10px] text-green-600 font-semibold">Live</span>
                                </div>
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- STATS BAND (We360: 10000+ / 120,000+ / 21+) -->
    <section class="bg-indigo-600 py-14">
        <div class="max-w-6xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-white">
            @foreach ([['100+','Agencies Served'],['120,000+','Tasks Delivered'],['21+','Integrations & Tools'],['98%','Client Retention']] as [$num,$label])
                <div>
                    <div class="text-4xl font-black">{{ $num }}</div>
                    <div class="text-indigo-200 mt-1 text-sm">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- HOW IT WORKS (We360: "Just 15 mins to set up") -->
    <section class="max-w-7xl mx-auto px-6 py-20">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight">How It Works</h2>
            <p class="text-gray-500 mt-3">Just 15 mins to set up &amp; get agency intelligence from day one.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach ([
                ['1','Create Your Workspace','Sign up, pick your subdomain, choose a plan or start the free trial. Zero code, zero IT headache.'],
                ['2','Invite Your Team & Clients','Add ops managers, account managers and specialists with role-based access. Connect Lead365 and BikriBook.'],
                ['3','Win & Deliver','Import leads, send proposals, deliver projects, invoice clients and share reports — all from one dashboard.'],
            ] as [$num,$title,$desc])
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl transition">
                    <div class="h-36 bg-gradient-to-br from-indigo-50 to-white flex items-center justify-center">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white font-black flex items-center justify-center text-xl shadow-lg shadow-indigo-200">{{ $num }}</div>
                    </div>
                    <div class="p-6">
                        <h3 class="font-bold text-lg">{{ $title }}</h3>
                        <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">{{ $desc }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- INDUSTRY TABS (We360: "A smarter way to navigate agency productivity") -->
    <section class="bg-gray-50/70 py-20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">A smarter way to navigate agency productivity</h2>
                <p class="text-gray-500 mt-3">Workflow intelligence that adapts to your sector.</p>
            </div>

            @php
                $industries = [
                    'digital' => [
                        'tab' => 'Digital Marketing',
                        'headline' => 'Campaign productivity that drives revenue',
                        'desc' => 'Match team output to client revenue, across ad accounts, content and reporting.',
                        'metric' => '38%',
                        'metricLabel' => 'Faster client reporting',
                        'persona' => ['Sneha Iyer','Performance Marketer','Top 3%','Top Performer Pattern Identified','Sneha\'s productivity trends sit 38% above the team baseline. Use her benchmarks to set quarterly goals for the wider team.'],
                    ],
                    'creative' => [
                        'tab' => 'Creative & Design',
                        'headline' => 'Design approvals that actually close',
                        'desc' => 'Briefs, revisions, approvals and deliverables without the email ping-pong.',
                        'metric' => '3x',
                        'metricLabel' => 'Faster approvals',
                        'persona' => ['Priya Nair','Operations Manager','HIGH ROI','Approval Cycle Halved','Clients approve deliverables in hours instead of days through the branded portal.'],
                    ],
                    'webdev' => [
                        'tab' => 'Web Development',
                        'headline' => 'Engineering output, unlocked',
                        'desc' => 'Match development investment to delivery, across sprints and maintenance retainers.',
                        'metric' => '1.4X',
                        'metricLabel' => 'Faster delivery velocity',
                        'persona' => ['Rohit Mehta','Project Lead','HIGH ROI','Capacity Unlock Identified','Reallocating two developers from a slower stream could lift quarterly delivery velocity by ~22%.'],
                    ],
                    'consulting' => [
                        'tab' => 'Consulting',
                        'headline' => 'Engagement intelligence for consultants',
                        'desc' => 'Proposals, onboarding, deliverables and billing for every engagement.',
                        'metric' => 'Q4',
                        'metricLabel' => 'Appraisal-ready records',
                        'persona' => ['James Anderson','Engagement Manager','AUDIT READY','Deliverable Trail Compiled','12 months of deliverables and approvals compiled into a single client-ready report in minutes.'],
                    ],
                ];
            @endphp

            <div x-data="{ active: 'digital' }">
                <!-- Tabs -->
                <div class="flex flex-wrap justify-center gap-2 mb-10">
                    @foreach ($industries as $key => $ind)
                        <button @click="active = '{{ $key }}'"
                                class="px-5 py-2.5 rounded-full text-sm font-semibold transition"
                                :class="active === '{{ $key }}' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white text-gray-600 border border-gray-200 hover:border-indigo-300'">
                            {{ $ind['tab'] }}
                        </button>
                    @endforeach
                </div>

                @foreach ($industries as $key => $ind)
                    <div x-show="active === '{{ $key }}'" x-cloak class="grid lg:grid-cols-2 gap-10 items-center">
                        <div>
                            <h3 class="text-2xl sm:text-3xl font-black">{{ $ind['headline'] }}</h3>
                            <p class="text-gray-500 mt-3 text-lg">{{ $ind['desc'] }}</p>
                            <a href="{{ route('site.use-cases') }}" class="inline-flex items-center gap-1.5 text-indigo-600 font-semibold mt-5 hover:underline">Read More on {{ $ind['tab'] }}
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.5 4.5L21 12l-7.5 7.5M21 12H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>

                            <div class="mt-8 grid sm:grid-cols-2 gap-4">
                                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
                                    <div class="text-3xl font-black text-indigo-600">{{ $ind['metric'] }}</div>
                                    <div class="text-xs text-gray-400 mt-1">{{ $ind['metricLabel'] }}</div>
                                </div>
                                <div class="bg-white rounded-2xl border border-gray-100 p-5">
                                    <div class="flex items-center gap-2.5 mb-2">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center text-xs">{{ strtoupper(substr($ind['persona'][0], 0, 1)) }}</div>
                                        <div>
                                            <div class="text-sm font-semibold">{{ $ind['persona'][0] }}</div>
                                            <div class="text-[10px] text-gray-400">{{ $ind['persona'][1] }}</div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700">{{ $ind['persona'][2] }}</span>
                                    <div class="text-[11px] text-gray-600 mt-2">{{ $ind['persona'][3] }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-lg shadow-gray-100">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                                <span class="ml-2 text-[10px] text-gray-400">{{ $ind['tab'] }} · Agency OS</span>
                            </div>
                            <div class="space-y-2.5">
                                @foreach ([
                                    'digital' => ['Campaign reporting — 38% faster','Client health: 12 active, 2 at risk','Pipeline: ₹12.9L this quarter'],
                                    'creative' => ['3 approvals closed today','Version history: v4 → v5 tracked','Client feedback recorded on every task'],
                                    'webdev' => ['Sprint velocity +1.4x','12 maintenance tickets this week','2 capacity risks flagged'],
                                    'consulting' => ['Proposal sent: 92% close rate','Onboarding: 18/20 steps done','Invoice paid: ₹4.2L this month'],
                                ][$key] ?? [] as $b)
                                    <div class="flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2.5">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        <span class="text-xs text-gray-700">{{ $b }}</span>
                                    </div>
                                @endforeach
                                <div class="rounded-lg border border-gray-100 px-3 py-2.5 flex items-center gap-2">
                                    <div class="flex-1 space-y-1.5">
                                        <div class="h-1.5 bg-gray-100 rounded-full w-full"></div>
                                        <div class="h-1.5 bg-gray-100 rounded-full w-3/5"></div>
                                    </div>
                                    <span class="text-[10px] text-green-600 font-semibold">● Live</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS (5day/We360: real quotes) -->
    <section class="max-w-7xl mx-auto px-6 py-20">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight">Loved by agency owners</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-5">
            @foreach ([
                ['Aarav S.','Founder, UrbanKart Agency','We replaced 4 tools and a dozen Excel sheets. Onboarding checklists alone saved us hours per client.'],
                ['Priya N.','Ops Manager, Bloom Digital','The client portal is a game-changer. Approvals that used to take days now happen in hours.'],
                ['Rohan M.','Account Manager, Northstar','Profitability per client finally makes sense. I know our margins before every renewal call.'],
            ] as [$name,$role,$quote])
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <div class="flex gap-0.5 text-amber-400 mb-3">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.5a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                        @endfor
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">"{{ $quote }}"</p>
                    <div class="mt-4 flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center text-xs">{{ strtoupper(substr($name, 0, 1)) }}</div>
                        <div><div class="text-sm font-semibold">{{ $name }}</div><div class="text-xs text-gray-400">{{ $role }}</div></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- PRICING (We360-style, links to full page) -->
    <section id="pricing" class="bg-gray-50/70 py-20">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">Simple, honest pricing</h2>
                <p class="text-gray-500 mt-3">Start free for 14 days. Upgrade when you're ready. Cancel anytime.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach ($plans as $plan)
                    <div class="rounded-2xl border p-7 bg-white {{ $plan->slug === 'professional' ? 'border-indigo-600 ring-2 ring-indigo-600 relative' : 'border-gray-200' }}">
                        @if ($plan->slug === 'professional')
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs font-semibold px-3 py-1 rounded-full">Most popular</span>
                        @endif
                        <h3 class="font-bold">{{ $plan->name }}</h3>
                        <div class="mt-3 flex items-baseline gap-1">
                            <span class="text-4xl font-black">₹{{ number_format($plan->price_monthly) }}</span>
                            <span class="text-sm text-gray-400">/month</span>
                        </div>
                        <ul class="mt-5 space-y-2.5 text-sm text-gray-600">
                            @foreach (array_slice($plan->features ?? [], 0, 5) as $feature)
                                <li class="flex gap-2.5">
                                    <svg class="w-4 h-4 text-green-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('pricing') }}" class="mt-6 block text-center rounded-xl py-2.5 text-sm font-semibold {{ $plan->slug === 'professional' ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">View plan</a>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-8">
                <a href="{{ route('pricing') }}" class="text-indigo-600 font-semibold hover:underline">See full pricing, comparison & FAQ →</a>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="max-w-3xl mx-auto px-6 py-20">
        <h2 class="text-3xl font-black text-center mb-10">Frequently asked questions</h2>
        <div x-data="{ open: 0 }" class="space-y-3">
            @foreach ([
                ['Is there a free trial?','Yes - every new workspace gets a full 14-day free trial with all features, no credit card required.'],
                ['Can I import my existing clients and tasks?','Yes. You can add clients, projects and tasks manually, and leads flow in automatically from Lead365.'],
                ['Does it work with BikriBook and Razorpay?','Yes - invoices sync to BikriBook, and plan payments are handled securely via Razorpay.'],
                ['Is my data secure?','Each agency gets fully isolated data with role-based access, encrypted API keys and audited activity logs.'],
                ['Can clients log in?','Yes - the client portal lets your clients approve deliverables, view reports, download invoices and submit requests.'],
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

    <!-- FINAL CTA -->
    <section class="max-w-7xl mx-auto px-6 pb-20">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-16 text-center text-white">
            <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/10 rounded-full blur-2xl"></div>
            <h2 class="text-3xl sm:text-4xl font-black relative">Ready to run your agency on autopilot?</h2>
            <p class="text-indigo-100 mt-3 relative">Join agencies that traded chaos for one clean operating system.</p>
            <div class="relative mt-8 flex items-center justify-center gap-4 flex-wrap">
                <a href="{{ route('register') }}" class="bg-white text-indigo-700 px-8 py-3.5 rounded-xl font-bold hover:bg-indigo-50 shadow-xl">Start Free Trial</a>
                <a href="{{ route('contact') }}" class="border border-white/40 text-white px-8 py-3.5 rounded-xl font-semibold hover:bg-white/10">Book A Demo</a>
            </div>
            <div class="relative mt-4 text-xs text-indigo-200">No credit card · 15-minute setup</div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-6 py-12 grid sm:grid-cols-4 gap-8">
            <div class="sm:col-span-2">
                <div class="text-xl font-black">Agency<span class="text-indigo-600">OS</span></div>
                <p class="text-sm text-gray-500 mt-2 max-w-xs">The operating system for modern agencies. Clients, projects, leads, invoices and reports in one place.</p>
            </div>
            <div>
                <div class="text-xs font-semibold text-gray-400 uppercase mb-3">Product</div>
                <div class="space-y-2 text-sm text-gray-600">
                    <a href="{{ route('site.features') }}" class="block hover:text-gray-900">Features</a>
                    <a href="{{ route('site.use-cases') }}" class="block hover:text-gray-900">Industries</a>
                    <a href="{{ route('site.integrations') }}" class="block hover:text-gray-900">Integrations</a>
                    <a href="{{ route('pricing') }}" class="block hover:text-gray-900">Pricing</a>
                </div>
            </div>
            <div>
                <div class="text-xs font-semibold text-gray-400 uppercase mb-3">Company</div>
                <div class="space-y-2 text-sm text-gray-600">
                    <a href="{{ route('site.company') }}" class="block hover:text-gray-900">About us</a>
                    <a href="{{ route('site.resources') }}" class="block hover:text-gray-900">Resources</a>
                    <a href="{{ route('blog.index') }}" class="block hover:text-gray-900">Blog</a>
                    <a href="{{ route('site.faq') }}" class="block hover:text-gray-900">FAQ</a>
                    <a href="{{ route('contact') }}" class="block hover:text-gray-900">Contact</a>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-6 py-5 flex flex-wrap items-center justify-between text-xs text-gray-400">
                <span>© {{ date('Y') }} Agency OS. All rights reserved.</span>
                <span>Made for agencies that ship.</span>
            </div>
        </div>
    </footer>
</body>
</html>
