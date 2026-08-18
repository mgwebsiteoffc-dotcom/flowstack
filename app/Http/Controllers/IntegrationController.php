<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ChannelNotificationService;
use App\Services\GoogleCalendarService;
use App\Services\SlackService;
use App\Services\TeamsService;
use Illuminate\Http\Request;

/**
 * Team channel integrations (Slack / Microsoft Teams) + Google Calendar sync
 * with automatic Google Meet generation.
 */
class IntegrationController extends Controller
{
    // ------------------------------------------------------------------
    // Slack + Teams channels
    // ------------------------------------------------------------------

    public function channels()
    {
        $slackUrl = Setting::get('slack_webhook_url');
        $teamsUrl = Setting::get('teams_webhook_url');

        $enabledEvents = json_decode((string) Setting::get('channel_notify_events', 'null'), true);

        return view('settings.integrations.channels', [
            'slackUrl' => $slackUrl,
            'teamsUrl' => $teamsUrl,
            'enabledEvents' => $enabledEvents,
            'events' => ChannelNotificationService::EVENTS,
        ]);
    }

    public function saveChannels(Request $request)
    {
        $validated = $request->validate([
            'slack_webhook_url' => ['nullable', 'url', 'max:500'],
            'teams_webhook_url' => ['nullable', 'url', 'max:500'],
            'events' => ['nullable', 'array'],
            'events.*' => ['string'],
        ]);

        Setting::set('slack_webhook_url', $validated['slack_webhook_url'] ?? '');
        Setting::set('teams_webhook_url', $validated['teams_webhook_url'] ?? '');

        $allowed = array_values(array_intersect(
            array_keys(ChannelNotificationService::EVENTS),
            $validated['events'] ?? []
        ));
        Setting::set('channel_notify_events', json_encode($allowed));

        return back()->with('success', 'Channel settings saved. Events will notify '.(count($allowed) === 0 ? 'nothing (all toggles off)' : implode(', ', array_map(fn ($e) => ChannelNotificationService::EVENTS[$e], $allowed)).'.'));
    }

    public function testSlack()
    {
        $url = Setting::get('slack_webhook_url');

        if (! $url) {
            return back()->with('error', 'Save a Slack webhook URL first.');
        }

        $ok = (new SlackService($url))->test();

        return back()->with($ok ? 'success' : 'error', $ok
            ? 'Slack test message sent to your channel.'
            : 'Slack webhook failed - check the URL (and that the app is not blocked).');
    }

    public function testTeams()
    {
        $url = Setting::get('teams_webhook_url');

        if (! $url) {
            return back()->with('error', 'Save a Teams webhook URL first.');
        }

        $ok = (new TeamsService($url))->test();

        return back()->with($ok ? 'success' : 'error', $ok
            ? 'Teams test card sent to your channel.'
            : 'Teams webhook failed - check the Workflows URL.');
    }

    // ------------------------------------------------------------------
    // Google Calendar + Meet
    // ------------------------------------------------------------------

    public function googleCalendar()
    {
        $service = new GoogleCalendarService;
        $connected = $service->isConnected();
        $email = $service->connectedEmail();
        $syncEnabled = (int) Setting::get('google_calendar_sync_tasks', 0) === 1;

        return view('settings.integrations.google-calendar', compact('connected', 'email', 'syncEnabled'));
    }

    public function googleConnect()
    {
        $service = new GoogleCalendarService;

        if (! $service->isConfigured()) {
            return back()->with('error', 'Google Calendar is not configured. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET and GOOGLE_REDIRECT_URI in .env.');
        }

        return redirect()->away($service->authUrl());
    }

    public function googleCallback(Request $request)
    {
        $service = new GoogleCalendarService;

        if ($request->filled('error')) {
            return redirect()->route('settings.integrations.google-calendar')->with('error', 'Google authorization was cancelled.');
        }

        try {
            $user = $service->handleCallback($request->input('code'), (string) $request->input('state', ''));

            return redirect()->route('settings.integrations.google-calendar')
                ->with('success', 'Google Calendar connected as '.($user['email'] ?? 'your account').'. Tasks with due dates now sync with Google Meet links.');
        } catch (\Throwable $e) {
            GoogleCalendarService::logFailure($e, 'callback');

            return redirect()->route('settings.integrations.google-calendar')->with('error', 'Could not connect Google Calendar: '.$e->getMessage());
        }
    }

    public function googleDisconnect()
    {
        (new GoogleCalendarService)->disconnect();

        return back()->with('success', 'Google Calendar disconnected.');
    }

    public function saveCalendarSettings(Request $request)
    {
        $validated = $request->validate([
            'sync_tasks' => ['sometimes', 'boolean'],
        ]);

        Setting::set('google_calendar_sync_tasks', (int) $request->boolean('sync_tasks'));

        return back()->with('success', 'Calendar sync preferences saved.');
    }
}
