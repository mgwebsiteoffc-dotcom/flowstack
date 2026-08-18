<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <x-pwa-meta />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Task365') · {{ app('currentTenant')?->name ?? 'Task365' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body x-data="{ sidebarOpen: window.innerWidth >= 768, isDesktop: window.matchMedia('(min-width: 768px)').matches, moreOpen: false }"
      x-init="window.addEventListener('resize', () => { isDesktop = window.matchMedia('(min-width: 768px)').matches })"
      class="bg-gray-100 min-h-screen">
    {{-- Mobile drawer backdrop --}}
    <div x-show="sidebarOpen && !isDesktop" x-cloak @click="sidebarOpen = false" x-transition.opacity
         class="fixed inset-0 bg-gray-900/50 z-30"></div>
    @include('components.sidebar')
    <div :class="(sidebarOpen || !isDesktop) ? 'md:ml-64' : 'md:ml-0'"
         class="transition-all duration-200 min-h-screen flex flex-col">
        @include('components.topbar')
        @if (session('impersonator_admin'))
            <div class="bg-purple-600 text-white text-xs px-6 py-2 flex items-center justify-center gap-3">
                <span><x-icon name="eye" class="w-4 h-4 inline-block" /> You are impersonating this workspace as a super admin.</span>
                <form method="POST" action="{{ route('super-admin.impersonate.stop') }}">@csrf
                    <button class="font-bold underline hover:no-underline">Exit impersonation</button>
                </form>
            </div>
        @endif
        @php $__tenant = app('currentTenant'); @endphp
        @if ($__tenant && $__tenant->is_trial && $__tenant->trial_ends_at)
            <div class="bg-amber-500 text-white text-xs px-6 py-2 flex items-center justify-center gap-2">
                <x-icon name="hourglass" class="w-4 h-4" /><span>{{ $__tenant->trialDaysRemaining() }} days remaining in your free trial</span>
                <a href="{{ route('upgrade') }}" class="font-bold underline hover:no-underline">Upgrade →</a>
            </div>
        @endif
        <main class="p-4 sm:p-6 flex-1 pb-24 md:pb-6">
            @include('components.alert')
            @yield('content')
        </main>
        <footer class="px-4 sm:px-6 pb-20 md:pb-4 text-xs text-gray-400">
            &copy; {{ date('Y') }} {{ app('currentTenant')?->name ?? 'Task365' }} · A product by Akestech Infotech Pvt Ltd · <a href="{{ route('upgrade') }}" class="hover:text-gray-600">Subscription</a>
        </footer>
    </div>
    @include('components.mobile-nav')
    @include('components.toast')
    @stack('scripts')
</body>
</html>
