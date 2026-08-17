<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class AutomationLog extends Model
{
 use TenantScoped;

 public $timestamps = false;

 protected $fillable = [
 'tenant_id', 'rule_id', 'trigger_data', 'status',
 'actions_taken', 'error_message', 'created_at',
 ];

 protected $casts = [
 'trigger_data' => 'array',
 'actions_taken' => 'array',
 'created_at' => 'datetime',
 ];

 public function rule()
 {
 return $this->belongsTo(AutomationRule::class);
 }
}
