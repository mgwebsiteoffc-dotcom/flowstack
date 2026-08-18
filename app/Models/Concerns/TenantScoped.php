<?php

namespace App\Models\Concerns;

use App\Scopes\TenantScope;

/**
 * Boots the TenantScope global scope for a model.
 */
trait TenantScoped
{
 public static function bootTenantScoped(): void
 {
 static::addGlobalScope(new TenantScope);
 }
}
