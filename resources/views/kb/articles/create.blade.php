@extends('layouts.app')
@section('title', 'New article')
@section('content')
<form method="POST" action="{{ route('kb.articles.store') }}" class="max-w-4xl space-y-6">
    @csrf
    <x-card title="Article details" icon="📚">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select name="category_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                <select name="tag_ids[]" multiple class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="draft">Draft</option>
                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Visibility</label>
                <select name="visibility" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="all">All (client portal)</option>
                    <option value="internal" {{ old('visibility') === 'internal' ? 'selected' : '' }}>Internal only</option>
                </select></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Video embed URL (YouTube / Loom)</label>
                <input type="url" name="video_url" value="{{ old('video_url') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                <div class="text-xs text-gray-400 mb-2">Write with headings (h2/h3) — a table of contents is generated automatically. For AI prompts, include [variables] in [brackets].</div>
                <div id="kb-editor" class="border border-gray-300 rounded-lg bg-white" style="height: 320px;"></div>
                <textarea name="content" id="kb-content" required class="hidden">{{ old('content') }}</textarea>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_featured" value="1" class="rounded"> Feature this article</label>
        </div>
    </x-card>
    <div class="flex justify-end gap-3">
        <a href="{{ route('kb.index') }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save article</button>
    </div>
</form>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    const quill = new Quill('#kb-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'code-block', 'link'],
                ['clean']
            ]
        }
    });
    document.querySelector('form').addEventListener('submit', function () {
        document.getElementById('kb-content').value = quill.root.innerHTML;
    });
</script>
@endpush
