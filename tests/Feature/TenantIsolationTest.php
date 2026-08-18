<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * THE critical multi-tenancy test: tenant A must never see tenant B's data.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        TenantScope::forget();
    }

    public function test_tenant_a_cannot_see_tenant_b_clients(): void
    {
        $tenantA = Tenant::create(['name' => 'Agency A', 'slug' => 'agency-a', 'email' => 'a@test.com']);
        $tenantB = Tenant::create(['name' => 'Agency B', 'slug' => 'agency-b', 'email' => 'b@test.com']);

        $userA = User::create(['tenant_id' => $tenantA->id, 'name' => 'Admin A', 'email' => 'admin-a@test.com', 'password' => bcrypt('secret'), 'role' => 'admin']);

        Client::create(['tenant_id' => $tenantA->id, 'company_name' => 'Client of A']);
        Client::create(['tenant_id' => $tenantB->id, 'company_name' => 'Client of B']);

        TenantScope::setCurrent($tenantA->id);

        $this->assertSame(1, Client::count());
        $this->assertSame('Client of A', Client::first()->company_name);

        TenantScope::setCurrent($tenantB->id);

        $this->assertSame(1, Client::count());
        $this->assertSame('Client of B', Client::first()->company_name);
    }

    public function test_tenant_scope_applies_via_auth_user_fallback(): void
    {
        $tenantA = Tenant::create(['name' => 'Agency A', 'slug' => 'agency-a2', 'email' => 'a2@test.com']);
        $tenantB = Tenant::create(['name' => 'Agency B', 'slug' => 'agency-b2', 'email' => 'b2@test.com']);

        Client::create(['tenant_id' => $tenantA->id, 'company_name' => 'A Client']);
        Client::create(['tenant_id' => $tenantB->id, 'company_name' => 'B Client']);

        $userA = User::create(['tenant_id' => $tenantA->id, 'name' => 'Admin A', 'email' => 'admin-a2@test.com', 'password' => bcrypt('secret'), 'role' => 'admin']);

        $this->actingAs($userA);

        // No explicit tenant context: scope falls back to the authenticated user's tenant.
        $this->assertSame(1, Client::count());
        $this->assertSame('A Client', Client::first()->company_name);
    }

    public function test_unknown_subdomain_returns_404(): void
    {
        $response = $this->withServerVariables(['HTTP_HOST' => 'nope.'.config('tenancy.tenant_domain')])
            ->get('/dashboard');

        $response->assertStatus(404);
    }

    public function test_lead365_webhook_logs_raw_payload_and_returns_200(): void
    {
        $tenant = Tenant::create(['name' => 'Agency W', 'slug' => 'agency-w', 'email' => 'w@test.com']);

        $payload = [
            'event' => 'lead.created',
            'data' => ['id' => 'L-101', 'name' => 'Test Lead', 'email' => 'lead@test.com'],
        ];

        $response = $this->postJson('/webhooks/lead365/'.$tenant->slug, $payload);

        $response->assertStatus(200)->assertJson(['status' => 'received']);

        $log = \App\Models\WebhookLog::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();

        $this->assertNotNull($log);
        $this->assertSame('lead.created', $log->event_type);
        $this->assertSame('L-101', $log->payload['data']['id']);
        $this->assertContains($log->status, ['received', 'processed']);

        // The queued job (sync driver in tests) processed the lead.
        $this->assertDatabaseHas('leads', [
            'tenant_id' => $tenant->id,
            'lead365_lead_id' => 'L-101',
            'contact_name' => 'Test Lead',
        ]);
    }

    public function test_registration_creates_tenant_admin_and_default_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'Founder',
            'agency_name' => 'Test Agency',
            'email' => 'founder@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'subdomain' => 'test-agency',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('onboarding'));

        $this->assertDatabaseHas('tenants', ['slug' => 'test-agency', 'is_trial' => true]);
        $this->assertDatabaseHas('users', ['email' => 'founder@test.com', 'role' => 'admin']);

        $tenant = Tenant::where('slug', 'test-agency')->first();

        $this->assertGreaterThanOrEqual(5, \App\Models\LeadPipelineStage::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertGreaterThanOrEqual(5, \App\Models\AutomationRule::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
        $this->assertSame(8, \App\Models\KbCategory::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count());
    }
}
