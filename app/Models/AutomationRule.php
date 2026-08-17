<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'name', 'is_active', 'trigger_event', 'trigger_delay_hours',
        'conditions', 'actions', 'last_run_at', 'run_count', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trigger_delay_hours' => 'integer',
        'conditions' => 'array',
        'actions' => 'array',
        'last_run_at' => 'datetime',
        'run_count' => 'integer',
    ];

    public const TRIGGERS = [
        'task.overdue', 'task.assigned', 'task.status_changed', 'task.created',
        'lead.created', 'lead.won', 'lead.lost', 'lead.stage_changed',
        'lead.meta_received', 'invoice.created', 'invoice.overdue', 'invoice.paid',
        'client.created', 'contract.expiring',
    ];

    public const CONDITION_FIELDS = [
        'client_id' => 'Client',
        'service_type' => 'Service type',
        'priority' => 'Priority',
        'assigned_to' => 'Assignee',
        'lead_source' => 'Lead source',
        'estimated_value' => 'Estimated value',
        'stage' => 'Pipeline stage',
        'status' => 'Status',
    ];

    public const ACTION_TYPES = [
        'send_notification', 'send_email', 'create_task', 'change_task_status',
        'assign_task', 'create_project_from_template', 'post_comment', 'log_activity',
    ];

    public function logs()
    {
        return $this->hasMany(AutomationLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
