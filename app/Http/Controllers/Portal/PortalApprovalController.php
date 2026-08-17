<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ClientApproval;
use Illuminate\Http\Request;

class PortalApprovalController extends Controller
{
    public function index()
    {
        $client = auth('portal')->user()->client;
        $portalUser = auth('portal')->user();

        $pending = $client->approvals()->where('status', 'pending')->latest()->get();
        $history = $client->approvals()->where('status', '!=', 'pending')->latest()->paginate(15);

        return view('portal.approvals', compact('pending', 'history', 'portalUser'));
    }

    public function respond(Request $request, ClientApproval $approval)
    {
        $client = auth('portal')->user()->client;

        if ($approval->client_id !== $client->id) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:approved,changes_requested'],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $approval->update([
            'status' => $validated['action'],
            'reviewed_by' => auth('portal')->id(),
            'review_notes' => $validated['review_notes'],
            'reviewed_at' => now(),
        ]);

        // If linked to a task, update the task status.
        if ($approval->task_id) {
            $task = \App\Models\Task::withoutGlobalScopes()->find($approval->task_id);

            if ($task) {
                $task->update([
                    'approval_status' => $validated['action'],
                    'approved_at' => $validated['action'] === 'approved' ? now() : null,
                    'status' => $validated['action'] === 'approved' ? 'done' : 'in_progress',
                ]);
            }
        }

        return back()->with('success', 'Deliverable '.($validated['action'] === 'approved' ? 'approved ✅' : 'marked for changes.').'');
    }
}
