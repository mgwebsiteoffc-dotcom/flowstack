<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <title>@yield('title', 'Client Portal') · {{ auth('portal')->user()?->client?->company_name ?? 'Client Portal' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ mobileOpen: false }">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden text-gray-500"><x-icon name="menu" class="w-4 h-4 inline-block" /></button>
                <span class="font-bold text-gray-900">{{ auth('portal')->user()?->client?->company_name }}</span>
                <span class="hidden sm:inline text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">Client Portal</span>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <span class="hidden sm:inline text-gray-500">{{ auth('portal')->user()?->name }}</span>
                <form method="POST" action="{{ route('portal.logout') }}">@csrf
                    <button class="text-gray-500 hover:text-gray-800">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="lg:hidden mb-4" x-show="mobileOpen" x-cloak>
            @include('components.portal-nav')
        </div>
        <div class="flex gap-6">
            <aside class="hidden lg:block w-52 shrink-0">
                @include('components.portal-nav')
            </aside>
            <main class="flex-1 min-w-0">
                @include('components.alert')
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
