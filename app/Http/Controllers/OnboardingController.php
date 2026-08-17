<?php

namespace App\Http\Controllers;

use App\Mail\TeamInviteMail;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
 /**
 * 6-step wizard. The current step is stored in the session so the user can
 * resume after skipping. Step POSTs validate + save, then bump the step.
 */
 public function index()
 {
 $tenant = app('currentTenant');
 $step = min((int) session('onboarding_step', 1), 6);

 return view('onboarding.index', compact('tenant', 'step'));
 }

 public function store(Request $request)
 {
 $tenant = app('currentTenant');
 $step = (int) $request->input('step', 1);

 $settings = $tenant->settings ?? [];
 $user = auth()->user();

 switch ($step) {
 case 1: // Setup: logo, timezone, currency
 $validated = $request->validate([
 'timezone' => ['required', 'string'],
 'currency' => ['required', 'string', 'size:3'],
 ]);
 $settings['timezone'] = $validated['timezone'];
 $settings['currency'] = $validated['currency'];
 $tenant->settings = $settings;
 $tenant->save();

 if ($request->hasFile('logo')) {
 $request->validate(['logo' => ['image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048']]);
 $path = $request->file('logo')->store('tenants/'.$tenant->id.'/branding', 'tenant');
 $tenant->logo = basename($path);
 $tenant->save();
 }
 break;

 case 2: // Invite team
 if ($request->input('skip')) {
 break;
 }

 $emails = collect($request->input('emails', []))->filter()->values();
 $roles = $request->input('roles', []);

 $emails->each(function ($email, $i) use ($roles, $tenant) {
 $role = $roles[$i] ?? 'specialist';

 if (! in_array($role, User::ROLES, true) || User::where('email', $email)->exists()) {
 return;
 }

 $inviteToken = Str::random(64);

 $user = User::create([
 'tenant_id' => $tenant->id,
 'name' => Str::before($email, '@'),
 'email' => $email,
 'password' => bcrypt(Str::random(24)),
 'role' => $role,
 ]);

 \App\Models\Setting::setForTenant($tenant->id, 'invite_token_'.$user->id, $inviteToken);

 Mail::to($email)->queue(new TeamInviteMail($tenant->name, $email, $role, route('onboarding.invite-accept', $inviteToken)));
 });
 break;

 case 4: // Lead365 integration instructions (no-op save; URL is auto-generated)
 break;

 case 5: // BikriBook connection
 if ($request->filled('bikribook_api_key') || $request->filled('skip')) {
 $apiKey = $request->input('bikribook_api_key');
 if ($apiKey) {
 $tenant->setBikriBookApiKeyEncrypted($apiKey);
 $tenant->bikribook_base_url = $request->input('bikribook_base_url') ?: null;
 $tenant->save();
 }
 }
 break;

 case 3: // Add first client is handled by ClientController
 case 6:
 break;
 }

 $next = $step + 1;

 if ($next > 6) {
 $request->session()->forget('onboarding_step');

 return redirect()->route('onboarding.done');
 }

 $request->session()->put('onboarding_step', $next);

 return redirect()->route('onboarding');
 }

 public function done()
 {
 $tenant = app('currentTenant');
 session()->forget('onboarding_step');

 return view('onboarding.done', compact('tenant'));
 }
}
