@extends('layouts.app')
@section('title', 'Search results')
@section('breadcrumb', 'Knowledge Base / Search')
@section('content')
<form action="{{ route('kb.search') }}" method="GET" class="max-w-xl mb-6">
    <div class="flex gap-2">
        <input type="text" name="q" value="{{ $term }}" placeholder="Search articles…" class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm">
        <button class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm">Search</button>
    </div>
</form>

<div class="bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
    @forelse ($articles as $article)
        <a href="{{ route('kb.articles.show', $article) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
            <span class="text-sm text-gray-800 flex-1">{{ $article->title }}</span>
            <span class="text-xs bg-indigo-50 text-indigo-700 rounded-full px-2 py-0.5">{{ $article->category?->name }}</span>
            <span class="text-xs text-gray-400">{{ $article->updated_at->diffForHumans() }}</span>
        </a>
    @empty
        <div class="p-10 text-center text-sm text-gray-400">No articles found for "{{ $term }}".</div>
    @endforelse
</div>
<x-pagination :paginator="$articles" />
@endsection
