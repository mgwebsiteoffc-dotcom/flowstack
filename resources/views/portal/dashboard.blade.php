@extends('layouts.portal')
@section('title', 'Dashboard')
@section('content')
<div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 text-white mb-6">
    <h1 class="text-2xl font-bold">Hello, {{ auth('portal')->user()->name }} 👋</h1>
    <p class="text-indigo-100 mt-1 text-sm">Welcome to the {{ $client->company_name }} portal. Here's what's happening with your account.</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-100 p-5"><div class="text-2xl font-bold text-gray-900">{{ $stats['active_projects'] }}</div><div class="text-xs text-gray-400 mt-1">Active projects</div></div>
    <div class="bg-white rounded-xl border border-gray-100 p-5"><div class="text-2xl font-bold text-gray-900">{{ $stats['pending_approvals'] }}</div><div class="text-xs text-gray-400 mt-1">Awaiting your approval</div></div>
    <div class="bg-white rounded-xl border border-gray-100 p-5"><div class="text-2xl font-bold text-gray-900">{{ $stats['open_requests'] }}</div><div class="text-xs text-gray-400 mt-1">Open requests</div></div>
    <div class="bg-white rounded-xl border border-gray-100 p-5"><div class="text-2xl font-bold text-gray-900">{{ $stats['recent_reports'] }}</div><div class="text-xs text-gray-400 mt-1">Shared reports</div></div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">Projects</h3>
        @forelse ($projects as $project)
            <a href="{{ route('portal.projects.show', $project) }}" class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                <span class="text-sm text-gray-800 flex-1">{{ $project->name }}</span>
                <span class="text-xs bg-gray-100 rounded-full px-2 py-0.5 text-gray-600 capitalize">{{ str_replace('_', ' ', $project->status) }}</span>
            </a>
        @empty
            <p class="text-sm text-gray-400 text-center py-4">No projects yet.</p>
        @endforelse
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">Recent reports</h3>
        @forelse ($reports as $report)
            <a href="{{ route('portal.reports.show', $report) }}" class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                <span class="text-sm text-gray-800 flex-1">{{ $report->title }}</span>
                <span class="text-xs text-gray-400">{{ $report->shared_at?->format('d M') }}</span>
            </a>
        @empty
            <p class="text-sm text-gray-400 text-center py-4">No shared reports yet.</p>
        @endforelse
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-100 p-5 mt-6">
    <h3 class="font-semibold text-gray-900 mb-4">Your recent requests</h3>
    @forelse ($requests as $request)
        <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
            <span class="text-sm text-gray-800 flex-1">{{ $request->title }}</span>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $request->status === 'completed' ? 'bg-green-100 text-green-700' : ($request->status === 'open' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">{{ str_replace('_', ' ', $request->status) }}</span>
        </div>
    @empty
        <p class="text-sm text-gray-400 text-center py-4">No requests yet. <a href="{{ route('portal.requests.create') }}" class="text-indigo-600">Submit one →</a></p>
    @endforelse
</div>
@endsection
