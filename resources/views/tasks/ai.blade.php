@extends('layouts.app')
@section('title', 'AI task generator')
@section('breadcrumb', 'Tasks / AI generator')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex gap-2 text-sm">
        <a href="{{ route('tasks.index') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('tasks.index') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">List</a>
        <a href="{{ route('tasks.board') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('tasks.board') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">Board</a>
        <a href="{{ route('tasks.today') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('tasks.today') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">Today</a>
        <a href="{{ route('tasks.my-tasks') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('tasks.my-tasks') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">My tasks</a>
        <a href="{{ route('tasks.calendar') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('tasks.calendar') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">Calendar</a>
        <a href="{{ route('tasks.ai') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('tasks.ai') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}"><x-icon name="sparkles" class="w-4 h-4 inline-block" /> AI generate</a>
    </div>
</div>

<div class="max-w-4xl space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5" x-data="{ loading: false }">
        <div class="flex items-center gap-2 mb-1">
            <x-icon name="sparkles" class="w-5 h-5 text-indigo-600" />
            <h2 class="font-semibold text-gray-900">Describe your work in plain language</h2>
        </div>
        <p class="text-sm text-gray-500 mb-4">Paste a paragraph — the AI will read it and split it into one or more tasks (title, dates, client, priority).</p>

        @unless ($configured)
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 mb-4 text-sm flex items-start gap-2">
                <span><x-icon name="exclamation-triangle" class="w-4 h-4 inline-block" /></span>
                <span>The AI feature is not configured yet. Add <code class="font-mono">OPENROUTER_API_KEY</code> to your <code class="font-mono">.env</code> file (see <code class="font-mono">.env.example</code>).</span>
            </div>
        @endunless

        <form method="POST" action="{{ route('tasks.ai.generate') }}" @submit="loading = true">
            @csrf
            <textarea name="paragraph" rows="6" required
                placeholder="e.g. Create a task to build a mobile app, delivery date is 29 Aug 2026. Also design the logo for Acme Corp by next Monday and send them a weekly report every Friday."
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('paragraph', $paragraph) }}</textarea>

            <div class="flex items-center gap-3 mt-3">
                <button type="submit" :disabled="loading" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium disabled:opacity-60 inline-flex items-center gap-2">
                    <template x-if="!loading"><x-icon name="sparkles" class="w-4 h-4" /></template>
                    <template x-if="loading"><x-icon name="arrow-path" class="w-4 h-4 animate-spin" /></template>
                    <span x-text="loading ? 'Reading your paragraph…' : 'Generate tasks'"></span>
                </button>
                @if (!empty($draft))
                    <a href="{{ route('tasks.ai', ['reset' => 1]) }}" class="px-4 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Start over</a>
                @endif
            </div>
        </form>
    </div>

    @if (!empty($draft))
        <form method="POST" action="{{ route('tasks.ai.store') }}">
            @csrf
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                    <x-icon name="check-circle" class="w-5 h-5 text-indigo-600" />
                    Review {{ count($draft) }} task(s) before creating
                </h2>
                <button class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium">Create {{ count($draft) }} task(s)</button>
            </div>

            <div class="space-y-4">
                @foreach ($draft as $i => $task)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                                <input type="text" name="tasks[{{ $i }}][title]" value="{{ $task['title'] ?? '' }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                                <select name="tasks[{{ $i }}][client_id]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">—</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" {{ (int) ($task['client_id'] ?? 0) === (int) $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                                    @endforeach
                                </select>
                                @if (!empty($task['client']) && empty($task['client_id']))
                                    <p class="text-[11px] text-amber-600 mt-1">"{{ $task['client'] }}" not matched — pick a client if needed.</p>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                <select name="tasks[{{ $i }}][priority]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    @foreach (\App\Models\Task::PRIORITIES as $p)
                                        <option value="{{ $p }}" {{ ($task['priority'] ?? 'medium') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start date</label>
                                <input type="date" name="tasks[{{ $i }}][start_date]" value="{{ $task['start_date'] ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Due date</label>
                                <input type="date" name="tasks[{{ $i }}][due_date]" value="{{ $task['due_date'] ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estimated hours</label>
                                <input type="number" step="0.5" min="0" name="tasks[{{ $i }}][estimated_hours]" value="{{ $task['estimated_hours'] ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="tasks[{{ $i }}][description]" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ $task['description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3 mt-5">
                <a href="{{ route('tasks.ai', ['reset' => 1]) }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Create {{ count($draft) }} task(s)</button>
            </div>
        </form>
    @endif
</div>
@endsection
