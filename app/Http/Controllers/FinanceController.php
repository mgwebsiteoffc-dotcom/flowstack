<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if (! $user->canAccessFinance()) {
            abort(403);
        }

        $query = Invoice::with('client');
        $expenseQuery = Expense::query();

        if ($user->isAccountManager()) {
            $clientIds = Client::where('account_manager_id', $user->id)->pluck('id');
            $query->whereIn('client_id', $clientIds);
            $expenseQuery->whereIn('client_id', $clientIds);
        }

        $monthStart = now()->startOfMonth();

        $stats = [
            'revenue' => (float) (clone $query)->where('status', 'paid')->sum('paid_amount'),
            'outstanding' => (float) (clone $query)->whereIn('status', ['sent', 'overdue'])->get()->sum(fn ($i) => $i->balanceDue()),
            'paid_this_month' => (float) (clone $query)->where('status', 'paid')->where('payment_date', '>=', $monthStart)->sum('paid_amount'),
            'overdue_count' => (clone $query)->where('status', 'overdue')->count(),
            'expenses_this_month' => (float) (clone $expenseQuery)->where('expense_date', '>=', $monthStart->toDateString())->sum('amount'),
        ];

        $revenueByMonth = (clone $query)->where('status', 'paid')
            ->where('payment_date', '>=', now()->startOfMonth()->subMonths(5))
            ->get()
            ->groupBy(fn ($i) => $i->payment_date?->format('M Y'))
            ->map(fn ($g) => (float) $g->sum('paid_amount'));

        $invoices = (clone $query)->latest()->limit(10)->get();

        return view('finance.index', compact('stats', 'revenueByMonth', 'invoices'));
    }
}
