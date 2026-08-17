<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ClientRequest extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'client_id', 'submitted_by', 'request_type', 'title',
 'description', 'priority', 'status', 'attachments', 'assigned_to',
 'task_id', 'response_message',
 ];

 protected $casts = [
 'attachments' => 'array',
 ];

 public const TYPES = ['general', 'content', 'design', 'technical', 'report'];
 public const STATUSES = ['open', 'in_progress', 'completed', 'cancelled'];

 public function client()
 {
 return $this->belongsTo(Client::class);
 }

 public function submitter()
 {
 return $this->belongsTo(ClientPortalUser::class, 'submitted_by');
 }

 public function assignee()
 {
 return $this->belongsTo(User::class, 'assigned_to');
 }

 public function task()
 {
 return $this->belongsTo(Task::class);
 }
}
