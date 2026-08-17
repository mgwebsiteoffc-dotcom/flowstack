@extends('layouts.portal')
@section('title', $project->name)
@section('content')
<a href="{{ route('portal.projects') }}" class="text-sm text-indigo-600">← All projects</a>
<h1 class="text-xl font-bold text-gray-900 mt-2 mb-1">{{ $project->name }}</h1>
<p class="text-sm text-gray-500 mb-5 capitalize">{{ str_replace('_', ' ', $project->status) }} · {{ $project->service_type }}</p>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Tasks</h3>
            <div class="divide-y divide-gray-50">
                @forelse ($project->tasks as $task)
                    <div class="flex items-center gap-3 py-2.5">
                        <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px] {{ $task->status === 'done' ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300' }}">{{ $task->status === 'done' ? '' : '' }}</span>
                        <span class="text-sm {{ $task->status === 'done' ? 'text-gray-400 line-through' : 'text-gray-800' }} flex-1">{{ $task->title }}</span>
                        @if ($task->due_date)
                            <span class="text-xs text-gray-400">{{ $task->due_date->format('d M') }}</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">No tasks in this project yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Your team</h3>
            @forelse ($project->members as $member)
                <div class="flex items-center gap-2 py-1.5">
                    <div class="w-8 h-8 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($member->user?->name ?? '?', 0, 1)) }}</div>
                    <span class="text-sm text-gray-700 flex-1">{{ $member->user?->name }}</span>
                    <span class="text-xs text-gray-400 capitalize">{{ $member->role }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-2">Team to be assigned.</p>
            @endforelse
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Progress</h3>
            @php $done = $project->tasks->where('status', 'done')->count(); $total = $project->tasks->count(); @endphp
            <div class="text-3xl font-black text-indigo-600">{{ $total > 0 ? round($done * 100 / $total) : 0 }}%</div>
            <div class="h-2 bg-gray-100 rounded-full mt-2">
                <div class="h-2 bg-indigo-500 rounded-full" style="width: {{ $total > 0 ? $done * 100 / $total : 0 }}%"></div>
            </div>
        </div>
    </div>
</div>
@endsection
