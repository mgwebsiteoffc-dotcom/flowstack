@extends('layouts.auth')
@section('title', 'Start your free trial')
@section('content')
    <h1 class="text-xl font-bold text-gray-900 mb-1">Start your 14-day free trial</h1>
    <p class="text-sm text-gray-500 mb-6">No credit card required. Cancel anytime.</p>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4"
          x-data="{ slug: '' }" @submit="document.getElementById('subdomain').value = slug">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Your name</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Agency name</label>
            <input type="text" name="agency_name" value="{{ old('agency_name') }}" required
                   x-model="slug" x-on:input="slug = slug.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Your subdomain</label>
            <div class="flex items-center gap-2">
                <input type="text" name="subdomain" id="subdomain" value="{{ old('subdomain') }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <span class="text-sm text-gray-400 whitespace-nowrap">.{{ config('tenancy.tenant_domain') }}</span>
            </div>
            <p class="text-xs text-gray-400 mt-1" x-text="'Preview: ' + (slug || 'your-agency') + '.{{ config('tenancy.tenant_domain') }}'"></p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Work email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm</label>
                <input type="password" name="password_confirmation" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>
        <label class="flex items-start gap-2 text-xs text-gray-500">
            <input type="checkbox" name="terms" value="1" required class="mt-0.5">
            I agree to the Terms of Service and Privacy Policy.
        </label>
        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg py-2.5 text-sm font-medium">
            Create my workspace
        </button>
    </form>

    <p class="text-sm text-gray-500 mt-6 text-center">
        Already have an account? <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Sign in</a>
    </p>
@endsection
