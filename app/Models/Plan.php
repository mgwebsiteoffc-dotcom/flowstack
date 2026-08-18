<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
 protected $fillable = [
 'name', 'slug', 'price_monthly', 'price_yearly',
 'max_users', 'max_clients', 'max_storage_gb', 'features', 'is_active',
 ];

 protected $casts = [
 'price_monthly' => 'decimal:2',
 'price_yearly' => 'decimal:2',
 'max_users' => 'integer',
 'max_clients' => 'integer',
 'max_storage_gb' => 'integer',
 'features' => 'array',
 'is_active' => 'boolean',
 ];

 public function tenants()
 {
 return $this->hasMany(Tenant::class);
 }
}
