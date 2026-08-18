<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'client_id', 'name', 'email', 'phone',
 'designation', 'is_primary', 'is_billing_contact',
 ];

 protected $casts = [
 'is_primary' => 'boolean',
 'is_billing_contact' => 'boolean',
 ];

 public function client()
 {
 return $this->belongsTo(Client::class);
 }
}
