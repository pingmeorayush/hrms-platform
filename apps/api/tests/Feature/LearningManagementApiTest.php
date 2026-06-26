<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LearningAssignmentTarget;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LearningManagementApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_hr_admin_can_create_learning_item_and_assign_it_to_a_department_scope(): void
    {
        Carbon::setTestNow('2026-06-10 09:00:00');

        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $otherDepartment, $designation, $location] = $this->createOrganizationContext($company->id);

        $employeeA = $this->createEmployee($company->id, $department->id, $designation->id, $location->id, 'LEARN-1001');
        $employeeB = $this->createEmployee($company->id, $department->id, $designation->id, $location->id, 'LEARN-1002');
        $employeeOther = $this->createEmployee($company->id, $otherDepartment->id, $designation->id, $location->id, 'LEARN-1003');

        Sanctum::actingAs($hrAdmin);

        $learningItemId = $this->postJson('/api/v1/learning/items', [
            'code' => 'SEC-2026',
            'title' => 'Security Awareness 2026',
            'description' => 'Annual compliance refresher for all in-scope teams.',
            'category' => 'compliance',
            'delivery_mode' => 'self_paced',
            'duration_minutes' => 45,
            'requires_completion_evidence' => false,
            'renewal_frequency_months' => 12,
            'default_due_days' => 30,
            'metadata' => [
                'provider' => 'Phoenix Academy',
            ],
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.code', 'SEC-2026')
            ->assertJsonPath('data.renewal_frequency_months', 12)
            ->json('data.id');

        $assignmentId = $this->postJson('/api/v1/learning/assignments', [
            'learning_item_id' => $learningItemId,
            'audience_type' => 'department',
            'audience_rules' => [
                'department_ids' => [$department->id],
            ],
            'due_on' => '2026-07-15',
            'notes' => 'Required before Q3 access reviews.',
        ])->assertCreated()
            ->assertJsonPath('data.item.id', $learningItemId)
            ->assertJsonPath('data.audience_type', 'department')
            ->assertJsonPath('data.target_count', 2)
            ->assertJsonPath('data.completion_rules.renewal_frequency_months', 12)
            ->assertJsonPath('data.target_summary.total_count', 2)
            ->json('data.id');

        $this->getJson('/api/v1/learning/targets?learning_assignment_id='.$assignmentId)
            ->assertOk()
            ->assertJsonPath('data.meta.total', 2)
            ->assertJsonPath('data.items.0.due_state', 'upcoming');

        $this->assertDatabaseHas('learning_assignments', [
            'id' => $assignmentId,
            'company_id' => $company->id,
            'learning_item_id' => $learningItemId,
            'audience_type' => 'department',
            'target_count' => 2,
        ]);

        $this->assertDatabaseHas('learning_assignment_targets', [
            'learning_assignment_id' => $assignmentId,
            'employee_id' => $employeeA->id,
            'status' => 'assigned',
        ]);

        $this->assertDatabaseHas('learning_assignment_targets', [
            'learning_assignment_id' => $assignmentId,
            'employee_id' => $employeeB->id,
            'status' => 'assigned',
        ]);

        $this->assertDatabaseMissing('learning_assignment_targets', [
            'learning_assignment_id' => $assignmentId,
            'employee_id' => $employeeOther->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'learning.item.created',
            'entity_id' => (string) $learningItemId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'learning.assignment.created',
            'entity_id' => (string) $assignmentId,
        ]);
    }

    public function test_employee_completion_requires_evidence_when_configured_and_sets_renewal_posture(): void
    {
        Carbon::setTestNow('2026-06-10 09:00:00');

        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $otherDepartment, $designation, $location] = $this->createOrganizationContext($company->id);

        $employee = $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'LEARN-2001',
            userId: $employeeUser->id,
        );

        Sanctum::actingAs($hrAdmin);

        $learningItemId = $this->postJson('/api/v1/learning/items', [
            'code' => 'SAFE-PRAC',
            'title' => 'Safe Working Practices',
            'category' => 'compliance',
            'delivery_mode' => 'document_acknowledgement',
            'requires_completion_evidence' => true,
            'renewal_frequency_months' => 12,
            'default_due_days' => 20,
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        $assignmentId = $this->postJson('/api/v1/learning/assignments', [
            'learning_item_id' => $learningItemId,
            'audience_type' => 'employee',
            'audience_rules' => [
                'employee_ids' => [$employee->id],
            ],
        ])->assertCreated()->json('data.id');

        $targetId = LearningAssignmentTarget::query()
            ->where('learning_assignment_id', $assignmentId)
            ->value('id');

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/learning/my-assignments')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $targetId)
            ->assertJsonPath('data.items.0.requires_completion_evidence', true)
            ->assertJsonPath('data.items.0.renewal_posture', 'pending_initial_completion');

        $this->patchJson('/api/v1/learning/targets/'.$targetId.'/complete', [
            'completion_notes' => 'Read and acknowledged.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['completion_evidence']);

        $this->patchJson('/api/v1/learning/targets/'.$targetId.'/complete', [
            'completion_notes' => 'Read and acknowledged.',
            'completion_evidence' => [
                'type' => 'document_receipt',
                'reference' => 'SAFE-PRAC-ACK-2001',
                'notes' => 'Signed acknowledgement stored in DMS.',
            ],
        ])->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.evidence_present', true)
            ->assertJsonPath('data.renewal_due_on', '2027-06-10')
            ->assertJsonPath('data.renewal_posture', 'current');

        $this->assertDatabaseHas('learning_assignment_targets', [
            'id' => $targetId,
            'employee_id' => $employee->id,
            'status' => 'completed',
            'completed_by_user_id' => $employeeUser->id,
            'renewal_due_on' => '2027-06-10 00:00:00',
        ]);

        $this->assertDatabaseHas('learning_assignments', [
            'id' => $assignmentId,
            'completion_count' => 1,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'learning.target.completed',
            'entity_id' => (string) $targetId,
        ]);
    }

    public function test_manager_can_only_view_learning_targets_for_self_and_direct_reports(): void
    {
        Carbon::setTestNow('2026-06-10 09:00:00');

        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $reportUser = User::factory()->create(['company_id' => $company->id]);
        $reportUser->assignRole('employee');

        $otherUser = User::factory()->create(['company_id' => $company->id]);
        $otherUser->assignRole('employee');

        [$department, $otherDepartment, $designation, $location] = $this->createOrganizationContext($company->id);

        $manager = $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'LEARN-MGR-1',
            userId: $managerUser->id,
        );

        $directReport = $this->createEmployee(
            companyId: $company->id,
            departmentId: $department->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'LEARN-REP-1',
            userId: $reportUser->id,
            managerId: $manager->id,
        );

        $otherEmployee = $this->createEmployee(
            companyId: $company->id,
            departmentId: $otherDepartment->id,
            designationId: $designation->id,
            locationId: $location->id,
            employeeCode: 'LEARN-OTH-1',
            userId: $otherUser->id,
        );

        Sanctum::actingAs($hrAdmin);

        $learningItemId = $this->postJson('/api/v1/learning/items', [
            'code' => 'PRIV-LEARN',
            'title' => 'Private Learning Scope Check',
            'category' => 'compliance',
            'delivery_mode' => 'self_paced',
            'requires_completion_evidence' => false,
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        $assignmentId = $this->postJson('/api/v1/learning/assignments', [
            'learning_item_id' => $learningItemId,
            'audience_type' => 'employee',
            'audience_rules' => [
                'employee_ids' => [$directReport->id, $otherEmployee->id],
            ],
            'due_on' => '2026-06-25',
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($managerUser);

        $this->getJson('/api/v1/learning/targets')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.employee.id', $directReport->id);

        $this->getJson('/api/v1/learning/assignments/'.$assignmentId)
            ->assertOk()
            ->assertJsonPath('data.target_count', 1)
            ->assertJsonPath('data.target_summary.total_count', 1)
            ->assertJsonPath('data.targets.0.employee.id', $directReport->id);
    }

    private function createOrganizationContext(int $companyId): array
    {
        return [
            Department::factory()->create(['company_id' => $companyId, 'code' => 'LRN-ENG']),
            Department::factory()->create(['company_id' => $companyId, 'code' => 'LRN-FIN']),
            Designation::factory()->create(['company_id' => $companyId, 'code' => 'LRN-SDE2']),
            Location::factory()->create(['company_id' => $companyId, 'code' => 'LRN-BLR']),
        ];
    }

    private function createEmployee(
        int $companyId,
        int $departmentId,
        int $designationId,
        int $locationId,
        string $employeeCode,
        ?int $userId = null,
        ?int $managerId = null,
    ): Employee {
        return Employee::factory()->create([
            'company_id' => $companyId,
            'department_id' => $departmentId,
            'designation_id' => $designationId,
            'location_id' => $locationId,
            'employee_code' => $employeeCode,
            'user_id' => $userId,
            'manager_id' => $managerId,
            'employment_status' => 'active',
            'date_of_joining' => '2025-01-15',
        ]);
    }
}
