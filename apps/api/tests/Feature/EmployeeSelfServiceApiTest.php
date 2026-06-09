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
use App\Models\DocumentCategory;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeContact;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmergencyContact;
use App\Models\Location;
use App\Models\PolicyAcknowledgement;
use App\Models\User;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmployeeSelfServiceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionRoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config()->set('document_repository.disk', 'documents');
        config()->set('employee_documents.disk', 'employee-documents');
    }

    public function test_linked_employee_can_load_self_service_workspace_with_allowed_documents_and_assets(): void
    {
        Storage::fake('documents');
        Storage::fake('employee-documents');

        $company = Company::factory()->create(['status' => 'active']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);

        $manager = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
            'employment_status' => 'active',
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
            'manager_id' => $manager->id,
            'user_id' => $employeeUser->id,
            'employment_status' => 'active',
            'date_of_joining' => '2025-03-01',
            'email' => 'employee.viewer@phoenixhrms.test',
        ]);

        EmployeeContact::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'type' => 'email',
            'label' => 'Work email',
            'value' => $employee->email,
            'is_primary' => true,
            'status' => 'active',
        ]);

        EmployeeAddress::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'type' => 'current',
            'address_line_1' => '221 Phoenix Residency',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'country' => 'India',
            'postal_code' => '560001',
        ]);

        EmployeeEmergencyContact::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'name' => 'Family Contact',
            'relationship' => 'Sibling',
            'phone_number' => '+91 98888 10001',
            'priority' => 1,
        ]);

        $employeeDocumentPath = 'companies/'.$company->id.'/employees/'.$employee->id.'/documents/id-proof.pdf';
        Storage::disk('employee-documents')->put($employeeDocumentPath, 'employee document');

        EmployeeDocument::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'document_type' => 'Government ID',
            'original_file_name' => 'id-proof.pdf',
            'disk' => 'employee-documents',
            'file_path' => $employeeDocumentPath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('employee document'),
            'checksum_sha256' => hash('sha256', 'employee document'),
        ]);

        $visibleCategory = DocumentCategory::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'EMP-GUIDE',
            'name' => 'Employee Guides',
            'repository_scope' => 'employee',
            'default_visibility_scope' => 'restricted',
            'allowed_role_names' => ['employee'],
            'status' => 'active',
        ]);

        $hiddenCategory = DocumentCategory::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'CONF-HR',
            'name' => 'Confidential HR',
            'repository_scope' => 'employee',
            'default_visibility_scope' => 'confidential',
            'allowed_role_names' => ['hr.admin'],
            'status' => 'active',
        ]);

        $visibleRepositoryPath = 'companies/'.$company->id.'/documents/employee/employee/'.$employee->id.'/travel-guide.pdf';
        Storage::disk('documents')->put($visibleRepositoryPath, 'visible repository document');

        Document::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'document_category_id' => $visibleCategory->id,
            'title' => 'Travel Desk Guide',
            'repository_scope' => 'employee',
            'linked_entity_type' => 'employee',
            'linked_entity_id' => $employee->id,
            'visibility_scope' => 'restricted',
            'original_file_name' => 'travel-guide.pdf',
            'disk' => 'documents',
            'file_path' => $visibleRepositoryPath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('visible repository document'),
            'checksum_sha256' => hash('sha256', 'visible repository document'),
            'notes' => 'Employee travel process baseline.',
        ]);

        $hiddenRepositoryPath = 'companies/'.$company->id.'/documents/employee/employee/'.$employee->id.'/comp-sheet.pdf';
        Storage::disk('documents')->put($hiddenRepositoryPath, 'hidden repository document');

        Document::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'document_category_id' => $hiddenCategory->id,
            'title' => 'Compensation Worksheet',
            'repository_scope' => 'employee',
            'linked_entity_type' => 'employee',
            'linked_entity_id' => $employee->id,
            'visibility_scope' => 'confidential',
            'original_file_name' => 'comp-sheet.pdf',
            'disk' => 'documents',
            'file_path' => $hiddenRepositoryPath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('hidden repository document'),
            'checksum_sha256' => hash('sha256', 'hidden repository document'),
        ]);

        $policyPath = 'companies/'.$company->id.'/documents/policy/company/'.$company->id.'/code-of-conduct.pdf';
        Storage::disk('documents')->put($policyPath, 'policy file');

        $policyDocument = Document::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'title' => 'Code of Conduct',
            'repository_scope' => 'policy',
            'linked_entity_type' => 'company',
            'linked_entity_id' => $company->id,
            'visibility_scope' => 'internal',
            'original_file_name' => 'code-of-conduct.pdf',
            'disk' => 'documents',
            'file_path' => $policyPath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('policy file'),
            'checksum_sha256' => hash('sha256', 'policy file'),
        ]);

        PolicyAcknowledgement::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'document_id' => $policyDocument->id,
            'employee_id' => $employee->id,
            'policy_title' => 'Code of Conduct',
            'policy_version' => '2026.1',
            'status' => 'assigned',
            'due_date' => '2026-06-20',
        ]);

        $historicalPolicyPath = 'companies/'.$company->id.'/documents/policy/company/'.$company->id.'/code-of-conduct-2025.pdf';
        Storage::disk('documents')->put($historicalPolicyPath, 'historical policy file');

        $historicalPolicyDocument = Document::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'title' => 'Code of Conduct',
            'repository_scope' => 'policy',
            'linked_entity_type' => 'company',
            'linked_entity_id' => $company->id,
            'visibility_scope' => 'internal',
            'original_file_name' => 'code-of-conduct-2025.pdf',
            'disk' => 'documents',
            'file_path' => $historicalPolicyPath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('historical policy file'),
            'checksum_sha256' => hash('sha256', 'historical policy file'),
        ]);

        PolicyAcknowledgement::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'document_id' => $historicalPolicyDocument->id,
            'employee_id' => $employee->id,
            'policy_title' => 'Code of Conduct',
            'policy_version' => '2025.4',
            'status' => 'acknowledged',
            'due_date' => '2026-05-15',
            'acknowledged_by_user_id' => $employeeUser->id,
            'acknowledged_at' => '2026-05-10 10:00:00',
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
            'assigned_by_user_id' => $employeeUser->id,
            'issued_by_user_id' => $employeeUser->id,
        ]);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/self-service/workspace')
            ->assertOk()
            ->assertJsonPath('data.employee.id', $employee->id)
            ->assertJsonPath('data.employee.manager.id', $manager->id)
            ->assertJsonPath('data.profile.contacts.0.value', 'employee.viewer@phoenixhrms.test')
            ->assertJsonPath('data.documents.summary.total_count', 4)
            ->assertJsonPath('data.documents.summary.pending_acknowledgement_count', 1)
            ->assertJsonPath('data.documents.summary.acknowledged_count', 1)
            ->assertJsonPath('data.documents.summary.hidden_sensitive_count', 1)
            ->assertJsonPath('data.profile.sensitive_panels.bank_accounts.visible', false)
            ->assertJsonPath('data.assets.summary.active_count', 1)
            ->assertJsonFragment(['source_type' => 'policy_acknowledgement'])
            ->assertJsonFragment(['source_type' => 'employee_document'])
            ->assertJsonFragment(['source_type' => 'repository_document'])
            ->assertJsonFragment(['name' => 'Dell Latitude 7440'])
            ->assertJsonMissing(['title' => 'Compensation Worksheet']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'employee.self_service.viewed',
            'entity_id' => (string) $employee->id,
        ]);
    }

    public function test_linked_employee_can_download_visible_self_service_documents_but_not_hidden_repository_documents(): void
    {
        Storage::fake('documents');
        Storage::fake('employee-documents');

        $company = Company::factory()->create(['status' => 'active']);
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
            'employment_status' => 'active',
        ]);

        $employeeDocumentPath = 'companies/'.$company->id.'/employees/'.$employee->id.'/documents/id-proof.pdf';
        Storage::disk('employee-documents')->put($employeeDocumentPath, 'employee document');

        $employeeDocument = EmployeeDocument::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'document_type' => 'Government ID',
            'original_file_name' => 'id-proof.pdf',
            'disk' => 'employee-documents',
            'file_path' => $employeeDocumentPath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('employee document'),
            'checksum_sha256' => hash('sha256', 'employee document'),
        ]);

        $category = DocumentCategory::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'EMP-GUIDE',
            'name' => 'Employee Guides',
            'repository_scope' => 'employee',
            'default_visibility_scope' => 'restricted',
            'allowed_role_names' => ['employee'],
            'status' => 'active',
        ]);

        $hiddenCategory = DocumentCategory::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'CONF-HR',
            'name' => 'Confidential HR',
            'repository_scope' => 'employee',
            'default_visibility_scope' => 'confidential',
            'allowed_role_names' => ['hr.admin'],
            'status' => 'active',
        ]);

        $visibleRepositoryPath = 'companies/'.$company->id.'/documents/employee/employee/'.$employee->id.'/travel-guide.pdf';
        Storage::disk('documents')->put($visibleRepositoryPath, 'visible repository document');

        $visibleRepositoryDocument = Document::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'document_category_id' => $category->id,
            'title' => 'Travel Desk Guide',
            'repository_scope' => 'employee',
            'linked_entity_type' => 'employee',
            'linked_entity_id' => $employee->id,
            'visibility_scope' => 'restricted',
            'original_file_name' => 'travel-guide.pdf',
            'disk' => 'documents',
            'file_path' => $visibleRepositoryPath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('visible repository document'),
            'checksum_sha256' => hash('sha256', 'visible repository document'),
        ]);

        $hiddenRepositoryPath = 'companies/'.$company->id.'/documents/employee/employee/'.$employee->id.'/comp-sheet.pdf';
        Storage::disk('documents')->put($hiddenRepositoryPath, 'hidden repository document');

        $hiddenRepositoryDocument = Document::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'document_category_id' => $hiddenCategory->id,
            'title' => 'Compensation Worksheet',
            'repository_scope' => 'employee',
            'linked_entity_type' => 'employee',
            'linked_entity_id' => $employee->id,
            'visibility_scope' => 'confidential',
            'original_file_name' => 'comp-sheet.pdf',
            'disk' => 'documents',
            'file_path' => $hiddenRepositoryPath,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('hidden repository document'),
            'checksum_sha256' => hash('sha256', 'hidden repository document'),
        ]);

        Sanctum::actingAs($employeeUser);

        $this->get('/api/v1/self-service/employee-documents/'.$employeeDocument->id.'/download')
            ->assertOk()
            ->assertDownload('id-proof.pdf');

        $this->get('/api/v1/self-service/repository-documents/'.$visibleRepositoryDocument->id.'/download')
            ->assertOk()
            ->assertDownload('travel-guide.pdf');

        $this->get('/api/v1/self-service/repository-documents/'.$hiddenRepositoryDocument->id.'/download')
            ->assertNotFound();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'employee.document.downloaded',
            'entity_id' => (string) $employeeDocument->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'employee.self_service.repository_document.downloaded',
            'entity_id' => (string) $visibleRepositoryDocument->id,
        ]);
    }

    public function test_self_service_workspace_requires_a_linked_employee_profile(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/self-service/workspace')
            ->assertNotFound();

        $this->get('/api/v1/self-service/employee-documents/999/download')
            ->assertNotFound();

        $this->get('/api/v1/self-service/repository-documents/999/download')
            ->assertNotFound();
    }

    private function createOrganizationContext(int $companyId): array
    {
        $department = Department::factory()->create([
            'company_id' => $companyId,
            'code' => 'PEO',
            'name' => 'People Operations',
        ]);

        $designation = Designation::factory()->create([
            'company_id' => $companyId,
            'code' => 'HRBP',
            'name' => 'HRBP',
        ]);

        $location = Location::factory()->create([
            'company_id' => $companyId,
            'code' => 'BLR',
            'name' => 'Bengaluru',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Bengaluru',
            'country' => 'India',
        ]);

        $costCenter = CostCenter::factory()->create([
            'company_id' => $companyId,
            'code' => 'PEO-01',
            'name' => 'People Operations',
        ]);

        return [$department, $designation, $location, $costCenter];
    }
}
