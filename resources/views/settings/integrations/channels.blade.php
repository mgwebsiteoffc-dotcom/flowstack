@extends('layouts.app')
@section('title', 'Slack & Teams')
@section('breadcrumb', 'Settings / Integrations / Slack & Teams')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'channels'])</div>
    <div class="lg:col-span-3 space-y-6">

        <form method="POST" action="{{ route('settings.integrations.channels.save') }}">
            @csrf
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Slack -->
                <x-card title="Slack" icon="chat-bubble-left-right">
                    <p class="text-xs text-gray-400 mb-3">Create a Slack app, enable <strong>Incoming Webhooks</strong>, and paste the webhook URL. Messages land in the channel you chose.</p>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL</label>
                    <input type="url" name="slack_webhook_url" value="{{ $slackUrl }}" placeholder="https://hooks.slack.com/services/…"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm mb-3">
                    <button formaction="{{ route('settings.integrations.channels.test-slack') }}" class="w-full bg-gray-800 text-white rounded-lg py-2 text-sm">Send test message</button>
                </x-card>

                <!-- Teams -->
                <x-card title="Microsoft Teams" icon="users">
                    <p class="text-xs text-gray-400 mb-3">In Teams, create a <strong>Workflows → Incoming webhook</strong> for your channel and paste the URL. Notifications arrive as Adaptive Cards.</p>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL</label>
                    <input type="url" name="teams_webhook_url" value="{{ $teamsUrl }}" placeholder="https://…webhook.office.com/webhookb2/…"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm mb-3">
                    <button formaction="{{ route('settings.integrations.channels.test-teams') }}" class="w-full bg-gray-800 text-white rounded-lg py-2 text-sm">Send test card</button>
                </x-card>
            </div>

            <x-card title="Events to notify" icon="bell">
                <div class="grid sm:grid-cols-2 gap-2">
                    @foreach ($events as $key => $label)
                        <label class="flex items-center gap-2 border rounded-lg px-3 py-2.5 text-sm text-gray-700 cursor-pointer hover:border-indigo-400">
                            <input type="checkbox" name="events[]" value="{{ $key }}" class="rounded"
                                   {{ is_array($enabledEvents) ? (in_array($key, $enabledEvents, true) ? 'checked' : '') : 'checked' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-3">When a webhook URL is set, these events are pushed to Slack and Teams. Unchecking all disables channel notifications.</p>
            </x-card>

            <div class="flex justify-end">
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save channel settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
