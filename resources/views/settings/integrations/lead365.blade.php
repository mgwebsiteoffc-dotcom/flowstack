@extends('layouts.app')
@section('title', 'Lead365 Integration')
@section('breadcrumb', 'Settings / Integrations / Lead365')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'lead365'])</div>
    <div class="lg:col-span-3 space-y-6">
        <x-card title="Webhook URL" icon="🔗">
            <div x-data="{ copied: false }" class="flex items-center gap-2 bg-gray-50 rounded-lg p-3 border border-gray-200">
                <code class="text-xs text-indigo-700 break-all flex-1">{{ url('webhooks/lead365/'.$tenant->slug) }}</code>
                <button type="button" @click="navigator.clipboard.writeText('{{ url('webhooks/lead365/'.$tenant->slug) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg" x-text="copied ? 'Copied ✅' : 'Copy'"></button>
            </div>
            <p class="text-xs text-gray-400 mt-2">Paste this URL in your Lead365 account and subscribe to: lead.created, lead.updated, lead.deleted, lead.stage_changed, lead.assigned, lead.won, lead.lost, form.submitted, meta.lead.received</p>
            <div class="mt-3">
                <form method="POST" action="{{ route('settings.integrations.lead365.test') }}">@csrf
                    <button class="text-xs bg-gray-800 text-white px-4 py-1.5 rounded-lg">▶ Test Connection (send test event)</button>
                </form>
            </div>
        </x-card>

        <form method="POST" action="{{ route('settings.integrations.lead365.save') }}" class="space-y-6">
            @csrf
            <x-card title="Settings" icon="⚙️">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Webhook secret <span class="text-gray-400">(optional, for request verification)</span></label>
                        <input type="text" name="lead365_webhook_secret" value="{{ $tenant->lead365_webhook_secret }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Ads leads →</label>
                        <select name="autoassign_meta" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">No auto-assignment</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ $settings['lead365_autoassign_meta'] == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Form submissions →</label>
                        <select name="autoassign_form" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">No auto-assignment</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ $settings['lead365_autoassign_form'] == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lead365 leads →</label>
                        <select name="autoassign_lead365" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">No auto-assignment</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ $settings['lead365_autoassign_lead365'] == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <div class="font-medium text-sm text-gray-700 mb-1">Notifications</div>
                    @foreach ([
                        'notify_meta' => 'Meta lead received (high priority)',
                        'notify_won' => 'Lead won',
                        'notify_lost' => 'Lead lost',
                        'notify_stage' => 'Stage change',
                        'notify_form' => 'Form submission',
                    ] as $key => $label)
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="{{ $key }}" value="1" class="rounded"
                                   {{ ($settings['lead365_notify_'.str_replace('notify_', '', $key)] ?? '1') != '0' ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </x-card>

            <x-card title="Stage mapping (Lead365 stage → local stage)" icon="🪜">
                <p class="text-xs text-gray-400 mb-3">When Lead365 sends a stage id, it is mapped to your local pipeline stage. The mapping keys come from Lead365's stage ids.</p>
                <div class="space-y-2">
                    @foreach ($stages as $stage)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-40 text-gray-600">{{ $stage->name }}</span>
                            <span class="text-gray-300">← Lead365 stage ID:</span>
                            <input type="text" name="stage_mappings[{{ $stage->lead365_stage_id ?? 'stage_'.$stage->id }}]" value="{{ $stage->lead365_stage_id }}"
                                   placeholder="(leave empty to disable)" class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                        </div>
                    @endforeach
                </div>
            </x-card>

            <div class="flex justify-end">
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save Lead365 settings</button>
            </div>
        </form>

        <x-card title="Webhook event log (last 50)" icon="🕓" :padding="false">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-2.5">Time</th><th class="px-4 py-2.5">Event</th><th class="px-4 py-2.5">Status</th><th class="px-4 py-2.5">Lead</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-2 text-xs text-gray-400">{{ $log->created_at->format('d M H:i:s') }}</td>
                            <td class="px-4 py-2"><code class="text-xs text-indigo-700">{{ $log->event_type }}</code></td>
                            <td class="px-4 py-2">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $log->status === 'processed' ? 'bg-green-100 text-green-700' : ($log->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $log->status }}</span>
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-500">
                                {{ collect($log->payload['data'] ?? [])->get('name', '—') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-sm text-gray-400">No webhook events received yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>
</div>
@endsection
