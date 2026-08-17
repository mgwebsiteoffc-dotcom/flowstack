<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Illuminate\Http\Request;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'trials' => Tenant::where('is_trial', true)->count(),
            'mrr' => Tenant::query()
                ->where('is_trial', false)
                ->where('is_active', true)
                ->get()
                ->sum(fn ($t) => (float) ($t->plan?->price_monthly ?? 0)),
            'new_signups_month' => Tenant::where('created_at', '>=', now()->startOfMonth())->count(),
            'churn_month' => Tenant::where('is_active', false)->where('updated_at', '>=', now()->startOfMonth())->count(),
        ];

        $recentPayments = SubscriptionPayment::with('tenant')->latest()->limit(10)->get();
        $tenants = Tenant::with('plan')->latest()->limit(8)->get();

        return view('super-admin.dashboard', compact('stats', 'recentPayments', 'tenants'));
    }

    public function payments()
    {
        $payments = SubscriptionPayment::with('tenant', 'subscription')->latest()->paginate(20);

        return view('super-admin.payments', compact('payments'));
    }
}
