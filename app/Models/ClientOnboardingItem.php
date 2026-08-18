<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ClientOnboardingItem extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'client_id', 'title', 'description', 'is_completed',
 'assigned_to', 'due_date', 'completed_at', 'order_index',
 ];

 protected $casts = [
 'is_completed' => 'boolean',
 'due_date' => 'date',
 'completed_at' => 'datetime',
 ];

 public function client()
 {
 return $this->belongsTo(Client::class);
 }

 public function assignee()
 {
 return $this->belongsTo(User::class, 'assigned_to');
 }
}
