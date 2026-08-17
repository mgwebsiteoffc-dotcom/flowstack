<?php

namespace App\Jobs\Lead365;

use App\Scopes\TenantScope;
use App\Services\Lead365WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLeadLost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public array $payload,
        public int $tenantId,
        public int $webhookLogId
    ) {
    }

    public function handle(Lead365WebhookService $service): void
    {
        TenantScope::setCurrent($this->tenantId);

        $service->handleLeadLost($this->payload, \App\Models\Tenant::find($this->tenantId));
        $service->markWebhookProcessed($this->webhookLogId);
    }
}
