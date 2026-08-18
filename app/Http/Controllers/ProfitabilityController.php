<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\Request;

class ProfitabilityController extends Controller
{
 /**
 * Monthly profitability: Revenue - Team Cost - Tools Cost per client,
 * with margin colours (green >40%, yellow 25-40%, red <25%).
 */
 public function index(Request $request)
 {
 $user = auth()->user();

 if (! $user->canAccessFinance()) {
 abort(403);
 }

 $month = $request->input('month')
 ? now()->parse($request->input('month').'-01')
 : now()->startOfMonth();

 $monthStart = $month->copy()->startOfMonth();
 $monthEnd = $month->copy()->endOfMonth();

 $query = Client::with(['timeEntries', 'expenses', 'invoices']);

 if ($user->isAccountManager()) {
 $query->where('account_manager_id', $user->id);
 }

 $clients = $query->get();

 $rows = $clients->map(function ($client) use ($monthStart, $monthEnd) {
 $revenue = (float) $client->invoices
 ->where('status', 'paid')
 ->filter(fn ($i) => $i->payment_date && $i->payment_date->between($monthStart, $monthEnd))
 ->sum('paid_amount');

 // Team cost: sum(duration_minutes/60 x hourly_cost) from time entries.
 $teamCost = 0.0;
 foreach ($client->timeEntries->filter(fn ($t) => $t->started_at && $t->started_at->between($monthStart, $monthEnd)) as $entry) {
 $hourlyCost = (float) ($entry->user?->hourly_cost ?? 0);
 $teamCost += round($entry->duration_minutes / 60, 2) * $hourlyCost;
 }

 $toolsCost = (float) $client->expenses
 ->where('is_billable', false)
 ->filter(fn ($e) => $e->expense_date->between($monthStart, $monthEnd))
 ->sum('amount');

 $profit = $revenue - $teamCost - $toolsCost;
 $margin = $revenue > 0 ? round($profit * 100 / $revenue, 1) : 0.0;

 $status = $margin > 40 ? 'green' : ($margin >= 25 ? 'yellow' : 'red');

 return (object) [
 'client' => $client,
 'revenue' => $revenue,
 'team_cost' => round($teamCost, 2),
 'tools_cost' => $toolsCost,
 'profit' => round($profit, 2),
 'margin' => $margin,
 'status' => $status,
 ];
 });

 $totals = (object) [
 'revenue' => round($rows->sum('revenue'), 2),
 'team_cost' => round($rows->sum('team_cost'), 2),
 'tools_cost' => round($rows->sum('tools_cost'), 2),
 'profit' => round($rows->sum('profit'), 2),
 'margin' => $rows->sum('revenue') > 0 ? round($rows->sum('profit') * 100 / $rows->sum('revenue'), 1) : 0,
 ];

 // Monthly trend (6 months).
 $trend = collect(range(5, 0))->mapWithKeys(function ($i) use ($monthStart, $user) {
 $start = $monthStart->copy()->subMonths($i)->startOfMonth();
 $end = $start->copy()->endOfMonth();

 $invoiceQuery = Invoice::where('status', 'paid')->whereBetween('payment_date', [$start, $end]);
 $expenseQuery = Expense::whereBetween('expense_date', [$start, $end]);

 if ($user->isAccountManager()) {
 $clientIds = Client::where('account_manager_id', $user->id)->pluck('id');
 $invoiceQuery->whereIn('client_id', $clientIds);
 $expenseQuery->whereIn('client_id', $clientIds);
 }

 $revenue = (float) $invoiceQuery->sum('paid_amount');
 $cost = (float) $expenseQuery->sum('amount');

 // Team cost for the month.
 $entries = TimeEntry::whereBetween('started_at', [$start, $end])->get();
 $teamCost = 0.0;
 foreach ($entries as $entry) {
 $teamCost += round($entry->duration_minutes / 60, 2) * (float) ($entry->user?->hourly_cost ?? 0);
 }

 return [$start->format('M Y') => [
 'revenue' => $revenue,
 'cost' => round($cost + $teamCost, 2),
 'profit' => round($revenue - $cost - $teamCost, 2),
 ]];
 });

 $teamMembers = User::with('timeEntries')->where('is_active', true)->orderBy('name')->get();

 return view('finance.profitability', compact('rows', 'totals', 'trend', 'month', 'teamMembers'));
 }
}
