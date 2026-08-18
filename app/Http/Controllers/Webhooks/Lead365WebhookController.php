<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\Lead365\ProcessLead365Webhook;
use App\Models\Tenant;
use App\Models\WebhookLog;
use Illuminate\Http\Request;

/**
 * Lead365.in webhook endpoint.
 *
 * CRITICAL RULES:
 * 1. Always answer HTTP 200 within 2 seconds.
 * 2. Raw payload is persisted to webhook_logs BEFORE anything else.
 * 3. Real processing always happens in the queued job - never inline.
 */
class Lead365WebhookController extends Controller
{
 public function handle(Request $request, string $tenantSlug)
 {
 $tenant = Tenant::where('slug', $tenantSlug)->first();

 if (! $tenant) {
 return response()->json(['status' => 'error', 'message' => 'Tenant not found'], 404);
 }

 $payload = $request->json()->all() ?: $request->all();
 $event = $payload['event'] ?? 'unknown';

 // 1. Persist the raw payload FIRST - we never lose webhook data.
 $log = WebhookLog::withoutGlobalScopes()->create([
 'tenant_id' => $tenant->id,
 'source' => 'lead365',
 'event_type' => $event,
 'payload' => $payload,
 'status' => 'received',
 'ip_address' => $request->ip(),
 ]);

 // 2. Queue the processing job.
 ProcessLead365Webhook::dispatch($payload, $tenant->id, $log->id);

 // 3. Answer immediately.
 return response()->json(['status' => 'received'], 200);
 }
}
