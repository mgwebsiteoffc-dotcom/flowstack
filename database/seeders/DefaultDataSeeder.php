<?php

namespace Database\Seeders;

use App\Models\AutomationRule;
use App\Models\ExpenseCategory;
use App\Models\KbCategory;
use App\Models\LeadPipelineStage;
use App\Models\ProjectTemplate;
use App\Models\ProjectTemplateTask;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

/**
 * Seeds the default data every new tenant needs. Called from registration and
 * from DemoTenantSeeder - NOT from DatabaseSeeder directly.
 */
class DefaultDataSeeder extends Seeder
{
    public function run(int $tenantId, ?int $createdBy = null): void
    {
        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            return;
        }

        $this->pipelineStages($tenantId);
        $this->expenseCategories($tenantId);
        $this->kbCategories($tenantId);
        $this->projectTemplates($tenantId, $createdBy);
        $this->automationRules($tenantId, $createdBy);
        $this->internalFolders($tenantId, $createdBy);
    }

    /**
     * Internal (non-client) folders: /Internal/SOPs, Templates, Finance, Hiring.
     */
    protected function internalFolders(int $tenantId, ?int $createdBy): void
    {
        foreach (['SOPs', 'Templates', 'Finance', 'Hiring'] as $folder) {
            \App\Models\FileFolder::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenantId, 'client_id' => null, 'name' => $folder],
                [
                    'tenant_id' => $tenantId,
                    'client_id' => null,
                    'name' => $folder,
                    'path' => 'internal/'.$folder,
                    'is_system_folder' => true,
                    'created_by' => $createdBy,
                ]
            );
        }
    }

    protected function pipelineStages(int $tenantId): void
    {
        $stages = [
            ['name' => 'New Lead', 'color' => '#3B82F6', 'order_index' => 1],
            ['name' => 'Discovery', 'color' => '#8B5CF6', 'order_index' => 2],
            ['name' => 'Proposal Sent', 'color' => '#F59E0B', 'order_index' => 3],
            ['name' => 'Negotiation', 'color' => '#EC4899', 'order_index' => 4],
            ['name' => 'Won', 'color' => '#10B981', 'order_index' => 5, 'is_won_stage' => true],
            ['name' => 'Lost', 'color' => '#EF4444', 'order_index' => 6, 'is_lost_stage' => true],
        ];

        foreach ($stages as $stage) {
            LeadPipelineStage::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $stage['name']],
                $stage
            );
        }
    }

    protected function expenseCategories(int $tenantId): void
    {
        $categories = [
            ['name' => 'Software/Tools', 'color' => '#3B82F6'],
            ['name' => 'Advertising', 'color' => '#F59E0B'],
            ['name' => 'Contractor', 'color' => '#8B5CF6'],
            ['name' => 'Office', 'color' => '#10B981'],
            ['name' => 'Marketing', 'color' => '#EC4899'],
            ['name' => 'Miscellaneous', 'color' => '#6B7280'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $category['name']],
                $category
            );
        }
    }

    protected function kbCategories(int $tenantId): void
    {
        $categories = [
            ['name' => 'Digital Marketing', 'icon' => '📈', 'color' => '#3B82F6', 'order_index' => 1],
            ['name' => 'Shopify Operations', 'icon' => '🛍️', 'color' => '#10B981', 'order_index' => 2],
            ['name' => 'Social Media', 'icon' => '📱', 'color' => '#EC4899', 'order_index' => 3],
            ['name' => 'Website Management', 'icon' => '🌐', 'color' => '#8B5CF6', 'order_index' => 4],
            ['name' => 'AI Automation', 'icon' => '🤖', 'color' => '#F59E0B', 'order_index' => 5],
            ['name' => 'Client Management', 'icon' => '🤝', 'color' => '#06B6D4', 'order_index' => 6],
            ['name' => 'Team and HR', 'icon' => '👥', 'color' => '#F97316', 'order_index' => 7],
            ['name' => 'AI Prompts Library', 'icon' => '✨', 'color' => '#6366F1', 'order_index' => 8],
        ];

        foreach ($categories as $category) {
            KbCategory::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $category['name']],
                $category
            );
        }
    }

    protected function projectTemplates(int $tenantId, ?int $createdBy): void
    {
        $templates = [
            'Digital Marketing Retainer' => [
                'service_type' => 'digital_marketing',
                'tasks' => [
                    ['Campaign Setup', 'Set up / configure ad campaigns', 'high', 4],
                    ['Weekly Performance Review', 'Review campaign KPIs and optimise', 'medium', 2],
                    ['Monthly Report', 'Compile monthly performance report', 'medium', 3],
                    ['A/B Test Tracking', 'Track and analyse A/B test results', 'low', 2],
                    ['Creative Refresh', 'Refresh ad creatives', 'medium', 3],
                ],
            ],
            'Shopify Operations' => [
                'service_type' => 'shopify_operations',
                'tasks' => [
                    ['Weekly Store Audit', 'Full store health check', 'high', 2],
                    ['Product Management', 'Add/update products, variants, pricing', 'medium', 3],
                    ['Order Review', 'Review pending orders and fulfilment', 'high', 1],
                    ['App Management', 'Review installed apps and subscriptions', 'low', 1],
                    ['Monthly SEO Review', 'SEO health check and fixes', 'medium', 2],
                ],
            ],
            'Social Media Management' => [
                'service_type' => 'social_media',
                'tasks' => [
                    ['Monthly Content Calendar', 'Plan monthly content calendar', 'high', 3],
                    ['Weekly Content Creation', 'Create weekly posts and assets', 'high', 4],
                    ['Weekly Scheduling', 'Schedule posts across platforms', 'medium', 2],
                    ['Community Management', 'Respond to comments and DMs', 'medium', 5],
                    ['Monthly Analytics Report', 'Compile engagement analytics', 'medium', 2],
                ],
            ],
            'Website Management' => [
                'service_type' => 'website_management',
                'tasks' => [
                    ['Weekly Updates', 'Apply plugin/theme/core updates', 'high', 2],
                    ['Speed Check', 'Measure and improve page speed', 'medium', 1],
                    ['SEO Audit', 'Technical SEO health check', 'medium', 2],
                    ['Security Check', 'Scan for malware and harden security', 'high', 2],
                    ['Monthly Review', 'Monthly site review with client', 'medium', 1],
                ],
            ],
            'AI Automation Project' => [
                'service_type' => 'ai_automation',
                'tasks' => [
                    ['Requirement Gathering', 'Workshop to capture automation requirements', 'high', 4],
                    ['Workflow Mapping', 'Map current and target workflows', 'high', 3],
                    ['Build Phase', 'Build the automation workflows', 'high', 10],
                    ['Testing', 'Test all scenarios and edge cases', 'high', 4],
                    ['Go Live', 'Deploy automation to production', 'urgent', 2],
                    ['Documentation', 'Document workflows and handover', 'medium', 2],
                ],
            ],
        ];

        foreach ($templates as $name => $config) {
            $template = ProjectTemplate::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $name],
                [
                    'tenant_id' => $tenantId,
                    'name' => $name,
                    'description' => 'System template: '.$name,
                    'service_type' => $config['service_type'],
                    'is_system_template' => true,
                    'created_by' => $createdBy,
                ]
            );

            foreach ($config['tasks'] as $index => [$taskName, $description, $priority, $hours]) {
                ProjectTemplateTask::firstOrCreate(
                    ['template_id' => $template->id, 'title' => $taskName],
                    [
                        'template_id' => $template->id,
                        'title' => $taskName,
                        'description' => $description,
                        'task_type' => 'one_time',
                        'default_priority' => $priority,
                        'estimated_hours' => $hours,
                        'order_index' => $index,
                    ]
                );
            }
        }
    }

    protected function automationRules(int $tenantId, ?int $createdBy): void
    {
        $rules = [
            [
                'name' => 'Task Overdue - Notify Assignee',
                'trigger_event' => 'task.overdue',
                'trigger_delay_hours' => 0,
                'conditions' => [],
                'actions' => [
                    ['type' => 'send_notification', 'params' => ['target' => 'assigned_to', 'message' => '⏰ Task "[task_title]" is overdue!']],
                ],
            ],
            [
                'name' => 'Task Overdue 3 Days - Notify Manager',
                'trigger_event' => 'task.overdue',
                'trigger_delay_hours' => 72,
                'conditions' => [],
                'actions' => [
                    ['type' => 'send_notification', 'params' => ['target' => 'client_account_manager', 'message' => '⚠️ Task "[task_title]" has been overdue for 3 days.']],
                ],
            ],
            [
                'name' => 'Meta Lead - Create Follow-up Task',
                'trigger_event' => 'lead.meta_received',
                'trigger_delay_hours' => 0,
                'conditions' => [],
                'actions' => [
                    ['type' => 'create_task', 'params' => ['title' => 'Call [lead_name] - Meta Lead', 'priority' => 'urgent', 'due_days' => 0, 'assignee' => 'assigned_to']],
                ],
            ],
            [
                'name' => 'Invoice Overdue - Email Client',
                'trigger_event' => 'invoice.overdue',
                'trigger_delay_hours' => 0,
                'conditions' => [],
                'actions' => [
                    ['type' => 'send_email', 'params' => ['target' => 'client_account_manager', 'subject' => 'Invoice overdue', 'message' => 'Invoice is overdue - please follow up with the client.']],
                ],
            ],
            [
                'name' => 'Contract Expiring - Alert Admin',
                'trigger_event' => 'contract.expiring',
                'trigger_delay_hours' => 0,
                'conditions' => [],
                'actions' => [
                    ['type' => 'send_notification', 'params' => ['target' => 'role', 'role' => 'admin', 'message' => '⚠️ A client contract is expiring within 30 days.']],
                ],
            ],
        ];

        foreach ($rules as $rule) {
            AutomationRule::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $rule['name']],
                $rule + ['tenant_id' => $tenantId, 'created_by' => $createdBy]
            );
        }
    }
}
