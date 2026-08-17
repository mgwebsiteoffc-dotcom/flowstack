<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class BikriBookSyncLog extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'invoice_id', 'action', 'request_payload',
        'response_payload', 'status', 'error_message',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
