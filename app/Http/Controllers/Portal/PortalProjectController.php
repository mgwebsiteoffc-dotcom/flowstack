<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class PortalProjectController extends Controller
{
    public function index()
    {
        $client = auth('portal')->user()->client;

        $projects = $client->projects()
            ->withCount(['tasks', 'tasks as done_tasks' => fn ($q) => $q->where('status', 'done')])
            ->where('status', '!=', 'cancelled')
            ->orderBy('status')
            ->get();

        return view('portal.projects', compact('projects'));
    }

    public function show(Project $project)
    {
        $client = auth('portal')->user()->client;

        if ($project->client_id !== $client->id) {
            abort(403, 'You do not have access to this project.');
        }

        $project->load(['tasks.assignee', 'members.user']);

        return view('portal.projects-show', compact('project'));
    }
}
