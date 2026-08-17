<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the /super-admin area, authenticated against the super_admins table
 * (separate from both team auth and the client portal).
 */
class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $superAdminId = $request->session()->get('super_admin');

        if ($superAdminId === null) {
            // Custom intended key - NEVER use redirect()->guest() here: it
            // writes Laravel's shared 'url.intended', which would then send
            // TEAM logins to the super-admin area (and vice versa).
            $request->session()->put('super_admin.intended', $request->fullUrl());

            return redirect()->route('super-admin.login');
        }

        $admin = \App\Models\SuperAdmin::find($superAdminId);

        if ($admin === null) {
            $request->session()->forget('super_admin');

            return redirect()->route('super-admin.login');
        }

        $request->attributes->set('super_admin', $admin);

        return $next($request);
    }
}
