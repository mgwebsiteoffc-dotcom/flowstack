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

    @include('components.site-nav')

    @yield('content')

    @include('components.site-footer')
    <!-- Mobile sticky CTA -->
    <div class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-white/95 backdrop-blur border-t border-gray-100 p-3 flex gap-3">
        <a href="{{ route('login') }}" class="flex-1 text-center px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 text-sm font-semibold">Log in</a>
        <a href="{{ route('register') }}" class="flex-1 text-center px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold">Start free trial</a>
    </div>
    @stack('scripts')
</body>
</html>
