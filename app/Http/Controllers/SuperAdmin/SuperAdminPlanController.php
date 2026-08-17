<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class SuperAdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('tenants')->orderBy('price_monthly')->get();

        return view('super-admin.plans', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:plans,slug'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_clients' => ['nullable', 'integer', 'min:1'],
            'max_storage_gb' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        Plan::create($validated + ['features' => $request->input('features', [])]);

        return back()->with('success', 'Plan created.');
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_clients' => ['nullable', 'integer', 'min:1'],
            'max_storage_gb' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $plan->update($validated + ['features' => $request->input('features', [])]);

        return back()->with('success', 'Plan updated.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->tenants()->exists()) {
            return back()->with('error', 'Cannot delete a plan that has tenants.');
        }

        $plan->delete();

        return back()->with('success', 'Plan deleted.');
    }
}
