<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set your portal password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-xl font-bold text-gray-900 mb-1">Welcome to the portal <x-icon name="sparkles" class="w-4 h-4 inline-block" /></h1>
            <p class="text-sm text-gray-500 mb-6">Set a password to access your client portal.</p>
            <form method="POST" action="{{ route('portal.set-password.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                    <input type="password" name="password" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                    <input type="password" name="password_confirmation" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                @if ($errors->any())
                    <div class="text-sm text-red-600">{{ $errors->first() }}</div>
                @endif
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg py-2.5 text-sm font-medium">Set password & enter portal</button>
            </form>
        </div>
    </div>
</body>
</html>
