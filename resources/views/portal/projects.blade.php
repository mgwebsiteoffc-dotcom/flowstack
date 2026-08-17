@extends('layouts.portal')
@section('title', 'Projects')
@section('content')
<h1 class="text-xl font-bold text-gray-900 mb-5">Our projects</h1>
<div class="grid sm:grid-cols-2 gap-4">
    @forelse ($projects as $project)
        <a href="{{ route('portal.projects.show', $project) }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 transition">
            <div class="flex justify-between items-start">
                <h3 class="font-semibold text-gray-900">{{ $project->name }}</h3>
                <span class="text-xs bg-gray-100 rounded-full px-2 py-0.5 text-gray-600 capitalize">{{ str_replace('_', ' ', $project->status) }}</span>
            </div>
            <div class="text-xs text-gray-400 mt-1">{{ $project->service_type ?? '' }}</div>
            <div class="flex justify-between text-xs text-gray-500 mt-3 mb-1">
                <span>{{ $project->done_tasks }}/{{ $project->tasks_count }} tasks done</span>
                <span>{{ $project->tasks_count > 0 ? round($project->done_tasks * 100 / $project->tasks_count) : 0 }}%</span>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full">
                <div class="h-1.5 bg-indigo-500 rounded-full" style="width: {{ $project->tasks_count > 0 ? $project->done_tasks * 100 / $project->tasks_count : 0 }}%"></div>
            </div>
        </a>
    @empty
        <div class="sm:col-span-2 text-center py-12 text-sm text-gray-400">No projects to show right now.</div>
    @endforelse
</div>
@endsection
