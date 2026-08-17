<?php

namespace App\Jobs\Lead365;

use App\Models\WebhookLog;
use App\Scopes\TenantScope;
use App\Services\Lead365WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Single dispatcher job for all Lead365 events. Retried up to 3 times with
 * exponential delay; the raw payload stays in webhook_logs either way.
 */
class ProcessLead365Webhook implements ShouldQueue
{
 use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

 public int $tries = 3;
 public array $backoff = [60, 300, 900];

 public function __construct(
 public array $payload,
 public int $tenantId,
 public int $webhookLogId
 ) {
 }

 public function handle(Lead365WebhookService $service): void
 {
 TenantScope::setCurrent($this->tenantId);

 $service->processWebhook($this->payload, $this->tenantId, $this->webhookLogId);
 }

 public function failed(\Throwable $e): void
 {
 WebhookLog::withoutGlobalScopes()->where('id', $this->webhookLogId)->update([
 'status' => 'failed',
 'error_message' => substr($e->getMessage(), 0, 2000),
 'processed_at' => now(),
 ]);
 }
}
