<?php

namespace Tests\Feature;

use App\Models\AttendanceCorrection;
use App\Models\AttendancePolicy;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\HolidayCalendar;
use App\Models\Location;
use App\Models\NotificationRecord;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftRoster;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionRoleSeeder::class);
        $this->seed(NotificationTemplateSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_tenant_admin_can_manage_attendance_policy_and_holiday_configuration(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'ATT-LOC',
            'name' => 'Attendance HQ',
        ]);

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'ATT-DEP',
            'name' => 'Attendance Ops',
        ]);

        Sanctum::actingAs($tenantAdmin);

        $this->getJson('/api/v1/attendance/policy')
            ->assertOk()
            ->assertJsonPath('data.working_hours_minutes', 480)
            ->assertJsonPath('data.weekend_rule.non_working_days.0', 0)
            ->assertJsonPath('data.weekend_rule.non_working_days.1', 6);

        $this->patchJson('/api/v1/attendance/policy', [
            'name' => 'India Standard Attendance Policy',
            'working_hours_minutes' => 510,
            'grace_minutes' => 10,
            'late_after_minutes' => 15,
            'half_day_minutes' => 255,
            'overtime_eligible' => true,
            'overtime_after_minutes' => 510,
            'weekend_rule' => [
                'non_working_days' => [0],
            ],
            'work_from_home_allowed' => true,
            'enforce_geofence' => true,
            'allowed_radius_meters' => 150,
            'status' => 'active',
        ])->assertOk()
            ->assertJsonPath('data.name', 'India Standard Attendance Policy')
            ->assertJsonPath('data.working_hours_minutes', 510)
            ->assertJsonPath('data.enforce_geofence', true)
            ->assertJsonPath('data.allowed_radius_meters', 150);

        $calendarId = $this->postJson('/api/v1/attendance/holiday-calendars', [
            'code' => 'IND-2026',
            'name' => 'India 2026 Calendar',
            'description' => 'Primary attendance holiday calendar.',
            'is_default' => true,
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.code', 'IND-2026')
            ->assertJsonPath('data.is_default', true)
            ->json('data.id');

        $this->postJson('/api/v1/attendance/holiday-calendars', [
            'code' => 'ATT-BLR',
            'name' => 'Bangalore Ops Calendar',
            'description' => 'Scoped location calendar.',
            'location_id' => $location->id,
            'department_id' => $department->id,
            'is_default' => false,
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.location.id', $location->id)
            ->assertJsonPath('data.department.id', $department->id);

        $holidayId = $this->postJson("/api/v1/attendance/holiday-calendars/{$calendarId}/holidays", [
            'name' => 'Republic Day',
            'holiday_date' => '2026-01-26',
            'type' => 'national',
            'is_optional' => false,
            'description' => 'National holiday',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Republic Day')
            ->json('data.id');

        $this->patchJson("/api/v1/attendance/holiday-calendars/{$calendarId}/holidays/{$holidayId}", [
            'name' => 'Republic Day Observed',
            'holiday_date' => '2026-01-26',
            'type' => 'national',
            'is_optional' => false,
            'description' => 'Observed holiday name.',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Republic Day Observed');

        $calendarListing = $this->getJson('/api/v1/attendance/holiday-calendars')
            ->assertOk();

        $this->assertSame(
            ['Bangalore Ops Calendar', 'India 2026 Calendar'],
            collect($calendarListing->json('data'))->pluck('name')->sort()->values()->all(),
        );

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'attendance.policy.updated',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'attendance.holiday_calendar.created',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'attendance.holiday.updated',
            'entity_id' => (string) $holidayId,
        ]);
    }

    public function test_attendance_policy_validation_and_calendar_scope_rules_are_enforced(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'VAL-LOC',
            'name' => 'Validation Office',
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->patchJson('/api/v1/attendance/policy', [
            'name' => 'Broken Policy',
            'working_hours_minutes' => 480,
            'grace_minutes' => 15,
            'late_after_minutes' => 10,
            'half_day_minutes' => 490,
            'overtime_eligible' => true,
            'overtime_after_minutes' => 480,
            'weekend_rule' => [
                'non_working_days' => [0, 0],
            ],
            'work_from_home_allowed' => false,
            'enforce_geofence' => true,
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors([
                'late_after_minutes',
                'half_day_minutes',
                'weekend_rule.non_working_days.1',
                'allowed_radius_meters',
            ]);

        $this->postJson('/api/v1/attendance/holiday-calendars', [
            'code' => 'VAL-2026',
            'name' => 'Invalid Default Calendar',
            'location_id' => $location->id,
            'is_default' => true,
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['is_default']);
    }

    public function test_attendance_configuration_endpoints_are_tenant_scoped_and_manage_actions_require_permission(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $employee = User::factory()->create(['company_id' => $company->id]);
        $employee->assignRole('employee');

        $otherCompany = Company::factory()->create(['status' => 'active']);

        $otherCalendar = HolidayCalendar::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'code' => 'OTH-2026',
            'name' => 'Other Tenant Calendar',
            'description' => null,
            'location_id' => null,
            'department_id' => null,
            'is_default' => true,
            'status' => 'active',
        ]);

        Holiday::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'holiday_calendar_id' => $otherCalendar->id,
            'name' => 'Other Tenant Holiday',
            'holiday_date' => '2026-08-15',
            'type' => 'company',
            'is_optional' => false,
        ]);

        Sanctum::actingAs($employee);

        $this->patchJson('/api/v1/attendance/policy', [
            'name' => 'Employee Attempt',
            'working_hours_minutes' => 480,
            'grace_minutes' => 15,
            'late_after_minutes' => 15,
            'half_day_minutes' => 240,
            'overtime_eligible' => false,
            'weekend_rule' => [
                'non_working_days' => [0, 6],
            ],
            'work_from_home_allowed' => false,
            'enforce_geofence' => false,
            'status' => 'active',
        ])->assertStatus(403);

        Sanctum::actingAs($hrAdmin);

        $this->getJson('/api/v1/attendance/holiday-calendars')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->patchJson("/api/v1/attendance/holiday-calendars/{$otherCalendar->id}", [
            'code' => 'OTH-2026',
            'name' => 'Blocked Update',
            'description' => null,
            'is_default' => true,
            'status' => 'active',
        ])->assertStatus(404);

        $this->assertSame(0, AttendancePolicy::query()->count());
    }

    public function test_tenant_admin_can_manage_shifts_assignments_and_rosters(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $tenantAdmin = User::factory()->create(['company_id' => $company->id]);
        $tenantAdmin->assignRole('tenant.admin');

        [$department, $location] = $this->createAttendanceStructure($company->id);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);

        Sanctum::actingAs($tenantAdmin);

        $shiftId = $this->postJson('/api/v1/attendance/shifts', [
            'code' => 'SHIFT-GEN',
            'name' => 'General Shift',
            'description' => 'Standard day shift',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_duration_minutes' => 60,
            'grace_minutes' => 15,
            'working_hours_minutes' => 480,
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.code', 'SHIFT-GEN')
            ->assertJsonPath('data.is_overnight', false)
            ->json('data.id');

        $this->patchJson('/api/v1/attendance/shifts/'.$shiftId, [
            'code' => 'SHIFT-GEN',
            'name' => 'General Shift Overnight',
            'description' => 'Shift adjusted for overnight support',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'break_duration_minutes' => 45,
            'grace_minutes' => 10,
            'working_hours_minutes' => 450,
            'status' => 'active',
        ])->assertOk()
            ->assertJsonPath('data.name', 'General Shift Overnight')
            ->assertJsonPath('data.is_overnight', true);

        $employeeAssignmentId = $this->postJson('/api/v1/attendance/shift-assignments', [
            'shift_id' => $shiftId,
            'assignment_type' => 'employee',
            'employee_id' => $employee->id,
            'effective_from' => '2026-06-01',
            'effective_to' => null,
            'notes' => 'Permanent night shift',
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.assignment_type', 'employee')
            ->assertJsonPath('data.employee.id', $employee->id)
            ->json('data.id');

        $this->postJson('/api/v1/attendance/shift-assignments', [
            'shift_id' => $shiftId,
            'assignment_type' => 'location',
            'location_id' => $location->id,
            'effective_from' => '2026-06-01',
            'effective_to' => '2026-06-30',
            'notes' => 'Site-wide backup coverage',
            'status' => 'inactive',
        ])->assertCreated()
            ->assertJsonPath('data.assignment_type', 'location')
            ->assertJsonPath('data.location.id', $location->id);

        $rosterResponse = $this->postJson('/api/v1/attendance/rosters', [
            'entries' => [
                [
                    'employee_id' => $employee->id,
                    'shift_id' => $shiftId,
                    'work_date' => '2026-06-03',
                    'notes' => 'Week 1 roster',
                    'status' => 'scheduled',
                ],
                [
                    'employee_id' => $employee->id,
                    'shift_id' => $shiftId,
                    'work_date' => '2026-06-04',
                    'notes' => 'Week 1 roster',
                    'status' => 'scheduled',
                ],
            ],
        ])->assertCreated()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.employee.id', $employee->id);

        $rosterId = $rosterResponse->json('data.0.id');

        $this->patchJson('/api/v1/attendance/rosters/'.$rosterId, [
            'shift_id' => $shiftId,
            'work_date' => '2026-06-03',
            'notes' => 'Updated week 1 roster',
            'status' => 'cancelled',
        ])->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.notes', 'Updated week 1 roster');

        $this->getJson('/api/v1/attendance/shifts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $shiftId);

        $this->getJson('/api/v1/attendance/shift-assignments?assignment_type=employee&employee_id='.$employee->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $employeeAssignmentId)
            ->assertJsonPath('data.0.shift.id', $shiftId);

        $this->getJson('/api/v1/attendance/rosters?employee_id='.$employee->id.'&date_from=2026-06-01&date_to=2026-06-30')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.employee.id', $employee->id);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'attendance.shift.updated',
            'entity_id' => (string) $shiftId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'attendance.shift_assignment.created',
            'entity_id' => (string) $employeeAssignmentId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'attendance.roster.scheduled',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $tenantAdmin->id,
            'event_type' => 'attendance.roster.updated',
            'entity_id' => (string) $rosterId,
        ]);
    }

    public function test_shift_scheduling_validation_rules_and_conflicts_are_enforced(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $location] = $this->createAttendanceStructure($company->id, 'VAL');

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/attendance/shifts', [
            'code' => 'SHIFT-ERR',
            'name' => 'Invalid Shift',
            'description' => 'Invalid equal start and end times',
            'start_time' => '09:00',
            'end_time' => '09:00',
            'break_duration_minutes' => 30,
            'grace_minutes' => 5,
            'working_hours_minutes' => 480,
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);

        $shiftId = $this->postJson('/api/v1/attendance/shifts', [
            'code' => 'SHIFT-VAL',
            'name' => 'Validation Shift',
            'description' => 'Validation shift',
            'start_time' => '09:00',
            'end_time' => '17:30',
            'break_duration_minutes' => 30,
            'grace_minutes' => 5,
            'working_hours_minutes' => 480,
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/attendance/shift-assignments', [
            'shift_id' => $shiftId,
            'assignment_type' => 'employee',
            'employee_id' => $employee->id,
            'effective_from' => '2026-06-01',
            'effective_to' => '2026-06-30',
            'notes' => 'Primary schedule',
            'status' => 'active',
        ])->assertCreated();

        $this->postJson('/api/v1/attendance/shift-assignments', [
            'shift_id' => $shiftId,
            'assignment_type' => 'employee',
            'employee_id' => $employee->id,
            'effective_from' => '2026-06-15',
            'effective_to' => null,
            'notes' => 'Overlapping schedule',
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['employee_id']);

        $this->postJson('/api/v1/attendance/shift-assignments', [
            'shift_id' => $shiftId,
            'assignment_type' => 'employee',
            'employee_id' => $employee->id,
            'effective_from' => '2026-06-15',
            'effective_to' => null,
            'notes' => 'Dormant overlap allowed',
            'status' => 'inactive',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'inactive');

        $this->postJson('/api/v1/attendance/rosters', [
            'entries' => [
                [
                    'employee_id' => $employee->id,
                    'shift_id' => $shiftId,
                    'work_date' => '2026-06-10',
                    'status' => 'scheduled',
                ],
                [
                    'employee_id' => $employee->id,
                    'shift_id' => $shiftId,
                    'work_date' => '2026-06-10',
                    'status' => 'scheduled',
                ],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['entries.0.work_date', 'entries.1.work_date']);

        $rosterId = $this->postJson('/api/v1/attendance/rosters', [
            'entries' => [
                [
                    'employee_id' => $employee->id,
                    'shift_id' => $shiftId,
                    'work_date' => '2026-06-11',
                    'status' => 'scheduled',
                ],
            ],
        ])->assertCreated()->json('data.0.id');

        $this->postJson('/api/v1/attendance/rosters', [
            'entries' => [
                [
                    'employee_id' => $employee->id,
                    'shift_id' => $shiftId,
                    'work_date' => '2026-06-11',
                    'status' => 'scheduled',
                ],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['entries']);

        $this->postJson('/api/v1/attendance/rosters', [
            'entries' => [
                [
                    'employee_id' => $employee->id,
                    'shift_id' => $shiftId,
                    'work_date' => '2026-06-12',
                    'status' => 'scheduled',
                ],
            ],
        ])->assertCreated();

        $this->patchJson('/api/v1/attendance/rosters/'.$rosterId, [
            'shift_id' => $shiftId,
            'work_date' => '2026-06-12',
            'notes' => 'Conflicting update',
            'status' => 'scheduled',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['work_date']);
    }

    public function test_shift_scheduling_endpoints_are_tenant_scoped_and_manage_actions_require_permission(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $location] = $this->createAttendanceStructure($company->id, 'LOC');
        $localEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);

        $localShift = Shift::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'LOC-DAY',
            'name' => 'Local Day Shift',
            'description' => 'Local tenant fixture',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_duration_minutes' => 60,
            'grace_minutes' => 10,
            'working_hours_minutes' => 480,
            'is_overnight' => false,
            'status' => 'active',
        ]);

        $otherCompany = Company::factory()->create(['status' => 'active']);
        [$otherDepartment, $otherLocation] = $this->createAttendanceStructure($otherCompany->id, 'OTH');
        $otherEmployee = Employee::factory()->create([
            'company_id' => $otherCompany->id,
            'department_id' => $otherDepartment->id,
            'location_id' => $otherLocation->id,
        ]);

        $otherShift = Shift::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'code' => 'OTH-NIGHT',
            'name' => 'Other Tenant Night Shift',
            'description' => 'Cross-tenant fixture',
            'start_time' => '21:00',
            'end_time' => '05:00',
            'break_duration_minutes' => 45,
            'grace_minutes' => 10,
            'working_hours_minutes' => 450,
            'is_overnight' => true,
            'status' => 'active',
        ]);

        $otherAssignment = ShiftAssignment::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'shift_id' => $otherShift->id,
            'assignment_type' => 'employee',
            'employee_id' => $otherEmployee->id,
            'effective_from' => '2026-06-01',
            'effective_to' => null,
            'notes' => 'Other tenant assignment',
            'status' => 'active',
        ]);

        $otherRoster = ShiftRoster::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'employee_id' => $otherEmployee->id,
            'shift_id' => $otherShift->id,
            'work_date' => '2026-06-05',
            'notes' => 'Other tenant roster',
            'status' => 'scheduled',
        ]);

        Sanctum::actingAs($employeeUser);

        $this->postJson('/api/v1/attendance/shifts', [
            'code' => 'EMP-SHIFT',
            'name' => 'Employee Attempt',
            'description' => 'Should be blocked',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_duration_minutes' => 60,
            'grace_minutes' => 10,
            'working_hours_minutes' => 480,
            'status' => 'active',
        ])->assertStatus(403);

        Sanctum::actingAs($hrAdmin);

        $this->getJson('/api/v1/attendance/shifts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $localShift->id);

        $this->getJson('/api/v1/attendance/shift-assignments')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/api/v1/attendance/rosters')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->patchJson('/api/v1/attendance/shifts/'.$otherShift->id, [
            'code' => 'OTH-NIGHT',
            'name' => 'Blocked Update',
            'description' => 'Should remain hidden',
            'start_time' => '21:00',
            'end_time' => '05:00',
            'break_duration_minutes' => 45,
            'grace_minutes' => 10,
            'working_hours_minutes' => 450,
            'status' => 'active',
        ])->assertStatus(404);

        $this->patchJson('/api/v1/attendance/shift-assignments/'.$otherAssignment->id, [
            'shift_id' => $localShift->id,
            'assignment_type' => 'employee',
            'employee_id' => $localEmployee->id,
            'effective_from' => '2026-06-01',
            'effective_to' => null,
            'notes' => 'Blocked update',
            'status' => 'active',
        ])->assertStatus(404);

        $this->patchJson('/api/v1/attendance/rosters/'.$otherRoster->id, [
            'shift_id' => $localShift->id,
            'work_date' => '2026-06-05',
            'notes' => 'Blocked update',
            'status' => 'scheduled',
        ])->assertStatus(404);
    }

    public function test_employee_can_check_in_and_check_out_with_metadata_and_worked_minutes(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $location] = $this->createAttendanceStructure($company->id, 'CAP');

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $employeeUser->id,
        ]);

        $shift = Shift::query()->create([
            'company_id' => $company->id,
            'code' => 'DAY-CAP',
            'name' => 'Capture Day Shift',
            'description' => 'Capture baseline shift',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_duration_minutes' => 60,
            'grace_minutes' => 10,
            'working_hours_minutes' => 480,
            'is_overnight' => false,
            'status' => 'active',
        ]);

        $roster = ShiftRoster::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'work_date' => '2026-06-10',
            'notes' => 'Capture roster',
            'status' => 'scheduled',
        ]);

        Sanctum::actingAs($employeeUser);

        $checkInResponse = $this->postJson('/api/v1/attendance/check-in', [
            'channel' => 'web',
            'captured_at' => '2026-06-10T09:05:00+05:30',
            'device' => [
                'device_id' => 'DEVICE-001',
                'device_name' => 'Pixel 8',
                'platform' => 'Android',
                'browser' => 'Chrome',
            ],
            'geolocation' => [
                'latitude' => 12.9716,
                'longitude' => 77.5946,
                'accuracy_meters' => 25,
            ],
        ])->assertCreated()
            ->assertJsonPath('data.employee.id', $employee->id)
            ->assertJsonPath('data.shift.id', $shift->id)
            ->assertJsonPath('data.shift_roster_id', $roster->id)
            ->assertJsonPath('data.state', 'checked_in')
            ->assertJsonPath('data.check_in.channel', 'web')
            ->assertJsonPath('data.check_in.metadata.device.platform', 'Android');

        $recordId = $checkInResponse->json('data.id');

        $this->postJson('/api/v1/attendance/check-out', [
            'channel' => 'api',
            'captured_at' => '2026-06-10T18:20:00+05:30',
            'device' => [
                'device_name' => 'Phoenix Web',
                'platform' => 'Browser',
                'browser' => 'Chrome',
            ],
        ])->assertOk()
            ->assertJsonPath('data.id', $recordId)
            ->assertJsonPath('data.state', 'checked_out')
            ->assertJsonPath('data.worked_minutes', 495)
            ->assertJsonPath('data.check_out.channel', 'api')
            ->assertJsonPath('data.calculation.primary_status', 'present')
            ->assertJsonPath('data.calculation.is_late', false)
            ->assertJsonPath('data.calculation.overtime_minutes', 15)
            ->assertJsonPath('data.check_out.metadata.device.platform', 'Browser');

        $this->assertDatabaseHas('attendance_records', [
            'id' => $recordId,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'shift_roster_id' => $roster->id,
            'check_in_channel' => 'web',
            'check_out_channel' => 'api',
            'worked_minutes' => 495,
            'primary_status' => 'present',
            'overtime_minutes' => 15,
        ]);

        $this->assertSame('2026-06-10', AttendanceRecord::query()->findOrFail($recordId)->attendance_date?->toDateString());

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'attendance.record.checked_in',
            'entity_id' => (string) $recordId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'attendance.record.checked_out',
            'entity_id' => (string) $recordId,
        ]);
    }

    public function test_attendance_capture_rejects_invalid_duplicate_and_out_of_order_actions(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $location] = $this->createAttendanceStructure($company->id, 'VALCAP');

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $employeeUser->id,
        ]);

        Sanctum::actingAs($employeeUser);

        $this->postJson('/api/v1/attendance/check-out', [
            'captured_at' => '2026-06-11T18:00:00+05:30',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['attendance']);

        $this->postJson('/api/v1/attendance/check-in', [
            'captured_at' => '2026-06-11T09:00:00+05:30',
        ])->assertCreated();

        $this->postJson('/api/v1/attendance/check-in', [
            'captured_at' => '2026-06-11T10:00:00+05:30',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['attendance']);

        $this->postJson('/api/v1/attendance/check-out', [
            'captured_at' => '2026-06-11T08:59:00+05:30',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['captured_at']);

        $this->postJson('/api/v1/attendance/check-out', [
            'captured_at' => '2026-06-11T17:00:00+05:30',
        ])->assertOk()
            ->assertJsonPath('data.worked_minutes', 480)
            ->assertJsonPath('data.calculation.primary_status', 'present');

        $this->postJson('/api/v1/attendance/check-in', [
            'captured_at' => '2026-06-11T18:15:00+05:30',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['attendance']);
    }

    public function test_attendance_read_endpoints_are_scoped_for_employee_manager_hr_and_tenant(): void
    {
        $company = Company::factory()->create(['status' => 'active']);

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        $peerUser = User::factory()->create(['company_id' => $company->id]);
        $peerUser->assignRole('employee');

        [$department, $location] = $this->createAttendanceStructure($company->id, 'READ');

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $employeeUser->id,
        ]);

        $peerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $peerUser->id,
        ]);

        $managerRecord = AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $managerEmployee->id,
            'attendance_date' => '2026-06-12',
            'check_in_at' => '2026-06-12 03:30:00',
            'check_in_channel' => 'api',
            'worked_minutes' => 510,
            'check_out_at' => '2026-06-12 12:00:00',
            'check_out_channel' => 'api',
        ]);

        $employeeRecord = AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'attendance_date' => '2026-06-12',
            'check_in_at' => '2026-06-12 03:45:00',
            'check_in_channel' => 'web',
            'worked_minutes' => 525,
            'check_out_at' => '2026-06-12 12:30:00',
            'check_out_channel' => 'web',
        ]);

        $peerRecord = AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $peerEmployee->id,
            'attendance_date' => '2026-06-12',
            'check_in_at' => '2026-06-12 04:00:00',
            'check_in_channel' => 'api',
        ]);

        $otherCompany = Company::factory()->create(['status' => 'active']);
        [$otherDepartment, $otherLocation] = $this->createAttendanceStructure($otherCompany->id, 'OTHER');

        $otherEmployee = Employee::factory()->create([
            'company_id' => $otherCompany->id,
            'department_id' => $otherDepartment->id,
            'location_id' => $otherLocation->id,
        ]);

        $otherRecord = AttendanceRecord::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'employee_id' => $otherEmployee->id,
            'attendance_date' => '2026-06-12',
            'check_in_at' => '2026-06-12 03:30:00',
            'check_in_channel' => 'api',
        ]);

        Sanctum::actingAs($employeeUser);

        $this->getJson('/api/v1/attendance')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $employeeRecord->id);

        $this->getJson('/api/v1/attendance/'.$employeeRecord->id)
            ->assertOk()
            ->assertJsonPath('data.employee.id', $employee->id);

        $this->getJson('/api/v1/attendance/'.$managerRecord->id)
            ->assertStatus(404);

        Sanctum::actingAs($managerUser);

        $this->getJson('/api/v1/attendance')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 2);

        $this->getJson('/api/v1/attendance?employee_id='.$employee->id)
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $employeeRecord->id);

        $this->getJson('/api/v1/attendance/'.$employeeRecord->id)
            ->assertOk()
            ->assertJsonPath('data.employee.id', $employee->id);

        $this->getJson('/api/v1/attendance/'.$peerRecord->id)
            ->assertStatus(404);

        Sanctum::actingAs($hrAdmin);

        $this->getJson('/api/v1/attendance?state=checked_out')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 2);

        $this->getJson('/api/v1/attendance/'.$peerRecord->id)
            ->assertOk()
            ->assertJsonPath('data.employee.id', $peerEmployee->id)
            ->assertJsonPath('data.state', 'checked_in');

        $this->getJson('/api/v1/attendance/'.$otherRecord->id)
            ->assertStatus(404);
    }

    public function test_attendance_recalculation_derives_late_half_day_overtime_and_incomplete_statuses_deterministically(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $shift = Shift::query()->create([
            'company_id' => $company->id,
            'code' => 'DAY-CALC',
            'name' => 'Calculation Day Shift',
            'description' => 'Calculation baseline shift',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_duration_minutes' => 60,
            'grace_minutes' => 10,
            'working_hours_minutes' => 480,
            'is_overnight' => false,
            'status' => 'active',
        ]);

        [$employeeOneUser, $employeeOne] = $this->createAttendanceEmployee($company->id, 'CALA');
        [$employeeTwoUser, $employeeTwo] = $this->createAttendanceEmployee($company->id, 'CALB');
        [$employeeThreeUser, $employeeThree] = $this->createAttendanceEmployee($company->id, 'CALC');

        foreach ([$employeeOne, $employeeTwo, $employeeThree] as $employee) {
            ShiftRoster::query()->create([
                'company_id' => $company->id,
                'employee_id' => $employee->id,
                'shift_id' => $shift->id,
                'work_date' => '2026-05-27',
                'status' => 'scheduled',
            ]);
        }

        Sanctum::actingAs($employeeOneUser);
        $this->postJson('/api/v1/attendance/check-in', [
            'captured_at' => '2026-05-27T09:20:00+05:30',
        ])->assertCreated();
        $this->postJson('/api/v1/attendance/check-out', [
            'captured_at' => '2026-05-27T18:30:00+05:30',
        ])->assertOk();

        Sanctum::actingAs($employeeTwoUser);
        $this->postJson('/api/v1/attendance/check-in', [
            'captured_at' => '2026-05-27T09:30:00+05:30',
        ])->assertCreated();
        $this->postJson('/api/v1/attendance/check-out', [
            'captured_at' => '2026-05-27T13:30:00+05:30',
        ])->assertOk();

        Sanctum::actingAs($employeeThreeUser);
        $this->postJson('/api/v1/attendance/check-in', [
            'captured_at' => '2026-05-27T09:00:00+05:30',
        ])->assertCreated();

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/attendance/recalculate', [
            'date_from' => '2026-05-27',
            'date_to' => '2026-05-27',
        ])->assertOk()
            ->assertJsonPath('data.processed', 3)
            ->assertJsonPath('data.created', 0)
            ->assertJsonPath('data.updated', 0);

        $presentRecord = AttendanceRecord::query()->where('employee_id', $employeeOne->id)->whereDate('attendance_date', '2026-05-27')->firstOrFail();
        $halfDayRecord = AttendanceRecord::query()->where('employee_id', $employeeTwo->id)->whereDate('attendance_date', '2026-05-27')->firstOrFail();
        $incompleteRecord = AttendanceRecord::query()->where('employee_id', $employeeThree->id)->whereDate('attendance_date', '2026-05-27')->firstOrFail();

        $this->assertSame('present', $presentRecord->primary_status);
        $this->assertTrue($presentRecord->is_late);
        $this->assertSame(20, $presentRecord->late_minutes);
        $this->assertSame(490, $presentRecord->worked_minutes);
        $this->assertSame(10, $presentRecord->overtime_minutes);

        $this->assertSame('half_day', $halfDayRecord->primary_status);
        $this->assertTrue($halfDayRecord->is_late);
        $this->assertTrue($halfDayRecord->is_half_day);
        $this->assertSame(30, $halfDayRecord->late_minutes);
        $this->assertSame(180, $halfDayRecord->worked_minutes);
        $this->assertSame(0, $halfDayRecord->overtime_minutes);

        $this->assertSame('incomplete', $incompleteRecord->primary_status);
        $this->assertNull($incompleteRecord->worked_minutes);
        $this->assertFalse($incompleteRecord->is_half_day);

        $this->getJson('/api/v1/attendance?primary_status=half_day')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $halfDayRecord->id)
            ->assertJsonPath('data.items.0.calculation.primary_status', 'half_day');

        $this->postJson('/api/v1/attendance/recalculate', [
            'date_from' => '2026-05-27',
            'date_to' => '2026-05-27',
        ])->assertOk()
            ->assertJsonPath('data.processed', 3)
            ->assertJsonPath('data.created', 0)
            ->assertJsonPath('data.updated', 0);
    }

    public function test_attendance_recalculation_creates_absent_holiday_weekend_and_overnight_outcomes(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$employeeUser, $employee] = $this->createAttendanceEmployee($company->id, 'OVR');

        $calendar = HolidayCalendar::query()->create([
            'company_id' => $company->id,
            'code' => 'HOL-2026',
            'name' => 'Holiday Calendar',
            'is_default' => true,
            'status' => 'active',
        ]);

        Holiday::query()->create([
            'company_id' => $company->id,
            'holiday_calendar_id' => $calendar->id,
            'name' => 'Founders Day',
            'holiday_date' => '2026-05-29',
            'type' => 'company',
            'is_optional' => false,
        ]);

        $overnightShift = Shift::query()->create([
            'company_id' => $company->id,
            'code' => 'OVR-NIGHT',
            'name' => 'Overnight Shift',
            'description' => 'Night roster',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'break_duration_minutes' => 30,
            'grace_minutes' => 5,
            'working_hours_minutes' => 450,
            'is_overnight' => true,
            'status' => 'active',
        ]);

        ShiftRoster::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'shift_id' => $overnightShift->id,
            'work_date' => '2026-05-28',
            'status' => 'scheduled',
        ]);

        Sanctum::actingAs($employeeUser);
        $this->postJson('/api/v1/attendance/check-in', [
            'captured_at' => '2026-05-28T21:55:00+05:30',
        ])->assertCreated();
        $this->postJson('/api/v1/attendance/check-out', [
            'captured_at' => '2026-05-29T06:10:00+05:30',
        ])->assertOk();

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/attendance/recalculate', [
            'date_from' => '2026-05-27',
            'date_to' => '2026-05-31',
            'employee_id' => $employee->id,
        ])->assertOk()
            ->assertJsonPath('data.processed', 5)
            ->assertJsonPath('data.created', 4);

        $absentRecord = AttendanceRecord::query()->where('employee_id', $employee->id)->whereDate('attendance_date', '2026-05-27')->firstOrFail();
        $overnightRecord = AttendanceRecord::query()->where('employee_id', $employee->id)->whereDate('attendance_date', '2026-05-28')->firstOrFail();
        $holidayRecord = AttendanceRecord::query()->where('employee_id', $employee->id)->whereDate('attendance_date', '2026-05-29')->firstOrFail();
        $weekendRecord = AttendanceRecord::query()->where('employee_id', $employee->id)->whereDate('attendance_date', '2026-05-31')->firstOrFail();

        $this->assertSame('absent', $absentRecord->primary_status);
        $this->assertSame(0, $absentRecord->worked_minutes);
        $this->assertNull($absentRecord->check_in_at);

        $this->assertSame('present', $overnightRecord->primary_status);
        $this->assertSame(465, $overnightRecord->worked_minutes);
        $this->assertFalse($overnightRecord->is_late);
        $this->assertSame('2026-05-29 06:00:00', $overnightRecord->scheduled_end_at?->format('Y-m-d H:i:s'));

        $this->assertSame('holiday', $holidayRecord->primary_status);
        $this->assertTrue($holidayRecord->is_holiday);
        $this->assertSame('Founders Day', $holidayRecord->holiday_name);
        $this->assertSame('not_captured', (string) $this->getJson('/api/v1/attendance/'.$holidayRecord->id)->assertOk()->json('data.state'));

        $this->assertSame('weekend', $weekendRecord->primary_status);
        $this->assertTrue($weekendRecord->is_weekend);
        $this->assertSame(0, $weekendRecord->worked_minutes);
    }

    public function test_employee_correction_is_approved_through_manager_and_hr_with_history_and_recalculation(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $location] = $this->createAttendanceStructure($company->id, 'CORR');

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $employeeUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $shift = Shift::query()->create([
            'company_id' => $company->id,
            'code' => 'CORR-DAY',
            'name' => 'Correction Day Shift',
            'description' => 'Correction approval baseline',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_duration_minutes' => 60,
            'grace_minutes' => 10,
            'working_hours_minutes' => 480,
            'is_overnight' => false,
            'status' => 'active',
        ]);

        ShiftRoster::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'work_date' => '2026-05-26',
            'status' => 'scheduled',
        ]);

        Sanctum::actingAs($employeeUser);
        $recordId = $this->postJson('/api/v1/attendance/check-in', [
            'captured_at' => '2026-05-26T09:30:00+05:30',
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/attendance/check-out', [
            'captured_at' => '2026-05-26T17:30:00+05:30',
        ])->assertOk()
            ->assertJsonPath('data.calculation.is_late', true)
            ->assertJsonPath('data.calculation.late_minutes', 30)
            ->assertJsonPath('data.calculation.overtime_minutes', 0)
            ->assertJsonPath('data.worked_minutes', 420);

        $correctionId = $this->postJson('/api/v1/attendance/corrections', [
            'attendance_record_id' => $recordId,
            'reason' => 'I forgot to use the kiosk at the exact times.',
            'corrected' => [
                'check_in_at' => '2026-05-26T09:00:00+05:30',
                'check_out_at' => '2026-05-26T18:15:00+05:30',
            ],
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.original_values.check_in_at', '2026-05-26T09:30:00+05:30')
            ->assertJsonPath('data.corrected_values.check_out_at', '2026-05-26T18:15:00+05:30')
            ->assertJsonPath('data.workflow.approval_history.0.stage_key', 'manager_review')
            ->json('data.id');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'attendance.correction.submitted',
            'entity_id' => (string) $correctionId,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $managerUser->id,
            'delivery_status' => 'delivered',
            'deep_link' => '/tasks/1',
        ]);

        Sanctum::actingAs($managerUser);
        $this->patchJson('/api/v1/attendance/corrections/'.$correctionId, [
            'action' => 'approve',
            'comment' => 'Manager approval granted.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.workflow.status', 'running')
            ->assertJsonPath('data.workflow.current_task.stage_key', 'hr_review')
            ->assertJsonPath('data.workflow.approval_history.0.decision', 'approve');

        Sanctum::actingAs($hrAdmin);
        $this->patchJson('/api/v1/attendance/corrections/'.$correctionId, [
            'action' => 'approve',
            'comment' => 'HR approval granted.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.workflow.status', 'completed')
            ->assertJsonPath('data.workflow.approval_history.1.decision', 'approve')
            ->assertJsonPath('data.applied_values.check_in_at', '2026-05-26T09:00:00+05:30')
            ->assertJsonPath('data.applied_values.check_out_at', '2026-05-26T18:15:00+05:30');

        $record = AttendanceRecord::query()->findOrFail($recordId);
        $correction = AttendanceCorrection::query()->findOrFail($correctionId);

        $this->assertSame('2026-05-26 09:00:00', $record->check_in_at?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-05-26 18:15:00', $record->check_out_at?->format('Y-m-d H:i:s'));
        $this->assertSame(495, $record->worked_minutes);
        $this->assertSame('present', $record->primary_status);
        $this->assertFalse($record->is_late);
        $this->assertSame(15, $record->overtime_minutes);
        $this->assertSame('2026-05-26T09:30:00+05:30', $correction->original_values['check_in_at']);
        $this->assertSame('2026-05-26T18:15:00+05:30', $correction->applied_values['check_out_at']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'attendance.correction.approved',
            'entity_id' => (string) $correctionId,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $employeeUser->id,
            'title' => 'Attendance correction approved',
            'delivery_status' => 'delivered',
            'deep_link' => '/attendance/corrections/'.$correctionId,
        ]);
    }

    public function test_attendance_correction_rejections_are_scoped_and_do_not_mutate_records(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        $peerUser = User::factory()->create(['company_id' => $company->id]);
        $peerUser->assignRole('employee');

        [$department, $location] = $this->createAttendanceStructure($company->id, 'CORR2');

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $employeeUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $peerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $peerUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $record = AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-25',
            'check_in_at' => '2026-05-25 03:45:00',
            'check_in_channel' => 'web',
            'check_out_at' => '2026-05-25 12:00:00',
            'check_out_channel' => 'web',
            'worked_minutes' => 495,
            'primary_status' => 'present',
        ]);

        Sanctum::actingAs($employeeUser);
        $correctionId = $this->postJson('/api/v1/attendance/corrections', [
            'attendance_record_id' => $record->id,
            'reason' => 'Checkout time was captured incorrectly.',
            'corrected' => [
                'check_out_at' => '2026-05-25T11:45:00+05:30',
            ],
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($peerUser);
        $this->getJson('/api/v1/attendance/corrections')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 0);

        $this->patchJson('/api/v1/attendance/corrections/'.$correctionId, [
            'action' => 'approve',
            'comment' => 'Peer cannot approve.',
        ])->assertStatus(403);

        $otherCompany = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $otherHr = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherHr->assignRole('hr.admin');

        Sanctum::actingAs($otherHr);
        $this->patchJson('/api/v1/attendance/corrections/'.$correctionId, [
            'action' => 'reject',
            'comment' => 'Cross-tenant users must not resolve corrections.',
        ])->assertStatus(404);

        Sanctum::actingAs($managerUser);
        $this->patchJson('/api/v1/attendance/corrections/'.$correctionId, [
            'action' => 'reject',
            'comment' => 'Please use the roster evidence and resubmit.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.workflow.status', 'rejected')
            ->assertJsonPath('data.decision_comment', 'Please use the roster evidence and resubmit.');

        $record->refresh();

        $this->assertSame('2026-05-25 12:00:00', $record->check_out_at?->format('Y-m-d H:i:s'));
        $this->assertSame(495, $record->worked_minutes);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $managerUser->id,
            'event_type' => 'attendance.correction.rejected',
            'entity_id' => (string) $correctionId,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $employeeUser->id,
            'title' => 'Attendance correction rejected',
            'delivery_status' => 'delivered',
            'deep_link' => '/attendance/corrections/'.$correctionId,
        ]);

        $this->assertSame(0, NotificationRecord::query()->where('user_id', $peerUser->id)->count());
        $this->assertNotSame($employee->id, $peerEmployee->id);
    }

    public function test_hr_admin_can_view_operational_attendance_review_and_pending_exceptions(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $lateEmployeeUser = User::factory()->create(['company_id' => $company->id]);
        $lateEmployeeUser->assignRole('employee');

        $presentEmployeeUser = User::factory()->create(['company_id' => $company->id]);
        $presentEmployeeUser->assignRole('employee');

        $incompleteEmployeeUser = User::factory()->create(['company_id' => $company->id]);
        $incompleteEmployeeUser->assignRole('employee');

        [$department, $location] = $this->createAttendanceStructure($company->id, 'OPSHR');

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $lateEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $lateEmployeeUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $presentEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $presentEmployeeUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $incompleteEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $incompleteEmployeeUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $absentEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $lateRecord = AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $lateEmployee->id,
            'attendance_date' => '2026-06-03',
            'check_in_at' => '2026-06-03 03:45:00',
            'check_in_channel' => 'web',
            'check_out_at' => '2026-06-03 12:15:00',
            'check_out_channel' => 'web',
            'worked_minutes' => 450,
            'primary_status' => 'present',
            'is_late' => true,
            'late_minutes' => 15,
            'is_half_day' => false,
            'overtime_minutes' => 0,
        ]);

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $presentEmployee->id,
            'attendance_date' => '2026-06-03',
            'check_in_at' => '2026-06-03 03:30:00',
            'check_in_channel' => 'api',
            'check_out_at' => '2026-06-03 12:30:00',
            'check_out_channel' => 'api',
            'worked_minutes' => 480,
            'primary_status' => 'present',
            'is_late' => false,
            'late_minutes' => 0,
            'is_half_day' => false,
            'overtime_minutes' => 0,
        ]);

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $incompleteEmployee->id,
            'attendance_date' => '2026-06-03',
            'check_in_at' => '2026-06-03 03:35:00',
            'check_in_channel' => 'web',
            'primary_status' => 'incomplete',
            'is_late' => false,
            'late_minutes' => 0,
            'is_half_day' => false,
            'overtime_minutes' => 0,
        ]);

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $absentEmployee->id,
            'attendance_date' => '2026-06-03',
            'worked_minutes' => 0,
            'primary_status' => 'absent',
            'is_late' => false,
            'late_minutes' => 0,
            'is_half_day' => false,
            'overtime_minutes' => 0,
        ]);

        Sanctum::actingAs($lateEmployeeUser);
        $this->postJson('/api/v1/attendance/corrections', [
            'attendance_record_id' => $lateRecord->id,
            'reason' => 'Actual checkout was slightly later.',
            'corrected' => [
                'check_out_at' => '2026-06-03T12:30:00+05:30',
            ],
        ])->assertCreated();

        Sanctum::actingAs($hrAdmin);

        $overviewResponse = $this->getJson('/api/v1/attendance/operational-review?date=2026-06-03')
            ->assertOk()
            ->assertJsonPath('data.window_date', '2026-06-03')
            ->assertJsonPath('data.summary.total_records', 4)
            ->assertJsonPath('data.summary.present_count', 2)
            ->assertJsonPath('data.summary.absent_count', 1)
            ->assertJsonPath('data.summary.incomplete_count', 1)
            ->assertJsonPath('data.summary.late_count', 1)
            ->assertJsonPath('data.summary.pending_correction_count', 1)
            ->assertJsonCount(4, 'data.items');

        $lateOverviewItem = collect($overviewResponse->json('data.items'))
            ->firstWhere('id', $lateRecord->id);

        $this->assertSame(['late', 'pending_correction'], $lateOverviewItem['exception_types']);
        $this->assertTrue($lateOverviewItem['has_pending_correction']);

        $pendingResponse = $this->getJson('/api/v1/attendance/pending-exceptions?date=2026-06-03')
            ->assertOk()
            ->assertJsonPath('data.window_date', '2026-06-03')
            ->assertJsonPath('data.summary.exception_record_count', 3)
            ->assertJsonPath('data.summary.late_record_count', 1)
            ->assertJsonPath('data.summary.absent_record_count', 1)
            ->assertJsonPath('data.summary.incomplete_record_count', 1)
            ->assertJsonPath('data.summary.pending_correction_record_count', 1)
            ->assertJsonPath('data.summary.pending_correction_request_count', 1)
            ->assertJsonCount(3, 'data.attendance_items')
            ->assertJsonCount(1, 'data.correction_items');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'attendance.review.operational_viewed',
            'entity_id' => '2026-06-03',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'attendance.review.pending_exceptions_viewed',
            'entity_id' => '2026-06-03',
        ]);
    }

    public function test_manager_operational_review_is_team_scoped_and_permission_controlled(): void
    {
        $company = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $teamMemberUser = User::factory()->create(['company_id' => $company->id]);
        $teamMemberUser->assignRole('employee');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $location] = $this->createAttendanceStructure($company->id, 'OPSMGR');

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $teamMember = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $teamMemberUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $secondTeamMember = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $peerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $employeeUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $teamLateRecord = AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $teamMember->id,
            'attendance_date' => '2026-06-04',
            'check_in_at' => '2026-06-04 03:50:00',
            'check_in_channel' => 'web',
            'check_out_at' => '2026-06-04 12:20:00',
            'check_out_channel' => 'web',
            'worked_minutes' => 450,
            'primary_status' => 'present',
            'is_late' => true,
            'late_minutes' => 20,
            'is_half_day' => false,
            'overtime_minutes' => 0,
        ]);

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $secondTeamMember->id,
            'attendance_date' => '2026-06-04',
            'worked_minutes' => 0,
            'primary_status' => 'absent',
            'is_late' => false,
            'late_minutes' => 0,
            'is_half_day' => false,
            'overtime_minutes' => 0,
        ]);

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $managerEmployee->id,
            'attendance_date' => '2026-06-04',
            'check_in_at' => '2026-06-04 03:30:00',
            'check_in_channel' => 'api',
            'check_out_at' => '2026-06-04 12:30:00',
            'check_out_channel' => 'api',
            'worked_minutes' => 480,
            'primary_status' => 'present',
            'is_late' => false,
            'late_minutes' => 0,
            'is_half_day' => false,
            'overtime_minutes' => 0,
        ]);

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $peerEmployee->id,
            'attendance_date' => '2026-06-04',
            'worked_minutes' => 0,
            'primary_status' => 'absent',
            'is_late' => false,
            'late_minutes' => 0,
            'is_half_day' => false,
            'overtime_minutes' => 0,
        ]);

        $otherCompany = Company::factory()->create([
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        [$otherDepartment, $otherLocation] = $this->createAttendanceStructure($otherCompany->id, 'OPSOTH');

        $otherManagerUser = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherManagerUser->assignRole('manager');

        $otherManagerEmployee = Employee::factory()->create([
            'company_id' => $otherCompany->id,
            'department_id' => $otherDepartment->id,
            'location_id' => $otherLocation->id,
            'user_id' => $otherManagerUser->id,
            'date_of_joining' => '2026-01-01',
        ]);

        $otherEmployee = Employee::factory()->create([
            'company_id' => $otherCompany->id,
            'department_id' => $otherDepartment->id,
            'location_id' => $otherLocation->id,
            'manager_id' => $otherManagerEmployee->id,
            'date_of_joining' => '2026-01-01',
        ]);

        AttendanceRecord::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'employee_id' => $otherEmployee->id,
            'attendance_date' => '2026-06-04',
            'worked_minutes' => 0,
            'primary_status' => 'absent',
            'is_late' => false,
            'late_minutes' => 0,
            'is_half_day' => false,
            'overtime_minutes' => 0,
        ]);

        Sanctum::actingAs($teamMemberUser);
        $this->postJson('/api/v1/attendance/corrections', [
            'attendance_record_id' => $teamLateRecord->id,
            'reason' => 'Please review the corrected exit time.',
            'corrected' => [
                'check_out_at' => '2026-06-04T12:35:00+05:30',
            ],
        ])->assertCreated();

        Sanctum::actingAs($employeeUser);
        $this->getJson('/api/v1/attendance/operational-review?date=2026-06-04')
            ->assertStatus(403);

        Sanctum::actingAs($managerUser);

        $overviewResponse = $this->getJson('/api/v1/attendance/operational-review?date=2026-06-04')
            ->assertOk()
            ->assertJsonPath('data.summary.total_records', 2)
            ->assertJsonPath('data.summary.absent_count', 1)
            ->assertJsonPath('data.summary.late_count', 1)
            ->assertJsonPath('data.summary.pending_correction_count', 1)
            ->assertJsonCount(2, 'data.items');

        $overviewEmployeeIds = collect($overviewResponse->json('data.items'))
            ->pluck('employee.id')
            ->all();

        $this->assertEqualsCanonicalizing([$teamMember->id, $secondTeamMember->id], $overviewEmployeeIds);

        $pendingResponse = $this->getJson('/api/v1/attendance/pending-exceptions?date=2026-06-04')
            ->assertOk()
            ->assertJsonPath('data.summary.exception_record_count', 2)
            ->assertJsonPath('data.summary.pending_correction_request_count', 1)
            ->assertJsonCount(2, 'data.attendance_items')
            ->assertJsonCount(1, 'data.correction_items');

        $pendingEmployeeIds = collect($pendingResponse->json('data.attendance_items'))
            ->pluck('employee.id')
            ->all();

        $this->assertEqualsCanonicalizing([$teamMember->id, $secondTeamMember->id], $pendingEmployeeIds);
    }

    /**
     * @return array{Department, Location}
     */
    private function createAttendanceStructure(int $companyId, string $suffix = ''): array
    {
        $label = $suffix === '' ? '' : '-'.$suffix;

        $department = Department::factory()->create([
            'company_id' => $companyId,
            'code' => 'ATTDEP'.$suffix.'01',
            'name' => 'Attendance Department'.$label,
        ]);

        $location = Location::factory()->create([
            'company_id' => $companyId,
            'code' => 'ATTLOC'.$suffix.'01',
            'name' => 'Attendance Location'.$label,
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
        ]);

        return [$department, $location];
    }

    /**
     * @return array{User, Employee}
     */
    private function createAttendanceEmployee(int $companyId, string $suffix): array
    {
        $user = User::factory()->create([
            'company_id' => $companyId,
            'email' => strtolower($suffix).'.employee@phoenixhrms.test',
        ]);
        $user->assignRole('employee');

        [$department, $location] = $this->createAttendanceStructure($companyId, $suffix);

        $employee = Employee::factory()->create([
            'company_id' => $companyId,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $user->id,
            'date_of_joining' => '2026-01-01',
        ]);

        return [$user, $employee];
    }
}
