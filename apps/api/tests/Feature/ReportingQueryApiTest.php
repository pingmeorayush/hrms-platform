<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Location;
use App\Models\ReportDataset;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportingQueryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_hr_admin_can_query_certified_workforce_report_with_filters_sorting_and_drilldowns(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $department = Department::factory()->create(['company_id' => $company->id, 'name' => 'Engineering']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'name' => 'Engineer']);
        $location = Location::factory()->create(['company_id' => $company->id, 'name' => 'Bengaluru']);

        $manager = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employee_code' => 'EMP-2001',
            'first_name' => 'Nisha',
            'last_name' => 'Lead',
            'email' => 'nisha.lead@example.com',
            'employment_status' => 'active',
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'manager_id' => $manager->id,
            'employee_code' => 'EMP-1001',
            'first_name' => 'Aarav',
            'last_name' => 'Singh',
            'email' => 'aarav.singh@example.com',
            'employment_status' => 'active',
            'date_of_joining' => '2025-01-10',
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'manager_id' => $manager->id,
            'employee_code' => 'EMP-1009',
            'first_name' => 'Meera',
            'last_name' => 'Joshi',
            'email' => 'meera.joshi@example.com',
            'employment_status' => 'active',
            'date_of_joining' => '2024-07-12',
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'manager_id' => $manager->id,
            'employee_code' => 'EMP-0999',
            'first_name' => 'Rohan',
            'last_name' => 'Past',
            'employment_status' => 'terminated',
            'date_of_joining' => '2023-02-01',
        ]);

        $this->createReportDataset($company, 'workforce_headcount_snapshot', 'workforce', [
            'employee_id',
            'employee_code',
            'employee_name',
            'employee_email',
            'department_name',
            'designation_name',
            'location_name',
            'manager_name',
            'employment_status',
            'employment_type',
            'date_of_joining',
        ], [
            'employee_code' => ['eq', 'contains', 'in'],
            'employment_status' => ['eq', 'in'],
            'department_name' => ['eq', 'contains', 'in'],
            'date_of_joining' => ['eq', 'gte', 'lte', 'between', 'date_between'],
        ], [
            [
                'key' => 'employee_profile',
                'label' => 'Employee profile',
                'target_dataset_key' => 'workforce_headcount_snapshot',
                'description' => 'Drill into governed employee-level workforce detail.',
                'allowed_filter_keys' => ['employee_code', 'employment_status'],
            ],
        ]);

        Sanctum::actingAs($hrAdmin);

        $response = $this->getJson('/api/v1/reporting/reports/workforce_headcount_snapshot?filters[employment_status]=active&sort_by=employee_code&sort_direction=desc')
            ->assertOk()
            ->assertJsonPath('data.dataset.key', 'workforce_headcount_snapshot')
            ->assertJsonPath('data.meta.total', 3)
            ->assertJsonPath('data.meta.sort_by', 'employee_code')
            ->assertJsonPath('data.items.0.employee_code', 'EMP-2001')
            ->assertJsonPath('data.items.0.employee_email', 'nisha.lead@example.com')
            ->assertJsonPath('data.items.1.employee_code', 'EMP-1009')
            ->assertJsonPath('data.items.2.employee_code', 'EMP-1001')
            ->assertJsonPath('data.items.0.drilldowns.0.key', 'employee_profile')
            ->assertJsonPath('data.visibility.masked_field_keys', [])
            ->assertJsonPath('data.visibility.hidden_field_keys', [])
            ->assertJsonPath('data.visibility.drilldown_keys.0', 'employee_profile');

        $this->assertSame('active', $response->json('data.filters.applied.employment_status.value'));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'reporting.dataset.queried',
        ]);

        /** @var AuditLog $auditLog */
        $auditLog = AuditLog::query()
            ->where('user_id', $hrAdmin->id)
            ->where('event_type', 'reporting.dataset.queried')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame([], $auditLog->metadata['masked_field_keys'] ?? []);
        $this->assertSame(['employee_profile'], $auditLog->metadata['drilldown_keys_returned'] ?? []);
    }

    public function test_manager_leave_report_is_scoped_to_self_and_direct_reports(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $department = Department::factory()->create(['company_id' => $company->id, 'name' => 'Operations']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'name' => 'Manager']);
        $location = Location::factory()->create(['company_id' => $company->id, 'name' => 'Mumbai']);

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
            'employee_code' => 'EMP-M001',
            'first_name' => 'Karan',
            'last_name' => 'Manager',
            'employment_status' => 'active',
        ]);

        $reportEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'manager_id' => $managerEmployee->id,
            'employee_code' => 'EMP-R001',
            'first_name' => 'Diya',
            'last_name' => 'Report',
            'employment_status' => 'active',
        ]);

        $otherEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employee_code' => 'EMP-O001',
            'first_name' => 'Other',
            'last_name' => 'Employee',
            'employment_status' => 'active',
        ]);

        $leaveType = LeaveType::query()->create([
            'company_id' => $company->id,
            'code' => 'CL',
            'name' => 'Casual Leave',
            'category' => 'time_off',
            'description' => 'Casual leave policy',
            'is_paid' => true,
            'requires_approval' => true,
            'allows_half_day' => true,
            'color_token' => '#336699',
            'status' => 'active',
        ]);

        $leavePolicy = LeavePolicy::query()->create([
            'company_id' => $company->id,
            'leave_type_id' => $leaveType->id,
            'version' => 1,
            'scope_key' => 'default',
            'annual_allowance_days' => 12,
            'opening_balance_days' => 0,
            'accrual_frequency' => 'monthly',
            'carry_forward_limit_days' => 5,
            'encashment_limit_days' => 0,
            'max_consecutive_days' => 10,
            'min_notice_days' => 1,
            'requires_documentation_after_days' => null,
            'applicable_department_id' => null,
            'applicable_location_id' => null,
            'eligibility_rule' => [],
            'status' => 'active',
        ]);

        LeaveRequest::query()->create([
            'company_id' => $company->id,
            'employee_id' => $managerEmployee->id,
            'leave_type_id' => $leaveType->id,
            'leave_policy_id' => $leavePolicy->id,
            'policy_version' => 1,
            'requested_by_user_id' => $managerUser->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'start_date' => '2026-06-20',
            'end_date' => '2026-06-20',
            'total_days' => 1,
            'status' => 'approved',
            'reason' => 'Personal work',
            'approver_comment' => 'Approved',
            'is_auto_approved' => false,
            'attendance_sync_status' => 'synced',
        ]);

        LeaveRequest::query()->create([
            'company_id' => $company->id,
            'employee_id' => $reportEmployee->id,
            'leave_type_id' => $leaveType->id,
            'leave_policy_id' => $leavePolicy->id,
            'policy_version' => 1,
            'requested_by_user_id' => $managerUser->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'start_date' => '2026-06-22',
            'end_date' => '2026-06-24',
            'total_days' => 3,
            'status' => 'pending',
            'reason' => 'Family event',
            'approver_comment' => null,
            'is_auto_approved' => false,
            'attendance_sync_status' => 'pending',
        ]);

        LeaveRequest::query()->create([
            'company_id' => $company->id,
            'employee_id' => $otherEmployee->id,
            'leave_type_id' => $leaveType->id,
            'leave_policy_id' => $leavePolicy->id,
            'policy_version' => 1,
            'requested_by_user_id' => $managerUser->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'start_date' => '2026-06-28',
            'end_date' => '2026-06-29',
            'total_days' => 2,
            'status' => 'approved',
            'reason' => 'Travel',
            'approver_comment' => 'Approved',
            'is_auto_approved' => false,
            'attendance_sync_status' => 'synced',
        ]);

        $this->createReportDataset($company, 'leave_request_register', 'leave', [
            'leave_request_id',
            'employee_id',
            'employee_code',
            'employee_name',
            'leave_type_name',
            'department_name',
            'start_date',
            'end_date',
            'total_days',
            'status',
            'attendance_sync_status',
        ], [
            'employee_code' => ['eq', 'contains', 'in'],
            'leave_type_name' => ['eq', 'contains', 'in'],
            'department_name' => ['eq', 'contains', 'in'],
            'start_date' => ['eq', 'gte', 'lte', 'between', 'date_between'],
            'status' => ['eq', 'in'],
        ], [
            [
                'key' => 'leave_request_detail',
                'label' => 'Leave request detail',
                'target_dataset_key' => 'leave_request_register',
                'description' => 'Drill into employee and leave-request context.',
                'allowed_filter_keys' => ['employee_code', 'status'],
            ],
        ]);

        Sanctum::actingAs($managerUser);

        $response = $this->getJson('/api/v1/reporting/reports/leave_request_register?sort_by=start_date&sort_direction=asc')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 2);

        $employeeCodes = collect($response->json('data.items'))->pluck('employee_code')->all();

        $this->assertSame(['EMP-M001', 'EMP-R001'], $employeeCodes);
    }

    public function test_reporting_query_rejects_uncertified_datasets_for_viewers_and_unknown_filters(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $this->createReportDataset($company, 'leave_request_register', 'leave', [
            'leave_request_id',
            'employee_id',
            'employee_code',
            'employee_name',
            'leave_type_name',
            'department_name',
            'start_date',
            'end_date',
            'total_days',
            'status',
            'attendance_sync_status',
        ], [
            'employee_code' => ['eq', 'contains', 'in'],
            'status' => ['eq', 'in'],
        ], [], 'draft');

        Sanctum::actingAs($managerUser);

        $this->getJson('/api/v1/reporting/reports/leave_request_register')
            ->assertForbidden();

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $this->createReportDataset($company, 'workforce_headcount_snapshot', 'workforce', [
            'employee_id',
            'employee_code',
            'employee_name',
            'employee_email',
            'department_name',
            'designation_name',
            'location_name',
            'manager_name',
            'employment_status',
            'employment_type',
            'date_of_joining',
        ], [
            'employee_code' => ['eq', 'contains', 'in'],
            'employment_status' => ['eq', 'in'],
        ], []);

        Sanctum::actingAs($hrAdmin);

        $this->getJson('/api/v1/reporting/reports/workforce_headcount_snapshot?filters[ghost]=value')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['filters']);
    }

    public function test_manager_workforce_report_masks_sensitive_fields_and_audits_visibility_metadata(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $department = Department::factory()->create(['company_id' => $company->id, 'name' => 'Operations']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'name' => 'Team Lead']);
        $location = Location::factory()->create(['company_id' => $company->id, 'name' => 'Pune']);

        $managerEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'user_id' => $managerUser->id,
            'employee_code' => 'EMP-M101',
            'first_name' => 'Ritika',
            'last_name' => 'Manager',
            'email' => 'ritika.manager@example.com',
            'employment_status' => 'active',
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employee_code' => 'EMP-R102',
            'manager_id' => $managerEmployee->id,
            'first_name' => 'Kabir',
            'last_name' => 'Report',
            'email' => 'kabir.report@example.com',
            'employment_status' => 'active',
        ]);

        $this->createReportDataset($company, 'workforce_headcount_snapshot', 'workforce', [
            'employee_id',
            'employee_code',
            'employee_name',
            'employee_email',
            'department_name',
            'designation_name',
            'location_name',
            'manager_name',
            'employment_status',
            'employment_type',
            'date_of_joining',
        ], [
            'employee_code' => ['eq', 'contains', 'in'],
            'employment_status' => ['eq', 'in'],
        ], [
            [
                'key' => 'employee_profile',
                'label' => 'Employee profile',
                'target_dataset_key' => 'workforce_headcount_snapshot',
                'description' => 'Drill into governed employee-level workforce detail.',
                'allowed_filter_keys' => ['employee_code', 'employment_status'],
            ],
        ]);

        Sanctum::actingAs($managerUser);

        $response = $this->getJson('/api/v1/reporting/reports/workforce_headcount_snapshot?sort_by=employee_code&sort_direction=asc')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 2)
            ->assertJsonPath('data.visibility.masked_field_keys.0', 'employee_email')
            ->assertJsonPath('data.visibility.hidden_field_keys', [])
            ->assertJsonPath('data.visibility.drilldown_keys.0', 'employee_profile');

        $maskedEmails = collect($response->json('data.items'))->pluck('employee_email')->all();

        $this->assertNotContains('ritika.manager@example.com', $maskedEmails);
        $this->assertNotContains('kabir.report@example.com', $maskedEmails);
        $this->assertSame('r', mb_substr($maskedEmails[0], 0, 1));
        $this->assertStringEndsWith('@example.com', $maskedEmails[0]);
        $this->assertSame('k', mb_substr($maskedEmails[1], 0, 1));
        $this->assertStringEndsWith('@example.com', $maskedEmails[1]);

        /** @var AuditLog $auditLog */
        $auditLog = AuditLog::query()
            ->where('user_id', $managerUser->id)
            ->where('event_type', 'reporting.dataset.queried')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(['employee_email'], $auditLog->metadata['masked_field_keys'] ?? []);
        $this->assertSame([], $auditLog->metadata['hidden_field_keys'] ?? []);
        $this->assertSame(['employee_profile'], $auditLog->metadata['drilldown_keys_returned'] ?? []);
    }

    /**
     * @param  list<string>  $approvedFieldKeys
     * @param  array<string, list<string>>  $approvedFilters
     * @param  list<array<string, mixed>>  $drilldownPaths
     */
    private function createReportDataset(
        Company $company,
        string $key,
        string $domain,
        array $approvedFieldKeys,
        array $approvedFilters,
        array $drilldownPaths,
        string $certificationStatus = 'certified',
    ): ReportDataset {
        return ReportDataset::query()->create([
            'company_id' => $company->id,
            'key' => $key,
            'name' => ucwords(str_replace('_', ' ', $key)),
            'domain' => $domain,
            'description' => 'Governed reporting dataset for automated query coverage.',
            'source_references' => [
                ['module' => $domain, 'entity' => $key, 'field' => null, 'notes' => 'Test dataset registry entry.'],
            ],
            'grain' => 'daily',
            'approved_fields' => collect($approvedFieldKeys)->map(fn (string $fieldKey): array => [
                'key' => $fieldKey,
                'label' => ucwords(str_replace('_', ' ', $fieldKey)),
                'type' => str_contains($fieldKey, 'date') || str_ends_with($fieldKey, '_at') ? 'date' : 'string',
                'description' => 'Approved field for reporting query coverage.',
                'sensitive' => $fieldKey === 'employee_email',
                'masking_strategy' => $fieldKey === 'employee_email' ? 'partial' : null,
            ])->values()->all(),
            'approved_filters' => collect($approvedFilters)->map(
                fn (array $operators, string $filterKey): array => [
                    'key' => $filterKey,
                    'label' => ucwords(str_replace('_', ' ', $filterKey)),
                    'type' => str_contains($filterKey, 'date') || str_ends_with($filterKey, '_at') ? 'date' : 'string',
                    'required' => false,
                    'operators' => $operators,
                ],
            )->values()->all(),
            'drilldown_paths' => $drilldownPaths,
            'masking_posture' => [
                'default_strategy' => 'none',
                'sensitive_field_keys' => in_array('employee_email', $approvedFieldKeys, true) ? ['employee_email'] : [],
                'notes' => 'Default test masking posture.',
            ],
            'freshness_expectation_minutes' => 1440,
            'certification_status' => $certificationStatus,
            'review_notes' => 'Automated test dataset.',
            'owner_user_id' => null,
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'certified_by_user_id' => null,
            'certified_at' => null,
            'version' => 1,
            'created_by_user_id' => null,
            'updated_by_user_id' => null,
        ]);
    }
}
