<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency OS — Run your agency on autopilot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white text-gray-900">
    <nav class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">
        <div class="text-2xl font-black tracking-tight">Agency<span class="text-indigo-600">OS</span></div>
        <div class="flex items-center gap-6 text-sm">
            <a href="{{ route('pricing') }}" class="text-gray-600 hover:text-gray-900">Pricing</a>
            <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Sign in</a>
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Start free trial</a>
        </div>
    </nav>

    <header class="max-w-5xl mx-auto px-6 pt-20 pb-16 text-center">
        <div class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 text-xs font-medium px-3 py-1 rounded-full mb-6">
            Built for agencies with 5–20 people
        </div>
        <h1 class="text-5xl sm:text-6xl font-black tracking-tight leading-tight">
            Your agency runs on<br>
            <span class="text-indigo-600">one operating system</span>
        </h1>
        <p class="text-lg text-gray-500 mt-6 max-w-2xl mx-auto">
            Clients, projects, tasks, leads, invoices and reports — all in one place.
            Say goodbye to Excel sheets and WhatsApp pings.
        </p>
        <div class="mt-8 flex items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-indigo-700">Start 14-day free trial</a>
            <a href="{{ route('pricing') }}" class="px-6 py-3 rounded-xl border border-gray-300 font-semibold hover:bg-gray-50">View pricing</a>
        </div>
    </header>

    <section class="max-w-6xl mx-auto px-6 py-14 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach ([
            ['🤝', 'Client Hub', 'Retainers, onboarding checklists, health scores and portals.'],
            ['✅', 'Task Engine', 'Boards, recurring tasks, approvals and time tracking.'],
            ['🎯', 'Lead Pipeline', 'Native Lead365 sync with Meta Ads & form automation.'],
            ['💰', 'Finance', 'Invoicing via BikriBook, expenses and profitability.'],
        ] as [$icon, $title, $desc])
            <div class="rounded-2xl border border-gray-200 p-6">
                <div class="text-3xl mb-3">{{ $icon }}</div>
                <h3 class="font-bold">{{ $title }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $desc }}</p>
            </div>
        @endforeach
    </section>

    <section class="bg-gray-50 py-16">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-black">Every agency workflow, covered</h2>
            <div class="grid sm:grid-cols-3 gap-5 mt-10 text-left">
                @foreach ([
                    ['📊', 'Operations', 'Dashboards, automation rules, reporting and a knowledge base.'],
                    ['🔗', 'Integrations', 'Lead365 for leads, BikriBook for invoices, Razorpay for billing.'],
                    ['🏢', 'Client portal', 'Approvals, requests, reports and invoices for your clients.'],
                ] as [$icon, $title, $desc])
                    <div class="bg-white rounded-2xl border border-gray-200 p-6">
                        <div class="text-2xl mb-2">{{ $icon }}</div>
                        <h3 class="font-semibold">{{ $title }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="max-w-7xl mx-auto px-6 py-10 text-center text-sm text-gray-400">
        © {{ date('Y') }} Agency OS · <a href="{{ route('pricing') }}" class="hover:text-gray-600">Pricing</a> · <a href="{{ route('login') }}" class="hover:text-gray-600">Sign in</a> · <a href="{{ route('super-admin.login') }}" class="hover:text-gray-600">Super Admin</a>
    </footer>
</body>
</html>
