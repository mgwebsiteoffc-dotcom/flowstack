<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
 public function __construct()
 {
 // Creating/managing announcements is manager-level; viewing is for everyone.
 $this->middleware('role:admin,ops_manager')->except(['index']);
 }

 public function index()
 {
 $user = auth()->user();

 $announcements = Announcement::with('creator')->visible()->latest()->paginate(15);

 // Mark all visible announcements as read for this user.
 $visibleIds = $announcements->getCollection()->pluck('id');

 foreach ($visibleIds as $id) {
 AnnouncementRead::firstOrCreate(
 ['announcement_id' => $id, 'user_id' => $user->id],
 ['read_at' => now()]
 );
 }

 $canManage = in_array($user->role, ['admin', 'ops_manager'], true);

 return view('announcements.index', compact('announcements', 'canManage'));
 }

 public function store(Request $request)
 {
 $validated = $request->validate([
 'title' => ['required', 'string', 'max:255'],
 'content' => ['required', 'string'],
 'is_pinned' => ['sometimes', 'boolean'],
 'expires_at' => ['nullable', 'date', 'after:today'],
 ]);

 Announcement::create([
 'tenant_id' => app('currentTenant')->id,
 'title' => $validated['title'],
 'content' => $validated['content'],
 'is_pinned' => $request->boolean('is_pinned'),
 'expires_at' => $validated['expires_at'] ? now()->parse($validated['expires_at']) : null,
 'created_by' => auth()->id(),
 ]);

 ActivityLog::record('announcement.created', null, null, ['title' => $validated['title']]);

 return back()->with('success', 'Announcement published.');
 }

 public function destroy(Announcement $announcement)
 {
 $announcement->delete();

 return back()->with('success', 'Announcement removed.');
 }
}
