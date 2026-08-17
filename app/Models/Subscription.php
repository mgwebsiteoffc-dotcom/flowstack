<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
 protected $fillable = [
 'tenant_id', 'plan_id', 'status', 'billing_cycle', 'amount',
 'started_at', 'expires_at', 'cancelled_at',
 'payment_gateway', 'gateway_subscription_id',
 ];

 protected $casts = [
 'amount' => 'decimal:2',
 'started_at' => 'datetime',
 'expires_at' => 'datetime',
 'cancelled_at' => 'datetime',
 ];

 public function tenant()
 {
 return $this->belongsTo(Tenant::class);
 }

 public function plan()
 {
 return $this->belongsTo(Plan::class);
 }

 public function payments()
 {
 return $this->hasMany(SubscriptionPayment::class);
 }
}
