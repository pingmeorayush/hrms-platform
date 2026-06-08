<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Location;
use App\Models\PayrollRun;
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

class PayrollRunCalculationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_payroll_run_can_be_calculated_approved_locked_reopened_and_recalculated_repeatably(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ]);

        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'date_of_joining' => '2024-01-01',
            'employment_status' => 'active',
        ]);

        $salaryStructure = $this->createSalaryStructure($company->id);

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'attendance_date' => '2026-06-03',
            'check_in_at' => Carbon::parse('2026-06-03 09:00:00', 'Asia/Kolkata'),
            'check_in_channel' => 'web',
            'check_out_at' => Carbon::parse('2026-06-03 18:00:00', 'Asia/Kolkata'),
            'check_out_channel' => 'web',
            'worked_minutes' => 480,
            'primary_status' => 'present',
            'late_minutes' => 0,
            'overtime_minutes' => 60,
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

        $runId = $this->preparePayrollRun($tenantAdmin->company_id);

        $calculateResponse = $this->postJson('/api/v1/payroll/runs/'.$runId.'/calculate')
            ->assertOk()
            ->assertJsonPath('data.status', 'calculated')
            ->assertJsonPath('data.calculation_summary.employee_count', 1)
            ->assertJsonPath('data.calculation_summary.error_count', 0);

        $netSalary = $calculateResponse->json('data.items.0.net_salary');
        $grossSalary = $calculateResponse->json('data.items.0.gross_salary');

        $this->postJson('/api/v1/payroll/runs/'.$runId.'/calculate')
            ->assertOk()
            ->assertJsonPath('data.items.0.net_salary', $netSalary)
            ->assertJsonPath('data.items.0.gross_salary', $grossSalary);

        $this->postJson('/api/v1/payroll/runs/'.$runId.'/approve', [
            'comment' => 'Ready for payroll lock.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->postJson('/api/v1/payroll/runs/'.$runId.'/lock')
            ->assertOk()
            ->assertJsonPath('data.status', 'locked');

        $this->postJson('/api/v1/payroll/runs/'.$runId.'/reopen', [
            'reason' => 'Bonus correction required.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'ready')
            ->assertJsonCount(0, 'data.items');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'payroll.run.locked',
            'entity_id' => (string) $runId,
        ]);
    }

    public function test_period_can_only_be_closed_after_run_is_locked_and_reopen_requires_permission(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ]);

        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        Sanctum::actingAs($tenantAdmin);

        $runId = $this->preparePayrollRun($company->id);
        $run = PayrollRun::query()->findOrFail($runId);
        $periodId = $run->payroll_period_id;

        $this->postJson('/api/v1/payroll/periods/'.$periodId.'/close')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->postJson('/api/v1/payroll/runs/'.$runId.'/calculate')->assertOk();
        $this->postJson('/api/v1/payroll/runs/'.$runId.'/approve')->assertOk();
        $this->postJson('/api/v1/payroll/runs/'.$runId.'/lock')->assertOk();

        $this->postJson('/api/v1/payroll/periods/'.$periodId.'/close')
            ->assertOk()
            ->assertJsonPath('data.status', 'closed');

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/payroll/runs/'.$runId.'/reopen', [
            'reason' => 'Need to edit locked payroll.',
        ])->assertForbidden();
    }

    private function preparePayrollRun(int $companyId): int
    {
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

        return $this->postJson('/api/v1/payroll/periods/'.$periodId.'/prepare')
            ->assertOk()
            ->json('data.run.id');
    }

    private function createOrganizationContext(int $companyId): array
    {
        $department = Department::factory()->create([
            'company_id' => $companyId,
            'code' => 'PAY-CALC',
            'name' => 'Payroll Calculation',
        ]);

        $designation = Designation::factory()->create([
            'company_id' => $companyId,
            'code' => 'PAY-ENG',
            'name' => 'Payroll Engineer',
        ]);

        $location = Location::factory()->create([
            'company_id' => $companyId,
            'code' => 'PAY-HQ',
            'name' => 'Payroll HQ',
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
            'code' => 'RUN-BASIC',
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
            'code' => 'RUN-PF',
            'name' => 'Provident Fund',
            'category' => 'deduction',
            'calculation_type' => 'expression',
            'expression_formula' => 'MIN(RUN-BASIC * 0.12, 1800)',
            'is_taxable' => false,
            'is_proratable' => true,
            'display_order' => 2,
            'status' => 'active',
        ]);

        $structure = SalaryStructure::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'previous_version_id' => null,
            'code' => 'RUN-G5',
            'name' => 'Run Grade 5',
            'currency' => 'INR',
            'country_code' => 'IN',
            'pay_frequency' => 'monthly',
            'grade' => 'G5',
            'band' => 'B2',
            'level' => 'L1',
            'annual_ctc_amount' => 1800000,
            'basic_salary_amount' => 600000,
            'gross_salary_amount' => 150000,
            'net_salary_amount' => 118000,
            'effective_from' => '2026-06-01',
            'revision_date' => '2026-06-01',
            'version' => 1,
            'status' => 'active',
            'notes' => 'Payroll run calculation baseline',
        ]);

        SalaryStructureComponent::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'salary_structure_id' => $structure->id,
            'salary_component_id' => $basic->id,
            'display_order' => 1,
            'configured_amount' => 50000,
            'configured_basis_component_codes' => [],
        ]);

        SalaryStructureComponent::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'salary_structure_id' => $structure->id,
            'salary_component_id' => $pf->id,
            'display_order' => 2,
            'configured_expression_formula' => 'MIN(RUN-BASIC * 0.12, 1800)',
            'configured_basis_component_codes' => [],
        ]);

        return $structure;
    }
}
