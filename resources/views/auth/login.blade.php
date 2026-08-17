@extends('layouts.auth')
@section('title', 'Login')
@section('content')
    <h1 class="text-xl font-bold text-gray-900 mb-1">Welcome back</h1>
    <p class="text-sm text-gray-500 mb-6">Sign in to your agency workspace.</p>

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-gray-600">
                <input type="checkbox" name="remember" class="rounded"> Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-indigo-600 hover:underline">Forgot password?</a>
        </div>
        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg py-2.5 text-sm font-medium">
            Sign in
        </button>
    </form>

    <p class="text-sm text-gray-500 mt-6 text-center">
        New to Agency OS? <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">Start your free trial</a>
    </p>
@endsection
