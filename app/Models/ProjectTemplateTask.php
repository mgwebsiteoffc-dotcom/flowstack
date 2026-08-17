<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTemplateTask extends Model
{
 protected $fillable = [
 'template_id', 'title', 'description', 'task_type', 'recurrence_type',
 'default_priority', 'estimated_hours', 'order_index',
 ];

 protected $casts = [
 'estimated_hours' => 'decimal:2',
 ];

 public function template()
 {
 return $this->belongsTo(ProjectTemplate::class);
 }
}
