<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use App\Models\Employee;
use App\Models\EmployeeOnboardingTask;
use App\Models\Location;
use App\Models\PolicyAcknowledgement;
use App\Models\User;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmployeeTaskCenterApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionRoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config()->set('document_repository.disk', 'documents');
    }

    public function test_hr_can_assign_track_download_and_acknowledge_policy_documents(): void
    {
        Storage::fake('documents');

        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);
        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
            'user_id' => $employeeUser->id,
        ]);

        $path = 'companies/'.$company->id.'/documents/policy/company/'.$company->id.'/code-of-conduct.pdf';
        Storage::disk('documents')->put($path, 'policy file');

        $document = Document::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'title' => 'Code of Conduct',
            'repository_scope' => 'policy',
            'linked_entity_type' => 'company',
            'linked_entity_id' => $company->id,
            'visibility_scope' => 'internal',
            'original_file_name' => 'code-of-conduct.pdf',
            'disk' => 'documents',
            'file_path' => $path,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('policy file'),
            'checksum_sha256' => hash('sha256', 'policy file'),
        ]);

        Sanctum::actingAs($hrAdmin);

        $acknowledgementId = $this->postJson('/api/v1/policy-acknowledgements', [
            'document_id' => $document->id,
            'employee_ids' => [$employee->id],
            'policy_version' => '2026.1',
            'due_date' => '2026-06-20',
            'assignment_notes' => 'Required for all active employees.',
        ])->assertCreated()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.policy_version', '2026.1')
            ->json('data.0.id');

        $this->getJson('/api/v1/policy-acknowledgements?employee_id='.$employee->id)
            ->assertOk()
            ->assertJsonPath('data.0.id', $acknowledgementId);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/policy-acknowledgements')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $acknowledgementId)
            ->assertJsonPath('data.0.document.download_url', '/api/v1/policy-acknowledgements/'.$acknowledgementId.'/download');

        $this->get('/api/v1/policy-acknowledgements/'.$acknowledgementId.'/download')
            ->assertOk()
            ->assertDownload('code-of-conduct.pdf');

        $this->patchJson('/api/v1/policy-acknowledgements/'.$acknowledgementId.'/acknowledge', [
            'acknowledgement_notes' => 'Reviewed and accepted.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'acknowledged');

        $this->assertDatabaseHas('policy_acknowledgements', [
            'id' => $acknowledgementId,
            'status' => 'acknowledged',
            'acknowledged_by_user_id' => $employeeUser->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'employee.policy_acknowledgement.assigned',
            'entity_id' => (string) $acknowledgementId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'employee.policy_acknowledgement.downloaded',
            'entity_id' => (string) $acknowledgementId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'employee.policy_acknowledgement.acknowledged',
            'entity_id' => (string) $acknowledgementId,
        ]);
    }

    public function test_employee_task_center_aggregates_policy_lifecycle_and_asset_items_and_can_complete_owned_task(): void
    {
        Storage::fake('documents');

        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);
        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
            'user_id' => $employeeUser->id,
        ]);

        $document = Document::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'title' => 'Remote Work Policy',
            'repository_scope' => 'policy',
            'linked_entity_type' => 'company',
            'linked_entity_id' => $company->id,
            'visibility_scope' => 'internal',
            'original_file_name' => 'remote-work-policy.pdf',
            'disk' => 'documents',
            'file_path' => 'companies/'.$company->id.'/documents/policy/company/'.$company->id.'/remote-work-policy.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('policy'),
            'checksum_sha256' => hash('sha256', 'policy'),
        ]);

        PolicyAcknowledgement::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'document_id' => $document->id,
            'employee_id' => $employee->id,
            'policy_title' => 'Remote Work Policy',
            'policy_version' => '2026.2',
            'status' => 'assigned',
            'due_date' => '2026-06-21',
        ]);

        $lifecycleTask = EmployeeOnboardingTask::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'lifecycle_type' => 'onboarding',
            'title' => 'Upload PAN card',
            'category' => 'hr',
            'task_type' => 'submit_documents',
            'assignee_type' => 'employee',
            'assigned_to_user_id' => $employeeUser->id,
            'status' => 'pending',
            'due_date' => '2026-06-18',
        ]);

        $assetCategory = AssetCategory::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'LAPTOP',
            'name' => 'Laptop',
            'status' => 'active',
        ]);

        $asset = Asset::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'asset_category_id' => $assetCategory->id,
            'asset_tag' => 'AST-LTP-3001',
            'name' => 'Dell Latitude 7440',
            'asset_type' => 'physical',
            'status' => 'issued',
        ]);

        AssetAssignment::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'asset_id' => $asset->id,
            'employee_id' => $employee->id,
            'status' => 'issued',
            'assigned_at' => '2026-06-10 09:00:00',
            'issued_at' => '2026-06-10 10:00:00',
            'expected_return_date' => '2027-06-10',
            'assigned_by_user_id' => $hrAdmin->id,
            'issued_by_user_id' => $hrAdmin->id,
        ]);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/task-center')
            ->assertOk()
            ->assertJsonPath('data.summary.total_count', 3)
            ->assertJsonPath('data.summary.policy_count', 1)
            ->assertJsonPath('data.summary.lifecycle_task_count', 1)
            ->assertJsonPath('data.summary.asset_count', 1)
            ->assertJsonFragment(['source_type' => 'policy_acknowledgement'])
            ->assertJsonFragment(['source_type' => 'lifecycle_task'])
            ->assertJsonFragment(['source_type' => 'asset_assignment'])
            ->assertJsonFragment(['action_domain' => 'document'])
            ->assertJsonFragment(['action_domain' => 'asset']);

        $this->patchJson('/api/v1/task-center/lifecycle-tasks/'.$lifecycleTask->id, [
            'status' => 'completed',
            'notes' => 'Uploaded through the task center.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('employee_onboarding_tasks', [
            'id' => $lifecycleTask->id,
            'status' => 'completed',
            'completed_by_user_id' => $employeeUser->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'employee.task_center.viewed',
            'entity_id' => (string) $employee->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'employee.lifecycle_task.updated',
            'entity_id' => (string) $lifecycleTask->id,
        ]);
    }

    public function test_self_service_policy_and_task_center_routes_enforce_linked_employee_scope(): void
    {
        Storage::fake('documents');

        $company = Company::factory()->create(['status' => 'active']);

        $userA = User::factory()->create(['company_id' => $company->id]);
        $userA->assignRole('employee');
        $userB = User::factory()->create(['company_id' => $company->id]);
        $userB->assignRole('employee');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);
        $employeeA = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
            'user_id' => $userA->id,
        ]);
        $employeeB = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
            'user_id' => $userB->id,
        ]);

        $document = Document::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'title' => 'Security Policy',
            'repository_scope' => 'policy',
            'linked_entity_type' => 'company',
            'linked_entity_id' => $company->id,
            'visibility_scope' => 'internal',
            'original_file_name' => 'security-policy.pdf',
            'disk' => 'documents',
            'file_path' => 'companies/'.$company->id.'/documents/policy/company/'.$company->id.'/security-policy.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('security policy'),
            'checksum_sha256' => hash('sha256', 'security policy'),
        ]);

        $acknowledgement = PolicyAcknowledgement::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'document_id' => $document->id,
            'employee_id' => $employeeA->id,
            'policy_title' => 'Security Policy',
            'status' => 'assigned',
        ]);

        $task = EmployeeOnboardingTask::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $employeeA->id,
            'lifecycle_type' => 'onboarding',
            'title' => 'Acknowledge information-security checklist',
            'category' => 'compliance',
            'task_type' => 'read_policy',
            'assignee_type' => 'employee',
            'assigned_to_user_id' => $userA->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($userB);

        $this->patchJson('/api/v1/policy-acknowledgements/'.$acknowledgement->id.'/acknowledge', [])
            ->assertNotFound();

        $this->get('/api/v1/policy-acknowledgements/'.$acknowledgement->id.'/download')
            ->assertNotFound();

        $this->patchJson('/api/v1/task-center/lifecycle-tasks/'.$task->id, [
            'status' => 'completed',
        ])->assertNotFound();

        $this->getJson('/api/v1/task-center')
            ->assertOk()
            ->assertJsonPath('data.employee.id', $employeeB->id)
            ->assertJsonPath('data.summary.total_count', 0);
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
