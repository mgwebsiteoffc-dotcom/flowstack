<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title>@yield('title', 'Task365')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <x-brand-head />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
    <x-tracking placement="head" />
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <x-tracking placement="body" />
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        <!-- Left brand panel -->
        <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-600 to-purple-700 text-white p-12">
            <x-tracking placement="body" />
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-40 -left-20 w-72 h-72 bg-purple-400/20 rounded-full blur-3xl"></div>

            <a href="{{ route('home') }}" class="relative flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center font-black text-lg">T</span>
                <span class="text-xl font-black tracking-tight">Task<span class="text-white/70">365</span></span>
            </a>

            <div class="relative max-w-md">
                <h2 class="text-3xl font-black leading-tight">Run your agency on one operating system</h2>
                <p class="text-indigo-100/90 mt-3 text-sm leading-relaxed">Clients, projects, leads, invoices, reports and your client portal — beautifully organized in one place.</p>

                <!-- SVG illustration -->
                <svg class="mt-10 w-full max-w-md" viewBox="0 0 440 260" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <!-- window -->
                    <rect x="20" y="20" width="400" height="220" rx="16" fill="white" fill-opacity="0.12" stroke="white" stroke-opacity="0.25"/>
                    <circle cx="44" cy="44" r="5" fill="#f87171"/>
                    <circle cx="66" cy="44" r="5" fill="#fbbf24"/>
                    <circle cx="88" cy="44" r="5" fill="#34d399"/>
                    <line x1="20" y1="62" x2="420" y2="62" stroke="white" stroke-opacity="0.2"/>
                    <!-- sidebar -->
                    <rect x="20" y="62" width="72" height="178" rx="0" fill="white" fill-opacity="0.06"/>
                    <rect x="32" y="84" width="48" height="10" rx="5" fill="white" fill-opacity="0.35"/>
                    <rect x="32" y="104" width="48" height="10" rx="5" fill="white" fill-opacity="0.18"/>
                    <rect x="32" y="124" width="48" height="10" rx="5" fill="white" fill-opacity="0.18"/>
                    <rect x="32" y="144" width="48" height="10" rx="5" fill="white" fill-opacity="0.18"/>
                    <!-- stat cards -->
                    <rect x="112" y="84" width="90" height="46" rx="8" fill="white" fill-opacity="0.14"/>
                    <rect x="122" y="94" width="40" height="6" rx="3" fill="white" fill-opacity="0.35"/>
                    <rect x="122" y="106" width="56" height="10" rx="3" fill="#a5b4fc"/>
                    <rect x="214" y="84" width="90" height="46" rx="8" fill="white" fill-opacity="0.14"/>
                    <rect x="224" y="94" width="40" height="6" rx="3" fill="white" fill-opacity="0.35"/>
                    <rect x="224" y="106" width="56" height="10" rx="3" fill="#6ee7b7"/>
                    <rect x="316" y="84" width="90" height="46" rx="8" fill="white" fill-opacity="0.14"/>
                    <rect x="326" y="94" width="40" height="6" rx="3" fill="white" fill-opacity="0.35"/>
                    <rect x="326" y="106" width="56" height="10" rx="3" fill="#fcd34d"/>
                    <!-- chart -->
                    <rect x="112" y="146" width="196" height="74" rx="8" fill="white" fill-opacity="0.1"/>
                    <polyline points="130,200 170,180 210,190 250,160 290,168" stroke="#a5b4fc" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="130,202 170,182 210,192 250,162 290,170" stroke="white" stroke-opacity="0.15" stroke-width="6" fill="none" stroke-linecap="round"/>
                    <!-- tasks -->
                    <rect x="322" y="146" width="84" height="74" rx="8" fill="white" fill-opacity="0.1"/>
                    <rect x="334" y="158" width="56" height="10" rx="5" fill="white" fill-opacity="0.3"/>
                    <rect x="334" y="176" width="56" height="10" rx="5" fill="white" fill-opacity="0.2"/>
                    <rect x="334" y="194" width="40" height="10" rx="5" fill="white" fill-opacity="0.2"/>
                </svg>

                <div class="mt-10 space-y-3">
                    @foreach (['Proposals, invoices and profitability in one dashboard', 'Client portal for approvals, reports and requests', 'Lead365 + BikriBook + Razorpay integrations built in'] as $bullet)
                        <div class="flex items-start gap-3 text-sm text-indigo-50">
                            <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            {{ $bullet }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative text-xs text-indigo-200/80">
                © {{ date('Y') }} Task365 · A product by Akestech Infotech Pvt Ltd · <a href="{{ route('pricing') }}" class="hover:text-white">Pricing</a> · <a href="{{ route('blog.index') }}" class="hover:text-white">Blog</a>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">
                <div class="lg:hidden mb-8 text-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                        <span class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-lg">T</span>
                        <span class="text-xl font-black tracking-tight text-gray-900">Task<span class="text-indigo-600">365</span></span>
                    </a>
                </div>
                <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/60 border border-gray-100 p-8">
                    @include('components.alert')
                    @yield('content')
                </div>
                <p class="text-center text-xs text-gray-400 mt-6">
                    © {{ date('Y') }} Task365 · A product by Akestech Infotech Pvt Ltd · <a href="{{ route('home') }}" class="hover:text-gray-600">Home</a> · <a href="{{ route('pricing') }}" class="hover:text-gray-600">Pricing</a> · <a href="{{ route('blog.index') }}" class="hover:text-gray-600">Blog</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
