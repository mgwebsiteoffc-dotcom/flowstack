<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Platform-wide seeders. Per-tenant default data is seeded by
     * DefaultDataSeeder at registration time (never here).
     */
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            SuperAdminSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call(DemoTenantSeeder::class);
        }
    }
}
