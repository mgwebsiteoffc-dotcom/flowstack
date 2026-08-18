@extends('layouts.site')
@section('title', 'Resources')
@php
    $seo = ['title' => 'Resources — Task365', 'description' => 'Guides, playbooks and insights to help your agency grow faster and deliver better.'];
@endphp
@section('content')

<header class="bg-gradient-to-b from-indigo-50/60 to-white">
    <div class="max-w-7xl mx-auto px-6 pt-16 pb-12 text-center">
        <div class="inline-flex items-center gap-2 bg-white border border-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm mb-6">
            <span class="w-2 h-2 rounded-full bg-green-500"></span> Playbooks, guides & insights
        </div>
        <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Resources for agency growth</h1>
        <p class="text-gray-500 mt-4 text-lg max-w-2xl mx-auto">Practical playbooks on onboarding clients, running retainers, reporting and winning more work.</p>
    </div>
</header>

<!-- Resource categories (We360-style topic cards) -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach ([
            ['megaphone','Growth & Sales','Win more leads, write better proposals, close faster.'],
            ['users','Client Success','Onboard, retain and renew with healthy client relationships.'],
            ['check-circle','Delivery','Run projects and tasks without the chaos.'],
            ['chart-bar','Reporting','Build reports clients actually read.'],
        ] as [$icon,$title,$desc])
            <div class="rounded-2xl border border-gray-100 p-6 hover:shadow-lg transition">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3"><x-icon :name="$icon" class="w-5 h-5" /></div>
                <h3 class="font-bold text-sm">{{ $title }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $desc }}</p>
            </div>
        @endforeach
    </div>
</section>

<!-- Latest articles -->
<section class="max-w-7xl mx-auto px-6 pb-16">
    <h2 class="text-2xl font-black mb-8">Latest from the blog</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="group rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl transition">
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
                    <div class="text-xs text-gray-400 mt-3">{{ $post->author_name ?? 'Task365' }} · {{ $post->published_at?->format('d M Y') }}</div>
                </div>
            </a>
        @empty
            <div class="sm:col-span-3 text-center py-10 text-gray-400">Articles coming soon.</div>
        @endforelse
    </div>
    <div class="text-center mt-10">
        <a href="{{ route('blog.index') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-indigo-700">View all articles</a>
    </div>
</section>

<!-- Featured guide banner -->
<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="rounded-3xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-purple-50 px-8 py-10 grid lg:grid-cols-2 gap-8 items-center">
        <div>
            <div class="text-xs font-semibold text-indigo-600 uppercase tracking-wide">Free playbook</div>
            <h2 class="text-2xl font-black mt-2">The Agency Onboarding Playbook</h2>
            <p class="text-gray-500 mt-2">20 steps to take any new client from signed to delivered — the exact checklist we built into Task365.</p>
            <a href="{{ route('register') }}" class="inline-block mt-5 bg-indigo-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-indigo-700">Get the playbook</a>
        </div>
        <div class="space-y-2.5">
            @foreach (['Send welcome email & collect brand assets','Get Meta / Google Ads / GA4 / Shopify access','Schedule & run the kickoff call','Create the 30-60-90 day plan','Set recurring tasks & reporting cadence'] as $i => $step)
                <div class="flex items-center gap-3 bg-white rounded-xl px-4 py-3 border border-gray-100">
                    <span class="w-7 h-7 rounded-full {{ $i < 2 ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center text-xs font-bold">{{ $i + 1 }}</span>
                    <span class="text-sm text-gray-700 {{ $i < 2 ? 'line-through text-gray-400' : '' }}">{{ $step }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Newsletter CTA -->
<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-14 text-center text-white">
        <h2 class="text-3xl font-black">Get agency growth tips in your inbox</h2>
        <p class="text-indigo-100 mt-2">One practical email a week. No spam.</p>
        <form class="mt-6 flex max-w-md mx-auto gap-2" onsubmit="event.preventDefault(); alert('Subscribed! (newsletter coming soon)')">
            <input type="email" placeholder="you@agency.com" required class="flex-1 rounded-xl px-4 py-3 text-sm text-gray-900">
            <button class="bg-white text-indigo-700 px-5 py-3 rounded-xl font-semibold">Subscribe</button>
        </form>
    </div>
</section>

@endsection
