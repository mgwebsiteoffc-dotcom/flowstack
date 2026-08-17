<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'lead_id', 'activity_type', 'title', 'description',
        'old_value', 'new_value', 'performed_by', 'source', 'lead365_event',
        'scheduled_at', 'completed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const TYPES = ['call', 'email', 'meeting', 'note', 'stage_change', 'assignment', 'webhook_event'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
