@extends('layouts.app')
@section('title', 'Notifications')
@section('breadcrumb', 'Notifications')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-gray-900">Notifications</h2>
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf
            <button class="text-sm text-indigo-600">Mark all read</button>
        </form>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
        @forelse ($notifications as $notification)
            <a href="{{ route('notifications.read', $notification->id) }}"
               class="flex items-start gap-3 px-5 py-4 hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-indigo-50/50' }}">
                <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-lg shrink-0">
                    {{ ($notification->data['priority'] ?? '') === 'high' ? '🔴' : '🔔' }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</div>
                    <div class="text-sm text-gray-500 mt-0.5">{{ $notification->data['body'] ?? '' }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $notification->created_at->format('d M Y H:i') }} · {{ $notification->created_at->diffForHumans() }}</div>
                </div>
                @if (! $notification->read_at)
                    <span class="w-2 h-2 rounded-full bg-indigo-600 mt-2"></span>
                @endif
            </a>
        @empty
            <div class="py-12 text-center text-sm text-gray-400">You're all caught up! 🎉</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
