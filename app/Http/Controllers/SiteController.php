<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Plan;

/**
 * Multi-page marketing website with complete, SEO-rich content per page:
 * every page has hero + body sections + FAQ + CTA (3-4+ sections each).
 */
class SiteController extends Controller
{
    public function company()
    {
        return view('site.company');
    }

    // ------------------------------------------------------------------
    // Features hub + detail pages
    // ------------------------------------------------------------------

    public function features()
    {
        return view('site.features', ['all' => $this->featuresData()]);
    }

    public function feature(string $slug)
    {
        $features = $this->featuresData();

        if (! isset($features[$slug])) {
            abort(404);
        }

        return view('site.feature', [
            'feature' => $features[$slug],
            'slug' => $slug,
            'current' => $slug,
            'all' => $features,
        ]);
    }

    public function featuresData(): array
    {
        return [
            'client-management' => [
                'title' => 'Client Management',
                'icon' => 'users',
                'badge' => 'Client Success',
                'meta_description' => 'Client management software for agencies: onboarding checklists, health scores, retainers, contracts and a branded client portal. Start your free trial.',
                'section_title' => 'From signed to delivered in days, not weeks',
                'section_text' => 'The first 30 days decide the next 3 years of a client relationship. Task365 gives you a proven 20-step onboarding checklist, contract and retainer tracking, and a health score so every account manager knows exactly where each client stands.',
                'section_points' => ['Assign onboarding steps to teammates with due dates', 'Track retainers, contracts and renewal dates automatically', 'Give clients a branded portal with approvals and requests'],
                'excerpt' => 'Retainers, onboarding checklists, health scores and a branded client portal.',
                'description' => 'Track every client from onboarding to offboarding with contracts, retainers, health scores and a 20-step onboarding checklist. Your account managers always know what is happening.',
                'bullets' => ['20-step onboarding checklists', 'Client health scores with reasons', 'Contracts, retainers & GST details', 'Client portal with approvals & requests', 'System folders for every client'],
                'stats' => ['20 onboarding steps automated', '9 client detail tabs', '100% portal-ready clients'],
                'faq' => [
                    ['How do onboarding checklists work?', 'Every new client gets a 20-step checklist (welcome email, brand assets, Meta/Google/GA4 access, kickoff call, KPIs…). Assign each step to a teammate with a due date and track progress live.'],
                    ['Can my clients see their own data?', 'Yes - the optional client portal gives clients their own login with projects, shared reports, approvals, invoices and requests. They only ever see their own company data.'],
                    ['What is a health score?', 'A green/yellow/red flag you set per client, with a reason, so the whole team instantly knows which accounts need attention.'],
                ],
            ],
            'project-tasks' => [
                'title' => 'Projects & Tasks',
                'icon' => 'check-circle',
                'badge' => 'Delivery & Operations',
                'meta_description' => 'Project and task management for agencies: kanban boards, recurring tasks, checklists, approvals, time tracking and workload views. Free 14-day trial.',
                'section_title' => 'Ship work without the status meetings',
                'section_text' => 'Agencies lose hours every week to "where are we at?" updates. Task365 replaces them with a live kanban board, self-creating recurring tasks and workload views that show who is overloaded before things slip.',
                'section_points' => ['Drag-and-drop tasks between status columns', 'Recurring tasks create their next instance automatically', 'Checklists, subtasks, attachments and comments in one place'],
                'excerpt' => 'Kanban boards, recurring tasks, approvals, time tracking and workload views.',
                'description' => 'Organize client work into projects, seed them from templates, and run them on drag-and-drop kanban boards. Recurring tasks create themselves automatically.',
                'bullets' => ['Drag-and-drop kanban board', 'Recurring tasks with auto-instances', 'Subtasks, checklists & attachments', 'Task templates per service', 'Calendar & my-tasks views'],
                'stats' => ['7 task statuses', '5 project templates included', 'Auto-created recurring instances'],
                'faq' => [
                    ['Can I automate recurring work?', 'Yes - mark a task as recurring (daily/weekly/monthly/custom) and Task365 creates the next instance automatically on schedule.'],
                    ['How does team workload work?', 'The Team page shows every member\'s active tasks, overdue count, hours this week and a 7-day capacity grid so you can rebalance before things slip.'],
                    ['Are there templates?', 'Yes - 5 system templates (Digital Marketing Retainer, Shopify Operations, Social Media, Website Management, AI Automation) seed a project with its standard task list in one click.'],
                ],
            ],
            'leads-crm' => [
                'title' => 'Leads & CRM',
                'icon' => 'target',
                'badge' => 'Sales & Pipeline',
                'meta_description' => 'Agency CRM: Lead365 sync, Meta Ads capture, pipeline stages, auto-assignment, proposals with PDF and email, and one-click lead-to-client conversion.',
                'section_title' => 'Never lose another lead to the spreadsheet',
                'section_text' => 'Leads land from Lead365 webhooks and Meta Ads forms, flow through your pipeline automatically, and convert to full clients with onboarding in one click. Proposals go out as branded PDFs.',
                'section_points' => ['9 Lead365 webhook events handled automatically', 'Meta Ads leads create urgent call tasks', 'Won leads convert to clients with onboarding in 1 click'],
                'excerpt' => 'Pipeline, Lead365 sync, Meta Ads capture, proposals and win/loss tracking.',
                'description' => 'A real pipeline with drag-and-drop stages, source badges for Meta Ads / forms / Lead365, auto-assignment rules and one-click proposal generation.',
                'bullets' => ['Visual pipeline with stage totals', 'Lead365 webhook sync (9 events)', 'Meta Ads & form lead capture', 'Auto-assignment rules', 'Proposals with PDF & email'],
                'stats' => ['9 webhook events handled', '6 pipeline stages pre-built', 'Won-to-client conversion in 1 click'],
                'faq' => [
                    ['How does Lead365 sync work?', 'Lead365 posts webhook events (created, updated, stage changed, won, lost, form submitted, meta lead…) to your workspace. Task365 logs every payload, processes it in the background and updates your pipeline automatically.'],
                    ['Can I send proposals?', 'Yes - create a proposal from any lead or client, add line items, generate a branded PDF, email it to the client and track accepted/rejected status.'],
                    ['What happens when a lead is won?', 'You get notified (in-app, email, Slack/Teams) and can convert the lead into a full client with onboarding checklist, folders and project in one click.'],
                ],
            ],
            'finance-invoicing' => [
                'title' => 'Finance & Invoicing',
                'icon' => 'banknotes',
                'badge' => 'Money & Billing',
                'meta_description' => 'Agency invoicing and finance: GST invoices, BikriBook sync, expenses, profitability with margin colours, Razorpay billing. Free 14-day trial.',
                'section_title' => 'Get paid on time, every time',
                'section_text' => 'Invoices are saved locally first (so an integration outage never blocks you), then synced to BikriBook, sent to clients, and tracked until paid. The profitability page shows true margins per client.',
                'section_points' => ['Auto-numbered GST invoices with PDF preview', 'BikriBook sync with retry and full logs', 'Profitability: revenue vs team cost vs tools cost'],
                'excerpt' => 'BikriBook sync, GST invoices, expenses and per-client profitability.',
                'description' => 'Create GST-ready invoices locally first, sync to BikriBook, send to clients and track payments. Know exactly which clients are profitable.',
                'bullets' => ['GST invoices with auto-numbering', 'BikriBook sync with retry & logs', 'Expenses with receipt uploads', 'Profitability by client & margin', 'Razorpay-powered plans'],
                'stats' => ['GST-ready PDF invoices', '6-hour payment sync', 'Margin colours: green/yellow/red'],
                'faq' => [
                    ['How does BikriBook integration work?', 'Invoices are saved locally first (so a BikriBook outage never blocks you), then synced with one click. Payment status is checked automatically every 6 hours and your team is notified when a client pays.'],
                    ['Can I see profitability per client?', 'Yes - the Profitability page shows revenue, team cost (from tracked hours), tools cost, profit and margin % per client, with green (>40%), yellow (25-40%) and red (<25%) status.'],
                    ['How are invoices numbered?', 'Automatically as INV-YYYY-SEQ, with your own prefix and starting number from Settings.'],
                ],
            ],
            'reporting' => [
                'title' => 'Reporting',
                'icon' => 'chart-bar',
                'badge' => 'Client Reporting',
                'meta_description' => 'Agency reporting software: weekly and monthly client reports with auto-calculated CTR, ROAS, AOV and engagement metrics, branded PDFs and portal sharing.',
                'section_title' => 'Reports clients actually read',
                'section_text' => 'Enter raw numbers once and get auto-calculated metrics, a branded executive summary and a one-click PDF. Share it to the client portal and they get an email with a link.',
                'section_points' => ['CTR, ROAS, CPL, AOV auto-calculated', 'Branded PDFs with agency and client logos', 'Share to portal with one click'],
                'excerpt' => 'Weekly/monthly client reports with auto-calculated metrics and PDFs.',
                'description' => 'Build beautiful client reports in minutes: pick the period, enter metrics, and get auto-calculated CTR, ROAS, AOV and engagement rates with one-click PDF export.',
                'bullets' => ['4-step report builder', 'Auto-calculated marketing metrics', 'Branded PDFs with logos', 'Share with client portal', 'Report templates'],
                'stats' => ['Auto-calculated CTR/ROAS/CPL', '4 report types', '1-click branded PDF'],
                'faq' => [
                    ['What metrics are auto-calculated?', 'Paid ads: CTR, ROAS, CPL. Shopify: AOV, conversion rate. Social: follower growth, engagement rate. Website: sessions, bounce rate, goal completions.'],
                    ['Can clients see reports?', 'Yes - share a report to the client portal and they get an email with a link. They can also download the PDF.'],
                    ['Are reports branded?', 'Yes - PDFs use your agency and client logos with a clean executive-summary layout, metric tables and commentary sections.'],
                ],
            ],
            'automation' => [
                'title' => 'Automation',
                'icon' => 'bolt',
                'badge' => 'Automation',
                'meta_description' => 'Agency automation: no-code rules for overdue tasks, meta leads, invoices and contracts. Notifications, task creation and follow-ups on autopilot.',
                'section_title' => 'Your agency runs itself',
                'section_text' => 'Set a rule once and Task365 handles the follow-up: overdue task alerts, meta-lead call tasks, invoice reminders, contract expiry warnings. Every execution is logged.',
                'section_points' => ['14 trigger events with conditions and delays', 'Send notifications, emails or create tasks', 'Test any rule with a dry run before enabling'],
                'excerpt' => 'No-code rules: notifications, task creation and follow-ups on autopilot.',
                'description' => 'Set rules once and let Task365 handle the follow-up: overdue task alerts, meta-lead call tasks, invoice reminders, contract expiry warnings.',
                'bullets' => ['14 trigger events', 'Conditions & delayed actions', 'Send notifications or emails', 'Auto-create tasks & projects', 'Execution log for every rule'],
                'stats' => ['14 triggers', '8 action types', '5 rules pre-built'],
                'faq' => [
                    ['What can automation rules do?', 'Trigger on events like task overdue, lead won, invoice overdue, contract expiring - then notify someone, email a client, create a task or change a status. Conditions and delays are supported.'],
                    ['Are there ready-made rules?', 'Yes - 5 default rules ship with every workspace: overdue notify assignee, 3-day overdue notify manager, Meta lead follow-up task, overdue invoice email, contract expiry alert.'],
                    ['Can I test a rule?', 'Yes - every rule has a dry-run test against a chosen task, lead or client so you can verify conditions before enabling it.'],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Use-case hub + detail pages
    // ------------------------------------------------------------------

    public function useCases()
    {
        return view('site.use-cases', ['cases' => $this->useCasesData()]);
    }

    public function useCase(string $slug)
    {
        $cases = $this->useCasesData();

        if (! isset($cases[$slug])) {
            abort(404);
        }

        return view('site.use-case', ['case' => $cases[$slug], 'slug' => $slug, 'all' => $cases]);
    }

    public function useCasesData(): array
    {
        return [
            'digital-agency' => [
                'title' => 'For Digital Marketing Agencies',
                'icon' => 'megaphone',
                'blurb' => 'Campaigns, ad accounts, client reporting and retainers in one place.',
                'intro' => 'Marketing agencies juggle ad accounts, reporting decks, client calls and retainers. Task365 gives you one place to run campaigns, track deliverables and report results - so nothing falls through the cracks.',
                'challenges' => ['Reporting decks built by hand every month', 'Ad account access scattered across emails', 'No single view of client deliverables', 'Retainer scope creep going unnoticed'],
                'solutions' => ['Auto-calculated CTR, ROAS and CPL in branded PDF reports', 'Per-client service tracking with retainers and contracts', 'Kanban boards per campaign with due dates and owners', 'Profitability page showing margin per client'],
                'features' => ['client-management', 'reporting', 'finance-invoicing', 'leads-crm'],
                'faq' => [
                    ['How does this replace my reporting spreadsheets?', 'Enter ad spend, impressions, clicks and conversions once - CTR, ROAS and CPL are calculated automatically and the branded PDF is ready in one click.'],
                    ['Can my account managers see only their clients?', 'Yes - account managers get role-based access to exactly the clients assigned to them, with their tasks, reports and invoices.'],
                ],
            ],
            'creative-agency' => [
                'title' => 'For Creative & Design Agencies',
                'icon' => 'pencil-square',
                'blurb' => 'Briefs, revisions, approvals and deliverables without the email ping-pong.',
                'intro' => 'Design agencies live on email threads full of revisions and "final_v3_FINAL" files. Task365 moves briefs, feedback and approvals into one place your clients actually use.',
                'challenges' => ['Revisions lost in email chains', 'Clients asking for changes after sign-off', 'No record of what was approved', 'Deliverable files scattered in Google Drive'],
                'solutions' => ['Submit deliverables for client approval in the portal', 'Clients approve or request changes with notes on record', 'Versioned files with upload history', 'Comments and activity log on every task'],
                'features' => ['client-management', 'project-tasks', 'files', 'automation'],
                'faq' => [
                    ['How do client approvals work?', 'Submit any deliverable for approval - the client logs into their portal, reviews the file, and clicks Approve or Request changes. The decision is recorded and the task updates automatically.'],
                    ['Is there version control?', 'Yes - uploading a new version keeps the previous one in history, so you always have the full audit trail.'],
                ],
            ],
            'web-dev' => [
                'title' => 'For Web Development Agencies',
                'icon' => 'globe-alt',
                'blurb' => 'Sprints, client hosting access, maintenance retainers and support requests.',
                'intro' => 'Development agencies need structure: sprints, tickets, client approvals and maintenance retainers. Task365 gives your devs a clean task workflow and your clients a way to raise requests without Slack pings.',
                'challenges' => ['Client requests buried in chat', 'No sprint visibility for clients', 'Maintenance work not billed', 'Access credentials shared insecurely'],
                'solutions' => ['Client portal request forms that auto-create tasks', 'Project boards with statuses and progress bars', 'Retainer invoices synced to BikriBook', 'Secure file storage outside the webroot'],
                'features' => ['project-tasks', 'client-management', 'finance-invoicing', 'files'],
                'faq' => [
                    ['Can clients submit technical requests?', 'Yes - the portal has a request form (technical type) that creates an internal task automatically with priority.'],
                    ['How do I bill maintenance retainers?', 'Create a monthly retainer invoice, sync to BikriBook, and let the 6-hour payment check keep you updated.'],
                ],
            ],
            'consulting' => [
                'title' => 'For Consultants & Coaches',
                'icon' => 'briefcase',
                'blurb' => 'Proposals, onboarding, deliverables and billing for every engagement.',
                'intro' => 'Consultants sell outcomes, not hours. Task365 handles the proposal-to-delivery-to-invoice lifecycle so you can focus on the engagement.',
                'challenges' => ['Proposals built in Word documents', 'No formal client onboarding', 'Deliverables delivered by email with no trail', 'Invoices chased manually'],
                'solutions' => ['Proposals with PDF and email from any lead', 'Onboarding checklists for every engagement', 'Client portal for deliverable sign-off', 'Invoice overdue reminders automated'],
                'features' => ['leads-crm', 'client-management', 'finance-invoicing', 'reporting'],
                'faq' => [
                    ['How fast can I send a proposal?', 'From any lead, create a proposal with line items, generate the branded PDF and email it to the client - all within a couple of minutes.'],
                    ['Do clients get a portal?', 'Yes - optional portal access gives clients their own login for approvals, reports, invoices and requests.'],
                ],
            ],
            'saas-agency' => [
                'title' => 'For SaaS & Product Agencies',
                'icon' => 'rocket-launch',
                'blurb' => 'Feature requests, QA tasks, releases and client approvals.',
                'intro' => 'Product agencies run on roadmaps, QA loops and release notes. Task365 keeps the delivery side organized while clients stay in the loop through the portal.',
                'challenges' => ['Feature requests scattered everywhere', 'QA tasks without owners or due dates', 'Client sign-off not tracked', 'Release scope creeping'],
                'solutions' => ['Request pipeline with priorities and auto-task creation', 'QA checklists inside every task', 'Approval workflow with recorded decisions', 'Project progress visible to clients'],
                'features' => ['project-tasks', 'automation', 'client-management', 'reporting'],
                'faq' => [
                    ['Can I track approvals per release?', 'Yes - submit each release deliverable for client approval and keep the record of what was signed off.'],
                    ['Does it handle recurring QA?', 'Yes - recurring tasks (daily/weekly) create themselves, so sprint QA never gets forgotten.'],
                ],
            ],
            'freelancers' => [
                'title' => 'For Freelancers Going Pro',
                'icon' => 'user',
                'blurb' => 'One organized workspace that looks and works like a real agency.',
                'intro' => 'You are a one-person agency. Task365 gives you the same tools the big agencies use - pipeline, proposals, invoicing, client portal - so you can charge like one.',
                'challenges' => ['Clients asking "where are we at?" constantly', 'Invoices sent as PDFs by email, chased manually', 'No pipeline for new work', 'No record of what was promised'],
                'solutions' => ['Client portal with live project progress', 'GST invoices + BikriBook sync + overdue reminders', 'Simple lead pipeline with proposals', 'Activity log on every client'],
                'features' => ['leads-crm', 'finance-invoicing', 'client-management', 'project-tasks'],
                'faq' => [
                    ['Is it affordable for a freelancer?', 'The Starter plan is built for solo operators - up to 5 users and 5 clients with the full feature set. Start with the 14-day free trial.'],
                    ['Can I give clients a login?', 'Yes - the portal is per client and free. Clients see only their own projects, reports, invoices and approvals.'],
                ],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Static pages
    // ------------------------------------------------------------------

    public function integrations()
    {
        return view('site.integrations');
    }

    public function resources()
    {
        $posts = BlogPost::published()->with('categories')->latest('published_at')->limit(6)->get();

        return view('site.resources', compact('posts'));
    }

    public function faq()
    {
        return view('site.faq');
    }
}
