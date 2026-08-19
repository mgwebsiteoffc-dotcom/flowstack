<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Sends browser push notifications to a user's subscribed devices.
 *
 * Uses minishlink/web-push (add to composer.json). The service degrades
 * gracefully: if the library is not installed or VAPID keys are missing, it
 * logs and returns without throwing, so in-app/email notifications are never
 * affected.
 */
class PushNotificationService
{
    public function isConfigured(): bool
    {
        return class_exists(\Minishlink\WebPush\WebPush::class)
            && ! empty(config('services.push.vapid.public_key'))
            && ! empty(config('services.push.vapid.private_key'));
    }

    /**
     * Send a push notification to every device subscribed for the user.
     */
    public function sendToUser(User $user, string $title, string $body, string $url = '/dashboard'): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $subscriptions = PushSubscription::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $auth = [
                'VAPID' => [
                    'subject' => config('services.push.vapid.subject'),
                    'publicKey' => config('services.push.vapid.public_key'),
                    'privateKey' => config('services.push.vapid.private_key'),
                ],
            ];

            $webPush = new \Minishlink\WebPush\WebPush($auth);

            foreach ($subscriptions as $subscription) {
                if (! $subscription->public_key || ! $subscription->auth_token) {
                    continue;
                }

                $webPush->queueNotification(
                    \Minishlink\WebPush\Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->public_key,
                        'authToken' => $subscription->auth_token,
                    ]),
                    json_encode(['title' => $title, 'body' => $body, 'url' => $url])
                );
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::withoutGlobalScopes()
                        ->where('endpoint', $report->getEndpoint())
                        ->delete();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Push notification failed', ['user' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
