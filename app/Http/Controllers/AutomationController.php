<?php

namespace App\Http\Controllers;

use App\Models\AutomationLog;
use App\Models\AutomationRule;
use App\Models\Client;
use App\Models\Lead;
use App\Models\ProjectTemplate;
use App\Models\Task;
use App\Services\AutomationService;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function __construct()
    {
        // Automation management is manager-level.
        $this->middleware('role:admin,ops_manager')->except(['index', 'test']);
    }

    public function index()
    {
        $rules = AutomationRule::withCount('logs')->orderBy('name')->get();
        $recentLogs = AutomationLog::with('rule')->latest()->take(20)->get();
        $templates = ProjectTemplate::orderBy('name')->get();

        return view('automation.index', compact('rules', 'recentLogs', 'templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trigger_event' => ['required', 'in:'.implode(',', AutomationRule::TRIGGERS)],
            'trigger_delay_hours' => ['nullable', 'integer', 'min:0', 'max:720'],
            'conditions' => ['nullable', 'array'],
            'actions' => ['required', 'array', 'min:1'],
        ]);

        AutomationRule::create([
            'tenant_id' => app('currentTenant')->id,
            'name' => $validated['name'],
            'trigger_event' => $validated['trigger_event'],
            'trigger_delay_hours' => $validated['trigger_delay_hours'] ?? 0,
            'conditions' => $validated['conditions'] ?? [],
            'actions' => $validated['actions'],
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Automation rule created.');
    }

    public function update(Request $request, AutomationRule $rule)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trigger_event' => ['required', 'in:'.implode(',', AutomationRule::TRIGGERS)],
            'trigger_delay_hours' => ['nullable', 'integer', 'min:0', 'max:720'],
            'conditions' => ['nullable', 'array'],
            'actions' => ['required', 'array', 'min:1'],
        ]);

        $rule->update($validated);

        return back()->with('success', 'Rule updated.');
    }

    public function toggle(AutomationRule $rule)
    {
        $rule->update(['is_active' => ! $rule->is_active]);

        return back()->with('success', $rule->is_active ? 'Rule enabled.' : 'Rule disabled.');
    }

    public function destroy(AutomationRule $rule)
    {
        $rule->delete();

        return back()->with('success', 'Rule deleted.');
    }

    /**
     * Dry run: executes the rule against a chosen test subject without
     * persisting actions (logs a skipped run instead).
     */
    public function test(Request $request, AutomationRule $rule)
    {
        $validated = $request->validate([
            'subject_type' => ['required', 'in:task,lead,client'],
            'subject_id' => ['required', 'integer'],
        ]);

        $model = match ($validated['subject_type']) {
            'task' => Task::find($validated['subject_id']),
            'lead' => Lead::find($validated['subject_id']),
            default => Client::find($validated['subject_id']),
        };

        if (! $model) {
            return back()->with('error', 'Test subject not found.');
        }

        $service = app(AutomationService::class);

        // Dry run: just evaluate conditions and log.
        $context = $this->dryContext($rule, $model);

        if ($context['matches']) {
            AutomationLog::create([
                'tenant_id' => $rule->tenant_id,
                'rule_id' => $rule->id,
                'trigger_data' => $context['context'],
                'status' => 'skipped',
                'actions_taken' => null,
                'error_message' => 'DRY RUN: conditions matched - '.count($rule->actions ?? []).' action(s) would execute.',
                'created_at' => now(),
            ]);

            return back()->with('success', 'Dry run OK: conditions matched. '.count($rule->actions ?? []).' action(s) would run.');
        }

        AutomationLog::create([
            'tenant_id' => $rule->tenant_id,
            'rule_id' => $rule->id,
            'trigger_data' => $context['context'],
            'status' => 'skipped',
            'error_message' => 'DRY RUN: conditions did not match.',
            'created_at' => now(),
        ]);

        return back()->with('info', 'Dry run: conditions did not match for this subject.');
    }

    protected function dryContext(AutomationRule $rule, $model): array
    {
        $context = [];
        $matches = true;

        foreach ($rule->conditions ?? [] as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? 'equals';
            $value = $condition['value'] ?? null;

            $actual = $model->{$field} ?? null;

            $ok = match ($operator) {
                'equals' => (string) $actual === (string) $value,
                'not_equals' => (string) $actual !== (string) $value,
                'greater_than' => (float) $actual > (float) $value,
                'less_than' => (float) $actual < (float) $value,
                'is_null' => $actual === null,
                'not_null' => $actual !== null,
                default => true,
            };

            $context[$field] = $actual;

            if (! $ok) {
                $matches = false;
            }
        }

        return ['matches' => $matches, 'context' => $context];
    }
}
