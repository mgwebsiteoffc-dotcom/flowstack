<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class TaskChecklist extends Model
{
 use TenantScoped;

 protected $fillable = ['tenant_id', 'task_id', 'title', 'order_index'];

 public function task()
 {
 return $this->belongsTo(Task::class);
 }

 public function items()
 {
 return $this->hasMany(TaskChecklistItem::class)->orderBy('order_index');
 }
}
