<?php

namespace App\Modules\RecruitmentManagement\Services;

use App\Models\Candidate;
use App\Models\Employee;
use App\Models\EmployeeLifecycleTaskTemplate;
use App\Models\JobRequisition;
use App\Models\Offer;
use App\Models\RecruitmentHireHandoff;
use App\Models\User;
use App\Modules\EmployeeManagement\Services\EmployeeCreationRules;
use App\Modules\EmployeeManagement\Services\EmployeeLifecycleTaskTemplateService;
use App\Modules\EmployeeManagement\Services\EmployeeService;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Notifications\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RecruitmentHireHandoffService
{
    public function __construct(
        private readonly RecruitmentHireHandoffAccessScopeService $accessScopeService,
        private readonly OfferAccessScopeService $offerAccessScopeService,
        private readonly EmployeeService $employeeService,
        private readonly EmployeeCreationRules $employeeCreationRules,
        private readonly EmployeeLifecycleTaskTemplateService $templateService,
        private readonly AuditLogger $auditLogger,
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, RecruitmentHireHandoff>
     */
    public function search(User $actor, array $filters): LengthAwarePaginator
    {
        $results = $this->accessScopeService
            ->handoffsQuery($actor)
            ->when(
                array_key_exists('job_requisition_id', $filters),
                fn (Builder $builder) => $builder->where('job_requisition_id', $filters['job_requisition_id']),
            )
            ->when(
                array_key_exists('candidate_id', $filters),
                fn (Builder $builder) => $builder->where('candidate_id', $filters['candidate_id']),
            )
            ->when(
                array_key_exists('offer_id', $filters),
                fn (Builder $builder) => $builder->where('offer_id', $filters['offer_id']),
            )
            ->when(
                array_key_exists('employee_id', $filters),
                fn (Builder $builder) => $builder->where('employee_id', $filters['employee_id']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', $filters['status']),
            )
            ->orderByDesc('converted_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);

        $this->auditLogger->record(
            eventType: 'recruitment.hire_handoff.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'handoff_count' => $results->total(),
            ],
            entityType: 'recruitment_hire_handoff',
            entityId: null,
        );

        return $results;
    }

    public function findForView(User $actor, int $handoffId): RecruitmentHireHandoff
    {
        $handoff = $this->accessScopeService->resolveAccessibleHandoff($actor, $handoffId);

        $this->auditLogger->record(
            eventType: 'recruitment.hire_handoff.viewed',
            actor: $actor,
            metadata: [
                'handoff_id' => $handoff->id,
                'offer_id' => $handoff->offer_id,
                'candidate_id' => $handoff->candidate_id,
                'employee_id' => $handoff->employee_id,
                'status' => $handoff->status,
            ],
            entityType: 'recruitment_hire_handoff',
            entityId: (string) $handoff->id,
        );

        return $handoff;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createFromAcceptedOffer(User $actor, int $offerId, array $payload): RecruitmentHireHandoff
    {
        if (! $actor->can('employee.manage')) {
            throw ValidationException::withMessages([
                'handoff' => ['Creating a hire handoff requires employee management permission.'],
            ]);
        }

        $offer = $this->offerAccessScopeService
            ->offersQuery($actor, [
                'candidate.resumes',
                'requisition',
                'recruiter',
                'handoff',
            ])
            ->findOrFail($offerId);

        if ($offer->status !== 'accepted') {
            throw ValidationException::withMessages([
                'offer' => ['Only accepted offers can be converted into a hire handoff.'],
            ]);
        }

        if ($offer->handoff) {
            throw ValidationException::withMessages([
                'offer' => ['This offer has already been converted into a hire handoff.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $offer, $payload): RecruitmentHireHandoff {
            $candidate = $offer->candidate()->with('resumes')->firstOrFail();
            $requisition = $offer->requisition()->firstOrFail();
            $sourceResume = $candidate->resumes->first();

            $employeePayload = $this->buildEmployeePayload($actor, $offer, $candidate, $requisition, $payload);
            $this->validateEmployeePayload($actor, $employeePayload);

            $employee = $this->employeeService->create($actor, $employeePayload);

            [$handoffStatus, $templateIds, $taskIds, $onboardingTriggeredAt] = $this->triggerOnboardingTasks(
                actor: $actor,
                employeeId: $employee->id,
                payload: $payload,
            );

            $handoff = RecruitmentHireHandoff::query()->create([
                'company_id' => $actor->company_id,
                'job_requisition_id' => $offer->job_requisition_id,
                'candidate_id' => $offer->candidate_id,
                'offer_id' => $offer->id,
                'employee_id' => $employee->id,
                'recruiter_user_id' => $offer->recruiter_user_id,
                'converted_by_user_id' => $actor->id,
                'source_resume_id' => $sourceResume?->id,
                'status' => $handoffStatus,
                'offer_snapshot' => [
                    'offer_code' => $offer->offer_code,
                    'status' => $offer->status,
                    'employment_type' => $offer->employment_type,
                    'currency' => $offer->currency,
                    'annual_ctc_amount' => $offer->annual_ctc_amount,
                    'joining_bonus_amount' => $offer->joining_bonus_amount,
                    'proposed_start_date' => $offer->proposed_start_date?->toDateString(),
                    'expires_on' => $offer->expires_on?->toDateString(),
                    'accepted_at' => $offer->accepted_at?->toIso8601String(),
                ],
                'candidate_snapshot' => [
                    'candidate_code' => $candidate->candidate_code,
                    'first_name' => $candidate->first_name,
                    'last_name' => $candidate->last_name,
                    'email' => $candidate->email,
                    'phone' => $candidate->phone,
                    'current_stage' => $candidate->current_stage,
                    'status' => $candidate->status,
                ],
                'requisition_snapshot' => [
                    'requisition_code' => $requisition->requisition_code,
                    'title' => $requisition->title,
                    'employment_type' => $requisition->employment_type,
                    'priority' => $requisition->priority,
                    'department_id' => $requisition->department_id,
                    'designation_id' => $requisition->designation_id,
                    'location_id' => $requisition->location_id,
                    'cost_center_id' => $requisition->cost_center_id,
                    'hiring_manager_employee_id' => $requisition->hiring_manager_employee_id,
                ],
                'document_references' => [
                    'candidate_resume_ids' => $candidate->resumes->pluck('id')->all(),
                    'current_resume_id' => $sourceResume?->id,
                    'current_resume_file_name' => $sourceResume?->original_file_name,
                ],
                'onboarding_template_ids' => $templateIds,
                'onboarding_task_ids' => $taskIds,
                'notes' => $payload['notes'] ?? null,
                'converted_at' => now(),
                'onboarding_triggered_at' => $onboardingTriggeredAt,
            ]);

            $this->markCandidateAsHired($candidate, $actor);

            $this->auditLogger->record(
                eventType: 'recruitment.hire_handoff.created',
                actor: $actor,
                metadata: [
                    'handoff_id' => $handoff->id,
                    'offer_id' => $offer->id,
                    'candidate_id' => $candidate->id,
                    'employee_id' => $employee->id,
                    'status' => $handoff->status,
                    'onboarding_task_ids' => $taskIds,
                ],
                entityType: 'recruitment_hire_handoff',
                entityId: (string) $handoff->id,
            );

            $this->notifyStakeholders($handoff, $actor);

            return $this->loadHandoff($handoff);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function buildEmployeePayload(User $actor, Offer $offer, Candidate $candidate, JobRequisition $requisition, array $payload): array
    {
        $dateOfJoining = array_key_exists('date_of_joining', $payload)
            ? Carbon::parse((string) $payload['date_of_joining'])->toDateString()
            : $offer->proposed_start_date?->toDateString();

        if (! $dateOfJoining) {
            throw ValidationException::withMessages([
                'date_of_joining' => ['A joining date is required either from the accepted offer or the handoff request.'],
            ]);
        }

        $departmentId = $payload['department_id'] ?? $requisition->department_id;
        $designationId = $payload['designation_id'] ?? $requisition->designation_id;

        if (! $departmentId) {
            throw ValidationException::withMessages([
                'department_id' => ['A department is required before an accepted offer can be converted into a hire handoff.'],
            ]);
        }

        if (! $designationId) {
            throw ValidationException::withMessages([
                'designation_id' => ['A designation is required before an accepted offer can be converted into a hire handoff.'],
            ]);
        }

        $lastName = $payload['last_name'] ?? $candidate->last_name;

        if (blank($lastName)) {
            throw ValidationException::withMessages([
                'last_name' => ['Candidate last name is required before an accepted offer can be converted into a hire handoff.'],
            ]);
        }

        return [
            'employee_code' => $payload['employee_code'] ?? null,
            'first_name' => $payload['first_name'] ?? $candidate->first_name,
            'middle_name' => $payload['middle_name'] ?? null,
            'last_name' => $lastName,
            'email' => $payload['email'] ?? $candidate->email,
            'phone' => array_key_exists('phone', $payload) ? $payload['phone'] : $candidate->phone,
            'date_of_birth' => $payload['date_of_birth'] ?? null,
            'gender' => $payload['gender'] ?? null,
            'marital_status' => $payload['marital_status'] ?? null,
            'date_of_joining' => $dateOfJoining,
            'employment_type' => $offer->employment_type,
            'employment_status' => $payload['employment_status'] ?? $this->defaultEmploymentStatus($dateOfJoining),
            'department_id' => $departmentId,
            'designation_id' => $designationId,
            'manager_id' => array_key_exists('manager_id', $payload) ? $payload['manager_id'] : $requisition->hiring_manager_employee_id,
            'location_id' => array_key_exists('location_id', $payload) ? $payload['location_id'] : $requisition->location_id,
            'cost_center_id' => array_key_exists('cost_center_id', $payload) ? $payload['cost_center_id'] : $requisition->cost_center_id,
            'user_id' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $employeePayload
     */
    private function validateEmployeePayload(User $actor, array $employeePayload): void
    {
        $validator = Validator::make(
            $employeePayload,
            $this->employeeCreationRules->rulesForCompany((int) $actor->company_id),
        );

        $this->employeeCreationRules->applyCodePolicyValidation(
            $validator,
            (int) $actor->company_id,
            $employeePayload,
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }
    }

    private function defaultEmploymentStatus(string $dateOfJoining): string
    {
        return Carbon::parse($dateOfJoining)->isFuture() ? 'inactive' : 'probation';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0: string, 1: list<int>, 2: list<int>, 3: \Illuminate\Support\Carbon|Carbon|null}
     */
    private function triggerOnboardingTasks(User $actor, int $employeeId, array $payload): array
    {
        $triggerOnboarding = (bool) ($payload['trigger_onboarding'] ?? true);

        if (! $triggerOnboarding) {
            return ['onboarding_skipped', [], [], null];
        }

        $templateIds = $payload['template_ids'] ?? EmployeeLifecycleTaskTemplate::query()
            ->where('lifecycle_type', 'onboarding')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($templateIds === []) {
            return ['employee_created', [], [], null];
        }

        $employee = Employee::query()->findOrFail($employeeId);
        $tasks = $this->templateService->apply($employee, $actor, ['template_ids' => $templateIds]);

        return [
            'onboarding_queued',
            array_values($templateIds),
            $tasks->pluck('id')->all(),
            now(),
        ];
    }

    private function markCandidateAsHired(Candidate $candidate, User $actor): void
    {
        $fromStage = $candidate->current_stage;

        if ($candidate->current_stage !== 'hired') {
            $candidate->stageTransitions()->create([
                'company_id' => $candidate->company_id,
                'from_stage' => $candidate->current_stage,
                'to_stage' => 'hired',
                'resulting_status' => 'hired',
                'comment' => 'Candidate converted into employee handoff after accepted offer.',
                'transitioned_by_user_id' => $actor->id,
                'transitioned_at' => now(),
            ]);
        }

        $candidate->forceFill([
            'current_stage' => 'hired',
            'status' => 'hired',
            'stage_entered_at' => now(),
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'recruitment.candidate.stage_transitioned',
            actor: $actor,
            metadata: [
                'candidate_id' => $candidate->id,
                'from_stage' => $fromStage,
                'to_stage' => 'hired',
                'resulting_status' => 'hired',
                'comment' => 'Candidate converted into employee handoff after accepted offer.',
            ],
            entityType: 'candidate',
            entityId: (string) $candidate->id,
        );
    }

    private function notifyStakeholders(RecruitmentHireHandoff $handoff, User $actor): void
    {
        $handoff->loadMissing('recruiter', 'requisition.hiringManager.user');

        $recipients = collect([
            $handoff->recruiter,
            $handoff->requisition?->hiringManager?->user,
        ])->filter(fn (?User $user) => $user?->is_active)->unique('id');

        foreach ($recipients as $recipient) {
            if ($recipient->id === $actor->id) {
                continue;
            }

            $this->notificationService->sendDirect($recipient, [
                'type' => 'recruitment',
                'channel' => 'in_app',
                'title' => 'Hire handoff created',
                'message' => 'An accepted offer has been converted into onboarding handoff for '.$handoff->candidate_snapshot['first_name'].' '.$handoff->candidate_snapshot['last_name'].'.',
                'priority' => 'normal',
                'deep_link' => '/recruitment/handoffs/'.$handoff->id,
                'data' => [
                    'handoff_id' => $handoff->id,
                    'offer_id' => $handoff->offer_id,
                    'candidate_id' => $handoff->candidate_id,
                    'employee_id' => $handoff->employee_id,
                    'status' => $handoff->status,
                ],
            ], $actor);
        }
    }

    private function loadHandoff(RecruitmentHireHandoff $handoff): RecruitmentHireHandoff
    {
        return $handoff->load([
            'offer',
            'candidate.recruiter',
            'requisition',
            'employee',
            'recruiter',
            'convertedBy',
            'sourceResume',
        ]);
    }
}
