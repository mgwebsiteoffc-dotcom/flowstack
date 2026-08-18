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
        title="{{ $category->name ?? 'Blog' }}"
        description="Agency growth insights, marketing playbooks and operations tips from the Task365 team."
        :jsonLd="[
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => ($category->name ?? 'Blog').' - '.config('app.name'),
            'url' => url()->current(),
            'isPartOf' => ['@type' => 'WebSite', 'name' => config('app.name'), 'url' => url('/')],
        ]"
    />
</head>
<body class="bg-white text-gray-900">
    <x-tracking placement="body" />
    @include('components.site-nav')

    <header class="max-w-4xl mx-auto px-6 pt-14 pb-10 text-center">
        <h1 class="text-4xl font-black tracking-tight">{{ $category->name ?? 'Task365 Blog' }}</h1>
        <p class="text-gray-500 mt-3">Growth strategies, client delivery playbooks and operations tips for modern agencies.</p>
    </header>

    @if (isset($featured) && $featured && ! isset($category))
        <div class="max-w-5xl mx-auto px-6 mb-10">
            <a href="{{ route('blog.show', $featured->slug) }}" class="group grid md:grid-cols-2 gap-6 bg-gray-50 rounded-2xl overflow-hidden hover:shadow-lg transition">
                <div class="h-64 md:h-auto bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center">
                    @if ($featured->cover_url)
                        <img src="{{ $featured->cover_url }}" alt="{{ $featured->title }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-white font-black text-6xl">A</span>
                    @endif
                </div>
                <div class="p-8 flex flex-col justify-center">
                    <div class="text-xs text-indigo-600 font-semibold uppercase">Featured</div>
                    <h2 class="text-2xl font-bold mt-2 group-hover:text-indigo-600">{{ $featured->title }}</h2>
                    <p class="text-gray-500 mt-2 text-sm">{{ $featured->excerpt }}</p>
                    <div class="text-xs text-gray-400 mt-4">{{ $featured->author_name ?? 'Task365' }} · {{ $featured->published_at->format('d M Y') }}</div>
                </div>
            </a>
        </div>
    @endif

    <div class="max-w-5xl mx-auto px-6 pb-16">
        @if (! isset($category))
            <div class="flex flex-wrap gap-2 mb-8">
                <a href="{{ route('blog.index') }}" class="px-3 py-1.5 rounded-full text-xs font-medium {{ ! isset($category) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">All</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('blog.category', $cat->slug) }}" class="px-3 py-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200">{{ $cat->name }} ({{ $cat->posts_count }})</a>
                @endforeach
            </div>
        @endif

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="group border border-gray-100 rounded-2xl overflow-hidden hover:shadow-lg transition">
                    <div class="h-40 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                        @if ($post->cover_url)
                            <img src="{{ $post->cover_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                        @else
                            <span class="text-gray-300 font-black text-4xl">A</span>
                        @endif
                    </div>
                    <div class="p-5">
                        <div class="flex gap-2 mb-2">
                            @foreach ($post->categories as $cat)
                                <span class="text-[10px] bg-indigo-50 text-indigo-600 rounded-full px-2 py-0.5">{{ $cat->name }}</span>
                            @endforeach
                        </div>
                        <h3 class="font-bold group-hover:text-indigo-600">{{ $post->title }}</h3>
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $post->excerpt }}</p>
                        <div class="text-xs text-gray-400 mt-3">{{ $post->author_name ?? 'Task365' }} · {{ $post->published_at->format('d M Y') }}</div>
                    </div>
                </a>
            @empty
                <div class="sm:col-span-3 text-center py-16 text-gray-400">No posts published yet.</div>
            @endforelse
        </div>

        <div class="mt-8">{{ $posts->links() }}</div>
    </div>

    @include('components.site-footer')
</body>
</html>
