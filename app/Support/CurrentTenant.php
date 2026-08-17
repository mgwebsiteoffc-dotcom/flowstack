<?php

namespace App\Support;

use App\Models\Tenant;

/**
 * Resolve the current tenant reliably, with layered fallbacks:
 *   1. app('currentTenant') instance (set by TenantMiddleware)
 *   2. authenticated user's tenant
 *   3. null
 *
 * Controllers/views should use currentTenant() instead of raw app('currentTenant')
 * so the null-tenant crash class is eliminated everywhere.
 */
class CurrentTenant
{
    public static function get(): ?Tenant
    {
        if (app()->bound('currentTenant')) {
            $tenant = app('currentTenant');
            if ($tenant instanceof Tenant) {
                return $tenant;
            }
        }

        if (auth()->check()) {
            $user = auth()->user();
            if ($user && $user->tenant) {
                return $user->tenant;
            }
        }

        return null;
    }

    /**
     * @return int 0 when no tenant
     */
    public static function id(): int
    {
        return (int) (self::get()?->id ?? 0);
    }
}
