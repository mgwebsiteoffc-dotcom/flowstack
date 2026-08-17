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
 // ALWAYS make the tenant context keys resolvable (returning null) so
 // that app('currentTenant') never throws "Target class [currentTenant]
 // does not exist" on routes that do NOT run TenantMiddleware (login,
 // register, portal, super-admin, webhooks, /dev/* diagnostics).
 //
 // IMPORTANT: this must be a CLOSURE binding, NOT app()->instance(key,
 // null). Laravel's container resolves instances via isset(), and
 // isset(null) is false in PHP, so a null instance is silently ignored
 // and the container still tries to build a class named 'currentTenant'
 // (BindingResolutionException). A closure binding is always resolved
 // and is allowed to return null.
 $this->app->bind('currentTenant', fn () => null);
 $this->app->bind('currentTenantId', fn () => null);
 // TenantMiddleware later overrides via app()->instance('currentTenant',
 // $tenant) - real objects ARE returned by isset(), so the override wins.

 // NOTE: strict mode (Model::shouldBeStrict) is intentionally NOT enabled.
 // In local it throws LazyLoadingViolationException on any lazy-loaded
 // relationship (e.g. $user->tenant right after login) and
 // MassAssignment exceptions on validated() payloads, turning routine
 // requests into 500 errors. Lazy loading is normal here; N+1 is kept
 // in check by eager loading in controllers.

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
