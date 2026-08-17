@extends('layouts.auth')
@section('title', 'Reset password')
@section('content')
    <h1 class="text-xl font-bold text-gray-900 mb-1">Forgot your password?</h1>
    <p class="text-sm text-gray-500 mb-6">Enter your email and we'll send you a reset link.</p>
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg py-2.5 text-sm font-medium">Send reset link</button>
    </form>
    <p class="text-sm text-gray-500 mt-6 text-center"><a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Back to login</a></p>
@endsection
