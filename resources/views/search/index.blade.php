@extends('layouts.app')
@section('title', 'Search')
@section('breadcrumb', 'Search')
@section('content')
<div class="max-w-3xl mx-auto">
    <form method="GET" action="{{ route('search') }}" class="mb-8">
        <input type="text" name="q" value="{{ $term }}" placeholder="Search clients, projects, tasks, leads, articles…" autofocus
               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
    </form>

    @if (mb_strlen($term) >= 2)
        @foreach ([
            ['clients', 'Clients', 'clients.show', 'clients.index'],
            ['projects', 'Projects', 'projects.show', 'projects.index'],
            ['tasks', 'Tasks', 'tasks.show', 'tasks.index'],
            ['leads', 'Leads', 'leads.show', 'leads.index'],
            ['articles', 'Knowledge base', 'kb.articles.show', 'kb.index'],
        ] as [$key, $label, $showRoute, $allRoute])
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ $label }}</h3>
                    @if ($results[$key]->isNotEmpty())
                        <a href="{{ route($allRoute, ['search' => $term]) }}" class="text-xs text-indigo-600">View all</a>
                    @endif
                </div>
                <div class="bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
                    @forelse ($results[$key] as $result)
                        @if ($key === 'articles')
                            <a href="{{ route($showRoute, $result) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50">
                                <span class="text-sm text-gray-800">{{ $result->title }}</span>
                                <span class="text-xs text-gray-400 ml-auto">{{ $result->category?->name }}</span>
                            </a>
                        @elseif ($key === 'clients')
                            <a href="{{ route($showRoute, $result) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50">
                                <x-health-badge :score="$result->health_score" />
                                <span class="text-sm text-gray-800">{{ $result->company_name }}</span>
                            </a>
                        @elseif ($key === 'projects')
                            <a href="{{ route($showRoute, $result) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50">
                                <span class="text-sm text-gray-800">{{ $result->name }}</span>
                                <span class="text-xs text-gray-400 ml-auto">{{ $result->client?->company_name }}</span>
                            </a>
                        @elseif ($key === 'tasks')
                            <a href="{{ route($showRoute, $result) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50">
                                <x-status-badge :status="$result->status" />
                                <span class="text-sm text-gray-800">{{ $result->title }}</span>
                                <span class="text-xs text-gray-400 ml-auto">{{ $result->client?->company_name }}</span>
                            </a>
                        @else
                            <a href="{{ route($showRoute, $result) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50">
                                <x-source-badge :source="$result->source_type" />
                                <span class="text-sm text-gray-800">{{ $result->contact_name }}</span>
                                <span class="text-xs text-gray-400 ml-auto">{{ $result->company_name }}</span>
                            </a>
                        @endif
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-400">No {{ $label }} found</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center py-16 text-gray-400 text-sm">Type at least 2 characters to search.</div>
    @endif
</div>
@endsection
