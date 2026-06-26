<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportingCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_hr_admin_can_create_and_update_governed_reporting_catalog_entries(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        Sanctum::actingAs($hrAdmin);

        $datasetId = $this->postJson('/api/v1/reporting/datasets', $this->datasetPayload($hrAdmin->id))
            ->assertCreated()
            ->assertJsonPath('data.key', 'workforce_headcount_snapshot')
            ->assertJsonPath('data.governance.version', 1)
            ->assertJsonPath('data.masking_posture.sensitive_field_keys.0', 'employee_email')
            ->json('data.id');

        $kpiId = $this->postJson('/api/v1/reporting/kpis', $this->kpiPayload($hrAdmin->id))
            ->assertCreated()
            ->assertJsonPath('data.key', 'active_headcount')
            ->assertJsonPath('data.owner.id', $hrAdmin->id)
            ->assertJsonPath('data.governance.certification_status', 'certified')
            ->json('data.id');

        $this->patchJson('/api/v1/reporting/datasets/'.$datasetId, [
            'description' => 'Certified workforce headcount snapshot for leadership reporting.',
            'certification_status' => 'certified',
            'review_notes' => 'Reviewed for Sprint 08 workforce reporting baseline.',
        ])->assertOk()
            ->assertJsonPath('data.governance.version', 2)
            ->assertJsonPath('data.governance.certification_status', 'certified');

        $this->getJson('/api/v1/reporting/datasets?domain=workforce')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $datasetId);

        $this->getJson('/api/v1/reporting/kpis?certification_status=certified')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $kpiId);

        $this->assertDatabaseHas('report_datasets', [
            'id' => $datasetId,
            'company_id' => $company->id,
            'key' => 'workforce_headcount_snapshot',
            'certification_status' => 'certified',
            'version' => 2,
        ]);

        $this->assertDatabaseHas('kpi_definitions', [
            'id' => $kpiId,
            'company_id' => $company->id,
            'key' => 'active_headcount',
            'certification_status' => 'certified',
            'version' => 1,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'reporting.dataset.created',
            'entity_id' => (string) $datasetId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'reporting.dataset.updated',
            'entity_id' => (string) $datasetId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'reporting.kpi.created',
            'entity_id' => (string) $kpiId,
        ]);
    }

    public function test_reporting_dataset_structure_validation_blocks_unmapped_filters_and_masking_keys(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        Sanctum::actingAs($hrAdmin);

        $payload = $this->datasetPayload($hrAdmin->id);
        $payload['approved_filters'][] = [
            'key' => 'ghost_field',
            'label' => 'Ghost field',
            'type' => 'string',
            'required' => false,
            'operators' => ['eq'],
        ];
        $payload['masking_posture']['sensitive_field_keys'][] = 'salary_band';

        $this->postJson('/api/v1/reporting/datasets', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['approved_filters']);
    }

    public function test_manage_only_user_can_create_draft_entries_but_cannot_certify_them(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $analyst = User::factory()->create(['company_id' => $company->id]);
        $analyst->givePermissionTo('reporting.view');
        $analyst->givePermissionTo('reporting.manage');

        Sanctum::actingAs($analyst);

        $this->postJson('/api/v1/reporting/kpis', [
            'key' => 'leave_approval_sla',
            'name' => 'Leave approval SLA',
            'domain' => 'leave',
            'description' => 'Average time to approve leave requests.',
            'formula' => 'Average hours between leave request submission and final approval.',
            'source_references' => [
                ['module' => 'leave', 'entity' => 'leave_requests', 'field' => 'approved_at', 'notes' => 'Uses approved workflow outcome timestamps.'],
            ],
            'grain' => 'monthly',
            'certification_status' => 'draft',
        ])->assertCreated()
            ->assertJsonPath('data.governance.certification_status', 'draft');

        $this->postJson('/api/v1/reporting/datasets', [
            ...$this->datasetPayload(null),
            'key' => 'leave_decision_trends',
            'name' => 'Leave decision trends',
            'domain' => 'leave',
            'certification_status' => 'certified',
        ])->assertForbidden();
    }

    /**
     * @return array<string, mixed>
     */
    private function datasetPayload(?int $ownerUserId): array
    {
        return [
            'key' => 'workforce_headcount_snapshot',
            'name' => 'Workforce headcount snapshot',
            'domain' => 'workforce',
            'description' => 'Employee-level governed workforce snapshot for headcount and attrition reporting.',
            'source_references' => [
                ['module' => 'employee', 'entity' => 'employees', 'field' => 'employment_status', 'notes' => 'Primary active employee population source.'],
                ['module' => 'organization', 'entity' => 'departments', 'field' => 'name', 'notes' => 'Used for current department labeling.'],
            ],
            'grain' => 'employee_daily_snapshot',
            'approved_fields' => [
                [
                    'key' => 'employee_id',
                    'label' => 'Employee ID',
                    'type' => 'number',
                    'description' => 'Internal employee primary key for governed drilldowns.',
                    'sensitive' => false,
                    'masking_strategy' => null,
                ],
                [
                    'key' => 'employee_email',
                    'label' => 'Employee email',
                    'type' => 'string',
                    'description' => 'Primary work email address.',
                    'sensitive' => true,
                    'masking_strategy' => 'partial',
                ],
                [
                    'key' => 'department_name',
                    'label' => 'Department',
                    'type' => 'string',
                    'description' => 'Current department name.',
                    'sensitive' => false,
                    'masking_strategy' => null,
                ],
                [
                    'key' => 'employment_status',
                    'label' => 'Employment status',
                    'type' => 'status',
                    'description' => 'Current employee lifecycle status.',
                    'sensitive' => false,
                    'masking_strategy' => null,
                ],
            ],
            'approved_filters' => [
                [
                    'key' => 'department_name',
                    'label' => 'Department',
                    'type' => 'entity',
                    'required' => false,
                    'operators' => ['eq', 'in'],
                ],
                [
                    'key' => 'employment_status',
                    'label' => 'Employment status',
                    'type' => 'status',
                    'required' => false,
                    'operators' => ['eq', 'in'],
                ],
            ],
            'drilldown_paths' => [
                [
                    'key' => 'employee_profile',
                    'label' => 'Employee profile',
                    'target_dataset_key' => 'workforce_headcount_snapshot',
                    'description' => 'Governed drilldown path to employee-level detail.',
                    'allowed_filter_keys' => ['department_name', 'employment_status'],
                ],
            ],
            'masking_posture' => [
                'default_strategy' => 'none',
                'sensitive_field_keys' => ['employee_email'],
                'notes' => 'Only role-approved reporting viewers can see partially masked employee email values.',
            ],
            'freshness_expectation_minutes' => 1440,
            'certification_status' => 'draft',
            'review_notes' => 'Initial draft created for Sprint 08 baseline.',
            'owner_user_id' => $ownerUserId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function kpiPayload(?int $ownerUserId): array
    {
        return [
            'key' => 'active_headcount',
            'name' => 'Active headcount',
            'domain' => 'workforce',
            'description' => 'Count of employees whose employment status is active on the reporting date.',
            'formula' => 'Count employees where employment_status = active and employment_start_date is on or before the reporting date.',
            'source_references' => [
                ['module' => 'employee', 'entity' => 'employees', 'field' => 'employment_status', 'notes' => 'Uses active employee status as the governing source.'],
                ['module' => 'employee', 'entity' => 'employees', 'field' => 'employment_start_date', 'notes' => 'Excludes future-dated joins from current-day headcount.'],
            ],
            'grain' => 'daily',
            'certification_status' => 'certified',
            'review_notes' => 'Certified for workforce dashboard use.',
            'owner_user_id' => $ownerUserId,
        ];
    }
}
