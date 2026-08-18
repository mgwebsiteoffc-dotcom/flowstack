<?php

namespace App\Jobs\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

/**
 * Sends a Slack or Teams webhook payload. Runs on the queue so channel
 * notifications never block the request. Failures are logged, never thrown.
 */
class DispatchChannelNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public string $provider, // slack|teams
        public string $webhookUrl,
        public string $title,
        public string $message,
        public string $color = '#4F46E5'
    ) {
    }

    public function handle(): void
    {
        try {
            $response = $this->provider === 'slack'
                ? Http::timeout(10)->post($this->webhookUrl, ['text' => $this->title."\n".$this->message])
                : Http::timeout(10)->post($this->webhookUrl, $this->teamsPayload());

            if ($response->failed()) {
                logger()->warning('Channel notification failed', [
                    'provider' => $this->provider,
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 300),
                ]);
            }
        } catch (\Throwable $e) {
            logger()->warning('Channel notification threw', [
                'provider' => $this->provider,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function teamsPayload(): array
    {
        return [
            'type' => 'message',
            'attachments' => [[
                'contentType' => 'application/vnd.microsoft.card.adaptive',
                'content' => [
                    '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                    'type' => 'AdaptiveCard',
                    'version' => '1.4',
                    'body' => [
                        ['type' => 'TextBlock', 'size' => 'Medium', 'weight' => 'Bolder', 'text' => $this->title, 'wrap' => true],
                        ['type' => 'TextBlock', 'text' => $this->message, 'wrap' => true],
                        ['type' => 'TextBlock', 'text' => 'Agency OS · '.now()->format('d M Y H:i'), 'isSubtle' => true, 'size' => 'Small'],
                    ],
                ],
            ]],
        ];
    }
}
