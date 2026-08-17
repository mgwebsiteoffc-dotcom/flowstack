<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Mail\AgencyMail;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientPortalUser;
use App\Models\Report;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReportController extends Controller
{
 public function __construct()
 {
 $this->authorizeResource(Report::class, 'report');
 }

 public function index(Request $request)
 {
 $user = auth()->user();

 $query = Report::with('client', 'creator');

 if ($user->isAccountManager()) {
 $query->whereHas('client', fn ($q) => $q->where('account_manager_id', $user->id));
 }

 if ($clientId = $request->input('client_id')) {
 $query->where('client_id', $clientId);
 }

 if ($status = $request->input('status')) {
 $query->where('status', $status);
 }

 $reports = $query->latest()->paginate(config('tenancy.pagination_size'))->withQueryString();
 $clients = $this->visibleClients();

 return view('reports.index', compact('reports', 'clients'));
 }

 public function create()
 {
 $clients = $this->visibleClients();

 return view('reports.create', compact('clients'));
 }

 /**
 * Multi-step builder; the view posts the whole form (step fields included).
 * Sections are derived from the client's services.
 */
 public function store(ReportRequest $request)
 {
 $validated = $request->validated();

 $data = [
 'tenant_id' => app('currentTenant')->id,
 'client_id' => $validated['client_id'],
 'title' => $validated['title'],
 'report_type' => $validated['report_type'],
 'period_start' => $validated['period_start'],
 'period_end' => $validated['period_end'],
 'status' => $validated['status'] ?? 'draft',
 'data' => $this->collectMetrics($request),
 'insights' => $request->input('insights'),
 'recommendations' => $request->input('recommendations'),
 'next_priorities' => $request->input('next_priorities'),
 'created_by' => auth()->id(),
 ];

 $report = Report::create($data);

 ActivityLog::record('report.created', $report, null, ['title' => $report->title]);

 return redirect()->route('reports.show', $report)->with('success', 'Report saved.');
 }

 public function show(Report $report)
 {
 $report->load('client', 'creator');

 return view('reports.show', compact('report'));
 }

 public function edit(Report $report)
 {
 $clients = $this->visibleClients();

 return view('reports.edit', compact('report', 'clients'));
 }

 public function update(ReportRequest $request, Report $report)
 {
 $validated = $request->validated();

 $data = [
 'client_id' => $validated['client_id'],
 'title' => $validated['title'],
 'report_type' => $validated['report_type'],
 'period_start' => $validated['period_start'],
 'period_end' => $validated['period_end'],
 'status' => $validated['status'] ?? $report->status,
 'data' => $this->collectMetrics($request),
 'insights' => $request->input('insights'),
 'recommendations' => $request->input('recommendations'),
 'next_priorities' => $request->input('next_priorities'),
 ];

 // Allow direct JSON metric editing from the edit screen.
 if ($request->filled('data_json')) {
 try {
 $decoded = json_decode($request->input('data_json'), true, 512, JSON_THROW_ON_ERROR);
 $data['data'] = is_array($decoded) ? $decoded : $data['data'];
 } catch (\JsonException) {
 return back()->with('error', 'The metrics JSON is invalid.');
 }
 }

 $report->update($data);

 return redirect()->route('reports.show', $report)->with('success', 'Report updated.');
 }

 public function destroy(Report $report)
 {
 $report->delete();

 return redirect()->route('reports.index')->with('success', 'Report deleted.');
 }

 /**
 * DomPDF: agency logo + client logo, period header, metric tables
 * (no JS charts in PDF), commentary sections, footer.
 */
 public function pdf(Report $report)
 {
 $this->authorize('view', $report);

 $report->load('client', 'tenant');

 try {
 $pdf = Pdf::loadView('reports.pdf', [
 'report' => $report,
 'tenant' => $report->tenant ?? app('currentTenant'),
 ]);

 return $pdf->download('report-'.Str::slug($report->title).'.pdf');
 } catch (\Throwable $e) {
 logger()->error('Report PDF generation failed', ['report' => $report->id, 'error' => $e->getMessage()]);

 return back()->with('error', 'Could not generate the report PDF right now. Please try again.');
 }
 }

 /**
 * Share with client: marks shared, ensures portal access exists,
 * emails the client contact.
 */
 public function share(Request $request, Report $report)
 {
 $this->authorize('share', $report);

 $client = $report->client;

 $report->update([
 'status' => 'shared',
 'shared_with_client' => true,
 'shared_at' => now(),
 ]);

 // Ensure a portal account exists.
 $portalUser = ClientPortalUser::where('client_id', $client->id)->first();

 if (! $portalUser && $client->portal_access_enabled) {
 $portalUser = ClientPortalUser::create([
 'tenant_id' => $client->tenant_id,
 'client_id' => $client->id,
 'name' => $client->primaryContact?->name ?? $client->company_name,
 'email' => $client->primaryContact?->email ?? $client->contacts()->value('email'),
 'password' => bcrypt(Str::random(32)),
 ]);
 }

 $email = $portalUser?->email ?? $client->billingContact?->email ?? $client->contacts()->value('email');

 if ($email) {
 Mail::to($email)->queue(new AgencyMail(
 'Your '.ucfirst($report->report_type).' report is ready - '.$report->title,
 'Hi, your latest report is ready to view in the client portal.',
 [
 'report_title' => $report->title,
 'portal_url' => route('portal.login'),
 ]
 ));
 }

 ActivityLog::record('report.shared', $report, null, ['client_id' => $client->id]);

 return back()->with('success', 'Report shared with client'.($email ? ' ('.$email.')' : '').'.');
 }

 /**
 * @return array<string, mixed>
 */
 protected function collectMetrics(Request $request): array
 {
 $client = Client::find($request->input('client_id'));
 $services = $client?->services->pluck('service_type')->toArray() ?? [];

 $data = ['sections' => []];

 $hasAny = fn (string $prefix) => collect($request->all())->keys()->contains(
 fn (string $key) => str_starts_with($key, $prefix.'_')
 );

 if (in_array('digital_marketing', $services, true) || $hasAny('pa')) {
 $data['sections']['paid_advertising'] = $this->sectionMetrics($request, 'pa', [
 'ad_spend', 'impressions', 'clicks', 'conversions', 'revenue',
 ]);
 // Auto-calculations
 $pa = &$data['sections']['paid_advertising'];
 $pa['ctr'] = ($pa['impressions'] ?? 0) > 0 ? round($pa['clicks'] * 100 / $pa['impressions'], 2) : 0;
 $pa['roas'] = ($pa['ad_spend'] ?? 0) > 0 ? round($pa['revenue'] / $pa['ad_spend'], 2) : 0;
 $pa['cpl'] = ($pa['conversions'] ?? 0) > 0 ? round($pa['ad_spend'] / $pa['conversions'], 2) : 0;
 unset($pa);
 }

 if (in_array('shopify_operations', $services, true) || $hasAny('sh')) {
 $data['sections']['shopify'] = $this->sectionMetrics($request, 'sh', [
 'orders', 'revenue', 'visitors', 'cart_abandonment_rate',
 ]);
 $sh = &$data['sections']['shopify'];
 $sh['aov'] = ($sh['orders'] ?? 0) > 0 ? round($sh['revenue'] / $sh['orders'], 2) : 0;
 $sh['conversion_rate'] = ($sh['visitors'] ?? 0) > 0 ? round($sh['orders'] * 100 / $sh['visitors'], 2) : 0;
 unset($sh);
 }

 if (in_array('social_media', $services, true) || $hasAny('sm')) {
 $data['sections']['social_media'] = $this->sectionMetrics($request, 'sm', [
 'followers_start', 'followers_end', 'posts', 'reach', 'engagements',
 ]);
 $sm = &$data['sections']['social_media'];
 $sm['growth'] = ($sm['followers_start'] ?? 0) > 0 ? round(($sm['followers_end'] - $sm['followers_start']) * 100 / $sm['followers_start'], 2) : 0;
 $sm['engagement_rate'] = ($sm['reach'] ?? 0) > 0 ? round($sm['engagements'] * 100 / $sm['reach'], 2) : 0;
 unset($sm);
 }

 if (in_array('website_management', $services, true) || $hasAny('ws')) {
 $data['sections']['website'] = $this->sectionMetrics($request, 'ws', [
 'sessions', 'users', 'bounce_rate', 'avg_session_duration', 'goal_completions',
 ]);
 }

 return $data;
 }

 /**
 * @param array<int, string> $fields
 * @return array<string, mixed>
 */
 protected function sectionMetrics(Request $request, string $prefix, array $fields): array
 {
 $metrics = [];

 foreach ($fields as $field) {
 $metrics[$field] = $request->filled($prefix.'_'.$field)
 ? (float) $request->input($prefix.'_'.$field)
 : 0;
 }

 return $metrics;
 }

 /**
 * JSON endpoint for the report builder (client services).
 */
 public function clientServices(Request $request)
 {
 $client = Client::find($request->integer('client_id'));

 return response()->json([
 'services' => $client?->services->pluck('service_type') ?? [],
 ]);
 }

 protected function visibleClients()
 {
 $query = Client::query();

 if (auth()->user()->isAccountManager()) {
 $query->where('account_manager_id', auth()->id());
 }

 return $query->orderBy('company_name')->get();
 }
}
