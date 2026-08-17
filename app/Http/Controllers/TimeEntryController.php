<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = TimeEntry::with('task', 'client', 'user')->latest();

        if (! $user->canAccessFinance()) {
            $query->where('user_id', $user->id);
        }

        $entries = $query->paginate(config('tenancy.pagination_size'))->withQueryString();
        $tasks = Task::whereNotIn('status', ['done', 'cancelled'])->orderBy('title')->get();
        $clients = Client::orderBy('company_name')->get();
        $running = TimeEntry::where('user_id', $user->id)->where('is_running', true)->first();

        return view('time.index', compact('entries', 'tasks', 'clients', 'running'));
    }

    /**
     * My timesheet: week selector, day columns, totals.
     */
    public function myTimesheet(Request $request)
    {
        $weekStart = $request->input('week')
            ? now()->parse($request->input('week'))->startOfWeek()
            : now()->startOfWeek();

        $weekEnd = $weekStart->copy()->endOfWeek();

        $entries = TimeEntry::where('user_id', auth()->id())
            ->where('started_at', '>=', $weekStart)
            ->where('started_at', '<=', $weekEnd)
            ->with('task', 'client')
            ->get();

        $days = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));

        $grid = $entries->groupBy(fn ($e) => $e->started_at->format('Y-m-d'));

        $byClient = $entries->groupBy(fn ($e) => $e->client_id)
            ->mapWithKeys(fn ($group, $clientId) => [
                ($clientId ? Client::find($clientId)?->company_name : 'No client') => [
                    'minutes' => $group->sum('duration_minutes'),
                    'billable' => $group->where('is_billable', true)->sum('duration_minutes'),
                ],
            ]);

        $totals = [
            'minutes' => $entries->sum('duration_minutes'),
            'billable' => $entries->where('is_billable', true)->sum('duration_minutes'),
        ];

        return view('time.my-timesheet', compact('entries', 'days', 'grid', 'byClient', 'totals', 'weekStart'));
    }

    /**
     * All time (admin/ops): team-wide, filterable.
     */
    public function all(Request $request)
    {
        if (! auth()->user()->canAccessFinance()) {
            abort(403);
        }

        $query = TimeEntry::with('task', 'client', 'user')->latest();

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($clientId = $request->input('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($from = $request->input('from')) {
            $query->where('started_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('started_at', '<=', $to);
        }

        $entries = $query->paginate(config('tenancy.pagination_size'))->withQueryString();
        $summary = (clone $query)->get()->groupBy('user_id')->map(fn ($g) => [
            'minutes' => $g->sum('duration_minutes'),
            'user' => $g->first()->user,
        ]);
        $users = User::where('is_active', true)->orderBy('name')->get();
        $clients = Client::orderBy('company_name')->get();

        return view('time.all', compact('entries', 'summary', 'users', 'clients'));
    }

    /**
     * Start the timer for a task.
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'task_id' => ['required', 'exists:tasks,id'],
        ]);

        $task = Task::findOrFail($validated['task_id']);

        // Stop any other running timer for this user.
        TimeEntry::where('user_id', auth()->id())->where('is_running', true)->get()->each(function ($entry) {
            $entry->update([
                'is_running' => false,
                'ended_at' => now(),
                'duration_minutes' => max(1, (int) $entry->started_at->diffInMinutes(now())),
            ]);
        });

        TimeEntry::create([
            'tenant_id' => $task->tenant_id,
            'task_id' => $task->id,
            'project_id' => $task->project_id,
            'client_id' => $task->client_id,
            'user_id' => auth()->id(),
            'started_at' => now(),
            'is_running' => true,
            'is_billable' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Timer started.');
    }

    public function stop(Request $request)
    {
        $entry = TimeEntry::where('user_id', auth()->id())->where('is_running', true)->first();

        if ($entry) {
            $entry->update([
                'is_running' => false,
                'ended_at' => now(),
                'duration_minutes' => max(1, (int) $entry->started_at->diffInMinutes(now())),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'entry_id' => $entry?->id]);
        }

        return back()->with('success', 'Timer stopped. '.($entry ? round($entry->duration_minutes / 60, 2).'h logged.' : ''));
    }

    /**
     * Manual time log.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_billable' => ['sometimes', 'boolean'],
        ]);

        $startedAt = now()->parse($validated['date'].' '.$validated['start_time'].':00');
        $endedAt = now()->parse($validated['date'].' '.$validated['end_time'].':00');

        $task = $validated['task_id'] ? Task::find($validated['task_id']) : null;

        TimeEntry::create([
            'tenant_id' => app('currentTenant')->id,
            'task_id' => $task?->id,
            'project_id' => $task?->project_id,
            'client_id' => $validated['client_id'] ?? $task?->client_id,
            'user_id' => auth()->id(),
            'description' => $validated['description'],
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_minutes' => (int) $startedAt->diffInMinutes($endedAt),
            'is_billable' => $request->boolean('is_billable', true),
            'is_running' => false,
        ]);

        return back()->with('success', 'Time entry logged.');
    }

    public function destroy(TimeEntry $entry)
    {
        $user = auth()->user();

        if (! $user->canAccessFinance() && $entry->user_id !== $user->id) {
            abort(403);
        }

        $entry->delete();

        return back()->with('success', 'Time entry deleted.');
    }
}
