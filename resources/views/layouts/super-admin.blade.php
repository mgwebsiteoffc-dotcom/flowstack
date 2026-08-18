<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <title>@yield('title', 'Super Admin') · Task365</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="bg-gray-900 min-h-screen">
    <div class="flex min-h-screen">
        <aside class="w-56 bg-gray-950 text-gray-300 p-4 shrink-0">
            <div class="text-white font-black text-lg mb-6">Task<span class="text-indigo-400">365</span> <span class="text-xs bg-indigo-500 text-white px-2 py-0.5 rounded">super</span></div>
            <nav class="space-y-1 text-sm">
                <a href="{{ route('super-admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('super-admin.dashboard') ? 'bg-gray-800 text-white' : '' }}">Dashboard</a>
                <a href="{{ route('super-admin.tenants.index') }}" class="block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('super-admin.tenants*') ? 'bg-gray-800 text-white' : '' }}">Tenants</a>
                <a href="{{ route('super-admin.plans.index') }}" class="block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('super-admin.plans*') ? 'bg-gray-800 text-white' : '' }}">Plans</a>
                <a href="{{ route('super-admin.payments') }}" class="block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('super-admin.payments') ? 'bg-gray-800 text-white' : '' }}">Payments</a>
                <a href="{{ route('super-admin.roles.index') }}" class="block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('super-admin.roles*') ? 'bg-gray-800 text-white' : '' }}">Role menus</a>
                <a href="{{ route('super-admin.blog.index') }}" class="block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('super-admin.blog*') ? 'bg-gray-800 text-white' : '' }}">Blog</a>
                <a href="{{ route('super-admin.tracking.index') }}" class="block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('super-admin.tracking*') ? 'bg-gray-800 text-white' : '' }}">Tracking & pixels</a>
            </nav>
            <form method="POST" action="{{ route('super-admin.logout') }}" class="mt-8">@csrf
                <button class="text-xs text-gray-500 hover:text-gray-300">Logout</button>
            </form>
        </aside>
        <main class="flex-1 p-8">
            @include('components.alert')
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
