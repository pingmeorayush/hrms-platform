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
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PayrollApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_tenant_admin_can_manage_payroll_calendars_periods_and_prerequisite_runs(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        Sanctum::actingAs($tenantAdmin);

        $calendarId = $this->postJson('/api/v1/payroll/calendars', [
            'name' => 'Main Monthly Payroll',
            'frequency' => 'monthly',
            'timezone' => 'Asia/Kolkata',
            'payroll_day' => 28,
            'is_default' => true,
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.frequency', 'monthly')
            ->assertJsonPath('data.is_default', true)
            ->json('data.id');

        $periodId = $this->postJson('/api/v1/payroll/periods', [
            'payroll_calendar_id' => $calendarId,
            'name' => 'June 2026 Payroll',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'payroll_date' => '2026-06-30',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->json('data.id');

        $this->postJson("/api/v1/payroll/periods/{$periodId}/open")
            ->assertOk()
            ->assertJsonPath('data.status', 'open');

        $prepareResponse = $this->postJson("/api/v1/payroll/periods/{$periodId}/prepare")
            ->assertOk()
            ->assertJsonPath('data.period.status', 'prepared')
            ->assertJsonPath('data.run.status', 'ready')
            ->assertJsonPath('data.prerequisites.summary.ready_for_calculation', true);

        $runId = $prepareResponse->json('data.run.id');

        $this->getJson("/api/v1/payroll/runs/{$runId}")
            ->assertOk()
            ->assertJsonPath('data.id', $runId)
            ->assertJsonPath('data.prerequisite_summary.ready_for_calculation', true);

        $this->getJson("/api/v1/payroll/periods/{$periodId}")
            ->assertOk()
            ->assertJsonPath('data.period.id', $periodId)
            ->assertJsonPath('data.prerequisites.summary.ready_for_calculation', true);

        $this->postJson("/api/v1/payroll/runs/{$runId}/calculate")
            ->assertOk()
            ->assertJsonPath('data.status', 'calculated');

        $this->postJson("/api/v1/payroll/runs/{$runId}/approve", [
            'comment' => 'Approved after payroll calculation review.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->postJson("/api/v1/payroll/runs/{$runId}/lock")
            ->assertOk()
            ->assertJsonPath('data.status', 'locked');

        $this->postJson("/api/v1/payroll/periods/{$periodId}/close")
            ->assertOk()
            ->assertJsonPath('data.status', 'closed');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'payroll.period.prepared',
            'entity_id' => (string) $periodId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'payroll.run.created',
            'entity_id' => (string) $runId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'payroll.run.locked',
            'entity_id' => (string) $runId,
        ]);
    }

    public function test_payroll_preparation_surfaces_blockers_and_blocks_overlapping_runs(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'PAY-OPS',
            'name' => 'Payroll Operations',
        ]);

        $designation = Designation::factory()->create([
            'company_id' => $company->id,
            'code' => 'PAY-ANL',
            'name' => 'Payroll Analyst',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'PAY-BLR',
            'name' => 'Payroll Campus',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Bengaluru',
            'country' => 'India',
        ]);

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'employee_code' => 'PAY1001',
            'first_name' => 'Priya',
            'middle_name' => null,
            'last_name' => 'Sharma',
            'email' => 'priya.sharma@example.test',
            'phone' => '+91-9999990001',
            'date_of_birth' => '1995-02-10',
            'gender' => 'female',
            'marital_status' => 'single',
            'date_of_joining' => '2024-01-01',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'manager_id' => null,
            'location_id' => $location->id,
            'cost_center_id' => null,
            'user_id' => null,
            'termination_reason' => null,
            'terminated_at' => null,
        ]);

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
            'check_out_at' => null,
            'check_out_channel' => null,
            'check_out_ip_address' => null,
            'check_out_user_agent' => null,
            'check_out_metadata' => null,
            'worked_minutes' => 0,
            'primary_status' => 'incomplete',
            'scheduled_start_at' => null,
            'scheduled_end_at' => null,
            'scheduled_work_minutes' => 0,
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

        $leaveType = LeaveType::query()->create([
            'company_id' => $company->id,
            'code' => 'EL',
            'name' => 'Earned Leave',
            'category' => 'earned',
            'description' => 'Payroll blocker coverage',
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
            'scope_key' => 'payroll-blocker-policy',
            'annual_allowance_days' => 18,
            'opening_balance_days' => 0,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 8,
            'encashment_limit_days' => 0,
            'max_consecutive_days' => 10,
            'min_notice_days' => 1,
            'requires_documentation_after_days' => 3,
            'applicable_department_id' => null,
            'applicable_location_id' => null,
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
            'end_date' => '2026-06-12',
            'total_days' => 3,
            'status' => 'pending',
            'reason' => 'Pending approval',
            'approver_comment' => null,
            'is_auto_approved' => false,
            'attendance_sync_status' => 'not_applicable',
            'attendance_synced_at' => null,
            'approved_at' => null,
            'rejected_at' => null,
            'cancelled_at' => null,
            'created_by_user_id' => $tenantAdmin->id,
            'updated_by_user_id' => $tenantAdmin->id,
        ]);

        Sanctum::actingAs($tenantAdmin);

        $calendarId = $this->postJson('/api/v1/payroll/calendars', [
            'name' => 'Monthly Payroll',
            'frequency' => 'monthly',
            'timezone' => 'Asia/Kolkata',
            'payroll_day' => 30,
            'is_default' => true,
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        $firstPeriodId = $this->postJson('/api/v1/payroll/periods', [
            'payroll_calendar_id' => $calendarId,
            'name' => 'June 2026 Payroll',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'payroll_date' => '2026-06-30',
        ])->assertCreated()->json('data.id');

        $this->postJson("/api/v1/payroll/periods/{$firstPeriodId}/open")->assertOk();

        $this->postJson("/api/v1/payroll/periods/{$firstPeriodId}/prepare")
            ->assertOk()
            ->assertJsonPath('data.period.status', 'prepared')
            ->assertJsonPath('data.run.status', 'blocked')
            ->assertJsonPath('data.prerequisites.summary.ready_for_calculation', false)
            ->assertJsonPath('data.prerequisites.summary.blocking_count', 3);

        $secondPeriodId = $this->postJson('/api/v1/payroll/periods', [
            'payroll_calendar_id' => $calendarId,
            'name' => 'Overlap Payroll',
            'start_date' => '2026-06-15',
            'end_date' => '2026-07-14',
            'payroll_date' => '2026-07-15',
        ])->assertCreated()->json('data.id');

        $this->postJson("/api/v1/payroll/periods/{$secondPeriodId}/open")->assertOk();

        $this->postJson("/api/v1/payroll/periods/{$secondPeriodId}/prepare")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    public function test_payroll_control_endpoints_are_tenant_scoped_and_permission_protected(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $otherCompany = Company::factory()->create(['status' => 'active']);
        $otherAdmin = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherAdmin->assignRole('tenant.admin');

        Sanctum::actingAs($otherAdmin);

        $otherCalendarId = $this->postJson('/api/v1/payroll/calendars', [
            'name' => 'Other Monthly Payroll',
            'frequency' => 'monthly',
            'timezone' => 'Asia/Kolkata',
            'payroll_day' => 25,
            'is_default' => true,
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($tenantAdmin);

        $this->getJson('/api/v1/payroll/calendars')
            ->assertOk()
            ->assertJsonMissing(['id' => $otherCalendarId]);

        $this->patchJson("/api/v1/payroll/calendars/{$otherCalendarId}", [
            'name' => 'Updated Other Payroll',
            'frequency' => 'monthly',
            'timezone' => 'Asia/Kolkata',
            'payroll_day' => 25,
            'is_default' => false,
            'status' => 'active',
        ])->assertNotFound();

        $employee = User::factory()->create(['company_id' => $company->id]);
        $employee->assignRole('employee');

        Sanctum::actingAs($employee);

        $this->postJson('/api/v1/payroll/calendars', [
            'name' => 'Employee Attempt',
            'frequency' => 'monthly',
            'timezone' => 'Asia/Kolkata',
            'payroll_day' => 30,
            'is_default' => false,
            'status' => 'active',
        ])->assertForbidden();
    }
}
