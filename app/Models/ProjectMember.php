<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    use TenantScoped;

    protected $fillable = ['tenant_id', 'project_id', 'user_id', 'role'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
