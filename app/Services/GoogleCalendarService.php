<?php

namespace App\Services;

use App\Models\IntegrationToken;
use App\Models\Tenant;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Google Calendar OAuth 2.0 client + Google Meet generation.
 *
 * - Connect via OAuth (offline access -> refresh token kept encrypted).
 * - listUpcomingEvents(): calendar widget on the dashboard.
 * - createEvent(..., withMeet=true): creates an event WITH a Google Meet
 *   conference link (conferenceData + hangoutsMeet solution) - this is how
 *   "GMeet generation" works: every synced task gets its own Meet URL.
 *
 * Requires GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET / GOOGLE_REDIRECT_URI
 * in .env. All failures are logged and never crash the caller.
 */
class GoogleCalendarService
{
    public const PROVIDER = 'google_calendar';
    public const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    public const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    public const USERINFO_URL = 'https://www.googleapis.com/oauth2/v2/userinfo';
    public const API_EVENTS = 'https://www.googleapis.com/calendar/v3/calendars/primary/events';

    public function __construct(protected ?Tenant $tenant = null)
    {
        $this->tenant = $tenant ?? (\App\Support\CurrentTenant::get() ?: null);
    }

    // ------------------------------------------------------------------
    // OAuth
    // ------------------------------------------------------------------

    public static function isConfigured(): bool
    {
        return (bool) (config('services.google_calendar.client_id') && config('services.google_calendar.client_secret'));
    }

    public function authUrl(): string
    {
        $params = [
            'client_id' => config('services.google_calendar.client_id'),
            'redirect_uri' => config('services.google_calendar.redirect_uri'),
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar.events',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => session()->getId(),
        ];

        return self::AUTH_URL.'?'.http_build_query($params);
    }

    /**
     * Exchange the OAuth code for tokens and store them encrypted.
     *
     * @return array{email: string, name: string}
     */
    public function handleCallback(string $code, string $state): array
    {
        if ($state !== session()->getId()) {
            throw new \RuntimeException('Invalid OAuth state.');
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => config('services.google_calendar.client_id'),
            'client_secret' => config('services.google_calendar.client_secret'),
            'redirect_uri' => config('services.google_calendar.redirect_uri'),
            'grant_type' => 'authorization_code',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Google token exchange failed: '.$response->body());
        }

        $tokens = $response->json();
        $userinfo = Http::withToken($tokens['access_token'])->get(self::USERINFO_URL)->json();

        if ($this->tenant) {
            IntegrationToken::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $this->tenant->id, 'provider' => self::PROVIDER],
                [
                    'token_json' => Crypt::encryptString(json_encode($tokens)),
                    'email' => $userinfo['email'] ?? null,
                    'expires_at' => now()->addSeconds((int) ($tokens['expires_in'] ?? 3600)),
                ]
            );
        }

        return ['email' => $userinfo['email'] ?? '', 'name' => $userinfo['name'] ?? ''];
    }

    public function isConnected(): bool
    {
        if (! $this->tenant) {
            return false;
        }

        return IntegrationToken::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant->id)
            ->where('provider', self::PROVIDER)
            ->exists();
    }

    public function connectedEmail(): ?string
    {
        if (! $this->tenant) {
            return null;
        }

        return IntegrationToken::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant->id)
            ->where('provider', self::PROVIDER)
            ->value('email');
    }

    public function disconnect(): void
    {
        if (! $this->tenant) {
            return;
        }

        IntegrationToken::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant->id)
            ->where('provider', self::PROVIDER)
            ->delete();
    }

    // ------------------------------------------------------------------
    // Token handling
    // ------------------------------------------------------------------

    protected function accessToken(): string
    {
        $tokenRow = IntegrationToken::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant?->id)
            ->where('provider', self::PROVIDER)
            ->first();

        if (! $tokenRow || ! $tokenRow->token_json) {
            throw new \RuntimeException('Google Calendar is not connected.');
        }

        $tokens = json_decode(Crypt::decryptString($tokenRow->token_json), true);

        // Refresh when expired (or close to it).
        if ($tokenRow->expires_at && $tokenRow->expires_at->isPast() && ! empty($tokens['refresh_token'])) {
            $tokens = $this->refreshAccessToken($tokens['refresh_token']);

            $tokenRow->token_json = Crypt::encryptString(json_encode($tokens));
            $tokenRow->expires_at = now()->addSeconds((int) ($tokens['expires_in'] ?? 3600));
            $tokenRow->save();
        }

        return $tokens['access_token'] ?? '';
    }

    protected function refreshAccessToken(string $refreshToken): array
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => config('services.google_calendar.client_id'),
            'client_secret' => config('services.google_calendar.client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Google token refresh failed: '.$response->body());
        }

        return $response->json() + ['refresh_token' => $refreshToken];
    }

    // ------------------------------------------------------------------
    // Calendar API
    // ------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listUpcomingEvents(int $maxResults = 10): array
    {
        $response = Http::withToken($this->accessToken())
            ->get(self::API_EVENTS, [
                'maxResults' => $maxResults,
                'orderBy' => 'startTime',
                'singleEvents' => 'true',
                'timeMin' => now()->toIso8601String(),
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Google events list failed: '.$response->body());
        }

        return collect($response->json('items') ?? [])->map(function ($event) {
            return [
                'id' => $event['id'] ?? null,
                'summary' => $event['summary'] ?? 'Untitled event',
                'start' => data_get($event, 'start.dateTime') ? now()->parse(data_get($event, 'start.dateTime')) : null,
                'end' => data_get($event, 'end.dateTime') ? now()->parse(data_get($event, 'end.dateTime')) : null,
                'hangout' => $event['hangoutLink'] ?? null,
                'html_link' => $event['htmlLink'] ?? null,
                'location' => $event['location'] ?? null,
            ];
        })->values()->toArray();
    }

    /**
     * Create a calendar event; withMeet=true adds a Google Meet conference.
     *
     * @return array{event_id: string, hangout_link: ?string, html_link: ?string}
     */
    public function createEvent(string $title, \Carbon\CarbonInterface $start, ?\Carbon\CarbonInterface $end = null, string $description = '', bool $withMeet = true): array
    {
        $payload = [
            'summary' => $title,
            'description' => $description,
            'start' => ['dateTime' => $start->toIso8601String(), 'timeZone' => config('app.timezone', 'Asia/Kolkata')],
            'end' => ['dateTime' => ($end ?? $start->copy()->addHour())->toIso8601String(), 'timeZone' => config('app.timezone', 'Asia/Kolkata')],
        ];

        if ($withMeet) {
            $payload['conferenceData'] = [
                'createRequest' => [
                    'requestId' => (string) Str::uuid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ];
        }

        $response = Http::withToken($this->accessToken())->post(self::API_EVENTS, $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Google event create failed: '.$response->body());
        }

        $data = $response->json();

        return [
            'event_id' => $data['id'] ?? '',
            'hangout_link' => $data['hangoutLink'] ?? null,
            'html_link' => $data['htmlLink'] ?? null,
        ];
    }

    public function updateEvent(string $eventId, string $title, \Carbon\CarbonInterface $start, ?\Carbon\CarbonInterface $end = null, string $description = ''): void
    {
        $payload = [
            'summary' => $title,
            'description' => $description,
            'start' => ['dateTime' => $start->toIso8601String(), 'timeZone' => config('app.timezone', 'Asia/Kolkata')],
            'end' => ['dateTime' => ($end ?? $start->copy()->addHour())->toIso8601String(), 'timeZone' => config('app.timezone', 'Asia/Kolkata')],
        ];

        $response = Http::withToken($this->accessToken())
            ->patch(self::API_EVENTS.'/'.urlencode($eventId), $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Google event update failed: '.$response->body());
        }
    }

    public function deleteEvent(string $eventId): void
    {
        $response = Http::withToken($this->accessToken())
            ->delete(self::API_EVENTS.'/'.urlencode($eventId));

        if ($response->failed() && $response->status() !== 404) {
            throw new \RuntimeException('Google event delete failed: '.$response->body());
        }
    }

    public static function logFailure(\Throwable $e, string $context): void
    {
        Log::warning('Google Calendar: '.$context, ['error' => $e->getMessage()]);
    }
}
