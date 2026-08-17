<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientOnboardingItem;
use App\Models\ClientService;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FileFolder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\KbArticle;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Local development only: a demo agency with team, clients, projects, tasks,
 * leads, invoices, KB articles and time entries.
 */
class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Demo Agency',
                'slug' => 'demo',
                'email' => 'admin@demo.com',
                'phone' => '+91 98765 43210',
                'address' => '91 Springboard, Koramangala, Bengaluru',
                'is_trial' => true,
                'is_active' => true,
                'trial_ends_at' => now()->addDays(12),
                'max_users' => 15,
                'max_clients' => 20,
                'settings' => [
                    'timezone' => 'Asia/Kolkata',
                    'currency' => 'INR',
                    'date_format' => 'd M Y',
                    'invoice_prefix' => 'INV',
                    'invoice_start_number' => 1001,
                    'payment_terms_days' => 15,
                    'tax_rate' => 18,
                    'bank_name' => 'HDFC Bank',
                    'bank_account_number' => '50200012345678',
                    'bank_ifsc' => 'HDFC0001234',
                    'bank_beneficiary' => 'Demo Agency Pvt Ltd',
                    'invoice_footer' => 'Thank you for your business!',
                ],
            ]
        );

        // Admin
        $admin = User::withoutGlobalScopes()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'admin@demo.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Aarav Sharma',
                'email' => 'admin@demo.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'designation' => 'Founder',
                'hourly_cost' => 1500,
            ]
        );

        // Team
        $team = [
            ['name' => 'Priya Nair', 'email' => 'priya@demo.com', 'role' => 'ops_manager', 'designation' => 'Operations Manager', 'hourly_cost' => 1000],
            ['name' => 'Rohan Mehta', 'email' => 'rohan@demo.com', 'role' => 'account_manager', 'designation' => 'Account Manager', 'hourly_cost' => 800],
            ['name' => 'Sneha Kulkarni', 'email' => 'sneha@demo.com', 'role' => 'specialist', 'designation' => 'Performance Marketer', 'hourly_cost' => 600],
        ];

        $users = [$admin];
        foreach ($team as $member) {
            $user = User::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'email' => $member['email']],
                $member + [
                    'tenant_id' => $tenant->id,
                    'password' => Hash::make('password123'),
                ]
            );
            $users[] = $user;
        }

        // Default data
        (new DefaultDataSeeder)->run($tenant->id, $admin->id);

        // Clients
        $clients = [
            [
                'company_name' => 'UrbanKart India',
                'industry' => 'E-commerce',
                'website' => 'https://urbankart.in',
                'gstin' => '29ABCDE1234F1Z5',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'country' => 'India',
                'status' => 'active',
                'health_score' => 'green',
                'monthly_retainer' => 85000,
                'account_manager_id' => $users[2]->id,
                'services' => ['digital_marketing', 'shopify_operations'],
                'contact' => ['name' => 'Kavita Joshi', 'email' => 'kavita@urbankart.in', 'phone' => '+91 98200 12345', 'designation' => 'Founder', 'is_primary' => true, 'is_billing_contact' => true],
            ],
            [
                'company_name' => 'WellNest Clinics',
                'industry' => 'Healthcare',
                'website' => 'https://wellnest.in',
                'gstin' => '27ABCDE5678F1Z9',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'country' => 'India',
                'status' => 'active',
                'health_score' => 'yellow',
                'monthly_retainer' => 45000,
                'account_manager_id' => $users[2]->id,
                'services' => ['social_media', 'website_management'],
                'contact' => ['name' => 'Dr. Amit Deshpande', 'email' => 'amit@wellnest.in', 'phone' => '+91 98220 54321', 'designation' => 'Director', 'is_primary' => true, 'is_billing_contact' => true],
            ],
            [
                'company_name' => 'FoodieExpress',
                'industry' => 'Food & Beverage',
                'website' => 'https://foodieexpress.in',
                'status' => 'onboarding',
                'health_score' => 'green',
                'monthly_retainer' => 30000,
                'account_manager_id' => $users[1]->id,
                'services' => ['digital_marketing', 'social_media', 'ai_automation'],
                'contact' => ['name' => 'Nikhil Verma', 'email' => 'nikhil@foodieexpress.in', 'phone' => '+91 99870 11223', 'designation' => 'CEO', 'is_primary' => true],
            ],
        ];

        $clientModels = [];
        foreach ($clients as $data) {
            $contact = $data['contact'];
            unset($data['contact'], $data['services']);

            $client = Client::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'company_name' => $data['company_name']],
                $data + ['tenant_id' => $tenant->id]
            );

            ClientContact::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'client_id' => $client->id, 'email' => $contact['email']],
                $contact + ['tenant_id' => $tenant->id, 'client_id' => $client->id]
            );

            foreach ($data['services'] ?? [] as $service) {
                ClientService::withoutGlobalScopes()->firstOrCreate(
                    ['tenant_id' => $tenant->id, 'client_id' => $client->id, 'service_type' => $service],
                    ['tenant_id' => $tenant->id, 'client_id' => $client->id, 'service_type' => $service]
                );
            }

            $clientModels[] = $client;
        }

        // Onboarding items for the onboarding client
        $foodie = $clientModels[2];
        $onboardingTitles = [
            'Send welcome email', 'Collect brand assets', 'Get Meta access', 'Get Google Ads access',
            'Get GA4 access', 'Get Shopify access', 'Get website/CMS access', 'Get social media access',
            'Get email marketing access', 'Review existing campaigns', 'Review analytics', 'Competitor research',
            'Schedule kickoff call', 'Conduct kickoff call', 'Document goals and KPIs', 'Create 30-60-90 day plan',
            'Assign team members', 'Set recurring tasks', 'Set up reporting', 'Send communication guidelines',
        ];
        foreach ($onboardingTitles as $index => $title) {
            ClientOnboardingItem::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'client_id' => $foodie->id, 'title' => $title],
                ['tenant_id' => $tenant->id, 'client_id' => $foodie->id, 'title' => $title, 'order_index' => $index, 'assigned_to' => $admin->id, 'due_date' => now()->addDays($index % 20)->toDateString()]
            );
        }

        // System folders
        $systemFolders = ['01_Onboarding', '02_Strategy', '03_Campaigns', '04_Creatives', '05_Reports', '06_Shopify', '07_Meetings'];
        foreach ($systemFolders as $index => $folder) {
            FileFolder::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'client_id' => $clientModels[0]->id, 'name' => $folder],
                ['tenant_id' => $tenant->id, 'client_id' => $clientModels[0]->id, 'name' => $folder, 'path' => 'clients/'.$clientModels[0]->id.'/'.$folder, 'is_system_folder' => true, 'created_by' => $admin->id]
            );
        }

        // Projects
        $projects = [
            ['name' => 'Q3 Performance Marketing', 'client' => 0, 'service_type' => 'digital_marketing', 'status' => 'active'],
            ['name' => 'Shopify Store Optimisation', 'client' => 0, 'service_type' => 'shopify_operations', 'status' => 'active'],
            ['name' => 'Social Growth Sprint', 'client' => 1, 'service_type' => 'social_media', 'status' => 'active'],
            ['name' => 'Website Revamp', 'client' => 1, 'service_type' => 'website_management', 'status' => 'on_hold'],
        ];

        $projectModels = [];
        foreach ($projects as $data) {
            $client = $clientModels[$data['client']];
            $project = Project::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'client_id' => $client->id, 'name' => $data['name']],
                [
                    'tenant_id' => $tenant->id,
                    'client_id' => $client->id,
                    'name' => $data['name'],
                    'description' => 'Demo project for '.$client->company_name,
                    'status' => $data['status'],
                    'service_type' => $data['service_type'],
                    'start_date' => now()->subDays(20)->toDateString(),
                    'end_date' => now()->addDays(70)->toDateString(),
                    'created_by' => $admin->id,
                ]
            );
            $projectModels[] = $project;
        }

        // Tasks
        $taskDefs = [
            ['title' => 'Audit Google Ads account structure', 'project' => 0, 'priority' => 'high', 'status' => 'in_progress', 'assignee' => 3, 'due' => 0],
            ['title' => 'Launch new Meta campaign - Diwali sale', 'project' => 0, 'priority' => 'urgent', 'status' => 'todo', 'assignee' => 3, 'due' => 1],
            ['title' => 'Weekly performance review deck', 'project' => 0, 'priority' => 'medium', 'status' => 'todo', 'assignee' => 2, 'due' => 2],
            ['title' => 'Fix broken collections on storefront', 'project' => 1, 'priority' => 'high', 'status' => 'in_review', 'assignee' => 3, 'due' => -1],
            ['title' => 'Update product descriptions (top 20 SKUs)', 'project' => 1, 'priority' => 'medium', 'status' => 'todo', 'assignee' => 3, 'due' => 3],
            ['title' => 'Content calendar for August', 'project' => 2, 'priority' => 'high', 'status' => 'done', 'assignee' => 3, 'due' => -3],
            ['title' => 'Draft Instagram carousel - clinic tour', 'project' => 2, 'priority' => 'medium', 'status' => 'todo', 'assignee' => 3, 'due' => 1],
            ['title' => 'Homepage hero copy and CTA review', 'project' => 3, 'priority' => 'low', 'status' => 'blocked', 'assignee' => 2, 'due' => 5],
        ];

        foreach ($taskDefs as $def) {
            $project = $projectModels[$def['project']];
            Task::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'project_id' => $project->id, 'title' => $def['title']],
                [
                    'tenant_id' => $tenant->id,
                    'client_id' => $project->client_id,
                    'project_id' => $project->id,
                    'title' => $def['title'],
                    'description' => 'Demo task seeded for '.$project->client->company_name,
                    'status' => $def['status'],
                    'priority' => $def['priority'],
                    'service_type' => $project->service_type,
                    'task_type' => 'one_time',
                    'assigned_to' => $users[$def['assignee']]->id,
                    'created_by' => $admin->id,
                    'due_date' => now()->addDays($def['due'])->toDateString(),
                    'estimated_hours' => 2,
                ]
            );
        }

        // Leads
        $leadDefs = [
            ['contact_name' => 'Rakesh Gupta', 'company_name' => 'Gupta Textiles', 'email' => 'rakesh@guptatextiles.com', 'source_type' => 'meta_ads', 'lead_source' => 'Meta Ads', 'stage' => 'Discovery', 'value' => 120000, 'probability' => 40, 'assignee' => 2],
            ['contact_name' => 'Meera Iyer', 'company_name' => 'Iyer Interiors', 'email' => 'meera@iyerinteriors.in', 'source_type' => 'form_submission', 'lead_source' => 'Website Form', 'stage' => 'Proposal Sent', 'value' => 60000, 'probability' => 60, 'assignee' => 2],
            ['contact_name' => 'Arjun Reddy', 'company_name' => 'Reddy Bros', 'email' => 'arjun@reddybros.in', 'source_type' => 'lead365', 'lead_source' => 'Lead365', 'stage' => 'Negotiation', 'value' => 200000, 'probability' => 70, 'assignee' => 1],
            ['contact_name' => 'Sana Khan', 'company_name' => 'Sana Beauty', 'email' => 'sana@sanabeauty.in', 'source_type' => 'manual', 'lead_source' => 'Referral', 'stage' => 'New Lead', 'value' => 45000, 'probability' => 20, 'assignee' => 1],
        ];

        foreach ($leadDefs as $def) {
            $stage = \App\Models\LeadPipelineStage::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('name', $def['stage'])->first();
            Lead::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'contact_name' => $def['contact_name']],
                [
                    'tenant_id' => $tenant->id,
                    'contact_name' => $def['contact_name'],
                    'company_name' => $def['company_name'],
                    'email' => $def['email'],
                    'source_type' => $def['source_type'],
                    'lead_source' => $def['lead_source'],
                    'current_stage' => $def['stage'],
                    'stage_id' => $stage?->id,
                    'estimated_value' => $def['value'],
                    'probability' => $def['probability'],
                    'status' => 'active',
                    'assigned_to' => $users[$def['assignee']]->id,
                    'expected_close_date' => now()->addDays(21)->toDateString(),
                ]
            );
        }

        // Invoices
        $invoiceDefs = [
            ['client' => 0, 'status' => 'paid', 'amount' => 85000, 'days_ago' => 20, 'paid' => true],
            ['client' => 0, 'status' => 'sent', 'amount' => 85000, 'days_ago' => 5, 'paid' => false],
            ['client' => 1, 'status' => 'sent', 'amount' => 45000, 'days_ago' => 25, 'paid' => false],
            ['client' => 1, 'status' => 'overdue', 'amount' => 45000, 'days_ago' => 40, 'paid' => false],
        ];

        $startNumber = 1001;
        foreach ($invoiceDefs as $index => $def) {
            $client = $clientModels[$def['client']];
            $number = 'INV-'.now()->year.'-'.$startNumber++;

            $invoice = Invoice::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'invoice_number' => $number],
                [
                    'tenant_id' => $tenant->id,
                    'client_id' => $client->id,
                    'invoice_number' => $number,
                    'status' => $def['status'],
                    'issue_date' => now()->subDays($def['days_ago'])->toDateString(),
                    'due_date' => now()->subDays($def['days_ago'] - 15)->toDateString(),
                    'subtotal' => $def['amount'],
                    'tax_rate' => 18,
                    'tax_amount' => round($def['amount'] * 0.18, 2),
                    'total_amount' => round($def['amount'] * 1.18, 2),
                    'paid_amount' => $def['paid'] ? round($def['amount'] * 1.18, 2) : 0,
                    'currency' => 'INR',
                    'payment_date' => $def['paid'] ? now()->subDays($def['days_ago'] - 5) : null,
                    'payment_method' => $def['paid'] ? 'Bank Transfer' : null,
                    'sent_at' => now()->subDays($def['days_ago'] - 2),
                    'created_by' => $admin->id,
                ]
            );

            InvoiceItem::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'invoice_id' => $invoice->id, 'description' => 'Monthly retainer - '.$client->company_name],
                [
                    'tenant_id' => $tenant->id,
                    'invoice_id' => $invoice->id,
                    'description' => 'Monthly retainer - '.$client->company_name,
                    'quantity' => 1,
                    'unit_price' => $def['amount'],
                    'tax_rate' => 18,
                    'tax_amount' => round($def['amount'] * 0.18, 2),
                    'total' => round($def['amount'] * 1.18, 2),
                ]
            );
        }

        // Expenses
        $toolsCategory = ExpenseCategory::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('name', 'Software/Tools')->first();
        Expense::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'category_id' => $toolsCategory?->id,
            'title' => 'Meta Ads Manager spend - UrbanKart',
            'amount' => 25000,
            'expense_date' => now()->subDays(3)->toDateString(),
            'is_billable' => true,
            'added_by' => $admin->id,
        ]);

        // Time entries
        TimeEntry::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'task_id' => Task::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first()?->id,
            'project_id' => $projectModels[0]->id,
            'client_id' => $clientModels[0]->id,
            'user_id' => $users[3]->id,
            'description' => 'Campaign audit work',
            'started_at' => now()->subHours(3),
            'ended_at' => now()->subHours(2),
            'duration_minutes' => 60,
            'is_billable' => true,
        ]);

        // KB article
        $dmCategory = KbCategory::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('name', 'Digital Marketing')->first();
        if ($dmCategory && ! KbArticle::withoutGlobalScopes()->where('tenant_id', $tenant->id)->exists()) {
            KbArticle::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'category_id' => $dmCategory->id,
                'title' => 'Google Ads Account Audit Checklist',
                'slug' => 'google-ads-account-audit-checklist',
                'content' => '<h2>Structure</h2><p>Check campaign hierarchy, ad groups and keywords.</p><h2>Budget</h2><p>Review daily budgets and pacing.</p><h2>Quality Score</h2><p>Review keyword quality scores and ad relevance.</p>',
                'status' => 'published',
                'visibility' => 'all',
                'is_featured' => true,
                'created_by' => $admin->id,
                'published_at' => now()->subDays(10),
            ]);
        }

        $this->command?->info('Demo tenant seeded: demo.'.config('tenancy.tenant_domain').' (admin@demo.com / password123)');
    }
}
