<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Models\SalaryStructureComponent;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmployeeCompensationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_hr_admin_can_assign_employee_compensation_and_preserve_revision_history(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ]);

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'date_of_joining' => '2024-01-15',
            'employment_status' => 'active',
        ]);

        $salaryStructure = $this->createSalaryStructure($company->id, [
            'code' => 'ENG-G6',
            'version' => 1,
            'name' => 'Engineering Grade 6',
            'annual_ctc_amount' => 1800000,
            'basic_salary_amount' => 600000,
            'gross_salary_amount' => 150000,
            'net_salary_amount' => 118000,
        ]);

        Sanctum::actingAs($hrAdmin);

        $initialId = $this->postJson('/api/v1/payroll/compensations', [
            'employee_id' => $employee->id,
            'salary_structure_id' => $salaryStructure->id,
            'revision_reason' => 'initial_assignment',
            'effective_from' => '2026-07-01',
            'revision_date' => '2026-07-01',
            'notes' => 'Initial payroll-ready compensation assignment.',
        ])->assertCreated()
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('data.salary_structure.code', 'ENG-G6')
            ->assertJsonPath('data.annual_ctc_amount', '1800000.00')
            ->assertJsonPath('data.component_snapshot.0.code', 'ENG-G6-BASIC')
            ->json('data.id');

        $revisedStructure = $this->createSalaryStructure($company->id, [
            'code' => 'ENG-G6',
            'version' => 2,
            'name' => 'Engineering Grade 6 Revised',
            'annual_ctc_amount' => 1980000,
            'basic_salary_amount' => 660000,
            'gross_salary_amount' => 165000,
            'net_salary_amount' => 129000,
            'previous_version_id' => $salaryStructure->id,
        ]);

        $revisionId = $this->postJson('/api/v1/payroll/compensations', [
            'employee_id' => $employee->id,
            'salary_structure_id' => $revisedStructure->id,
            'revision_reason' => 'annual_revision',
            'effective_from' => '2026-10-01',
            'revision_date' => '2026-10-01',
            'notes' => 'Annual cycle revision.',
        ])->assertCreated()
            ->assertJsonPath('data.previous_revision_id', $initialId)
            ->assertJsonPath('data.salary_structure.version', 2)
            ->assertJsonPath('data.annual_ctc_amount', '1980000.00')
            ->json('data.id');

        $this->getJson('/api/v1/payroll/compensations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $revisionId);

        $this->getJson('/api/v1/payroll/compensations?current_only=0&employee_id='.$employee->id)
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/v1/payroll/compensations/'.$employee->id)
            ->assertOk()
            ->assertJsonPath('data.employee.id', $employee->id)
            ->assertJsonPath('data.current_assignment.id', $revisionId)
            ->assertJsonPath('data.history.1.id', $initialId);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.compensation.assigned',
            'entity_id' => (string) $initialId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'employee.compensation.revised',
            'entity_id' => (string) $revisionId,
        ]);
    }

    public function test_compensation_assignment_is_tenant_scoped_permission_protected_and_validated(): void
    {
        $company = Company::factory()->create(['status' => 'active', 'currency' => 'INR']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $otherCompany = Company::factory()->create(['status' => 'active', 'currency' => 'INR']);
        $otherAdmin = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherAdmin->assignRole('hr.admin');

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'date_of_joining' => '2025-01-01',
        ]);

        $salaryStructure = $this->createSalaryStructure($company->id, ['code' => 'OPS-G4']);
        $otherStructure = $this->createSalaryStructure($otherCompany->id, ['code' => 'OTH-G4']);

        Sanctum::actingAs($otherAdmin);

        $otherEmployee = Employee::factory()->create(['company_id' => $otherCompany->id]);

        $otherAssignmentId = $this->postJson('/api/v1/payroll/compensations', [
            'employee_id' => $otherEmployee->id,
            'salary_structure_id' => $otherStructure->id,
            'revision_reason' => 'initial_assignment',
            'effective_from' => '2026-07-01',
            'revision_date' => '2026-07-01',
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($hrAdmin);

        $this->getJson('/api/v1/payroll/compensations')
            ->assertOk()
            ->assertJsonMissing(['id' => $otherAssignmentId]);

        $this->getJson('/api/v1/payroll/compensations/'.$otherEmployee->id)
            ->assertNotFound();

        $this->postJson('/api/v1/payroll/compensations', [
            'employee_id' => $employee->id,
            'salary_structure_id' => $otherStructure->id,
            'revision_reason' => 'initial_assignment',
            'effective_from' => '2026-07-01',
            'revision_date' => '2026-07-01',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['salary_structure_id']);

        $this->postJson('/api/v1/payroll/compensations', [
            'employee_id' => $employee->id,
            'salary_structure_id' => $salaryStructure->id,
            'revision_reason' => 'initial_assignment',
            'effective_from' => '2024-12-01',
            'revision_date' => '2024-12-01',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['effective_from']);

        $terminatedEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employment_status' => 'terminated',
            'terminated_at' => now(),
        ]);

        $this->postJson('/api/v1/payroll/compensations', [
            'employee_id' => $terminatedEmployee->id,
            'salary_structure_id' => $salaryStructure->id,
            'revision_reason' => 'manual_change',
            'effective_from' => '2026-07-01',
            'revision_date' => '2026-07-01',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['employee_id']);

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/payroll/compensations')->assertForbidden();

        $this->postJson('/api/v1/payroll/compensations', [
            'employee_id' => $employee->id,
            'salary_structure_id' => $salaryStructure->id,
            'revision_reason' => 'manual_change',
            'effective_from' => '2026-07-01',
            'revision_date' => '2026-07-01',
        ])->assertForbidden();
    }

    public function test_compensation_assignments_make_payroll_prerequisites_ready_for_active_employees(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ]);

        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'date_of_joining' => '2024-01-01',
            'employment_status' => 'active',
        ]);

        $salaryStructure = $this->createSalaryStructure($company->id, ['code' => 'PAY-G5']);

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'shift_id' => null,
            'shift_roster_id' => null,
            'attendance_date' => '2026-06-05',
            'check_in_at' => Carbon::parse('2026-06-05 09:00:00', 'Asia/Kolkata'),
            'check_in_channel' => 'web',
            'check_in_ip_address' => null,
            'check_in_user_agent' => null,
            'check_in_metadata' => null,
            'check_out_at' => Carbon::parse('2026-06-05 18:00:00', 'Asia/Kolkata'),
            'check_out_channel' => 'web',
            'check_out_ip_address' => null,
            'check_out_user_agent' => null,
            'check_out_metadata' => null,
            'worked_minutes' => 480,
            'primary_status' => 'present',
            'scheduled_start_at' => null,
            'scheduled_end_at' => null,
            'scheduled_work_minutes' => 480,
            'break_duration_minutes' => 0,
            'is_late' => false,
            'late_minutes' => 0,
            'is_half_day' => false,
            'overtime_minutes' => 0,
            'is_weekend' => false,
            'is_holiday' => false,
            'holiday_name' => null,
            'is_early_departure' => false,
            'early_departure_minutes' => 0,
            'calculated_at' => now(),
            'calculation_metadata' => [],
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        Sanctum::actingAs($tenantAdmin);

        $this->postJson('/api/v1/payroll/compensations', [
            'employee_id' => $employee->id,
            'salary_structure_id' => $salaryStructure->id,
            'revision_reason' => 'initial_assignment',
            'effective_from' => '2026-06-01',
            'revision_date' => '2026-06-01',
        ])->assertCreated();

        $calendarId = $this->postJson('/api/v1/payroll/calendars', [
            'name' => 'Main Monthly Payroll',
            'frequency' => 'monthly',
            'timezone' => 'Asia/Kolkata',
            'payroll_day' => 30,
            'is_default' => true,
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        $periodId = $this->postJson('/api/v1/payroll/periods', [
            'payroll_calendar_id' => $calendarId,
            'name' => 'June 2026 Payroll',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'payroll_date' => '2026-06-30',
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/payroll/periods/'.$periodId.'/open')->assertOk();

        $this->postJson('/api/v1/payroll/periods/'.$periodId.'/prepare')
            ->assertOk()
            ->assertJsonPath('data.run.status', 'ready')
            ->assertJsonPath('data.prerequisites.summary.ready_for_calculation', true)
            ->assertJsonPath('data.prerequisites.checks.3.metrics.unassigned_active_employee_count', 0);
    }

    private function createSalaryStructure(int $companyId, array $overrides = []): SalaryStructure
    {
        $code = $overrides['code'] ?? 'ENG-G5';
        $version = $overrides['version'] ?? 1;
        $componentCodePrefix = $version > 1 ? $code.'-V'.$version : $code;

        $basic = SalaryComponent::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'code' => $componentCodePrefix.'-BASIC',
            'name' => 'Basic Salary',
            'category' => 'earning',
            'calculation_type' => 'fixed',
            'flat_amount' => 50000,
            'percentage_value' => null,
            'percentage_basis_component_codes' => [],
            'expression_formula' => null,
            'is_taxable' => true,
            'is_proratable' => true,
            'display_order' => 1,
            'status' => 'active',
            'created_by_user_id' => null,
            'updated_by_user_id' => null,
        ]);

        $allowance = SalaryComponent::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'code' => $componentCodePrefix.'-HRA',
            'name' => 'Allowance',
            'category' => 'earning',
            'calculation_type' => 'percentage',
            'flat_amount' => null,
            'percentage_value' => 40,
            'percentage_basis_component_codes' => [$basic->code],
            'expression_formula' => null,
            'is_taxable' => true,
            'is_proratable' => true,
            'display_order' => 2,
            'status' => 'active',
            'created_by_user_id' => null,
            'updated_by_user_id' => null,
        ]);

        $structure = SalaryStructure::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'previous_version_id' => $overrides['previous_version_id'] ?? null,
            'code' => $code,
            'name' => $overrides['name'] ?? 'Engineering Grade 5',
            'currency' => 'INR',
            'country_code' => 'IN',
            'pay_frequency' => 'monthly',
            'grade' => 'G5',
            'band' => 'B2',
            'level' => 'L1',
            'annual_ctc_amount' => $overrides['annual_ctc_amount'] ?? 1800000,
            'basic_salary_amount' => $overrides['basic_salary_amount'] ?? 600000,
            'gross_salary_amount' => $overrides['gross_salary_amount'] ?? 150000,
            'net_salary_amount' => $overrides['net_salary_amount'] ?? 118000,
            'effective_from' => '2026-06-01',
            'revision_date' => '2026-06-01',
            'version' => $version,
            'status' => 'active',
            'notes' => 'Payroll compensation baseline',
            'created_by_user_id' => null,
            'updated_by_user_id' => null,
        ]);

        SalaryStructureComponent::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'salary_structure_id' => $structure->id,
            'salary_component_id' => $basic->id,
            'display_order' => 1,
            'configured_amount' => 50000,
            'configured_percentage' => null,
            'configured_basis_component_codes' => [],
            'configured_expression_formula' => null,
        ]);

        SalaryStructureComponent::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'salary_structure_id' => $structure->id,
            'salary_component_id' => $allowance->id,
            'display_order' => 2,
            'configured_amount' => null,
            'configured_percentage' => 40,
            'configured_basis_component_codes' => [$basic->code],
            'configured_expression_formula' => null,
        ]);

        return $structure->load(['components.salaryComponent']);
    }
}
