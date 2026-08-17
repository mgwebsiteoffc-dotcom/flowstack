<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <title>@yield('title', 'Agency OS')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="text-3xl font-black tracking-tight text-gray-900">Agency<span class="text-indigo-600">OS</span></div>
            <p class="text-sm text-gray-500 mt-1">The agency operating system</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-8">
            @include('components.alert')
            @yield('content')
        </div>
    </div>
</body>
</html>
