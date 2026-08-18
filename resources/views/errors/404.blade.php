<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 · Not found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <div class="text-7xl font-black text-gray-300">404</div>
        <h1 class="text-2xl font-bold text-gray-900 mt-4">Page not found</h1>
        <p class="text-sm text-gray-500 mt-2">{{ $message ?? 'The page you are looking for doesn\'t exist or was moved.' }}</p>
        <div class="mt-6 flex items-center justify-center gap-3">
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="inline-block bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700">← Back to {{ auth()->check() ? 'dashboard' : 'home' }}</a>
        </div>
        <div class="text-xs text-gray-400 mt-6">Task365 · <a href="{{ route('contact') }}" class="hover:text-gray-600 underline">Contact support</a></div>
    </div>
</body>
</html>
