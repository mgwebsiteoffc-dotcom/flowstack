<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PortalRequestController extends Controller
{
 public function index()
 {
 $client = auth('portal')->user()->client;

 $requests = $client->requests()->latest()->paginate(15);

 return view('portal.requests', compact('requests'));
 }

 public function create()
 {
 return view('portal.requests-create');
 }

 public function store(Request $request)
 {
 $client = auth('portal')->user()->client;
 $portalUser = auth('portal')->user();

 $validated = $request->validate([
 'request_type' => ['required', 'in:'.implode(',', ClientRequest::TYPES)],
 'title' => ['required', 'string', 'max:255'],
 'description' => ['required', 'string', 'max:5000'],
 'priority' => ['required', 'in:normal,urgent'],
 'attachments' => ['nullable', 'array', 'max:5'],
 'attachments.*' => ['file', 'max:10240'],
 ]);

 $attachmentPaths = [];

 foreach ($request->file('attachments', []) as $file) {
 $attachmentPaths[] = $file->store('tenants/'.$client->tenant_id.'/portal-requests/'.$client->id, 'tenant');
 }

 $clientRequest = ClientRequest::create([
 'tenant_id' => $client->tenant_id,
 'client_id' => $client->id,
 'submitted_by' => $portalUser->id,
 'request_type' => $validated['request_type'],
 'title' => $validated['title'],
 'description' => $validated['description'],
 'priority' => $validated['priority'],
 'status' => 'open',
 'attachments' => $attachmentPaths ?: null,
 ]);

 // Create an internal task so the request is actionable.
 $admin = User::withoutGlobalScopes()
 ->where('tenant_id', $client->tenant_id)
 ->where('role', 'admin')
 ->orderBy('id')
 ->first();

 $task = Task::withoutGlobalScopes()->create([
 'tenant_id' => $client->tenant_id,
 'client_id' => $client->id,
 'title' => '[Portal] '.$validated['title'],
 'description' => $validated['description']."\n\nSubmitted via client portal.",
 'status' => 'todo',
 'priority' => $validated['priority'] === 'urgent' ? 'urgent' : 'medium',
 'service_type' => match ($validated['request_type']) {
 'content' => 'social_media',
 'design' => 'digital_marketing',
 'technical' => 'website_management',
 'report' => 'internal',
 default => 'internal',
 },
 'task_type' => 'client_request',
 'assigned_to' => $admin?->id,
 'created_by' => $admin?->id ?? 1,
 'due_date' => $validated['priority'] === 'urgent' ? now()->addDay()->toDateString() : now()->addDays(3)->toDateString(),
 ]);

 $clientRequest->update(['task_id' => $task->id]);

 // Notify the team (in-app + email).
 $tenant = \App\Models\Tenant::find($client->tenant_id);
 if ($tenant) {
 $notifications = app(NotificationService::class);
 $notifications->notifyRole(
 $tenant,
 ['admin', 'ops_manager'],
 'inbox New client request',
 $client->company_name.' - '.$validated['title'],
 'tasks.show',
 ['task' => $task->id]
 );
 $notifications->emailRole(
 $tenant,
 ['admin', 'ops_manager'],
 'inbox New client portal request: '.$validated['title'],
 $client->company_name.' submitted a '.$validated['priority'].' '.$validated['request_type'].' request: '.$validated['title'],
 'portal_request'
 );
 }

 return redirect()->route('portal.requests')->with('success', 'Request submitted! We will get back to you shortly.');
 }
}
