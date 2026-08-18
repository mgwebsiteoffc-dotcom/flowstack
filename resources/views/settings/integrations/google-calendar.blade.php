@extends('layouts.app')
@section('title', 'Google Calendar & Meet')
@section('breadcrumb', 'Settings / Integrations / Google Calendar')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'google-calendar'])</div>
    <div class="lg:col-span-3 space-y-6">

        <x-card title="Google Calendar sync + Google Meet" icon="calendar">
            <div class="flex items-start gap-4 flex-wrap">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <x-icon name="calendar" class="w-6 h-6" />
                </div>
                <div class="flex-1 min-w-[260px]">
                    @if ($connected)
                        <div class="flex items-center gap-2 text-sm text-green-700 font-medium mb-1">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Connected as {{ $email ?? 'your Google account' }}
                        </div>
                        <p class="text-xs text-gray-500">Tasks with due dates are synced to your calendar as 10:00 AM events, each with an automatic <strong>Google Meet</strong> link.</p>
                    @else
                        <div class="text-sm text-gray-700 font-medium mb-1">Not connected</div>
                        <p class="text-xs text-gray-500 mb-4">Connect your Google account to sync tasks to your calendar and auto-generate Google Meet links for every deliverable.</p>
                        @if (! \App\Services\GoogleCalendarService::isConfigured())
                            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 text-xs">
                                <strong>Configuration required:</strong> add to <code>.env</code>:
                                <code class="block mt-1">GOOGLE_CLIENT_ID</code>
                                <code class="block">GOOGLE_CLIENT_SECRET</code>
                                <code class="block">GOOGLE_REDIRECT_URI</code>
                                then create OAuth credentials (scope: <code>https://www.googleapis.com/auth/calendar.events</code>) in Google Cloud Console.
                            </div>
                        @endif
                    @endif
                </div>
                <div class="flex gap-2">
                    @if ($connected)
                        <form method="POST" action="{{ route('settings.integrations.google-calendar.disconnect') }}" onsubmit="return confirm('Disconnect Google Calendar?')">@csrf
                            <button class="px-4 py-2 rounded-lg bg-red-50 text-red-600 text-sm">Disconnect</button>
                        </form>
                    @else
                        <a href="{{ route('settings.integrations.google-calendar.connect') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium">Connect Google account</a>
                    @endif
                </div>
            </div>
        </x-card>

        @if ($connected)
            <form method="POST" action="{{ route('settings.integrations.google-calendar.save') }}">
                @csrf
                <x-card title="Sync preferences" icon="cog-6-tooth">
                    <label class="flex items-center gap-2 border rounded-lg px-3 py-2.5 text-sm text-gray-700 cursor-pointer hover:border-indigo-400">
                        <input type="checkbox" name="sync_tasks" value="1" class="rounded" {{ $syncEnabled ? 'checked' : '' }}>
                        Sync tasks with due dates to Google Calendar (with Google Meet links)
                    </label>
                    <div class="flex justify-end mt-4">
                        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save preferences</button>
                    </div>
                </x-card>
            </form>

            <x-card title="How it works" icon="info">
                <ul class="text-sm text-gray-600 space-y-2 list-disc pl-5">
                    <li>Creating or updating a task with a due date creates a calendar event at 10:00 AM on that day.</li>
                    <li>Each event gets an automatic <strong>Google Meet</strong> conference link, shown on the task page.</li>
                    <li>Deleting a task removes its calendar event.</li>
                    <li>Upcoming calendar events appear on your dashboard.</li>
                </ul>
            </x-card>
        @endif
    </div>
</div>
@endsection
