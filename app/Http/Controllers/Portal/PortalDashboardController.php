<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PortalDashboardController extends Controller
{
 public function index()
 {
 $client = auth('portal')->user()->client;

 $client->load(['projects.tasks', 'reports', 'requests', 'approvals']);

 $stats = [
 'active_projects' => $client->projects->where('status', 'active')->count(),
 'pending_approvals' => $client->approvals->where('status', 'pending')->count(),
 'open_requests' => $client->requests->whereIn('status', ['open', 'in_progress'])->count(),
 'recent_reports' => $client->reports->where('shared_with_client', true)->count(),
 ];

 $projects = $client->projects->where('status', '!=', 'cancelled')->take(5);
 $reports = $client->reports->where('shared_with_client', true)->sortByDesc('shared_at')->take(5);
 $requests = $client->requests->sortByDesc('created_at')->take(5);

 return view('portal.dashboard', compact('client', 'stats', 'projects', 'reports', 'requests'));
 }
}
