# Task365 — Multi-tenant Agency Management SaaS

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

Register at `/register` — a tenant + admin user are created for you (choose a
14-day free trial or a paid plan), then the 6-step onboarding wizard takes over.

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

Proposals (create from leads/clients, PDF, email to client, accept/reject) ·
Super-admin role-menu mapping (/super-admin/roles) · Master data (expense
categories, task tags, custom service types in Settings) ·
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

### Slack & Microsoft Teams (channel notifications)

Settings → Integrations → Slack & Teams: paste an **incoming webhook URL** for
Slack (Incoming Webhooks) and/or Teams (Workflows → Incoming webhook), choose
which events notify, and hit "Send test". Events pushed (queued, never block):
new lead, lead won/lost, task assigned, task overdue, invoice paid/overdue,
contract expiring, new client request, report shared. Slack gets a text
message; Teams gets an Adaptive Card.

### Google Calendar sync + Google Meet generation

Settings → Integrations → Google Calendar: **OAuth 2.0 connect** (offline
access; tokens stored encrypted in `integration_tokens`). When "Sync tasks"
is enabled:
- Tasks with a due date create a 10:00 AM calendar event **with an automatic
  Google Meet conference link** (conferenceData → hangoutsMeet).
- The Meet link appears on the task page ("Join Google Meet") with a manual
  "Generate Google Meet link" button.
- Updating a task updates the event; deleting removes it.
- Upcoming events (with Meet/Calendar links) appear on the dashboard.

Requires `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`
(scopes: `https://www.googleapis.com/auth/calendar.events`) in `.env`.

### Razorpay (subscriptions)

`/upgrade` → order creation via the Razorpay SDK → checkout.js → signature
verified server-side → subscription + payment recorded, tenant plan activated.

### Gmail SMTP (outgoing email)

All outgoing mail — welcome emails, invoices, reports, portal invites, automation
alerts — is sent through Gmail SMTP. Two ways to configure:

**In the app (recommended):** Settings → **Email (Gmail)** (admin role). Enter the Gmail
address, paste the 16-character App Password, Save, then click **Send test email**.

**Or via `.env`:**

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=you@gmail.com
MAIL_PASSWORD=<16-char app password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=you@gmail.com
MAIL_FROM_NAME="Task365"
```

To create an App Password: Google Account → Security → turn on **2-Step Verification**,
then Security → **App passwords** → create one for Mail. Your normal Gmail password will
NOT work. Works with free Gmail and Google Workspace accounts.

Verify with `php artisan app:test-mail` (or `php artisan app:test-mail you@example.com`);
`php artisan app:doctor` also checks the mail configuration. Port 465 + SSL is supported
if your network blocks 587.

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

## Branding the public website (color, hero copy, screenshots)

All in one place - `config/brand.php` or `.env`:

- `BRAND_COLOR` - accent hex; the Tailwind indigo/purple palettes are
  re-derived from it on every public page (`<x-brand-head />`), so the whole
  site recolors from one value.
- `BRAND_COLOR_SECONDARY` - optional gradient partner (defaults to a purple mix).
- `BRAND_HERO_1` / `BRAND_HERO_2` / `BRAND_HERO_SUB` - homepage hero copy.
- Screenshots: drop real dashboard images into `public/screenshots/` and set
  `BRAND_DASHBOARD_SCREENSHOT`, `BRAND_SCREENSHOT_CLIENTS`, `BRAND_SCREENSHOT_TASKS`,
  `BRAND_SCREENSHOT_LEADS`, `BRAND_SCREENSHOT_FINANCE`. When the file exists it
  replaces the CSS mockup on the homepage; otherwise the mockup is shown.

## Local development notes (Windows / Laragon included)

- Use `APP_ENV=local` and `APP_DEBUG=true` while developing - with
  `APP_ENV=production`/`APP_DEBUG=false` error details are hidden and the
  `/dev/*` diagnostics are disabled.
- **Session cookie pitfalls (the classic "login keeps looping back" cause):**
  - `SESSION_SECURE_COOKIE` must be unset/`false` when testing over plain
    `http://127.0.0.1` - a secure cookie is never sent over HTTP, so the
    session never persists and every page acts logged-out.
  - `SESSION_DOMAIN` must be empty (host-only) when testing on `127.0.0.1` -
    a value like `yoursaas.com` scopes the cookie away from localhost.
  - `php artisan app:doctor` checks both and will tell you.
- **Diagnostics (when `APP_DEBUG=true`):**
  - `/dev/session` - session persistence probe (reload it: `probe_visits`
    must increment) + auth/tenant state summary.
  - `/dev/error` - renders the last exceptions from `storage/logs/laravel.log`.
- **Login events are logged** (`Login success` / `Login failed` / `Super admin
  login`) - see `storage/logs/laravel.log`, or the "Last login events" section
  of `php artisan app:doctor`.
- Team login only honors an intended URL inside the internal app; super-admin
  and portal logins use their own intended keys, so the three auth areas can
  never redirect each other into login loops.

## Free tools & blog content

- `/tools` - free agency calculators (retainer calculator, invoice due-date +
  GST calculator, proposal value calculator) with SEO meta; linked from nav.
- `php artisan db:seed --class=BlogSeeder` - seeds 4 published blog articles
  (onboarding checklist, retainer pricing, automation rules, client portals)
  across 4 categories so `/blog` and `/resources` have content.

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

## Public website & blog

- **Website (multi-page, SEO/AEO-rich):** landing `/`, `/pricing` (plans +
  comparison table + FAQ + testimonials), `/features` + 6 feature deep-dives
  (bullets + stats + FAQ), `/use-cases` + 6 use-case pages (challenges vs
  solutions + features + FAQ), `/integrations`, `/resources`, `/faq`
  (FAQPage schema), `/company`, `/contact`, and a **blog** at `/blog` with
  category pages and article pages.
- **Blog management (admin end):** Super Admin → Blog - create/edit/delete posts,
  categories, featured flag, cover images, publish scheduling.
- **SEO-ready:** per-page meta title/description, canonical URLs, Open Graph +
  Twitter cards, `robots.txt` and a dynamic **`/sitemap.xml`**.
- **JSON-LD structured data:** Organization + WebSite/SearchAction on the landing
  page, CollectionPage on blog listings, Article (headline, dates, author,
  publisher, image) on every post.

## Super Admin panel

- **URL:** `/super-admin/login` (route `super-admin.login`) - linked from the landing page footer.
- **Seed once:** `php artisan db:seed --class=SuperAdminSeeder` (also included in `php artisan db:seed`).
- **Credentials:** `SUPER_ADMIN_EMAIL` / `SUPER_ADMIN_PASSWORD` from `.env`
  (defaults: `superadmin@task365.test` / `ChangeMe123!` - change immediately after first login).
  Note: with `php artisan config:cache`, `env()` is unavailable at runtime, so the
  seeders fall back to those defaults - set the env vars before seeding.
- **Capabilities:** platform dashboard (tenants, MRR, signups, churn), tenant list/detail
  (usage, subscription history, extend trial, change plan, deactivate), plan CRUD,
  payments list, and **impersonation** of any tenant admin (exited via the purple
  "Exit impersonation" banner).
- **Auth:** fully separate - uses the `super_admins` table + `SuperAdminMiddleware`
  (session key `super_admin`), independent of team users and client portal accounts.

## Production notes

- Run the queue worker 24/7 via Supervisor: `php artisan queue:work database --tries=3`
  (see `deploy/supervisor.conf.example`).
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
