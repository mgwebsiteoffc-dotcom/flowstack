@extends('layouts.auth')
@section('title', 'Accept invitation')
@section('content')
    <h1 class="text-xl font-bold text-gray-900 mb-1">You're invited 🎉</h1>
    <p class="text-sm text-gray-500 mb-6">Set your password to join <strong>{{ $user->tenant?->name ?? 'the team' }}</strong>.</p>
    <form method="POST" action="{{ route('onboarding.invite-accept.store', $token) }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" value="{{ $user->email }}" disabled class="w-full rounded-lg bg-gray-50 border border-gray-300 px-3 py-2 text-sm text-gray-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
            <input type="password" name="password_confirmation" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg py-2.5 text-sm font-medium">Join workspace</button>
    </form>
@endsection
