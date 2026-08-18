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
 return view('auth.login', [
 'hasUsers' => User::withoutGlobalScopes()->exists(),
 ]);
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
 logger()->warning('Login failed (bad credentials)', [
 'email' => $credentials['email'],
 'ip' => $request->ip(),
 'users_total' => User::withoutGlobalScopes()->count(),
 ]);

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

 // Redirect to the intended URL ONLY when it is a safe internal app
 // URL. Never honor URLs pointing at the super-admin or portal areas:
 // a stale 'url.intended' from an earlier /super-admin or /portal visit
 // (they share this same session) used to bounce team logins to the
 // super-admin login screen after a successful login.
 $intended = $request->session()->pull('url.intended');
 $target = route('dashboard');

 if ($intended
 && str_starts_with($intended, '/')
 && ! str_starts_with($intended, '//')
 && ! str_contains($intended, '/super-admin')
 && ! str_contains($intended, '/portal')
 ) {
 $target = $intended;
 }

 logger()->info('Login success', [
 'email' => $user->email,
 'user_id' => $user->id,
 'tenant_id' => $user->tenant_id,
 'session_id' => $request->session()->getId(),
 'redirect' => $target,
 ]);

 return redirect($target);
 }

 public function destroy(Request $request)
 {
 Auth::guard('web')->logout();

 $request->session()->invalidate();
 $request->session()->regenerateToken();

 return redirect('/');
 }
}
