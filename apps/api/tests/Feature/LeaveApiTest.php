<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceEntry;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Location;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowStage;
use App\Models\WorkflowVersion;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LeaveApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_hr_admin_can_manage_leave_types_and_policy_rules(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'LEAVE-HR',
            'name' => 'Leave Operations',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'LEAVE-BLR',
            'name' => 'Bengaluru Campus',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Bengaluru',
            'country' => 'India',
        ]);

        Sanctum::actingAs($hrAdmin);

        $leaveTypeId = $this->postJson('/api/v1/leave/types', [
            'code' => 'el',
            'name' => 'Earned leave',
            'category' => 'earned',
            'description' => 'Primary annual leave bucket.',
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#0f766e',
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.code', 'EL')
            ->assertJsonPath('data.name', 'Earned leave')
            ->json('data.id');

        $this->patchJson("/api/v1/leave/types/{$leaveTypeId}", [
            'code' => 'EL',
            'name' => 'Earned Leave',
            'category' => 'earned',
            'description' => 'Primary annual leave bucket with manager approval.',
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#0F766E',
            'status' => 'active',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Earned Leave');

        $policyId = $this->postJson('/api/v1/leave/policies', [
            'leave_type_id' => $leaveTypeId,
            'annual_allowance_days' => 18,
            'opening_balance_days' => 2,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 8,
            'encashment_limit_days' => 5,
            'max_consecutive_days' => 10,
            'min_notice_days' => 2,
            'requires_documentation_after_days' => 4,
            'applicable_department_id' => $department->id,
            'applicable_location_id' => $location->id,
            'eligibility_rule' => [
                'employment_types' => ['full_time'],
                'employment_statuses' => ['active', 'probation'],
                'genders' => ['female'],
                'marital_statuses' => ['single', 'married'],
                'minimum_tenure_days' => 30,
            ],
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.leave_type.id', $leaveTypeId)
            ->assertJsonPath('data.version', 1)
            ->assertJsonPath('data.applicable_department.id', $department->id)
            ->assertJsonPath('data.applicable_location.id', $location->id)
            ->assertJsonPath('data.eligibility_rule.minimum_tenure_days', 30)
            ->json('data.id');

        $this->patchJson("/api/v1/leave/policies/{$policyId}", [
            'leave_type_id' => $leaveTypeId,
            'annual_allowance_days' => 18,
            'opening_balance_days' => 2,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 10,
            'encashment_limit_days' => 6,
            'max_consecutive_days' => 12,
            'min_notice_days' => 3,
            'requires_documentation_after_days' => 5,
            'applicable_department_id' => $department->id,
            'applicable_location_id' => $location->id,
            'eligibility_rule' => [
                'employment_types' => ['full_time'],
                'employment_statuses' => ['active'],
                'genders' => ['female'],
                'marital_statuses' => ['married', 'single'],
                'minimum_tenure_days' => 90,
            ],
            'status' => 'active',
        ])->assertOk()
            ->assertJsonPath('data.version', 2)
            ->assertJsonPath('data.carry_forward_limit_days', 10)
            ->assertJsonPath('data.eligibility_rule.minimum_tenure_days', 90);

        $this->getJson('/api/v1/leave/types')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'EL');

        $this->getJson('/api/v1/leave/policies')
            ->assertOk()
            ->assertJsonPath('data.0.id', $policyId)
            ->assertJsonPath('data.0.version', 2);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'leave.type.created',
            'entity_id' => (string) $leaveTypeId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'leave.policy.updated',
            'entity_id' => (string) $policyId,
        ]);
    }

    public function test_leave_configuration_validation_and_scope_rules_are_enforced(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $otherCompany = Company::factory()->create(['status' => 'active']);

        $otherDepartment = Department::factory()->create([
            'company_id' => $otherCompany->id,
            'code' => 'OTH-LEAVE',
            'name' => 'Other Department',
        ]);

        Sanctum::actingAs($hrAdmin);

        $leaveTypeId = $this->postJson('/api/v1/leave/types', [
            'code' => 'SL',
            'name' => 'Sick Leave',
            'category' => 'sick',
            'description' => 'Medical leave type.',
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#B91C1C',
            'status' => 'active',
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/leave/types', [
            'code' => 'SL',
            'name' => 'Second Sick Leave',
            'category' => 'sick',
            'description' => null,
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#B91C1C',
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        $this->postJson('/api/v1/leave/policies', [
            'leave_type_id' => $leaveTypeId,
            'annual_allowance_days' => 12,
            'opening_balance_days' => 0,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 6,
            'encashment_limit_days' => 0,
            'max_consecutive_days' => 5,
            'min_notice_days' => 0,
            'requires_documentation_after_days' => 2,
            'applicable_department_id' => $otherDepartment->id,
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['applicable_department_id']);

        $this->postJson('/api/v1/leave/policies', [
            'leave_type_id' => $leaveTypeId,
            'annual_allowance_days' => 12,
            'opening_balance_days' => 0,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 6,
            'encashment_limit_days' => 0,
            'max_consecutive_days' => 5,
            'min_notice_days' => 0,
            'requires_documentation_after_days' => 2,
            'eligibility_rule' => [
                'employment_types' => ['full_time'],
                'employment_statuses' => ['active'],
            ],
            'status' => 'active',
        ])->assertCreated();

        $this->postJson('/api/v1/leave/policies', [
            'leave_type_id' => $leaveTypeId,
            'annual_allowance_days' => 15,
            'opening_balance_days' => 0,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 8,
            'encashment_limit_days' => 1,
            'max_consecutive_days' => 5,
            'min_notice_days' => 1,
            'requires_documentation_after_days' => 2,
            'eligibility_rule' => [
                'employment_types' => ['full_time'],
                'employment_statuses' => ['active'],
            ],
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['leave_type_id']);
    }

    public function test_leave_configuration_endpoints_are_tenant_scoped_and_manage_actions_require_permission(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $employee = User::factory()->create(['company_id' => $company->id]);
        $employee->assignRole('employee');

        $otherCompany = Company::factory()->create(['status' => 'active']);

        $ownLeaveType = LeaveType::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'CL',
            'name' => 'Casual Leave',
            'category' => 'casual',
            'description' => null,
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#2563EB',
            'status' => 'active',
        ]);

        LeavePolicy::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'leave_type_id' => $ownLeaveType->id,
            'version' => 1,
            'scope_key' => hash('sha256', json_encode([
                'leave_type_id' => $ownLeaveType->id,
                'applicable_department_id' => null,
                'applicable_location_id' => null,
                'eligibility_rule' => [
                    'employment_types' => [],
                    'employment_statuses' => [],
                    'genders' => [],
                    'marital_statuses' => [],
                    'minimum_tenure_days' => null,
                ],
            ], JSON_THROW_ON_ERROR)),
            'annual_allowance_days' => 10,
            'opening_balance_days' => 0,
            'accrual_frequency' => 'none',
            'carry_forward_limit_days' => 0,
            'encashment_limit_days' => 0,
            'max_consecutive_days' => 3,
            'min_notice_days' => 0,
            'requires_documentation_after_days' => null,
            'applicable_department_id' => null,
            'applicable_location_id' => null,
            'eligibility_rule' => [
                'employment_types' => [],
                'employment_statuses' => [],
                'genders' => [],
                'marital_statuses' => [],
                'minimum_tenure_days' => null,
            ],
            'status' => 'active',
        ]);

        $otherLeaveType = LeaveType::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'code' => 'OTH',
            'name' => 'Other Tenant Leave',
            'category' => 'earned',
            'description' => null,
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#9333EA',
            'status' => 'active',
        ]);

        LeavePolicy::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'leave_type_id' => $otherLeaveType->id,
            'version' => 1,
            'scope_key' => hash('sha256', json_encode([
                'leave_type_id' => $otherLeaveType->id,
                'applicable_department_id' => null,
                'applicable_location_id' => null,
                'eligibility_rule' => [
                    'employment_types' => [],
                    'employment_statuses' => [],
                    'genders' => [],
                    'marital_statuses' => [],
                    'minimum_tenure_days' => null,
                ],
            ], JSON_THROW_ON_ERROR)),
            'annual_allowance_days' => 15,
            'opening_balance_days' => 0,
            'accrual_frequency' => 'annual',
            'carry_forward_limit_days' => 5,
            'encashment_limit_days' => 0,
            'max_consecutive_days' => 5,
            'min_notice_days' => 1,
            'requires_documentation_after_days' => null,
            'applicable_department_id' => null,
            'applicable_location_id' => null,
            'eligibility_rule' => [
                'employment_types' => [],
                'employment_statuses' => [],
                'genders' => [],
                'marital_statuses' => [],
                'minimum_tenure_days' => null,
            ],
            'status' => 'active',
        ]);

        Sanctum::actingAs($employee);

        $this->getJson('/api/v1/leave/types')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownLeaveType->id);

        $this->getJson('/api/v1/leave/policies')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.leave_type.id', $ownLeaveType->id);

        $this->postJson('/api/v1/leave/types', [
            'code' => 'UL',
            'name' => 'Unauthorized Leave',
            'category' => 'unpaid',
            'description' => null,
            'is_paid' => false,
            'requires_approval' => true,
            'allows_half_day' => false,
            'color_token' => '#111827',
            'status' => 'active',
        ])->assertStatus(403);

        Sanctum::actingAs($hrAdmin);

        $this->getJson('/api/v1/leave/types')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownLeaveType->id);
    }

    public function test_hr_admin_can_preview_repeatable_leave_accruals_with_carry_forward_and_encashment_caps(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'ACCR-HR',
            'name' => 'Accrual Operations',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'ACCR-BLR',
            'name' => 'Bengaluru Hub',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Bengaluru',
            'country' => 'India',
        ]);

        $leaveType = LeaveType::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'EL',
            'name' => 'Earned Leave',
            'category' => 'earned',
            'description' => null,
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#0F766E',
            'status' => 'active',
        ]);

        $policy = LeavePolicy::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'leave_type_id' => $leaveType->id,
            'version' => 1,
            'scope_key' => hash('sha256', json_encode([
                'leave_type_id' => $leaveType->id,
                'applicable_department_id' => $department->id,
                'applicable_location_id' => $location->id,
                'eligibility_rule' => [
                    'employment_types' => ['full_time'],
                    'employment_statuses' => ['active'],
                    'genders' => [],
                    'marital_statuses' => [],
                    'minimum_tenure_days' => 30,
                ],
            ], JSON_THROW_ON_ERROR)),
            'annual_allowance_days' => 24,
            'opening_balance_days' => 2,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 8,
            'encashment_limit_days' => 4,
            'max_consecutive_days' => 10,
            'min_notice_days' => 2,
            'requires_documentation_after_days' => null,
            'applicable_department_id' => $department->id,
            'applicable_location_id' => $location->id,
            'eligibility_rule' => [
                'employment_types' => ['full_time'],
                'employment_statuses' => ['active'],
                'genders' => [],
                'marital_statuses' => [],
                'minimum_tenure_days' => 30,
            ],
            'status' => 'active',
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP2001',
            'first_name' => 'Kabir',
            'last_name' => 'Malik',
            'date_of_joining' => '2025-10-01',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $response = $this->postJson("/api/v1/leave/policies/{$policy->id}/accrual-preview", [
            'employee_id' => $employee->id,
            'period_start' => '2026-01-09',
            'unused_balance_days' => 15,
            'used_days_in_period' => 3,
        ])->assertOk()
            ->assertJsonPath('data.employee.id', $employee->id)
            ->assertJsonPath('data.policy_version', 1)
            ->assertJsonPath('data.period.start', '2026-01-01')
            ->assertJsonPath('data.period.end', '2026-01-31')
            ->assertJsonPath('data.is_eligible', true)
            ->assertJsonPath('data.balances.opening_balance_days', 2)
            ->assertJsonPath('data.balances.accrued_days', 2)
            ->assertJsonPath('data.balances.carry_forward_days', 8)
            ->assertJsonPath('data.balances.encashable_days', 4)
            ->assertJsonPath('data.balances.projected_closing_balance_days', 9);

        $secondResponse = $this->postJson("/api/v1/leave/policies/{$policy->id}/accrual-preview", [
            'employee_id' => $employee->id,
            'period_start' => '2026-01-15',
            'unused_balance_days' => 15,
            'used_days_in_period' => 3,
        ])->assertOk()
            ->assertJsonPath('data.id', $response->json('data.id'))
            ->assertJsonPath('data.calculation_hash', $response->json('data.calculation_hash'));

        $this->assertDatabaseHas('leave_encashments', [
            'employee_id' => $employee->id,
            'leave_policy_id' => $policy->id,
            'projected_days' => 4,
            'status' => 'projected',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'leave.accrual.previewed',
            'entity_id' => (string) $response->json('data.id'),
        ]);
    }

    public function test_leave_accrual_preview_respects_eligibility_and_permission_scope(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'SICK-HR',
            'name' => 'People Support',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'SICK-LOC',
            'name' => 'Mumbai Branch',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Mumbai',
            'country' => 'India',
        ]);

        $leaveType = LeaveType::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'SL',
            'name' => 'Sick Leave',
            'category' => 'sick',
            'description' => null,
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#B91C1C',
            'status' => 'active',
        ]);

        $policy = LeavePolicy::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'leave_type_id' => $leaveType->id,
            'version' => 3,
            'scope_key' => hash('sha256', json_encode([
                'leave_type_id' => $leaveType->id,
                'applicable_department_id' => null,
                'applicable_location_id' => null,
                'eligibility_rule' => [
                    'employment_types' => ['full_time'],
                    'employment_statuses' => ['active'],
                    'genders' => [],
                    'marital_statuses' => [],
                    'minimum_tenure_days' => 180,
                ],
            ], JSON_THROW_ON_ERROR)),
            'annual_allowance_days' => 12,
            'opening_balance_days' => 1,
            'accrual_frequency' => 'quarterly',
            'carry_forward_limit_days' => 5,
            'encashment_limit_days' => 2,
            'max_consecutive_days' => 6,
            'min_notice_days' => 0,
            'requires_documentation_after_days' => 2,
            'applicable_department_id' => null,
            'applicable_location_id' => null,
            'eligibility_rule' => [
                'employment_types' => ['full_time'],
                'employment_statuses' => ['active'],
                'genders' => [],
                'marital_statuses' => [],
                'minimum_tenure_days' => 180,
            ],
            'status' => 'active',
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP3001',
            'first_name' => 'Naina',
            'last_name' => 'Kapoor',
            'date_of_joining' => '2026-02-15',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->postJson("/api/v1/leave/policies/{$policy->id}/accrual-preview", [
            'employee_id' => $employee->id,
            'period_start' => '2026-04-10',
            'unused_balance_days' => 4,
        ])->assertOk()
            ->assertJsonPath('data.policy_version', 3)
            ->assertJsonPath('data.period.start', '2026-04-01')
            ->assertJsonPath('data.period.end', '2026-06-30')
            ->assertJsonPath('data.is_eligible', false)
            ->assertJsonPath('data.balances.accrued_days', 0)
            ->assertJsonPath('data.balances.carry_forward_days', 0)
            ->assertJsonPath('data.balances.encashable_days', 0)
            ->assertJsonPath('data.eligibility_snapshot.ineligibility_reasons.0', 'Employee has not met the minimum tenure requirement.');

        $this->assertDatabaseCount('leave_encashments', 0);

        Sanctum::actingAs($employeeUser);

        $this->postJson("/api/v1/leave/policies/{$policy->id}/accrual-preview", [
            'employee_id' => $employee->id,
            'period_start' => '2026-04-10',
        ])->assertStatus(403);
    }

    public function test_hr_admin_can_read_policy_aware_leave_balances_and_history_after_accrual_sync(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'BAL-HR',
            'name' => 'Leave Balances',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'BAL-BLR',
            'name' => 'Bengaluru Balance Hub',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Bengaluru',
            'country' => 'India',
        ]);

        $leaveType = LeaveType::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'EL',
            'name' => 'Earned Leave',
            'category' => 'earned',
            'description' => null,
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#0F766E',
            'status' => 'active',
        ]);

        $policy = LeavePolicy::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'leave_type_id' => $leaveType->id,
            'version' => 1,
            'scope_key' => hash('sha256', json_encode([
                'leave_type_id' => $leaveType->id,
                'applicable_department_id' => $department->id,
                'applicable_location_id' => $location->id,
                'eligibility_rule' => [
                    'employment_types' => ['full_time'],
                    'employment_statuses' => ['active'],
                    'genders' => [],
                    'marital_statuses' => [],
                    'minimum_tenure_days' => 30,
                ],
            ], JSON_THROW_ON_ERROR)),
            'annual_allowance_days' => 24,
            'opening_balance_days' => 2,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 8,
            'encashment_limit_days' => 4,
            'max_consecutive_days' => 10,
            'min_notice_days' => 2,
            'requires_documentation_after_days' => null,
            'applicable_department_id' => $department->id,
            'applicable_location_id' => $location->id,
            'eligibility_rule' => [
                'employment_types' => ['full_time'],
                'employment_statuses' => ['active'],
                'genders' => [],
                'marital_statuses' => [],
                'minimum_tenure_days' => 30,
            ],
            'status' => 'active',
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP4001',
            'first_name' => 'Asha',
            'last_name' => 'Rao',
            'date_of_joining' => '2025-01-01',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->postJson("/api/v1/leave/policies/{$policy->id}/accrual-preview", [
            'employee_id' => $employee->id,
            'period_start' => '2026-01-12',
            'unused_balance_days' => 15,
            'used_days_in_period' => 3,
        ])->assertOk();

        $this->postJson("/api/v1/leave/policies/{$policy->id}/accrual-preview", [
            'employee_id' => $employee->id,
            'period_start' => '2026-02-05',
            'unused_balance_days' => 0,
            'used_days_in_period' => 1,
        ])->assertOk();

        $this->getJson("/api/v1/leave/balances?employee_id={$employee->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee.id', $employee->id)
            ->assertJsonPath('data.0.leave_type.id', $leaveType->id)
            ->assertJsonPath('data.0.available_days', 10)
            ->assertJsonPath('data.0.used_days', 4)
            ->assertJsonPath('data.0.accrued_days', 4)
            ->assertJsonPath('data.0.carry_forward_days', 8)
            ->assertJsonPath('data.0.projected_encashable_days', 0)
            ->assertJsonPath('data.0.current_period_start', '2026-02-01')
            ->assertJsonPath('data.0.current_period_end', '2026-02-28');

        $this->getJson("/api/v1/leave/balances/{$employee->id}")
            ->assertOk()
            ->assertJsonPath('data.employee.id', $employee->id)
            ->assertJsonCount(1, 'data.balances')
            ->assertJsonCount(6, 'data.history')
            ->assertJsonPath('data.history.0.entry_type', 'usage_projection')
            ->assertJsonPath('data.history.0.balance_after_days', 10)
            ->assertJsonPath('data.history.1.entry_type', 'accrual');

        $this->assertDatabaseHas('leave_balances', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'available_days' => 10,
            'used_days' => 4,
            'accrued_days' => 4,
            'carry_forward_days' => 8,
        ]);

        $this->assertDatabaseHas('leave_balance_entries', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'entry_type' => 'carry_forward',
            'quantity_days' => 8,
        ]);
    }

    public function test_leave_balance_visibility_is_scoped_for_manager_and_employee_contexts(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $directReportUser = User::factory()->create(['company_id' => $company->id]);
        $directReportUser->assignRole('employee');

        $peerUser = User::factory()->create(['company_id' => $company->id]);
        $peerUser->assignRole('employee');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'SCOPE-HR',
            'name' => 'Scope Team',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'SCOPE-LOC',
            'name' => 'Scope Office',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Hyderabad',
            'country' => 'India',
        ]);

        $leaveType = LeaveType::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'code' => 'CL',
            'name' => 'Casual Leave',
            'category' => 'casual',
            'description' => null,
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#2563EB',
            'status' => 'active',
        ]);

        $policy = LeavePolicy::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'leave_type_id' => $leaveType->id,
            'version' => 2,
            'scope_key' => hash('sha256', json_encode([
                'leave_type_id' => $leaveType->id,
                'applicable_department_id' => null,
                'applicable_location_id' => null,
                'eligibility_rule' => [
                    'employment_types' => ['full_time'],
                    'employment_statuses' => ['active'],
                    'genders' => [],
                    'marital_statuses' => [],
                    'minimum_tenure_days' => null,
                ],
            ], JSON_THROW_ON_ERROR)),
            'annual_allowance_days' => 12,
            'opening_balance_days' => 1,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 4,
            'encashment_limit_days' => 2,
            'max_consecutive_days' => 6,
            'min_notice_days' => 1,
            'requires_documentation_after_days' => null,
            'applicable_department_id' => null,
            'applicable_location_id' => null,
            'eligibility_rule' => [
                'employment_types' => ['full_time'],
                'employment_statuses' => ['active'],
                'genders' => [],
                'marital_statuses' => [],
                'minimum_tenure_days' => null,
            ],
            'status' => 'active',
        ]);

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP5001',
            'first_name' => 'Manager',
            'last_name' => 'One',
            'date_of_joining' => '2024-01-01',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
        ]);

        $directReport = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP5002',
            'first_name' => 'Direct',
            'last_name' => 'Report',
            'date_of_joining' => '2024-02-01',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $directReportUser->id,
        ]);

        $peerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP5003',
            'first_name' => 'Peer',
            'last_name' => 'Employee',
            'date_of_joining' => '2024-03-01',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $peerUser->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->postJson("/api/v1/leave/policies/{$policy->id}/accrual-preview", [
            'employee_id' => $directReport->id,
            'period_start' => '2026-03-04',
            'unused_balance_days' => 3,
            'used_days_in_period' => 1,
        ])->assertOk();

        $this->postJson("/api/v1/leave/policies/{$policy->id}/accrual-preview", [
            'employee_id' => $peerEmployee->id,
            'period_start' => '2026-03-04',
            'unused_balance_days' => 2,
            'used_days_in_period' => 0,
        ])->assertOk();

        Sanctum::actingAs($managerUser);

        $this->getJson('/api/v1/leave/balances')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee.id', $directReport->id);

        $this->getJson("/api/v1/leave/balances/{$directReport->id}")
            ->assertOk()
            ->assertJsonPath('data.employee.id', $directReport->id)
            ->assertJsonCount(2, 'data.history');

        $this->getJson("/api/v1/leave/balances/{$peerEmployee->id}")
            ->assertStatus(404);

        Sanctum::actingAs($directReportUser);

        $this->getJson('/api/v1/leave/balances')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee.id', $directReport->id);

        $this->getJson("/api/v1/leave/balances/{$directReport->id}")
            ->assertOk()
            ->assertJsonPath('data.employee.id', $directReport->id);

        $this->getJson("/api/v1/leave/balances/{$peerEmployee->id}")
            ->assertStatus(404);
    }

    public function test_employee_can_submit_pending_leave_request_and_cancel_it(): void
    {
        $this->travelTo(Carbon::parse('2026-06-03 09:00:00', 'Asia/Kolkata'));

        $company = Company::factory()->create(['status' => 'active', 'timezone' => 'Asia/Kolkata']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'REQ-HR',
            'name' => 'Request Team',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'REQ-BLR',
            'name' => 'Request Campus',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Bengaluru',
            'country' => 'India',
        ]);

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6100',
            'first_name' => 'Ishaan',
            'last_name' => 'Nanda',
            'date_of_joining' => '2024-01-10',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6101',
            'first_name' => 'Naina',
            'last_name' => 'Kapoor',
            'date_of_joining' => '2025-01-10',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $employeeUser->id,
        ]);

        ['leaveType' => $leaveType, 'policy' => $policy] = $this->createLeaveTypeAndPolicy($company, $department, $location, [
            'code' => 'CL',
            'name' => 'Casual Leave',
            'requires_approval' => true,
        ]);
        $this->seedLeaveApprovalWorkflowForCompany($company, $employeeUser);

        $this->seedLeaveBalance($employee, $leaveType, $policy, $employeeUser, 10);

        Sanctum::actingAs($employeeUser);

        $startDate = $this->nextWeekdayFromNow()->toDateString();
        $endDate = $this->nextWeekdayFromNow(1)->toDateString();

        $leaveRequestId = $this->postJson('/api/v1/leave/requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Family travel plans.',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.can_cancel', true)
            ->assertJsonPath('data.total_days', 2)
            ->assertJsonPath('data.workflow.definition.key', 'leave-approval')
            ->assertJsonPath('data.workflow.tasks.0.assignee.id', $managerUser->id)
            ->json('data.id');

        $this->getJson('/api/v1/leave/requests')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $leaveRequestId);

        $this->getJson("/api/v1/leave/requests/{$leaveRequestId}")
            ->assertOk()
            ->assertJsonPath('data.id', $leaveRequestId)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('leave_balances', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'available_days' => 8,
            'booked_days' => 2,
        ]);

        $this->patchJson("/api/v1/leave/requests/{$leaveRequestId}", [
            'action' => 'cancel',
            'comment' => 'Trip was postponed.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.can_cancel', false)
            ->assertJsonPath('data.attendance_sync_status', 'not_applicable')
            ->assertJsonPath('data.workflow.status', 'cancelled')
            ->assertJsonPath('data.workflow.tasks.0.decision', 'cancelled');

        $this->assertDatabaseHas('leave_balances', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'available_days' => 10,
            'booked_days' => 0,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'leave.request.submitted',
            'entity_id' => (string) $leaveRequestId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $employeeUser->id,
            'event_type' => 'leave.request.cancelled',
            'entity_id' => (string) $leaveRequestId,
        ]);

        $this->travelBack();
    }

    public function test_leave_request_submission_rejects_overlap_and_balance_exceed_conditions(): void
    {
        $this->travelTo(Carbon::parse('2026-06-03 09:00:00', 'Asia/Kolkata'));

        $company = Company::factory()->create(['status' => 'active', 'timezone' => 'Asia/Kolkata']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'REQ-VAL',
            'name' => 'Validation Team',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'REQ-HYD',
            'name' => 'Validation Office',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Hyderabad',
            'country' => 'India',
        ]);

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6107',
            'first_name' => 'Karan',
            'last_name' => 'Sethi',
            'date_of_joining' => '2024-01-10',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6102',
            'first_name' => 'Riya',
            'last_name' => 'Shah',
            'date_of_joining' => '2025-01-10',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $employeeUser->id,
        ]);

        ['leaveType' => $leaveType, 'policy' => $policy] = $this->createLeaveTypeAndPolicy($company, $department, $location, [
            'code' => 'SL',
            'name' => 'Sick Leave',
            'requires_approval' => true,
        ], [
            'annual_allowance_days' => 4,
            'opening_balance_days' => 0,
            'max_consecutive_days' => 5,
            'min_notice_days' => 0,
        ]);
        $this->seedLeaveApprovalWorkflowForCompany($company, $employeeUser);

        $this->seedLeaveBalance($employee, $leaveType, $policy, $employeeUser, 4);

        Sanctum::actingAs($employeeUser);

        $firstStart = $this->nextWeekdayFromNow()->toDateString();
        $firstEnd = $this->nextWeekdayFromNow(1)->toDateString();

        $this->postJson('/api/v1/leave/requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => $firstStart,
            'end_date' => $firstEnd,
            'reason' => 'Initial leave request.',
        ])->assertCreated();

        $this->postJson('/api/v1/leave/requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => $firstEnd,
            'end_date' => $this->nextWeekdayFromNow(2)->toDateString(),
            'reason' => 'Overlapping request.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);

        $this->postJson('/api/v1/leave/requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => $this->nextWeekdayFromNow(5)->toDateString(),
            'end_date' => $this->nextWeekdayFromNow(7)->toDateString(),
            'reason' => 'Balance exceed request.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);

        $this->travelBack();
    }

    public function test_leave_request_runs_manager_then_hr_workflow_and_final_approval_syncs_attendance(): void
    {
        $this->travelTo(Carbon::parse('2026-06-03 09:00:00', 'Asia/Kolkata'));

        $company = Company::factory()->create(['status' => 'active', 'timezone' => 'Asia/Kolkata']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');
        $hrUser = User::factory()->create(['company_id' => $company->id]);
        $hrUser->assignRole('hr.admin');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'REQ-APR',
            'name' => 'Approval Team',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'REQ-MUM',
            'name' => 'Mumbai Office',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Mumbai',
            'country' => 'India',
        ]);

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6110',
            'first_name' => 'Tara',
            'last_name' => 'Suri',
            'date_of_joining' => '2024-01-10',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6111',
            'first_name' => 'Mira',
            'last_name' => 'Joshi',
            'date_of_joining' => '2025-01-10',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $employeeUser->id,
        ]);

        ['leaveType' => $leaveType, 'policy' => $policy] = $this->createLeaveTypeAndPolicy($company, $department, $location, [
            'code' => 'PL',
            'name' => 'Planned Leave',
            'requires_approval' => true,
        ]);
        $this->seedLeaveApprovalWorkflowForCompany($company, $employeeUser);
        $this->seedLeaveBalance($employee, $leaveType, $policy, $employeeUser, 8);

        Sanctum::actingAs($employeeUser);

        $startDate = $this->nextWeekdayFromNow()->toDateString();
        $endDate = $this->nextWeekdayFromNow(1)->toDateString();

        $leaveRequestId = $this->postJson('/api/v1/leave/requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Annual family travel.',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.workflow.tasks.0.assignee.id', $managerUser->id)
            ->json('data.id');

        Sanctum::actingAs($managerUser);

        $this->patchJson("/api/v1/leave/requests/{$leaveRequestId}", [
            'action' => 'approve',
            'comment' => 'Approved after reviewing team coverage.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.workflow.current_stage_sequence', 2)
            ->assertJsonPath('data.workflow.tasks.0.decision', 'approve')
            ->assertJsonPath('data.workflow.tasks.1.assignee.id', $hrUser->id);

        Sanctum::actingAs($hrUser);

        $this->patchJson("/api/v1/leave/requests/{$leaveRequestId}", [
            'action' => 'approve',
            'comment' => 'Final HR approval recorded.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.attendance_sync_status', 'synced')
            ->assertJsonPath('data.approver_comment', 'Final HR approval recorded.')
            ->assertJsonPath('data.workflow.status', 'completed');

        $this->assertTrue(
            AttendanceRecord::query()
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $startDate)
                ->where('primary_status', 'leave')
                ->exists(),
        );

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrUser->id,
            'event_type' => 'leave.request.approved',
            'entity_id' => (string) $leaveRequestId,
        ]);

        $this->travelBack();
    }

    public function test_manager_can_reject_direct_report_leave_request_and_release_reserved_balance(): void
    {
        $this->travelTo(Carbon::parse('2026-06-03 09:00:00', 'Asia/Kolkata'));

        $company = Company::factory()->create(['status' => 'active', 'timezone' => 'Asia/Kolkata']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'REQ-REJ',
            'name' => 'Rejection Team',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'REQ-CHN',
            'name' => 'Chennai Office',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Chennai',
            'country' => 'India',
        ]);

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6112',
            'first_name' => 'Aditi',
            'last_name' => 'Rao',
            'date_of_joining' => '2024-01-10',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6113',
            'first_name' => 'Neil',
            'last_name' => 'Verma',
            'date_of_joining' => '2025-01-10',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $employeeUser->id,
        ]);

        ['leaveType' => $leaveType, 'policy' => $policy] = $this->createLeaveTypeAndPolicy($company, $department, $location, [
            'code' => 'UL',
            'name' => 'Unplanned Leave',
            'requires_approval' => true,
        ]);
        $this->seedLeaveApprovalWorkflowForCompany($company, $employeeUser);
        $this->seedLeaveBalance($employee, $leaveType, $policy, $employeeUser, 5);

        Sanctum::actingAs($employeeUser);

        $leaveRequestId = $this->postJson('/api/v1/leave/requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => $this->nextWeekdayFromNow()->toDateString(),
            'end_date' => $this->nextWeekdayFromNow(1)->toDateString(),
            'reason' => 'Urgent personal matter.',
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($managerUser);

        $this->patchJson("/api/v1/leave/requests/{$leaveRequestId}", [
            'action' => 'reject',
            'comment' => 'Please choose dates that do not affect the launch window.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.approver_comment', 'Please choose dates that do not affect the launch window.')
            ->assertJsonPath('data.workflow.status', 'rejected');

        $this->assertDatabaseHas('leave_balances', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'available_days' => 5,
            'booked_days' => 0,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $managerUser->id,
            'event_type' => 'leave.request.rejected',
            'entity_id' => (string) $leaveRequestId,
        ]);

        $this->travelBack();
    }

    public function test_auto_approved_leave_request_syncs_and_reverses_attendance_deterministically(): void
    {
        $this->travelTo(Carbon::parse('2026-06-03 09:00:00', 'Asia/Kolkata'));

        $company = Company::factory()->create(['status' => 'active', 'timezone' => 'Asia/Kolkata']);
        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'AUTO-HR',
            'name' => 'Auto Approval Team',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'AUTO-DEL',
            'name' => 'Delhi Office',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Delhi',
            'country' => 'India',
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6103',
            'first_name' => 'Aarav',
            'last_name' => 'Batra',
            'date_of_joining' => '2025-01-10',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $employeeUser->id,
        ]);

        ['leaveType' => $leaveType, 'policy' => $policy] = $this->createLeaveTypeAndPolicy($company, $department, $location, [
            'code' => 'WFH',
            'name' => 'Wellness Leave',
            'requires_approval' => false,
        ], [
            'annual_allowance_days' => 6,
            'opening_balance_days' => 0,
            'max_consecutive_days' => 5,
            'min_notice_days' => 0,
        ]);

        $this->seedLeaveBalance($employee, $leaveType, $policy, $employeeUser, 6);

        Sanctum::actingAs($employeeUser);

        $startDate = $this->nextWeekdayFromNow()->toDateString();
        $endDate = $this->nextWeekdayFromNow(1)->toDateString();

        $leaveRequestId = $this->postJson('/api/v1/leave/requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Personal wellness break.',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.is_auto_approved', true)
            ->assertJsonPath('data.attendance_sync_status', 'synced')
            ->json('data.id');

        $this->getJson("/api/v1/attendance?date_from={$startDate}&date_to={$endDate}&primary_status=leave")
            ->assertOk()
            ->assertJsonPath('data.meta.total', 2)
            ->assertJsonPath('data.items.0.calculation.primary_status', 'leave');

        $this->assertTrue(
            AttendanceRecord::query()
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $startDate)
                ->where('primary_status', 'leave')
                ->exists(),
        );

        $this->patchJson("/api/v1/leave/requests/{$leaveRequestId}", [
            'action' => 'cancel',
            'comment' => 'No longer needed.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.attendance_sync_status', 'reversed');

        $this->assertTrue(
            AttendanceRecord::query()
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $startDate)
                ->where('primary_status', 'absent')
                ->exists(),
        );

        $this->assertDatabaseHas('leave_balances', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'available_days' => 6,
            'booked_days' => 0,
        ]);

        $this->travelBack();
    }

    public function test_leave_request_visibility_is_scoped_for_manager_and_employee_contexts(): void
    {
        $company = Company::factory()->create(['status' => 'active', 'timezone' => 'Asia/Kolkata']);
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $directReportUser = User::factory()->create(['company_id' => $company->id]);
        $directReportUser->assignRole('employee');

        $peerUser = User::factory()->create(['company_id' => $company->id]);
        $peerUser->assignRole('employee');

        $department = Department::factory()->create([
            'company_id' => $company->id,
            'code' => 'REQ-SCOPE',
            'name' => 'Scope Team',
        ]);

        $location = Location::factory()->create([
            'company_id' => $company->id,
            'code' => 'REQ-SCOPE-LOC',
            'name' => 'Scope Office',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'city' => 'Pune',
            'country' => 'India',
        ]);

        ['leaveType' => $leaveType, 'policy' => $policy] = $this->createLeaveTypeAndPolicy($company, $department, $location, [
            'code' => 'ML',
            'name' => 'Manager Leave',
            'requires_approval' => true,
        ]);

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6104',
            'first_name' => 'Manager',
            'last_name' => 'Lead',
            'date_of_joining' => '2024-01-01',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
        ]);

        $directReport = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6105',
            'first_name' => 'Direct',
            'last_name' => 'Report',
            'date_of_joining' => '2024-02-01',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'user_id' => $directReportUser->id,
        ]);

        $peerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'employee_code' => 'EMP6106',
            'first_name' => 'Peer',
            'last_name' => 'Employee',
            'date_of_joining' => '2024-03-01',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'location_id' => $location->id,
            'user_id' => $peerUser->id,
        ]);

        $directRequest = LeaveRequest::query()->create([
            'company_id' => $company->id,
            'employee_id' => $directReport->id,
            'leave_type_id' => $leaveType->id,
            'leave_policy_id' => $policy->id,
            'policy_version' => $policy->version,
            'requested_by_user_id' => $directReportUser->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-11',
            'total_days' => 2,
            'status' => 'pending',
            'reason' => 'Direct report leave.',
            'attendance_sync_status' => 'not_applicable',
            'created_by_user_id' => $directReportUser->id,
            'updated_by_user_id' => $directReportUser->id,
        ]);

        $peerRequest = LeaveRequest::query()->create([
            'company_id' => $company->id,
            'employee_id' => $peerEmployee->id,
            'leave_type_id' => $leaveType->id,
            'leave_policy_id' => $policy->id,
            'policy_version' => $policy->version,
            'requested_by_user_id' => $peerUser->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'start_date' => '2026-06-12',
            'end_date' => '2026-06-12',
            'total_days' => 1,
            'status' => 'pending',
            'reason' => 'Peer leave.',
            'attendance_sync_status' => 'not_applicable',
            'created_by_user_id' => $peerUser->id,
            'updated_by_user_id' => $peerUser->id,
        ]);

        Sanctum::actingAs($managerUser);

        $this->getJson('/api/v1/leave/requests')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $directRequest->id);

        $this->getJson("/api/v1/leave/requests/{$directRequest->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $directRequest->id);

        $this->getJson("/api/v1/leave/requests/{$peerRequest->id}")
            ->assertStatus(404);

        Sanctum::actingAs($directReportUser);

        $this->getJson('/api/v1/leave/requests')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $directRequest->id);

        $this->getJson("/api/v1/leave/requests/{$peerRequest->id}")
            ->assertStatus(404);
    }

    /**
     * @return array{leaveType: LeaveType, policy: LeavePolicy}
     */
    private function createLeaveTypeAndPolicy(
        Company $company,
        Department $department,
        Location $location,
        array $leaveTypeOverrides = [],
        array $policyOverrides = [],
    ): array {
        $leaveType = LeaveType::withoutGlobalScopes()->create(array_merge([
            'company_id' => $company->id,
            'code' => 'EL',
            'name' => 'Earned Leave',
            'category' => 'earned',
            'description' => null,
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#0F766E',
            'status' => 'active',
        ], $leaveTypeOverrides));

        $policy = LeavePolicy::withoutGlobalScopes()->create(array_merge([
            'company_id' => $company->id,
            'leave_type_id' => $leaveType->id,
            'version' => 1,
            'scope_key' => hash('sha256', json_encode([
                'leave_type_id' => $leaveType->id,
                'applicable_department_id' => $department->id,
                'applicable_location_id' => $location->id,
                'eligibility_rule' => [
                    'employment_types' => ['full_time'],
                    'employment_statuses' => ['active'],
                    'genders' => [],
                    'marital_statuses' => [],
                    'minimum_tenure_days' => null,
                ],
            ], JSON_THROW_ON_ERROR)),
            'annual_allowance_days' => 12,
            'opening_balance_days' => 0,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 4,
            'encashment_limit_days' => 0,
            'max_consecutive_days' => 10,
            'min_notice_days' => 0,
            'requires_documentation_after_days' => null,
            'applicable_department_id' => $department->id,
            'applicable_location_id' => $location->id,
            'eligibility_rule' => [
                'employment_types' => ['full_time'],
                'employment_statuses' => ['active'],
                'genders' => [],
                'marital_statuses' => [],
                'minimum_tenure_days' => null,
            ],
            'status' => 'active',
        ], $policyOverrides));

        return [
            'leaveType' => $leaveType,
            'policy' => $policy,
        ];
    }

    private function seedLeaveBalance(
        Employee $employee,
        LeaveType $leaveType,
        LeavePolicy $policy,
        User $actor,
        float $availableDays,
    ): LeaveBalance {
        $balance = LeaveBalance::withoutGlobalScopes()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'leave_policy_id' => $policy->id,
            'policy_version' => $policy->version,
            'available_days' => $availableDays,
            'booked_days' => 0,
            'used_days' => 0,
            'accrued_days' => $availableDays,
            'carry_forward_days' => 0,
            'projected_encashable_days' => 0,
            'current_period_start' => '2026-06-01',
            'current_period_end' => '2026-06-30',
            'last_calculation_hash' => hash('sha256', $employee->id.'-'.$leaveType->id.'-'.$availableDays),
            'status' => 'active',
        ]);

        LeaveBalanceEntry::withoutGlobalScopes()->create([
            'company_id' => $employee->company_id,
            'leave_balance_id' => $balance->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'leave_policy_id' => $policy->id,
            'entry_type' => 'accrual',
            'quantity_days' => $availableDays,
            'balance_before_days' => 0,
            'balance_after_days' => $availableDays,
            'effective_on' => '2026-06-01',
            'reference_type' => 'seed',
            'reference_id' => $balance->id,
            'metadata' => ['source' => 'test_seed'],
            'created_by_user_id' => $actor->id,
        ]);

        return $balance;
    }

    private function seedLeaveApprovalWorkflowForCompany(Company $company, User $actor): WorkflowDefinition
    {
        $definition = WorkflowDefinition::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'key' => 'leave-approval',
            ],
            [
                'name' => 'Leave Approval Workflow',
                'module' => 'leave',
                'description' => 'Sequential leave approval through manager and HR.',
                'is_template' => true,
                'status' => 'published',
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ],
        );

        $version = WorkflowVersion::withoutGlobalScopes()->updateOrCreate(
            [
                'workflow_definition_id' => $definition->id,
                'version' => 1,
            ],
            [
                'status' => 'published',
                'definition' => [
                    'module' => 'leave',
                    'stages' => [
                        [
                            'key' => 'manager_approval',
                            'name' => 'Manager Approval',
                            'sequence' => 1,
                            'approver_type' => 'employee_manager',
                            'approver_value' => 'employee_manager',
                            'available_actions' => ['approve', 'reject', 'request_changes'],
                            'sla_hours' => 24,
                        ],
                        [
                            'key' => 'hr_approval',
                            'name' => 'HR Approval',
                            'sequence' => 2,
                            'approver_type' => 'role',
                            'approver_value' => 'hr.admin',
                            'available_actions' => ['approve', 'reject'],
                            'sla_hours' => 24,
                        ],
                    ],
                ],
                'created_by' => $actor->id,
                'published_at' => now(),
            ],
        );

        foreach ([
            [
                'key' => 'manager_approval',
                'name' => 'Manager Approval',
                'sequence' => 1,
                'approver_type' => 'employee_manager',
                'approver_value' => 'employee_manager',
                'available_actions' => ['approve', 'reject', 'request_changes'],
                'sla_hours' => 24,
                'metadata' => [],
            ],
            [
                'key' => 'hr_approval',
                'name' => 'HR Approval',
                'sequence' => 2,
                'approver_type' => 'role',
                'approver_value' => 'hr.admin',
                'available_actions' => ['approve', 'reject'],
                'sla_hours' => 24,
                'metadata' => [],
            ],
        ] as $stageData) {
            WorkflowStage::withoutGlobalScopes()->updateOrCreate(
                [
                    'workflow_version_id' => $version->id,
                    'key' => $stageData['key'],
                ],
                $stageData,
            );
        }

        $definition->forceFill([
            'active_version_id' => $version->id,
        ])->save();

        return $definition->refresh();
    }

    private function nextWeekdayFromNow(int $additionalDays = 0): Carbon
    {
        return Carbon::parse('2026-06-09', 'Asia/Kolkata')
            ->addDays($additionalDays)
            ->startOfDay();
    }
}
