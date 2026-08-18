<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auth guard for the client portal, backed by the client_portal_users table.
 * The portal session lives in the same session store under the 'portal_user'
 * key, fully separated from the internal team auth.
 */
class PortalAuthMiddleware
{
 public function handle(Request $request, Closure $next): Response
 {
 $portalUserId = $request->session()->get('portal_user');

 if ($portalUserId === null) {
 // Custom intended key - redirect()->guest() would pollute the
 // shared 'url.intended' used by the team login.
 $request->session()->put('portal.intended', $request->fullUrl());

 return redirect()->route('portal.login');
 }

 $user = \App\Models\ClientPortalUser::withoutGlobalScopes()
 ->find($portalUserId);

 if ($user === null || ! $user->is_active) {
 $request->session()->forget('portal_user');

 return redirect()->route('portal.login')->with('error', 'Your portal account is no longer active.');
 }

 // Register the guard instance so Auth::guard('portal')->user() works.
 Auth::guard('portal')->setUser($user);

 return $next($request);
 }
}
