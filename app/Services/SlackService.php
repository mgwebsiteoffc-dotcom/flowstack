<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Slack channel notifications via incoming webhook (simplest, no OAuth app
 * required). Post a plain-text message to the configured channel.
 */
class SlackService
{
    public function __construct(protected ?string $webhookUrl = null)
    {
    }

    public function send(string $text): bool
    {
        $url = $this->webhookUrl ?: config('services.slack.webhook_url');

        if (! $url) {
            return false;
        }

        $response = Http::timeout(10)->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, ['text' => $text]);

        return $response->successful();
    }

    public function test(string $text = 'Task365 connected to Slack successfully.'): bool
    {
        return $this->send($text);
    }
}
