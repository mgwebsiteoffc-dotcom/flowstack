<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <x-brand-head />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <x-seo :title="$seo['title'] ?? null" :description="$seo['description'] ?? null" :jsonLd="$seo['jsonLd'] ?? null" />
    <x-tracking placement="head" />
    @stack('styles')
</head>
<body class="bg-white text-gray-900 antialiased">
    <x-tracking placement="body" />

    <nav class="sticky top-0 z-40 bg-white/85 backdrop-blur border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight">Agency<span class="text-indigo-600">OS</span></a>
            <div class="hidden lg:flex items-center gap-6 text-sm text-gray-600">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="hover:text-gray-900 flex items-center gap-1">Features
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" class="absolute left-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-100 py-2">
                        <a href="{{ route('site.features') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 font-medium">All features</a>
                        @foreach (['client-management','project-tasks','leads-crm','finance-invoicing','reporting','automation'] as $slug)
                            <a href="{{ route('site.feature', $slug) }}" class="block px-4 py-2 text-sm hover:bg-gray-50">{{ ucwords(str_replace('-', ' ', $slug)) }}</a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('site.use-cases') }}" class="hover:text-gray-900">Use cases</a>
                <a href="{{ route('site.integrations') }}" class="hover:text-gray-900">Integrations</a>
                <a href="{{ route('pricing') }}" class="hover:text-gray-900">Pricing</a>
                <a href="{{ route('site.resources') }}" class="hover:text-gray-900">Resources</a>
                <a href="{{ route('tools.index') }}" class="hover:text-gray-900">Free Tools</a>
                <a href="{{ route('blog.index') }}" class="hover:text-gray-900">Blog</a>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 font-medium hidden sm:block">Sign in</a>
                <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Start free trial</a>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="border-t border-gray-100 bg-gray-50/50">
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
    <!-- Mobile sticky CTA -->
    <div class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-white/95 backdrop-blur border-t border-gray-100 p-3 flex gap-3">
        <a href="{{ route('login') }}" class="flex-1 text-center px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 text-sm font-semibold">Log in</a>
        <a href="{{ route('register') }}" class="flex-1 text-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold">Start free trial</a>
    </div>
    @stack('scripts')
</body>
</html>
