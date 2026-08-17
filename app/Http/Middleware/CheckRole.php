<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level role guard: role:admin,ops_manager
 * Restricts a route group to the given roles. Finer-grained permission checks
 * happen in Policies.
 */
class CheckRole
{
 public function handle(Request $request, Closure $next, string ...$roles): Response
 {
 $user = $request->user();

 if ($user === null) {
 abort(403);
 }

 if (! in_array($user->role, $roles, true)) {
 abort(403);
 }

 return $next($request);
 }
}
