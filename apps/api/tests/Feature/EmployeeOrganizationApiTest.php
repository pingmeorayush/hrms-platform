<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeDocument;
use App\Models\EmployeeOnboardingTask;
use App\Models\Location;
use App\Models\TenantSetting;
use App\Models\User;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmployeeOrganizationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionRoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_tenant_admin_can_manage_company_profile_and_organization_masters(): void
    {
        $tenantAdmin = User::factory()->create();
        $tenantAdmin->assignRole('tenant.admin');

        Department::withoutGlobalScopes()->create([
            'company_id' => Company::factory()->create()->id,
            'code' => 'EXT-HR',
            'name' => 'External HR',
            'status' => 'active',
        ]);

        Sanctum::actingAs($tenantAdmin);

        $this->getJson('/api/v1/organization/company-profile')
            ->assertOk()
            ->assertJsonPath('data.id', $tenantAdmin->company_id);

        $this->patchJson('/api/v1/organization/company-profile', [
            'name' => 'Phoenix India HQ',
            'subscription_plan' => 'enterprise-plus',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Phoenix India HQ')
            ->assertJsonPath('data.currency', 'INR');

        $departmentId = $this->postJson('/api/v1/organization/departments', [
            'code' => 'HR',
            'name' => 'Human Resources',
        ])->assertCreated()
            ->assertJsonPath('data.code', 'HR')
            ->json('data.id');

        $this->postJson('/api/v1/organization/designations', [
            'code' => 'HR-EXEC',
            'name' => 'HR Executive',
        ])->assertCreated();

        $this->postJson('/api/v1/organization/locations', [
            'code' => 'BLR',
            'name' => 'Bangalore HQ',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Bengaluru',
            'country' => 'India',
        ])->assertCreated();

        $this->postJson('/api/v1/organization/cost-centers', [
            'code' => 'CC-HR',
            'name' => 'HR Operations',
        ])->assertCreated();

        $this->patchJson("/api/v1/organization/departments/{$departmentId}", [
            'code' => 'HR',
            'name' => 'People Operations',
            'description' => 'Updated department name.',
            'status' => 'active',
        ])->assertOk()
            ->assertJsonPath('data.name', 'People Operations');

        $departmentListing = $this->getJson('/api/v1/organization/departments')
            ->assertOk();

        $this->assertSame(
            ['People Operations'],
            collect($departmentListing->json('data'))->pluck('name')->all(),
        );

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'organization.company.updated',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'organization.department.updated',
        ]);
    }

    public function test_hr_admin_can_create_employee_with_generated_code_and_initial_history(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);

        $manager = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP00001',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $response = $this->postJson('/api/v1/employees', [
            'first_name' => 'Asha',
            'last_name' => 'Sharma',
            'email' => 'asha.sharma@phoenixhrms.test',
            'date_of_joining' => '2026-06-01',
            'employment_type' => 'full_time',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'manager_id' => $manager->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ])->assertCreated()
            ->assertJsonPath('data.first_name', 'Asha')
            ->assertJsonPath('data.manager.id', $manager->id);

        $employeeId = $response->json('data.id');

        $this->assertSame('EMP00002', $response->json('data.employee_code'));

        $this->assertDatabaseHas('employment_histories', [
            'employee_id' => $employeeId,
            'action' => 'created',
            'changed_by_user_id' => $hrAdmin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.record.created',
            'entity_id' => (string) $employeeId,
        ]);
    }

    public function test_manual_employee_code_policy_requires_explicit_unique_codes(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);

        TenantSetting::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'key' => 'employee.code_policy',
            'value' => [
                'mode' => 'manual',
                'prefix' => 'LEG',
                'number_padding' => 4,
            ],
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/employees', [
            'first_name' => 'Riya',
            'last_name' => 'Kapoor',
            'email' => 'riya.kapoor@phoenixhrms.test',
            'date_of_joining' => '2026-06-01',
            'employment_type' => 'full_time',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ])->assertStatus(422)
            ->assertJsonPath(
                'errors.employee_code.0',
                'Employee code is required when the tenant uses manual employee codes.',
            );

        $this->postJson('/api/v1/employees', [
            'employee_code' => 'LEG1001',
            'first_name' => 'Riya',
            'last_name' => 'Kapoor',
            'email' => 'riya.kapoor@phoenixhrms.test',
            'date_of_joining' => '2026-06-01',
            'employment_type' => 'full_time',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ])->assertCreated()
            ->assertJsonPath('data.employee_code', 'LEG1001');

        $this->postJson('/api/v1/employees', [
            'employee_code' => 'LEG1001',
            'first_name' => 'Naina',
            'last_name' => 'Kapoor',
            'email' => 'naina.kapoor@phoenixhrms.test',
            'date_of_joining' => '2026-06-01',
            'employment_type' => 'full_time',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['employee_code']);
    }

    public function test_employee_read_endpoints_are_tenant_scoped_and_support_filters(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$departmentA, $designationA, $locationA, $costCenterA] = $this->createOrganizationContext($company->id, 'A');
        [$departmentB, $designationB, $locationB, $costCenterB] = $this->createOrganizationContext($company->id, 'B');

        $employeeA = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP00010',
            'department_id' => $departmentA->id,
            'designation_id' => $designationA->id,
            'location_id' => $locationA->id,
            'cost_center_id' => $costCenterA->id,
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP00011',
            'department_id' => $departmentB->id,
            'designation_id' => $designationB->id,
            'location_id' => $locationB->id,
            'cost_center_id' => $costCenterB->id,
        ]);

        $otherCompany = Company::factory()->create(['status' => 'active']);
        [$otherDepartment, $otherDesignation, $otherLocation, $otherCostCenter] = $this->createOrganizationContext($otherCompany->id, 'X');

        $otherEmployee = Employee::factory()->create([
            'company_id' => $otherCompany->id,
            'employee_code' => 'EMP90001',
            'department_id' => $otherDepartment->id,
            'designation_id' => $otherDesignation->id,
            'location_id' => $otherLocation->id,
            'cost_center_id' => $otherCostCenter->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->getJson('/api/v1/employees?department_id='.$departmentA->id)
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $employeeA->id);

        $this->getJson('/api/v1/employees/'.$employeeA->id)
            ->assertOk()
            ->assertJsonPath('data.employee_code', 'EMP00010');

        $this->getJson('/api/v1/employees/'.$otherEmployee->id)
            ->assertStatus(404);
    }

    public function test_employee_directory_supports_search_and_status_designation_and_manager_filters(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $designationA, $location, $costCenter] = $this->createOrganizationContext($company->id, 'S');
        [, $designationB] = $this->createOrganizationContext($company->id, 'T');

        $manager = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP20001',
            'department_id' => $department->id,
            'designation_id' => $designationA->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        $matchingEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP20002',
            'first_name' => 'Priya',
            'last_name' => 'Nair',
            'email' => 'priya.nair@phoenixhrms.test',
            'employment_status' => 'probation',
            'department_id' => $department->id,
            'designation_id' => $designationB->id,
            'manager_id' => $manager->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP20003',
            'first_name' => 'Rahul',
            'last_name' => 'Iyer',
            'email' => 'rahul.iyer@phoenixhrms.test',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'designation_id' => $designationA->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->getJson('/api/v1/employees?search=priya.nair')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $matchingEmployee->id);

        $this->getJson('/api/v1/employees?search=Priya%20Nair')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $matchingEmployee->id);

        $this->getJson('/api/v1/employees?employment_status=probation&designation_id='.$designationB->id.'&manager_id='.$manager->id)
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $matchingEmployee->id);
    }

    public function test_hr_admin_can_update_employee_profile_and_audit_before_after_state(): void
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
            'first_name' => 'Ananya',
            'phone' => '9000000001',
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->patchJson('/api/v1/employees/'.$employee->id, [
            'first_name' => 'Ana',
            'phone' => '9000000009',
            'marital_status' => 'married',
        ])->assertOk()
            ->assertJsonPath('data.first_name', 'Ana')
            ->assertJsonPath('data.phone', '9000000009')
            ->assertJsonPath('data.marital_status', 'married');

        $auditLog = AuditLog::withoutGlobalScopes()
            ->where('event_type', 'employee.record.updated')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('Ananya', $auditLog->metadata['before']['first_name']);
        $this->assertSame('Ana', $auditLog->metadata['after']['first_name']);
        $this->assertSame('9000000001', $auditLog->metadata['before']['phone']);
        $this->assertSame('9000000009', $auditLog->metadata['after']['phone']);
    }

    public function test_hr_admin_can_transfer_employee_and_preserve_reporting_history(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$departmentA, $designationA, $locationA, $costCenterA] = $this->createOrganizationContext($company->id, 'A');
        [$departmentB, $designationB, $locationB, $costCenterB] = $this->createOrganizationContext($company->id, 'B');

        $managerA = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $departmentA->id,
            'designation_id' => $designationA->id,
            'location_id' => $locationA->id,
            'cost_center_id' => $costCenterA->id,
        ]);

        $managerB = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $departmentB->id,
            'designation_id' => $designationB->id,
            'location_id' => $locationB->id,
            'cost_center_id' => $costCenterB->id,
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $departmentA->id,
            'designation_id' => $designationA->id,
            'manager_id' => $managerA->id,
            'location_id' => $locationA->id,
            'cost_center_id' => $costCenterA->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/employees/'.$employee->id.'/transfer', [
            'effective_date' => '2026-07-01',
            'department_id' => $departmentB->id,
            'manager_id' => $managerB->id,
            'location_id' => $locationB->id,
            'cost_center_id' => $costCenterB->id,
            'notes' => 'Business unit realignment.',
        ])->assertOk()
            ->assertJsonPath('data.department.id', $departmentB->id)
            ->assertJsonPath('data.manager.id', $managerB->id)
            ->assertJsonPath('data.location.id', $locationB->id)
            ->assertJsonPath('data.cost_center.id', $costCenterB->id);

        $this->assertDatabaseHas('employment_histories', [
            'employee_id' => $employee->id,
            'action' => 'transferred',
            'previous_department_id' => $departmentA->id,
            'department_id' => $departmentB->id,
            'previous_manager_id' => $managerA->id,
            'manager_id' => $managerB->id,
            'previous_location_id' => $locationA->id,
            'location_id' => $locationB->id,
        ]);

        $auditLog = AuditLog::withoutGlobalScopes()
            ->where('event_type', 'employee.record.transferred')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($departmentA->id, $auditLog->metadata['before']['department_id']);
        $this->assertSame($departmentB->id, $auditLog->metadata['after']['department_id']);
    }

    public function test_hr_admin_can_promote_employee_and_preserve_designation_history(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $designationA, $location, $costCenter] = $this->createOrganizationContext($company->id, 'A');
        [, $designationB] = $this->createOrganizationContext($company->id, 'B');

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designationA->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/employees/'.$employee->id.'/promote', [
            'effective_date' => '2026-08-01',
            'designation_id' => $designationB->id,
            'notes' => 'Promotion to senior role.',
        ])->assertOk()
            ->assertJsonPath('data.designation.id', $designationB->id);

        $this->assertDatabaseHas('employment_histories', [
            'employee_id' => $employee->id,
            'action' => 'promoted',
            'previous_designation_id' => $designationA->id,
            'designation_id' => $designationB->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.record.promoted',
            'entity_id' => (string) $employee->id,
        ]);
    }

    public function test_termination_retains_employee_record_and_disables_linked_user_access(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $linkedUser = User::factory()->create(['company_id' => $company->id]);
        $token = $linkedUser->createToken('browser');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
            'user_id' => $linkedUser->id,
            'employment_status' => 'active',
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/employees/'.$employee->id.'/terminate', [
            'termination_date' => '2026-09-01',
            'reason' => 'Role redundancy after restructuring.',
            'notes' => 'Offboarding initiated.',
        ])->assertOk()
            ->assertJsonPath('data.employment_status', 'terminated')
            ->assertJsonPath('data.termination_reason', 'Role redundancy after restructuring.');

        $linkedUser->refresh();

        $this->assertFalse($linkedUser->is_active);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'employment_status' => 'terminated',
            'termination_reason' => 'Role redundancy after restructuring.',
        ]);

        $this->assertDatabaseHas('employment_histories', [
            'employee_id' => $employee->id,
            'action' => 'terminated',
            'previous_employment_status' => 'active',
            'employment_status' => 'terminated',
        ]);

        $auditLog = AuditLog::withoutGlobalScopes()
            ->where('event_type', 'employee.record.terminated')
            ->latest('id')
            ->firstOrFail();

        $this->assertTrue($auditLog->metadata['user_deactivated']);
    }

    public function test_hr_admin_can_create_encrypted_employee_bank_account(): void
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

        $response = $this->postJson('/api/v1/employees/'.$employee->id.'/bank-accounts', [
            'account_holder_name' => 'Asha Sharma',
            'bank_name' => 'State Bank of India',
            'branch_name' => 'MG Road',
            'account_number' => '123456789012',
            'ifsc_code' => 'SBIN0000123',
            'status' => 'active',
            'is_primary' => true,
        ])->assertCreated()
            ->assertJsonPath('data.account_number', '123456789012')
            ->assertJsonPath('data.sensitive_access', 'full');

        $bankAccountId = $response->json('data.id');
        $rawRecord = DB::table('employee_bank_accounts')->where('id', $bankAccountId)->first();

        $this->assertNotNull($rawRecord);
        $this->assertNotSame('Asha Sharma', $rawRecord->account_holder_name);
        $this->assertNotSame('State Bank of India', $rawRecord->bank_name);
        $this->assertNotSame('123456789012', $rawRecord->account_number);
        $this->assertNotSame('SBIN0000123', $rawRecord->ifsc_code);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.bank_account.created',
            'entity_id' => (string) $bankAccountId,
        ]);
    }

    public function test_view_only_bank_access_returns_masked_fields_and_writes_a_view_audit_log(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $viewer = User::factory()->create(['company_id' => $company->id]);
        $viewer->givePermissionTo('employee.bank.view');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        EmployeeBankAccount::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'account_holder_name' => 'Asha Sharma',
            'bank_name' => 'State Bank of India',
            'branch_name' => 'MG Road',
            'account_number' => '123456789012',
            'ifsc_code' => 'SBIN0000123',
            'status' => 'active',
            'is_primary' => true,
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/employees/'.$employee->id.'/bank-accounts')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.account_number', '********9012')
            ->assertJsonPath('data.items.0.ifsc_code', '*******0123')
            ->assertJsonPath('data.items.0.sensitive_access', 'masked');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $viewer->id,
            'event_type' => 'employee.bank_account.viewed',
            'entity_id' => (string) $employee->id,
        ]);
    }

    public function test_primary_employee_bank_account_can_be_switched(): void
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

        $primary = EmployeeBankAccount::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'account_holder_name' => 'Asha Sharma',
            'bank_name' => 'State Bank of India',
            'account_number' => '123456789012',
            'status' => 'active',
            'is_primary' => true,
        ]);

        $secondary = EmployeeBankAccount::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'account_holder_name' => 'Asha Sharma',
            'bank_name' => 'ICICI Bank',
            'account_number' => '987654321098',
            'status' => 'active',
            'is_primary' => false,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->patchJson('/api/v1/employees/'.$employee->id.'/bank-accounts/'.$secondary->id, [
            'is_primary' => true,
            'notes' => 'Payroll moved to the new salary account.',
        ])->assertOk()
            ->assertJsonPath('data.is_primary', true);

        $primary->refresh();
        $secondary->refresh();

        $this->assertFalse($primary->is_primary);
        $this->assertTrue($secondary->is_primary);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.bank_account.updated',
            'entity_id' => (string) $secondary->id,
        ]);
    }

    public function test_hr_admin_can_manage_employee_contacts_and_keep_one_primary_per_type(): void
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

        $primaryContactId = $this->postJson('/api/v1/employees/'.$employee->id.'/contacts', [
            'type' => 'phone',
            'label' => 'Primary mobile',
            'value' => '+91-9000000001',
            'is_primary' => true,
        ])->assertCreated()
            ->assertJsonPath('data.is_primary', true)
            ->json('data.id');

        $secondaryContactId = $this->postJson('/api/v1/employees/'.$employee->id.'/contacts', [
            'type' => 'phone',
            'label' => 'Backup mobile',
            'value' => '+91-9000000002',
            'is_primary' => false,
        ])->assertCreated()
            ->json('data.id');

        $this->patchJson('/api/v1/employees/'.$employee->id.'/contacts/'.$secondaryContactId, [
            'is_primary' => true,
            'label' => 'New primary mobile',
        ])->assertOk()
            ->assertJsonPath('data.is_primary', true);

        $listing = $this->getJson('/api/v1/employees/'.$employee->id.'/contacts')
            ->assertOk();

        $this->assertSame($primaryContactId, $listing->json('data.0.id'));

        $this->assertDatabaseHas('employee_contacts', [
            'id' => $primaryContactId,
            'is_primary' => false,
        ]);

        $this->assertDatabaseHas('employee_contacts', [
            'id' => $secondaryContactId,
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.contact.updated',
            'entity_id' => (string) $secondaryContactId,
        ]);
    }

    public function test_employee_addresses_support_multiple_address_types(): void
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

        $this->postJson('/api/v1/employees/'.$employee->id.'/addresses', [
            'type' => 'current',
            'address_line_1' => '12 Residency Road',
            'city' => 'Bengaluru',
            'country' => 'India',
            'postal_code' => '560025',
        ])->assertCreated();

        $officeAddressId = $this->postJson('/api/v1/employees/'.$employee->id.'/addresses', [
            'type' => 'office',
            'address_line_1' => 'Phoenix Tower, MG Road',
            'address_line_2' => 'Floor 8',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'country' => 'India',
            'postal_code' => '560001',
        ])->assertCreated()
            ->json('data.id');

        $this->patchJson('/api/v1/employees/'.$employee->id.'/addresses/'.$officeAddressId, [
            'city' => 'Bangalore',
            'notes' => 'Corporate office address.',
        ])->assertOk()
            ->assertJsonPath('data.city', 'Bangalore');

        $this->getJson('/api/v1/employees/'.$employee->id.'/addresses')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.address.updated',
            'entity_id' => (string) $officeAddressId,
        ]);
    }

    public function test_emergency_contacts_can_be_added_updated_and_validated(): void
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

        $this->postJson('/api/v1/employees/'.$employee->id.'/emergency-contacts', [
            'name' => 'Parent Contact',
            'relationship' => 'Father',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);

        $contactId = $this->postJson('/api/v1/employees/'.$employee->id.'/emergency-contacts', [
            'name' => 'Parent Contact',
            'relationship' => 'Father',
            'phone_number' => '+91-9888888888',
            'email' => 'parent@example.test',
            'priority' => 1,
        ])->assertCreated()
            ->json('data.id');

        $this->patchJson('/api/v1/employees/'.$employee->id.'/emergency-contacts/'.$contactId, [
            'priority' => 2,
            'notes' => 'Use only after 6 PM.',
        ])->assertOk()
            ->assertJsonPath('data.priority', 2);

        $this->getJson('/api/v1/employees/'.$employee->id.'/emergency-contacts')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.emergency_contact.updated',
            'entity_id' => (string) $contactId,
        ]);
    }

    public function test_profile_detail_endpoints_respect_tenant_scope(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);
        $otherCompany = Company::factory()->create(['status' => 'active']);
        [$otherDepartment, $otherDesignation, $otherLocation, $otherCostCenter] = $this->createOrganizationContext($otherCompany->id, 'X');

        $otherEmployee = Employee::factory()->create([
            'company_id' => $otherCompany->id,
            'department_id' => $otherDepartment->id,
            'designation_id' => $otherDesignation->id,
            'location_id' => $otherLocation->id,
            'cost_center_id' => $otherCostCenter->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->getJson('/api/v1/employees/'.$otherEmployee->id.'/contacts')
            ->assertStatus(404);

        $this->postJson('/api/v1/employees/'.$otherEmployee->id.'/addresses', [
            'type' => 'current',
            'address_line_1' => 'Blocked address',
            'city' => 'Delhi',
            'country' => 'India',
            'postal_code' => '110001',
        ])->assertStatus(404);
    }

    public function test_hr_admin_can_upload_employee_documents_with_approved_file_types(): void
    {
        Storage::fake('local');

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

        $response = $this->post('/api/v1/employees/'.$employee->id.'/documents', [
            'document_type' => 'employment_contract',
            'expiry_date' => '2027-06-01',
            'notes' => 'Signed joining pack.',
            'file' => UploadedFile::fake()->create(
                'offer-letter.pdf',
                256,
                'application/pdf',
            ),
        ], ['Accept' => 'application/json'])->assertCreated()
            ->assertJsonPath('data.document_type', 'employment_contract')
            ->assertJsonPath('data.original_file_name', 'offer-letter.pdf');

        $documentId = $response->json('data.id');
        $document = EmployeeDocument::query()->findOrFail($documentId);

        Storage::disk('local')->assertExists($document->file_path);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.document.uploaded',
            'entity_id' => (string) $documentId,
        ]);
    }

    public function test_employee_documents_can_be_listed_and_downloaded_by_authorized_users(): void
    {
        Storage::fake('local');

        $company = Company::factory()->create(['status' => 'active']);
        $viewer = User::factory()->create(['company_id' => $company->id]);
        $viewer->givePermissionTo('employee.view');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);
        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        $path = 'companies/'.$company->id.'/employees/'.$employee->id.'/documents/onboarding-passport.pdf';
        Storage::disk('local')->put($path, 'passport scan');

        $document = EmployeeDocument::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'document_type' => 'identity_proof',
            'original_file_name' => 'passport.pdf',
            'disk' => 'local',
            'file_path' => $path,
            'mime_type' => 'application/pdf',
            'file_size_bytes' => strlen('passport scan'),
            'checksum_sha256' => hash('sha256', 'passport scan'),
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/employees/'.$employee->id.'/documents')
            ->assertOk()
            ->assertJsonPath('data.0.id', $document->id)
            ->assertJsonPath('data.0.download_url', '/api/v1/employees/'.$employee->id.'/documents/'.$document->id.'/download');

        $this->get('/api/v1/employees/'.$employee->id.'/documents/'.$document->id.'/download')
            ->assertOk()
            ->assertDownload('passport.pdf');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $viewer->id,
            'event_type' => 'employee.document.listed',
            'entity_id' => (string) $employee->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $viewer->id,
            'event_type' => 'employee.document.downloaded',
            'entity_id' => (string) $document->id,
        ]);
    }

    public function test_employee_document_endpoints_enforce_file_policy_and_tenant_scope(): void
    {
        Storage::fake('local');

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

        $otherCompany = Company::factory()->create(['status' => 'active']);
        [$otherDepartment, $otherDesignation, $otherLocation, $otherCostCenter] = $this->createOrganizationContext($otherCompany->id, 'DOC');
        $otherEmployee = Employee::factory()->create([
            'company_id' => $otherCompany->id,
            'department_id' => $otherDepartment->id,
            'designation_id' => $otherDesignation->id,
            'location_id' => $otherLocation->id,
            'cost_center_id' => $otherCostCenter->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->post('/api/v1/employees/'.$employee->id.'/documents', [
            'document_type' => 'identity_proof',
            'file' => UploadedFile::fake()->create(
                'script.exe',
                32,
                'application/octet-stream',
            ),
        ], ['Accept' => 'application/json'])->assertStatus(422)
            ->assertJsonValidationErrors(['file']);

        $this->getJson('/api/v1/employees/'.$otherEmployee->id.'/documents')
            ->assertStatus(404);
    }

    public function test_hr_admin_can_record_update_onboarding_tasks_and_progress_is_derived(): void
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

        $firstTaskId = $this->postJson('/api/v1/employees/'.$employee->id.'/onboarding-tasks', [
            'title' => 'Collect signed employment contract',
            'category' => 'hr',
            'task_type' => 'submit_documents',
            'assignee_type' => 'employee',
            'sort_order' => 1,
            'due_date' => '2026-06-05',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->json('data.id');

        $this->postJson('/api/v1/employees/'.$employee->id.'/onboarding-tasks', [
            'title' => 'Provision laptop and email',
            'category' => 'it',
            'task_type' => 'setup_equipment',
            'assignee_type' => 'it_team',
            'status' => 'completed',
            'sort_order' => 2,
        ])->assertCreated();

        $thirdTaskId = $this->postJson('/api/v1/employees/'.$employee->id.'/onboarding-tasks', [
            'title' => 'Complete induction training',
            'category' => 'training',
            'task_type' => 'complete_training',
            'assignee_type' => 'employee',
            'status' => 'in_progress',
            'sort_order' => 3,
        ])->assertCreated()
            ->json('data.id');

        $this->patchJson('/api/v1/employees/'.$employee->id.'/onboarding-tasks/'.$firstTaskId, [
            'status' => 'skipped',
            'notes' => 'Handled in preboarding pack.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'skipped');

        $response = $this->getJson('/api/v1/employees/'.$employee->id.'/onboarding-tasks')
            ->assertOk()
            ->assertJsonPath('data.summary.total_count', 3)
            ->assertJsonPath('data.summary.completed_count', 1)
            ->assertJsonPath('data.summary.skipped_count', 1)
            ->assertJsonPath('data.summary.incomplete_count', 1)
            ->assertJsonPath('data.summary.progress_percentage', 67);

        $this->assertSame($firstTaskId, $response->json('data.items.0.id'));
        $this->assertSame($thirdTaskId, $response->json('data.items.2.id'));

        $this->assertDatabaseHas('employee_onboarding_tasks', [
            'id' => $firstTaskId,
            'status' => 'skipped',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.onboarding_task.updated',
            'entity_id' => (string) $firstTaskId,
        ]);
    }

    public function test_hr_can_view_incomplete_onboarding_status(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $viewer = User::factory()->create(['company_id' => $company->id]);
        $viewer->givePermissionTo('employee.view');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id);

        $incompleteEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP10001',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        $completeEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP10002',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        EmployeeOnboardingTask::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $incompleteEmployee->id,
            'title' => 'Submit ID proof',
            'category' => 'hr',
            'assignee_type' => 'employee',
            'status' => 'pending',
        ]);

        EmployeeOnboardingTask::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $incompleteEmployee->id,
            'title' => 'Assign buddy',
            'category' => 'manager',
            'assignee_type' => 'manager',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        EmployeeOnboardingTask::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'employee_id' => $completeEmployee->id,
            'title' => 'Activate account',
            'category' => 'it',
            'assignee_type' => 'it_team',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Sanctum::actingAs($viewer);

        $response = $this->getJson('/api/v1/employees/onboarding-status')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee.id', $incompleteEmployee->id)
            ->assertJsonPath('data.0.summary.total_count', 2)
            ->assertJsonPath('data.0.summary.closed_count', 1)
            ->assertJsonPath('data.0.summary.incomplete_count', 1)
            ->assertJsonPath('data.0.summary.progress_percentage', 50);

        $this->assertSame('Department', $response->json('data.0.employee.department'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $viewer->id,
            'event_type' => 'employee.onboarding_status.viewed',
        ]);
    }

    public function test_onboarding_task_endpoints_enforce_validation_and_tenant_scope(): void
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

        $otherCompany = Company::factory()->create(['status' => 'active']);
        [$otherDepartment, $otherDesignation, $otherLocation, $otherCostCenter] = $this->createOrganizationContext($otherCompany->id, 'ONB');
        $otherEmployee = Employee::factory()->create([
            'company_id' => $otherCompany->id,
            'department_id' => $otherDepartment->id,
            'designation_id' => $otherDesignation->id,
            'location_id' => $otherLocation->id,
            'cost_center_id' => $otherCostCenter->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/employees/'.$employee->id.'/onboarding-tasks', [
            'category' => 'hr',
            'assignee_type' => 'employee',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->getJson('/api/v1/employees/'.$otherEmployee->id.'/onboarding-tasks')
            ->assertStatus(404);
    }

    public function test_employee_audit_history_endpoint_returns_lifecycle_audit_entries(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$departmentA, $designationA, $locationA, $costCenterA] = $this->createOrganizationContext($company->id, 'EA');
        [$departmentB, $designationB, $locationB, $costCenterB] = $this->createOrganizationContext($company->id, 'EB');

        Sanctum::actingAs($hrAdmin);

        $employeeId = $this->postJson('/api/v1/employees', [
            'first_name' => 'Sana',
            'last_name' => 'Mehta',
            'email' => 'sana.mehta@phoenixhrms.test',
            'date_of_joining' => '2026-06-01',
            'employment_type' => 'full_time',
            'department_id' => $departmentA->id,
            'designation_id' => $designationA->id,
            'location_id' => $locationA->id,
            'cost_center_id' => $costCenterA->id,
        ])->assertCreated()->json('data.id');

        $this->patchJson('/api/v1/employees/'.$employeeId, [
            'phone' => '9888800011',
        ])->assertOk();

        $this->postJson('/api/v1/employees/'.$employeeId.'/transfer', [
            'effective_date' => '2026-07-01',
            'department_id' => $departmentB->id,
            'location_id' => $locationB->id,
            'cost_center_id' => $costCenterB->id,
        ])->assertOk();

        $this->getJson('/api/v1/employees/'.$employeeId.'/audit-history')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 3)
            ->assertJsonPath('data.items.0.event_type', 'employee.record.transferred')
            ->assertJsonPath('data.items.0.metadata.after.department_id', $departmentB->id)
            ->assertJsonPath('data.items.1.event_type', 'employee.record.updated')
            ->assertJsonPath('data.items.1.metadata.after.phone', '9888800011')
            ->assertJsonPath('data.items.2.event_type', 'employee.record.created');
    }

    public function test_organization_audit_history_endpoint_returns_filtered_structure_changes(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        Sanctum::actingAs($tenantAdmin);

        $departmentId = $this->postJson('/api/v1/organization/departments', [
            'code' => 'OPS',
            'name' => 'Operations',
        ])->assertCreated()->json('data.id');

        $this->patchJson('/api/v1/organization/departments/'.$departmentId, [
            'code' => 'OPS',
            'name' => 'Operations Excellence',
            'status' => 'active',
        ])->assertOk();

        $this->getJson('/api/v1/organization/audit-history?entity_type=department&entity_id='.$departmentId)
            ->assertOk()
            ->assertJsonPath('data.meta.total', 2)
            ->assertJsonPath('data.items.0.event_type', 'organization.department.updated')
            ->assertJsonPath('data.items.0.metadata.before.name', 'Operations')
            ->assertJsonPath('data.items.0.metadata.after.name', 'Operations Excellence')
            ->assertJsonPath('data.items.1.event_type', 'organization.department.created');
    }

    public function test_bulk_import_validation_reports_success_and_failure_counts_without_creating_employees(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id, 'BI');

        Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP30001',
            'email' => 'existing.employee@phoenixhrms.test',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'cost_center_id' => $costCenter->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $response = $this->postJson('/api/v1/employees/bulk-import/validate', [
            'rows' => [
                [
                    'first_name' => 'Isha',
                    'last_name' => 'Bose',
                    'email' => 'isha.bose@phoenixhrms.test',
                    'date_of_joining' => '2026-06-01',
                    'employment_type' => 'full_time',
                    'department_id' => $department->id,
                    'designation_id' => $designation->id,
                    'location_id' => $location->id,
                    'cost_center_id' => $costCenter->id,
                ],
                [
                    'first_name' => 'Duplicate',
                    'last_name' => 'Email',
                    'email' => 'existing.employee@phoenixhrms.test',
                    'date_of_joining' => '2026-06-01',
                    'employment_type' => 'full_time',
                    'department_id' => $department->id,
                    'designation_id' => $designation->id,
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('data.processed', 2)
            ->assertJsonPath('data.success_count', 1)
            ->assertJsonPath('data.failed_count', 1)
            ->assertJsonPath('data.rows.0.status', 'valid')
            ->assertJsonPath('data.rows.1.status', 'invalid');

        $this->assertArrayHasKey('email', $response->json('data.rows.1.errors'));

        $this->assertDatabaseMissing('employees', [
            'email' => 'isha.bose@phoenixhrms.test',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.bulk_import.validated',
        ]);
    }

    public function test_bulk_import_validation_enforces_manual_code_policy_and_payload_uniqueness(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $designation] = $this->createOrganizationContext($company->id, 'BM');

        TenantSetting::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'key' => 'employee.code_policy',
            'value' => [
                'mode' => 'manual',
                'prefix' => 'MAN',
                'number_padding' => 4,
            ],
        ]);

        Sanctum::actingAs($hrAdmin);

        $response = $this->postJson('/api/v1/employees/bulk-import/validate', [
            'rows' => [
                [
                    'first_name' => 'NoCode',
                    'last_name' => 'User',
                    'email' => 'nocode.user@phoenixhrms.test',
                    'date_of_joining' => '2026-06-01',
                    'employment_type' => 'full_time',
                    'department_id' => $department->id,
                    'designation_id' => $designation->id,
                ],
                [
                    'employee_code' => 'MAN1001',
                    'first_name' => 'Aarav',
                    'last_name' => 'Singh',
                    'email' => 'duplicate.batch@phoenixhrms.test',
                    'date_of_joining' => '2026-06-01',
                    'employment_type' => 'full_time',
                    'department_id' => $department->id,
                    'designation_id' => $designation->id,
                ],
                [
                    'employee_code' => 'MAN1001',
                    'first_name' => 'Mira',
                    'last_name' => 'Singh',
                    'email' => 'duplicate.batch@phoenixhrms.test',
                    'date_of_joining' => '2026-06-01',
                    'employment_type' => 'full_time',
                    'department_id' => $department->id,
                    'designation_id' => $designation->id,
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('data.processed', 3)
            ->assertJsonPath('data.success_count', 0)
            ->assertJsonPath('data.failed_count', 3);

        $this->assertArrayHasKey('employee_code', $response->json('data.rows.0.errors'));
        $this->assertArrayHasKey('employee_code', $response->json('data.rows.1.errors'));
        $this->assertArrayHasKey('email', $response->json('data.rows.1.errors'));
        $this->assertArrayHasKey('employee_code', $response->json('data.rows.2.errors'));
        $this->assertArrayHasKey('email', $response->json('data.rows.2.errors'));
    }

    public function test_bulk_import_validation_supports_csv_uploads(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $designation, $location, $costCenter] = $this->createOrganizationContext($company->id, 'BC');

        Sanctum::actingAs($hrAdmin);

        $csv = <<<CSV
first_name,last_name,email,date_of_joining,employment_type,department_id,designation_id,location_id,cost_center_id
Sara,Thomas,sara.thomas@phoenixhrms.test,2026-06-01,full_time,{$department->id},{$designation->id},{$location->id},{$costCenter->id}
Broken,Row,,2026-06-01,full_time,{$department->id},{$designation->id},,
CSV;

        $response = $this->post('/api/v1/employees/bulk-import/validate', [
            'file' => UploadedFile::fake()->createWithContent('employees.csv', $csv),
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('data.processed', 2)
            ->assertJsonPath('data.success_count', 1)
            ->assertJsonPath('data.failed_count', 1)
            ->assertJsonPath('data.rows.0.row_number', 2)
            ->assertJsonPath('data.rows.1.row_number', 3)
            ->assertJsonPath('data.rows.1.status', 'invalid');

        $this->assertArrayHasKey('email', $response->json('data.rows.1.errors'));
    }

    /**
     * @return array{Department, Designation, Location, CostCenter}
     */
    private function createOrganizationContext(int $companyId, string $suffix = ''): array
    {
        $label = $suffix === '' ? '' : '-'.$suffix;

        $department = Department::factory()->create([
            'company_id' => $companyId,
            'code' => 'DEP'.$suffix.'01',
            'name' => 'Department'.$label,
        ]);

        $designation = Designation::factory()->create([
            'company_id' => $companyId,
            'code' => 'DSG'.$suffix.'01',
            'name' => 'Designation'.$label,
        ]);

        $location = Location::factory()->create([
            'company_id' => $companyId,
            'code' => 'LOC'.$suffix.'01',
            'name' => 'Location'.$label,
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
        ]);

        $costCenter = CostCenter::factory()->create([
            'company_id' => $companyId,
            'code' => 'CC'.$suffix.'01',
            'name' => 'Cost Center'.$label,
        ]);

        return [$department, $designation, $location, $costCenter];
    }
}
