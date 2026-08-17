<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectTemplate;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
 public function __construct()
 {
 $this->authorizeResource(Project::class, 'project');
 }

 public function index(Request $request)
 {
 $user = auth()->user();

 $query = Project::with('client', 'members.user')->withCount('tasks');

 if ($user->isAccountManager()) {
 $query->whereHas('client', fn ($q) => $q->where('account_manager_id', $user->id));
 } elseif ($user->isSpecialist()) {
 $query->whereHas('members', fn ($q) => $q->where('user_id', $user->id));
 }

 if ($clientId = $request->input('client_id')) {
 $query->where('client_id', $clientId);
 }

 if ($status = $request->input('status')) {
 $query->where('status', $status);
 }

 if ($service = $request->input('service_type')) {
 $query->where('service_type', $service);
 }

 $projects = $query->orderBy('updated_at', 'desc')->paginate(config('tenancy.pagination_size'))->withQueryString();
 $clients = $this->visibleClients($user);

 return view('projects.index', compact('projects', 'clients'));
 }

 public function create()
 {
 $user = auth()->user();
 $clients = $this->visibleClients($user);
 $templates = ProjectTemplate::orderBy('name')->get();
 $teamMembers = User::where('is_active', true)->orderBy('name')->get();

 return view('projects.create', compact('clients', 'templates', 'teamMembers'));
 }

 public function store(ProjectRequest $request)
 {
 $data = $request->validated();
 $data['tenant_id'] = app('currentTenant')->id;
 $data['created_by'] = auth()->id();

 $project = DB::transaction(function () use ($data, $request) {
 $project = Project::create($data);

 if ($request->filled('member_ids')) {
 foreach ($request->input('member_ids') as $index => $userId) {
 $role = ($request->input('member_roles')[$index] ?? 'member') === 'lead' ? 'lead' : 'member';
 ProjectMember::create([
 'tenant_id' => $project->tenant_id,
 'project_id' => $project->id,
 'user_id' => $userId,
 'role' => $role,
 ]);
 }
 }

 // Seed tasks from a chosen template.
 if ($request->filled('template_id')) {
 $template = ProjectTemplate::findOrFail($request->input('template_id'));

 foreach ($template->templateTasks as $index => $templateTask) {
 Task::create([
 'tenant_id' => $project->tenant_id,
 'client_id' => $project->client_id,
 'project_id' => $project->id,
 'title' => $templateTask->title,
 'description' => $templateTask->description,
 'status' => 'todo',
 'priority' => $templateTask->default_priority,
 'service_type' => $template->service_type,
 'task_type' => $templateTask->task_type,
 'recurrence_type' => $templateTask->recurrence_type,
 'estimated_hours' => $templateTask->estimated_hours,
 'created_by' => auth()->id(),
 'order_index' => $templateTask->order_index,
 ]);
 }
 }

 return $project;
 });

 ActivityLog::record('project.created', $project, null, ['name' => $project->name]);

 return redirect()->route('projects.show', $project)->with('success', 'Project created.');
 }

 public function show(Project $project)
 {
 $project->load([
 'client',
 'members.user',
 'template',
 'tasks.assignee',
 'tasks.subtasks',
 'files',
 ]);

 $tasksByStatus = $project->tasks->groupBy('status');
 $teamMembers = User::where('is_active', true)->orderBy('name')->get();

 return view('projects.show', compact('project', 'tasksByStatus', 'teamMembers'));
 }

 public function edit(Project $project)
 {
 $clients = $this->visibleClients(auth()->user());

 return view('projects.edit', compact('project', 'clients'));
 }

 public function update(ProjectRequest $request, Project $project)
 {
 $old = $project->only(['name', 'status']);
 $project->update($request->validated());
 ActivityLog::record('project.updated', $project, $old, $project->only(['name', 'status']));

 return redirect()->route('projects.show', $project)->with('success', 'Project updated.');
 }

 public function destroy(Project $project)
 {
 $project->delete();
 ActivityLog::record('project.deleted', $project);

 return redirect()->route('projects.index')->with('success', 'Project deleted.');
 }

 public function addMember(Request $request, Project $project)
 {
 $validated = $request->validate([
 'user_id' => ['required', 'exists:users,id'],
 'role' => ['required', 'in:lead,member'],
 ]);

 ProjectMember::firstOrCreate(
 ['project_id' => $project->id, 'user_id' => $validated['user_id']],
 $validated + ['tenant_id' => $project->tenant_id]
 );

 return back()->with('success', 'Member added.');
 }

 public function removeMember(Project $project, ProjectMember $member)
 {
 $member->delete();

 return back()->with('success', 'Member removed.');
 }

 /**
 * Kanban: create a project from a system/custom template.
 */
 public function createFromTemplate(Request $request)
 {
 $validated = $request->validate([
 'template_id' => ['required', 'exists:project_templates,id'],
 'client_id' => ['required', 'exists:clients,id'],
 'name' => ['required', 'string', 'max:255'],
 ]);

 $template = ProjectTemplate::findOrFail($validated['template_id']);

 $project = Project::create([
 'tenant_id' => app('currentTenant')->id,
 'client_id' => $validated['client_id'],
 'name' => $validated['name'],
 'description' => $template->description,
 'status' => 'active',
 'service_type' => $template->service_type,
 'template_id' => $template->id,
 'created_by' => auth()->id(),
 ]);

 foreach ($template->templateTasks as $index => $templateTask) {
 Task::create([
 'tenant_id' => $project->tenant_id,
 'client_id' => $project->client_id,
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

 return redirect()->route('projects.show', $project)->with('success', 'Project created from template.');
 }

 protected function visibleClients(User $user)
 {
 $query = Client::query();

 if ($user->isAccountManager()) {
 $query->where('account_manager_id', $user->id);
 } elseif ($user->isSpecialist()) {
 return collect();
 }

 return $query->orderBy('company_name')->get();
 }
}
