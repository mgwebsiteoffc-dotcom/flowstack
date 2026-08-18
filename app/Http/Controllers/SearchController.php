<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\KbArticle;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class SearchController extends Controller
{
 /**
 * Global search across clients, projects, tasks, leads and KB articles.
 */
 public function index(Request $request)
 {
 $term = trim((string) $request->input('q'));

 $results = ['clients' => [], 'projects' => [], 'tasks' => [], 'leads' => [], 'articles' => []];

 if (mb_strlen($term) >= 2) {
 $user = auth()->user();

 $clientQuery = Client::query();
 if ($user->isAccountManager()) {
 $clientQuery->where('account_manager_id', $user->id);
 }

 $results['clients'] = (clone $clientQuery)->where('company_name', 'like', "%{$term}%")->limit(5)->get();

 $projectQuery = Project::with('client');
 if ($user->isAccountManager()) {
 $projectQuery->whereHas('client', fn ($q) => $q->where('account_manager_id', $user->id));
 } elseif ($user->isSpecialist()) {
 $projectQuery->whereHas('members', fn ($q) => $q->where('user_id', $user->id));
 }

 $results['projects'] = (clone $projectQuery)->where('name', 'like', "%{$term}%")->limit(5)->get();

 $taskQuery = Task::with('client')->where('title', 'like', "%{$term}%");
 if ($user->isAccountManager()) {
 $taskQuery->whereHas('client', fn ($q) => $q->where('account_manager_id', $user->id));
 } elseif ($user->isSpecialist()) {
 $taskQuery->where(function ($q) use ($user) {
 $q->where('assigned_to', $user->id)->orWhere('created_by', $user->id);
 });
 }

 $results['tasks'] = (clone $taskQuery)->limit(5)->get();

 $leadQuery = Lead::where(function ($q) use ($term) {
 $q->where('contact_name', 'like', "%{$term}%")
 ->orWhere('company_name', 'like', "%{$term}%")
 ->orWhere('email', 'like', "%{$term}%");
 });

 if ($user->isSpecialist()) {
 $leadQuery->where('assigned_to', $user->id);
 }

 $results['leads'] = (clone $leadQuery)->limit(5)->get();

 $results['articles'] = KbArticle::published()->search($term)->limit(5)->get();
 }

 return view('search.index', compact('term', 'results'));
 }
}
