<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
 use TenantScoped;

 protected $fillable = [
 'tenant_id', 'title', 'content', 'is_pinned', 'created_by', 'expires_at',
 ];

 protected $casts = [
 'is_pinned' => 'boolean',
 'expires_at' => 'datetime',
 ];

 public function creator()
 {
 return $this->belongsTo(User::class, 'created_by');
 }

 public function reads()
 {
 return $this->hasMany(AnnouncementRead::class);
 }

 public function isReadBy(User $user): bool
 {
 return $this->reads()->where('user_id', $user->id)->exists();
 }

 public function scopeVisible($query)
 {
 return $query->where(function ($q) {
 $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
 });
 }
}
