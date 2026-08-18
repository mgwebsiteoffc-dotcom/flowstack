@extends('layouts.portal')
@section('title', 'Approvals')
@section('content')
<h1 class="text-xl font-bold text-gray-900 mb-5">Approvals</h1>

<h3 class="text-sm font-semibold text-gray-500 mb-3">Waiting for your approval</h3>
<div class="space-y-4 mb-8">
    @forelse ($pending as $approval)
        <div class="bg-white rounded-xl border border-amber-200 p-5">
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <div>
                    <h4 class="font-semibold text-gray-900">{{ $approval->title }}</h4>
                    <p class="text-sm text-gray-500 mt-1">{{ $approval->description }}</p>
                    <div class="text-xs text-gray-400 mt-2">Submitted {{ $approval->created_at->format('d M Y') }}</div>
                </div>
            </div>
            @if ($approval->file_paths)
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach ($approval->file_paths as $path)
                        <span class="text-xs bg-gray-100 rounded-lg px-3 py-1.5"><x-icon name="paper-clip" class="w-4 h-4 inline-block" /> {{ basename($path) }}</span>
                    @endforeach
                </div>
            @endif
            <div x-data="{ notes: false }" class="mt-4 flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('portal.approvals.respond', $approval) }}">@csrf
                    <input type="hidden" name="action" value="approved">
                    <button class="bg-green-600 text-white px-5 py-2 rounded-lg text-sm font-medium"> Approve</button>
                </form>
                <button @click="notes = !notes" class="bg-amber-50 text-amber-700 px-5 py-2 rounded-lg text-sm">Request changes</button>
                <form method="POST" action="{{ route('portal.approvals.respond', $approval) }}" x-show="notes" x-cloak class="w-full flex gap-2">
                    @csrf
                    <input type="hidden" name="action" value="changes_requested">
                    <input type="text" name="review_notes" placeholder="What changes do you need?" required class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <button class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm">Submit</button>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-center text-sm text-gray-400">Nothing waiting for your approval <x-icon name="sparkles" class="w-4 h-4 inline-block" /></div>
    @endforelse
</div>

<h3 class="text-sm font-semibold text-gray-500 mb-3">History</h3>
<div class="bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
    @forelse ($history as $approval)
        <div class="flex items-center gap-3 px-5 py-3">
            <span class="w-6 text-center">@if ($approval->status === 'approved')<x-icon name="check-circle" class="w-4 h-4 text-green-600" />@else<x-icon name="arrow-path" class="w-4 h-4 text-amber-600" />@endif</span>
            <div class="flex-1 min-w-0">
                <div class="text-sm text-gray-800">{{ $approval->title }}</div>
                @if ($approval->review_notes)<div class="text-xs text-gray-400 mt-0.5">{{ $approval->review_notes }}</div>@endif
            </div>
            <span class="text-xs text-gray-400">{{ $approval->reviewed_at?->format('d M Y') }}</span>
        </div>
    @empty
        <div class="py-8 text-center text-sm text-gray-400">No past approvals.</div>
    @endforelse
</div>
<x-pagination :paginator="$history" />
@endsection
