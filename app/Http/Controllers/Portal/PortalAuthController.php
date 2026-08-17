<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ClientPortalUser;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class PortalAuthController extends Controller
{
    public function showLogin()
    {
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = ClientPortalUser::withoutGlobalScopes()
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        if (! $user->is_active) {
            return back()->withErrors(['email' => 'This portal account is inactive.']);
        }

        $user->update(['last_login_at' => now()]);

        $request->session()->put('portal_user', $user->id);
        $request->session()->regenerate();

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('portal_user');
        $request->session()->regenerate();

        return redirect()->route('portal.login');
    }

    public function showSetPassword(string $token)
    {
        $record = Setting::withoutGlobalScopes()
            ->where('key', 'like', 'portal_set_password_%')
            ->where('value', $token)
            ->first();

        if (! $record) {
            abort(404, 'This invitation link is invalid or has expired.');
        }

        $user = ClientPortalUser::withoutGlobalScopes()->find((int) str_replace('portal_set_password_', '', $record->key));

        if (! $user) {
            abort(404, 'This invitation link is invalid or has expired.');
        }

        return view('portal.set-password', compact('token'));
    }

    public function setPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $record = Setting::withoutGlobalScopes()
            ->where('key', 'like', 'portal_set_password_%')
            ->where('value', $validated['token'])
            ->first();

        if (! $record) {
            return back()->withErrors(['token' => 'This invitation link is invalid or has expired.']);
        }

        $user = ClientPortalUser::withoutGlobalScopes()->find((int) str_replace('portal_set_password_', '', $record->key));

        if (! $user) {
            return back()->withErrors(['token' => 'This invitation link is invalid or has expired.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $record->delete();

        $request->session()->put('portal_user', $user->id);

        return redirect()->route('portal.dashboard')->with('success', 'Welcome to your client portal!');
    }
}
