<?php

namespace App\Jobs\Notifications;

use App\Mail\DailyDigestMail;
use App\Models\Announcement;
use App\Models\Setting;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Daily 08:00: sends every active team member their personal digest:
 * tasks due today, overdue tasks, pending approvals, unread announcements.
 */
class SendDailyDigestToAllUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationService $notifications): void
    {
        $users = User::withoutGlobalScopes()
            ->with('tenant')
            ->where('is_active', true)
            ->get();

        foreach ($users as $user) {
            if (! $notifications->userWantsEmail($user, 'daily_digest')) {
                continue;
            }

            $dueToday = Task::withoutGlobalScopes()
                ->where('tenant_id', $user->tenant_id)
                ->where('assigned_to', $user->id)
                ->where('due_date', now()->toDateString())
                ->whereNotIn('status', ['done', 'cancelled'])
                ->pluck('title');

            $overdue = Task::withoutGlobalScopes()
                ->where('tenant_id', $user->tenant_id)
                ->where('assigned_to', $user->id)
                ->where('due_date', '<', now()->toDateString())
                ->whereNotIn('status', ['done', 'cancelled'])
                ->pluck('title');

            $pendingApprovals = \App\Models\ClientApproval::withoutGlobalScopes()
                ->where('tenant_id', $user->tenant_id)
                ->where('status', 'pending')
                ->count();

            $unreadAnnouncements = Announcement::withoutGlobalScopes()
                ->where('tenant_id', $user->tenant_id)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id))
                ->pluck('title');

            Mail::to($user->email, $user->name)->queue(new DailyDigestMail($user, $dueToday, $overdue, $pendingApprovals, $unreadAnnouncements));
        }
    }
}
