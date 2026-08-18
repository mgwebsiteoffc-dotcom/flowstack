<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminTenantController extends Controller
{
 public function index(Request $request)
 {
 $query = Tenant::with('plan')->withCount(['users', 'clients']);

 if ($status = $request->input('status')) {
 $query->where('is_active', $status === 'active');
 }

 if ($trial = $request->input('trial')) {
 $query->where('is_trial', $trial === '1');
 }

 if ($planId = $request->input('plan_id')) {
 $query->where('plan_id', $planId);
 }

 if ($search = $request->input('search')) {
 $query->where(function ($q) use ($search) {
 $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
 });
 }

 $tenants = $query->latest()->paginate(20)->withQueryString();
 $plans = \App\Models\Plan::all();

 return view('super-admin.tenants', compact('tenants', 'plans'));
 }

 public function show(Tenant $tenant)
 {
 $tenant->load('plan', 'subscriptions.plan', 'subscriptionPayments');

 $usage = [
 'users' => $tenant->users()->count(),
 'clients' => $tenant->clients()->count(),
 'invoices' => $tenant->invoices()->count(),
 'leads' => $tenant->leads()->count(),
 ];

 return view('super-admin.tenant-show', compact('tenant', 'usage'));
 }

 public function update(Request $request, Tenant $tenant)
 {
 $validated = $request->validate([
 'name' => ['required', 'string', 'max:255'],
 'email' => ['required', 'email'],
 'is_active' => ['sometimes', 'boolean'],
 'plan_id' => ['nullable', 'exists:plans,id'],
 'max_users' => ['nullable', 'integer', 'min:1'],
 'max_clients' => ['nullable', 'integer', 'min:1'],
 ]);

 $tenant->update($validated);

 return back()->with('success', 'Tenant updated.');
 }

 /**
 * Login as the tenant's admin (session-based impersonation).
 */
 public function impersonate(Tenant $tenant)
 {
 $admin = User::withoutGlobalScopes()
 ->where('tenant_id', $tenant->id)
 ->where('role', 'admin')
 ->orderBy('id')
 ->first();

 if (! $admin) {
 return back()->with('error', 'This tenant has no admin user.');
 }

 session()->put('impersonator_admin', session()->get('super_admin'));

 \App\Scopes\TenantScope::setCurrentTenant($tenant);

 auth()->login($admin);

 return redirect()->route('dashboard')->with('success', 'Impersonating '.$tenant->name.' (admin: '.$admin->email.')');
 }

 /**
 * Ends a super-admin impersonation and restores the super admin session.
 * This route must be reachable while logged in as the tenant admin, so it
 * is registered outside the super.admin middleware group.
 */
 public function stopImpersonation()
 {
 if (! session()->has('impersonator_admin')) {
 return redirect()->route('dashboard');
 }

 $superAdminId = session()->pull('impersonator_admin');
 session()->put('super_admin', $superAdminId);

 auth()->logout();
 session()->regenerate();

 return redirect()->route('super-admin.dashboard')->with('success', 'Impersonation ended.');
 }

 public function extendTrial(Tenant $tenant)
 {
 $days = (int) request()->input('days', 14);

 $tenant->update([
 'is_trial' => true,
 'is_active' => true,
 'trial_ends_at' => now()->addDays($days),
 ]);

 return back()->with('success', "Trial extended by {$days} days.");
 }
}
