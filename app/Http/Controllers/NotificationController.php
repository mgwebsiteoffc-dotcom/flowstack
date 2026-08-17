<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
 public function index()
 {
 $notifications = auth()->user()->notifications()->paginate(20);

 return view('notifications.index', compact('notifications'));
 }

 public function read(Request $request, string $notification)
 {
 $notification = auth()->user()->notifications()->findOrFail($notification);
 $notification->markAsRead();

 $data = $notification->data;
 $routeName = $data['route'] ?? 'dashboard';

 if ($routeName === 'dashboard') {
 return redirect()->route('dashboard');
 }

 $params = $data['data'] ?? [];

 try {
 return redirect()->route($routeName, $params);
 } catch (\Throwable) {
 return redirect()->route('dashboard');
 }
 }

 public function readAll()
 {
 auth()->user()->unreadNotifications()->update(['read_at' => now()]);

 return back()->with('success', 'All notifications marked as read.');
 }
}
