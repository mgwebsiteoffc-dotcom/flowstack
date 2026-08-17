<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PortalReportController extends Controller
{
 public function index()
 {
 $client = auth('portal')->user()->client;

 $reports = $client->reports()
 ->where('shared_with_client', true)
 ->latest('shared_at')
 ->paginate(15);

 return view('portal.reports', compact('reports'));
 }

 public function show(Report $report)
 {
 $client = auth('portal')->user()->client;

 if ($report->client_id !== $client->id || ! $report->shared_with_client) {
 abort(403);
 }

 $report->load('client');

 return view('portal.reports-show', compact('report'));
 }

 public function download(Report $report)
 {
 $client = auth('portal')->user()->client;

 if ($report->client_id !== $client->id || ! $report->shared_with_client) {
 abort(403);
 }

 $report->update(['client_viewed_at' => now()]);

 try {
 $pdf = Pdf::loadView('reports.pdf', [
 'report' => $report->load('client', 'tenant'),
 'tenant' => $report->tenant,
 ]);

 return $pdf->download('report-'.Str::slug($report->title).'.pdf');
 } catch (\Throwable $e) {
 logger()->error('Portal report PDF failed', ['report' => $report->id, 'error' => $e->getMessage()]);

 return back()->with('error', 'Could not generate the PDF right now. Please try again.');
 }
 }

 public function comment(Request $request, Report $report)
 {
 $client = auth('portal')->user()->client;

 if ($report->client_id !== $client->id || ! $report->shared_with_client) {
 abort(403);
 }

 $validated = $request->validate(['comment' => ['required', 'string', 'max:2000']]);

 // Store as a lightweight client comment via activity log.
 \App\Models\ActivityLog::withoutGlobalScopes()->create([
 'tenant_id' => $report->tenant_id,
 'user_id' => null,
 'action' => 'portal_comment',
 'model_type' => Report::class,
 'model_id' => $report->id,
 'new_values' => ['comment' => $validated['comment'], 'from' => $client->company_name],
 'created_at' => now(),
 ]);

 return back()->with('success', 'Comment submitted. Your agency will see it.');
 }
}
