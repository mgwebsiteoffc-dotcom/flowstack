@extends('layouts.app')
@section('title', $client->company_name)
@section('breadcrumb', $client->company_name)
@section('content')
@php
    $tabs = [
        ['id' => 'overview', 'label' => 'Overview', 'url' => route('clients.show', [$client, 'tab' => 'overview'])],
        ['id' => 'projects', 'label' => 'Projects', 'url' => route('clients.show', [$client, 'tab' => 'projects'])],
        ['id' => 'tasks', 'label' => 'Tasks', 'url' => route('clients.show', [$client, 'tab' => 'tasks'])],
        ['id' => 'reports', 'label' => 'Reports', 'url' => route('clients.show', [$client, 'tab' => 'reports'])],
        ['id' => 'finance', 'label' => 'Finance', 'url' => route('clients.show', [$client, 'tab' => 'finance']), 'finance' => true],
        ['id' => 'files', 'label' => 'Files', 'url' => route('clients.show', [$client, 'tab' => 'files'])],
        ['id' => 'contacts', 'label' => 'Contacts', 'url' => route('clients.show', [$client, 'tab' => 'contacts'])],
        ['id' => 'activity', 'label' => 'Activity', 'url' => route('clients.show', [$client, 'tab' => 'activity'])],
        ['id' => 'notes', 'label' => 'Notes', 'url' => route('clients.show', [$client, 'tab' => 'notes'])],
    ];

    // Hide the Finance tab from users who can't view billing terms.
    $tabs = array_values(array_filter($tabs, fn ($t) => ! isset($t['finance']) || auth()->user()->canViewFinancials()));
@endphp

<div class="flex items-start justify-between gap-4 mb-4 flex-wrap">
    <div class="flex items-center gap-4">
        @if ($client->logo_url)
            <img src="{{ $client->logo_url }}" alt="{{ $client->company_name }}" class="w-14 h-14 rounded-xl object-cover border border-gray-200">
        @else
            <div class="w-14 h-14 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xl font-bold">{{ strtoupper(substr($client->company_name, 0, 1)) }}</div>
        @endif
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $client->company_name }}</h1>
            <div class="flex items-center gap-2 mt-1">
                <x-status-badge :status="$client->status" type="client" />
                <x-health-badge :score="$client->health_score" />
                @if ($client->industry)<span class="text-xs text-gray-400">{{ $client->industry }}</span>@endif
            </div>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('clients.edit', $client) }}" class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">Edit</a>
        @can('delete', $client)
            <x-confirm-delete :action="route('clients.destroy', $client)" message="Delete this client and all related data?">
                <x-slot:trigger><span class="px-3 py-1.5 text-sm rounded-lg bg-red-50 hover:bg-red-100 text-red-600">Delete</span></x-slot:trigger>
            </x-confirm-delete>
        @endcan
    </div>
</div>

@if ($client->status === 'onboarding' || $client->onboardingItems()->where('is_completed', false)->exists())
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5" x-data="{ progress: {{ $client->onboardingProgress() }}, remaining: {{ $client->onboardingItems()->where('is_completed', false)->count() }}, init() { window.addEventListener('onboarding-updated', e => { this.progress = e.detail.progress; this.remaining = e.detail.remaining; }); } }">
        <div class="flex items-center justify-between text-sm">
            <span class="font-medium text-amber-800">Onboarding progress: <span x-text="progress + '%'"></span></span>
            <span class="text-xs text-amber-600"><span x-text="remaining"></span> items remaining</span>
        </div>
        <div class="h-2 bg-amber-100 rounded-full mt-2">
            <div class="h-2 bg-amber-500 rounded-full transition-all" :style="'width: ' + progress + '%'"></div>
        </div>
    </div>
@endif

<x-tab-nav :tabs="$tabs" />

@if ($tab === 'overview')
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Company info" icon="building-office">
                <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-400 text-xs">Industry</dt><dd class="text-gray-800">{{ $client->industry === 'Other' ? ($client->industry_other ?? 'Other') : ($client->industry ?? '—') }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">Website</dt><dd class="text-gray-800">{{ $client->website ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">GSTIN</dt><dd class="text-gray-800">{{ $client->gstin ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">Address</dt><dd class="text-gray-800">{{ $client->address ?: '—' }}@if ($client->city || $client->pincode)<div class="text-xs">{{ $client->city }}{{ $client->state ? ', '.$client->state : '' }}{{ $client->pincode ? ' - '.$client->pincode : '' }}</div>@endif</dd></div>
                    <div><dt class="text-gray-400 text-xs">Account manager</dt><dd class="text-gray-800">{{ $client->accountManager?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-400 text-xs">Contract</dt><dd class="text-gray-800">{{ $client->contract_start_date?->format('d M Y') }} → {{ $client->contract_end_date?->format('d M Y') ?? 'open' }}</dd></div>
                    @if (auth()->user()->canViewFinancials())
                        <div><dt class="text-gray-400 text-xs">Monthly retainer</dt><dd class="text-gray-800 font-medium">₹{{ number_format($client->monthly_retainer ?? 0) }}</dd></div>
                    @endif
                </dl>
                @if ($client->notes)<div class="mt-4 text-sm text-gray-600 bg-gray-50 rounded-lg p-3">{{ $client->notes }}</div>@endif
            </x-card>

            <x-card title="Services" icon="wrench">
                <div class="flex flex-wrap gap-2">
                    @forelse ($client->services as $service)
                        <span class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 rounded-full px-3 py-1 text-sm">
                            {{ $service->type_label }}
                            @if ($service->monthly_price && auth()->user()->canViewFinancials())<span class="text-xs">₹{{ number_format($service->monthly_price) }}/mo</span>@endif
                        </span>
                    @empty
                        <span class="text-sm text-gray-400">No services selected</span>
                    @endforelse
                </div>
            </x-card>


            <x-card title="Onboarding checklist" icon="clipboard" x-data="onboardingChecklist({{ $client->onboardingProgress() }}, {{ $client->onboardingItems()->count() }}, {{ $client->onboardingItems()->where('is_completed', true)->count() }})">
                @foreach ($client->onboardingItems as $item)
                    <div x-data="{ editing: false }" class="py-2 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-3">
                            <button type="button" @click="toggle({{ $item->id }}, $event.currentTarget)"
                                    class="w-5 h-5 rounded border-2 flex items-center justify-center text-white text-xs shrink-0 transition
                                    {{ $item->is_completed ? 'bg-green-500 border-green-500' : 'border-gray-300 hover:border-green-500' }}"
                                    :class="{ 'bg-green-500 border-green-500': isDone({{ $item->id }}), 'border-gray-300': !isDone({{ $item->id }}) }"
                                    :disabled="busy">
                                <x-icon name="check" class="w-3 h-3" x-show="isDone({{ $item->id }})" />
                            </button>
                            <span class="text-sm flex-1 transition" :class="isDone({{ $item->id }}) ? 'text-gray-400 line-through' : 'text-gray-700'">{{ $item->title }}</span>
                            <span class="text-xs text-gray-400">{{ $item->assignee?->name }}</span>
                            @if ($item->due_date)<span class="text-xs text-gray-400">{{ $item->due_date->format('d M') }}</span>@endif
                            <button @click="editing = !editing" class="text-xs text-gray-400 hover:text-gray-600"><x-icon name="pencil" class="w-4 h-4 inline-block" /></button>
                        </div>
                        <form method="POST" action="{{ route('clients.onboarding.update', [$client, $item->id]) }}" x-show="editing" x-cloak class="flex gap-2 mt-2 pl-8">
                            @csrf
                            @method('PATCH')
                            <select name="assigned_to" class="rounded-lg border border-gray-300 px-2 py-1 text-xs">
                                <option value="">Unassigned</option>
                                @foreach ($teamMembers as $member)
                                    <option value="{{ $member->id }}" {{ $item->assigned_to === $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                @endforeach
                            </select>
                            <input type="date" name="due_date" value="{{ $item->due_date?->toDateString() }}" class="rounded-lg border border-gray-300 px-2 py-1 text-xs">
                            <button class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-xs">Save</button>
                        </form>
                    </div>
                @endforeach
                <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                    <div class="h-1.5 bg-gray-100 rounded-full flex-1">
                        <div class="h-1.5 bg-green-500 rounded-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
                    </div>
                    <span x-text="done + '/' + total + ' done (' + progress + '%)'"></span>
                </div>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Quick stats" icon="chart-bar">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div><div class="text-xl font-bold text-gray-900">{{ $client->projects()->count() }}</div><div class="text-xs text-gray-400">Projects</div></div>
                    <div><div class="text-xl font-bold text-gray-900">{{ $client->tasks()->whereNotIn('status', ['done', 'cancelled'])->count() }}</div><div class="text-xs text-gray-400">Open tasks</div></div>
                    <div><div class="text-xl font-bold text-gray-900"><x-financial>₹{{ number_format($client->outstandingBalance()) }}</x-financial></div><div class="text-xs text-gray-400">Outstanding</div></div>
                </div>
            </x-card>
            <x-card title="Contacts" icon="identification">
                @forelse ($client->contacts as $contact)
                    <div class="py-2 border-b border-gray-50 last:border-0">
                        <div class="text-sm font-medium text-gray-800">{{ $contact->name }} @if ($contact->is_primary)<span class="text-[10px] bg-indigo-100 text-indigo-700 rounded px-1.5 py-0.5">Primary</span>@endif</div>
                        <div class="text-xs text-gray-400">{{ $contact->email }} · {{ $contact->phone }}</div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-3">No contacts yet</p>
                @endforelse
                <a href="?tab=contacts" class="text-xs text-indigo-600 mt-2 inline-block">Manage contacts →</a>
            </x-card>

            <x-card title="Team members" icon="users">
                @forelse ($client->teamMembers as $member)
                    <div class="flex items-center gap-2 py-1.5">
                        <x-user-avatar :user="$member->user" size="sm" />
                        <span class="text-sm text-gray-700 flex-1">{{ $member->user?->name }}</span>
                        <span class="text-xs text-gray-400">{{ $member->role_in_project }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-3">No team assigned</p>
                @endforelse
            </x-card>

            <x-card title="Client portal" icon="lock-closed">
                <div class="text-sm text-gray-600 mb-3">
                    {{ $client->portal_access_enabled ? 'Portal is enabled' : 'Portal is disabled' }}
                </div>
                <div x-data="{ open: {{ $client->portal_access_enabled ? 'false' : 'true' }} }">
                    @if (! $client->portal_access_enabled)
                        <form method="POST" action="{{ route('clients.portal-access', $client) }}" class="space-y-2">
                            @csrf
                            <input type="hidden" name="enabled" value="1">
                            <input type="email" name="email" placeholder="Client email" required class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                            <input type="text" name="name" placeholder="Contact name" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                            <button class="w-full bg-indigo-600 text-white rounded-lg py-1.5 text-sm">Enable portal & send invite</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('clients.portal-access', $client) }}">
                            @csrf
                            <input type="hidden" name="enabled" value="0">
                            <button class="w-full bg-gray-100 text-gray-600 rounded-lg py-1.5 text-sm">Disable portal</button>
                        </form>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

@elseif ($tab === 'projects')
    <div class="flex justify-end mb-4">
        <a href="{{ route('projects.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">+ New project</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($projects as $project)
            <a href="{{ route('projects.show', $project) }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 transition">
                <div class="flex justify-between items-start">
                    <h3 class="font-semibold text-gray-900">{{ $project->name }}</h3>
                    <x-status-badge :status="$project->status" type="project" />
                </div>
                <div class="text-xs text-gray-400 mt-2">{{ $project->service_type ?? '—' }} · {{ $project->tasks_count }} tasks</div>
                <div class="h-1.5 bg-gray-100 rounded-full mt-3">
                    <div class="h-1.5 bg-indigo-500 rounded-full" style="width: {{ $project->progress() }}%"></div>
                </div>
            </a>
        @empty
            <div class="sm:col-span-3"><x-empty-state icon="folder" title="No projects" message="Create a project to organize work for this client." :action="route('projects.create')" actionLabel="New project" /></div>
        @endforelse
    </div>

@elseif ($tab === 'tasks')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                <tr><th class="px-4 py-3">Task</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Priority</th><th class="px-4 py-3">Assignee</th><th class="px-4 py-3">Due</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($tasks as $task)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="{{ route('tasks.show', $task) }}" class="text-gray-900 hover:text-indigo-600 font-medium">{{ $task->title }}</a></td>
                        <td class="px-4 py-3"><x-status-badge :status="$task->status" /></td>
                        <td class="px-4 py-3"><x-priority-badge :priority="$task->priority" /></td>
                        <td class="px-4 py-3 text-gray-600">{{ $task->assignee?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $task->due_date?->format('d M') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state icon="check-circle" title="No tasks" message="No tasks for this client yet." :action="route('tasks.create')" actionLabel="New task" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$tasks" />

@elseif ($tab === 'reports')
    <div class="flex justify-end mb-4">
        <a href="{{ route('reports.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">+ New report</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($client->reports->sortByDesc('created_at') as $report)
            <a href="{{ route('reports.show', $report) }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 transition">
                <div class="font-semibold text-gray-900 text-sm">{{ $report->title }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ ucfirst($report->report_type) }} · {{ $report->period_start->format('d M') }} – {{ $report->period_end->format('d M') }}</div>
                <div class="mt-2"><x-status-badge :status="$report->status" type="invoice" /></div>
            </a>
        @empty
            <div class="sm:col-span-3"><x-empty-state icon="chart-bar" title="No reports" message="Create performance reports for this client." :action="route('reports.create')" actionLabel="New report" /></div>
        @endforelse
    </div>

@elseif ($tab === 'finance' && auth()->user()->canViewFinancials())
    <div class="flex justify-end mb-4">
        <a href="{{ route('finance.invoices.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">+ New invoice</a>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                <tr><th class="px-4 py-3">Invoice</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">BikriBook</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3 text-right">Due</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="{{ route('finance.invoices.show', $invoice) }}" class="font-medium text-indigo-600">{{ $invoice->invoice_number }}</a></td>
                        <td class="px-4 py-3"><x-status-badge :status="$invoice->status" type="invoice" /></td>
                        <td class="px-4 py-3"><x-bb-status :invoice="$invoice" /></td>
                        <td class="px-4 py-3 text-right font-medium">₹{{ number_format($invoice->total_amount) }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $invoice->due_date->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state icon="receipt" title="No invoices" message="Create an invoice for this client." :action="route('finance.invoices.create')" actionLabel="New invoice" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$invoices" />

@elseif ($tab === 'files')
    <div class="flex justify-end mb-4">
        <a href="{{ route('files.index', ['client_id' => $client->id]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Open file manager →</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($files as $file)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="text-2xl mb-2">
                    @if ($file->isImage())<x-icon name="photo" class="w-6 h-6" />
                    @elseif ($file->isPdf())<x-icon name="document" class="w-6 h-6" />
                    @else<x-icon name="paper-clip" class="w-6 h-6" />@endif
                </div>
                <div class="text-sm font-medium text-gray-800 truncate">{{ $file->original_name }}</div>
                <div class="text-xs text-gray-400">{{ $file->sizeHuman() }} · v{{ $file->version }}</div>
                <a href="{{ route('files.download', $file) }}" class="text-xs text-indigo-600 mt-2 inline-block">Download</a>
            </div>
        @empty
            <div class="sm:col-span-3"><x-empty-state icon="paper-clip" title="No files" message="Upload files for this client from the file manager." /></div>
        @endforelse
    </div>
    <x-pagination :paginator="$files" />

@elseif ($tab === 'contacts')
    <div class="grid lg:grid-cols-2 gap-6">
        <x-card title="Contacts" icon="identification">
            @forelse ($client->contacts as $contact)
                <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                    <div>
                        <div class="text-sm font-medium text-gray-800">
                            {{ $contact->name }}
                            @if ($contact->is_primary)<span class="text-[10px] bg-indigo-100 text-indigo-700 rounded px-1.5 py-0.5">Primary</span>@endif
                            @if ($contact->is_billing_contact)<span class="text-[10px] bg-green-100 text-green-700 rounded px-1.5 py-0.5">Billing</span>@endif
                        </div>
                        <div class="text-xs text-gray-400">{{ $contact->email }} · {{ $contact->phone }} · {{ $contact->designation }}</div>
                    </div>
                    <form method="POST" action="{{ route('clients.contacts.destroy', [$client, $contact]) }}" onsubmit="return confirm('Remove this contact?')">
                        @csrf @method('DELETE')
                        <button class="text-red-400 hover:text-red-600 text-sm"><x-icon name="x-mark" class="w-4 h-4" /></button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-3">No contacts yet</p>
            @endforelse
        </x-card>
        <x-card title="Add contact" icon="plus">
            <form method="POST" action="{{ route('clients.contacts.store', $client) }}" class="space-y-3">
                @csrf
                <input type="text" name="name" placeholder="Full name *" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="email" name="email" placeholder="Email" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="text" name="phone" placeholder="Phone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <input type="text" name="designation" placeholder="Designation" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_primary" value="1" class="rounded"> Primary contact</label>
                <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_billing_contact" value="1" class="rounded"> Billing contact</label>
                <button class="w-full bg-indigo-600 text-white rounded-lg py-2 text-sm">Add contact</button>
            </form>
        </x-card>
    </div>

@elseif ($tab === 'activity')
    <x-card title="Activity" icon="clock">
        @forelse ($activity as $log)
            <div class="flex items-start gap-3 py-2.5 border-b border-gray-50 last:border-0">
                <x-user-avatar :user="$log->user" size="sm" />
                <div class="min-w-0">
                    <div class="text-sm text-gray-800">{{ $log->action }}</div>
                    <div class="text-xs text-gray-400">{{ $log->created_at?->diffForHumans() }} @if ($log->new_values) · {{ json_encode($log->new_values) }} @endif</div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-4">No activity recorded yet.</p>
        @endforelse
    </x-card>

@else
    <div class="grid lg:grid-cols-2 gap-6">
        <x-card title="Notes" icon="pencil-square">
            @forelse ($notes as $note)
                <div class="py-3 border-b border-gray-50 last:border-0 {{ $note->is_pinned ? 'bg-amber-50 rounded-lg px-2' : '' }}">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] uppercase font-bold {{ $note->note_type === 'warning' ? 'text-amber-600' : ($note->note_type === 'important' ? 'text-red-600' : 'text-gray-400') }}">{{ $note->note_type }}</span>
                        @if ($note->is_pinned)<span class="text-[10px] bg-amber-100 text-amber-700 rounded px-1.5 py-0.5"><x-icon name="map-pin" class="w-4 h-4 inline-block" /> Pinned</span>@endif
                        <span class="text-xs text-gray-400 ml-auto">{{ $note->creator?->name }} · {{ $note->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $note->note }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-3">No notes yet</p>
            @endforelse
        </x-card>
        <x-card title="Add note" icon="plus">
            <form method="POST" action="{{ route('clients.notes.store', $client) }}" class="space-y-3">
                @csrf
                <textarea name="note" rows="4" placeholder="Write a note…" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
                <select name="note_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="info">Info</option>
                    <option value="warning">Warning</option>
                    <option value="important">Important</option>
                </select>
                <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_pinned" value="1" class="rounded"> Pin this note</label>
                <button class="w-full bg-indigo-600 text-white rounded-lg py-2 text-sm">Add note</button>
            </form>
        </x-card>
    </div>
@endif
@push('scripts')
<script>
function onboardingChecklist(initialProgress, total, initialDone) {
    return {
        progress: initialProgress,
        total: total,
        done: initialDone,
        busy: false,
        doneIds: @json($client->onboardingItems->where('is_completed', true)->pluck('id')->map(fn ($i) => (int) $i)),
        isDone(id) { return this.doneIds.includes(id); },
        async toggle(id, btn) {
            if (this.busy) return;
            this.busy = true;
            try {
                const res = await fetch(@json(route('clients.onboarding.toggle', [$client, 0])).replace('/0', '/' + id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (data.ok) {
                    if (data.is_completed && !this.doneIds.includes(data.item_id)) this.doneIds.push(data.item_id);
                    else if (!data.is_completed) this.doneIds = this.doneIds.filter(i => i !== data.item_id);
                    this.done = this.doneIds.length;
                    this.progress = data.progress;
                    window.dispatchEvent(new CustomEvent('onboarding-updated', { detail: { progress: this.progress, remaining: this.total - this.done } }));
                }
            } catch (e) {
                window.location.reload();
            } finally {
                this.busy = false;
            }
        }
    }
}
</script>
@endpush
@endsection
