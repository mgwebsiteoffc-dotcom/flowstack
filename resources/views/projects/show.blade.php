@extends('layouts.app')
@section('title', $project->name)
@section('breadcrumb', 'Projects / '.$project->name)
@section('content')
@php
    $doneCount = $project->tasks->where('status', 'done')->count();
    $totalCount = $project->tasks->count();
    $progress = $totalCount > 0 ? (int) round($doneCount * 100 / $totalCount) : ($project->status === 'completed' ? 100 : 0);
@endphp

<div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
    <div>
        <h1 class="text-lg font-bold text-gray-900">{{ $project->name }}</h1>
        <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500">
            <a href="{{ route('clients.show', $project->client) }}" class="text-indigo-600 hover:underline">{{ $project->client?->company_name }}</a>
            <x-status-badge :status="$project->status" type="project" />
            <span>{{ $project->service_type ?? '' }}</span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <div class="flex items-center gap-2 mr-2">
            <div class="h-1.5 w-28 bg-gray-200 rounded-full">
                <div class="h-1.5 bg-indigo-500 rounded-full" style="width: {{ $progress }}%"></div>
            </div>
            <span class="text-xs font-medium text-gray-600">{{ $progress }}%</span>
        </div>
        <a href="{{ route('tasks.board', ['project_id' => $project->id]) }}" class="px-2.5 py-1.5 text-xs rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Kanban</a>
        <a href="{{ route('tasks.create') }}" class="px-2.5 py-1.5 text-xs rounded-lg bg-indigo-600 text-white">+ Task</a>
        <a href="{{ route('projects.edit', $project) }}" class="px-2.5 py-1.5 text-xs rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Edit</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <!-- Left 2 cols: tasks + files -->
    <div class="lg:col-span-2 space-y-4">
        <x-card title="Tasks ({{ $doneCount }}/{{ $totalCount }})" icon="check-circle" :padding="false">
            <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                @forelse ($project->tasks as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="flex items-center gap-2.5 px-4 py-2 hover:bg-gray-50">
                        <x-status-badge :status="$task->status" />
                        <span class="text-sm text-gray-800 flex-1 truncate">{{ $task->title }}</span>
                        <x-priority-badge :priority="$task->priority" />
                        <span class="text-xs text-gray-400 w-24 truncate text-right">{{ $task->assignee?->name }}</span>
                        <span class="text-xs text-gray-400 w-14 text-right">{{ $task->due_date?->format('d M') }}</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">No tasks yet.</p>
                @endforelse
            </div>
        </x-card>

        <x-card title="Files ({{ $project->files->count() }})" icon="paper-clip">
            <div class="grid sm:grid-cols-2 gap-2">
                @forelse ($project->files as $file)
                    <div class="flex items-center gap-2 border border-gray-100 rounded-lg px-3 py-2">
                        @if ($file->isImage())<x-icon name="photo" class="w-4 h-4 text-gray-400" />
                        @elseif ($file->isPdf())<x-icon name="document" class="w-4 h-4 text-gray-400" />
                        @else<x-icon name="paper-clip" class="w-4 h-4 text-gray-400" />@endif
                        <span class="text-xs text-gray-700 truncate flex-1">{{ $file->original_name }}</span>
                        <span class="text-[10px] text-gray-400">{{ $file->sizeHuman() }}</span>
                        <a href="{{ route('files.download', $file) }}" class="text-indigo-600" title="Download"><x-icon name="arrow-down-tray" class="w-3.5 h-3.5" /></a>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-3 sm:col-span-2">No files yet - upload below.</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('files.upload') }}" enctype="multipart/form-data" class="flex gap-2 mt-3">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <input type="file" name="files[]" multiple class="text-xs flex-1">
                <button class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs">Upload</button>
            </form>
        </x-card>
    </div>

    <!-- Right col: details + team -->
    <div class="space-y-4">
        <x-card title="Details" icon="info">
            <dl class="space-y-1.5 text-xs">
                <div class="flex justify-between"><dt class="text-gray-400">Status</dt><dd><x-status-badge :status="$project->status" type="project" /></dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Service</dt><dd class="text-gray-700">{{ $project->service_type ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Dates</dt><dd class="text-gray-700">{{ $project->start_date?->format('d M') }} – {{ $project->end_date?->format('d M') ?? 'open' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Template</dt><dd class="text-gray-700">{{ $project->template?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Created</dt><dd class="text-gray-700">{{ $project->created_at->format('d M Y') }}</dd></div>
            </dl>
            @if ($project->description)
                <p class="text-xs text-gray-500 mt-2.5 bg-gray-50 rounded-lg p-2">{{ $project->description }}</p>
            @endif
        </x-card>

        <x-card title="Team ({{ $project->members->count() }})" icon="users">
            <div class="space-y-1.5 max-h-48 overflow-y-auto">
                @forelse ($project->members as $member)
                    <div class="flex items-center gap-2">
                        <x-user-avatar :user="$member->user" size="sm" />
                        <span class="text-xs text-gray-700 flex-1">{{ $member->user?->name }}</span>
                        <span class="text-[10px] bg-gray-100 rounded px-1.5 py-0.5 text-gray-500">{{ $member->role }}</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-2">No members</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('projects.members.store', $project) }}" class="flex gap-1.5 mt-2.5">
                @csrf
                <select name="user_id" class="flex-1 rounded-lg border border-gray-300 px-2 py-1 text-xs">
                    @foreach ($teamMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
                <select name="role" class="rounded-lg border border-gray-300 px-1.5 py-1 text-xs">
                    <option value="member">Member</option>
                    <option value="lead">Lead</option>
                </select>
                <button class="bg-indigo-600 text-white px-2.5 py-1 rounded-lg text-xs">Add</button>
            </form>
        </x-card>

        <x-card title="Status breakdown" icon="chart-bar">
            <div class="space-y-1.5">
                @foreach (\App\Models\Task::STATUSES as $status)
                    @php $count = $tasksByStatus[$status]?->count() ?? 0; @endphp
                    @if ($count > 0)
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-24 text-gray-500 capitalize truncate">{{ str_replace('_', ' ', $status) }}</span>
                            <div class="h-1.5 bg-gray-100 rounded-full flex-1">
                                <div class="h-1.5 bg-indigo-500 rounded-full" style="width: {{ $totalCount > 0 ? round($count * 100 / $totalCount) : 0 }}%"></div>
                            </div>
                            <span class="w-5 text-right font-medium">{{ $count }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </x-card>
    </div>
</div>
@endsection
