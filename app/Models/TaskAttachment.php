<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'task_id', 'file_name', 'file_path', 'file_size', 'mime_type', 'uploaded_by',
 ];

 protected $casts = [
 'file_size' => 'integer',
 ];

 public function task()
 {
 return $this->belongsTo(Task::class);
 }

 public function uploader()
 {
 return $this->belongsTo(User::class, 'uploaded_by');
 }
}
