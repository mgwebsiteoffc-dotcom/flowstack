<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Portal Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="text-3xl font-black text-gray-900">Client <span class="text-indigo-600">Portal</span></div>
            <p class="text-sm text-gray-500 mt-1">Sign in to view your projects, reports and invoices</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-8">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('portal.login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg py-2.5 text-sm font-medium">Sign in</button>
            </form>
        </div>
    </div>
</body>
</html>
