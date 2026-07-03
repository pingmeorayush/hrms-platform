<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReleaseQualityGateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_authorized_operator_can_view_release_quality_gates(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        Sanctum::actingAs($tenantAdmin);

        $this
            ->getJson('/api/v1/release/quality-gates')
            ->assertOk()
            ->assertJsonPath('data.summary.total_gate_count', 4)
            ->assertJsonPath('data.summary.blocking_gate_count', 0)
            ->assertJsonPath('data.policy.protected_branch', 'main')
            ->assertJsonPath('data.gates.0.key', 'api_quality')
            ->assertJsonPath('data.environments.2.key', 'production')
            ->assertJsonPath('data.environments.2.status', 'pending');
    }

    public function test_release_quality_gate_endpoint_is_permission_protected(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $manager = User::factory()->create(['company_id' => $company->id]);
        $manager->assignRole('manager');

        Sanctum::actingAs($manager);

        $this
            ->getJson('/api/v1/release/quality-gates')
            ->assertForbidden();
    }
}
