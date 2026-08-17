@extends('layouts.app')
@section('title', 'Projects')
@section('breadcrumb', 'Projects')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <form method="GET" class="flex flex-wrap gap-2 text-sm">
        <select name="client_id" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All clients</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All statuses</option>
            @foreach (\App\Models\Project::STATUSES as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        <button class="bg-gray-800 text-white px-4 py-1.5 rounded-lg">Filter</button>
    </form>
    <a href="{{ route('projects.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New project</a>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($projects as $project)
        <a href="{{ route('projects.show', $project) }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 transition">
            <div class="flex justify-between items-start gap-2">
                <h3 class="font-semibold text-gray-900">{{ $project->name }}</h3>
                <x-status-badge :status="$project->status" type="project" />
            </div>
            <div class="text-xs text-gray-400 mt-1">{{ $project->client?->company_name }} · {{ $project->service_type ?? '—' }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $project->tasks_count }} tasks · {{ $project->members->count() }} members</div>
            <div class="h-1.5 bg-gray-100 rounded-full mt-3">
                <div class="h-1.5 bg-indigo-500 rounded-full" style="width: {{ $project->progress() }}%"></div>
            </div>
        </a>
    @empty
        <div class="sm:col-span-3">
            <x-empty-state icon="📁" title="No projects" message="Create your first project to organize client work." :action="route('projects.create')" actionLabel="New project" />
        </div>
    @endforelse
</div>
<x-pagination :paginator="$projects" />
@endsection
