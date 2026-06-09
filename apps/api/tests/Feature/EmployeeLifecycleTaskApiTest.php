<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeOnboardingTask;
use App\Models\Location;
use App\Models\User;
use App\Models\WorkflowTask;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmployeeLifecycleTaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionRoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_hr_admin_can_manage_lifecycle_task_templates_and_apply_them_to_employees(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);
        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $templateId = $this->postJson('/api/v1/employee-task-templates', [
            'name' => 'Standard offboarding asset checklist',
            'lifecycle_type' => 'offboarding',
            'title' => 'Collect company laptop and badge',
            'category' => 'it',
            'task_type' => 'setup_equipment',
            'assignee_type' => 'hr',
            'due_offset_days' => 2,
            'sort_order' => 5,
        ])->assertCreated()
            ->assertJsonPath('data.lifecycle_type', 'offboarding')
            ->json('data.id');

        $this->patchJson('/api/v1/employee-task-templates/'.$templateId, [
            'notes' => 'Coordinate with facilities before final closure.',
        ])->assertOk()
            ->assertJsonPath('data.notes', 'Coordinate with facilities before final closure.');

        $this->getJson('/api/v1/employee-task-templates?lifecycle_type=offboarding')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $response = $this->postJson('/api/v1/employees/'.$employee->id.'/lifecycle-tasks/apply-templates', [
            'template_ids' => [$templateId],
        ])->assertCreated()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.lifecycle_type', 'offboarding')
            ->assertJsonPath('data.0.template_id', $templateId);

        $taskId = $response->json('data.0.id');

        $this->assertDatabaseHas('employee_onboarding_tasks', [
            'id' => $taskId,
            'employee_id' => $employee->id,
            'template_id' => $templateId,
            'lifecycle_type' => 'offboarding',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.lifecycle_task_template.applied',
            'entity_id' => (string) $employee->id,
        ]);
    }

    public function test_offboarding_task_completion_can_trigger_and_finish_approval_workflow(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
            'user_id' => $managerUser->id,
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
            'manager_id' => $managerEmployee->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $taskId = $this->postJson('/api/v1/employees/'.$employee->id.'/lifecycle-tasks', [
            'lifecycle_type' => 'offboarding',
            'title' => 'Approve final asset clearance',
            'category' => 'hr',
            'task_type' => 'complete_forms',
            'assignee_type' => 'manager',
            'requires_approval' => true,
            'sort_order' => 1,
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->json('data.id');

        $response = $this->patchJson('/api/v1/employees/'.$employee->id.'/lifecycle-tasks/'.$taskId, [
            'lifecycle_type' => 'offboarding',
            'status' => 'completed',
        ])->assertOk()
            ->assertJsonPath('data.status', 'awaiting_approval');

        $workflowInstanceId = $response->json('data.workflow_instance_id');
        $this->assertNotNull($workflowInstanceId);

        $managerTask = WorkflowTask::withoutGlobalScopes()
            ->where('workflow_instance_id', $workflowInstanceId)
            ->where('sequence', 1)
            ->firstOrFail();

        $this->assertSame($managerUser->id, $managerTask->assigned_to_user_id);

        Sanctum::actingAs($managerUser);
        $this->patchJson('/api/v1/tasks/'.$managerTask->id, [
            'action' => 'approve',
        ])->assertOk();

        $hrTask = WorkflowTask::withoutGlobalScopes()
            ->where('workflow_instance_id', $workflowInstanceId)
            ->where('sequence', 2)
            ->firstOrFail();

        Sanctum::actingAs($hrAdmin);
        $this->patchJson('/api/v1/tasks/'.$hrTask->id, [
            'action' => 'approve',
            'comment' => 'Exit checks cleared.',
        ])->assertOk();

        $this->getJson('/api/v1/employees/'.$employee->id.'/lifecycle-tasks?lifecycle_type=offboarding')
            ->assertOk()
            ->assertJsonPath('data.items.0.status', 'completed');

        $task = EmployeeOnboardingTask::withoutGlobalScopes()->findOrFail($taskId);
        $this->assertNotNull($task->approved_at);
        $this->assertSame('completed', $task->status);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'employee.lifecycle_task.approved',
            'entity_id' => (string) $taskId,
        ]);
    }

    public function test_lifecycle_status_endpoint_filters_offboarding_without_regressing_onboarding_status(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $viewer = User::factory()->create(['company_id' => $company->id]);
        $viewer->givePermissionTo('employee.view');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);

        $onboardingEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP30001',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        $offboardingEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP30002',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        EmployeeOnboardingTask::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $onboardingEmployee->id,
            'lifecycle_type' => 'onboarding',
            'title' => 'Submit identity documents',
            'category' => 'hr',
            'assignee_type' => 'employee',
            'status' => 'pending',
        ]);

        EmployeeOnboardingTask::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $offboardingEmployee->id,
            'lifecycle_type' => 'offboarding',
            'title' => 'Revoke building access',
            'category' => 'security',
            'assignee_type' => 'hr',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/employees/lifecycle-task-status?lifecycle_type=offboarding')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee.id', $offboardingEmployee->id)
            ->assertJsonPath('data.0.lifecycle_type', 'offboarding');

        $this->getJson('/api/v1/employees/onboarding-status')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee.id', $onboardingEmployee->id)
            ->assertJsonPath('data.0.lifecycle_type', 'onboarding');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $viewer->id,
            'event_type' => 'employee.onboarding_status.viewed',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $viewer->id,
            'event_type' => 'employee.lifecycle_task_status.viewed',
        ]);
    }

    private function createOrganizationContext(int $companyId, string $suffix = ''): array
    {
        $department = Department::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'code' => 'DEP'.$suffix,
            'name' => 'Department'.$suffix,
            'status' => 'active',
        ]);

        $designation = Designation::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'code' => 'DES'.$suffix,
            'name' => 'Designation'.$suffix,
            'status' => 'active',
        ]);

        $location = Location::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'code' => 'LOC'.$suffix,
            'name' => 'Location'.$suffix,
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Bengaluru',
            'country' => 'India',
            'status' => 'active',
        ]);

        $costCenter = CostCenter::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'code' => 'CC'.$suffix,
            'name' => 'Cost Center'.$suffix,
            'status' => 'active',
        ]);

        return [$department, $designation, $location, $costCenter];
    }
}
