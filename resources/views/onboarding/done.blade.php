@extends('layouts.app')
@section('title', 'You\'re all set')
@section('content')
<div class="max-w-2xl mx-auto text-center py-10">
    <div class="text-6xl mb-4">🎉</div>
    <h1 class="text-2xl font-bold text-gray-900">Welcome to {{ $tenant->name }}!</h1>
    <p class="text-gray-500 mt-2 text-sm">Your workspace is ready. Here's what you can do next:</p>
    <div class="grid sm:grid-cols-2 gap-4 mt-8 text-left">
        <a href="{{ route('clients.create') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 transition">
            <div class="text-2xl mb-2">🤝</div>
            <div class="font-semibold text-sm">Add your first client</div>
            <div class="text-xs text-gray-500 mt-1">Create a client and start their onboarding checklist.</div>
        </a>
        <a href="{{ route('leads.create') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 transition">
            <div class="text-2xl mb-2">🎯</div>
            <div class="font-semibold text-sm">Add a lead</div>
            <div class="text-xs text-gray-500 mt-1">Or connect Lead365 to receive leads automatically.</div>
        </a>
        <a href="{{ route('team.index') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 transition">
            <div class="text-2xl mb-2">👥</div>
            <div class="font-semibold text-sm">Invite your team</div>
            <div class="text-xs text-gray-500 mt-1">Bring your teammates on board.</div>
        </a>
        <a href="{{ route('dashboard') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 transition">
            <div class="text-2xl mb-2">📊</div>
            <div class="font-semibold text-sm">Go to dashboard</div>
            <div class="text-xs text-gray-500 mt-1">Start your day with a full overview.</div>
        </a>
    </div>
    <a href="{{ route('dashboard') }}" class="inline-block mt-8 bg-indigo-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-indigo-700">Go to Dashboard</a>
</div>
@endsection
