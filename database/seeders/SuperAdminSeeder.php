<?php

namespace Database\Seeders;

use App\Models\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'superadmin@agencyos.test');
        $password = env('SUPER_ADMIN_PASSWORD', 'ChangeMe123!');

        SuperAdmin::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Platform Super Admin',
                'password' => Hash::make($password),
            ]
        );
    }
}
