<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

use App\Exceptions\BikriBookApiException;
use App\Exceptions\BikriBookAuthException;
use App\Exceptions\SubscriptionExpiredException;
use App\Exceptions\TenantNotFoundException;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureSubscriptionActive;
use App\Http\Middleware\PortalAuthMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\TenantMiddleware;
use App\Jobs\BikriBook\SyncAllTenantsInvoices;
use App\Jobs\Finance\CheckContractRenewals;
use App\Jobs\Finance\CheckOverdueInvoices;
use App\Jobs\Finance\CleanExpiredTrials;
use App\Jobs\Finance\CleanOldWebhookLogs;
use App\Jobs\Notifications\SendDailyDigestToAllUsers;
use App\Jobs\Notifications\SendWeeklyManagerSummary;
use App\Jobs\Tasks\CheckOverdueTasks;
use App\Jobs\Tasks\CreateRecurringTaskInstances;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant' => TenantMiddleware::class,
            'role' => CheckRole::class,
            'subscription' => EnsureSubscriptionActive::class,
            'portal.auth' => PortalAuthMiddleware::class,
            'super.admin' => SuperAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TenantNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 404);
            }

            return response()->view('errors.404', ['message' => $e->getMessage()], 404);
        });

        $exceptions->render(function (SubscriptionExpiredException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 403);
            }

            return redirect()->route('upgrade')->with('error', $e->getMessage());
        });

        $exceptions->render(function (BikriBookAuthException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            return back()->with('error', $e->getMessage());
        });

        $exceptions->render(function (BikriBookApiException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        // Laravel 11+ defines scheduled jobs here (see app/Console/Kernel.php for
        // the Laravel 10 equivalent kept for reference).
        $schedule->job(new CheckOverdueTasks)->dailyAt('07:00');
        $schedule->job(new CheckOverdueInvoices)->dailyAt('07:00');
        $schedule->job(new CreateRecurringTaskInstances)->dailyAt('06:00');
        $schedule->job(new SendDailyDigestToAllUsers)->dailyAt('08:00');
        $schedule->job(new SyncAllTenantsInvoices)->everySixHours();
        $schedule->job(new SendWeeklyManagerSummary)->weeklyOn(1, '08:00');
        $schedule->job(new CheckContractRenewals)->monthlyOn(1, '09:00');
        $schedule->job(new CleanOldWebhookLogs)->weekly();
        $schedule->job(new CleanExpiredTrials)->daily();
    })
    ->create();
