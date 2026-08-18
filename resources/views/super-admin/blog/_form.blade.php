@extends('layouts.super-admin')
@section('title', isset($post) ? 'Edit post' : 'New post')
@section('content')
<form method="POST" action="{{ isset($post) ? route('super-admin.blog.update', $post) : route('super-admin.blog.store') }}" enctype="multipart/form-data" class="max-w-4xl space-y-5">
    @csrf
    @if (isset($post)) @method('PUT') @endif

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5 space-y-4">
        <h3 class="font-semibold text-white">Content</h3>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Slug (auto from title)</label>
                <input type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}" placeholder="auto" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Author</label>
                <input type="text" name="author_name" value="{{ old('author_name', $post->author_name ?? '') }}" placeholder="Agency OS" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Excerpt (shown on cards)</label>
                <textarea name="excerpt" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Content * (HTML - h2/h3/p/ul)</label>
                <textarea name="content" rows="14" required class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white font-mono">{{ old('content', $post->content ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5 space-y-4">
        <h3 class="font-semibold text-white">Publishing</h3>
        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
                    <option value="draft" {{ old('status', $post->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $post->status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Publish date</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Categories</label>
                <select name="category_ids[]" multiple class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ isset($post) && $post->categories->contains('id', $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Cover image</label>
                <input type="file" name="cover_image" accept="image/*" class="text-sm text-gray-400">
                @if (isset($post) && $post->cover_url)
                    <img src="{{ $post->cover_url }}" class="h-16 rounded mt-1 object-cover" alt="">
                @endif
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-300 pt-6">
                <input type="checkbox" name="is_featured" value="1" class="rounded" {{ old('is_featured', $post->is_featured ?? false) ? 'checked' : '' }}>
                Featured post
            </label>
        </div>
    </div>

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5 space-y-4">
        <h3 class="font-semibold text-white">SEO</h3>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Meta title (defaults to post title)</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title ?? '') }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Meta description (defaults to excerpt)</label>
                <textarea name="meta_description" rows="2" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
            </div>
        </div>
        <p class="text-xs text-gray-500">JSON-LD Article structured data + Open Graph + Twitter cards are generated automatically from these fields.</p>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('super-admin.blog.index') }}" class="px-5 py-2 rounded-lg text-sm text-gray-400">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm">Save post</button>
    </div>
</form>
@endsection
