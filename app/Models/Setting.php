<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use TenantScoped;

    protected $fillable = ['tenant_id', 'key', 'value'];

    /**
     * Get a setting value for the current tenant.
     */
    public static function get(string $key, $default = null)
    {
        $tenantId = app('currentTenant')?->id ?? auth()->user()?->tenant_id;

        if (! $tenantId) {
            return $default;
        }

        return static::where('tenant_id', $tenantId)->where('key', $key)->value('value') ?? $default;
    }

    /**
     * Set a setting value for the current tenant (creates when missing).
     */
    public static function set(string $key, $value): void
    {
        $tenantId = app('currentTenant')?->id ?? auth()->user()?->tenant_id;

        if (! $tenantId) {
            return;
        }

        static::updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => $key],
            ['value' => is_scalar($value) ? (string) $value : json_encode($value)]
        );
    }

    /**
     * Set settings for an explicit tenant id (registration / jobs).
     */
    public static function setForTenant(int $tenantId, string $key, $value): void
    {
        static::withoutGlobalScopes()->updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => $key],
            ['value' => is_scalar($value) ? (string) $value : json_encode($value)]
        );
    }
}
