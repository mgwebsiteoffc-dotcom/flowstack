<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Models\IntegrationToken;
use App\Models\Setting;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
 public function index()
 {
 $user = auth()->user();
 $tenant = app('currentTenant') ?: $user?->tenant;

 if (! $tenant) {
 return redirect()->route('register')->with('error', 'No workspace found for your account. Please register a new workspace.');
 }

 if ($user->isSpecialist()) {
 return $this->specialistDashboard($user);
 }

 if ($user->isAccountManager()) {
 return $this->accountManagerDashboard($user);
 }

 return $this->fullDashboard($tenant);
 }

 protected function fullDashboard($tenant)
 {
 $stats = Cache::remember('dashboard.stats.'.$tenant->id, 300, function () use ($tenant) {
 $sixMonthsAgo = now()->startOfMonth()->subMonths(5);

 return [
 'monthly_revenue' => Invoice::where('status', 'paid')
 ->where('payment_date', '>=', now()->startOfMonth())
 ->sum('paid_amount'),
 'active_clients' => Client::where('status', 'active')->count(),
 'overdue_tasks' => Task::overdue()->count(),
 'pipeline_value' => Lead::where('status', 'active')->sum('estimated_value'),
 'revenue_by_month' => Invoice::where('status', 'paid')
 ->where('payment_date', '>=', $sixMonthsAgo)
 ->get()
 ->filter(fn ($i) => $i->payment_date !== null)
 ->groupBy(fn ($i) => $i->payment_date->format('M Y'))
 ->map(fn ($group) => (float) $group->sum('paid_amount')),
 ];
 });

 $clients = Client::with('accountManager')->orderBy('health_score')->take(8)->get();
 $tasksDueToday = Task::dueToday()->with('assignee', 'client')->orderBy('priority')->get();
 $upcomingDeadlines = Task::whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
 ->whereNotIn('status', ['done', 'cancelled'])
 ->with('assignee', 'client')
 ->orderBy('due_date')
 ->take(10)
 ->get();
 $teamWorkload = User::withCount([
 'assignedTasks' => fn ($q) => $q->whereNotIn('status', ['done', 'cancelled']),
 ])->orderBy('assigned_tasks_count', 'desc')->get();
 $recentActivity = \App\Models\ActivityLog::with('user')->latest()->take(10)->get();
 $pipeline = \App\Models\LeadPipelineStage::withCount(['leads' => fn ($q) => $q->where('status', 'active')])
 ->orderBy('order_index')->get();

 // Google Calendar upcoming-events widget (when connected).
 $upcomingEvents = [];
 $calendarConnected = IntegrationToken::withoutGlobalScopes()
 ->where('provider', GoogleCalendarService::PROVIDER)
 ->where('tenant_id', $tenant->id)
 ->exists();

 if ($calendarConnected) {
 try {
 $upcomingEvents = (new GoogleCalendarService($tenant))->listUpcomingEvents(6);
 } catch (\Throwable $e) {
 GoogleCalendarService::logFailure($e, 'dashboard widget');
 }
 }

 return view('dashboard.index', compact('stats', 'clients', 'tasksDueToday', 'upcomingDeadlines', 'teamWorkload', 'recentActivity', 'pipeline', 'upcomingEvents', 'calendarConnected'));
 }

 protected function accountManagerDashboard($user)
 {
 $clientIds = Client::where('account_manager_id', $user->id)->pluck('id');

 $stats = [
 'clients' => $clientIds->count(),
 'open_tasks' => Task::whereIn('client_id', $clientIds)->whereNotIn('status', ['done', 'cancelled'])->count(),
 'pending_approvals' => \App\Models\ClientApproval::whereIn('client_id', $clientIds)->where('status', 'pending')->count(),
 'reports_due' => \App\Models\Report::whereIn('client_id', $clientIds)->where('status', 'draft')->count(),
 ];

 $clients = Client::whereIn('id', $clientIds)->with('accountManager')->get();
 $tasksDueToday = Task::whereIn('client_id', $clientIds)->dueToday()->with('client')->get();

 return view('dashboard.account-manager', compact('stats', 'clients', 'tasksDueToday'));
 }

 protected function specialistDashboard($user)
 {
 $stats = [
 'my_tasks' => Task::where('assigned_to', $user->id)->whereNotIn('status', ['done', 'cancelled'])->count(),
 'overdue' => Task::where('assigned_to', $user->id)->overdue()->count(),
 'due_today' => Task::where('assigned_to', $user->id)->dueToday()->count(),
 'hours_this_week' => round($user->timeEntries()
 ->where('started_at', '>=', now()->startOfWeek())
 ->sum('duration_minutes') / 60, 1),
 ];

 $tasks = Task::where('assigned_to', $user->id)
 ->with('client', 'project')
 ->orderByRaw('CASE WHEN status = "done" THEN 1 ELSE 0 END, due_date ASC')
 ->take(15)
 ->get();

 return view('dashboard.specialist', compact('stats', 'tasks'));
 }
}
