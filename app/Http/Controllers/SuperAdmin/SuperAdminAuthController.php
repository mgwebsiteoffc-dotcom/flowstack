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
        return view('super-admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $admin = SuperAdmin::where('email', $credentials['email'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        $request->session()->put('super_admin', $admin->id);
        $request->session()->regenerate();

        return redirect()->intended(route('super-admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('super_admin');
        $request->session()->regenerate();

        return redirect()->route('super-admin.login');
    }
}
