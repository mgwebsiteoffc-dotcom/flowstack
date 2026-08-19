<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Expose the VAPID public key + support flags to the frontend.
     */
    public function config()
    {
        return response()->json([
            'publicKey' => config('services.push.vapid.public_key'),
            'supported' => $this->supported(),
        ]);
    }

    /**
     * Store (or refresh) this device's browser push subscription.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
        ]);

        PushSubscription::withoutGlobalScopes()->updateOrCreate(
            ['user_id' => auth()->id(), 'endpoint' => $validated['endpoint']],
            [
                'tenant_id' => \App\Support\CurrentTenant::id(),
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Remove this device's subscription (user disabled notifications).
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
        ]);

        PushSubscription::withoutGlobalScopes()
            ->where('user_id', auth()->id())
            ->where('endpoint', $validated['endpoint'])
            ->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Whether push is even possible: config present + user's browser supported
     * (checked on the client too — this is the server-side signal).
     */
    protected function supported(): bool
    {
        return ! empty(config('services.push.vapid.public_key'))
            && ! empty(config('services.push.vapid.private_key'));
    }
}
