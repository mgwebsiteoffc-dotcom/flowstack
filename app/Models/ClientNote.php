<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ClientNote extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'client_id', 'note', 'note_type', 'is_pinned', 'created_by',
 ];

 protected $casts = [
 'is_pinned' => 'boolean',
 ];

 public function client()
 {
 return $this->belongsTo(Client::class);
 }

 public function creator()
 {
 return $this->belongsTo(User::class, 'created_by');
 }
}
