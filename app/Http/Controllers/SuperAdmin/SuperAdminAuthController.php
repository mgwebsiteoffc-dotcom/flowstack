<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SuperAdminAuthController extends Controller
{
    public function showLogin()
    {
        return view('super-admin.login', [
            'hasAdmins' => SuperAdmin::exists(),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $admin = SuperAdmin::where('email', $credentials['email'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            logger()->warning('Super admin login failed', [
                'email' => $credentials['email'],
                'admins_total' => SuperAdmin::count(),
            ]);

            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        $request->session()->put('super_admin', $admin->id);
        $request->session()->regenerate();

        logger()->info('Super admin login success', [
            'email' => $admin->email,
            'session_id' => $request->session()->getId(),
        ]);

        // Direct redirect, NOT intended(): a stale "intended URL" (e.g. /dashboard
        // left over from an earlier team-auth attempt) would bounce the super
        // admin to the TEAM dashboard -> team login page loop.
        return redirect()->route('super-admin.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('super_admin');
        $request->session()->regenerate();

        return redirect()->route('super-admin.login');
    }
}
