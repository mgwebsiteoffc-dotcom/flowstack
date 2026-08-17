<?php

namespace App\Http\Middleware;

use App\Exceptions\TenantNotFoundException;
use App\Models\Tenant;
use App\Scopes\TenantScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the tenant from the request subdomain and stores it in the app
 * container as app('currentTenant'). Applied to all internal (authenticated)
 * routes. Public routes (landing, register, webhooks, portal, super admin)
 * do not use this middleware.
 */
class TenantMiddleware
{
 public function handle(Request $request, Closure $next): Response
 {
 $tenant = $this->resolveTenant($request);

 if ($tenant !== null) {
 if (! $tenant->is_active) {
 return $this->abortOrRedirect($request, 'This workspace is inactive. Contact support.');
 }

 TenantScope::setCurrentTenant($tenant);
 }

 return $next($request);
 }

 protected function resolveTenant(Request $request): ?Tenant
 {
 $host = $request->getHost();
 $baseDomain = strtolower((string) config('tenancy.tenant_domain', 'yoursaas.com'));
 $host = strtolower($host);

 // Strip a "www." prefix before matching.
 if (str_starts_with($host, 'www.')) {
 $host = substr($host, 4);
 }

 // Host is exactly the base domain (no subdomain).
 if ($host === $baseDomain || $host === 'localhost') {
 return $this->fallbackTenant();
 }

 $subdomain = strstr($host, '.', true) ?: $host;

 // Host is not under the tenant domain at all (e.g. a preview domain).
 if (! str_ends_with($host, '.'.$baseDomain)) {
 return $this->fallbackTenant();
 }

 $tenant = Tenant::where('slug', $subdomain)->first();

 if ($tenant === null) {
 throw new TenantNotFoundException('No workspace found for "'.$subdomain.'.'.$baseDomain.'".');
 }

 return $tenant;
 }

 /**
 * Local-development convenience: when the request arrives without a
 * subdomain (artisan serve / preview host) use the authenticated user's
 * tenant first (fixes multi-tenant local DBs where the wrong fallback
 * tenant would make auth()->user() resolve to null -> 500 on every page),
 * then fall back to the first active tenant. Disabled in production.
 */
 protected function fallbackTenant(): ?Tenant
 {
 if (! config('tenancy.fallback_to_single_tenant', false)) {
 return null;
 }

 // Prefer the logged-in user's own tenant.
 if (auth()->check()) {
 $userTenant = auth()->user()->tenant;

 if ($userTenant && $userTenant->is_active) {
 return $userTenant;
 }
 }

 return Tenant::query()
 ->where('is_active', true)
 ->orderBy('id')
 ->first();
 }

 protected function abortOrRedirect(Request $request, string $message): Response
 {
 if ($request->expectsJson()) {
 return response()->json(['message' => $message], 403);
 }

 return redirect()->route('upgrade')->with('error', $message);
 }
}
