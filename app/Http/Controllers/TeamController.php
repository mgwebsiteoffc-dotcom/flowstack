<?php

namespace App\Http\Controllers;

use App\Mail\TeamInviteMail;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index()
    {
        // "Online" = active session within the last 5 minutes (database session driver).
        $onlineUserIds = \Illuminate\Support\Facades\DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        $users = User::withCount([
            'assignedTasks' => fn ($q) => $q->whereNotIn('status', ['done', 'cancelled'])->whereNull('parent_task_id'),
            'assignedTasks as overdue_count' => fn ($q) => $q->overdue()->whereNull('parent_task_id'),
        ])->orderBy('name')->get();

        $hoursThisWeek = TimeEntry::where('started_at', '>=', now()->startOfWeek())
            ->get()
            ->groupBy('user_id')
            ->map(fn ($entries) => round($entries->sum('duration_minutes') / 60, 1));

        $workloadByUser = $users->mapWithKeys(fn ($u) => [$u->id => [
            'active' => $u->assigned_tasks_count ?? 0,
            'overdue' => $u->overdue_count ?? 0,
            'hours' => $hoursThisWeek->get($u->id, 0),
        ]]);

        // Capacity planning: open tasks due per day for the next 7 days, per member.
        $capacityDays = collect(range(0, 6))->map(fn ($i) => now()->addDays($i));
        $capacity = $users->mapWithKeys(function ($u) use ($capacityDays) {
            $byDay = [];
            foreach ($capacityDays as $day) {
                $byDay[$day->toDateString()] = Task::where('assigned_to', $u->id)
                    ->where('due_date', $day->toDateString())
                    ->whereNotIn('status', ['done', 'cancelled'])
                    ->count();
            }

            return [$u->id => $byDay];
        });

        return view('team.index', compact('users', 'workloadByUser', 'capacityDays', 'capacity', 'onlineUserIds'));
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load('assignedTasks.client', 'assignedTasks.project');

        $stats = [
            'tasks_completed_month' => Task::where('assigned_to', $user->id)
                ->where('status', 'done')
                ->where('updated_at', '>=', now()->startOfMonth())
                ->count(),
            'on_time_rate' => $this->onTimeRate($user),
            'hours_logged_month' => round($user->timeEntries()
                ->where('started_at', '>=', now()->startOfMonth())
                ->sum('duration_minutes') / 60, 1),
        ];

        $clients = $user->clientTeamMembers()->with('client')->get()->pluck('client')->unique('id')->values();

        $tasks = Task::where('assigned_to', $user->id)
            ->with('client', 'project')
            ->orderByRaw("CASE status WHEN 'done' THEN 1 ELSE 0 END, due_date ASC")
            ->paginate(15);

        return view('team.show', compact('user', 'stats', 'clients', 'tasks'));
    }

    public function invite(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')->where(fn ($q) => $q->where('tenant_id', auth()->user()->tenant_id))],
            'role' => ['required', Rule::in(User::ROLES)],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $tenant = app('currentTenant');

        // Enforce plan user limit.
        if ($tenant->max_users && User::count() >= $tenant->max_users) {
            return back()->with('error', 'You have reached your plan limit of '.$tenant->max_users.' users. Upgrade to add more.');
        }

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'] ?? Str::before($validated['email'], '@'),
            'email' => $validated['email'],
            'password' => bcrypt(Str::random(24)),
            'role' => $validated['role'],
        ]);

        $token = Str::random(64);
        Setting::setForTenant($tenant->id, 'invite_token_'.$user->id, $token);

        Mail::to($user->email, $user->name)->queue(new TeamInviteMail(
            $tenant->name,
            $user->email,
            $user->role,
            route('onboarding.invite-accept', $token)
        ));

        return back()->with('success', 'Invitation sent to '.$user->email.'.');
    }

    public function resendInvite(User $user)
    {
        $this->authorize('create', User::class);

        if ($user->email_verified_at) {
            return back()->with('info', $user->email.' has already joined the workspace.');
        }

        $token = Str::random(64);
        Setting::setForTenant(app('currentTenant')->id, 'invite_token_'.$user->id, $token);

        Mail::to($user->email, $user->name)->queue(new TeamInviteMail(
            app('currentTenant')->name,
            $user->email,
            $user->role,
            route('onboarding.invite-accept', $token)
        ));

        return back()->with('success', 'Invitation re-sent to '.$user->email.'.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'designation' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:64'],
            'hourly_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Only admins may set hourly cost (profitability input).
        if (! auth()->user()->isAdmin()) {
            unset($validated['hourly_cost']);
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated.');
    }

    public function updateRole(Request $request, User $user)
    {
        $this->authorize('setRole', User::class);

        $validated = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', 'Role updated.');
    }

    public function toggleActive(Request $request, User $user)
    {
        $this->authorize('delete', $user);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    protected function onTimeRate(User $user): int
    {
        $done = Task::where('assigned_to', $user->id)
            ->where('status', 'done')
            ->where('updated_at', '>=', now()->startOfMonth())
            ->get();

        if ($done->isEmpty()) {
            return 100;
        }

        $onTime = $done->filter(fn ($t) => ! $t->due_date || $t->updated_at->lte($t->due_date->endOfDay()))->count();

        return (int) round($onTime * 100 / $done->count());
    }
}
