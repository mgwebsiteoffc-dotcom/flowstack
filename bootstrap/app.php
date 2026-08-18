<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
use App\Jobs\Finance\CheckAutomationDelays;
use App\Jobs\Finance\CheckContractRenewals;
use App\Jobs\Finance\CheckInvoiceReminders;
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
        // Friendly branded pages for known errors.
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

        // Production: NEVER show code/stack traces - show the friendly 500 page.
        // Errors are still logged to storage/logs/laravel.log for the team.
        // (404/403/419/429 keep Laravel's own branded pages - this only
        // catches unhandled 500-class exceptions.)
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (app()->environment('production') && ! $request->expectsJson()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                if ($status >= 500) {
                    Log::error('Unhandled error', ['exception' => $e->getMessage(), 'url' => $request->fullUrl()]);

                    return response()->view('errors.500', [], 500);
                }
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Something went wrong. Our team has been notified.'], 500);
            }
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new CheckAutomationDelays)->hourly();
        $schedule->job(new CheckOverdueTasks)->dailyAt('07:00');
        $schedule->job(new CheckOverdueInvoices)->dailyAt('07:00');
        $schedule->job(new CheckInvoiceReminders)->dailyAt('08:30');
        $schedule->job(new CreateRecurringTaskInstances)->dailyAt('06:00');
        $schedule->job(new SendDailyDigestToAllUsers)->dailyAt('08:00');
        $schedule->job(new SyncAllTenantsInvoices)->everySixHours();
        $schedule->job(new SendWeeklyManagerSummary)->weeklyOn(1, '08:00');
        $schedule->job(new CheckContractRenewals)->monthlyOn(1, '09:00');
        $schedule->job(new CleanOldWebhookLogs)->weekly();
        $schedule->job(new CleanExpiredTrials)->daily();
    })
    ->create();
