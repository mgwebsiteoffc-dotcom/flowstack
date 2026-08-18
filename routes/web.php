<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KbArticleController;
use App\Http\Controllers\KbCategoryController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ProfitabilityController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\Webhooks\Lead365WebhookController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Portal\PortalApprovalController;
use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalFileController;
use App\Http\Controllers\Portal\PortalInvoiceController;
use App\Http\Controllers\Portal\PortalProjectController;
use App\Http\Controllers\Portal\PortalReportController;
use App\Http\Controllers\Portal\PortalRequestController;
use App\Http\Controllers\SuperAdmin\SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\SuperAdminBlogController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminPlanController;
use App\Http\Controllers\SuperAdmin\SuperAdminTrackingController;
use App\Http\Controllers\SuperAdmin\SuperAdminRoleController;
use App\Http\Controllers\SuperAdmin\SuperAdminTenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes (no tenant, no auth)
|--------------------------------------------------------------------------
*/
// --- DEVELOPMENT DIAGNOSTIC (available when APP_DEBUG=true) -------------
if (config('app.debug')) {
    Route::get('/dev/clear', function () {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');

        return response('<pre style="font:12px monospace;padding:20px">Caches cleared:\n'.\Illuminate\Support\Facades\Artisan::output().'</pre>');
    })->name('dev.clear');

    Route::get('/dev/pages', function () {
        // Smoke test: hit the key pages and report status codes.
        $paths = ['/', '/pricing', '/register', '/login', '/blog', '/sitemap.xml', '/robots.txt'];
        $out = [];
        foreach ($paths as $path) {
            try {
                $start = microtime(true);
                $resp = \Illuminate\Support\Facades\Http::withoutVerifying()->get(url($path));
                $ms = round((microtime(true) - $start) * 1000);
                $out[] = [$path, $resp->status(), $ms.'ms'];
            } catch (\Throwable $e) {
                $out[] = [$path, 'ERR', $e->getMessage()];
            }
        }
        $html = '<pre style="font:12px monospace;padding:20px">'."\n";
        foreach ($out as [$path, $status, $extra]) {
            $color = $status === 200 ? '#16a34a' : '#dc2626';
            $html .= '<span style="color:'.$color.'">'.$status.'</span>  '.str_pad($path, 20).' '.$extra."\n";
        }
        $html .= "</pre>";

        return response($html);
    })->name('dev.pages');

 Route::get('/dev/session', function () {
 $probe = (int) session('_probe', 0);
 session(['_probe' => $probe + 1]);

 return response()->json([
 'session_driver' => config('session.driver'),
 'session_id' => session()->getId(),
 'cookie_name' => config('session.cookie'),
 'cookie_sent_by_browser' => request()->cookies->has(config('session.cookie')),
 'probe_visits' => $probe + 1,
 'auth_check' => auth()->check(),
 'auth_email' => auth()->user()?->email,
 'current_tenant' => app('currentTenant')?->slug,
 'users_total' => \App\Models\User::withoutGlobalScopes()->count(),
 'super_admins_total' => \App\Models\SuperAdmin::count(),
 'sessions_table_exists' => \Illuminate\Support\Facades\Schema::hasTable('sessions'),
 ]);
 })->name('dev.session');

 Route::get('/dev/error', function () {
 $log = storage_path('logs/laravel.log');
 $out = 'No log file.';
 if (is_file($log)) {
 $content = file_get_contents($log);
 // Last 10 exception blocks
 $blocks = preg_split('/\[20\d{2}-/', $content);
 $last = array_slice($blocks, -10);
 $out = '';
 foreach (array_reverse($last) as $b) {
 $out .= '[20' . substr($b, 0, 80) . "
" . substr($b, 80, 1800) . "

========

";
 }
 }
 return response('<pre style="font-size:12px;white-space:pre-wrap;word-break:break-word;padding:20px">'
 . htmlspecialchars($out) . '</pre>')
 ->header('Content-Type', 'text/html');
 })->name('dev.error');
}
// -----------------------------------------------------------------------

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

// Public blog + SEO files
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/company', [SiteController::class, 'company'])->name('site.company');
Route::get('/features', [SiteController::class, 'features'])->name('site.features');
Route::get('/features/{slug}', [SiteController::class, 'feature'])->name('site.feature');
Route::get('/use-cases', [SiteController::class, 'useCases'])->name('site.use-cases');
Route::get('/use-cases/{slug}', [SiteController::class, 'useCase'])->name('site.use-case');
Route::get('/integrations', [SiteController::class, 'integrations'])->name('site.integrations');
Route::get('/resources', [SiteController::class, 'resources'])->name('site.resources');
Route::get('/faq', [SiteController::class, 'faq'])->name('site.faq');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:login')->name('contact.store');
Route::get('/sitemap.xml', function () {
    $posts = \App\Models\BlogPost::published()->get(['slug', 'updated_at']);
    $categories = \App\Models\BlogCategory::get(['slug']);

    return response()->view('seo.sitemap', compact('posts', 'categories'))->header('Content-Type', 'application/xml');
})->name('sitemap');
Route::get('/robots.txt', function () {
    $disallow = config('app.env') === 'production' ? '' : "Disallow: /\n";
    $content = "User-agent: *\n{$disallow}Allow: /\nSitemap: ".url('/sitemap.xml');
    return response($content)->header('Content-Type', 'text/plain');
})->name('robots');

/*
|--------------------------------------------------------------------------
| Guest auth (registration creates the tenant)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
 Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
 Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

 Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
 Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');

 Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
 Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:login')->name('password.email');
 Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
 Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:login')->name('password.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

// Team invitation acceptance (public, one-time token)
Route::get('/invite/{token}', [InviteController::class, 'accept'])->name('onboarding.invite-accept');
Route::post('/invite/{token}', [InviteController::class, 'store'])->middleware('guest')->name('onboarding.invite-accept.store');

/*
|--------------------------------------------------------------------------
| Lead365 webhook - public, rate limited 60/min/IP, answers in <2s
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/lead365/{tenant_slug}', [Lead365WebhookController::class, 'handle'])
 ->middleware('throttle:lead365-webhook')
 ->name('webhooks.lead365');

/*
|--------------------------------------------------------------------------
| Client portal - fully separate auth (client_portal_users)
|--------------------------------------------------------------------------
*/
Route::prefix('portal')->name('portal.')->group(function () {
 Route::get('/login', [PortalAuthController::class, 'showLogin'])->middleware('guest')->name('login');
 Route::post('/login', [PortalAuthController::class, 'login'])->middleware(['guest', 'throttle:portal-login'])->name('login.store');
 Route::get('/set-password/{token}', [PortalAuthController::class, 'showSetPassword'])->middleware('guest')->name('set-password');
 Route::post('/set-password', [PortalAuthController::class, 'setPassword'])->middleware('guest')->name('set-password.store');
 Route::post('/logout', [PortalAuthController::class, 'logout'])->name('logout');

 Route::middleware('portal.auth')->group(function () {
 Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');
 Route::get('/projects', [PortalProjectController::class, 'index'])->name('projects');
 Route::get('/projects/{project}', [PortalProjectController::class, 'show'])->name('projects.show');
 Route::get('/reports', [PortalReportController::class, 'index'])->name('reports');
 Route::get('/reports/{report}', [PortalReportController::class, 'show'])->name('reports.show');
 Route::get('/reports/{report}/download', [PortalReportController::class, 'download'])->name('reports.download');
 Route::post('/reports/{report}/comment', [PortalReportController::class, 'comment'])->name('reports.comment');
 Route::get('/approvals', [PortalApprovalController::class, 'index'])->name('approvals');
 Route::post('/approvals/{approval}/respond', [PortalApprovalController::class, 'respond'])->name('approvals.respond');
 Route::get('/requests', [PortalRequestController::class, 'index'])->name('requests');
 Route::get('/requests/create', [PortalRequestController::class, 'create'])->name('requests.create');
 Route::post('/requests', [PortalRequestController::class, 'store'])->name('requests.store');
 Route::get('/invoices', [PortalInvoiceController::class, 'index'])->name('invoices');
 Route::get('/invoices/{invoice}/download', [PortalInvoiceController::class, 'download'])->name('invoices.download');
 Route::get('/files', [PortalFileController::class, 'index'])->name('files');
 Route::get('/files/{file}/download', [PortalFileController::class, 'download'])->name('files.download');
 });
});

/*
|--------------------------------------------------------------------------
| Super admin panel - separate auth (super_admins)
|--------------------------------------------------------------------------
*/
Route::prefix('super-admin')->name('super-admin.')->group(function () {
 Route::get('/login', [SuperAdminAuthController::class, 'showLogin'])->middleware('guest')->name('login');
 Route::post('/login', [SuperAdminAuthController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('login.store');
 Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
 Route::post('/impersonate/stop', [SuperAdminTenantController::class, 'stopImpersonation'])->name('impersonate.stop');

 Route::middleware('super.admin')->group(function () {
 Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
 Route::get('/tenants', [SuperAdminTenantController::class, 'index'])->name('tenants.index');
 Route::get('/tenants/{tenant}', [SuperAdminTenantController::class, 'show'])->name('tenants.show');
 Route::patch('/tenants/{tenant}', [SuperAdminTenantController::class, 'update'])->name('tenants.update');
 Route::post('/tenants/{tenant}/impersonate', [SuperAdminTenantController::class, 'impersonate'])->name('tenants.impersonate');
 Route::post('/tenants/{tenant}/extend-trial', [SuperAdminTenantController::class, 'extendTrial'])->name('tenants.extend-trial');
 Route::get('/plans', [SuperAdminPlanController::class, 'index'])->name('plans.index');
 Route::post('/plans', [SuperAdminPlanController::class, 'store'])->name('plans.store');
 Route::put('/plans/{plan}', [SuperAdminPlanController::class, 'update'])->name('plans.update');
 Route::delete('/plans/{plan}', [SuperAdminPlanController::class, 'destroy'])->name('plans.destroy');
 Route::get('/payments', [SuperAdminDashboardController::class, 'payments'])->name('payments');
 Route::get('/roles', [SuperAdminRoleController::class, 'index'])->name('roles.index');
 Route::post('/roles', [SuperAdminRoleController::class, 'save'])->name('roles.save');
 Route::get('/blog', [SuperAdminBlogController::class, 'index'])->name('blog.index');
 Route::get('/blog/create', [SuperAdminBlogController::class, 'create'])->name('blog.create');
 Route::post('/blog', [SuperAdminBlogController::class, 'store'])->name('blog.store');
 Route::get('/blog/{post}/edit', [SuperAdminBlogController::class, 'edit'])->name('blog.edit');
 Route::put('/blog/{post}', [SuperAdminBlogController::class, 'update'])->name('blog.update');
 Route::delete('/blog/{post}', [SuperAdminBlogController::class, 'destroy'])->name('blog.destroy');
 Route::post('/blog/categories', [SuperAdminBlogController::class, 'storeCategory'])->name('blog.categories.store');
 Route::delete('/blog/categories/{category}', [SuperAdminBlogController::class, 'destroyCategory'])->name('blog.categories.destroy');
 Route::get('/tracking', [SuperAdminTrackingController::class, 'index'])->name('tracking.index');
 Route::post('/tracking/pixels', [SuperAdminTrackingController::class, 'storePixel'])->name('tracking.pixels.store');
 Route::post('/tracking/pixels/{pixel}/toggle', [SuperAdminTrackingController::class, 'togglePixel'])->name('tracking.pixels.toggle');
 Route::delete('/tracking/pixels/{pixel}', [SuperAdminTrackingController::class, 'destroyPixel'])->name('tracking.pixels.destroy');
 Route::post('/tracking/links', [SuperAdminTrackingController::class, 'storeLink'])->name('tracking.links.store');
 Route::delete('/tracking/links/{link}', [SuperAdminTrackingController::class, 'destroyLink'])->name('tracking.links.destroy');
 });
});

/*
|--------------------------------------------------------------------------
| Internal application (auth + tenant + active subscription)
|--------------------------------------------------------------------------
*/
Route::middleware(['tenant', 'auth', 'subscription'])->group(function () {

 Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

 // Onboarding wizard
 Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
 Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
 Route::get('/onboarding/done', [OnboardingController::class, 'done'])->name('onboarding.done');

 // Trial / subscription upgrade
 Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
 Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
 Route::post('/subscription/callback', [SubscriptionController::class, 'callback'])->name('subscription.checkout.callback');
 Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

 // Clients
 Route::resource('clients', ClientController::class);
 Route::post('/clients/{client}/notes', [ClientController::class, 'storeNote'])->name('clients.notes.store');
 Route::patch('/clients/{client}/notes/{note}', [ClientController::class, 'updateNote'])->name('clients.notes.update');
 Route::delete('/clients/{client}/notes/{note}', [ClientController::class, 'destroyNote'])->name('clients.notes.destroy');
 Route::post('/clients/{client}/onboarding/{item}/toggle', [ClientController::class, 'toggleOnboarding'])->name('clients.onboarding.toggle');
 Route::patch('/clients/{client}/onboarding/{item}', [ClientController::class, 'updateOnboarding'])->name('clients.onboarding.update');
 Route::post('/clients/{client}/portal-access', [ClientController::class, 'togglePortalAccess'])->name('clients.portal-access');
 Route::post('/clients/{client}/contacts', [ClientController::class, 'storeContact'])->name('clients.contacts.store');
 Route::delete('/clients/{client}/contacts/{contact}', [ClientController::class, 'destroyContact'])->name('clients.contacts.destroy');

 // Projects
 Route::resource('projects', ProjectController::class);
 Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.members.store');
 Route::delete('/projects/{project}/members/{member}', [ProjectController::class, 'removeMember'])->name('projects.members.destroy');
 Route::post('/projects/{project}/from-template', [ProjectController::class, 'createFromTemplate'])->name('projects.from-template');

 // Tasks
 Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
 Route::get('/tasks/board', [TaskController::class, 'board'])->name('tasks.board');
 Route::get('/tasks/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.my-tasks');
 Route::get('/tasks/calendar', [TaskController::class, 'calendar'])->name('tasks.calendar');
 Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
 Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
 Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
 Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
 Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
 Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
 Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
 Route::post('/tasks/{task}/checklists', [TaskController::class, 'storeChecklist'])->name('tasks.checklists.store');
 Route::post('/tasks/checklist-items/{item}/toggle', [TaskController::class, 'toggleChecklistItem'])->name('tasks.checklist-items.toggle');
 Route::post('/tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
 Route::get('/tasks/{task}/attachments/{attachment}/download', [TaskController::class, 'downloadAttachment'])->name('tasks.attachments.download');
 Route::delete('/tasks/{task}/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
 Route::post('/tasks/{task}/subtasks', [TaskController::class, 'storeSubtask'])->name('tasks.subtasks.store');
 Route::post('/tasks/{task}/watchers', [TaskController::class, 'toggleWatcher'])->name('tasks.watchers.toggle');
 Route::post('/tasks/{task}/approval', [TaskController::class, 'submitApproval'])->name('tasks.approval.submit');
 Route::post('/tasks/board/reorder', [TaskController::class, 'reorderBoard'])->name('tasks.board.reorder');

 // Team
 Route::get('/team', [TeamController::class, 'index'])->name('team.index');
 Route::get('/team/{user}', [TeamController::class, 'show'])->name('team.show');
 Route::post('/team/invite', [TeamController::class, 'invite'])->name('team.invite');
 Route::post('/team/{user}/resend-invite', [TeamController::class, 'resendInvite'])->name('team.resend-invite');
 Route::patch('/team/{user}', [TeamController::class, 'update'])->name('team.update');
 Route::patch('/team/{user}/role', [TeamController::class, 'updateRole'])->name('team.update-role');
 Route::post('/team/{user}/toggle', [TeamController::class, 'toggleActive'])->name('team.toggle');

 // Leads
 Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
 Route::get('/leads/pipeline', [LeadController::class, 'pipeline'])->name('leads.pipeline');
 Route::get('/leads/analytics', [LeadController::class, 'analytics'])->name('leads.analytics');
 Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
 Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
 Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
 Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
 Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
 Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
 Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
 Route::post('/leads/{lead}/win', [LeadController::class, 'markWon'])->name('leads.win');
 Route::post('/leads/{lead}/lose', [LeadController::class, 'markLost'])->name('leads.lose');
 Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
 Route::post('/leads/{lead}/activities', [LeadController::class, 'storeActivity'])->name('leads.activities.store');
 Route::post('/leads/{lead}/stage', [LeadController::class, 'updateStage'])->name('leads.stage');

 // Finance
 Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
 Route::get('/invoices', [InvoiceController::class, 'index'])->name('finance.invoices.index');
 Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('finance.invoices.create');
 Route::post('/invoices', [InvoiceController::class, 'store'])->name('finance.invoices.store');
 Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('finance.invoices.show');
 Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('finance.invoices.edit');
 Route::patch('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('finance.invoices.update');
 Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('finance.invoices.destroy');
 Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('finance.invoices.pdf');
 Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('finance.invoices.send');
 Route::post('/invoices/{invoice}/sync', [InvoiceController::class, 'sync'])->name('finance.invoices.sync');
 Route::post('/invoices/sync-all', [InvoiceController::class, 'syncAll'])->name('finance.invoices.sync-all');
 Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('finance.invoices.mark-paid');
 Route::get('/expenses', [ExpenseController::class, 'index'])->name('finance.expenses.index');
 Route::post('/expenses', [ExpenseController::class, 'store'])->name('finance.expenses.store');
 Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('finance.expenses.destroy');
 Route::get('/profitability', [ProfitabilityController::class, 'index'])->name('finance.profitability');

 // Proposals
 Route::get('/proposals', [ProposalController::class, 'index'])->name('proposals.index');
 Route::get('/proposals/create', [ProposalController::class, 'create'])->name('proposals.create');
 Route::post('/proposals', [ProposalController::class, 'store'])->name('proposals.store');
 Route::get('/proposals/{proposal}', [ProposalController::class, 'show'])->name('proposals.show');
 Route::get('/proposals/{proposal}/pdf', [ProposalController::class, 'pdf'])->name('proposals.pdf');
 Route::post('/proposals/{proposal}/send', [ProposalController::class, 'send'])->name('proposals.send');
 Route::post('/proposals/{proposal}/status', [ProposalController::class, 'updateStatus'])->name('proposals.status');
 Route::delete('/proposals/{proposal}', [ProposalController::class, 'destroy'])->name('proposals.destroy');

 // Reports
 Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
 Route::get('/reports/__services', [ReportController::class, 'clientServices'])->name('reports.client-services');
 Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
 Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
 Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
 Route::get('/reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
 Route::patch('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');
 Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
 Route::get('/reports/{report}/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
 Route::post('/reports/{report}/share', [ReportController::class, 'share'])->name('reports.share');

 // Knowledge base
 Route::get('/knowledge-base', [KbCategoryController::class, 'index'])->name('kb.index');
 Route::get('/knowledge-base/search', [KbArticleController::class, 'search'])->name('kb.search');
 Route::get('/knowledge-base/create', [KbArticleController::class, 'create'])->name('kb.articles.create');
 Route::post('/knowledge-base', [KbArticleController::class, 'store'])->name('kb.articles.store');
 Route::get('/knowledge-base/{article}', [KbArticleController::class, 'show'])->name('kb.articles.show');
 Route::get('/knowledge-base/{article}/edit', [KbArticleController::class, 'edit'])->name('kb.articles.edit');
 Route::patch('/knowledge-base/{article}', [KbArticleController::class, 'update'])->name('kb.articles.update');
 Route::delete('/knowledge-base/{article}', [KbArticleController::class, 'destroy'])->name('kb.articles.destroy');
 Route::post('/knowledge-base/{article}/comments', [KbArticleController::class, 'storeComment'])->name('kb.articles.comments.store');
 Route::post('/knowledge-base/{article}/feedback', [KbArticleController::class, 'feedback'])->name('kb.articles.feedback');

 // Files
 Route::get('/files', [FileController::class, 'index'])->name('files.index');
 Route::post('/files/upload', [FileController::class, 'upload'])->name('files.upload');
 Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
 Route::get('/files/{file}/preview', [FileController::class, 'preview'])->name('files.preview');
 Route::patch('/files/{file}', [FileController::class, 'update'])->name('files.update');
 Route::post('/files/{file}/share', [FileController::class, 'share'])->name('files.share');
 Route::post('/files/{file}/unshare', [FileController::class, 'unshare'])->name('files.unshare');
 Route::delete('/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
 Route::post('/files/folders', [FileController::class, 'storeFolder'])->name('files.folders.store');
 Route::post('/files/{file}/version', [FileController::class, 'uploadVersion'])->name('files.version');
 Route::get('/files/shared/{token}', [FileController::class, 'shared'])->name('files.shared');

 // Time tracking
 Route::get('/time', [TimeEntryController::class, 'index'])->name('time.index');
 Route::get('/time/my-timesheet', [TimeEntryController::class, 'myTimesheet'])->name('time.my');
 Route::get('/time/all', [TimeEntryController::class, 'all'])->name('time.all');
 Route::post('/time/start', [TimeEntryController::class, 'start'])->name('time.start');
 Route::post('/time/stop', [TimeEntryController::class, 'stop'])->name('time.stop');
 Route::post('/time', [TimeEntryController::class, 'store'])->name('time.store');
 Route::delete('/time/{entry}', [TimeEntryController::class, 'destroy'])->name('time.destroy');

 // Automation
 Route::get('/automation', [AutomationController::class, 'index'])->name('automation.index');
 Route::post('/automation', [AutomationController::class, 'store'])->name('automation.store');
 Route::patch('/automation/{rule}', [AutomationController::class, 'update'])->name('automation.update');
 Route::post('/automation/{rule}/toggle', [AutomationController::class, 'toggle'])->name('automation.toggle');
 Route::delete('/automation/{rule}', [AutomationController::class, 'destroy'])->name('automation.destroy');
 Route::post('/automation/{rule}/test', [AutomationController::class, 'test'])->name('automation.test');

 // Notifications & announcements
 Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
 Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
 Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
 Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
 Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
 Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

 // Settings
 Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
 Route::get('/settings/master', [MasterDataController::class, 'index'])->name('settings.master.index');
 Route::post('/settings/master/categories', [MasterDataController::class, 'storeCategory'])->name('settings.master.category.store');
 Route::delete('/settings/master/categories/{category}', [MasterDataController::class, 'destroyCategory'])->name('settings.master.category.destroy');
 Route::post('/settings/master/tags', [MasterDataController::class, 'storeTag'])->name('settings.master.tag.store');
 Route::delete('/settings/master/tags/{tag}', [MasterDataController::class, 'destroyTag'])->name('settings.master.tag.destroy');
 Route::post('/settings/master/services', [MasterDataController::class, 'storeService'])->name('settings.master.service.store');
 Route::delete('/settings/master/services/{item}', [MasterDataController::class, 'destroyService'])->name('settings.master.service.destroy');
 Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
 Route::get('/settings/users', [SettingController::class, 'users'])->name('settings.users');
 Route::get('/settings/integrations/lead365', [SettingController::class, 'lead365'])->name('settings.integrations.lead365');
 Route::post('/settings/integrations/lead365', [SettingController::class, 'saveLead365'])->name('settings.integrations.lead365.save');
 Route::post('/settings/integrations/lead365/test', [SettingController::class, 'testLead365'])->name('settings.integrations.lead365.test');
 Route::get('/settings/integrations/bikribook', [SettingController::class, 'bikribook'])->name('settings.integrations.bikribook');
 Route::post('/settings/integrations/bikribook', [SettingController::class, 'saveBikribook'])->name('settings.integrations.bikribook.save');
 Route::post('/settings/integrations/bikribook/test', [SettingController::class, 'testBikribook'])->name('settings.integrations.bikribook.test');
 Route::get('/settings/integrations/channels', [IntegrationController::class, 'channels'])->name('settings.integrations.channels');
 Route::post('/settings/integrations/channels', [IntegrationController::class, 'saveChannels'])->name('settings.integrations.channels.save');
 Route::post('/settings/integrations/channels/test-slack', [IntegrationController::class, 'testSlack'])->name('settings.integrations.channels.test-slack');
 Route::post('/settings/integrations/channels/test-teams', [IntegrationController::class, 'testTeams'])->name('settings.integrations.channels.test-teams');
 Route::get('/settings/integrations/google-calendar', [IntegrationController::class, 'googleCalendar'])->name('settings.integrations.google-calendar');
 Route::get('/settings/integrations/google-calendar/connect', [IntegrationController::class, 'googleConnect'])->name('settings.integrations.google-calendar.connect');
 Route::get('/settings/integrations/google-calendar/callback', [IntegrationController::class, 'googleCallback'])->name('settings.integrations.google-calendar.callback');
 Route::post('/settings/integrations/google-calendar/disconnect', [IntegrationController::class, 'googleDisconnect'])->name('settings.integrations.google-calendar.disconnect');
 Route::post('/settings/integrations/google-calendar/save', [IntegrationController::class, 'saveCalendarSettings'])->name('settings.integrations.google-calendar.save');
 Route::post('/tasks/{task}/meet', [TaskController::class, 'generateMeet'])->name('tasks.meet');
 Route::get('/settings/notifications', [SettingController::class, 'notifications'])->name('settings.notifications');
 Route::post('/settings/notifications', [SettingController::class, 'saveNotifications'])->name('settings.notifications.save');
 Route::get('/settings/audit', [SettingController::class, 'auditLog'])->name('settings.audit');
 Route::get('/settings/subscription', [SettingController::class, 'subscription'])->name('settings.subscription');

 // Profile
 Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
 Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
 Route::post('/profile/password', [ProfileController::class, 'password'])->name('profile.password');

 // Global search
 Route::get('/search', [SearchController::class, 'index'])->name('search');
});
