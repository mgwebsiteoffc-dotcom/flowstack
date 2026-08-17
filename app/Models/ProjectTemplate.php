<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ProjectTemplate extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'service_type',
        'is_system_template', 'created_by',
    ];

    protected $casts = [
        'is_system_template' => 'boolean',
    ];

    public function templateTasks()
    {
        return $this->hasMany(ProjectTemplateTask::class)->orderBy('order_index');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
