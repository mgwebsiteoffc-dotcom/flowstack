<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-6">
            <div class="text-2xl font-black text-white">Task<span class="text-indigo-400">365</span></div>
            <p class="text-xs text-gray-500 mt-1">Super admin panel</p>
        </div>
        <div class="bg-gray-900 rounded-2xl border border-gray-800 p-8">
            @if (empty($hasAdmins))
                <div class="bg-amber-500/10 border border-amber-500/30 text-amber-400 rounded-lg px-4 py-3 mb-4 text-xs">
                    <strong>No super admin exists yet.</strong> Run:
                    <code class="block mt-1 bg-gray-800 rounded px-1.5 py-0.5">php artisan db:seed --class=SuperAdminSeeder</code>
                    (uses SUPER_ADMIN_EMAIL / SUPER_ADMIN_PASSWORD from .env; defaults <code>superadmin@task365.test / ChangeMe123!</code>)
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg px-4 py-3 mb-4 text-sm">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('super-admin.login.store') }}" class="space-y-4">
                @csrf
                <input type="email" name="email" placeholder="Email" required autofocus class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500">
                <input type="password" name="password" placeholder="Password" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500">
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg py-2.5 text-sm font-medium">Sign in</button>
            </form>
        </div>
    </div>
</body>
</html>
