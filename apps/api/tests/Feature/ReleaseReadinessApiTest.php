<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ReleaseReadinessDecision;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReleaseReadinessApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->travelTo(now()->create(2026, 7, 1, 11, 0, 0, 'Asia/Kolkata'));
    }

    public function test_authorized_operator_can_record_go_no_go_decisions_and_view_release_readiness(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        Sanctum::actingAs($tenantAdmin);

        $this
            ->postJson('/api/v1/release/readiness/decisions', [
                'release_window_label' => 'FY26 payroll launch wave 1',
                'target_environment' => 'production',
                'decision_status' => 'no_go',
                'summary' => 'Launch is blocked until recovery evidence is refreshed and blocker owners confirm readiness.',
                'blockers' => [
                    [
                        'area_key' => 'backups',
                        'title' => 'Monthly restore evidence is overdue',
                        'owner_role' => 'platform.super_admin',
                        'status' => 'open',
                        'notes' => 'A fresh restore rehearsal must be completed before launch.',
                    ],
                ],
                'artifact_refs' => [
                    'docs/runbooks/release-rollback.md',
                    'docs/runbooks/backup-restore-disaster-recovery-validation.md',
                ],
                'decision_notes' => 'Ops and tenant admin agreed to hold release until recovery evidence is rerun.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.release_window_label', 'FY26 payroll launch wave 1')
            ->assertJsonPath('data.decision_status', 'no_go')
            ->assertJsonPath('data.blockers.0.area_key', 'backups')
            ->assertJsonPath('data.decided_by_name', $tenantAdmin->name);

        $this
            ->getJson('/api/v1/release/readiness')
            ->assertOk()
            ->assertJsonPath('data.summary.total_area_count', 6)
            ->assertJsonPath('data.summary.decision_count', 1)
            ->assertJsonPath('data.summary.runbook_count', 3)
            ->assertJsonPath('data.policy.target_environments.1', 'production')
            ->assertJsonPath('data.recommendation.status', 'no_go')
            ->assertJsonPath('data.latest_decision.decision_status', 'no_go')
            ->assertJsonPath('data.areas.0.key', 'testing')
            ->assertJsonPath('data.runbooks.0.key', 'incident_response')
            ->assertJsonFragment([
                'key' => 'backups',
                'status' => 'attention',
            ])
            ->assertJsonFragment([
                'title' => 'Monthly restore evidence is overdue',
                'owner_role' => 'platform.super_admin',
            ]);

        $this->assertSame(1, ReleaseReadinessDecision::query()->count());
    }

    public function test_release_readiness_endpoints_are_permission_protected(): void
    {
        $company = Company::query()->where('slug', 'phoenix-demo')->firstOrFail();
        $manager = User::factory()->create(['company_id' => $company->id]);
        $manager->assignRole('manager');

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/release/readiness')->assertForbidden();
        $this->postJson('/api/v1/release/readiness/decisions', [
            'release_window_label' => 'Blocked manager review',
            'target_environment' => 'production',
            'decision_status' => 'no_go',
            'summary' => 'Managers cannot record release decisions.',
            'blockers' => [
                [
                    'title' => 'Unauthorized actor',
                    'owner_role' => 'platform.support',
                ],
            ],
        ])->assertForbidden();
    }
}
