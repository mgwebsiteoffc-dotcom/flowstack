@extends('layouts.auth')
@section('title', 'Sign in to Task365')
@section('content')
<h1 class="text-2xl font-bold text-gray-900">Welcome back</h1>
<p class="text-sm text-gray-500 mt-1 mb-6">Sign in to your agency workspace.</p>

@if (empty($hasUsers))
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 mb-5 text-xs">
        <strong>No team users exist yet.</strong> Either <a href="{{ route('register') }}" class="underline">register a workspace</a> or seed the demo:
        <code class="block mt-1 bg-amber-100 rounded px-1.5 py-0.5">php artisan db:seed --class=DemoTenantSeeder</code>
        (then log in with <code>admin@demo.com / password123</code>)
    </div>
@endif

<form method="POST" action="{{ route('login.store') }}" class="space-y-5">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Work email</label>
        <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4.5 h-4.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
            </span>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@agency.com"
                   class="w-full rounded-xl border border-gray-300 pl-10 pr-4 py-3 text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
        <div class="relative" x-data="{ show: false }">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4.5 h-4.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            </span>
            <input :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••"
                   class="w-full rounded-xl border border-gray-300 pl-10 pr-11 py-3 text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <button type="button" @click="show = !show" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" title="Show password">
                <svg x-show="!show" class="w-4.5 h-4.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <svg x-show="show" x-cloak class="w-4.5 h-4.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
            </button>
        </div>
    </div>

    <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2 text-gray-600 cursor-pointer">
            <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            Remember me
        </label>
        <a href="{{ route('password.request') }}" class="text-indigo-600 hover:underline font-medium">Forgot password?</a>
    </div>

    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 text-sm font-semibold shadow-lg shadow-indigo-200 transition">
        Sign in
    </button>
</form>

<div class="mt-6 pt-6 border-t border-gray-100 text-center text-sm text-gray-500">
    New to Task365?
    <a href="{{ route('register') }}" class="text-indigo-600 hover:underline font-semibold">Start your free trial</a>
</div>

<div class="mt-6 pt-5 border-t border-gray-100">
    <div class="flex items-center gap-3">
        <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
            <x-icon name="device-phone-mobile" class="w-5 h-5" />
        </span>
        <div>
            <div class="text-sm font-semibold text-gray-900">Get the Task365 app</div>
            <p class="text-xs text-gray-500 leading-snug">Install Task365 on your phone or desktop for one-tap access — just like a native app.</p>
        </div>
    </div>
    <x-pwa-install label="Install the app" block class="mt-3" />
</div>
@endsection
