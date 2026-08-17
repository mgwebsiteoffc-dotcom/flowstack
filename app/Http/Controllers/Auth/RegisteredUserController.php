<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Scopes\TenantScope;
use Database\Seeders\DefaultDataSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * SaaS registration: creates the tenant (14-day trial), the admin user,
     * seeds default tenant data, logs the admin in and sends them to the
     * onboarding wizard.
     */
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $validated = $request->validated();

        $slug = Str::slug($validated['agency_name'] ?? $validated['subdomain'] ?? $validated['name']);

        $tenant = Tenant::create([
            'name' => $validated['agency_name'],
            'slug' => $validated['subdomain'],
            'email' => $validated['email'],
            'is_trial' => true,
            'is_active' => true,
            'trial_ends_at' => now()->addDays(14),
            'plan_started_at' => now(),
            'settings' => [
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
                'date_format' => 'd M Y',
                'invoice_prefix' => 'INV',
                'invoice_start_number' => 1001,
                'payment_terms_days' => 15,
                'tax_rate' => 18,
            ],
        ]);

        $user = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'designation' => 'Founder',
        ]);

        // Default per-tenant data (pipeline, categories, templates, automation rules).
        (new DefaultDataSeeder)->run($tenant->id, $user->id);

        event(new Registered($user));

        TenantScope::setCurrentTenant($tenant);

        Auth::login($user);

        return redirect()->route('onboarding')->with('success', 'Welcome to Agency OS! Let\'s set up your workspace.');
    }
}
