@extends('layouts.app')
@section('title', 'Knowledge Base')
@section('breadcrumb', 'Knowledge Base')
@section('content')
<div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
    <form action="{{ route('kb.search') }}" method="GET" class="flex-1 max-w-xl">
        <div class="flex gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search articles…" class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm">
            <button class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm">Search</button>
        </div>
    </form>
    <a href="{{ route('kb.articles.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New article</a>
</div>

@if ($featured->isNotEmpty())
    <div class="mb-8">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">⭐ Featured</h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($featured as $article)
                <a href="{{ route('kb.articles.show', $article) }}" class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white rounded-xl p-5 hover:opacity-95 transition">
                    <div class="text-xs opacity-80 mb-2">{{ $article->category?->name }}</div>
                    <div class="font-semibold">{{ $article->title }}</div>
                    <div class="text-xs opacity-80 mt-2">👁 {{ $article->view_count }} views</div>
                </a>
            @endforeach
        </div>
    </div>
@endif

<h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Categories</h3>
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach ($categories as $category)
        <a href="{{ route('kb.search', ['q' => '', 'category' => $category->id]) }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 transition">
            <div class="text-2xl mb-2">{{ $category->icon ?? '📚' }}</div>
            <div class="font-semibold text-gray-900 text-sm">{{ $category->name }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $category->published_articles_count }} articles</div>
        </a>
    @endforeach
</div>

@if ($recent->isNotEmpty())
    <div class="mt-8">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Recently updated</h3>
        <div class="bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
            @foreach ($recent as $article)
                <a href="{{ route('kb.articles.show', $article) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
                    <span class="text-sm text-gray-800 flex-1">{{ $article->title }}</span>
                    <span class="text-xs text-gray-400">{{ $article->category?->name }}</span>
                    <span class="text-xs text-gray-400">{{ $article->updated_at->diffForHumans() }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
@endsection
