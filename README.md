# Agency OS — Multi-tenant Agency Management SaaS

A complete agency operating system for digital marketing & operations agencies
(8–10 remote team members, 5–6 clients), built as a multi-tenant SaaS where each
agency gets its own subdomain (`agency.yoursaas.com`) with fully isolated data.

**Stack (no exceptions):** Laravel 12 · Blade · Tailwind CSS (CDN) · Alpine.js (CDN) ·
Chart.js (CDN) · MySQL 8 · database queue · DomPDF · Maatwebsite Excel · Razorpay ·
Breeze-style auth (scaffolding included in the repo) · Lead365 webhooks · BikriBook REST API.

---

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
# create a MySQL database and set DB_DATABASE/DB_USERNAME/DB_PASSWORD in .env
php artisan storage:link
php artisan migrate --seed        # plans + super admin (+ demo tenant in local)
php artisan serve                 # http://localhost:8000
php artisan queue:work            # REQUIRED — the platform depends on the queue
php artisan schedule:work         # runs scheduled jobs (or cron: * * * * * php artisan schedule:run)
```

Register at `/register` — a tenant (14-day trial) + admin user are created for you,
then the 6-step onboarding wizard takes over.

> The auth scaffolding (login/register/reset/team invites) is already included —
> **do not run `php artisan breeze:install`**, it would overwrite these files.

### Local demo tenant (non-production)

```bash
php artisan db:seed --class=DemoTenantSeeder
```
Log in at `http://localhost:8000` with `admin@demo.com` / `password123`.
With `TENANT_FALLBACK_SINGLE=true` (default in `.env.example`), the base host falls
back to the first active tenant so `artisan serve` works without wildcard DNS.
In production keep it `false` and point `*.yoursaas.com` at the app.

---

## Multi-tenancy (security-critical)

- **One database**; every tenant table carries `tenant_id`.
- `App\Scopes\TenantScope` is a **global scope on every tenant model** — every
  query automatically gets `WHERE tenant_id = <current tenant>`.
- `App\Http\Middleware\TenantMiddleware` reads the subdomain from the request host,
  resolves the tenant by slug (404 if unknown, upgrade redirect if inactive), and
  stores it as `app('currentTenant')`. Queued jobs set the context via
  `TenantScope::setCurrent($tenantId)`.
- Tenant id resolution order: `app('currentTenant')` → `app('currentTenantId')` →
  `auth()->user()->tenant_id`. If no context exists, no constraint is applied —
  but every code path touching tenant data establishes one first.
- Tenant isolation is covered by `tests/Feature/TenantIsolationTest.php`
  (tenant A cannot see tenant B's clients; scope falls back to the auth user;
  unknown subdomains 404; webhook logging; registration + default data).

## Roles

| Role | Access |
|---|---|
| `admin` | Everything |
| `ops_manager` | Everything except P&L, permanent client deletion, subscriptions |
| `account_manager` | Own clients, their tasks/reports/invoices/leads |
| `specialist` | Own tasks, projects they belong to, knowledge base |
| `client_portal_user` | Separate `/portal` auth — own projects, shared reports, approvals, requests, invoices, shared files |
| `super_admin` | Separate `/super-admin` auth — tenants, plans, payments, impersonation |

Policies: `ClientPolicy`, `TaskPolicy`, `LeadPolicy`, `InvoicePolicy`,
`ReportPolicy`, `UserPolicy` + `CheckRole` middleware.

## Modules

Dashboard (role-specific) · Clients (+ 9 tabs, 20-item onboarding checklist,
system folders, health scores) · Projects (+ 5 system templates) · Tasks
(list/kanban via SortableJS/my-tasks/calendar, checklists, subtasks, attachments,
comments, recurring instances, approvals) · Team (+ invites, workload, hourly
costs) · Leads (pipeline, analytics, Excel export, won→client conversion) ·
Finance (invoices with BikriBook sync, expenses, profitability with margin
colours) · Reports (4-step builder, DomPDF, share to client) · Knowledge Base
(Quill editor, search, auto-TOC, AI Prompts Library with copy buttons, team
comments, feedback) · File manager (hierarchical folders incl. /Internal,
versions, share links, drag & drop, inline image/PDF preview, MIME validation,
lead attachments) · Time tracking (top-bar timer widget, timesheets, billable
split, Excel export) · Automation rules engine (incl. delayed executions) ·
Announcements · Notifications (in-app + queued emails incl. welcome/assigned/
comment/paid/meta-lead/won/portal-request/contract/reminder digests + daily
digest + weekly summary) · Client portal · Settings (agency, users, resend
invites, integrations, notification prefs, audit log, subscription) · SaaS
registration + onboarding wizard · Super admin panel.

## Integrations

### Lead365 (inbound webhooks)

`POST /webhooks/lead365/{tenant_slug}` — public, rate-limited 60/min/IP.

Contract: **always answers 200 in <2s**, raw payload is written to `webhook_logs`
**before** anything else, processing always happens in the queued job
(`ProcessLead365Webhook`, retry 3× with backoff). All 9 events handled:
`lead.created`, `lead.updated`, `lead.deleted`, `lead.stage_changed`,
`lead.assigned`, `lead.won`, `lead.lost`, `form.submitted`, `meta.lead.received`
(meta leads auto-create an urgent same-day call task). Idempotent by
`lead365_lead_id`. Settings page: `/settings/integrations/lead365` (webhook URL
with copy button, secret, stage mapping, auto-assignment, notification toggles,
last-50 event log).

### BikriBook (outbound REST)

`App\Services\BikriBookService` — Guzzle client with bearer auth, 401 →
`BikriBookAuthException`, 429 retry/backoff (3×), 5xx → `BikriBookServerException`,
every call mirrored to `bikribook_sync_logs`. API key is **always** stored
`Crypt::encrypted` and only decrypted inside the service — never logged, only the
last 4 chars shown in the UI.

> **Endpoint note:** BikriBook's public API docs are not authoritative in this
> codebase — endpoint paths (`/customers`, `/invoices`, `/invoices/{id}/send`,
> `/company`) and field names follow a standard REST invoice API and are
> centralised in the `ENDPOINT_*` constants at the top of the service so they can
> be adjusted in one place.

Workflow: invoice saved locally as draft **first** (a BikriBook outage never
blocks invoicing — `bikribook_sync_status=failed` + Retry button) → explicit
"Sync to BikriBook" (`SyncInvoiceToBikriBook`, retry 3×/5 min) → "Send to
Client" via BB → every 6h `SyncAllTenantsInvoices` checks payment status
(paid → local update + `InvoicePaid` event + admin notification) → PDF: BB PDF
first, DomPDF fallback. Settings: `/settings/integrations/bikribook` with
encrypted key fields, test connection, toggles and sync log.

### Razorpay (subscriptions)

`/upgrade` → order creation via the Razorpay SDK → checkout.js → signature
verified server-side → subscription + payment recorded, tenant plan activated.

## Scheduler (Laravel 11+ `bootstrap/app.php` → `withSchedule`)

| Job | Schedule |
|---|---|
| `CheckAutomationDelays` (delayed rule executions) | hourly |
| `CheckOverdueTasks` (overdue automation + emails) | daily 07:00 |
| `CheckOverdueInvoices` (sent→overdue + client email) | daily 07:00 |
| `CheckInvoiceReminders` (email 3 days before due date) | daily 08:30 |
| `CreateRecurringTaskInstances` | daily 06:00 |
| `SendDailyDigestToAllUsers` | daily 08:00 |
| `SyncAllTenantsInvoices` (BikriBook payment sync) | every 6 hours |
| `SendWeeklyManagerSummary` (admins) | Monday 08:00 |
| `CheckContractRenewals` (30-day expiry alerts) | 1st of month 09:00 |
| `CleanOldWebhookLogs` (retention 90 days) | weekly |
| `CleanExpiredTrials` (grace 30d, then data deletion) | daily |

`app/Console/Kernel.php` mirrors the same schedule for Laravel-10-style setups
(the file is ignored by L11+ to avoid double execution).

## Testing & CI

```bash
php artisan test
```
Includes tenant-isolation tests and role-access tests (specialists blocked from
finance; AMs only see own clients). A GitHub Actions workflow
(`.github/workflows/ci.yml`, PHP 8.2/8.3 × MySQL 8) runs lint, migrate+seed,
`view:cache`/`config:cache`/`route:cache`, and the test suite. Push it from your
own GitHub account (the bot token used during development lacks the
`workflows` permission, so the file is in the working tree but was not pushed).

## Production notes

- Run the queue worker 24/7 via Supervisor: `php artisan queue:work database --tries=3`.
- Cron: `* * * * * php artisan schedule:run`.
- Set `TENANT_DOMAIN` and wildcard DNS `*.yoursaas.com`; keep
  `TENANT_FALLBACK_SINGLE=false`.
- `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, 2h session lifetime.
- File uploads land outside the webroot (`storage/app/public/tenants/...`) and are
  served through controllers; `storage:link` only exposes avatars/logos.

## Repository layout (highlights)

```
app/Scopes/TenantScope.php            – tenant isolation global scope
app/Http/Middleware/                  – Tenant, CheckRole, Subscription, Portal, SuperAdmin
app/Services/                         – Lead365Webhook, BikriBook, Automation, Notification
app/Jobs/{Lead365,BikriBook,Tasks,Notifications,Finance} – queued jobs
app/Models/                           – 40+ models, all tenant-scoped
app/Policies/                         – role-based authorization
database/migrations/                  – one migration per table (54 files)
database/seeders/                     – Plans, SuperAdmin, DefaultData (per tenant), Demo
resources/views/                      – 120+ Blade views (layouts, components, modules)
tests/Feature/TenantIsolationTest.php – THE multi-tenant security test
```

Build order followed: environment → schema → seeders → auth → tenancy core →
models → layouts/components → dashboard → clients → projects → tasks → team →
leads → Lead365 → finance/BikriBook → reports → KB → files → time → automation →
notifications → portal → registration/onboarding → subscriptions → super admin →
settings → tests/CI.
