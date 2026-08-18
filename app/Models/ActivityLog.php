<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
 use TenantScoped;

 public $timestamps = false;

 protected $fillable = [
 'tenant_id', 'user_id', 'action', 'model_type', 'model_id',
 'old_values', 'new_values', 'ip_address', 'created_at',
 ];

 protected $casts = [
 'old_values' => 'array',
 'new_values' => 'array',
 'created_at' => 'datetime',
 ];

 public function user()
 {
 return $this->belongsTo(User::class);
 }

 /**
 * Persist an activity entry for the current request context.
 *
 * @param array<string, mixed>|null $oldValues
 * @param array<string, mixed>|null $newValues
 */
 public static function record(
 string $action,
 ?Model $subject = null,
 ?array $oldValues = null,
 ?array $newValues = null,
 ?int $userId = null
 ): void {
 $tenant = app('currentTenant');
 $user = $userId ? User::find($userId) : auth()->user();

 static::create([
 'tenant_id' => $tenant?->id ?? $user?->tenant_id,
 'user_id' => $user?->id,
 'action' => $action,
 'model_type' => $subject ? get_class($subject) : null,
 'model_id' => $subject?->getKey(),
 'old_values' => $oldValues,
 'new_values' => $newValues,
 'ip_address' => request()->ip(),
 'created_at' => now(),
 ]);
 }
}
