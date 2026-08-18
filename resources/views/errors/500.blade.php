<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title>Something went wrong · Agency OS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <div class="text-7xl font-black text-gray-300 mb-4">500</div>
        <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Something went wrong</h1>
        <p class="text-gray-500 mt-2 text-sm">Our team has been notified and is working on it. Please try again in a few minutes.</p>
        <div class="mt-6 flex items-center justify-center gap-3">
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700">← Back to {{ auth()->check() ? 'dashboard' : 'home' }}</a>
            <button onclick="location.reload()" class="px-6 py-2.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Try again</button>
        </div>
        <div class="text-xs text-gray-400 mt-6">
            Agency OS · <a href="{{ route('contact') }}" class="hover:text-gray-600 underline">Contact support</a>
        </div>
    </div>
</body>
</html>
