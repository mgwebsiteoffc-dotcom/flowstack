<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BikriBookSyncLog;
use App\Models\LeadPipelineStage;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WebhookLog;
use App\Services\BikriBookService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Agency settings: branding, defaults, invoice details, bank info.
     */
    public function index()
    {
        $tenant = app('currentTenant');
        $settings = $tenant->settings ?? [];

        return view('settings.index', compact('tenant', 'settings'));
    }

    public function update(Request $request)
    {
        $tenant = app('currentTenant');
        $settings = $tenant->settings ?? [];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'timezone' => ['required', 'string'],
            'currency' => ['required', 'string', 'size:3'],
            'date_format' => ['required', 'string', 'max:30'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'invoice_start_number' => ['required', 'integer', 'min:1'],
            'payment_terms_days' => ['required', 'integer', 'min:0', 'max:365'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bank_name' => ['nullable', 'string'],
            'bank_account_number' => ['nullable', 'string'],
            'bank_ifsc' => ['nullable', 'string'],
            'bank_beneficiary' => ['nullable', 'string'],
            'invoice_footer' => ['nullable', 'string'],
        ]);

        $tenant->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
        ]);

        $settings = array_merge($settings, [
            'timezone' => $validated['timezone'],
            'currency' => $validated['currency'],
            'date_format' => $validated['date_format'],
            'invoice_prefix' => $validated['invoice_prefix'],
            'invoice_start_number' => $validated['invoice_start_number'],
            'payment_terms_days' => $validated['payment_terms_days'],
            'tax_rate' => $validated['tax_rate'],
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_ifsc' => $validated['bank_ifsc'],
            'bank_beneficiary' => $validated['bank_beneficiary'],
            'invoice_footer' => $validated['invoice_footer'],
        ]);

        $tenant->settings = $settings;
        $tenant->save();

        if ($request->hasFile('logo')) {
            $request->validate(['logo' => ['image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048']]);
            $path = $request->file('logo')->store('tenants/'.$tenant->id.'/branding', 'tenant');
            $tenant->update(['logo' => basename($path)]);
        }

        ActivityLog::record('settings.updated');

        return back()->with('success', 'Agency settings saved.');
    }

    // ------------------------------------------------------------------
    // Users (manage team)
    // ------------------------------------------------------------------

    public function users()
    {
        $users = User::withCount('assignedTasks')->orderBy('name')->get();

        return view('settings.users', compact('users'));
    }

    // ------------------------------------------------------------------
    // Lead365 integration
    // ------------------------------------------------------------------

    public function lead365()
    {
        $tenant = app('currentTenant');
        $stages = LeadPipelineStage::orderBy('order_index')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $logs = WebhookLog::where('source', 'lead365')->latest()->limit(50)->get();

        $settings = collect([
            'lead365_autoassign_meta',
            'lead365_autoassign_form',
            'lead365_autoassign_lead365',
            'lead365_notify_meta',
            'lead365_notify_won',
            'lead365_notify_lost',
            'lead365_notify_stage',
            'lead365_notify_form',
        ])->mapWithKeys(fn ($key) => [$key => Setting::get($key, null)]);

        $stageMappings = collect($stages)->mapWithKeys(fn ($stage) => [
            'lead365_stage_map_'.$stage->lead365_stage_id => $stage->id,
        ])->filter();

        return view('settings.integrations.lead365', compact('tenant', 'stages', 'users', 'logs', 'settings', 'stageMappings'));
    }

    /**
     * Fires a synthetic lead.created event through the real webhook pipeline so
     * the tenant can verify end-to-end connectivity (appears in the event log).
     */
    public function testLead365()
    {
        $tenant = app('currentTenant');

        $log = WebhookLog::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'source' => 'lead365',
            'event_type' => 'test.connection',
            'payload' => [
                'event' => 'lead.created',
                'test' => true,
                'data' => [
                    'id' => 'TEST-'.now()->timestamp,
                    'name' => 'Test Lead',
                    'email' => 'test@example.com',
                    'company' => 'Test Company',
                    'source' => 'Lead365 Test',
                    'created_at' => now()->toIso8601String(),
                ],
            ],
            'status' => 'received',
            'ip_address' => request()->ip(),
        ]);

        \App\Jobs\Lead365\ProcessLead365Webhook::dispatch($log->payload, $tenant->id, $log->id);

        return back()->with('success', 'Test event sent through the pipeline. Check the event log below - a "Test Lead" should appear shortly.');
    }

    public function saveLead365(Request $request)
    {
        $tenant = app('currentTenant');

        $validated = $request->validate([
            'lead365_webhook_secret' => ['nullable', 'string', 'max:255'],
            'autoassign_meta' => ['nullable', 'exists:users,id'],
            'autoassign_form' => ['nullable', 'exists:users,id'],
            'autoassign_lead365' => ['nullable', 'exists:users,id'],
            'notify_meta' => ['sometimes', 'boolean'],
            'notify_won' => ['sometimes', 'boolean'],
            'notify_lost' => ['sometimes', 'boolean'],
            'notify_stage' => ['sometimes', 'boolean'],
            'notify_form' => ['sometimes', 'boolean'],
            'stage_mappings' => ['nullable', 'array'],
        ]);

        $tenant->update(['lead365_webhook_secret' => $validated['lead365_webhook_secret'] ?: $tenant->lead365_webhook_secret]);

        Setting::set('lead365_autoassign_meta', $validated['autoassign_meta'] ?? '');
        Setting::set('lead365_autoassign_form', $validated['autoassign_form'] ?? '');
        Setting::set('lead365_autoassign_lead365', $validated['autoassign_lead365'] ?? '');
        Setting::set('lead365_notify_meta', (int) $request->boolean('notify_meta', true));
        Setting::set('lead365_notify_won', (int) $request->boolean('notify_won', true));
        Setting::set('lead365_notify_lost', (int) $request->boolean('notify_lost', true));
        Setting::set('lead365_notify_stage', (int) $request->boolean('notify_stage', true));
        Setting::set('lead365_notify_form', (int) $request->boolean('notify_form', true));

        // Stage mappings: lead365 stage id -> local stage id.
        Setting::where('key', 'like', 'lead365_stage_map_%')->delete();
        foreach ($validated['stage_mappings'] ?? [] as $lead365StageId => $localStageId) {
            if ($localStageId) {
                Setting::set('lead365_stage_map_'.$lead365StageId, $localStageId);
            }
        }

        return back()->with('success', 'Lead365 settings saved.');
    }

    // ------------------------------------------------------------------
    // BikriBook integration
    // ------------------------------------------------------------------

    public function bikribook()
    {
        $tenant = app('currentTenant');
        $logs = BikriBookSyncLog::latest()->limit(20)->get();
        $settings = $tenant->settings ?? [];

        return view('settings.integrations.bikribook', compact('tenant', 'logs', 'settings'));
    }

    public function saveBikribook(Request $request)
    {
        $tenant = app('currentTenant');
        $settings = $tenant->settings ?? [];

        $validated = $request->validate([
            'api_key' => ['nullable', 'string'],
            'api_secret' => ['nullable', 'string'],
            'company_id' => ['nullable', 'string', 'max:255'],
            'base_url' => ['nullable', 'url'],
            'auto_sync' => ['sometimes', 'boolean'],
            'auto_create_customer' => ['sometimes', 'boolean'],
            'notify_payment' => ['sometimes', 'boolean'],
        ]);

        if ($request->filled('api_key')) {
            $tenant->setBikriBookApiKeyEncrypted($validated['api_key']);
        }

        if ($request->filled('api_secret')) {
            $tenant->setBikriBookApiSecretEncrypted($validated['api_secret']);
        }

        $tenant->bikribook_company_id = $validated['company_id'] ?: $tenant->bikribook_company_id;
        $tenant->bikribook_base_url = $validated['base_url'] ? rtrim($validated['base_url'], '/') : null;

        $settings = array_merge($settings, [
            'bikribook_auto_sync' => (int) $request->boolean('auto_sync', true),
            'bikribook_auto_create_customer' => (int) $request->boolean('auto_create_customer', true),
            'bikribook_notify_payment' => (int) $request->boolean('notify_payment', true),
        ]);

        $tenant->settings = $settings;
        $tenant->save();

        ActivityLog::record('settings.bikribook.updated');

        return back()->with('success', 'BikriBook settings saved. The API key is stored encrypted.');
    }

    public function testBikribook()
    {
        $tenant = app('currentTenant');

        if (! $tenant->bikribook_api_key) {
            return back()->with('error', 'Save an API key first, then test the connection.');
        }

        $ok = (new BikriBookService($tenant))->testConnection();

        return back()->with(
            $ok ? 'success' : 'error',
            $ok ? 'Connection OK ✅ - BikriBook is reachable.' : 'Connection failed ❌ - check the API key and base URL.'
        );
    }

    // ------------------------------------------------------------------
    // Notifications preferences
    // ------------------------------------------------------------------

    public function notifications()
    {
        $user = auth()->user();

        $types = [
            'task_assigned' => 'Task assigned',
            'task_overdue' => 'Task overdue',
            'task_comment' => 'New comment on task',
            'invoice_sent' => 'Invoice sent',
            'invoice_reminder' => 'Payment reminder',
            'invoice_paid' => 'Invoice paid',
            'invoice_overdue' => 'Invoice overdue',
            'meta_lead' => 'Meta lead received',
            'lead_won' => 'Lead won',
            'report_shared' => 'Report shared',
            'portal_request' => 'New portal request',
            'welcome' => 'Welcome email',
            'daily_digest' => 'Daily digest',
            'weekly_summary' => 'Weekly summary (admin)',
            'contract_expiring' => 'Contract expiring',
        ];

        $prefs = collect($types)->mapWithKeys(fn ($label, $key) => [
            $key => Setting::get('email_notify_'.$key, 1),
        ]);

        return view('settings.notifications', compact('types', 'prefs'));
    }

    public function saveNotifications(Request $request)
    {
        $validated = $request->validate([
            'prefs' => ['required', 'array'],
            'prefs.*' => ['boolean'],
        ]);

        foreach ($validated['prefs'] as $key => $enabled) {
            Setting::set('email_notify_'.$key, (int) $enabled);
        }

        return back()->with('success', 'Notification preferences saved.');
    }

    // ------------------------------------------------------------------
    // Audit log & subscription
    // ------------------------------------------------------------------

    public function auditLog(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($from = $request->input('from')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('created_at', '<=', $to);
        }

        $logs = $query->paginate(config('tenancy.pagination_size'))->withQueryString();
        $users = User::orderBy('name')->get();

        return view('settings.audit', compact('logs', 'users'));
    }

    public function subscription()
    {
        $tenant = app('currentTenant');
        $subscriptions = $tenant->subscriptions()->with('plan')->latest()->get();
        $payments = $tenant->subscriptionPayments()->latest()->get();
        $plans = \App\Models\Plan::query()->where('is_active', true)->get();

        $usage = [
            'users' => User::count(),
            'clients' => \App\Models\Client::count(),
            'storage_gb' => round(\App\Models\File::sum('file_size') / 1024 / 1024 / 1024, 2),
        ];

        return view('settings.subscription', compact('tenant', 'subscriptions', 'payments', 'plans', 'usage'));
    }
}
