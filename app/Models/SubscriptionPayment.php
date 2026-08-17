<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
 protected $fillable = [
 'tenant_id', 'subscription_id', 'amount', 'currency',
 'status', 'gateway_payment_id', 'paid_at',
 ];

 protected $casts = [
 'amount' => 'decimal:2',
 'paid_at' => 'datetime',
 ];

 public function tenant()
 {
 return $this->belongsTo(Tenant::class);
 }

 public function subscription()
 {
 return $this->belongsTo(Subscription::class);
 }
}
