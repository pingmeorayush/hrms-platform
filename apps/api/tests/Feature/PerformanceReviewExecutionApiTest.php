<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Location;
use App\Models\PerformanceCompetency;
use App\Models\PerformanceGoal;
use App\Models\PerformanceReviewCycle;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PerformanceReviewExecutionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_review_can_progress_from_self_submission_to_publish_with_role_aware_visibility(): void
    {
        CarbonImmutable::setTestNow('2026-08-15 09:00:00');

        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $reviewerUser = User::factory()->create(['company_id' => $company->id]);
        $reviewerUser->assignRole('manager');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);

        $manager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'manager_id' => $manager->id,
            'employment_type' => 'full_time',
            'employment_status' => 'active',
        ]);

        $competency = $this->createCompetency($company->id, $hrAdmin->id);
        $cycle = $this->createReviewCycle($company->id, $hrAdmin->id, $department->id, $designation->id, $competency->id);
        $this->createGoal($company->id, $hrAdmin->id, $cycle->id, $employee->id, $department->id, 'PLAT-FY26-01', 100);

        Sanctum::actingAs($hrAdmin);

        $reviewId = $this->postJson('/api/v1/performance/reviews', [
            'performance_review_cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'reviewer_user_ids' => [$reviewerUser->id],
            'visibility_rules' => [
                'employee_can_view_manager_assessment_before_publish' => false,
                'employee_can_view_peer_feedback_after_publish' => true,
                'peer_feedback_anonymous_to_employee' => true,
                'manager_can_view_peer_feedback' => true,
                'reviewer_can_view_other_reviewer_feedback' => false,
            ],
            'launch_immediately' => true,
        ])->assertCreated()
            ->assertJsonPath('data.status', 'self_assessment')
            ->assertJsonPath('data.goal_snapshot.0.goal_code', 'PLAT-FY26-01')
            ->assertJsonPath('data.competency_snapshot.0.id', $competency->id)
            ->json('data.id');

        Sanctum::actingAs($employeeUser);

        $this->postJson("/api/v1/performance/reviews/{$reviewId}/submit", $this->submissionPayload($competency->id, 4.2))
            ->assertOk()
            ->assertJsonPath('data.status', 'manager_review')
            ->assertJsonCount(1, 'data.submissions')
            ->assertJsonPath('data.submissions.0.role_type', 'self');

        Sanctum::actingAs($reviewerUser);

        $this->postJson("/api/v1/performance/reviews/{$reviewId}/submit", $this->submissionPayload($competency->id, 4.6, 'Peer reviewer summary'))
            ->assertOk()
            ->assertJsonPath('data.status', 'manager_review');

        Sanctum::actingAs($managerUser);

        $this->postJson("/api/v1/performance/reviews/{$reviewId}/submit", $this->submissionPayload($competency->id, 4.8, 'Manager summary'))
            ->assertOk()
            ->assertJsonPath('data.status', 'calibration')
            ->assertJsonCount(3, 'data.submissions');

        Sanctum::actingAs($hrAdmin);

        $this->postJson("/api/v1/performance/reviews/{$reviewId}/calibrate", [
            'overall_rating' => 4.7,
            'summary' => 'Leadership calibration aligned the employee between strong and exceptional.',
            'section_adjustments' => [
                ['key' => 'goals', 'calibrated_rating' => 4.8, 'note' => 'Goal delivery exceeded expectations.'],
                ['key' => 'competencies', 'calibrated_rating' => 4.5, 'note' => 'Competency signal remains strong.'],
            ],
            'competency_adjustments' => [
                ['competency_id' => $competency->id, 'calibrated_rating' => 4.4, 'note' => 'Leadership rubric normalized across teams.'],
            ],
        ])->assertOk()
            ->assertJsonPath('data.status', 'calibration')
            ->assertJsonPath('data.calibration_payload.overall_rating', 4.7);

        $this->postJson("/api/v1/performance/reviews/{$reviewId}/finalize", [
            'final_rating' => 4.7,
            'summary' => 'Final panel agreed on a high-performing rating with strong platform impact.',
            'employee_visible_summary' => 'Strong half with measurable platform improvements and clear growth signal.',
            'recommendation' => 'retain_and_grow',
        ])->assertOk()
            ->assertJsonPath('data.status', 'finalized')
            ->assertJsonPath('data.final_payload.final_rating', 4.7);

        $this->postJson("/api/v1/performance/reviews/{$reviewId}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', 'published');

        Sanctum::actingAs($employeeUser);

        $this->getJson("/api/v1/performance/reviews/{$reviewId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'published')
            ->assertJsonCount(3, 'data.submissions')
            ->assertJsonPath('data.submissions.1.role_type', 'reviewer')
            ->assertJsonPath('data.submissions.1.submitted_by', null)
            ->assertJsonPath('data.submissions.1.is_anonymous_to_current_user', true)
            ->assertJsonPath('data.final_payload.employee_visible_summary', 'Strong half with measurable platform improvements and clear growth signal.');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'performance.review.created',
            'entity_id' => (string) $reviewId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $managerUser->id,
            'event_type' => 'performance.review.submission.submitted',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'performance.review.published',
            'entity_id' => (string) $reviewId,
        ]);
    }

    public function test_review_deadlines_and_visibility_rules_are_enforced(): void
    {
        CarbonImmutable::setTestNow('2026-08-15 09:00:00');

        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        $reviewerUser = User::factory()->create(['company_id' => $company->id]);
        $reviewerUser->assignRole('manager');

        $unassignedReviewer = User::factory()->create(['company_id' => $company->id]);
        $unassignedReviewer->assignRole('manager');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);

        $manager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'manager_id' => $manager->id,
            'employment_type' => 'full_time',
            'employment_status' => 'active',
        ]);

        $competency = $this->createCompetency($company->id, $hrAdmin->id);
        $expiredCycle = $this->createReviewCycle(
            companyId: $company->id,
            actorUserId: $hrAdmin->id,
            departmentId: $department->id,
            designationId: $designation->id,
            competencyId: $competency->id,
            selfReviewDueOn: '2026-08-10',
            managerReviewDueOn: '2026-08-20',
        );
        $this->createGoal($company->id, $hrAdmin->id, $expiredCycle->id, $employee->id, $department->id, 'PLAT-FY26-02', 100);

        Sanctum::actingAs($hrAdmin);

        $expiredReviewId = $this->postJson('/api/v1/performance/reviews', [
            'performance_review_cycle_id' => $expiredCycle->id,
            'employee_id' => $employee->id,
            'reviewer_user_ids' => [],
            'launch_immediately' => true,
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($employeeUser);

        $this->postJson("/api/v1/performance/reviews/{$expiredReviewId}/submit", $this->submissionPayload($competency->id, 4.0))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['review']);

        $activeCycle = $this->createReviewCycle(
            companyId: $company->id,
            actorUserId: $hrAdmin->id,
            departmentId: $department->id,
            designationId: $designation->id,
            competencyId: $competency->id,
            selfReviewDueOn: '2026-08-25',
            managerReviewDueOn: '2026-08-30',
        );
        $this->createGoal($company->id, $hrAdmin->id, $activeCycle->id, $employee->id, $department->id, 'PLAT-FY26-03', 100);

        Sanctum::actingAs($hrAdmin);

        $reviewId = $this->postJson('/api/v1/performance/reviews', [
            'performance_review_cycle_id' => $activeCycle->id,
            'employee_id' => $employee->id,
            'reviewer_user_ids' => [$reviewerUser->id],
            'visibility_rules' => [
                'employee_can_view_manager_assessment_before_publish' => false,
                'employee_can_view_peer_feedback_after_publish' => false,
                'peer_feedback_anonymous_to_employee' => true,
                'manager_can_view_peer_feedback' => true,
                'reviewer_can_view_other_reviewer_feedback' => false,
            ],
            'launch_immediately' => true,
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($employeeUser);
        $this->postJson("/api/v1/performance/reviews/{$reviewId}/submit", $this->submissionPayload($competency->id, 4.1))->assertOk();

        Sanctum::actingAs($reviewerUser);
        $this->postJson("/api/v1/performance/reviews/{$reviewId}/submit", $this->submissionPayload($competency->id, 4.4, 'Peer reviewer feedback'))->assertOk();

        Sanctum::actingAs($managerUser);
        $this->postJson("/api/v1/performance/reviews/{$reviewId}/submit", $this->submissionPayload($competency->id, 4.5, 'Manager feedback'))->assertOk();

        Sanctum::actingAs($employeeUser);
        $this->getJson("/api/v1/performance/reviews/{$reviewId}")
            ->assertOk()
            ->assertJsonCount(1, 'data.submissions')
            ->assertJsonPath('data.submissions.0.role_type', 'self');

        Sanctum::actingAs($unassignedReviewer);
        $this->getJson("/api/v1/performance/reviews/{$reviewId}")
            ->assertNotFound();
    }

    public function test_finalized_review_is_locked_and_reopen_is_controlled_and_audited(): void
    {
        CarbonImmutable::setTestNow('2026-08-15 09:00:00');

        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $managerUser = User::factory()->create(['company_id' => $company->id]);
        $managerUser->assignRole('manager');

        $employeeUser = User::factory()->create(['company_id' => $company->id]);
        $employeeUser->assignRole('employee');

        $outsiderManager = User::factory()->create(['company_id' => $company->id]);
        $outsiderManager->assignRole('manager');

        [$department, $designation, $location] = $this->createOrganizationContext($company->id);

        $manager = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $managerUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
            'manager_id' => $manager->id,
            'employment_type' => 'full_time',
            'employment_status' => 'active',
        ]);

        $competency = $this->createCompetency($company->id, $hrAdmin->id);
        $cycle = $this->createReviewCycle($company->id, $hrAdmin->id, $department->id, $designation->id, $competency->id);
        $this->createGoal($company->id, $hrAdmin->id, $cycle->id, $employee->id, $department->id, 'PLAT-FY26-04', 100);

        Sanctum::actingAs($hrAdmin);
        $reviewId = $this->postJson('/api/v1/performance/reviews', [
            'performance_review_cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'reviewer_user_ids' => [],
            'launch_immediately' => true,
        ])->assertCreated()->json('data.id');

        Sanctum::actingAs($employeeUser);
        $this->postJson("/api/v1/performance/reviews/{$reviewId}/submit", $this->submissionPayload($competency->id, 4.2))->assertOk();

        Sanctum::actingAs($managerUser);
        $this->postJson("/api/v1/performance/reviews/{$reviewId}/submit", $this->submissionPayload($competency->id, 4.6, 'Manager summary'))->assertOk();

        Sanctum::actingAs($hrAdmin);
        $this->postJson("/api/v1/performance/reviews/{$reviewId}/finalize", [
            'final_rating' => 4.4,
            'summary' => 'Final review summary.',
            'employee_visible_summary' => 'Published summary for the employee.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'finalized');

        Sanctum::actingAs($employeeUser);
        $this->postJson("/api/v1/performance/reviews/{$reviewId}/submit", $this->submissionPayload($competency->id, 4.3, 'Updated summary'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['review']);

        Sanctum::actingAs($outsiderManager);
        $this->postJson("/api/v1/performance/reviews/{$reviewId}/reopen", [
            'reason' => 'Should not be allowed.',
        ])->assertForbidden();

        Sanctum::actingAs($hrAdmin);
        $this->postJson("/api/v1/performance/reviews/{$reviewId}/reopen", [
            'reason' => 'Calibration committee requested one more manager clarification pass.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'reopened')
            ->assertJsonPath('data.reopen_count', 1)
            ->assertJsonPath('data.reopened_reason', 'Calibration committee requested one more manager clarification pass.');

        Sanctum::actingAs($employeeUser);
        $this->postJson("/api/v1/performance/reviews/{$reviewId}/submit", $this->submissionPayload($competency->id, 4.5, 'Updated after reopen'))
            ->assertOk()
            ->assertJsonPath('data.status', 'calibration');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'performance.review.reopened',
            'entity_id' => (string) $reviewId,
        ]);
    }

    /**
     * @return array{Department, Designation, Location}
     */
    private function createOrganizationContext(int $companyId): array
    {
        return [
            Department::factory()->create(['company_id' => $companyId]),
            Designation::factory()->create(['company_id' => $companyId]),
            Location::factory()->create(['company_id' => $companyId]),
        ];
    }

    private function createCompetency(int $companyId, int $actorUserId): PerformanceCompetency
    {
        return PerformanceCompetency::query()->create([
            'company_id' => $companyId,
            'code' => 'COLLAB-01',
            'name' => 'Collaboration',
            'category' => 'leadership',
            'description' => 'Measures teamwork and cross-functional collaboration.',
            'scale_definition' => [
                'min_rating' => 1,
                'max_rating' => 5,
                'labels' => [
                    ['value' => 1, 'label' => 'Needs improvement'],
                    ['value' => 3, 'label' => 'Meets expectations'],
                    ['value' => 5, 'label' => 'Role model'],
                ],
            ],
            'status' => 'active',
            'created_by_user_id' => $actorUserId,
            'updated_by_user_id' => $actorUserId,
        ]);
    }

    private function createReviewCycle(
        int $companyId,
        int $actorUserId,
        int $departmentId,
        int $designationId,
        int $competencyId,
        string $selfReviewDueOn = '2026-08-20',
        string $managerReviewDueOn = '2026-08-28',
    ): PerformanceReviewCycle {
        return PerformanceReviewCycle::query()->create([
            'company_id' => $companyId,
            'code' => 'FY26-H2-'.uniqid(),
            'name' => 'FY26 Second Half Review',
            'cycle_type' => 'half_yearly',
            'starts_on' => '2026-07-01',
            'ends_on' => '2026-12-31',
            'self_review_due_on' => $selfReviewDueOn,
            'manager_review_due_on' => $managerReviewDueOn,
            'calibration_starts_on' => '2026-09-01',
            'calibration_ends_on' => '2026-09-10',
            'publish_on' => '2026-09-15',
            'participant_rules' => [
                'population' => [
                    'employment_statuses' => ['active'],
                    'employment_types' => ['full_time'],
                    'department_ids' => [$departmentId],
                    'designation_ids' => [$designationId],
                ],
                'reviewers' => [
                    'self_review_required' => true,
                    'manager_review_required' => true,
                    'peer_reviewer_slots' => 1,
                    'allow_hr_reviewer' => true,
                ],
            ],
            'review_template' => [
                'sections' => [
                    ['key' => 'goals', 'label' => 'Goals', 'weight_percent' => 70, 'required' => true],
                    ['key' => 'competencies', 'label' => 'Competencies', 'weight_percent' => 30, 'required' => true],
                ],
                'rating_scale' => [
                    'min' => 1,
                    'max' => 5,
                ],
            ],
            'competency_visibility' => [
                'enabled' => true,
                'visible_to_employee' => true,
                'visible_to_manager' => true,
                'visible_to_hr' => true,
                'required_competency_ids' => [$competencyId],
            ],
            'status' => 'active',
            'created_by_user_id' => $actorUserId,
            'updated_by_user_id' => $actorUserId,
        ]);
    }

    private function createGoal(
        int $companyId,
        int $actorUserId,
        int $reviewCycleId,
        int $employeeId,
        int $departmentId,
        string $goalCode,
        float $weightPercent,
    ): PerformanceGoal {
        return PerformanceGoal::query()->create([
            'company_id' => $companyId,
            'performance_review_cycle_id' => $reviewCycleId,
            'owner_employee_id' => $employeeId,
            'department_id' => $departmentId,
            'goal_code' => $goalCode,
            'goal_type' => 'library',
            'title' => 'Platform reliability objective',
            'description' => 'Improve resilience of release pipelines and rollback posture.',
            'due_on' => '2026-10-31',
            'weight_percent' => $weightPercent,
            'success_metric' => [
                'measure_type' => 'percentage',
                'target_value' => 99.5,
                'unit' => 'uptime',
            ],
            'status' => 'active',
            'created_by_user_id' => $actorUserId,
            'updated_by_user_id' => $actorUserId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function submissionPayload(int $competencyId, float $overallRating, string $summary = 'Structured review summary'): array
    {
        return [
            'sections' => [
                ['key' => 'goals', 'rating' => $overallRating, 'comment' => 'Goals section comment.'],
                ['key' => 'competencies', 'rating' => $overallRating, 'comment' => 'Competency section comment.'],
            ],
            'competencies' => [
                ['competency_id' => $competencyId, 'rating' => $overallRating, 'comment' => 'Competency observation.'],
            ],
            'overall_rating' => $overallRating,
            'summary' => $summary,
        ];
    }
}
