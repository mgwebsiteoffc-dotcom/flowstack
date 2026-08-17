<?php

namespace App\Jobs\Notifications;

use App\Mail\WeeklySummaryMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Monday 08:00: weekly summary to admins only.
 */
class SendWeeklyManagerSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $admins = User::withoutGlobalScopes()
            ->with('tenant')
            ->where('role', 'admin')
            ->where('is_active', true)
            ->get();

        foreach ($admins as $admin) {
            $weekStart = now()->startOfWeek();
            $weekEnd = now()->endOfWeek();

            $stats = [
                'tasks_completed' => \App\Models\Task::withoutGlobalScopes()
                    ->where('tenant_id', $admin->tenant_id)
                    ->where('status', 'done')
                    ->where('updated_at', '>=', $weekStart)
                    ->count(),
                'hours_logged' => round(\App\Models\TimeEntry::withoutGlobalScopes()
                    ->where('tenant_id', $admin->tenant_id)
                    ->where('started_at', '>=', $weekStart)
                    ->where('started_at', '<=', $weekEnd)
                    ->sum('duration_minutes') / 60, 1),
                'new_leads' => \App\Models\Lead::withoutGlobalScopes()
                    ->where('tenant_id', $admin->tenant_id)
                    ->where('created_at', '>=', $weekStart)
                    ->count(),
                'revenue' => \App\Models\Invoice::withoutGlobalScopes()
                    ->where('tenant_id', $admin->tenant_id)
                    ->where('status', 'paid')
                    ->where('payment_date', '>=', $weekStart)
                    ->sum('paid_amount'),
            ];

            Mail::to($admin->email, $admin->name)->queue(new WeeklySummaryMail($admin, $stats));
        }
    }
}
