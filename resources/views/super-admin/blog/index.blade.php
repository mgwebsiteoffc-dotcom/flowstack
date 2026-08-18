@extends('layouts.super-admin')
@section('title', 'Blog')
@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <h1 class="text-xl font-bold text-white">Blog posts</h1>
    <div class="flex gap-2">
        <a href="{{ route('super-admin.blog.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">+ New post</a>
    </div>
</div>

<form method="GET" class="flex gap-2 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts…" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500">
    <select name="status" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
        <option value="">All statuses</option>
        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
    </select>
    <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
</form>

<div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-950 text-left text-xs text-gray-500 uppercase">
            <tr><th class="px-4 py-3">Title</th><th class="px-4 py-3">Categories</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Published</th><th class="px-4 py-3 text-right">Views</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse ($posts as $post)
                <tr class="hover:bg-gray-800/40">
                    <td class="px-4 py-3">
                        <span class="text-gray-200 font-medium">{{ $post->title }}</span>
                        <div class="text-xs text-gray-500">/blog/{{ $post->slug }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @foreach ($post->categories as $cat)
                            <span class="text-xs bg-indigo-500/10 text-indigo-400 rounded-full px-2 py-0.5">{{ $cat->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $post->status === 'published' ? 'bg-green-500/10 text-green-400' : 'bg-gray-700 text-gray-300' }}">{{ $post->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $post->published_at?->format('d M Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-gray-300">{{ $post->view_count }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-xs text-indigo-400 mr-2">View</a>
                        <a href="{{ route('super-admin.blog.edit', $post) }}" class="text-xs text-gray-300 mr-2">Edit</a>
                        <form method="POST" action="{{ route('super-admin.blog.destroy', $post) }}" class="inline" onsubmit="return confirm('Delete this post?')">@csrf @method('DELETE')
                            <button class="text-xs text-red-400">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-10 text-center text-gray-500">No blog posts yet. Write your first one!</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $posts->links() }}</div>

<div class="bg-gray-900 rounded-xl border border-gray-800 p-5 mt-8">
    <h3 class="font-semibold text-white mb-3">Categories</h3>
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ($categories as $cat)
            <span class="inline-flex items-center gap-2 bg-gray-800 rounded-full px-3 py-1 text-sm text-gray-300">
                {{ $cat->name }} ({{ $cat->posts_count }})
                <form method="POST" action="{{ route('super-admin.blog.categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')
                    <button class="text-red-400 hover:text-red-500"><x-icon name="x-mark" class="w-3 h-3" /></button>
                </form>
            </span>
        @endforeach
    </div>
    <form method="POST" action="{{ route('super-admin.blog.categories.store') }}" class="flex gap-2 max-w-sm">
        @csrf
        <input type="text" name="name" placeholder="New category name *" required class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Add</button>
    </form>
</div>
@endsection
