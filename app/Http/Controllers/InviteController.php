<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * Team member invitation acceptance: public link with a one-time token.
 */
class InviteController extends Controller
{
 public function accept(string $token)
 {
 $record = Setting::withoutGlobalScopes()
 ->where('key', 'like', 'invite_token_%')
 ->where('value', $token)
 ->first();

 if (! $record) {
 abort(404, 'This invitation link is invalid or has expired.');
 }

 $user = User::withoutGlobalScopes()->find((int) str_replace('invite_token_', '', $record->key));

 if (! $user) {
 abort(404, 'This invitation link is invalid or has expired.');
 }

 return view('auth.invite-accept', compact('token', 'user'));
 }

 public function store(Request $request, string $token)
 {
 $record = Setting::withoutGlobalScopes()->where('key', 'like', 'invite_token_%')->where('value', $token)->first();

 if (! $record) {
 abort(404, 'This invitation link is invalid or has expired.');
 }

 $user = User::withoutGlobalScopes()->find((int) str_replace('invite_token_', '', $record->key));

 if (! $user) {
 abort(404, 'This invitation link is invalid or has expired.');
 }

 $validated = $request->validate([
 'name' => ['required', 'string', 'max:255'],
 'password' => ['required', 'confirmed', Rules\Password::defaults()],
 ]);

 $user->update([
 'name' => $validated['name'],
 'password' => Hash::make($validated['password']),
 'email_verified_at' => now(),
 ]);

 // One-time token: consume it.
 $record->delete();

 \App\Scopes\TenantScope::setCurrent($user->tenant_id);
 Auth::login($user);

 return redirect()->route('dashboard')->with('success', 'Welcome aboard! Your account is ready.');
 }
}
