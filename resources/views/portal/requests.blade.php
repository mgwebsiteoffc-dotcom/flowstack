@extends('layouts.portal')
@section('title', 'Requests')
@section('content')
<div class="flex items-center justify-between mb-5">
    <h1 class="text-xl font-bold text-gray-900">Your requests</h1>
    <a href="{{ route('portal.requests.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">+ New request</a>
</div>
<div class="bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
    @forelse ($requests as $request)
        <div class="flex items-center gap-3 px-5 py-4">
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900">{{ $request->title }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ ucfirst($request->request_type) }} · {{ $request->created_at->format('d M Y') }}</div>
                @if ($request->response_message)
                    <div class="text-xs text-green-700 mt-1 bg-green-50 rounded-lg p-2">💬 {{ $request->response_message }}</div>
                @endif
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full {{ $request->status === 'completed' ? 'bg-green-100 text-green-700' : ($request->status === 'open' ? 'bg-blue-100 text-blue-700' : ($request->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500')) }}">
                {{ str_replace('_', ' ', $request->status) }}
            </span>
        </div>
    @empty
        <div class="py-12 text-center text-sm text-gray-400">No requests yet.</div>
    @endforelse
</div>
<x-pagination :paginator="$requests" />
@endsection
