<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'task_id', 'project_id', 'client_id', 'user_id',
 'description', 'started_at', 'ended_at', 'duration_minutes',
 'is_billable', 'is_running',
 ];

 protected $casts = [
 'started_at' => 'datetime',
 'ended_at' => 'datetime',
 'duration_minutes' => 'integer',
 'is_billable' => 'boolean',
 'is_running' => 'boolean',
 ];

 public function task()
 {
 return $this->belongsTo(Task::class);
 }

 public function project()
 {
 return $this->belongsTo(Project::class);
 }

 public function client()
 {
 return $this->belongsTo(Client::class);
 }

 public function user()
 {
 return $this->belongsTo(User::class);
 }

 public function scopeRunning($query)
 {
 return $query->where('is_running', true);
 }

 public function durationHours(): float
 {
 return $this->duration_minutes ? round($this->duration_minutes / 60, 2) : 0;
 }
}
