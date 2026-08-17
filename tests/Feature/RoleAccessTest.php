<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_specialist_cannot_open_finance(): void
    {
        $tenant = Tenant::create(['name' => 'A', 'slug' => 'role-a', 'email' => 'r@test.com']);
        $specialist = User::create(['tenant_id' => $tenant->id, 'name' => 'Spec', 'email' => 'spec@test.com', 'password' => bcrypt('secret'), 'role' => 'specialist']);

        TenantScope::setCurrent($tenant->id);

        $this->actingAs($specialist)->get('/finance')->assertForbidden();
    }

    public function test_account_manager_only_sees_own_clients(): void
    {
        $tenant = Tenant::create(['name' => 'A', 'slug' => 'role-b', 'email' => 'r2@test.com']);
        $am = User::create(['tenant_id' => $tenant->id, 'name' => 'AM', 'email' => 'am@test.com', 'password' => bcrypt('secret'), 'role' => 'account_manager']);
        $other = User::create(['tenant_id' => $tenant->id, 'name' => 'Other', 'email' => 'other@test.com', 'password' => bcrypt('secret'), 'role' => 'account_manager']);

        Client::create(['tenant_id' => $tenant->id, 'company_name' => 'Mine', 'account_manager_id' => $am->id]);
        Client::create(['tenant_id' => $tenant->id, 'company_name' => 'Not Mine', 'account_manager_id' => $other->id]);

        TenantScope::setCurrent($tenant->id);

        $response = $this->actingAs($am)->get('/clients');
        $response->assertOk();
        $response->assertSee('Mine');
        $response->assertDontSee('Not Mine');
    }
}
