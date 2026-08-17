<?php

namespace App\Http\Middleware;

use App\Exceptions\SubscriptionExpiredException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks internal routes when the tenant's trial has expired or the paid
 * subscription lapsed, redirecting to the upgrade page. The upgrade/checkout
 * routes themselves are exempt.
 */
class EnsureSubscriptionActive
{
 public function handle(Request $request, Closure $next): Response
 {
 // Belt & braces: never let app('currentTenant') throw on the few
 // paths where the key could still be unresolvable.
 $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

 if ($tenant === null) {
 return $next($request);
 }

 if (! $tenant->is_active) {
 throw new SubscriptionExpiredException('Your workspace has been deactivated.');
 }

 $now = now();
 $trialExpired = $tenant->is_trial && $tenant->trial_ends_at && $tenant->trial_ends_at->lt($now);
 $planExpired = $tenant->plan_expires_at && $tenant->plan_expires_at->lt($now);

 if ($trialExpired || $planExpired) {
 $route = $request->route()?->getName();

 $exempt = ['upgrade', 'subscription.checkout', 'subscription.checkout.callback', 'logout', 'subscription.plans'];

 if (in_array($route, $exempt, true)) {
 return $next($request);
 }

 throw new SubscriptionExpiredException('Your trial/subscription has expired. Please upgrade to continue.');
 }

 return $next($request);
 }
}
