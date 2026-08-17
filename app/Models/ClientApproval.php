<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ClientApproval extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'client_id', 'task_id', 'title', 'description',
        'file_paths', 'status', 'submitted_by', 'reviewed_by',
        'review_notes', 'reviewed_at',
    ];

    protected $casts = [
        'file_paths' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'approved', 'changes_requested'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(ClientPortalUser::class, 'reviewed_by');
    }
}
