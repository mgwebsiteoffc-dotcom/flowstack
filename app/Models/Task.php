<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
 use SoftDeletes, TenantScoped;

 protected $fillable = [
 'tenant_id', 'client_id', 'project_id', 'parent_task_id', 'title',
 'description', 'status', 'priority', 'service_type', 'task_type',
 'assigned_to', 'created_by', 'due_date', 'estimated_hours', 'actual_hours',
 'approval_status', 'approved_by', 'approved_at', 'is_recurring',
 'recurrence_type', 'recurrence_interval', 'recurrence_days',
 'next_recurrence_date', 'recurrence_ends_at', 'parent_recurring_task_id',
 'order_index',
 ];

 protected $casts = [
 'estimated_hours' => 'decimal:2',
 'actual_hours' => 'decimal:2',
 'due_date' => 'date',
 'approved_at' => 'datetime',
 'is_recurring' => 'boolean',
 'recurrence_days' => 'array',
 'next_recurrence_date' => 'date',
 'recurrence_ends_at' => 'date',
 ];

 public const STATUSES = ['backlog', 'todo', 'in_progress', 'in_review', 'waiting_approval', 'done', 'blocked'];
 public const PRIORITIES = ['urgent', 'high', 'medium', 'low'];
 public const TASK_TYPES = ['recurring', 'one_time', 'client_request', 'internal'];
 public const RECURRENCE_TYPES = ['daily', 'weekly', 'biweekly', 'monthly', 'custom'];

 public function tenant()
 {
 return $this->belongsTo(Tenant::class);
 }

 public function client()
 {
 return $this->belongsTo(Client::class);
 }

 public function project()
 {
 return $this->belongsTo(Project::class);
 }

 public function parentTask()
 {
 return $this->belongsTo(Task::class, 'parent_task_id');
 }

 public function subtasks()
 {
 return $this->hasMany(Task::class, 'parent_task_id')->orderBy('order_index');
 }

 public function parentRecurringTask()
 {
 return $this->belongsTo(Task::class, 'parent_recurring_task_id');
 }

 public function assignee()
 {
 return $this->belongsTo(User::class, 'assigned_to');
 }

 public function creator()
 {
 return $this->belongsTo(User::class, 'created_by');
 }

 public function approver()
 {
 return $this->belongsTo(User::class, 'approved_by');
 }

 public function comments()
 {
 return $this->hasMany(TaskComment::class)->latest();
 }

 public function attachments()
 {
 return $this->hasMany(TaskAttachment::class);
 }

 public function checklists()
 {
 return $this->hasMany(TaskChecklist::class)->orderBy('order_index');
 }

 public function watchers()
 {
 return $this->belongsToMany(User::class, 'task_watchers');
 }

 public function tags()
 {
 return $this->belongsToMany(TaskTag::class, 'task_tag_pivot');
 }

 public function timeEntries()
 {
 return $this->hasMany(TimeEntry::class);
 }

 public function approvals()
 {
 return $this->hasMany(ClientApproval::class);
 }

 public function scopeOverdue($query)
 {
 return $query->where('due_date', '<', now()->toDateString())
 ->whereNotIn('status', ['done', 'cancelled']);
 }

 public function scopeDueToday($query)
 {
 return $query->whereDate('due_date', now()->toDateString())
 ->whereNotIn('status', ['done', 'cancelled']);
 }

 public function scopeAssignedTo($query, ?int $userId)
 {
 if ($userId) {
 return $query->where('assigned_to', $userId);
 }

 return $query;
 }

 public function isDone(): bool
 {
 return in_array($this->status, ['done'], true);
 }

 public function isOverdue(): bool
 {
 return $this->due_date !== null
 && $this->due_date->isBefore(today())
 && ! $this->isDone();
 }

 public function loggedMinutes(): int
 {
 return (int) $this->timeEntries()->sum('duration_minutes');
 }

 public function checklistProgress(): array
 {
 $total = 0;
 $done = 0;

 foreach ($this->checklists as $checklist) {
 foreach ($checklist->items as $item) {
 $total++;
 if ($item->is_completed) {
 $done++;
 }
 }
 }

 return ['total' => $total, 'done' => $done, 'percent' => $total > 0 ? (int) round($done * 100 / $total) : 0];
 }
}
