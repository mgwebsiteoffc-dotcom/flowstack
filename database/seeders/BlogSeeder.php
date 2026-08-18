<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Agency Growth', 'Client Management', 'Productivity', 'Finance & Billing',
        ];
        $catIds = [];
        foreach ($categories as $name) {
            $catIds[Str::slug($name)] = BlogCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id;
        }

        $posts = [
            [
                'title' => 'The Complete Agency Client Onboarding Checklist (20 Steps)',
                'category' => 'client-management',
                'excerpt' => 'The exact 20-step checklist we built into Agency OS to take any new client from signed to delivered — without the chaos.',
                'content' => '<h2>Why onboarding decides everything</h2><p>The first 30 days of a client relationship set the tone for the next three years. A structured onboarding checklist makes you look professional from day one.</p><h2>The 20 steps</h2><p>1. Send welcome email. 2. Collect brand assets. 3. Get Meta access. 4. Get Google Ads access. 5. Get GA4 access. 6. Get Shopify access. 7. Get website/CMS access. 8. Get social media access. 9. Get email marketing access. 10. Review existing campaigns. 11. Review analytics. 12. Competitor research. 13. Schedule kickoff call. 14. Conduct kickoff call. 15. Document goals and KPIs. 16. Create 30-60-90 day plan. 17. Assign team members. 18. Set recurring tasks. 19. Set up reporting. 20. Send communication guidelines.</p><h2>Automate the checklist</h2><p>In Agency OS every new client gets this checklist automatically. Assign each step to a teammate with a due date and track progress live.</p>',
            ],
            [
                'title' => 'How to Price Agency Retainers (Calculator Included)',
                'category' => 'finance-billing',
                'excerpt' => 'Stop guessing your retainers. Here is the formula agencies use to price monthly retainers with a healthy margin.',
                'content' => '<h2>The retainer formula</h2><p>Retainer = (Team hours × blended hourly cost + tools cost) ÷ (1 − target margin).</p><h2>Example</h2><p>40 hours at ₹800/hr plus ₹5,000 in tools with a 40% margin target: (32,000 + 5,000) ÷ 0.6 = ₹61,667/month.</p><h2>Use the free calculator</h2><p>Try our <a href="/tools/retainer-calculator">retainer calculator</a> to price your next client, or manage retainers in Agency OS with contracts and health scores.</p>',
            ],
            [
                'title' => '10 Automation Rules Every Agency Should Turn On',
                'category' => 'productivity',
                'excerpt' => 'From overdue task alerts to meta-lead follow-ups — the automation rules that save agencies hours every week.',
                'content' => '<h2>Start with these 5</h2><p>1. Task overdue → notify assignee. 2. Task overdue 3 days → notify manager. 3. Meta lead → create urgent call task. 4. Invoice overdue → email client. 5. Contract expiring → alert admin.</p><h2>Then add</h2><p>6. New lead → notify sales. 7. Lead won → notify the team. 8. Lead lost → log the reason. 9. Client request → create task. 10. Report shared → notify the account manager.</p><p>Every rule in Agency OS supports conditions, delays and a dry-run test.</p>',
            ],
            [
                'title' => 'Client Portals: Why Your Agency Needs One (and How to Launch It)',
                'category' => 'client-management',
                'excerpt' => 'Approvals that took days now take hours. Here is why a client portal is the highest-ROI feature for agencies.',
                'content' => '<h2>What a portal changes</h2><p>Clients get a login where they can approve deliverables, view reports, download invoices and submit requests. No more email ping-pong.</p><h2>Launch it in a day</h2><p>In Agency OS, enable portal access on any client, and an invitation email is sent automatically with a set-password link. Clients only ever see their own company data.</p>',
            ],
        ];

        foreach ($posts as $post) {
            $slug = Str::slug($post['title']);
            $p = BlogPost::updateOrCreate(['slug' => $slug], [
                'title' => $post['title'],
                'excerpt' => $post['excerpt'],
                'content' => $post['content'],
                'status' => 'published',
                'author_name' => 'Agency OS Team',
                'published_at' => now()->subDays(rand(1, 30)),
                'is_featured' => $post['title'] === 'The Complete Agency Client Onboarding Checklist (20 Steps)',
            ]);
            $p->categories()->sync([$catIds[$post['category']] ?? $catIds['agency-growth']]);
        }

        $this->command?->info('Blog seeded: '.count($posts).' articles.');
    }
}
