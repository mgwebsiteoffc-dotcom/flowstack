<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class AutomationDelay extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'rule_id', 'event', 'model_class', 'model_id', 'context', 'run_at',
 ];

 protected $casts = [
 'context' => 'array',
 'run_at' => 'datetime',
 ];

 public function rule()
 {
 return $this->belongsTo(AutomationRule::class, 'rule_id');
 }
}
