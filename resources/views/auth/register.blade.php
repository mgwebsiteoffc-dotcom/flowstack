@extends('layouts.auth')
@section('title', 'Start your free trial')
@section('content')
<h1 class="text-2xl font-bold text-gray-900">Create your workspace</h1>
<p class="text-sm text-gray-500 mt-1 mb-6">Start your 14-day free trial. No credit card required.</p>

<form method="POST" action="{{ route('register.store') }}" class="space-y-4" x-data="{ slug: '', mode: 'trial' }"
      @submit="document.getElementById('subdomain').value = slug">
    @csrf
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Your name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Aarav Sharma"
                   class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Work email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@agency.com"
                   class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Agency name</label>
        <input type="text" name="agency_name" value="{{ old('agency_name') }}" required
               x-model="slug" x-on:input="slug = slug.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')"
               placeholder="Your agency name"
               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Your workspace subdomain</label>
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m-17.432 0A8.959 8.959 0 013 12c0-.778.099-1.533.284-2.253"/></svg>
                </span>
                <input type="text" name="subdomain" id="subdomain" value="{{ old('subdomain') }}" required
                       class="w-full rounded-xl border border-gray-300 pl-10 pr-4 py-3 text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <span class="text-sm text-gray-400 whitespace-nowrap">.{{ config('tenancy.tenant_domain') }}</span>
        </div>
        <p class="text-xs text-gray-400 mt-1.5" x-text="'Preview: ' + (slug || 'your-agency') + '.{{ config('tenancy.tenant_domain') }}'"></p>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <input type="password" name="password" required autocomplete="new-password" placeholder="••••••••"
                   class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
            <input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••"
                   class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Billing</label>
        <div class="grid grid-cols-2 gap-3">
            <label class="flex items-start gap-3 border rounded-xl px-4 py-3 cursor-pointer transition hover:border-gray-300"
                   :class="mode === 'trial' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-200'">
                <input type="radio" name="billing_mode" value="trial" x-model="mode" class="mt-1">
                <span>
                    <span class="text-sm font-semibold text-gray-800 block">Free trial</span>
                    <span class="text-xs text-gray-400">14 days · no card</span>
                </span>
            </label>
            <label class="flex items-start gap-3 border rounded-xl px-4 py-3 cursor-pointer transition hover:border-gray-300"
                   :class="mode === 'plan' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-200'">
                <input type="radio" name="billing_mode" value="plan" x-model="mode" class="mt-1">
                <span>
                    <span class="text-sm font-semibold text-gray-800 block">Paid plan</span>
                    <span class="text-xs text-gray-400">Start billing now</span>
                </span>
            </label>
        </div>
        <select name="plan_id" x-show="mode === 'plan'" x-cloak class="mt-3 w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            <option value="">Select a plan…</option>
            @foreach ($plans as $plan)
                <option value="{{ $plan->id }}">{{ $plan->name }} — ₹{{ number_format($plan->price_monthly) }}/month</option>
            @endforeach
        </select>
    </div>

    <label class="flex items-start gap-2 text-xs text-gray-500 cursor-pointer">
        <input type="checkbox" name="terms" value="1" required class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        I agree to the <span class="underline">Terms of Service</span> and <span class="underline">Privacy Policy</span>.
    </label>

    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 text-sm font-semibold shadow-lg shadow-indigo-200 transition">
        Create my workspace
    </button>
</form>

<div class="mt-6 pt-6 border-t border-gray-100 text-center text-sm text-gray-500">
    Already have an account?
    <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-semibold">Sign in</a>
</div>
@endsection
