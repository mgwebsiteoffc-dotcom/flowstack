<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
 use SoftDeletes, TenantScoped;

 protected $fillable = [
 'tenant_id', 'client_id', 'name', 'description', 'status', 'service_type',
 'start_date', 'end_date', 'template_id', 'created_by',
 ];

 protected $casts = [
 'start_date' => 'date',
 'end_date' => 'date',
 ];

 public const STATUSES = ['active', 'on_hold', 'completed', 'cancelled'];

 public function tenant()
 {
 return $this->belongsTo(Tenant::class);
 }

 public function client()
 {
 return $this->belongsTo(Client::class);
 }

 public function template()
 {
 return $this->belongsTo(ProjectTemplate::class, 'template_id');
 }

 public function creator()
 {
 return $this->belongsTo(User::class, 'created_by');
 }

 public function members()
 {
 return $this->hasMany(ProjectMember::class);
 }

 public function memberUsers()
 {
 return $this->belongsToMany(User::class, 'project_members')->withPivot('role');
 }

 public function tasks()
 {
 return $this->hasMany(Task::class);
 }

 public function timeEntries()
 {
 return $this->hasMany(TimeEntry::class);
 }

 public function openTasksCount(): int
 {
 return $this->tasks()->whereNotIn('status', ['done', 'cancelled'])->count();
 }

 public function progress(): int
 {
 $total = $this->tasks()->count();

 if ($total === 0) {
 return $this->status === 'completed' ? 100 : 0;
 }

 return (int) round($this->tasks()->where('status', 'done')->count() * 100 / $total);
 }
}
