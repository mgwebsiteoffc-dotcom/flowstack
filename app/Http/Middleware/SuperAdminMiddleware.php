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
            return redirect()->guest(route('super-admin.login'));
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
