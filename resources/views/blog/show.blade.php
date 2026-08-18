<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <x-brand-head />
    <x-tracking placement="head" />
    <x-seo
        :title="$post->meta_title"
        :description="$post->meta_description"
        :image="$post->cover_url"
        type="article"
        :jsonLd="[
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->meta_description,
            'image' => $post->cover_url,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $post->author_name ?? 'Task365'],
            'publisher' => ['@type' => 'Organization', 'name' => config('app.name'), 'logo' => ['@type' => 'ImageObject', 'url' => url('/favicon.svg')]],
            'mainEntityOfPage' => url()->current(),
        ]"
    />
    <style>.blog-body h2{font-size:1.4rem;font-weight:700;margin:1.6rem 0 .5rem}.blog-body h3{font-size:1.15rem;font-weight:600;margin:1.3rem 0 .4rem}.blog-body p{margin:.6rem 0;color:#374151;line-height:1.7}.blog-body ul,.blog-body ol{margin:.5rem 0 .75rem 1.25rem;color:#374151}.blog-body ul{list-style:disc}.blog-body ol{list-style:decimal}.blog-body li{margin:.25rem 0}.blog-body img{border-radius:.75rem;margin:1rem 0}.blog-body pre{background:#111827;color:#e5e7eb;padding:1rem;border-radius:.5rem;overflow-x:auto;margin:.75rem 0;font-size:.85rem}.blog-body code{background:#f3f4f6;padding:.15rem .35rem;border-radius:.25rem;font-size:.85em}.blog-body pre code{background:none;padding:0}.blog-body blockquote{border-left:3px solid #6366f1;padding-left:1rem;color:#4b5563;margin:.75rem 0}</style>
</head>
<body class="bg-white text-gray-900">
    <x-tracking placement="body" />
    @include('components.site-nav')

    <article class="max-w-3xl mx-auto px-6 pb-16">
        <nav class="text-xs text-gray-400 mb-6">
            <a href="{{ route('home') }}" class="hover:text-gray-600">Home</a>
            <span class="mx-1">/</span>
            <a href="{{ route('blog.index') }}" class="hover:text-gray-600">Blog</a>
            @foreach ($post->categories as $cat)
                <span class="mx-1">/</span>
                <a href="{{ route('blog.category', $cat->slug) }}" class="hover:text-gray-600">{{ $cat->name }}</a>
            @endforeach
        </nav>

        <h1 class="text-3xl sm:text-4xl font-black leading-tight">{{ $post->title }}</h1>
        <div class="flex items-center gap-3 mt-4 text-sm text-gray-400">
            <span>{{ $post->author_name ?? 'Task365' }}</span>
            <span>·</span>
            <time datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->published_at->format('d M Y') }}</time>
            <span>·</span>
            <span>{{ $post->view_count }} reads</span>
        </div>

        @if ($post->cover_url)
            <img src="{{ $post->cover_url }}" alt="{{ $post->title }}" class="w-full h-64 sm:h-96 object-cover rounded-2xl mt-6">
        @endif

        @if ($post->excerpt)
            <p class="text-lg text-gray-500 mt-6 italic">{{ $post->excerpt }}</p>
        @endif

        <div class="blog-body mt-6">
            {!! $post->content !!}
        </div>
    </article>

    <section class="max-w-3xl mx-auto px-6 pb-16">
        <h2 class="text-xl font-bold mb-4">Related articles</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($latest as $relatedPost)
                <a href="{{ route('blog.show', $relatedPost->slug) }}" class="border border-gray-100 rounded-xl p-4 hover:shadow-md transition">
                    <h3 class="font-semibold text-sm hover:text-indigo-600">{{ $relatedPost->title }}</h3>
                    <div class="text-xs text-gray-400 mt-1">{{ $relatedPost->published_at->format('d M Y') }}</div>
                </a>
            @endforeach
        </div>
        <div class="mt-8 text-center">
            <a href="{{ route('register') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-indigo-700">Start your 14-day free trial</a>
        </div>
    </section>

    @include('components.site-footer')
</body>
</html>
