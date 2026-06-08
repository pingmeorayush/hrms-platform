<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
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

class PayrollInputApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_prepared_run_aggregates_attendance_leave_and_manual_adjustments_into_run_inputs(): void
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

        Sanctum::actingAs($tenantAdmin);

        $this->postJson('/api/v1/payroll/compensations', [
            'employee_id' => $employee->id,
            'salary_structure_id' => $salaryStructure->id,
            'revision_reason' => 'initial_assignment',
            'effective_from' => '2026-06-01',
            'revision_date' => '2026-06-01',
        ])->assertCreated();

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'attendance_date' => '2026-06-03',
            'check_in_at' => Carbon::parse('2026-06-03 09:00:00', 'Asia/Kolkata'),
            'check_in_channel' => 'web',
            'check_out_at' => Carbon::parse('2026-06-03 18:30:00', 'Asia/Kolkata'),
            'check_out_channel' => 'web',
            'worked_minutes' => 510,
            'primary_status' => 'present',
            'late_minutes' => 5,
            'overtime_minutes' => 30,
            'scheduled_work_minutes' => 480,
            'break_duration_minutes' => 0,
            'is_late' => true,
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

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'attendance_date' => '2026-06-04',
            'check_in_at' => Carbon::parse('2026-06-04 09:15:00', 'Asia/Kolkata'),
            'check_in_channel' => 'web',
            'check_out_at' => Carbon::parse('2026-06-04 13:15:00', 'Asia/Kolkata'),
            'check_out_channel' => 'web',
            'worked_minutes' => 240,
            'primary_status' => 'half_day',
            'late_minutes' => 15,
            'overtime_minutes' => 0,
            'scheduled_work_minutes' => 480,
            'break_duration_minutes' => 0,
            'is_late' => true,
            'is_half_day' => true,
            'is_weekend' => false,
            'is_holiday' => false,
            'is_early_departure' => true,
            'early_departure_minutes' => 240,
            'calculated_at' => now(),
            'calculation_metadata' => [],
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        $leaveType = LeaveType::query()->create([
            'company_id' => $company->id,
            'code' => 'EL',
            'name' => 'Earned Leave',
            'category' => 'earned',
            'description' => 'Paid leave for payroll inputs',
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#0F766E',
            'status' => 'active',
        ]);

        $leavePolicy = LeavePolicy::query()->create([
            'company_id' => $company->id,
            'leave_type_id' => $leaveType->id,
            'version' => 1,
            'scope_key' => 'payroll-input-policy',
            'annual_allowance_days' => 18,
            'opening_balance_days' => 0,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 8,
            'encashment_limit_days' => 0,
            'max_consecutive_days' => 10,
            'min_notice_days' => 1,
            'requires_documentation_after_days' => 3,
            'eligibility_rule' => [],
            'status' => 'active',
        ]);

        LeaveRequest::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'leave_policy_id' => $leavePolicy->id,
            'policy_version' => 1,
            'workflow_instance_id' => null,
            'requested_by_user_id' => $tenantAdmin->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-11',
            'total_days' => 2,
            'status' => 'approved',
            'reason' => 'Approved leave',
            'approver_comment' => 'Approved',
            'is_auto_approved' => false,
            'attendance_sync_status' => 'synced',
            'attendance_synced_at' => now(),
            'approved_at' => now(),
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

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

        $runId = $this->postJson('/api/v1/payroll/periods/'.$periodId.'/prepare')
            ->assertOk()
            ->assertJsonPath('data.run.status', 'ready')
            ->assertJsonPath('data.run.input_summary.employee_count', 1)
            ->assertJsonPath('data.run.input_summary.total_lop_days', 0.5)
            ->assertJsonPath('data.run.input_summary.total_paid_leave_days', 2)
            ->json('data.run.id');

        $this->postJson('/api/v1/payroll/runs/'.$runId.'/adjustments', [
            'employee_id' => $employee->id,
            'adjustment_code' => 'BONUS_JUN',
            'name' => 'Project Bonus',
            'category' => 'bonus',
            'amount' => 15000,
            'effective_date' => '2026-06-20',
            'notes' => 'Quarter close adjustment.',
        ])->assertCreated()
            ->assertJsonPath('data.category', 'bonus')
            ->assertJsonPath('data.amount', '15000.00');

        $inputsResponse = $this->getJson('/api/v1/payroll/runs/'.$runId.'/inputs')
            ->assertOk()
            ->assertJsonPath('data.run.input_summary.employee_count', 1)
            ->assertJsonPath('data.run.input_summary.input_count', 7)
            ->assertJsonPath('data.run.input_summary.manual_adjustment_count', 1)
            ->assertJsonPath('data.run.input_summary.total_overtime_minutes', 30)
            ->assertJsonPath('data.run.input_summary.total_manual_adjustment_amount', 15000);

        $inputCodes = collect($inputsResponse->json('data.items'))->pluck('input_code')->all();

        $this->assertContains('attendance_worked_minutes', $inputCodes);
        $this->assertContains('manual_adjustment_BONUS_JUN', $inputCodes);

        $adjustmentId = $this->getJson('/api/v1/payroll/runs/'.$runId.'/adjustments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->json('data.0.id');

        $this->patchJson('/api/v1/payroll/runs/'.$runId.'/adjustments/'.$adjustmentId, [
            'employee_id' => $employee->id,
            'adjustment_code' => 'BONUS_JUN',
            'name' => 'Project Bonus',
            'category' => 'bonus',
            'amount' => 15000,
            'effective_date' => '2026-06-20',
            'status' => 'cancelled',
            'notes' => 'Reversed before calculation.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->getJson('/api/v1/payroll/runs/'.$runId.'/inputs')
            ->assertOk()
            ->assertJsonPath('data.run.input_summary.manual_adjustment_count', 0)
            ->assertJsonPath('data.run.input_summary.total_manual_adjustment_amount', 0)
            ->assertJsonMissing(['input_code' => 'manual_adjustment_BONUS_JUN']);
    }

    public function test_payroll_input_and_adjustment_endpoints_are_tenant_scoped_and_permission_protected(): void
    {
        $company = Company::factory()->create(['status' => 'active', 'currency' => 'INR', 'timezone' => 'Asia/Kolkata']);
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $otherCompany = Company::factory()->create(['status' => 'active', 'currency' => 'INR', 'timezone' => 'Asia/Kolkata']);
        $otherAdmin = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherAdmin->assignRole('tenant.admin');

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

        $runId = $this->postJson('/api/v1/payroll/periods/'.$periodId.'/prepare')
            ->assertOk()
            ->json('data.run.id');

        Sanctum::actingAs($otherAdmin);
        $otherEmployee = Employee::factory()->create(['company_id' => $otherCompany->id]);

        $this->getJson('/api/v1/payroll/runs/'.$runId.'/inputs')->assertNotFound();
        $this->postJson('/api/v1/payroll/runs/'.$runId.'/adjustments', [
            'employee_id' => $otherEmployee->id,
            'adjustment_code' => 'DENIED',
            'name' => 'Denied',
            'category' => 'custom',
            'amount' => 1,
            'effective_date' => '2026-06-01',
        ])->assertNotFound();

        $viewer = User::factory()->create(['company_id' => $company->id]);
        $viewer->givePermissionTo('payroll.view');

        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/payroll/runs/'.$runId.'/inputs')->assertOk();
        $this->postJson('/api/v1/payroll/runs/'.$runId.'/adjustments', [
            'employee_id' => $employee->id,
            'adjustment_code' => 'DENIED',
            'name' => 'Denied',
            'category' => 'custom',
            'amount' => 1,
            'effective_date' => '2026-06-01',
        ])->assertForbidden();
    }

    private function createOrganizationContext(int $companyId): array
    {
        $department = Department::factory()->create([
            'company_id' => $companyId,
            'code' => 'PAY-OPS',
            'name' => 'Payroll Operations',
        ]);

        $designation = Designation::factory()->create([
            'company_id' => $companyId,
            'code' => 'PAY-ANL',
            'name' => 'Payroll Analyst',
        ]);

        $location = Location::factory()->create([
            'company_id' => $companyId,
            'code' => 'PAY-BLR',
            'name' => 'Payroll Campus',
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
            'code' => 'PAYROLL-BASIC',
            'name' => 'Basic Salary',
            'category' => 'earning',
            'calculation_type' => 'fixed',
            'flat_amount' => 50000,
            'is_taxable' => true,
            'is_proratable' => true,
            'display_order' => 1,
            'status' => 'active',
        ]);

        $structure = SalaryStructure::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'previous_version_id' => null,
            'code' => 'PAYROLL-G5',
            'name' => 'Payroll Grade 5',
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
            'notes' => 'Payroll input baseline',
        ]);

        SalaryStructureComponent::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'salary_structure_id' => $structure->id,
            'salary_component_id' => $basic->id,
            'display_order' => 1,
            'configured_amount' => 50000,
            'configured_basis_component_codes' => [],
        ]);

        return $structure;
    }
}
