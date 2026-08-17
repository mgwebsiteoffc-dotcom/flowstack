<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes, TenantScoped;

    protected $fillable = [
        'tenant_id', 'lead365_lead_id', 'source_type', 'company_name',
        'contact_name', 'email', 'phone', 'lead_source', 'campaign_name',
        'ad_name', 'form_name', 'services_interested', 'estimated_value',
        'current_stage', 'stage_id', 'status', 'assigned_to', 'won_value',
        'won_at', 'lost_reason', 'lost_at', 'converted_to_client_id',
        'expected_close_date', 'probability', 'notes', 'custom_fields',
        'last_activity_at', 'lead365_created_at',
    ];

    protected $casts = [
        'services_interested' => 'array',
        'estimated_value' => 'decimal:2',
        'won_value' => 'decimal:2',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
        'expected_close_date' => 'date',
        'custom_fields' => 'array',
        'last_activity_at' => 'datetime',
        'lead365_created_at' => 'datetime',
    ];

    public const STATUSES = ['active', 'won', 'lost', 'archived'];
    public const SOURCE_TYPES = ['lead365', 'manual', 'meta_ads', 'form_submission'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedClient()
    {
        return $this->belongsTo(Client::class, 'converted_to_client_id');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function stage()
    {
        return $this->belongsTo(LeadPipelineStage::class, 'stage_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function daysInStage(): int
    {
        return max(0, (int) ($this->last_activity_at ?? $this->created_at)?->diffInDays(now(), false) ?? 0);
    }

    public function sourceLabel(): string
    {
        return match ($this->source_type) {
            'meta_ads' => 'Meta Ads',
            'form_submission' => 'Form',
            'lead365' => 'Lead365',
            default => 'Manual',
        };
    }
}
