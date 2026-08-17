<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price_monthly' => 2999,
                'price_yearly' => 29990,
                'max_users' => 5,
                'max_clients' => 5,
                'max_storage_gb' => 10,
                'features' => [
                    'Up to 5 users',
                    'Up to 5 clients',
                    'Task & project management',
                    'Lead management (Lead365)',
                    'Invoicing (BikriBook)',
                    'Client portal',
                    '10 GB storage',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'price_monthly' => 6999,
                'price_yearly' => 69990,
                'max_users' => 15,
                'max_clients' => 20,
                'max_storage_gb' => 50,
                'features' => [
                    'Up to 15 users',
                    'Up to 20 clients',
                    'Everything in Starter',
                    'Reporting & PDF reports',
                    'Automation rules',
                    'Time tracking & profitability',
                    '50 GB storage',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price_monthly' => 14999,
                'price_yearly' => 149990,
                'max_users' => null,
                'max_clients' => null,
                'max_storage_gb' => 500,
                'features' => [
                    'Unlimited users',
                    'Unlimited clients',
                    'Everything in Professional',
                    'Priority support',
                    'Custom integrations',
                    '500 GB storage',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
