<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <x-seo
        title="Agency OS"
        description="Agency OS is the all-in-one agency management platform: clients, projects, tasks, leads, proposals, invoicing, reporting and a client portal. Start your 14-day free trial."
        :jsonLd="[
            ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'Agency OS', 'url' => url('/'), 'logo' => url('/favicon.svg')],
            ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => 'Agency OS', 'url' => url('/'), 'potentialAction' => ['@type' => 'SearchAction', 'target' => url('/blog?q={search_term_string}'), 'query-input' => 'required name=search_term_string']],
        ]"
    />
    <x-tracking placement="head" />
</head>
<body class="bg-white text-gray-900 antialiased">
    <x-tracking placement="body" />

    <!-- Nav -->
    <nav class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight">Agency<span class="text-indigo-600">OS</span></a>
            <div class="hidden md:flex items-center gap-7 text-sm text-gray-600">
                <a href="{{ route('site.features') }}" class="hover:text-gray-900">Features</a>
                <a href="{{ route('site.use-cases') }}" class="hover:text-gray-900">Use cases</a>
                <a href="{{ route('site.integrations') }}" class="hover:text-gray-900">Integrations</a>
                <a href="#pricing" class="hover:text-gray-900">Pricing</a>
                <a href="{{ route('site.resources') }}" class="hover:text-gray-900">Resources</a>
                <a href="{{ route('blog.index') }}" class="hover:text-gray-900">Blog</a>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 font-medium">Sign in</a>
                <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Start free trial</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <header class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-indigo-50/80 via-white to-white pointer-events-none"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-200/40 rounded-full blur-3xl"></div>
        <div class="absolute top-40 -left-24 w-80 h-80 bg-purple-200/40 rounded-full blur-3xl"></div>
        <div class="relative max-w-7xl mx-auto px-6 pt-16 pb-20 grid lg:grid-cols-2 gap-14 items-center">
            <div>
                <div class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 text-xs font-medium px-3 py-1 rounded-full mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Built for agencies with 5-50 people
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.1]">
                    Run your agency on<br>
                    <span class="text-indigo-600">one operating system</span>
                </h1>
                <p class="text-lg text-gray-500 mt-6 max-w-lg leading-relaxed">
                    Clients, projects, tasks, leads, proposals, invoices, reports and a client portal — all in one place.
                    Say goodbye to Excel sheets and WhatsApp pings.
                </p>
                <div class="mt-8 flex items-center gap-4 flex-wrap">
                    <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-200">Start 14-day free trial</a>
                    <a href="{{ route('pricing') }}" class="px-7 py-3.5 rounded-xl border border-gray-300 font-semibold hover:bg-gray-50">View pricing</a>
                </div>
                <div class="mt-5 flex items-center gap-2 text-xs text-gray-400">
                    <svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    No credit card required · Free 14-day trial · Cancel anytime
                </div>
            </div>

            <!-- Product mockup -->
            <div class="relative">
                <div class="bg-white rounded-2xl shadow-2xl shadow-indigo-100 border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2.5 flex items-center gap-1.5 border-b">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                        <span class="ml-3 text-[10px] text-gray-400">app.agencyos.com/dashboard</span>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-4 gap-3">
                            @foreach ([['Revenue','₹8.4L','text-green-600'],['Clients','24','text-indigo-600'],['Tasks','58','text-amber-600'],['Pipeline','₹12.9L','text-purple-600']] as [$l,$v,$c])
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <div class="text-[9px] text-gray-400">{{ $l }}</div>
                                    <div class="text-sm font-bold {{ $c }}">{{ $v }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="rounded-lg border border-gray-100 p-3">
                            <div class="flex justify-between text-[10px] text-gray-400 mb-2"><span>Revenue (6 months)</span><span class="text-indigo-600">+32%</span></div>
                            <div class="flex items-end gap-1.5 h-20">
                                @foreach ([35, 45, 40, 60, 75, 90] as $h)
                                    <div class="flex-1 rounded-t bg-indigo-500/20" style="height: {{ $h }}%"></div>
                                @endforeach
                                <div class="flex-1 rounded-t bg-indigo-600" style="height: 100%"></div>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            @foreach ([['●','in_progress','Audit Google Ads structure','Sneha'],['●','todo','Launch Meta campaign','Rohan'],['●','done','Content calendar August','Priya']] as [$dot,$st,$title,$who])
                                <div class="flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $st === 'done' ? 'bg-green-500' : ($st === 'in_progress' ? 'bg-blue-500' : 'bg-gray-300') }}"></span>
                                    <span class="text-[11px] text-gray-700 flex-1 truncate">{{ $title }}</span>
                                    <span class="text-[9px] text-gray-400">{{ $who }}</span>
                                </div>
                            @endforeach
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
        </div>
    </header>

    <!-- Logo strip -->
    <section class="border-y border-gray-100 bg-gray-50/60">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <p class="text-center text-xs text-gray-400 uppercase tracking-widest mb-5">Trusted by growing agencies</p>
            <div class="flex flex-wrap justify-center gap-x-12 gap-y-4 text-gray-300 font-bold">
                @foreach (['UrbanKart','WellNest','FoodieExpress','Bloom Digital','Northstar Media','Peak & Co'] as $logo)
                    <span class="text-lg select-none">{{ $logo }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="max-w-7xl mx-auto px-6 py-20">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl sm:text-4xl font-black">Everything your agency needs to grow</h2>
            <p class="text-gray-500 mt-3">One platform replaces your Excel sheets, WhatsApp groups, and scattered tools.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ([
                ['users','Client Hub','Retainers, onboarding checklists, health scores, contracts and a branded client portal.'],
                ['check-circle','Task Engine','Kanban boards, recurring tasks, approvals, time tracking and workload views.'],
                ['target','Lead Pipeline','Native Lead365 sync, Meta Ads & form capture, proposals and win/loss tracking.'],
                ['banknotes','Finance & Invoicing','Invoicing via BikriBook, GST-ready PDFs, expenses and profitability margins.'],
                ['chart-bar','Reporting','Weekly/monthly client reports with auto-calculated metrics and one-click PDFs.'],
                ['bolt','Automation','Notify, create tasks and follow up automatically with a no-code rules engine.'],
            ] as [$icon,$title,$desc])
                <div class="rounded-2xl border border-gray-100 p-6 hover:shadow-lg hover:border-indigo-100 transition group">
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition">
                        <x-icon :name="$icon" class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold">{{ $title }}</h3>
                    <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- How it works -->
    <section id="how" class="bg-gray-50/70 py-20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h2 class="text-3xl sm:text-4xl font-black">Set up in under 10 minutes</h2>
                <p class="text-gray-500 mt-3">From signup to your first client invoice — no IT team required.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach ([
                    ['1','Create your workspace','Sign up, pick a subdomain and choose your plan or start with the free 14-day trial.'],
                    ['2','Invite your team','Add your ops manager, account managers and specialists with role-based access.'],
                    ['3','Win & deliver','Import leads from Lead365, send proposals, deliver projects and invoice via BikriBook.'],
                ] as [$num,$title,$desc])
                    <div class="relative bg-white rounded-2xl border border-gray-100 p-7">
                        <div class="w-9 h-9 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-sm mb-4">{{ $num }}</div>
                        <h3 class="font-bold">{{ $title }}</h3>
                        <p class="text-sm text-gray-500 mt-1.5">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="max-w-7xl mx-auto px-6 py-20">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl sm:text-4xl font-black">Loved by agency owners</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-5">
            @foreach ([
                ['Aarav S.','Founder, UrbanKart Agency','We replaced 4 tools and a dozen Excel sheets. Onboarding checklists alone saved us hours per client.'],
                ['Priya N.','Ops Manager, Bloom Digital','The client portal is a game-changer. Approvals that used to take days now happen in hours.'],
                ['Rohan M.','Account Manager, Northstar','Profitability per client finally makes sense. I know our margins before every renewal call.'],
            ] as [$name,$role,$quote])
                <div class="rounded-2xl border border-gray-100 p-6">
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

    <!-- Pricing -->
    <section id="pricing" class="bg-gray-50/70 py-20">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h2 class="text-3xl sm:text-4xl font-black">Simple, honest pricing</h2>
                <p class="text-gray-500 mt-3">Start free for 14 days. Upgrade when you're ready. Cancel anytime.</p>
            </div>
            <div x-data="{ yearly: false }" class="flex justify-center mb-10">
                <div class="bg-white rounded-full border border-gray-200 p-1 flex text-sm">
                    <button @click="yearly = false" :class="!yearly ? 'bg-indigo-600 text-white' : 'text-gray-500'" class="px-4 py-1.5 rounded-full font-medium">Monthly</button>
                    <button @click="yearly = true" :class="yearly ? 'bg-indigo-600 text-white' : 'text-gray-500'" class="px-4 py-1.5 rounded-full font-medium">Yearly <span class="text-xs opacity-80">-2 months free</span></button>
                </div>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach ($plans as $plan)
                    <div class="rounded-2xl border p-7 bg-white {{ $plan->slug === 'professional' ? 'border-indigo-600 ring-2 ring-indigo-600 relative' : 'border-gray-200' }}">
                        @if ($plan->slug === 'professional')
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs font-semibold px-3 py-1 rounded-full">Most popular</span>
                        @endif
                        <h3 class="font-bold text-gray-900">{{ $plan->name }}</h3>
                        <div class="mt-3 flex items-baseline gap-1">
                            <span class="text-4xl font-black" x-text="'₹' + (yearly ? {{ $plan->price_yearly }} : {{ $plan->price_monthly }}).toLocaleString('en-IN')"></span>
                            <span class="text-sm text-gray-400" x-text="yearly ? '/year' : '/month'"></span>
                        </div>
                        <ul class="mt-5 space-y-2.5 text-sm text-gray-600">
                            @foreach (($plan->features ?? []) as $feature)
                                <li class="flex gap-2.5">
                                    <svg class="w-4 h-4 text-green-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('register') }}" class="mt-6 block text-center rounded-xl py-2.5 text-sm font-semibold {{ $plan->slug === 'professional' ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                            Start free trial
                        </a>
                    </div>
                @endforeach
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
                        <span x-show="open === {{ $loop->index + 1 }}">−</span>
                    </button>
                    <div x-show="open === {{ $loop->index + 1 }}" x-cloak class="px-5 pb-4 text-sm text-gray-500">{{ $a }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Final CTA -->
    <section class="max-w-7xl mx-auto px-6 pb-20">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-16 text-center text-white">
            <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/10 rounded-full blur-2xl"></div>
            <h2 class="text-3xl sm:text-4xl font-black relative">Ready to run your agency on autopilot?</h2>
            <p class="text-indigo-100 mt-3 relative">Join agencies that traded chaos for one clean operating system.</p>
            <a href="{{ route('register') }}" class="relative inline-block mt-8 bg-white text-indigo-700 px-8 py-3.5 rounded-xl font-bold hover:bg-indigo-50 shadow-xl">
                Start your free 14-day trial
            </a>
            <div class="relative mt-4 text-xs text-indigo-200">No credit card · 2-minute setup</div>
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
                    <a href="{{ route('site.use-cases') }}" class="block hover:text-gray-900">Use cases</a>
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
