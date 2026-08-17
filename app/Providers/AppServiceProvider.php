<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Report;
use App\Models\Task;
use App\Models\User;
use App\Policies\ClientPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LeadPolicy;
use App\Policies\ReportPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Strict mode catches missing attributes & unguarded writes during dev.
        Model::shouldBeStrict(! $this->app->isProduction());

        // Policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);

        // Login throttling: 5 attempts per minute per email+ip.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });

        // Lead365 webhook: 60 requests per minute per IP.
        RateLimiter::for('lead365-webhook', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Portal login: 5 attempts per minute per email+ip.
        RateLimiter::for('portal-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });
    }
}
