<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskChecklistItem extends Model
{
 protected $fillable = [
 'checklist_id', 'title', 'is_completed', 'completed_by', 'completed_at', 'order_index',
 ];

 protected $casts = [
 'is_completed' => 'boolean',
 'completed_at' => 'datetime',
 ];

 public function checklist()
 {
 return $this->belongsTo(TaskChecklist::class, 'checklist_id');
 }

 public function completedBy()
 {
 return $this->belongsTo(User::class, 'completed_by');
 }
}
