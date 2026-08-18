@extends('layouts.app')
@section('title', 'Announcements')
@section('breadcrumb', 'Announcements')
@section('content')
<div class="max-w-3xl mx-auto">
    @if ($canManage)
        <div class="bg-white rounded-xl border border-gray-100 p-5 mb-6">
            <h3 class="font-semibold text-gray-900 mb-3"><x-icon name="megaphone" class="w-4 h-4 inline-block" /> New announcement</h3>
            <form method="POST" action="{{ route('announcements.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="title" placeholder="Title *" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <textarea name="content" rows="3" placeholder="Message…" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <div class="flex items-center gap-4 text-sm text-gray-600">
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_pinned" value="1" class="rounded"> <x-icon name="map-pin" class="w-4 h-4 inline-block" /> Pin</label>
                    <label class="flex items-center gap-2">Expires <input type="date" name="expires_at" class="rounded-lg border border-gray-300 px-2 py-1 text-sm"></label>
                    <button class="ml-auto bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium">Publish</button>
                </div>
            </form>
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($announcements as $announcement)
            <div class="bg-white rounded-xl border border-gray-100 p-5 {{ $announcement->is_pinned ? 'border-amber-200 bg-amber-50/40' : '' }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="font-semibold text-gray-900 flex items-center gap-2">
                            @if ($announcement->is_pinned)<span class="text-xs bg-amber-100 text-amber-700 rounded-full px-2 py-0.5"><x-icon name="map-pin" class="w-4 h-4 inline-block" /> Pinned</span>@endif
                            {{ $announcement->title }}
                        </h4>
                        <div class="text-xs text-gray-400 mt-1">{{ $announcement->creator?->name }} · {{ $announcement->created_at->format('d M Y H:i') }}</div>
                    </div>
                    @if ($canManage)
                        <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('Remove this announcement?')">@csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600"><x-icon name="x-mark" class="w-4 h-4" /></button>
                        </form>
                    @endif
                </div>
                <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $announcement->content }}</p>
                @if ($announcement->expires_at)
                    <div class="text-[10px] text-gray-400 mt-2">Visible until {{ $announcement->expires_at->format('d M Y') }}</div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-100 py-12 text-center text-sm text-gray-400">
                No announcements yet.
                @if ($canManage) Publish one above! @endif
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $announcements->links() }}</div>
</div>
@endsection
