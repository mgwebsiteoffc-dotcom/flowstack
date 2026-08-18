<?php

namespace App\Http\Controllers;

use App\Events\ClientCreated;
use App\Http\Requests\ClientRequest;
use App\Jobs\BikriBook\SyncInvoiceToBikriBook;
use App\Mail\PortalInviteMail;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientNote;
use App\Models\ClientOnboardingItem;
use App\Models\ClientPortalUser;
use App\Models\ClientService;
use App\Models\ClientTeamMember;
use App\Models\FileFolder;
use App\Models\ProjectTemplate;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ClientController extends Controller
{
 public function __construct()
 {
 $this->authorizeResource(Client::class, 'client');
 }

 /**
 * List with filters (status, account manager, health, service, search).
 */
 public function index(Request $request)
 {
 $user = auth()->user();

 $query = Client::with('accountManager', 'services', 'contacts');

 // Account managers only see their own clients.
 if ($user->isAccountManager()) {
 $query->where('account_manager_id', $user->id);
 }

 if ($status = $request->input('status')) {
 $query->where('status', $status);
 }

 if ($am = $request->input('account_manager_id')) {
 $query->where('account_manager_id', $am);
 }

 if ($health = $request->input('health_score')) {
 $query->where('health_score', $health);
 }

 if ($service = $request->input('service')) {
 $query->whereHas('services', fn ($q) => $q->where('service_type', $service));
 }

 if ($search = $request->input('search')) {
 $query->where('company_name', 'like', "%{$search}%");
 }

 $clients = $query->orderBy('company_name')->paginate(config('tenancy.pagination_size'))->withQueryString();
 $accountManagers = User::whereIn('role', ['admin', 'ops_manager', 'account_manager'])->orderBy('name')->get();

 return view('clients.index', compact('clients', 'accountManagers'));
 }

 public function create()
 {
 $accountManagers = User::whereIn('role', ['admin', 'ops_manager', 'account_manager'])->orderBy('name')->get();

 return view('clients.create', compact('accountManagers'));
 }

 /**
 * Create client + 20 default onboarding items + system folders + optional
 * BikriBook customer creation (queued).
 */
 public function store(ClientRequest $request)
 {
 $data = $request->validated();
 $tenant = \App\Support\CurrentTenant::get();

 if ($tenant->max_clients && Client::count() >= $tenant->max_clients) {
 return back()->with('error', 'You have reached your plan limit of '.$tenant->max_clients.' clients. Upgrade to add more.');
 }

 $data['tenant_id'] = $tenant->id;
 $data['status'] = $data['status'] ?? 'active';

 if ($request->hasFile('logo')) {
 $path = $request->file('logo')->store('tenants/'.$tenant->id.'/clients/logo', 'tenant');
 $data['logo'] = basename($path);
 }

 $client = DB::transaction(function () use ($data, $request, $tenant) {
 $client = Client::create($data);

 // Services
 foreach ($request->input('services', []) as $serviceType) {
 ClientService::create([
 'tenant_id' => $tenant->id,
 'client_id' => $client->id,
 'service_type' => $serviceType,
 'monthly_price' => $request->input('service_price_'.$serviceType) ?: null,
 ]);
 }

 // Primary contact
 if ($request->filled('contact_name')) {
 ClientContact::create([
 'tenant_id' => $tenant->id,
 'client_id' => $client->id,
 'name' => $request->input('contact_name'),
 'email' => $request->input('contact_email'),
 'phone' => $request->input('contact_phone'),
 'designation' => $request->input('contact_designation'),
 'is_primary' => true,
 'is_billing_contact' => true,
 ]);
 }

 // 20 default onboarding checklist items
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

 // System file folders (06_Shopify only when shopify service selected)
 $folders = ['01_Onboarding', '02_Strategy', '03_Campaigns', '04_Creatives', '05_Reports', '07_Meetings'];
 $services = collect($request->input('services', []));

 if ($services->contains('shopify_operations')) {
 $folders[] = '06_Shopify';
 }

 sort($folders);

 foreach ($folders as $folder) {
 FileFolder::create([
 'tenant_id' => $tenant->id,
 'client_id' => $client->id,
 'name' => $folder,
 'path' => 'clients/'.$client->id.'/'.$folder,
 'is_system_folder' => true,
 'created_by' => auth()->id(),
 ]);
 }

 return $client;
 });

 ActivityLog::record('client.created', $client, null, ['company_name' => $client->company_name]);

 ClientCreated::dispatch($client);

 // Auto-create BikriBook customer (queued, never blocks creation).
 if ($tenant->bikribook_api_key) {
 try {
 (new \App\Services\BikriBookService($tenant))->getOrCreateCustomer($client);
 } catch (\Throwable $e) {
 logger()->warning('BikriBook customer creation failed', ['client_id' => $client->id, 'error' => $e->getMessage()]);
 }
 }

 return redirect()->route('clients.show', $client)->with('success', 'Client created. Onboarding checklist and folders are ready.');
 }

 public function show(Client $client)
 {
 $tab = request()->input('tab', 'overview');

 $client->load([
 'accountManager',
 'contacts',
 'services',
 'notes.creator',
 'teamMembers.user',
 'onboardingItems.assignee',
 'projects',
 'reports',
 ]);

 $data = [
 'client' => $client,
 'tab' => $tab,
 'tasks' => $client->tasks()->with('assignee', 'project')->latest()->paginate(15),
 'invoices' => $client->invoices()->with('items')->latest()->paginate(15),
 'files' => $client->files()->with('folder')->latest()->paginate(15),
 'activity' => ActivityLog::where('model_type', Client::class)->where('model_id', $client->id)->with('user')->latest()->take(30)->get(),
 'projects' => $client->projects()->withCount('tasks')->get(),
 'teamMembers' => User::where('is_active', true)->orderBy('name')->get(),
 ];

 return view('clients.show', $data);
 }

 public function edit(Client $client)
 {
 $accountManagers = User::whereIn('role', ['admin', 'ops_manager', 'account_manager'])->orderBy('name')->get();

 return view('clients.edit', compact('client', 'accountManagers'));
 }

 public function update(ClientRequest $request, Client $client)
 {
 $data = $request->validated();
 $tenant = \App\Support\CurrentTenant::get();

 if ($request->hasFile('logo')) {
 $path = $request->file('logo')->store('tenants/'.$tenant->id.'/clients/logo', 'tenant');
 $data['logo'] = basename($path);
 }

 $old = $client->only(['company_name', 'status', 'account_manager_id']);
 $client->update($data);

 // Sync services
 $client->services()->delete();
 foreach ($request->input('services', []) as $serviceType) {
 ClientService::create([
 'tenant_id' => $tenant->id,
 'client_id' => $client->id,
 'service_type' => $serviceType,
 'monthly_price' => $request->input('service_price_'.$serviceType) ?: null,
 ]);
 }

 ActivityLog::record('client.updated', $client, $old, $client->only(['company_name', 'status', 'account_manager_id']));

 return redirect()->route('clients.show', $client)->with('success', 'Client updated.');
 }

 public function destroy(Client $client)
 {
 $client->delete();
 ActivityLog::record('client.deleted', $client);

 return redirect()->route('clients.index')->with('success', 'Client moved to trash.');
 }

 // ----------------------------------------------------------------
 // Tabs / sub-resources
 // ----------------------------------------------------------------

 public function storeNote(Request $request, Client $client)
 {
 $validated = $request->validate([
 'note' => ['required', 'string'],
 'note_type' => ['required', 'in:info,warning,important'],
 'is_pinned' => ['sometimes', 'boolean'],
 ]);

 ClientNote::create($validated + [
 'tenant_id' => $client->tenant_id,
 'client_id' => $client->id,
 'created_by' => auth()->id(),
 ]);

 return back()->with('success', 'Note added.');
 }

 public function updateNote(Request $request, Client $client, ClientNote $note)
 {
 $validated = $request->validate([
 'note' => ['required', 'string'],
 'note_type' => ['required', 'in:info,warning,important'],
 ]);

 $note->update($validated);

 return back()->with('success', 'Note updated.');
 }

 public function destroyNote(Client $client, ClientNote $note)
 {
 $note->delete();

 return back()->with('success', 'Note deleted.');
 }

 public function updateOnboarding(Request $request, Client $client, ClientOnboardingItem $item)
 {
 $validated = $request->validate([
 'assigned_to' => ['nullable', 'exists:users,id'],
 'due_date' => ['nullable', 'date'],
 ]);

 $item->update([
 'assigned_to' => $validated['assigned_to'] ?: null,
 'due_date' => $validated['due_date'] ?: null,
 ]);

 return back()->with('success', 'Onboarding item updated.');
 }

 public function toggleOnboarding(Request $request, Client $client, ClientOnboardingItem $item)
 {
 $item->update([
 'is_completed' => ! $item->is_completed,
 'completed_at' => $item->is_completed ? now() : null,
 ]);

 // First time everything is complete: mark onboarding done.
 if ($client->onboardingItems()->where('is_completed', false)->count() === 0 && ! $client->onboarding_completed_at) {
 $client->update(['onboarding_completed_at' => now(), 'status' => 'active']);
 }

 return back();
 }

 /**
 * Enable/disable the client portal and (re)send the portal invite.
 */
 public function togglePortalAccess(Request $request, Client $client)
 {
 $validated = $request->validate([
 'email' => ['nullable', 'email'],
 'name' => ['nullable', 'string', 'max:255'],
 'enabled' => ['required', 'boolean'],
 ]);

 if ($validated['enabled']) {
 $portalUser = ClientPortalUser::where('client_id', $client->id)->first();

 if (! $portalUser) {
 $portalUser = ClientPortalUser::create([
 'tenant_id' => $client->tenant_id,
 'client_id' => $client->id,
 'name' => $validated['name'] ?? $client->primaryContact?->name ?? $client->company_name,
 'email' => $validated['email'] ?? $client->primaryContact?->email ?? $client->contacts()->value('email'),
 'password' => bcrypt(Str::random(32)),
 ]);
 }

 $token = Str::random(64);
 Setting::setForTenant($client->tenant_id, 'portal_set_password_'.$portalUser->id, $token);

 Mail::to($portalUser->email, $portalUser->name)->queue(new PortalInviteMail(
 $client->company_name,
 $portalUser->email,
 route('portal.set-password', $token)
 ));

 $client->update(['portal_access_enabled' => true]);
 $message = 'Portal enabled. Invitation email sent to '.$portalUser->email.'.';
 } else {
 $client->update(['portal_access_enabled' => false]);
 $message = 'Portal access disabled.';
 }

 return back()->with('success', $message);
 }

 public function storeContact(Request $request, Client $client)
 {
 $validated = $request->validate([
 'name' => ['required', 'string', 'max:255'],
 'email' => ['nullable', 'email'],
 'phone' => ['nullable', 'string', 'max:30'],
 'designation' => ['nullable', 'string', 'max:255'],
 'is_primary' => ['sometimes', 'boolean'],
 'is_billing_contact' => ['sometimes', 'boolean'],
 ]);

 if (! empty($validated['is_primary'])) {
 $client->contacts()->update(['is_primary' => false]);
 }

 ClientContact::create($validated + [
 'tenant_id' => $client->tenant_id,
 'client_id' => $client->id,
 ]);

 return back()->with('success', 'Contact added.');
 }

 public function destroyContact(Client $client, ClientContact $contact)
 {
 $contact->delete();

 return back()->with('success', 'Contact removed.');
 }
}
