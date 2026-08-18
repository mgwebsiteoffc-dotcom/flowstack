<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
 use TenantScoped;

 protected $fillable = ['tenant_id', 'task_id', 'user_id', 'comment'];

 public function task()
 {
 return $this->belongsTo(Task::class);
 }

 public function user()
 {
 return $this->belongsTo(User::class);
 }
}
