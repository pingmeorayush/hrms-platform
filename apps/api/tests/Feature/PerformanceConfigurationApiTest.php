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
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PerformanceConfigurationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_hr_admin_can_create_competency_review_cycle_and_goal_configuration(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $designation, $location, $owner] = $this->createEmployeeContext($company);

        Sanctum::actingAs($hrAdmin);

        $competencyId = $this->postJson('/api/v1/performance/competencies', [
            'code' => 'COMM-01',
            'name' => 'Communication',
            'category' => 'leadership',
            'description' => 'Measures written and verbal communication clarity.',
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
        ])->assertCreated()
            ->assertJsonPath('data.code', 'COMM-01')
            ->assertJsonPath('data.scale_definition.max_rating', 5)
            ->json('data.id');

        $reviewCycleId = $this->postJson('/api/v1/performance/review-cycles', [
            'code' => 'FY26-H2',
            'name' => 'FY26 Second Half Review',
            'cycle_type' => 'half_yearly',
            'starts_on' => '2026-07-01',
            'ends_on' => '2026-12-31',
            'self_review_due_on' => '2026-11-30',
            'manager_review_due_on' => '2026-12-15',
            'calibration_starts_on' => '2026-12-18',
            'calibration_ends_on' => '2026-12-24',
            'publish_on' => '2027-01-05',
            'participant_rules' => [
                'population' => [
                    'employment_statuses' => ['active'],
                    'employment_types' => ['full_time'],
                    'department_ids' => [$department->id],
                    'designation_ids' => [$designation->id],
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
            'status' => 'draft',
        ])->assertCreated()
            ->assertJsonPath('data.code', 'FY26-H2')
            ->assertJsonPath('data.competency_visibility.required_competency_ids.0', $competencyId)
            ->assertJsonPath('data.review_template.sections.0.weight_percent', 70)
            ->json('data.id');

        $goalId = $this->postJson('/api/v1/performance/goals', [
            'goal_code' => 'PLAT-H2-01',
            'goal_type' => 'library',
            'title' => 'Improve platform deployment resilience',
            'description' => 'Reduce rollout failure posture through automation and rollback readiness.',
            'performance_review_cycle_id' => $reviewCycleId,
            'owner_employee_id' => $owner->id,
            'department_id' => $department->id,
            'due_on' => '2026-10-31',
            'weight_percent' => 60,
            'success_metric' => [
                'measure_type' => 'percentage',
                'target_value' => 99.9,
                'unit' => 'uptime',
                'notes' => 'Measured across weekly release windows.',
            ],
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('data.goal_code', 'PLAT-H2-01')
            ->assertJsonPath('data.owner_employee.id', $owner->id)
            ->assertJsonPath('data.review_cycle.id', $reviewCycleId)
            ->assertJsonPath('data.weight_percent', 60)
            ->json('data.id');

        $this->getJson('/api/v1/performance/goals?review_cycle_id='.$reviewCycleId)
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $goalId);

        $this->assertDatabaseHas('performance_competencies', [
            'id' => $competencyId,
            'company_id' => $company->id,
            'code' => 'COMM-01',
        ]);

        $this->assertDatabaseHas('performance_review_cycles', [
            'id' => $reviewCycleId,
            'company_id' => $company->id,
            'code' => 'FY26-H2',
        ]);

        $this->assertDatabaseHas('performance_goals', [
            'id' => $goalId,
            'company_id' => $company->id,
            'goal_code' => 'PLAT-H2-01',
            'owner_employee_id' => $owner->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'performance.competency.created',
            'entity_id' => (string) $competencyId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'performance.review_cycle.created',
            'entity_id' => (string) $reviewCycleId,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $hrAdmin->id,
            'event_type' => 'performance.goal.created',
            'entity_id' => (string) $goalId,
        ]);
    }

    public function test_goal_weight_budgets_and_review_template_weights_are_validated(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        [$department, $designation, $location, $owner] = $this->createEmployeeContext($company);

        $reviewCycle = PerformanceReviewCycle::query()->create([
            'company_id' => $company->id,
            'code' => 'FY26-Q4',
            'name' => 'FY26 Quarter 4 Review',
            'cycle_type' => 'quarterly',
            'starts_on' => '2026-10-01',
            'ends_on' => '2026-12-31',
            'self_review_due_on' => '2026-12-10',
            'manager_review_due_on' => '2026-12-20',
            'calibration_starts_on' => null,
            'calibration_ends_on' => null,
            'publish_on' => null,
            'participant_rules' => [
                'population' => [
                    'employment_statuses' => ['active'],
                    'employment_types' => ['full_time'],
                    'department_ids' => [$department->id],
                    'designation_ids' => [$designation->id],
                ],
                'reviewers' => [
                    'self_review_required' => true,
                    'manager_review_required' => true,
                    'peer_reviewer_slots' => 0,
                    'allow_hr_reviewer' => false,
                ],
            ],
            'review_template' => [
                'sections' => [
                    ['key' => 'goals', 'label' => 'Goals', 'weight_percent' => 100, 'required' => true],
                ],
                'rating_scale' => ['min' => 1, 'max' => 5],
            ],
            'competency_visibility' => [
                'enabled' => false,
                'visible_to_employee' => false,
                'visible_to_manager' => false,
                'visible_to_hr' => true,
                'required_competency_ids' => [],
            ],
            'status' => 'draft',
            'created_by_user_id' => $hrAdmin->id,
            'updated_by_user_id' => $hrAdmin->id,
        ]);

        PerformanceGoal::query()->create([
            'company_id' => $company->id,
            'performance_review_cycle_id' => $reviewCycle->id,
            'owner_employee_id' => $owner->id,
            'department_id' => $department->id,
            'goal_code' => 'PLAT-Q4-01',
            'goal_type' => 'library',
            'title' => 'Stabilize deployment orchestration',
            'description' => null,
            'due_on' => '2026-11-30',
            'weight_percent' => 60,
            'success_metric' => null,
            'status' => 'active',
            'created_by_user_id' => $hrAdmin->id,
            'updated_by_user_id' => $hrAdmin->id,
        ]);

        Sanctum::actingAs($hrAdmin);

        $this->postJson('/api/v1/performance/goals', [
            'goal_code' => 'PLAT-Q4-02',
            'goal_type' => 'library',
            'title' => 'Improve observability coverage',
            'performance_review_cycle_id' => $reviewCycle->id,
            'owner_employee_id' => $owner->id,
            'department_id' => $department->id,
            'due_on' => '2026-12-01',
            'weight_percent' => 50,
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['weight_percent']);

        $this->postJson('/api/v1/performance/review-cycles', [
            'code' => 'FY27-H1',
            'name' => 'FY27 First Half Review',
            'cycle_type' => 'half_yearly',
            'starts_on' => '2027-01-01',
            'ends_on' => '2027-06-30',
            'participant_rules' => [
                'population' => [
                    'employment_statuses' => ['active'],
                    'employment_types' => ['full_time'],
                    'department_ids' => [$department->id],
                    'designation_ids' => [$designation->id],
                ],
                'reviewers' => [
                    'self_review_required' => true,
                    'manager_review_required' => true,
                    'peer_reviewer_slots' => 0,
                    'allow_hr_reviewer' => false,
                ],
            ],
            'review_template' => [
                'sections' => [
                    ['key' => 'goals', 'label' => 'Goals', 'weight_percent' => 70, 'required' => true],
                    ['key' => 'competencies', 'label' => 'Competencies', 'weight_percent' => 20, 'required' => true],
                ],
                'rating_scale' => [
                    'min' => 1,
                    'max' => 5,
                ],
            ],
            'competency_visibility' => [
                'enabled' => false,
                'visible_to_employee' => false,
                'visible_to_manager' => false,
                'visible_to_hr' => true,
                'required_competency_ids' => [],
            ],
            'status' => 'draft',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['review_template.sections']);
    }

    public function test_performance_configuration_access_is_permission_aware_and_tenant_scoped(): void
    {
        $company = Company::factory()->create(['status' => 'active']);
        $otherCompany = Company::factory()->create(['status' => 'active']);

        $hrAdmin = User::factory()->create(['company_id' => $company->id]);
        $hrAdmin->assignRole('hr.admin');

        $manager = User::factory()->create(['company_id' => $company->id]);
        $manager->assignRole('manager');

        $recruiter = User::factory()->create(['company_id' => $company->id]);
        $recruiter->assignRole('recruiter');

        $otherManager = User::factory()->create(['company_id' => $otherCompany->id]);
        $otherManager->assignRole('manager');

        [$department, $designation, $location, $owner] = $this->createEmployeeContext($company, $manager->id);
        [$otherDepartment, $otherDesignation, $otherLocation, $otherOwner] = $this->createEmployeeContext($otherCompany, $otherManager->id);

        $competency = PerformanceCompetency::query()->create([
            'company_id' => $company->id,
            'code' => 'EXEC-01',
            'name' => 'Execution',
            'category' => 'delivery',
            'description' => 'Measures follow-through and operating rigor.',
            'scale_definition' => [
                'min_rating' => 1,
                'max_rating' => 5,
                'labels' => [
                    ['value' => 1, 'label' => 'Needs improvement'],
                    ['value' => 5, 'label' => 'Role model'],
                ],
            ],
            'status' => 'active',
            'created_by_user_id' => $hrAdmin->id,
            'updated_by_user_id' => $hrAdmin->id,
        ]);

        $cycle = PerformanceReviewCycle::query()->create([
            'company_id' => $company->id,
            'code' => 'FY26-ANNUAL',
            'name' => 'FY26 Annual Review',
            'cycle_type' => 'annual',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'self_review_due_on' => '2026-11-30',
            'manager_review_due_on' => '2026-12-15',
            'calibration_starts_on' => null,
            'calibration_ends_on' => null,
            'publish_on' => null,
            'participant_rules' => [
                'population' => [
                    'employment_statuses' => ['active'],
                    'employment_types' => ['full_time'],
                    'department_ids' => [$department->id],
                    'designation_ids' => [$designation->id],
                ],
                'reviewers' => [
                    'self_review_required' => true,
                    'manager_review_required' => true,
                    'peer_reviewer_slots' => 0,
                    'allow_hr_reviewer' => true,
                ],
            ],
            'review_template' => [
                'sections' => [
                    ['key' => 'goals', 'label' => 'Goals', 'weight_percent' => 100, 'required' => true],
                ],
                'rating_scale' => ['min' => 1, 'max' => 5],
            ],
            'competency_visibility' => [
                'enabled' => true,
                'visible_to_employee' => true,
                'visible_to_manager' => true,
                'visible_to_hr' => true,
                'required_competency_ids' => [$competency->id],
            ],
            'status' => 'draft',
            'created_by_user_id' => $hrAdmin->id,
            'updated_by_user_id' => $hrAdmin->id,
        ]);

        $goal = PerformanceGoal::query()->create([
            'company_id' => $company->id,
            'performance_review_cycle_id' => $cycle->id,
            'owner_employee_id' => $owner->id,
            'department_id' => $department->id,
            'goal_code' => 'OPS-ANNUAL-01',
            'goal_type' => 'library',
            'title' => 'Drive quarterly operating cadence',
            'description' => null,
            'due_on' => '2026-09-30',
            'weight_percent' => 100,
            'success_metric' => null,
            'status' => 'active',
            'created_by_user_id' => $hrAdmin->id,
            'updated_by_user_id' => $hrAdmin->id,
        ]);

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/performance/competencies')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $competency->id);

        $this->getJson('/api/v1/performance/review-cycles')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $cycle->id);

        $this->getJson('/api/v1/performance/goals')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.id', $goal->id);

        Sanctum::actingAs($recruiter);

        $this->postJson('/api/v1/performance/goals', [
            'goal_code' => 'NOPE-01',
            'goal_type' => 'library',
            'title' => 'Should not be allowed',
            'owner_employee_id' => $owner->id,
            'due_on' => '2026-08-01',
            'weight_percent' => 20,
            'status' => 'draft',
        ])->assertForbidden();

        Sanctum::actingAs($otherManager);

        $this->getJson('/api/v1/performance/goals/'.$goal->id)
            ->assertNotFound();
    }

    /**
     * @return array{Department, Designation, Location, Employee}
     */
    private function createEmployeeContext(Company $company, ?int $userId = null): array
    {
        $department = Department::factory()->create(['company_id' => $company->id]);
        $designation = Designation::factory()->create(['company_id' => $company->id]);
        $location = Location::factory()->create(['company_id' => $company->id]);

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'user_id' => $userId,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'location_id' => $location->id,
        ]);

        return [$department, $designation, $location, $employee];
    }
}
