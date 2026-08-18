<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Microsoft Teams channel notifications via incoming webhook
 * (Workflows / Connector URL). Sends an Adaptive Card.
 */
class TeamsService
{
    public function __construct(protected ?string $webhookUrl = null)
    {
    }

    public function send(string $title, string $message, string $color = '#4F46E5'): bool
    {
        $url = $this->webhookUrl ?: config('services.teams.webhook_url');

        if (! $url) {
            return false;
        }

        $payload = [
            'type' => 'message',
            'attachments' => [[
                'contentType' => 'application/vnd.microsoft.card.adaptive',
                'content' => [
                    '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                    'type' => 'AdaptiveCard',
                    'version' => '1.4',
                    'body' => [
                        ['type' => 'TextBlock', 'size' => 'Medium', 'weight' => 'Bolder', 'text' => $title, 'wrap' => true, 'color' => 'Attention'],
                        ['type' => 'TextBlock', 'text' => $message, 'wrap' => true],
                        ['type' => 'TextBlock', 'text' => 'Sent by Agency OS · '.now()->format('d M Y H:i'), 'isSubtle' => true, 'size' => 'Small'],
                    ],
                ],
            ]],
        ];

        $response = Http::timeout(10)->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        return $response->successful();
    }

    public function test(string $title = 'Agency OS connected to Microsoft Teams', string $message = 'Your channel is now receiving Agency OS notifications.'): bool
    {
        return $this->send($title, $message);
    }
}
