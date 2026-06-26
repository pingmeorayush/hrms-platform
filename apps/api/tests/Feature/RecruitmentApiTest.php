<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowStage;
use App\Models\WorkflowVersion;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RecruitmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_recruiter_can_create_submit_and_complete_requisition_approval_workflow(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $department = Department::factory()->create(['company_id' => $company->id, 'code' => 'REC-ENG']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'code' => 'REC-SDE2']);
        $location = Location::factory()->create(['company_id' => $company->id, 'code' => 'REC-BLR']);
        $costCenter = CostCenter::factory()->create(['company_id' => $company->id, 'code' => 'REC-100']);

        $recruiter = User::factory()->create(['company_id' => $company->id]);
        $recruiter->assignRole('recruiter');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $hiringManager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $this->seedRecruitmentApprovalWorkflowForCompany($company, $hrAdmin);

        Sanctum::actingAs($recruiter);

        $requisitionId = $this->postJson('/api/v1/recruitment/requisitions', $this->requisitionPayload(
            department: $department,
            designation: $designation,
            location: $location,
            costCenter: $costCenter,
            recruiter: $recruiter,
            hiringManager: $hiringManager,
        ))->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.recruiter.id', $recruiter->id)
            ->assertJsonPath('data.hiring_manager.id', $hiringManager->id)
            ->json('data.id');

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'action' => 'submit',
        ])->assertOk()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.workflow.definition.key', 'recruitment-requisition-approval')
            ->assertJsonPath('data.workflow.tasks.0.assignee.id', $managerUser->id)
            ->assertJsonPath('data.workflow.tasks.0.stage_key', 'hiring_manager_review');

        Sanctum::actingAs($managerUser);

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'action' => 'approve',
            'comment' => 'Headcount is justified for this quarter.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.workflow.tasks.0.status', 'completed')
            ->assertJsonPath('data.workflow.tasks.1.assignee.id', $hrAdmin->id)
            ->assertJsonPath('data.workflow.tasks.1.stage_key', 'hr_review');

        Sanctum::actingAs($hrAdmin);

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'action' => 'approve',
            'comment' => 'HR approves the requisition for recruiter execution.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.workflow.status', 'completed');

        $this->assertDatabaseHas('job_requisitions', [
            'id' => $requisitionId,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $recruiter->id,
            'event_type' => 'recruitment.requisition.submitted',
            'entity_id' => (string) $requisitionId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'recruitment.requisition.approved',
            'entity_id' => (string) $requisitionId,
        ]);
    }

    public function test_requisition_access_is_tenant_scoped_and_hiring_manager_visibility_is_owner_bound(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $otherCompany = Company::factory()->create(['status' => 'active']);

        $department = Department::factory()->create(['company_id' => $company->id, 'code' => 'REC-SALES']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'code' => 'REC-AE']);
        $location = Location::factory()->create(['company_id' => $company->id, 'code' => 'REC-HYD']);

        $recruiter = User::factory()->create(['company_id' => $company->id]);
        $recruiter->assignRole('recruiter');

        $ownerManagerUser = User::factory()->create(['company_id' => $company->id]);
        $ownerManagerUser->assignRole('manager');

        $otherManagerUser = User::factory()->create(['company_id' => $company->id]);
        $otherManagerUser->assignRole('manager');

        $otherCompanyHr = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherCompanyHr->assignRole('hr.admin');

        $ownerManager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $ownerManagerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $otherManagerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        Sanctum::actingAs($recruiter);

        $requisitionId = $this->postJson('/api/v1/recruitment/requisitions', $this->requisitionPayload(
            department: $department,
            designation: $designation,
            location: $location,
            costCenter: null,
            recruiter: $recruiter,
            hiringManager: $ownerManager,
        ))->assertCreated()->json('data.id');

        $this->getJson('/api/v1/recruitment/requisitions?status=draft&recruiter_user_id='.$recruiter->id)
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $requisitionId);

        Sanctum::actingAs($ownerManagerUser);

        $this->getJson("/api/v1/recruitment/requisitions/{$requisitionId}")
            ->assertOk()
            ->assertJsonPath('data.id', $requisitionId);

        Sanctum::actingAs($otherManagerUser);

        $this->getJson("/api/v1/recruitment/requisitions/{$requisitionId}")
            ->assertNotFound();

        Sanctum::actingAs($otherCompanyHr);

        $this->getJson("/api/v1/recruitment/requisitions/{$requisitionId}")
            ->assertNotFound();
    }

    public function test_requisition_lifecycle_validates_workflow_readiness_and_supports_hold_resume_and_close(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $department = Department::factory()->create(['company_id' => $company->id, 'code' => 'REC-PM']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'code' => 'REC-PM-D']);
        $location = Location::factory()->create(['company_id' => $company->id, 'code' => 'REC-PUN']);

        $recruiter = User::factory()->create(['company_id' => $company->id]);
        $recruiter->assignRole('recruiter');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $unlinkedManager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => null,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $linkedManager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $this->seedRecruitmentApprovalWorkflowForCompany($company, $hrAdmin);

        Sanctum::actingAs($recruiter);

        $requisitionId = $this->postJson('/api/v1/recruitment/requisitions', $this->requisitionPayload(
            department: $department,
            designation: $designation,
            location: $location,
            costCenter: null,
            recruiter: $recruiter,
            hiringManager: $unlinkedManager,
        ))->assertCreated()->json('data.id');

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'action' => 'submit',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['hiring_manager_employee_id']);

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'hiring_manager_employee_id' => $linkedManager->id,
            'priority' => 'critical',
        ])->assertOk()
            ->assertJsonPath('data.priority', 'critical');

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'action' => 'submit',
        ])->assertOk()
            ->assertJsonPath('data.status', 'submitted');

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'action' => 'put_on_hold',
            'comment' => 'Budget check pending.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['requisition']);

        Sanctum::actingAs($managerUser);

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'action' => 'approve',
        ])->assertOk();

        Sanctum::actingAs($hrAdmin);

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'action' => 'approve',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved');

        Sanctum::actingAs($recruiter);

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'action' => 'put_on_hold',
            'comment' => 'Budget check pending.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'on_hold')
            ->assertJsonPath('data.status_before_hold', 'approved');

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'action' => 'resume',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.status_before_hold', null);

        $this->patchJson("/api/v1/recruitment/requisitions/{$requisitionId}", [
            'action' => 'close',
            'comment' => 'Position filled through an internal mobility decision.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'closed')
            ->assertJsonPath('data.closed_reason', 'Position filled through an internal mobility decision.');

        $this->assertDatabaseHas('job_requisitions', [
            'id' => $requisitionId,
            'status' => 'closed',
            'closed_reason' => 'Position filled through an internal mobility decision.',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $recruiter->id,
            'event_type' => 'recruitment.requisition.closed',
            'entity_id' => (string) $requisitionId,
        ]);
    }

    private function requisitionPayload(
        Department $department,
        Designation $designation,
        Location $location,
        ?CostCenter $costCenter,
        User $recruiter,
        Employee $hiringManager,
    ): array {
        return [
            'title' => 'Senior Platform Engineer',
            'employment_type' => 'full_time',
            'hiring_type' => 'new_position',
            'priority' => 'high',
            'openings_count' => 2,
            'min_experience_years' => 5,
            'target_start_date' => '2026-08-01',
            'headcount_reference' => 'HC-2026-PLAT-02',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter?->id,
            'recruiter_user_id' => $recruiter->id,
            'hiring_manager_employee_id' => $hiringManager->id,
            'justification' => 'The platform team needs added hiring capacity for roadmap commitments and production scale work.',
            'notes' => 'Open to Bengaluru or Pune once the location expansion is approved.',
        ];
    }

    private function seedRecruitmentApprovalWorkflowForCompany(Company $company, User $actor): WorkflowDefinition
    {
        $definition = WorkflowDefinition::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'key' => 'recruitment-requisition-approval',
            ],
            [
                'name' => 'Recruitment Requisition Approval Workflow',
                'module' => 'recruitment',
                'description' => 'Sequential requisition approval through the assigned hiring manager and HR.',
                'is_template' => true,
                'status' => 'published',
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ],
        );

        $version = WorkflowVersion::withoutGlobalScopes()->updateOrCreate(
            [
                'workflow_definition_id' => $definition->id,
                'version' => 1,
            ],
            [
                'status' => 'published',
                'definition' => [
                    'module' => 'recruitment',
                    'stages' => [
                        [
                            'key' => 'hiring_manager_review',
                            'name' => 'Hiring Manager Review',
                            'sequence' => 1,
                            'approver_type' => 'payload_user',
                            'approver_value' => 'hiring_manager_user_id',
                            'available_actions' => ['approve', 'reject', 'request_changes'],
                            'sla_hours' => 48,
                        ],
                        [
                            'key' => 'hr_review',
                            'name' => 'HR Review',
                            'sequence' => 2,
                            'approver_type' => 'role',
                            'approver_value' => 'hr.admin',
                            'available_actions' => ['approve', 'reject', 'request_changes'],
                            'sla_hours' => 48,
                        ],
                    ],
                ],
                'created_by' => $actor->id,
                'published_at' => now(),
            ],
        );

        foreach ([
            [
                'key' => 'hiring_manager_review',
                'name' => 'Hiring Manager Review',
                'sequence' => 1,
                'approver_type' => 'payload_user',
                'approver_value' => 'hiring_manager_user_id',
                'available_actions' => ['approve', 'reject', 'request_changes'],
                'sla_hours' => 48,
            ],
            [
                'key' => 'hr_review',
                'name' => 'HR Review',
                'sequence' => 2,
                'approver_type' => 'role',
                'approver_value' => 'hr.admin',
                'available_actions' => ['approve', 'reject', 'request_changes'],
                'sla_hours' => 48,
            ],
        ] as $stageData) {
            WorkflowStage::withoutGlobalScopes()->updateOrCreate(
                [
                    'workflow_version_id' => $version->id,
                    'key' => $stageData['key'],
                ],
                $stageData,
            );
        }

        $definition->forceFill([
            'active_version_id' => $version->id,
        ])->save();

        return $definition->refresh();
    }
}
