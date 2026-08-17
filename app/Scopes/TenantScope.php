<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that automatically constrains every query to the current
 * tenant. This is THE critical multi-tenancy safety net: no tenant model can
 * ever be queried without a tenant_id condition, so agency A can never see
 * agency B's data.
 *
 * The tenant id is resolved from (in order):
 *   1. app('currentTenant')    - set by TenantMiddleware for web requests
 *   2. app('currentTenantId')  - set by queued jobs / services via setCurrent()
 *   3. auth()->user()->tenant_id - belt & braces for authenticated contexts
 *
 * If no tenant context exists at all (e.g. public routes, artisan commands
 * that legitimately operate platform-wide) no constraint is added. All code
 * paths that touch tenant data are expected to establish one of the contexts
 * above first.
 */
class TenantScope implements Scope
{
    /**
     * Recursion guard for the auth() fallback below. When the auth guard is
     * resolving its user through this very scope, the inner lookup must run
     * unconstrained - otherwise auth()->check() re-enters the guard, which
     * queries the user again through this scope, forever (stack overflow ->
     * 500 on every authenticated page).
     */
    protected static bool $resolvingAuthUser = false;

    /**
     * Set the tenant id used by the scope (used by queued jobs and services).
     */
    public static function setCurrent(?int $tenantId): void
    {
        app()->instance('currentTenantId', $tenantId);
    }

    /**
     * Set the full tenant model (used by TenantMiddleware).
     */
    public static function setCurrentTenant($tenant): void
    {
        app()->instance('currentTenant', $tenant);
        app()->instance('currentTenantId', $tenant?->id);
    }

    /**
     * Clear the tenant context (tests, console commands, etc.).
     */
    public static function forget(): void
    {
        // Remove any tenant instance overrides. The container still resolves
        // both keys (to null) thanks to the closure bindings registered in
        // AppServiceProvider::boot(), so app('currentTenant') never throws.
        app()->forgetInstance('currentTenant');
        app()->forgetInstance('currentTenantId');
    }

    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = $this->resolveTenantId();

        if ($tenantId !== null) {
            $builder->where($model->getTable().'.tenant_id', $tenantId);
        }
    }

    protected function resolveTenantId(): ?int
    {
        if (app()->bound('currentTenant') && ($tenant = app('currentTenant'))) {
            return (int) $tenant->id;
        }

        if (app()->bound('currentTenantId') && app('currentTenantId') !== null) {
            return (int) app('currentTenantId');
        }

        // Belt & braces for authenticated contexts WITHOUT TenantMiddleware.
        // Guarded so the auth user lookup itself (which runs through this
        // scope) is never constrained while it is still being resolved.
        if (! self::$resolvingAuthUser) {
            self::$resolvingAuthUser = true;

            try {
                if (auth()->check()) {
                    return (int) auth()->user()->tenant_id;
                }
            } finally {
                self::$resolvingAuthUser = false;
            }
        }

        return null;
    }
}
