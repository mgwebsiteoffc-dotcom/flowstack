<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeadRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientOnboardingItem;
use App\Models\FileFolder;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadPipelineStage;
use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\Task;
use App\Models\User;
use App\Services\AutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LeadController extends Controller
{
 public function __construct()
 {
 $this->authorizeResource(Lead::class, 'lead');
 }

 public function index(Request $request)
 {
 $query = Lead::with('assignee', 'stage', 'convertedClient');

 $this->scopeLeadsForUser($query);

 if ($status = $request->input('status')) {
 $query->where('status', $status);
 }

 if ($source = $request->input('source_type')) {
 $query->where('source_type', $source);
 }

 if ($assigneeId = $request->input('assigned_to')) {
 $query->where('assigned_to', $assigneeId);
 }

 if ($search = $request->input('search')) {
 $query->where(function ($q) use ($search) {
 $q->where('contact_name', 'like', "%{$search}%")
 ->orWhere('company_name', 'like', "%{$search}%")
 ->orWhere('email', 'like', "%{$search}%");
 });
 }

 $leads = $query->orderBy('created_at', 'desc')->paginate(config('tenancy.pagination_size'))->withQueryString();
 $users = User::where('is_active', true)->orderBy('name')->get();

 return view('leads.index', compact('leads', 'users'));
 }

 public function pipeline()
 {
 $stages = LeadPipelineStage::with(['leads' => function ($q) {
 $q->with('assignee')->where('status', 'active')->orderBy('updated_at', 'desc');
 }])->orderBy('order_index')->get();

 $users = User::where('is_active', true)->orderBy('name')->get();

 return view('leads.pipeline', compact('stages', 'users'));
 }

 public function analytics()
 {
 $totalLeads = Lead::count();
 $wonLeads = Lead::where('status', 'won')->count();
 $lostLeads = Lead::where('status', 'lost')->count();
 $winRate = $totalLeads > 0 ? round($wonLeads * 100 / $totalLeads, 1) : 0;
 $avgDealValue = Lead::where('status', 'won')->avg('won_value') ?? 0;
 $pipelineValue = Lead::where('status', 'active')->sum('estimated_value');
 $monthlyWonValue = Lead::where('status', 'won')->where('won_at', '>=', now()->startOfYear())
 ->get()
 ->groupBy(fn ($l) => $l->won_at->format('M'))
 ->map(fn ($g) => (float) $g->sum('won_value'));

 $bySource = Lead::select('source_type', DB::raw('count(*) as total'), DB::raw('SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as won'))
 ->groupBy('source_type')->get();

 $byStage = LeadPipelineStage::withCount(['leads' => fn ($q) => $q->where('status', 'active')])
 ->orderBy('order_index')->get();

 $overTime = Lead::where('created_at', '>=', now()->subMonths(6))
 ->get()
 ->groupBy(fn ($l) => $l->created_at->format('M Y'))
 ->map(fn ($g) => $g->count());

 $leads = Lead::with('assignee')->latest()->paginate(10);

 return view('leads.analytics', compact(
 'totalLeads', 'wonLeads', 'lostLeads', 'winRate', 'avgDealValue',
 'pipelineValue', 'monthlyWonValue', 'bySource', 'byStage', 'overTime', 'leads'
 ));
 }

 public function create()
 {
 $stages = LeadPipelineStage::orderBy('order_index')->get();
 $users = User::where('is_active', true)->orderBy('name')->get();

 return view('leads.create', compact('stages', 'users'));
 }

 public function store(LeadRequest $request)
 {
 $data = $request->validated();
 $data['tenant_id'] = app('currentTenant')->id;
 $data['last_activity_at'] = now();

 if (empty($data['status'])) {
 $data['status'] = 'active';
 }

 if ($data['stage_id'] ?? null) {
 $stage = LeadPipelineStage::find($data['stage_id']);
 $data['current_stage'] = $stage?->name;
 }

 $lead = Lead::create($data);

 LeadActivity::create([
 'tenant_id' => $lead->tenant_id,
 'lead_id' => $lead->id,
 'activity_type' => 'note',
 'title' => 'Lead created manually',
 'performed_by' => auth()->id(),
 'source' => 'manual',
 ]);

 ActivityLog::record('lead.created', $lead, null, ['contact_name' => $lead->contact_name]);
 app(AutomationService::class)->processEvent('lead.created', $lead, app('currentTenant'));

 return redirect()->route('leads.show', $lead)->with('success', 'Lead created.');
 }

 public function show(Lead $lead)
 {
 $lead->load(['assignee', 'stage', 'activities.performer', 'convertedClient', 'tasks', 'files']);

 $stages = LeadPipelineStage::orderBy('order_index')->get();
 $users = User::where('is_active', true)->orderBy('name')->get();

 return view('leads.show', compact('lead', 'stages', 'users'));
 }

 public function edit(Lead $lead)
 {
 $stages = LeadPipelineStage::orderBy('order_index')->get();
 $users = User::where('is_active', true)->orderBy('name')->get();

 return view('leads.edit', compact('lead', 'stages', 'users'));
 }

 public function update(LeadRequest $request, Lead $lead)
 {
 $data = $request->validated();

 if ($data['stage_id'] ?? null) {
 $stage = LeadPipelineStage::find($data['stage_id']);
 $data['current_stage'] = $stage?->name;
 }

 $old = $lead->only(['status', 'current_stage', 'assigned_to']);
 $lead->update($data);
 $lead->update(['last_activity_at' => now()]);

 ActivityLog::record('lead.updated', $lead, $old, $lead->only(['status', 'current_stage', 'assigned_to']));

 return redirect()->route('leads.show', $lead)->with('success', 'Lead updated.');
 }

 public function destroy(Lead $lead)
 {
 $lead->delete();
 ActivityLog::record('lead.deleted', $lead);

 return redirect()->route('leads.index')->with('success', 'Lead deleted.');
 }

 public function markWon(Request $request, Lead $lead)
 {
 $validated = $request->validate(['won_value' => ['nullable', 'numeric', 'min:0']]);

 $wonStage = LeadPipelineStage::where('is_won_stage', true)->first();

 $lead->update([
 'status' => 'won',
 'won_value' => $validated['won_value'] ?? $lead->estimated_value,
 'won_at' => now(),
 'current_stage' => $wonStage?->name,
 'stage_id' => $wonStage?->id,
 ]);

 LeadActivity::create([
 'tenant_id' => $lead->tenant_id,
 'lead_id' => $lead->id,
 'activity_type' => 'stage_change',
 'title' => 'Lead marked as Won',
 'new_value' => 'Won',
 'performed_by' => auth()->id(),
 ]);

 app(AutomationService::class)->processEvent('lead.won', $lead, app('currentTenant'));

 return back()->with('success', 'Lead marked as won. You can now convert it to a client.');
 }

 public function markLost(Request $request, Lead $lead)
 {
 $validated = $request->validate(['lost_reason' => ['nullable', 'string']]);

 $lostStage = LeadPipelineStage::where('is_lost_stage', true)->first();

 $lead->update([
 'status' => 'lost',
 'lost_reason' => $validated['lost_reason'],
 'lost_at' => now(),
 'current_stage' => $lostStage?->name,
 'stage_id' => $lostStage?->id,
 ]);

 LeadActivity::create([
 'tenant_id' => $lead->tenant_id,
 'lead_id' => $lead->id,
 'activity_type' => 'stage_change',
 'title' => 'Lead marked as Lost',
 'description' => $validated['lost_reason'],
 'new_value' => 'Lost',
 'performed_by' => auth()->id(),
 ]);

 app(AutomationService::class)->processEvent('lead.lost', $lead, app('currentTenant'));

 return back()->with('success', 'Lead marked as lost.');
 }

 /**
 * Won lead -> client: creates the client, links the lead, seeds onboarding
 * items + system folders + an onboarding project from a template.
 */
 public function convert(Request $request, Lead $lead)
 {
 $this->authorize('convert', $lead);

 $validated = $request->validate([
 'company_name' => ['required', 'string', 'max:255'],
 'monthly_retainer' => ['nullable', 'numeric', 'min:0'],
 'account_manager_id' => ['nullable', 'exists:users,id'],
 'service_type' => ['nullable', 'in:'.implode(',', array_keys(\App\Models\ClientService::TYPES))],
 ]);

 if ($lead->status !== 'won') {
 return back()->with('error', 'Only won leads can be converted to clients.');
 }

 if ($lead->converted_to_client_id) {
 return redirect()->route('clients.show', $lead->converted_to_client_id)->with('info', 'This lead was already converted.');
 }

 $tenant = app('currentTenant');

 $client = DB::transaction(function () use ($lead, $validated, $tenant) {
 $client = Client::create([
 'tenant_id' => $tenant->id,
 'company_name' => $validated['company_name'] ?: $lead->company_name ?: $lead->contact_name,
 'website' => $lead->custom_fields['website'] ?? null,
 'status' => 'onboarding',
 'health_score' => 'green',
 'monthly_retainer' => $validated['monthly_retainer'] ?? null,
 'account_manager_id' => $validated['account_manager_id'] ?? $lead->assigned_to,
 'lead_id' => $lead->id,
 'notes' => 'Converted from won lead ('.$lead->sourceLabel().')',
 ]);

 if ($validated['service_type'] ?? null) {
 \App\Models\ClientService::create([
 'tenant_id' => $tenant->id,
 'client_id' => $client->id,
 'service_type' => $validated['service_type'],
 ]);
 }

 if ($lead->contact_name) {
 \App\Models\ClientContact::create([
 'tenant_id' => $tenant->id,
 'client_id' => $client->id,
 'name' => $lead->contact_name,
 'email' => $lead->email,
 'phone' => $lead->phone,
 'is_primary' => true,
 'is_billing_contact' => true,
 ]);
 }

 // Onboarding checklist + folders.
 $items = [
 'Send welcome email', 'Collect brand assets', 'Get Meta access', 'Get Google Ads access',
 'Get GA4 access', 'Get Shopify access', 'Get website/CMS access', 'Get social media access',
 'Get email marketing access', 'Review existing campaigns', 'Review analytics', 'Competitor research',
 'Schedule kickoff call', 'Conduct kickoff call', 'Document goals and KPIs', 'Create 30-60-90 day plan',
 'Assign team members', 'Set recurring tasks', 'Set up reporting', 'Send communication guidelines',
 ];

 foreach ($items as $index => $title) {
 ClientOnboardingItem::create([
 'tenant_id' => $tenant->id,
 'client_id' => $client->id,
 'title' => $title,
 'order_index' => $index,
 ]);
 }

 foreach (['01_Onboarding', '02_Strategy', '03_Campaigns', '04_Creatives', '05_Reports', '06_Shopify', '07_Meetings'] as $folder) {
 FileFolder::create([
 'tenant_id' => $tenant->id,
 'client_id' => $client->id,
 'name' => $folder,
 'path' => 'clients/'.$client->id.'/'.$folder,
 'is_system_folder' => true,
 'created_by' => auth()->id(),
 ]);
 }

 // Onboarding project from the matching service template.
 $template = ProjectTemplate::where('service_type', $validated['service_type'] ?? null)->first()
 ?? ProjectTemplate::first();

 if ($template) {
 $project = Project::create([
 'tenant_id' => $tenant->id,
 'client_id' => $client->id,
 'name' => 'Onboarding - '.$client->company_name,
 'description' => 'Auto-created from won lead '.$lead->contact_name,
 'status' => 'active',
 'service_type' => $template->service_type,
 'template_id' => $template->id,
 'created_by' => auth()->id(),
 ]);

 foreach ($template->templateTasks as $index => $templateTask) {
 Task::create([
 'tenant_id' => $tenant->id,
 'client_id' => $client->id,
 'project_id' => $project->id,
 'title' => $templateTask->title,
 'description' => $templateTask->description,
 'status' => 'todo',
 'priority' => $templateTask->default_priority,
 'service_type' => $template->service_type,
 'task_type' => $templateTask->task_type,
 'estimated_hours' => $templateTask->estimated_hours,
 'created_by' => auth()->id(),
 'order_index' => $templateTask->order_index,
 ]);
 }
 }

 return $client;
 });

 $lead->update(['converted_to_client_id' => $client->id]);

 ActivityLog::record('lead.converted', $lead, null, ['client_id' => $client->id]);

 return redirect()->route('clients.show', $client)->with('success', 'Lead converted to client! Onboarding is ready.');
 }

 public function storeActivity(Request $request, Lead $lead)
 {
 $validated = $request->validate([
 'activity_type' => ['required', 'in:'.implode(',', LeadActivity::TYPES)],
 'title' => ['required', 'string', 'max:255'],
 'description' => ['nullable', 'string'],
 'scheduled_at' => ['nullable', 'date'],
 ]);

 LeadActivity::create($validated + [
 'tenant_id' => $lead->tenant_id,
 'lead_id' => $lead->id,
 'performed_by' => auth()->id(),
 'source' => 'manual',
 ]);

 $lead->update(['last_activity_at' => now()]);

 return back()->with('success', 'Activity logged.');
 }

 public function updateStage(Request $request, Lead $lead)
 {
 $validated = $request->validate(['stage_id' => ['required', 'exists:lead_pipeline_stages,id']]);

 $stage = LeadPipelineStage::find($validated['stage_id']);

 $old = $lead->current_stage;
 $lead->update([
 'stage_id' => $stage->id,
 'current_stage' => $stage->name,
 'last_activity_at' => now(),
 ]);

 if ($stage->is_won_stage) {
 $lead->update(['status' => 'won', 'won_at' => now(), 'won_value' => $lead->won_value ?? $lead->estimated_value]);
 } elseif ($stage->is_lost_stage) {
 $lead->update(['status' => 'lost', 'lost_at' => now()]);
 } else {
 $lead->update(['status' => 'active']);
 }

 LeadActivity::create([
 'tenant_id' => $lead->tenant_id,
 'lead_id' => $lead->id,
 'activity_type' => 'stage_change',
 'title' => 'Stage changed to '.$stage->name,
 'old_value' => $old,
 'new_value' => $stage->name,
 'performed_by' => auth()->id(),
 ]);

 app(AutomationService::class)->processEvent('lead.stage_changed', $lead, app('currentTenant'));

 if ($request->expectsJson()) {
 return response()->json(['ok' => true, 'stage' => $stage->name]);
 }

 return back()->with('success', 'Stage updated.');
 }

 /**
 * Excel export of leads (maatwebsite/excel).
 */
 public function export()
 {
 $query = Lead::with('assignee', 'stage');
 $this->scopeLeadsForUser($query);

 $leads = $query->get();

 $rows = $leads->map(fn ($lead) => [
 'Contact' => $lead->contact_name,
 'Company' => $lead->company_name,
 'Email' => $lead->email,
 'Phone' => $lead->phone,
 'Source' => $lead->sourceLabel(),
 'Stage' => $lead->current_stage,
 'Status' => ucfirst($lead->status),
 'Value' => $lead->estimated_value,
 'Assignee' => $lead->assignee?->name,
 'Created' => $lead->created_at?->toDateTimeString(),
 ])->toArray();

 return Excel::download(new \App\Exports\ArrayExport($rows, ['Contact', 'Company', 'Email', 'Phone', 'Source', 'Stage', 'Status', 'Value', 'Assignee', 'Created']), 'leads-'.now()->format('Y-m-d').'.xlsx');
 }

 protected function scopeLeadsForUser($query): void
 {
 if (auth()->user()->isSpecialist()) {
 $query->where('assigned_to', auth()->id());
 }
 }
}
