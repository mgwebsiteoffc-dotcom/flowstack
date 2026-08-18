<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Plan;

/**
 * Multi-page marketing website (We360-style): company, feature deep-dives,
 * use cases, resources, integrations, FAQ, contact.
 */
class SiteController extends Controller
{
    public function company()
    {
        return view('site.company');
    }

    public function feature(string $slug)
    {
        $features = $this->features();

        if (! isset($features[$slug])) {
            abort(404);
        }

        return view('site.feature', [
            'feature' => $features[$slug],
            'slug' => $slug,
            'all' => $features,
        ]);
    }

    public function features()
    {
        return [
            'client-management' => [
                'title' => 'Client Management',
                'icon' => 'users',
                'excerpt' => 'Retainers, onboarding checklists, health scores and a branded client portal.',
                'description' => 'Track every client from onboarding to offboarding with contracts, retainers, health scores and a 20-step onboarding checklist. Your account managers always know what is happening.',
                'bullets' => ['20-step onboarding checklists', 'Client health scores with reasons', 'Contracts, retainers & GST details', 'Client portal with approvals & requests', 'System folders for every client'],
            ],
            'project-tasks' => [
                'title' => 'Projects & Tasks',
                'icon' => 'check-circle',
                'excerpt' => 'Kanban boards, recurring tasks, approvals, time tracking and workload views.',
                'description' => 'Organize client work into projects, seed them from templates, and run them on drag-and-drop kanban boards. Recurring tasks create themselves automatically.',
                'bullets' => ['Drag-and-drop kanban board', 'Recurring tasks with auto-instances', 'Subtasks, checklists & attachments', 'Task templates per service', 'Calendar & my-tasks views'],
            ],
            'leads-crm' => [
                'title' => 'Leads & CRM',
                'icon' => 'target',
                'excerpt' => 'Pipeline, Lead365 sync, Meta Ads capture, proposals and win/loss tracking.',
                'description' => 'A real pipeline with drag-and-drop stages, source badges for Meta Ads / forms / Lead365, auto-assignment rules and one-click proposal generation.',
                'bullets' => ['Visual pipeline with stage totals', 'Lead365 webhook sync (9 events)', 'Meta Ads & form lead capture', 'Auto-assignment rules', 'Proposals with PDF & email'],
            ],
            'finance-invoicing' => [
                'title' => 'Finance & Invoicing',
                'icon' => 'banknotes',
                'excerpt' => 'BikriBook sync, GST invoices, expenses and per-client profitability.',
                'description' => 'Create GST-ready invoices locally first, sync to BikriBook, send to clients and track payments. Know exactly which clients are profitable.',
                'bullets' => ['GST invoices with auto-numbering', 'BikriBook sync with retry & logs', 'Expenses with receipt uploads', 'Profitability by client & margin', 'Razorpay-powered plans'],
            ],
            'reporting' => [
                'title' => 'Reporting',
                'icon' => 'chart-bar',
                'excerpt' => 'Weekly/monthly client reports with auto-calculated metrics and PDFs.',
                'description' => 'Build beautiful client reports in minutes: pick the period, enter metrics, and get auto-calculated CTR, ROAS, AOV and engagement rates with one-click PDF export.',
                'bullets' => ['4-step report builder', 'Auto-calculated marketing metrics', 'Branded PDFs with logos', 'Share with client portal', 'Report templates'],
            ],
            'automation' => [
                'title' => 'Automation',
                'icon' => 'bolt',
                'excerpt' => 'No-code rules: notifications, task creation and follow-ups on autopilot.',
                'description' => 'Set rules once and let Agency OS handle the follow-up: overdue task alerts, meta-lead call tasks, invoice reminders, contract expiry warnings.',
                'bullets' => ['14 trigger events', 'Conditions & delayed actions', 'Send notifications or emails', 'Auto-create tasks & projects', 'Execution log for every rule'],
            ],
        ];
    }

    public function useCases()
    {
        return [
            'digital-agency' => ['title' => 'For Digital Marketing Agencies', 'icon' => 'megaphone', 'blurb' => 'Campaigns, ad accounts, client reporting and retainers in one place.'],
            'creative-agency' => ['title' => 'For Creative & Design Agencies', 'icon' => 'pencil-square', 'blurb' => 'Briefs, revisions, approvals and deliverables without the email ping-pong.'],
            'web-dev' => ['title' => 'For Web Development Agencies', 'icon' => 'globe-alt', 'blurb' => 'Sprints, client hosting access, maintenance retainers and support requests.'],
            'consulting' => ['title' => 'For Consultants & Coaches', 'icon' => 'briefcase', 'blurb' => 'Proposals, onboarding, deliverables and billing for every engagement.'],
            'saas-agency' => ['title' => 'For SaaS & Product Agencies', 'icon' => 'rocket-launch', 'blurb' => 'Feature requests, QA tasks, releases and client approvals.'],
            'freelancers' => ['title' => 'For Freelancers Going Pro', 'icon' => 'user', 'blurb' => 'One organized workspace that looks and works like a real agency.'],
        ];
    }

    public function useCase(string $slug)
    {
        $cases = $this->useCases();

        if (! isset($cases[$slug])) {
            abort(404);
        }

        return view('site.use-case', ['case' => $cases[$slug], 'slug' => $slug]);
    }

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
