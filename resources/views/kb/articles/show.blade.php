@extends('layouts.app')
@section('title', $article->title)
@section('breadcrumb', 'Knowledge Base / '.$article->title)
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div class="lg:col-span-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <div class="flex items-center gap-2 mb-4 text-xs">
                <span class="bg-indigo-50 text-indigo-700 rounded-full px-2.5 py-1">{{ $article->category?->name }}</span>
                @foreach ($article->tags as $tag)
                    <span class="bg-gray-100 text-gray-600 rounded-full px-2.5 py-1">#{{ $tag->name }}</span>
                @endforeach
                <span class="text-gray-400 ml-auto"><x-icon name="eye" class="w-4 h-4 inline-block" /> {{ $article->view_count }} views · {{ $article->updated_at->diffForHumans() }}</span>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $article->title }}</h1>

            @if ($article->video_url)
                <div class="aspect-video bg-gray-900 rounded-xl mb-6 flex items-center justify-center">
                    <a href="{{ $article->video_url }}" target="_blank" class="text-white text-center">
                        <div class="text-4xl mb-2"><x-icon name="play" class="w-4 h-4 inline-block" /></div>
                        <div class="text-sm">Watch video</div>
                    </a>
                </div>
            @endif

            @if ($article->toc())
                <div class="bg-gray-50 rounded-xl p-4 mb-6">
                    <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Table of contents</div>
                    @foreach ($article->toc() as $heading)
                        <a href="#{{ $heading['id'] }}" class="block py-0.5 text-sm text-indigo-600 hover:underline" style="padding-left: {{ ($heading['level'] - 2) * 12 }}px">
                            {{ $heading['text'] }}
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="prose max-w-none text-gray-800 leading-relaxed kb-content">
                {!! $article->safeContent() !!}
            </div>

            <div class="border-t mt-8 pt-6">
                <h3 class="font-semibold text-gray-900 text-sm mb-3"><x-icon name="chat-bubble-left-right" class="w-4 h-4 inline-block" /> Team comments ({{ $article->comments->count() }})</h3>
                <div class="space-y-3 mb-4 max-h-72 overflow-y-auto">
                    @forelse ($article->comments as $comment)
                        <div class="flex gap-2.5">
                            <x-user-avatar :user="$comment->user" size="sm" />
                            <div class="bg-gray-50 rounded-xl rounded-tl-none px-3 py-2 flex-1">
                                <div class="text-xs text-gray-500"><span class="font-medium text-gray-800">{{ $comment->user?->name }}</span> · {{ $comment->created_at->diffForHumans() }}</div>
                                <div class="text-sm text-gray-700 mt-0.5 whitespace-pre-line">{{ $comment->comment }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No comments yet.</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('kb.articles.comments.store', $article) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="comment" placeholder="Add a team comment…" required class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Comment</button>
                </form>
            </div>

            <div class="border-t mt-8 pt-6 flex items-center justify-between flex-wrap gap-3">
                <div class="text-sm text-gray-500">
                    Was this helpful?
                    <form method="POST" action="{{ route('kb.articles.feedback', $article) }}" class="inline">
                        @csrf
                        <input type="hidden" name="helpful" value="1">
                        <button class="ml-2 px-3 py-1 bg-green-50 text-green-700 rounded-lg text-xs"><x-icon name="hand-thumb-up" class="w-4 h-4 inline-block" /> Yes</button>
                    </form>
                    <form method="POST" action="{{ route('kb.articles.feedback', $article) }}" class="inline">
                        @csrf
                        <input type="hidden" name="helpful" value="0">
                        <button class="px-3 py-1 bg-red-50 text-red-700 rounded-lg text-xs"><x-icon name="hand-thumb-down" class="w-4 h-4 inline-block" /> No</button>
                    </form>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('kb.articles.edit', $article) }}" class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-700">Edit</a>
                    <x-confirm-delete :action="route('kb.articles.destroy', $article)" message="Delete this article?">
                        <x-slot:trigger><span class="px-3 py-1.5 text-sm rounded-lg bg-red-50 text-red-600 cursor-pointer">Delete</span></x-slot:trigger>
                    </x-confirm-delete>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <x-card title="Related articles" icon="link">
            @forelse ($related as $relatedArticle)
                <a href="{{ route('kb.articles.show', $relatedArticle) }}" class="block py-2 text-sm text-gray-700 hover:text-indigo-600 border-b border-gray-50 last:border-0">{{ $relatedArticle->title }}</a>
            @empty
                <p class="text-sm text-gray-400 text-center py-2">No related articles</p>
            @endforelse
        </x-card>
        <x-card title="Details" icon="ℹ">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-400">Author</dt><dd>{{ $article->creator?->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Status</dt><dd>{{ ucfirst($article->status) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Visibility</dt><dd>{{ ucfirst($article->visibility) }}</dd></div>
            </dl>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add copy buttons to every code block (AI Prompts Library).
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.kb-content pre').forEach(function (pre) {
            const btn = document.createElement('button');
            btn.textContent = 'clipboard Copy';
            btn.className = 'text-[10px] bg-gray-700 text-gray-200 hover:bg-gray-600 rounded px-2 py-1 float-right -mt-2 -mr-2 mb-1';
            btn.onclick = function () {
                navigator.clipboard.writeText(pre.textContent.trim());
                btn.textContent = 'Copied check-circle';
                setTimeout(() => btn.textContent = 'clipboard Copy', 2000);
            };
            pre.style.position = 'relative';
            pre.prepend(btn);
        });
    });
</script>
@endpush

@push('styles')
<style>
.kb-content h2 { font-size: 1.25rem; font-weight: 700; margin-top: 1.5rem; margin-bottom: 0.5rem; }
.kb-content h3 { font-size: 1.1rem; font-weight: 600; margin-top: 1.25rem; margin-bottom: 0.4rem; }
.kb-content p { margin-bottom: 0.75rem; }
.kb-content ul, .kb-content ol { margin: 0.5rem 0 0.75rem 1.25rem; list-style: disc; }
.kb-content ol { list-style: decimal; }
.kb-content li { margin-bottom: 0.25rem; }
.kb-content pre { background: #1f2937; color: #e5e7eb; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 0.75rem 0; font-size: 0.85rem; }
.kb-content code { background: #f3f4f6; padding: 0.15rem 0.35rem; border-radius: 0.25rem; font-size: 0.85em; }
.kb-content pre code { background: none; padding: 0; }
.kb-content blockquote { border-left: 3px solid #6366f1; padding-left: 1rem; color: #4b5563; margin: 0.75rem 0; }
</style>
@endpush
