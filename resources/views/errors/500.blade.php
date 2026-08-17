<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 · Server error</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <div class="text-7xl font-black text-gray-300">500</div>
        <h1 class="text-2xl font-bold text-gray-900 mt-4">Something went wrong</h1>
        <p class="text-sm text-gray-500 mt-2">An unexpected error occurred. Our team has been notified.</p>
        <a href="{{ route('dashboard') }}" class="inline-block mt-6 bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium">← Back to dashboard</a>
    </div>
</body>
</html>
