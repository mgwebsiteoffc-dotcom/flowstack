<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class LeadPipelineStage extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'name', 'lead365_stage_id', 'color',
 'order_index', 'is_won_stage', 'is_lost_stage',
 ];

 protected $casts = [
 'is_won_stage' => 'boolean',
 'is_lost_stage' => 'boolean',
 ];

 public function leads()
 {
 return $this->hasMany(Lead::class, 'stage_id');
 }
}
