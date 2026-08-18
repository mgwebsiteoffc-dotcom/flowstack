{{-- Canonical public site navigation — used on EVERY public page so the menu never changes between pages. --}}
<nav class="sticky top-0 z-40 bg-white/85 backdrop-blur border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight">Task<span class="text-indigo-600">365</span></a>
        <div class="hidden lg:flex items-center gap-6 text-sm text-gray-600">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="hover:text-gray-900 flex items-center gap-1 {{ request()->routeIs('site.feature*', 'site.features') ? 'text-indigo-600 font-medium' : '' }}">Features
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" class="absolute left-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-100 py-2">
                    <a href="{{ route('site.features') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 font-medium {{ request()->routeIs('site.features') ? 'text-indigo-600' : '' }}">All features</a>
                    @foreach (['client-management','project-tasks','leads-crm','finance-invoicing','reporting','automation'] as $slug)
                        <a href="{{ route('site.feature', $slug) }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('site.feature', $slug) ? 'text-indigo-600' : '' }}">{{ ucwords(str_replace('-', ' ', $slug)) }}</a>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('site.use-cases') }}" class="hover:text-gray-900 {{ request()->routeIs('site.use-case*') ? 'text-indigo-600 font-medium' : '' }}">Use cases</a>
            <a href="{{ route('site.integrations') }}" class="hover:text-gray-900 {{ request()->routeIs('site.integrations') ? 'text-indigo-600 font-medium' : '' }}">Integrations</a>
            <a href="{{ route('pricing') }}" class="hover:text-gray-900 {{ request()->routeIs('pricing') ? 'text-indigo-600 font-medium' : '' }}">Pricing</a>
            <a href="{{ route('site.resources') }}" class="hover:text-gray-900 {{ request()->routeIs('site.resources') ? 'text-indigo-600 font-medium' : '' }}">Resources</a>
            <a href="{{ route('tools.index') }}" class="hover:text-gray-900 {{ request()->routeIs('tools.*') ? 'text-indigo-600 font-medium' : '' }}">Free Tools</a>
            <a href="{{ route('blog.index') }}" class="hover:text-gray-900 {{ request()->routeIs('blog.*') ? 'text-indigo-600 font-medium' : '' }}">Blog</a>
            <a href="{{ route('contact') }}" class="hover:text-gray-900 {{ request()->routeIs('contact') ? 'text-indigo-600 font-medium' : '' }}">Contact</a>
        </div>
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 font-medium hidden sm:block">Log in</a>
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Start for free</a>
        </div>
    </div>
</nav>
