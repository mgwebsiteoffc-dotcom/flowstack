<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes, TenantScoped;

    protected $fillable = [
        'tenant_id', 'client_id', 'title', 'report_type', 'period_start',
        'period_end', 'status', 'data', 'insights', 'recommendations',
        'next_priorities', 'created_by', 'shared_at', 'shared_with_client',
        'client_viewed_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'data' => 'array',
        'shared_at' => 'datetime',
        'shared_with_client' => 'boolean',
        'client_viewed_at' => 'datetime',
    ];

    public const TYPES = ['weekly', 'monthly', 'quarterly', 'custom'];
    public const STATUSES = ['draft', 'final', 'shared'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
