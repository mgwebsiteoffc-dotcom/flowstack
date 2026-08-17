<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * NOTE: Laravel 11+ no longer auto-discovers this class. Scheduled jobs for
 * this application are registered in bootstrap/app.php -> withSchedule() and
 * mirrored in routes/console.php. This file is kept for Laravel 10
 * compatibility and documents the full schedule.
 */
class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Mirrored from bootstrap/app.php -> withSchedule() - see routes/console.php.
        $schedule->job(new \App\Jobs\Tasks\CheckOverdueTasks)->dailyAt('07:00');
        $schedule->job(new \App\Jobs\Finance\CheckAutomationDelays)->hourly();
        $schedule->job(new \App\Jobs\Finance\CheckOverdueInvoices)->dailyAt('07:00');
        $schedule->job(new \App\Jobs\Finance\CheckInvoiceReminders)->dailyAt('08:30');
        $schedule->job(new \App\Jobs\Tasks\CreateRecurringTaskInstances)->dailyAt('06:00');
        $schedule->job(new \App\Jobs\Notifications\SendDailyDigestToAllUsers)->dailyAt('08:00');
        $schedule->job(new \App\Jobs\BikriBook\SyncAllTenantsInvoices)->everySixHours();
        $schedule->job(new \App\Jobs\Notifications\SendWeeklyManagerSummary)->weeklyOn(1, '08:00');
        $schedule->job(new \App\Jobs\Finance\CheckContractRenewals)->monthlyOn(1, '09:00');
        $schedule->job(new \App\Jobs\Finance\CleanOldWebhookLogs)->weekly();
        $schedule->job(new \App\Jobs\Finance\CleanExpiredTrials)->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
