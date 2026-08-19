@extends('layouts.app')
@section('title', $lead->contact_name)
@section('breadcrumb', 'Leads / '.$lead->contact_name)
@section('content')
@php
    $tabs = [
        ['id' => 'overview', 'label' => 'Overview', 'url' => '?tab=overview'],
        ['id' => 'activities', 'label' => 'Activities', 'url' => '?tab=activities'],
        ['id' => 'tasks', 'label' => 'Tasks', 'url' => '?tab=tasks'],
        ['id' => 'files', 'label' => 'Files', 'url' => '?tab=files'],
    ];
@endphp

<div class="flex items-start justify-between gap-4 mb-4 flex-wrap">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-purple-600 text-white flex items-center justify-center text-lg font-bold">{{ strtoupper(substr($lead->contact_name, 0, 1)) }}</div>
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $lead->contact_name }}</h1>
            <div class="flex items-center gap-2 mt-1">
                <x-source-badge :source="$lead->source_type" />
                <x-status-badge :status="$lead->status" type="lead" />
                @if ($lead->lead365_lead_id)<span class="text-xs text-gray-400">Lead365 ID: {{ $lead->lead365_lead_id }}</span>@endif
            </div>
        </div>
    </div>
    <div class="flex items-center gap-2">
        @if ($lead->status === 'active')
            <form method="POST" action="{{ route('leads.win', $lead) }}">@csrf
                <button class="px-3 py-1.5 text-sm rounded-lg bg-green-600 text-white hover:bg-green-700">Mark Won</button>
            </form>
            <form method="POST" action="{{ route('leads.lose', $lead) }}" id="lose-form">@csrf
                <input type="hidden" name="lost_reason" id="lost_reason">
                <button type="button" onclick="document.getElementById('lost_reason').value = prompt('Reason for losing this lead?') || ''; document.getElementById('lose-form').submit()"
                        class="px-3 py-1.5 text-sm rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Mark Lost</button>
            </form>
        @endif
        @if ($lead->status === 'won' && ! $lead->converted_to_client_id)
            <button x-data @click="$refs.convertModal.showModal()" class="px-3 py-1.5 text-sm rounded-lg bg-indigo-600 text-white">Convert to Client</button>
        @endif
        <a href="{{ route('proposals.create', ['lead_id' => $lead->id]) }}" class="px-3 py-1.5 text-sm rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Create Proposal</a>
        <a href="{{ route('leads.edit', $lead) }}" class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-700">Edit</a>
    </div>
</div>

@if ($lead->converted_to_client_id)
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm">
        <x-icon name="check-circle" class="w-4 h-4 inline-block" /> Converted to client: <a href="{{ route('clients.show', $lead->convertedClient) }}" class="font-medium underline">{{ $lead->convertedClient?->company_name }}</a>
    </div>
@endif

<dialog id="convert-modal" x-ref="convertModal" class="rounded-2xl shadow-2xl p-0 w-full max-w-md">
    <form method="POST" action="{{ route('leads.convert', $lead) }}" class="p-6 space-y-4">
        @csrf
        <h3 class="font-semibold text-gray-900">Convert "{{ $lead->contact_name }}" to a client?</h3>
        <p class="text-xs text-gray-500">A client will be created with the onboarding checklist, system folders and an onboarding project from the service template.</p>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Company name *</label>
            <input type="text" name="company_name" value="{{ $lead->company_name ?? $lead->contact_name }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        @if (auth()->user()->canViewFinancials())
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Monthly retainer (₹)</label>
            <input type="number" step="0.01" name="monthly_retainer" value="{{ $lead->won_value ?? $lead->estimated_value }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        @endif
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account manager</label>
            <select name="account_manager_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">—</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ $lead->assigned_to === $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Service type</label>
            <select name="service_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <option value="">—</option>
                @foreach (\App\Support\ServiceCatalog::all() as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-3 justify-end">
            <button type="button" @click="$refs.convertModal.close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
            <button class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">Convert & create client</button>
        </div>
    </form>
</dialog>

<x-tab-nav :tabs="$tabs" />

@if (request('tab', 'overview') === 'overview')
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Contact & company" icon="building-office">
                <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-400 text-xs">Email</dt><dd class="text-gray-800">{{ $lead->email ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">Phone</dt><dd class="text-gray-800">{{ $lead->phone ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">Company</dt><dd class="text-gray-800">{{ $lead->company_name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">Assignee</dt><dd class="text-gray-800">{{ $lead->assignee?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">Stage</dt><dd class="text-gray-800">{{ $lead->current_stage ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">Probability</dt><dd class="text-gray-800">{{ $lead->probability ?? '—' }}%</dd></div>
                    @if (auth()->user()->canViewFinancials())
                        <div><dt class="text-gray-400 text-xs">Estimated value</dt><dd class="text-gray-800 font-medium">₹{{ number_format($lead->estimated_value ?? 0) }}</dd></div>
                    @endif
                    <div><dt class="text-gray-400 text-xs">Expected close</dt><dd class="text-gray-800">{{ $lead->expected_close_date?->format('d M Y') ?? '—' }}</dd></div>
                    @if ($lead->won_at && auth()->user()->canViewFinancials())
                        <div><dt class="text-gray-400 text-xs">Won value</dt><dd class="text-green-600 font-medium">₹{{ number_format($lead->won_value ?? 0) }}</dd></div>
                        <div><dt class="text-gray-400 text-xs">Won at</dt><dd class="text-gray-800">{{ $lead->won_at->format('d M Y') }}</dd></div>
                    @endif
                    @if ($lead->lost_at)
                        <div><dt class="text-gray-400 text-xs">Lost reason</dt><dd class="text-red-600">{{ $lead->lost_reason ?? '—' }}</dd></div>
                    @endif
                </dl>
            </x-card>

        </div>

        <div class="space-y-6">
            <x-card title="Recent activity" icon="clock">
                @forelse ($lead->activities->take(6) as $activity)
                    <div class="py-2 border-b border-gray-50 last:border-0">
                        <div class="text-sm text-gray-800">{{ $activity->title }}</div>
                        <div class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-2">No activity yet</p>
                @endforelse
                <a href="?tab=activities" class="text-xs text-indigo-600 mt-2 inline-block">View all →</a>
            </x-card>
            <x-card title="Campaign data" icon="megaphone">
                <dl class="grid sm:grid-cols-2 gap-y-3 text-sm">
                    <div><dt class="text-gray-400 text-xs">Lead source</dt><dd class="text-gray-800">{{ $lead->lead_source ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">Campaign</dt><dd class="text-gray-800">{{ $lead->campaign_name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">Ad</dt><dd class="text-gray-800">{{ $lead->ad_name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">Form</dt><dd class="text-gray-800">{{ $lead->form_name ?? '—' }}</dd></div>
                </dl>
                @if ($lead->services_interested)
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach ($lead->services_interested as $service)
                            <span class="text-xs bg-indigo-50 text-indigo-700 rounded-full px-2.5 py-1">{{ $service }}</span>
                        @endforeach
                    </div>
                @endif
                @if ($lead->notes)<p class="mt-4 text-sm text-gray-600 bg-gray-50 rounded-lg p-3">{{ $lead->notes }}</p>@endif
            </x-card>
        </div>
    </div>

@elseif (request('tab') === 'activities')
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card title="Timeline" icon="clock">
                @forelse ($lead->activities as $activity)
                    <div class="flex gap-3 py-3 border-b border-gray-50 last:border-0">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm">
                            @if ($activity->activity_type === 'call')<x-icon name="phone" class="w-4 h-4" />
@elseif ($activity->activity_type === 'email')<x-icon name="envelope" class="w-4 h-4" />
@elseif ($activity->activity_type === 'meeting')<x-icon name="users" class="w-4 h-4" />
@elseif ($activity->activity_type === 'note')<x-icon name="pencil-square" class="w-4 h-4" />
@elseif ($activity->activity_type === 'stage_change')<x-icon name="arrow-path" class="w-4 h-4" />
@elseif ($activity->activity_type === 'assignment')<x-icon name="user" class="w-4 h-4" />
@else<x-icon name="link" class="w-4 h-4" />@endif
                        </div>
                        <div class="flex-1">
                            <div class="text-sm text-gray-800">{{ $activity->title }}</div>
                            @if ($activity->description)<p class="text-xs text-gray-500 mt-0.5">{{ $activity->description }}</p>@endif
                            @if ($activity->old_value || $activity->new_value)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $activity->old_value }} → <span class="text-gray-700">{{ $activity->new_value }}</span></p>
                            @endif
                            <div class="text-xs text-gray-400 mt-0.5">{{ $activity->performer?->name ?? 'Lead365' }} · {{ $activity->created_at->format('d M Y H:i') }} · {{ $activity->source }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">No activities logged.</p>
                @endforelse
            </x-card>
        </div>
        <x-card title="Log activity" icon="plus">
            <form method="POST" action="{{ route('leads.activities.store', $lead) }}" class="space-y-3">
                @csrf
                <select name="activity_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (['call', 'email', 'meeting', 'note'] as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
                <input type="text" name="title" placeholder="Title *" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <textarea name="description" rows="3" placeholder="Notes…" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <input type="datetime-local" name="scheduled_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <button class="w-full bg-indigo-600 text-white rounded-lg py-2 text-sm">Log activity</button>
            </form>
        </x-card>
    </div>

@elseif (request('tab') === 'tasks')
    <x-card title="Related tasks" icon="check-circle">
        @forelse ($lead->tasks as $task)
            <a href="{{ route('tasks.show', $task) }}" class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                <x-status-badge :status="$task->status" />
                <span class="text-sm text-gray-800 flex-1">{{ $task->title }}</span>
                <span class="text-xs text-gray-400">{{ $task->due_date?->format('d M') }}</span>
            </a>
        @empty
            <p class="text-sm text-gray-400 text-center py-4">No tasks linked to this lead.</p>
        @endforelse
    </x-card>

@else
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card title="Files" icon="paper-clip">
                @forelse ($lead->files as $file)
                    <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                        <span class="text-sm text-gray-800 flex-1 truncate">{{ $file->original_name }}</span>
                        <span class="text-xs text-gray-400">{{ $file->sizeHuman() }}</span>
                        @if ($file->isImage() || $file->isPdf())
                            <a href="{{ route('files.preview', $file) }}" target="_blank" class="text-xs text-gray-500">Preview</a>
                        @endif
                        <a href="{{ route('files.download', $file) }}" class="text-xs text-indigo-600">Download</a>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No files attached to this lead yet.</p>
                @endforelse
            </x-card>
        </div>
        <x-card title="Upload file" icon="arrow-up-tray">
            <form method="POST" action="{{ route('files.upload') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                <input type="file" name="files[]" required class="text-sm">
                <button class="w-full bg-indigo-600 text-white rounded-lg py-2 text-sm">Upload</button>
            </form>
        </x-card>
    </div>
@endif
@endsection
