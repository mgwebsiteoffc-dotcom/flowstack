<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $authenticated = Auth::attempt($credentials, $request->boolean('remember'));
        } catch (\Throwable $e) {
            // e.g. users table missing -> let the real error surface for diagnosis.
            logger()->error('Login attempt failed with exception', [
                'email' => $credentials['email'],
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => config('app.debug')
                    ? 'Login error: '.$e->getMessage()
                    : __('auth.failed'),
            ]);
        }

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Contact your workspace admin.',
            ]);
        }

        // Post-login setup must NEVER block the login itself: if anything here
        // fails (tenant context, last_login write, …) the user is still logged
        // in and the error is logged instead of a 500 on the login POST.
        try {
            $user->update(['last_login_at' => now()]);

            // Load the tenant explicitly so the container context is always set.
            $user->loadMissing('tenant');

            if ($user->tenant) {
                TenantScope::setCurrentTenant($user->tenant);
            }
        } catch (\Throwable $e) {
            logger()->error('Post-login setup failed (continuing login)', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
