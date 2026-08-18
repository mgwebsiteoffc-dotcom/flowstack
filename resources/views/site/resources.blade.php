@extends('layouts.site')
@section('title', 'Resources')
@php
    $seo = ['title' => 'Resources — Agency OS', 'description' => 'Guides, playbooks and insights to help your agency grow.'];
@endphp
@section('content')
<header class="max-w-4xl mx-auto px-6 pt-16 pb-10 text-center">
    <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Resources for agency growth</h1>
    <p class="text-gray-500 mt-4 text-lg">Guides, playbooks and insights from the Agency OS team.</p>
</header>

<section class="max-w-5xl mx-auto px-6 pb-20">
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="group rounded-2xl border border-gray-100 overflow-hidden hover:shadow-lg transition">
                <div class="h-36 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                    @if ($post->cover_url)
                        <img src="{{ $post->cover_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-gray-300 font-black text-3xl">A</span>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="font-bold group-hover:text-indigo-600">{{ $post->title }}</h3>
                    <div class="text-xs text-gray-400 mt-2">{{ $post->published_at?->format('d M Y') }}</div>
                </div>
            </a>
        @endforeach
    </div>
    <div class="text-center mt-10">
        <a href="{{ route('blog.index') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-indigo-700">View all articles</a>
    </div>
</section>
@endsection
