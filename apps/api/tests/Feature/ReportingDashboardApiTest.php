<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\JobRequisition;
use App\Models\KpiDefinition;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Location;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewCycle;
use App\Models\ReportDataset;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportingDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_hr_dashboard_generates_governed_snapshot_and_reuses_cache_within_freshness_window(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $department = Department::factory()->create(['company_id' => $company->id, 'name' => 'Engineering']);
        $designation = Designation::factory()->create(['company_id' => $company->id, 'name' => 'Engineer']);
        $location = Location::factory()->create(['company_id' => $company->id, 'name' => 'Bengaluru']);

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employee_code' => 'EMP-1001',
            'first_name' => 'Aarav',
            'last_name' => 'Singh',
            'employment_status' => 'active',
        ]);

        $attendanceEmployee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employee_code' => 'EMP-1002',
            'first_name' => 'Meera',
            'last_name' => 'Joshi',
            'employment_status' => 'active',
        ]);

        Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'employee_code' => 'EMP-1099',
            'first_name' => 'Rohan',
            'last_name' => 'Past',
            'employment_status' => 'terminated',
        ]);

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $attendanceEmployee->id,
            'attendance_date' => Carbon::today()->toDateString(),
            'worked_minutes' => 390,
            'primary_status' => 'absent',
            'is_late' => false,
            'late_minutes' => 0,
            'overtime_minutes' => 0,
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
            'eligibility_rule' => [],
            'status' => 'active',
        ]);

        LeaveRequest::query()->create([
            'company_id' => $company->id,
            'employee_id' => $attendanceEmployee->id,
            'leave_type_id' => $leaveType->id,
            'leave_policy_id' => $leavePolicy->id,
            'policy_version' => 1,
            'requested_by_user_id' => $hrAdmin->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-15',
            'total_days' => 1,
            'status' => 'pending',
            'reason' => 'Personal work',
            'attendance_sync_status' => 'pending',
        ]);

        Candidate::query()->create([
            'company_id' => $company->id,
            'job_requisition_id' => JobRequisition::query()->create([
                'company_id' => $company->id,
                'requisition_code' => 'REQ-1001',
                'title' => 'Senior Engineer',
                'employment_type' => 'full_time',
                'hiring_type' => 'replacement',
                'priority' => 'high',
                'openings_count' => 1,
                'department_id' => $department->id,
                'designation_id' => $designation->id,
                'location_id' => $location->id,
                'recruiter_user_id' => $hrAdmin->id,
                'hiring_manager_employee_id' => $attendanceEmployee->id,
                'requested_by_user_id' => $hrAdmin->id,
                'status' => 'approved',
                'justification' => 'Backfill for reporting dashboard tests.',
            ])->id,
            'candidate_code' => 'CAN-1001',
            'recruiter_user_id' => $hrAdmin->id,
            'first_name' => 'Sara',
            'last_name' => 'Khan',
            'email' => 'sara.khan@example.com',
            'source' => 'LinkedIn',
            'current_stage' => 'interview',
            'status' => 'active',
            'stage_entered_at' => Carbon::now()->subDays(1),
        ]);

        $this->createKpi($company, 'active_headcount', 'workforce', 'Count active employees');
        $this->createKpi($company, 'attendance_exceptions_today', 'attendance', 'Count today attendance exceptions');
        $this->createKpi($company, 'pending_leave_requests', 'leave', 'Count pending leave requests');
        $this->createKpi($company, 'active_candidates', 'recruitment', 'Count active candidates');

        $this->createDataset($company, 'workforce_headcount_snapshot', 'workforce', 1440, [
            [
                'key' => 'employee_profile',
                'label' => 'Employee profile',
                'target_dataset_key' => 'workforce_headcount_snapshot',
                'description' => 'Employee level drilldown.',
                'allowed_filter_keys' => ['employment_status'],
            ],
        ]);
        $this->createDataset($company, 'attendance_daily_register', 'attendance', 30);
        $this->createDataset($company, 'leave_request_register', 'leave', 120, [
            [
                'key' => 'leave_request_detail',
                'label' => 'Leave request detail',
                'target_dataset_key' => 'leave_request_register',
                'description' => 'Leave request level drilldown.',
                'allowed_filter_keys' => ['status'],
            ],
        ]);
        $this->createDataset($company, 'recruitment_candidate_pipeline', 'recruitment', 180);

        Sanctum::actingAs($hrAdmin);

        $firstResponse = $this->getJson('/api/v1/reporting/dashboards/hr_overview')
            ->assertOk()
            ->assertJsonPath('data.dashboard.key', 'hr_overview')
            ->assertJsonPath('data.snapshot.cache_hit', false)
            ->assertJsonPath('data.freshness.expectation_minutes', 30);

        $firstWidgets = collect($firstResponse->json('data.widgets'))->keyBy('key');

        $this->assertSame(2, $firstWidgets['active_headcount_card']['value']);
        $this->assertSame(1, $firstWidgets['attendance_exceptions_card']['value']);
        $this->assertSame(1, $firstWidgets['pending_leave_requests_card']['value']);
        $this->assertSame(1, $firstWidgets['active_candidates_card']['value']);
        $this->assertSame('active_headcount', $firstWidgets['active_headcount_card']['governance']['kpi']['key']);
        $this->assertSame('workforce_headcount_snapshot', $firstWidgets['active_headcount_card']['governance']['dataset']['key']);
        $this->assertSame('employee_profile', $firstWidgets['active_headcount_card']['drilldown']['key']);

        $snapshotId = $firstResponse->json('data.snapshot.id');

        $this->assertDatabaseHas('dashboard_snapshots', [
            'id' => $snapshotId,
            'company_id' => $company->id,
            'dashboard_key' => 'hr_overview',
            'freshness_expectation_minutes' => 30,
        ]);
        $this->assertDatabaseCount('dashboard_widgets', 4);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'reporting.dashboard.snapshot.generated',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'reporting.dashboard.viewed',
        ]);

        $secondResponse = $this->getJson('/api/v1/reporting/dashboards/hr_overview')
            ->assertOk()
            ->assertJsonPath('data.snapshot.cache_hit', true)
            ->assertJsonPath('data.snapshot.id', $snapshotId)
            ->assertJsonPath('data.freshness.expectation_minutes', 30);

        $this->assertSame(
            $firstResponse->json('data.snapshot.generated_at'),
            $secondResponse->json('data.snapshot.generated_at'),
        );
    }

    public function test_manager_dashboard_applies_team_scope_to_dashboard_aggregates(): void
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

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $reportEmployee->id,
            'attendance_date' => Carbon::today()->toDateString(),
            'worked_minutes' => 410,
            'primary_status' => 'absent',
            'is_late' => false,
            'late_minutes' => 0,
            'overtime_minutes' => 0,
        ]);
        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'employee_id' => $otherEmployee->id,
            'attendance_date' => Carbon::today()->toDateString(),
            'worked_minutes' => 390,
            'primary_status' => 'absent',
            'is_late' => false,
            'late_minutes' => 0,
            'overtime_minutes' => 0,
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
            'eligibility_rule' => [],
            'status' => 'active',
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
            'start_date' => '2026-06-18',
            'end_date' => '2026-06-18',
            'total_days' => 1,
            'status' => 'pending',
            'reason' => 'Family event',
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
            'start_date' => '2026-06-19',
            'end_date' => '2026-06-19',
            'total_days' => 1,
            'status' => 'pending',
            'reason' => 'Travel',
            'attendance_sync_status' => 'pending',
        ]);

        $reviewCycle = PerformanceReviewCycle::query()->create([
            'company_id' => $company->id,
            'code' => 'FY26-H1',
            'name' => 'FY26 H1',
            'cycle_type' => 'semi_annual',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-06-30',
            'self_review_due_on' => '2026-06-15',
            'manager_review_due_on' => '2026-06-25',
            'participant_rules' => ['scope' => 'manager_hierarchy'],
            'review_template' => ['sections' => [['key' => 'delivery', 'weight_percent' => 100]]],
            'competency_visibility' => ['manager_can_view' => true],
            'status' => 'active',
            'created_by_user_id' => $managerUser->id,
            'updated_by_user_id' => $managerUser->id,
        ]);
        PerformanceReview::query()->create([
            'company_id' => $company->id,
            'performance_review_cycle_id' => $reviewCycle->id,
            'employee_id' => $reportEmployee->id,
            'manager_employee_id' => $managerEmployee->id,
            'reviewer_user_ids' => [],
            'goal_snapshot' => [],
            'competency_snapshot' => [],
            'visibility_rules' => ['manager_visible' => true],
            'status' => 'launched',
            'launched_at' => Carbon::now()->subDays(2),
            'reopen_count' => 0,
            'created_by_user_id' => $managerUser->id,
            'updated_by_user_id' => $managerUser->id,
        ]);
        PerformanceReview::query()->create([
            'company_id' => $company->id,
            'performance_review_cycle_id' => $reviewCycle->id,
            'employee_id' => $otherEmployee->id,
            'reviewer_user_ids' => [],
            'goal_snapshot' => [],
            'competency_snapshot' => [],
            'visibility_rules' => ['manager_visible' => true],
            'status' => 'launched',
            'launched_at' => Carbon::now()->subDays(1),
            'reopen_count' => 0,
            'created_by_user_id' => $managerUser->id,
            'updated_by_user_id' => $managerUser->id,
        ]);

        $this->createKpi($company, 'active_headcount', 'workforce', 'Count active employees');
        $this->createKpi($company, 'attendance_exceptions_today', 'attendance', 'Count today attendance exceptions');
        $this->createKpi($company, 'pending_leave_requests', 'leave', 'Count pending leave requests');
        $this->createKpi($company, 'open_performance_reviews', 'performance', 'Count open performance reviews');

        $this->createDataset($company, 'workforce_headcount_snapshot', 'workforce', 1440, [
            [
                'key' => 'employee_profile',
                'label' => 'Employee profile',
                'target_dataset_key' => 'workforce_headcount_snapshot',
                'description' => 'Employee level drilldown.',
                'allowed_filter_keys' => ['employment_status'],
            ],
        ]);
        $this->createDataset($company, 'attendance_daily_register', 'attendance', 30);
        $this->createDataset($company, 'leave_request_register', 'leave', 120, [
            [
                'key' => 'leave_request_detail',
                'label' => 'Leave request detail',
                'target_dataset_key' => 'leave_request_register',
                'description' => 'Leave request level drilldown.',
                'allowed_filter_keys' => ['status'],
            ],
        ]);
        $this->createDataset($company, 'performance_review_status', 'performance', 240);

        Sanctum::actingAs($managerUser);

        $response = $this->getJson('/api/v1/reporting/dashboards/manager_overview')
            ->assertOk()
            ->assertJsonPath('data.dashboard.key', 'manager_overview')
            ->assertJsonPath('data.snapshot.cache_hit', false)
            ->assertJsonPath('data.freshness.expectation_minutes', 30);

        $widgets = collect($response->json('data.widgets'))->keyBy('key');

        $this->assertSame(2, $widgets['team_headcount_card']['value']);
        $this->assertSame(1, $widgets['team_attendance_exceptions_card']['value']);
        $this->assertSame(1, $widgets['team_pending_leave_requests_card']['value']);
        $this->assertSame(1, $widgets['open_team_reviews_card']['value']);
        $this->assertSame('employee_profile', $widgets['team_headcount_card']['drilldown']['key']);
    }

    private function createKpi(Company $company, string $key, string $domain, string $formula): KpiDefinition
    {
        return KpiDefinition::query()->create([
            'company_id' => $company->id,
            'key' => $key,
            'name' => ucwords(str_replace('_', ' ', $key)),
            'domain' => $domain,
            'description' => 'Governed dashboard KPI for Sprint 08 dashboard coverage.',
            'formula' => $formula,
            'source_references' => [
                ['module' => $domain, 'entity' => $key, 'field' => null, 'notes' => 'Dashboard KPI test coverage.'],
            ],
            'grain' => 'daily',
            'certification_status' => 'certified',
            'review_notes' => 'Certified for dashboard coverage.',
            'version' => 1,
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $drilldownPaths
     */
    private function createDataset(Company $company, string $key, string $domain, int $freshnessMinutes, array $drilldownPaths = []): ReportDataset
    {
        $approvedFields = match ($key) {
            'workforce_headcount_snapshot' => ['employee_id', 'employee_code', 'employee_name', 'employment_status'],
            'attendance_daily_register' => ['attendance_record_id', 'employee_id', 'primary_status', 'attendance_date'],
            'leave_request_register' => ['leave_request_id', 'employee_id', 'status', 'start_date'],
            'recruitment_candidate_pipeline' => ['candidate_id', 'candidate_code', 'current_stage', 'status'],
            'performance_review_status' => ['performance_review_id', 'employee_id', 'status', 'launched_at'],
            default => ['id'],
        };

        return ReportDataset::query()->create([
            'company_id' => $company->id,
            'key' => $key,
            'name' => ucwords(str_replace('_', ' ', $key)),
            'domain' => $domain,
            'description' => 'Governed dashboard dataset for Sprint 08 dashboard coverage.',
            'source_references' => [
                ['module' => $domain, 'entity' => $key, 'field' => null, 'notes' => 'Dashboard dataset test coverage.'],
            ],
            'grain' => 'daily',
            'approved_fields' => collect($approvedFields)->map(fn (string $fieldKey): array => [
                'key' => $fieldKey,
                'label' => ucwords(str_replace('_', ' ', $fieldKey)),
                'type' => 'string',
                'description' => 'Approved field for dashboard lineage coverage.',
                'sensitive' => false,
                'masking_strategy' => null,
            ])->values()->all(),
            'approved_filters' => [],
            'drilldown_paths' => $drilldownPaths,
            'masking_posture' => [
                'default_strategy' => 'none',
                'sensitive_field_keys' => [],
                'notes' => 'Dashboard coverage test posture.',
            ],
            'freshness_expectation_minutes' => $freshnessMinutes,
            'certification_status' => 'certified',
            'review_notes' => 'Certified for dashboard coverage.',
            'version' => 1,
        ]);
    }
}
