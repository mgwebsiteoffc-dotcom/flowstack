<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'source', 'event_type', 'payload', 'status',
        'error_message', 'processed_at', 'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
