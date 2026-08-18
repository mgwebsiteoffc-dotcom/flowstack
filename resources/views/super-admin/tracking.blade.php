@extends('layouts.super-admin')
@section('title', 'Tracking & pixels')
@section('content')
<h1 class="text-xl font-bold text-white mb-6">Tracking &amp; pixels</h1>

<div class="grid lg:grid-cols-2 gap-6">
    <!-- Pixels -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <h3 class="font-semibold text-white mb-1">Tracking pixels</h3>
        <p class="text-xs text-gray-500 mb-4">Scripts injected site-wide (landing, pricing, blog, login/signup). Add your Meta/FB pixel, GA4 (gtag), TikTok or Google Ads snippets.</p>

        <div class="space-y-2 mb-4">
            @forelse ($pixels as $pixel)
                <div class="flex items-center gap-3 bg-gray-800 rounded-lg px-3 py-2">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $pixel->is_active ? 'bg-green-500/10 text-green-400' : 'bg-gray-700 text-gray-400' }}">{{ $pixel->provider }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-gray-200 truncate">{{ $pixel->name }}</div>
                        <div class="text-[10px] text-gray-500">placement: {{ $pixel->placement }} · priority {{ $pixel->priority }}</div>
                    </div>
                    <form method="POST" action="{{ route('super-admin.tracking.pixels.toggle', $pixel) }}">@csrf
                        <button class="text-xs px-2 py-1 rounded {{ $pixel->is_active ? 'bg-gray-700 text-gray-300' : 'bg-green-500/10 text-green-400' }}">{{ $pixel->is_active ? 'Disable' : 'Enable' }}</button>
                    </form>
                    <form method="POST" action="{{ route('super-admin.tracking.pixels.destroy', $pixel) }}" onsubmit="return confirm('Remove this pixel?')">@csrf @method('DELETE')
                        <button class="text-xs text-red-400"><x-icon name="trash" class="w-3.5 h-3.5" /></button>
                    </form>
                </div>
            @empty
                <p class="text-xs text-gray-500 py-3">No pixels yet. Add your first one below.</p>
            @endforelse
        </div>

        <details class="bg-gray-800 rounded-lg p-3">
            <summary class="text-sm text-indigo-400 cursor-pointer">+ Add pixel</summary>
            <form method="POST" action="{{ route('super-admin.tracking.pixels.store') }}" class="mt-3 space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="name" placeholder="Name * (e.g. Meta Pixel)" required class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500">
                    <select name="provider" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
                        @foreach (['facebook', 'google', 'tiktok', 'gtag', 'hotjar', 'custom'] as $p)
                            <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                    <select name="placement" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
                        <option value="head">Head (recommended)</option>
                        <option value="body">Body (end)</option>
                    </select>
                    <input type="number" name="priority" value="0" min="0" max="100" placeholder="Priority" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500">
                </div>
                <textarea name="code" rows="5" placeholder="Paste the full <script> snippet…" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500 font-mono"></textarea>
                <label class="flex items-center gap-2 text-xs text-gray-400"><input type="checkbox" name="is_active" value="1" checked class="rounded"> Active immediately</label>
                <button class="w-full bg-indigo-600 text-white rounded-lg py-2 text-sm">Save pixel</button>
            </form>
        </details>
    </div>

    <!-- Tracking links -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <h3 class="font-semibold text-white mb-1">Tracking / campaign URLs</h3>
        <p class="text-xs text-gray-500 mb-4">Library of conversion tracking URLs (e.g. UTM-tagged landing pages for ads).</p>

        <div class="space-y-2 mb-4">
            @forelse ($links as $link)
                <div class="flex items-center gap-3 bg-gray-800 rounded-lg px-3 py-2">
                    <x-icon name="link" class="w-4 h-4 text-indigo-400" />
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-gray-200 truncate">{{ $link->name }}</div>
                        <a href="{{ $link->url }}" target="_blank" class="text-[10px] text-gray-500 hover:text-indigo-400 truncate block">{{ $link->url }}</a>
                    </div>
                    <form method="POST" action="{{ route('super-admin.tracking.links.destroy', $link) }}" onsubmit="return confirm('Remove this link?')">@csrf @method('DELETE')
                        <button class="text-xs text-red-400"><x-icon name="trash" class="w-3.5 h-3.5" /></button>
                    </form>
                </div>
            @empty
                <p class="text-xs text-gray-500 py-3">No tracking links yet.</p>
            @endforelse
        </div>

        <details class="bg-gray-800 rounded-lg p-3">
            <summary class="text-sm text-indigo-400 cursor-pointer">+ Add tracking link</summary>
            <form method="POST" action="{{ route('super-admin.tracking.links.store') }}" class="mt-3 space-y-3">
                @csrf
                <input type="text" name="name" placeholder="Name * (e.g. Meta Lead Ad URL)" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500">
                <input type="url" name="url" placeholder="https://… (with UTM params)" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500">
                <input type="text" name="description" placeholder="Notes (optional)" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500">
                <button class="w-full bg-indigo-600 text-white rounded-lg py-2 text-sm">Save link</button>
            </form>
        </details>
    </div>
</div>
@endsection
