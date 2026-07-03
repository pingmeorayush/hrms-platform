<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ResilienceValidationRun;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResilienceReadinessApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->travelTo(now()->create(2026, 7, 1, 9, 30, 0, 'Asia/Kolkata'));
    }

    public function test_authorized_operator_can_record_validation_runs_and_view_resilience_readiness(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        Sanctum::actingAs($tenantAdmin);

        $this
            ->postJson('/api/v1/resilience/validation-runs', [
                'scenario_key' => 'daily_application_backup',
                'status' => 'passed',
                'recovery_point_actual_minutes' => 22,
                'recovery_time_actual_minutes' => 74,
                'evidence_refs' => [
                    'backup-job-20260701-0200',
                    'checksum-report-20260701',
                ],
                'notes' => 'Nightly backup completed inside the expected window.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.scenario_key', 'daily_application_backup')
            ->assertJsonPath('data.status', 'passed')
            ->assertJsonPath('data.executed_by_name', $tenantAdmin->name);

        $this
            ->postJson('/api/v1/resilience/validation-runs', [
                'scenario_key' => 'regional_failover_drill',
                'status' => 'failed',
                'recovery_point_actual_minutes' => 48,
                'recovery_time_actual_minutes' => 310,
                'evidence_refs' => [
                    'incident-log-dr-2026-q3',
                    'failover-smoke-check-20260701',
                ],
                'notes' => 'Queue workers did not recover cleanly after failover.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.scenario_key', 'regional_failover_drill')
            ->assertJsonPath('data.status', 'failed');

        $this
            ->getJson('/api/v1/resilience/readiness')
            ->assertOk()
            ->assertJsonPath('data.summary.total_scenario_count', 4)
            ->assertJsonPath('data.summary.ready_scenario_count', 1)
            ->assertJsonPath('data.summary.failed_scenario_count', 1)
            ->assertJsonPath('data.summary.attention_scenario_count', 2)
            ->assertJsonPath('data.summary.overdue_scenario_count', 2)
            ->assertJsonPath('data.summary.validation_run_count', 2)
            ->assertJsonPath('data.policy.primary_region', 'ap-south-1')
            ->assertJsonPath('data.runbook.0.key', 'declare')
            ->assertJsonFragment([
                'key' => 'daily_application_backup',
                'status' => 'ready',
            ])
            ->assertJsonFragment([
                'key' => 'regional_failover_drill',
                'status' => 'failed',
            ]);

        $this->assertSame(2, ResilienceValidationRun::query()->count());
    }

    public function test_resilience_endpoints_are_permission_protected(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $manager = User::factory()->create(['company_id' => $company->id]);
        $manager->assignRole('manager');

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/resilience/readiness')->assertForbidden();
        $this->postJson('/api/v1/resilience/validation-runs', [
            'scenario_key' => 'daily_application_backup',
            'status' => 'passed',
            'evidence_refs' => ['backup-job-1'],
        ])->assertForbidden();
    }
}
