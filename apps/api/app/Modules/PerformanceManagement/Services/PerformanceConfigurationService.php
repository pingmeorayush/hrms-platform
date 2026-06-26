<?php

namespace App\Modules\PerformanceManagement\Services;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\PerformanceCompetency;
use App\Models\PerformanceGoal;
use App\Models\PerformanceReviewCycle;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type PerformanceGoalFilters array{
 *   status?: string,
 *   owner_employee_id?: int|string,
 *   review_cycle_id?: int|string,
 *   department_id?: int|string,
 *   q?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type PerformanceCompetencyFilters array{
 *   category?: string,
 *   status?: string,
 *   q?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type PerformanceReviewCycleFilters array{
 *   cycle_type?: string,
 *   status?: string,
 *   q?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type SuccessMetricPayload array{
 *   measure_type?: mixed,
 *   target_value?: mixed,
 *   unit?: mixed,
 *   notes?: mixed
 * }
 * @phpstan-type SuccessMetricData array{
 *   measure_type: string|null,
 *   target_value: mixed,
 *   unit: string|null,
 *   notes: string|null
 * }
 * @phpstan-type GoalPayloadInput array{
 *   goal_code: string,
 *   goal_type: string,
 *   title: string,
 *   description?: string|null,
 *   performance_review_cycle_id?: int|string|null,
 *   owner_employee_id: int|string,
 *   department_id?: int|string|null,
 *   due_on: string,
 *   weight_percent: int|float|string,
 *   success_metric?: SuccessMetricPayload|null,
 *   status: string
 * }
 * @phpstan-type GoalPayloadUpdate array{
 *   goal_code?: string,
 *   goal_type?: string,
 *   title?: string,
 *   description?: string|null,
 *   performance_review_cycle_id?: int|string|null,
 *   owner_employee_id?: int|string,
 *   department_id?: int|string|null,
 *   due_on?: string,
 *   weight_percent?: int|float|string,
 *   success_metric?: SuccessMetricPayload|null,
 *   status?: string
 * }
 * @phpstan-type GoalPayloadData array{
 *   performance_review_cycle_id: int|null,
 *   owner_employee_id: int,
 *   department_id: int|null,
 *   goal_code: string,
 *   goal_type: string,
 *   title: string,
 *   description: string|null,
 *   due_on: string,
 *   weight_percent: float,
 *   success_metric: SuccessMetricData|null,
 *   status: string
 * }
 * @phpstan-type ScaleLabelPayload array{
 *   value: int|string,
 *   label: string
 * }
 * @phpstan-type ScaleLabel array{
 *   value: int,
 *   label: string
 * }
 * @phpstan-type ScaleDefinitionPayload array{
 *   min_rating: int|string,
 *   max_rating: int|string,
 *   labels: list<ScaleLabelPayload>
 * }
 * @phpstan-type ScaleDefinition array{
 *   min_rating: int,
 *   max_rating: int,
 *   labels: list<ScaleLabel>
 * }
 * @phpstan-type CompetencyPayloadInput array{
 *   code: string,
 *   name: string,
 *   category: string,
 *   description?: string|null,
 *   scale_definition: ScaleDefinitionPayload,
 *   status: string
 * }
 * @phpstan-type CompetencyPayloadUpdate array{
 *   code?: string,
 *   name?: string,
 *   category?: string,
 *   description?: string|null,
 *   scale_definition?: ScaleDefinitionPayload,
 *   status?: string
 * }
 * @phpstan-type CompetencyPayloadData array{
 *   code: string,
 *   name: string,
 *   category: string,
 *   description: string|null,
 *   scale_definition: ScaleDefinition,
 *   status: string
 * }
 * @phpstan-type ParticipantPopulationPayload array{
 *   employment_statuses?: list<mixed>,
 *   employment_types?: list<mixed>,
 *   department_ids?: list<mixed>,
 *   designation_ids?: list<mixed>
 * }
 * @phpstan-type ParticipantPopulation array{
 *   employment_statuses: list<string>,
 *   employment_types: list<string>,
 *   department_ids: list<int>,
 *   designation_ids: list<int>
 * }
 * @phpstan-type ReviewerRulesPayload array{
 *   self_review_required?: bool|int|string,
 *   manager_review_required?: bool|int|string,
 *   peer_reviewer_slots?: int|string|null,
 *   allow_hr_reviewer?: bool|int|string
 * }
 * @phpstan-type ReviewerRules array{
 *   self_review_required: bool,
 *   manager_review_required: bool,
 *   peer_reviewer_slots: int,
 *   allow_hr_reviewer: bool
 * }
 * @phpstan-type ParticipantRulesPayload array{
 *   population: ParticipantPopulationPayload,
 *   reviewers: ReviewerRulesPayload
 * }
 * @phpstan-type ParticipantRules array{
 *   population: ParticipantPopulation,
 *   reviewers: ReviewerRules
 * }
 * @phpstan-type ReviewTemplateSectionPayload array{
 *   key: string,
 *   label: string,
 *   weight_percent: int|float|string,
 *   required: bool|int|string
 * }
 * @phpstan-type ReviewTemplateSection array{
 *   key: string,
 *   label: string,
 *   weight_percent: float,
 *   required: bool
 * }
 * @phpstan-type RatingScalePayload array{
 *   min: int|string,
 *   max: int|string
 * }
 * @phpstan-type RatingScale array{
 *   min: int,
 *   max: int
 * }
 * @phpstan-type ReviewTemplatePayload array{
 *   sections: list<ReviewTemplateSectionPayload>,
 *   rating_scale: RatingScalePayload
 * }
 * @phpstan-type ReviewTemplate array{
 *   sections: list<ReviewTemplateSection>,
 *   rating_scale: RatingScale
 * }
 * @phpstan-type CompetencyVisibilityPayload array{
 *   enabled?: bool|int|string,
 *   visible_to_employee?: bool|int|string,
 *   visible_to_manager?: bool|int|string,
 *   visible_to_hr?: bool|int|string,
 *   required_competency_ids?: list<mixed>
 * }
 * @phpstan-type CompetencyVisibility array{
 *   enabled: bool,
 *   visible_to_employee: bool,
 *   visible_to_manager: bool,
 *   visible_to_hr: bool,
 *   required_competency_ids: list<int>
 * }
 * @phpstan-type ReviewCyclePayloadInput array{
 *   code: string,
 *   name: string,
 *   cycle_type: string,
 *   starts_on: string,
 *   ends_on: string,
 *   self_review_due_on?: string|null,
 *   manager_review_due_on?: string|null,
 *   calibration_starts_on?: string|null,
 *   calibration_ends_on?: string|null,
 *   publish_on?: string|null,
 *   participant_rules: ParticipantRulesPayload,
 *   review_template: ReviewTemplatePayload,
 *   competency_visibility: CompetencyVisibilityPayload,
 *   status: string
 * }
 * @phpstan-type ReviewCyclePayloadUpdate array{
 *   code?: string,
 *   name?: string,
 *   cycle_type?: string,
 *   starts_on?: string,
 *   ends_on?: string,
 *   self_review_due_on?: string|null,
 *   manager_review_due_on?: string|null,
 *   calibration_starts_on?: string|null,
 *   calibration_ends_on?: string|null,
 *   publish_on?: string|null,
 *   participant_rules?: ParticipantRulesPayload,
 *   review_template?: ReviewTemplatePayload,
 *   competency_visibility?: CompetencyVisibilityPayload,
 *   status?: string
 * }
 * @phpstan-type ReviewCyclePayloadData array{
 *   code: string,
 *   name: string,
 *   cycle_type: string,
 *   starts_on: string,
 *   ends_on: string,
 *   self_review_due_on: string|null,
 *   manager_review_due_on: string|null,
 *   calibration_starts_on: string|null,
 *   calibration_ends_on: string|null,
 *   publish_on: string|null,
 *   participant_rules: ParticipantRules,
 *   review_template: ReviewTemplate,
 *   competency_visibility: CompetencyVisibility,
 *   status: string
 * }
 */
class PerformanceConfigurationService
{
    public function __construct(
        private readonly PerformanceAccessScopeService $accessScopeService,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  PerformanceGoalFilters  $filters
     * @return LengthAwarePaginator<int, PerformanceGoal>
     */
    public function searchGoals(User $actor, array $filters): LengthAwarePaginator
    {
        return $this->accessScopeService
            ->goalsQuery($actor)
            ->when(array_key_exists('status', $filters), fn (Builder $builder) => $builder->where('status', $filters['status']))
            ->when(array_key_exists('owner_employee_id', $filters), fn (Builder $builder) => $builder->where('owner_employee_id', $filters['owner_employee_id']))
            ->when(array_key_exists('review_cycle_id', $filters), fn (Builder $builder) => $builder->where('performance_review_cycle_id', $filters['review_cycle_id']))
            ->when(array_key_exists('department_id', $filters), fn (Builder $builder) => $builder->where('department_id', $filters['department_id']))
            ->when(
                array_key_exists('q', $filters),
                function (Builder $builder) use ($filters): void {
                    $query = trim((string) $filters['q']);
                    $builder->where(function (Builder $nested) use ($query): void {
                        $nested
                            ->where('goal_code', 'like', '%'.$query.'%')
                            ->orWhere('title', 'like', '%'.$query.'%')
                            ->orWhere('description', 'like', '%'.$query.'%');
                    });
                },
            )
            ->orderBy('due_on')
            ->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    /**
     * @param  PerformanceCompetencyFilters  $filters
     * @return LengthAwarePaginator<int, PerformanceCompetency>
     */
    public function searchCompetencies(User $actor, array $filters): LengthAwarePaginator
    {
        return $this->accessScopeService
            ->competenciesQuery($actor)
            ->when(array_key_exists('category', $filters), fn (Builder $builder) => $builder->where('category', $filters['category']))
            ->when(array_key_exists('status', $filters), fn (Builder $builder) => $builder->where('status', $filters['status']))
            ->when(
                array_key_exists('q', $filters),
                function (Builder $builder) use ($filters): void {
                    $query = trim((string) $filters['q']);
                    $builder->where(function (Builder $nested) use ($query): void {
                        $nested
                            ->where('code', 'like', '%'.$query.'%')
                            ->orWhere('name', 'like', '%'.$query.'%')
                            ->orWhere('description', 'like', '%'.$query.'%');
                    });
                },
            )
            ->orderBy('category')
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    /**
     * @param  PerformanceReviewCycleFilters  $filters
     * @return LengthAwarePaginator<int, PerformanceReviewCycle>
     */
    public function searchReviewCycles(User $actor, array $filters): LengthAwarePaginator
    {
        return $this->accessScopeService
            ->reviewCyclesQuery($actor)
            ->when(array_key_exists('cycle_type', $filters), fn (Builder $builder) => $builder->where('cycle_type', $filters['cycle_type']))
            ->when(array_key_exists('status', $filters), fn (Builder $builder) => $builder->where('status', $filters['status']))
            ->when(
                array_key_exists('q', $filters),
                function (Builder $builder) use ($filters): void {
                    $query = trim((string) $filters['q']);
                    $builder->where(function (Builder $nested) use ($query): void {
                        $nested
                            ->where('code', 'like', '%'.$query.'%')
                            ->orWhere('name', 'like', '%'.$query.'%');
                    });
                },
            )
            ->orderByDesc('starts_on')
            ->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findGoalForView(User $actor, int $goalId): PerformanceGoal
    {
        return $this->accessScopeService->resolveAccessibleGoal($actor, $goalId);
    }

    public function findCompetencyForView(User $actor, int $competencyId): PerformanceCompetency
    {
        return $this->accessScopeService->resolveAccessibleCompetency($actor, $competencyId);
    }

    public function findReviewCycleForView(User $actor, int $reviewCycleId): PerformanceReviewCycle
    {
        return $this->accessScopeService->resolveAccessibleReviewCycle($actor, $reviewCycleId);
    }

    /**
     * @param  GoalPayloadInput  $payload
     */
    public function createGoal(User $actor, array $payload): PerformanceGoal
    {
        return DB::transaction(function () use ($actor, $payload): PerformanceGoal {
            $payload = $this->normalizeGoalPayload($actor, $payload);
            $this->ensureGoalCodeUnique($actor->company_id, $payload['goal_code']);
            $this->ensureGoalWeightBudget(
                actor: $actor,
                ownerEmployeeId: $payload['owner_employee_id'],
                reviewCycleId: $payload['performance_review_cycle_id'],
                weightPercent: $payload['weight_percent'],
            );

            $goal = PerformanceGoal::query()->create([
                ...$payload,
                'company_id' => $actor->company_id,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'performance.goal.created',
                actor: $actor,
                metadata: $goal->only([
                    'goal_code',
                    'goal_type',
                    'performance_review_cycle_id',
                    'owner_employee_id',
                    'department_id',
                    'title',
                    'due_on',
                    'weight_percent',
                    'status',
                ]),
                entityType: 'performance_goal',
                entityId: (string) $goal->id,
            );

            return $this->accessScopeService->resolveAccessibleGoal($actor, $goal->id);
        });
    }

    /**
     * @param  GoalPayloadUpdate  $payload
     */
    public function updateGoal(User $actor, PerformanceGoal $goal, array $payload): PerformanceGoal
    {
        return DB::transaction(function () use ($actor, $goal, $payload): PerformanceGoal {
            $before = $goal->only([
                'goal_code',
                'goal_type',
                'performance_review_cycle_id',
                'owner_employee_id',
                'department_id',
                'title',
                'description',
                'due_on',
                'weight_percent',
                'status',
            ]);

            $payload = $this->normalizeGoalPayload($actor, array_merge($goal->toArray(), $payload));
            $this->ensureGoalCodeUnique($actor->company_id, $payload['goal_code'], $goal->id);
            $this->ensureGoalWeightBudget(
                actor: $actor,
                ownerEmployeeId: $payload['owner_employee_id'],
                reviewCycleId: $payload['performance_review_cycle_id'],
                weightPercent: $payload['weight_percent'],
                ignoreGoalId: $goal->id,
            );

            $goal->fill([
                ...$payload,
                'updated_by_user_id' => $actor->id,
            ]);
            $goal->save();

            $this->auditLogger->record(
                eventType: 'performance.goal.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $goal->only([
                        'goal_code',
                        'goal_type',
                        'performance_review_cycle_id',
                        'owner_employee_id',
                        'department_id',
                        'title',
                        'description',
                        'due_on',
                        'weight_percent',
                        'status',
                    ]),
                ],
                entityType: 'performance_goal',
                entityId: (string) $goal->id,
            );

            return $this->accessScopeService->resolveAccessibleGoal($actor, $goal->id);
        });
    }

    /**
     * @param  CompetencyPayloadInput  $payload
     */
    public function createCompetency(User $actor, array $payload): PerformanceCompetency
    {
        return DB::transaction(function () use ($actor, $payload): PerformanceCompetency {
            $payload = $this->normalizeCompetencyPayload($payload);
            $this->ensureCompetencyCodeUnique($actor->company_id, $payload['code']);

            $competency = PerformanceCompetency::query()->create([
                ...$payload,
                'company_id' => $actor->company_id,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'performance.competency.created',
                actor: $actor,
                metadata: $competency->only([
                    'code',
                    'name',
                    'category',
                    'status',
                ]),
                entityType: 'performance_competency',
                entityId: (string) $competency->id,
            );

            return $competency->refresh();
        });
    }

    /**
     * @param  CompetencyPayloadUpdate  $payload
     */
    public function updateCompetency(User $actor, PerformanceCompetency $competency, array $payload): PerformanceCompetency
    {
        return DB::transaction(function () use ($actor, $competency, $payload): PerformanceCompetency {
            $before = $competency->only([
                'code',
                'name',
                'category',
                'description',
                'scale_definition',
                'status',
            ]);

            $payload = $this->normalizeCompetencyPayload(array_merge($competency->toArray(), $payload));
            $this->ensureCompetencyCodeUnique($actor->company_id, $payload['code'], $competency->id);

            $competency->fill([
                ...$payload,
                'updated_by_user_id' => $actor->id,
            ]);
            $competency->save();

            $this->auditLogger->record(
                eventType: 'performance.competency.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $competency->only([
                        'code',
                        'name',
                        'category',
                        'description',
                        'scale_definition',
                        'status',
                    ]),
                ],
                entityType: 'performance_competency',
                entityId: (string) $competency->id,
            );

            return $competency->refresh();
        });
    }

    /**
     * @param  ReviewCyclePayloadInput  $payload
     */
    public function createReviewCycle(User $actor, array $payload): PerformanceReviewCycle
    {
        return DB::transaction(function () use ($actor, $payload): PerformanceReviewCycle {
            $payload = $this->normalizeReviewCyclePayload($actor, $payload);
            $this->ensureReviewCycleCodeUnique($actor->company_id, $payload['code']);

            $cycle = PerformanceReviewCycle::query()->create([
                ...$payload,
                'company_id' => $actor->company_id,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'performance.review_cycle.created',
                actor: $actor,
                metadata: $cycle->only([
                    'code',
                    'name',
                    'cycle_type',
                    'starts_on',
                    'ends_on',
                    'status',
                ]),
                entityType: 'performance_review_cycle',
                entityId: (string) $cycle->id,
            );

            return $this->accessScopeService->resolveAccessibleReviewCycle($actor, $cycle->id);
        });
    }

    /**
     * @param  ReviewCyclePayloadUpdate  $payload
     */
    public function updateReviewCycle(User $actor, PerformanceReviewCycle $cycle, array $payload): PerformanceReviewCycle
    {
        return DB::transaction(function () use ($actor, $cycle, $payload): PerformanceReviewCycle {
            if (in_array($cycle->status, ['finalized', 'published'], true)) {
                throw ValidationException::withMessages([
                    'review_cycle' => ['Finalized or published review cycles cannot have their configuration edited directly.'],
                ]);
            }

            $before = $cycle->only([
                'code',
                'name',
                'cycle_type',
                'starts_on',
                'ends_on',
                'self_review_due_on',
                'manager_review_due_on',
                'calibration_starts_on',
                'calibration_ends_on',
                'publish_on',
                'participant_rules',
                'review_template',
                'competency_visibility',
                'status',
            ]);

            $payload = $this->normalizeReviewCyclePayload($actor, array_merge($cycle->toArray(), $payload));
            $this->ensureReviewCycleCodeUnique($actor->company_id, $payload['code'], $cycle->id);

            $cycle->fill([
                ...$payload,
                'updated_by_user_id' => $actor->id,
            ]);
            $cycle->save();

            $this->auditLogger->record(
                eventType: 'performance.review_cycle.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $cycle->only([
                        'code',
                        'name',
                        'cycle_type',
                        'starts_on',
                        'ends_on',
                        'self_review_due_on',
                        'manager_review_due_on',
                        'calibration_starts_on',
                        'calibration_ends_on',
                        'publish_on',
                        'participant_rules',
                        'review_template',
                        'competency_visibility',
                        'status',
                    ]),
                ],
                entityType: 'performance_review_cycle',
                entityId: (string) $cycle->id,
            );

            return $this->accessScopeService->resolveAccessibleReviewCycle($actor, $cycle->id);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return GoalPayloadData
     */
    private function normalizeGoalPayload(User $actor, array $payload): array
    {
        $reviewCycleId = $payload['performance_review_cycle_id'] ?? null;
        $ownerEmployeeId = (int) $payload['owner_employee_id'];
        $departmentId = $payload['department_id'] ?? null;

        $this->ensureEmployeeBelongsToCompany($actor, $ownerEmployeeId);
        $this->ensureDepartmentBelongsToCompany($actor, $departmentId);

        if ($reviewCycleId !== null) {
            $reviewCycle = $this->accessScopeService->resolveAccessibleReviewCycle($actor, (int) $reviewCycleId);
            $dueOn = $payload['due_on'];

            if ($dueOn < $reviewCycle->starts_on->toDateString() || $dueOn > $reviewCycle->ends_on->toDateString()) {
                throw ValidationException::withMessages([
                    'due_on' => ['Goal deadlines tied to a review cycle must fall within that cycle window.'],
                ]);
            }
        }

        return [
            'performance_review_cycle_id' => $reviewCycleId !== null ? (int) $reviewCycleId : null,
            'owner_employee_id' => $ownerEmployeeId,
            'department_id' => $departmentId !== null ? (int) $departmentId : null,
            'goal_code' => strtoupper(trim((string) $payload['goal_code'])),
            'goal_type' => $payload['goal_type'],
            'title' => trim((string) $payload['title']),
            'description' => filled($payload['description'] ?? null) ? trim((string) $payload['description']) : null,
            'due_on' => $payload['due_on'],
            'weight_percent' => round((float) $payload['weight_percent'], 2),
            'success_metric' => $this->normalizeSuccessMetric($payload['success_metric'] ?? null),
            'status' => $payload['status'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return CompetencyPayloadData
     */
    private function normalizeCompetencyPayload(array $payload): array
    {
        return [
            'code' => strtoupper(trim((string) $payload['code'])),
            'name' => trim((string) $payload['name']),
            'category' => trim((string) $payload['category']),
            'description' => filled($payload['description'] ?? null) ? trim((string) $payload['description']) : null,
            'scale_definition' => $this->normalizeScaleDefinition($payload['scale_definition']),
            'status' => $payload['status'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return ReviewCyclePayloadData
     */
    private function normalizeReviewCyclePayload(User $actor, array $payload): array
    {
        $participantRules = $this->normalizeParticipantRules($actor, $payload['participant_rules']);
        $reviewTemplate = $this->normalizeReviewTemplate($payload['review_template']);
        $competencyVisibility = $this->normalizeCompetencyVisibility($actor, $payload['competency_visibility']);

        return [
            'code' => strtoupper(trim((string) $payload['code'])),
            'name' => trim((string) $payload['name']),
            'cycle_type' => $payload['cycle_type'],
            'starts_on' => $payload['starts_on'],
            'ends_on' => $payload['ends_on'],
            'self_review_due_on' => $payload['self_review_due_on'] ?? null,
            'manager_review_due_on' => $payload['manager_review_due_on'] ?? null,
            'calibration_starts_on' => $payload['calibration_starts_on'] ?? null,
            'calibration_ends_on' => $payload['calibration_ends_on'] ?? null,
            'publish_on' => $payload['publish_on'] ?? null,
            'participant_rules' => $participantRules,
            'review_template' => $reviewTemplate,
            'competency_visibility' => $competencyVisibility,
            'status' => $payload['status'],
        ];
    }

    /**
     * @param  SuccessMetricPayload|null  $payload
     * @return SuccessMetricData|null
     */
    private function normalizeSuccessMetric(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        return [
            'measure_type' => filled($payload['measure_type'] ?? null) ? trim((string) $payload['measure_type']) : null,
            'target_value' => $payload['target_value'] ?? null,
            'unit' => filled($payload['unit'] ?? null) ? trim((string) $payload['unit']) : null,
            'notes' => filled($payload['notes'] ?? null) ? trim((string) $payload['notes']) : null,
        ];
    }

    /**
     * @param  ScaleDefinitionPayload  $payload
     * @return ScaleDefinition
     */
    private function normalizeScaleDefinition(array $payload): array
    {
        $labels = array_map(
            static fn (array $label): array => [
                'value' => (int) $label['value'],
                'label' => trim((string) $label['label']),
            ],
            $payload['labels'],
        );

        usort($labels, static fn (array $left, array $right): int => $left['value'] <=> $right['value']);

        return [
            'min_rating' => (int) $payload['min_rating'],
            'max_rating' => (int) $payload['max_rating'],
            'labels' => $labels,
        ];
    }

    /**
     * @param  ParticipantRulesPayload|array<string, mixed>  $payload
     * @return ParticipantRules
     */
    private function normalizeParticipantRules(User $actor, array $payload): array
    {
        $departmentIds = $this->normalizeIntegerArray($payload['population']['department_ids'] ?? []);
        $designationIds = $this->normalizeIntegerArray($payload['population']['designation_ids'] ?? []);

        $this->ensureDepartmentIdsBelongToCompany($actor, $departmentIds);
        $this->ensureDesignationIdsBelongToCompany($actor, $designationIds);

        $reviewers = [
            'self_review_required' => (bool) ($payload['reviewers']['self_review_required'] ?? false),
            'manager_review_required' => (bool) ($payload['reviewers']['manager_review_required'] ?? false),
            'peer_reviewer_slots' => (int) ($payload['reviewers']['peer_reviewer_slots'] ?? 0),
            'allow_hr_reviewer' => (bool) ($payload['reviewers']['allow_hr_reviewer'] ?? false),
        ];

        if (! $reviewers['self_review_required'] && ! $reviewers['manager_review_required'] && $reviewers['peer_reviewer_slots'] === 0) {
            throw ValidationException::withMessages([
                'participant_rules.reviewers' => ['At least one reviewer mode must be enabled on the review cycle.'],
            ]);
        }

        return [
            'population' => [
                'employment_statuses' => $this->normalizeStringArray($payload['population']['employment_statuses'] ?? []),
                'employment_types' => $this->normalizeStringArray($payload['population']['employment_types'] ?? []),
                'department_ids' => $departmentIds,
                'designation_ids' => $designationIds,
            ],
            'reviewers' => $reviewers,
        ];
    }

    /**
     * @param  ReviewTemplatePayload|array<string, mixed>  $payload
     * @return ReviewTemplate
     */
    private function normalizeReviewTemplate(array $payload): array
    {
        $sections = array_map(
            static fn (array $section): array => [
                'key' => trim((string) $section['key']),
                'label' => trim((string) $section['label']),
                'weight_percent' => round((float) $section['weight_percent'], 2),
                'required' => (bool) $section['required'],
            ],
            $payload['sections'],
        );

        $totalWeight = round(array_reduce(
            $sections,
            static fn (float $carry, array $section): float => $carry + $section['weight_percent'],
            0.0,
        ), 2);

        if ($totalWeight !== 100.0) {
            throw ValidationException::withMessages([
                'review_template.sections' => ['Review template section weights must total exactly 100 percent.'],
            ]);
        }

        $keys = array_column($sections, 'key');
        if (count($keys) !== count(array_unique($keys))) {
            throw ValidationException::withMessages([
                'review_template.sections' => ['Review template section keys must be unique within a cycle template.'],
            ]);
        }

        return [
            'sections' => $sections,
            'rating_scale' => [
                'min' => (int) $payload['rating_scale']['min'],
                'max' => (int) $payload['rating_scale']['max'],
            ],
        ];
    }

    /**
     * @param  CompetencyVisibilityPayload|array<string, mixed>  $payload
     * @return CompetencyVisibility
     */
    private function normalizeCompetencyVisibility(User $actor, array $payload): array
    {
        $requiredCompetencyIds = $this->normalizeIntegerArray($payload['required_competency_ids'] ?? []);
        $this->ensureCompetencyIdsBelongToCompany($actor, $requiredCompetencyIds);

        return [
            'enabled' => (bool) ($payload['enabled'] ?? false),
            'visible_to_employee' => (bool) ($payload['visible_to_employee'] ?? false),
            'visible_to_manager' => (bool) ($payload['visible_to_manager'] ?? false),
            'visible_to_hr' => (bool) ($payload['visible_to_hr'] ?? false),
            'required_competency_ids' => $requiredCompetencyIds,
        ];
    }

    private function ensureGoalCodeUnique(int $companyId, string $goalCode, ?int $ignoreGoalId = null): void
    {
        $exists = PerformanceGoal::query()
            ->where('company_id', $companyId)
            ->where('goal_code', $goalCode)
            ->when($ignoreGoalId !== null, fn (Builder $builder) => $builder->whereKeyNot($ignoreGoalId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'goal_code' => ['A performance goal with this code already exists for the tenant.'],
            ]);
        }
    }

    private function ensureCompetencyCodeUnique(int $companyId, string $code, ?int $ignoreCompetencyId = null): void
    {
        $exists = PerformanceCompetency::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->when($ignoreCompetencyId !== null, fn (Builder $builder) => $builder->whereKeyNot($ignoreCompetencyId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'code' => ['A competency with this code already exists for the tenant.'],
            ]);
        }
    }

    private function ensureReviewCycleCodeUnique(int $companyId, string $code, ?int $ignoreReviewCycleId = null): void
    {
        $exists = PerformanceReviewCycle::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->when($ignoreReviewCycleId !== null, fn (Builder $builder) => $builder->whereKeyNot($ignoreReviewCycleId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'code' => ['A review cycle with this code already exists for the tenant.'],
            ]);
        }
    }

    private function ensureGoalWeightBudget(
        User $actor,
        int $ownerEmployeeId,
        ?int $reviewCycleId,
        float $weightPercent,
        ?int $ignoreGoalId = null,
    ): void {
        if ($reviewCycleId === null) {
            return;
        }

        $currentWeight = (float) PerformanceGoal::query()
            ->where('company_id', $actor->company_id)
            ->where('owner_employee_id', $ownerEmployeeId)
            ->where('performance_review_cycle_id', $reviewCycleId)
            ->where('status', '!=', 'archived')
            ->when($ignoreGoalId !== null, fn (Builder $builder) => $builder->whereKeyNot($ignoreGoalId))
            ->sum('weight_percent');

        if (round($currentWeight + $weightPercent, 2) > 100.0) {
            throw ValidationException::withMessages([
                'weight_percent' => ['Goal weights for the same owner and review cycle cannot exceed 100 percent.'],
            ]);
        }
    }

    private function ensureEmployeeBelongsToCompany(User $actor, int $employeeId): void
    {
        $exists = Employee::query()
            ->where('company_id', $actor->company_id)
            ->whereKey($employeeId)
            ->exists();

        $this->accessScopeService->ensureCompanyMatch(Employee::class, $exists);
    }

    private function ensureDepartmentBelongsToCompany(User $actor, ?int $departmentId): void
    {
        if ($departmentId === null) {
            return;
        }

        $exists = Department::query()
            ->where('company_id', $actor->company_id)
            ->whereKey($departmentId)
            ->exists();

        $this->accessScopeService->ensureCompanyMatch(Department::class, $exists);
    }

    /**
     * @param  list<int>  $departmentIds
     */
    private function ensureDepartmentIdsBelongToCompany(User $actor, array $departmentIds): void
    {
        if ($departmentIds === []) {
            return;
        }

        $count = Department::query()
            ->where('company_id', $actor->company_id)
            ->whereIn('id', $departmentIds)
            ->count();

        if ($count !== count($departmentIds)) {
            throw ValidationException::withMessages([
                'participant_rules.population.department_ids' => ['One or more departments do not belong to the active tenant.'],
            ]);
        }
    }

    /**
     * @param  list<int>  $designationIds
     */
    private function ensureDesignationIdsBelongToCompany(User $actor, array $designationIds): void
    {
        if ($designationIds === []) {
            return;
        }

        $count = Designation::query()
            ->where('company_id', $actor->company_id)
            ->whereIn('id', $designationIds)
            ->count();

        if ($count !== count($designationIds)) {
            throw ValidationException::withMessages([
                'participant_rules.population.designation_ids' => ['One or more designations do not belong to the active tenant.'],
            ]);
        }
    }

    /**
     * @param  list<int>  $competencyIds
     */
    private function ensureCompetencyIdsBelongToCompany(User $actor, array $competencyIds): void
    {
        if ($competencyIds === []) {
            return;
        }

        $count = PerformanceCompetency::query()
            ->where('company_id', $actor->company_id)
            ->whereIn('id', $competencyIds)
            ->count();

        if ($count !== count($competencyIds)) {
            throw ValidationException::withMessages([
                'competency_visibility.required_competency_ids' => ['One or more competencies do not belong to the active tenant.'],
            ]);
        }
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<int, int>
     */
    private function normalizeIntegerArray(array $values): array
    {
        return collect($values)
            ->map(fn (mixed $value): int => (int) $value)
            ->filter(fn (int $value): bool => $value > 0)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<int, string>
     */
    private function normalizeStringArray(array $values): array
    {
        return collect($values)
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
