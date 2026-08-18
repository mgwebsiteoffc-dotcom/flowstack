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

        // Never show code/stack traces outside local dev - render branded pages.
        // Validation errors, auth redirects and 419/429 keep the framework's
        // default behaviour (messages still reach the user); everything else
        // (404/403/500+) gets a clean branded page. Errors are always logged.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return null;
            }

            $hideCode = app()->environment('production') || ! app()->hasDebugModeEnabled();
            if (! $hideCode) {
                return null;
            }

            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $status >= 500
                        ? 'Something went wrong. Our team has been notified.'
                        : ($status === 404 ? 'Not found.' : 'Request failed.'),
                ], $status);
            }

            if ($status === 419 || $status === 429) {
                return null; // session expired / rate limited - keep Laravel's own pages
            }

            Log::error('Unhandled error', [
                'exception' => get_class($e).': '.$e->getMessage(),
                'url' => $request->fullUrl(),
            ]);

            $view = $status === 404 ? 'errors.404' : ($status === 403 ? 'errors.403' : 'errors.500');

            return response()->view($view, [], in_array($status, [404, 403], true) ? $status : 500);
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
