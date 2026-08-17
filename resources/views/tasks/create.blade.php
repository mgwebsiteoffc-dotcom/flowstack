@extends('layouts.app')
@section('title', 'New task')
@section('content')
<form method="POST" action="{{ route('tasks.store') }}" class="max-w-3xl space-y-6">
    @csrf
    <x-card title="Task details" icon="check-circle">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Task::STATUSES as $s)
                        <option value="{{ $s }}" {{ old('status', 'todo') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select name="priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Task::PRIORITIES as $p)
                        <option value="{{ $p }}" {{ old('priority', 'medium') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                <select name="client_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">—</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                <select name="project_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">—</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Assignee</label>
                <select name="assigned_to" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Unassigned</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Due date</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Service type</label>
                <select name="service_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">—</option>
                    @foreach (\App\Models\ClientService::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ old('service_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                    <option value="internal" {{ old('service_type') === 'internal' ? 'selected' : '' }}>Internal</option>
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Task type</label>
                <select name="task_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Task::TASK_TYPES as $t)
                        <option value="{{ $t }}" {{ old('task_type', 'one_time') === $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Estimated hours</label>
                <input type="number" step="0.5" name="estimated_hours" value="{{ old('estimated_hours') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Actual hours</label>
                <input type="number" step="0.5" name="actual_hours" value="{{ old('actual_hours') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
        </div>
    </x-card>

    <x-card title="Recurring" icon="arrow-path">
        <label class="flex items-center gap-2 text-sm text-gray-700 mb-3">
            <input type="checkbox" name="is_recurring" value="1" x-data x-init="$el.addEventListener('change', e => document.getElementById('recurring-box').classList.toggle('hidden', !e.target.checked))" class="rounded">
            This is a recurring task
        </label>
        <div id="recurring-box" class="hidden grid sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Repeat</label>
                <select name="recurrence_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Task::RECURRENCE_TYPES as $r)
                        <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Interval</label>
                <input type="number" name="recurrence_interval" value="1" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Next occurrence</label>
                <input type="date" name="next_recurrence_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Ends at</label>
                <input type="date" name="recurrence_ends_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
        </div>
    </x-card>

    <div class="flex justify-end gap-3">
        <a href="{{ route('tasks.index') }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Create task</button>
    </div>
</form>
@endsection
