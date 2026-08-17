@extends('layouts.app')
@section('title', $project->name)
@section('breadcrumb', 'Projects / '.$project->name)
@section('content')
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ $project->name }}</h1>
        <div class="flex items-center gap-2 mt-1 text-sm text-gray-500">
            <a href="{{ route('clients.show', $project->client) }}" class="text-indigo-600 hover:underline">{{ $project->client?->company_name }}</a>
            <x-status-badge :status="$project->status" type="project" />
            <span>{{ $project->service_type ?? '' }}</span>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('tasks.board', ['project_id' => $project->id]) }}" class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Kanban</a>
        <a href="{{ route('tasks.create') }}" class="px-3 py-1.5 text-sm rounded-lg bg-indigo-600 text-white">+ Task</a>
        <a href="{{ route('projects.edit', $project) }}" class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Edit</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card title="Tasks by status" icon="✅">
            <div class="grid grid-cols-3 sm:grid-cols-7 gap-2">
                @foreach (\App\Models\Task::STATUSES as $status)
                    <div class="bg-gray-50 rounded-lg p-2 text-center">
                        <div class="text-lg font-bold text-gray-800">{{ $tasksByStatus[$status]?->count() ?? 0 }}</div>
                        <div class="text-[10px] text-gray-400">{{ str_replace('_', ' ', $status) }}</div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 divide-y divide-gray-50">
                @forelse ($project->tasks as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="flex items-center gap-3 py-2.5 hover:bg-gray-50 rounded-lg px-2">
                        <x-status-badge :status="$task->status" />
                        <span class="text-sm text-gray-800 flex-1 truncate">{{ $task->title }}</span>
                        <x-priority-badge :priority="$task->priority" />
                        <span class="text-xs text-gray-400">{{ $task->assignee?->name }}</span>
                        <span class="text-xs text-gray-400">{{ $task->due_date?->format('d M') }}</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No tasks yet.</p>
                @endforelse
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card title="Details" icon="ℹ️">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-400">Progress</dt><dd class="font-medium">{{ $project->progress() }}%</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Dates</dt><dd>{{ $project->start_date?->format('d M') }} – {{ $project->end_date?->format('d M') ?? 'open' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Template</dt><dd>{{ $project->template?->name ?? '—' }}</dd></div>
            </dl>
            @if ($project->description)<p class="text-xs text-gray-500 mt-3">{{ $project->description }}</p>@endif
        </x-card>

        <x-card title="Team" icon="👥">
            @forelse ($project->members as $member)
                <div class="flex items-center gap-2 py-1.5">
                    <x-user-avatar :user="$member->user" size="sm" />
                    <span class="text-sm text-gray-700 flex-1">{{ $member->user?->name }}</span>
                    <span class="text-xs bg-gray-100 rounded px-1.5 py-0.5 text-gray-500">{{ $member->role }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-2">No members</p>
            @endforelse
            <form method="POST" action="{{ route('projects.members.store', $project) }}" class="flex gap-2 mt-3">
                @csrf
                <select name="user_id" class="flex-1 rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                    @foreach ($teamMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
                <select name="role" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                    <option value="member">Member</option>
                    <option value="lead">Lead</option>
                </select>
                <button class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-sm">Add</button>
            </form>
        </x-card>
    </div>
</div>
@endsection
