<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class TaskTag extends Model
{
 use TenantScoped;

 protected $fillable = ['tenant_id', 'name', 'color'];

 public function tasks()
 {
 return $this->belongsToMany(Task::class, 'task_tag_pivot', 'tag_id', 'task_id');
 }
}
