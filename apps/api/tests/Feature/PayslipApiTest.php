<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Location;
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

class PayslipApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_locked_payroll_run_can_generate_payslips_and_employee_can_access_only_their_own(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ]);

        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        $peerUser = User::factory()->create(['company_id' => $company->id]);
        $peerUser->assignRole('employee');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'date_of_joining' => '2024-01-01',
            'employment_status' => 'active',
        ]);

        $peerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $peerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'date_of_joining' => '2024-01-01',
            'employment_status' => 'active',
        ]);

        $salaryStructure = $this->createSalaryStructure($company->id);

        $this->createAttendanceRecord($company->id, $tenantAdmin->id, $employee->id, '2026-06-03', 540, 60);
        $this->createAttendanceRecord($company->id, $tenantAdmin->id, $peerEmployee->id, '2026-06-04', 510, 30);

        Sanctum::actingAs($tenantAdmin);

        $this->assignCompensation($employee, $salaryStructure->id);
        $this->assignCompensation($peerEmployee, $salaryStructure->id);

        $runId = $this->prepareLockedPayrollRun();

        $generateResponse = $this->postJson('/api/v1/payroll/runs/'.$runId.'/generate-payslips')
            ->assertOk()
            ->assertJsonPath('data.generated_count', 2);

        $employeePayslipId = collect($generateResponse->json('data.items'))
            ->firstWhere('employee_id', $employee->id)['id'];

        $peerPayslipId = collect($generateResponse->json('data.items'))
            ->firstWhere('employee_id', $peerEmployee->id)['id'];

        $this->getJson('/api/v1/payroll/payslips')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 2);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/payroll/payslips')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $employeePayslipId);

        $this->getJson('/api/v1/payroll/payslips/'.$employeePayslipId)
            ->assertOk()
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('data.net_salary', '48512.50');

        $this->get('/api/v1/payroll/payslips/'.$employeePayslipId.'/download')
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSeeText($employee->full_name, false)
            ->assertSee('Salary Slip', false)
            ->assertSee('Employee Details', false)
            ->assertSee('Payroll Summary', false)
            ->assertSee('Net Pay Released', false);

        $this->getJson('/api/v1/payroll/payslips/'.$peerPayslipId)
            ->assertNotFound();

        Sanctum::actingAs($peerUser);

        $this->getJson('/api/v1/payroll/payslips')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $peerPayslipId);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'payroll.payslip.generated',
            'entity_id' => (string) $runId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'payroll.payslip.downloaded',
            'entity_id' => (string) $employeePayslipId,
        ]);
    }

    public function test_payslip_generation_requires_locked_run_and_reopen_revokes_existing_payslips(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ]);

        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'date_of_joining' => '2024-01-01',
            'employment_status' => 'active',
        ]);

        $salaryStructure = $this->createSalaryStructure($company->id);
        $this->createAttendanceRecord($company->id, $tenantAdmin->id, $employee->id, '2026-06-03', 480, 0);

        Sanctum::actingAs($tenantAdmin);

        $this->assignCompensation($employee, $salaryStructure->id);

        $periodId = $this->createPayrollPeriod();

        $this->postJson('/api/v1/payroll/periods/'.$periodId.'/open')->assertOk();

        $runId = $this->postJson('/api/v1/payroll/periods/'.$periodId.'/prepare')
            ->assertOk()
            ->json('data.run.id');

        $this->postJson('/api/v1/payroll/runs/'.$runId.'/generate-payslips')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->postJson('/api/v1/payroll/runs/'.$runId.'/calculate')->assertOk();
        $this->postJson('/api/v1/payroll/runs/'.$runId.'/approve')->assertOk();
        $this->postJson('/api/v1/payroll/runs/'.$runId.'/lock')->assertOk();

        $payslipId = $this->postJson('/api/v1/payroll/runs/'.$runId.'/generate-payslips')
            ->assertOk()
            ->assertJsonPath('data.generated_count', 1)
            ->json('data.items.0.id');

        $this->assertDatabaseHas('payslips', [
            'id' => $payslipId,
            'payroll_run_id' => $runId,
        ]);

        $this->postJson('/api/v1/payroll/runs/'.$runId.'/reopen', [
            'reason' => 'Payroll input update required before release.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'ready');

        $this->assertDatabaseCount('payslips', 0);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/payroll/payslips')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 0);

        $this->getJson('/api/v1/payroll/payslips/'.$payslipId)
            ->assertNotFound();
    }

    private function assignCompensation(Employee $employee, int $salaryStructureId): void
    {
        $this->postJson('/api/v1/payroll/compensations', [
            'employee_id' => $employee->id,
            'salary_structure_id' => $salaryStructureId,
            'revision_reason' => 'initial_assignment',
            'effective_from' => '2026-06-01',
            'revision_date' => '2026-06-01',
        ])->assertCreated();
    }

    private function prepareLockedPayrollRun(): int
    {
        $periodId = $this->createPayrollPeriod();

        $this->postJson('/api/v1/payroll/periods/'.$periodId.'/open')->assertOk();

        $runId = $this->postJson('/api/v1/payroll/periods/'.$periodId.'/prepare')
            ->assertOk()
            ->json('data.run.id');

        $this->postJson('/api/v1/payroll/runs/'.$runId.'/calculate')->assertOk();
        $this->postJson('/api/v1/payroll/runs/'.$runId.'/approve')->assertOk();
        $this->postJson('/api/v1/payroll/runs/'.$runId.'/lock')->assertOk();

        return $runId;
    }

    private function createPayrollPeriod(): int
    {
        $calendarId = $this->postJson('/api/v1/payroll/calendars', [
            'name' => 'Main Monthly Payroll',
            'frequency' => 'monthly',
            'timezone' => 'Asia/Kolkata',
            'payroll_day' => 30,
            'is_default' => true,
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        return $this->postJson('/api/v1/payroll/periods', [
            'payroll_calendar_id' => $calendarId,
            'name' => 'June 2026 Payroll',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'payroll_date' => '2026-06-30',
        ])->assertCreated()->json('data.id');
    }

    private function createOrganizationContext(int $companyId): array
    {
        $department = Department::factory()->create([
            'company_id' => $companyId,
            'code' => 'PAY-SLIP',
            'name' => 'Payroll Delivery',
        ]);

        $designation = Designation::factory()->create([
            'company_id' => $companyId,
            'code' => 'PAY-SPEC',
            'name' => 'Payroll Specialist',
        ]);

        $location = Location::factory()->create([
            'company_id' => $companyId,
            'code' => 'PAY-CEN',
            'name' => 'Payroll Center',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Bengaluru',
            'country' => 'India',
        ]);

        return [$department, $designation, $location];
    }

    private function createSalaryStructure(int $companyId): SalaryStructure
    {
        $basic = SalaryComponent::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'code' => 'SLIP-BASIC',
            'name' => 'Basic Salary',
            'category' => 'earning',
            'calculation_type' => 'fixed',
            'flat_amount' => 50000,
            'is_taxable' => true,
            'is_proratable' => true,
            'display_order' => 1,
            'status' => 'active',
        ]);

        $pf = SalaryComponent::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'code' => 'SLIP-PF',
            'name' => 'Provident Fund',
            'category' => 'deduction',
            'calculation_type' => 'expression',
            'expression_formula' => 'MIN(SLIP-BASIC * 0.12, 1800)',
            'is_taxable' => false,
            'is_proratable' => true,
            'display_order' => 2,
            'status' => 'active',
        ]);

        $structure = SalaryStructure::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'previous_version_id' => null,
            'code' => 'SLIP-G5',
            'name' => 'Payslip Grade 5',
            'currency' => 'INR',
            'country_code' => 'IN',
            'pay_frequency' => 'monthly',
            'grade' => 'G5',
            'band' => 'B2',
            'level' => 'L1',
            'version' => 1,
            'annual_ctc_amount' => 600000,
            'basic_salary_amount' => 50000,
            'gross_salary_amount' => 50000,
            'net_salary_amount' => 48200,
            'effective_from' => '2026-06-01',
            'revision_date' => '2026-06-01',
            'status' => 'active',
            'notes' => 'Payslip baseline structure.',
            'created_by_user_id' => null,
            'updated_by_user_id' => null,
        ]);

        SalaryStructureComponent::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'salary_structure_id' => $structure->id,
            'salary_component_id' => $basic->id,
            'display_order' => 1,
            'resolved_formula_inputs' => [
                'calculation_type' => 'fixed',
                'flat_amount' => 50000,
            ],
            'resolved_amount' => 50000,
        ]);

        SalaryStructureComponent::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'salary_structure_id' => $structure->id,
            'salary_component_id' => $pf->id,
            'display_order' => 2,
            'resolved_formula_inputs' => [
                'calculation_type' => 'expression',
                'expression_formula' => 'MIN(SLIP-BASIC * 0.12, 1800)',
            ],
            'resolved_amount' => 1800,
        ]);

        $structure->refresh();

        return $structure;
    }

    private function createAttendanceRecord(
        int $companyId,
        int $actorUserId,
        int $employeeId,
        string $attendanceDate,
        int $workedMinutes,
        int $overtimeMinutes,
    ): void {
        AttendanceRecord::query()->create([
            'company_id' => $companyId,
            'employee_id' => $employeeId,
            'attendance_date' => $attendanceDate,
            'check_in_at' => Carbon::parse($attendanceDate.' 09:00:00', 'Asia/Kolkata'),
            'check_in_channel' => 'web',
            'check_out_at' => Carbon::parse($attendanceDate.' 18:00:00', 'Asia/Kolkata'),
            'check_out_channel' => 'web',
            'worked_minutes' => $workedMinutes,
            'primary_status' => 'present',
            'late_minutes' => 0,
            'overtime_minutes' => $overtimeMinutes,
            'scheduled_work_minutes' => 480,
            'break_duration_minutes' => 0,
            'is_late' => false,
            'is_half_day' => false,
            'is_weekend' => false,
            'is_holiday' => false,
            'is_early_departure' => false,
            'early_departure_minutes' => 0,
            'calculated_at' => now(),
            'calculation_metadata' => [],
            'created_by_user_id' => $actorUserId,
            'updated_by_user_id' => $actorUserId,
        ]);
    }
}
