<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ClientTeamMember extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'client_id', 'user_id', 'role_in_project', 'service_type',
 ];

 public function client()
 {
 return $this->belongsTo(Client::class);
 }

 public function user()
 {
 return $this->belongsTo(User::class);
 }
}
