<?php

namespace App\Modules\RecruitmentManagement\Services;

use App\Models\Employee;
use App\Models\JobRequisition;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Workflow\Services\WorkflowService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JobRequisitionService
{
    public function __construct(
        private readonly RecruitmentAccessScopeService $accessScopeService,
        private readonly WorkflowService $workflowService,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, JobRequisition>
     */
    public function search(User $actor, array $filters): LengthAwarePaginator
    {
        return $this->accessScopeService
            ->requisitionsQuery($actor)
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', $filters['status']),
            )
            ->when(
                array_key_exists('department_id', $filters),
                fn (Builder $builder) => $builder->where('department_id', $filters['department_id']),
            )
            ->when(
                array_key_exists('location_id', $filters),
                fn (Builder $builder) => $builder->where('location_id', $filters['location_id']),
            )
            ->when(
                array_key_exists('recruiter_user_id', $filters),
                fn (Builder $builder) => $builder->where('recruiter_user_id', $filters['recruiter_user_id']),
            )
            ->when(
                array_key_exists('hiring_manager_employee_id', $filters),
                fn (Builder $builder) => $builder->where('hiring_manager_employee_id', $filters['hiring_manager_employee_id']),
            )
            ->when(
                array_key_exists('employment_type', $filters),
                fn (Builder $builder) => $builder->where('employment_type', $filters['employment_type']),
            )
            ->when(
                array_key_exists('hiring_type', $filters),
                fn (Builder $builder) => $builder->where('hiring_type', $filters['hiring_type']),
            )
            ->when(
                array_key_exists('priority', $filters),
                fn (Builder $builder) => $builder->where('priority', $filters['priority']),
            )
            ->when(
                array_key_exists('date_from', $filters),
                fn (Builder $builder) => $builder->whereDate('created_at', '>=', $filters['date_from']),
            )
            ->when(
                array_key_exists('date_to', $filters),
                fn (Builder $builder) => $builder->whereDate('created_at', '<=', $filters['date_to']),
            )
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findForView(User $actor, int $jobRequisitionId): JobRequisition
    {
        return $this->accessScopeService->resolveAccessibleRequisition($actor, $jobRequisitionId);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(User $actor, array $payload): JobRequisition
    {
        return DB::transaction(function () use ($actor, $payload): JobRequisition {
            $requisition = JobRequisition::query()->create([
                'company_id' => $actor->company_id,
                'requisition_code' => $this->resolveRequisitionCode($payload['requisition_code'] ?? null),
                'title' => trim((string) $payload['title']),
                'employment_type' => $payload['employment_type'],
                'hiring_type' => $payload['hiring_type'],
                'priority' => $payload['priority'],
                'openings_count' => (int) $payload['openings_count'],
                'min_experience_years' => $payload['min_experience_years'] ?? null,
                'target_start_date' => $payload['target_start_date'] ?? null,
                'headcount_reference' => $payload['headcount_reference'] ?? null,
                'department_id' => $payload['department_id'] ?? null,
                'designation_id' => $payload['designation_id'] ?? null,
                'location_id' => $payload['location_id'] ?? null,
                'cost_center_id' => $payload['cost_center_id'] ?? null,
                'recruiter_user_id' => (int) $payload['recruiter_user_id'],
                'hiring_manager_employee_id' => (int) $payload['hiring_manager_employee_id'],
                'requested_by_user_id' => $actor->id,
                'workflow_instance_id' => null,
                'status' => 'draft',
                'status_before_hold' => null,
                'justification' => trim((string) $payload['justification']),
                'notes' => array_key_exists('notes', $payload) ? trim((string) $payload['notes']) ?: null : null,
                'closed_reason' => null,
                'submitted_at' => null,
                'approved_at' => null,
                'on_hold_at' => null,
                'closed_at' => null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'recruitment.requisition.created',
                actor: $actor,
                metadata: [
                    'job_requisition_id' => $requisition->id,
                    'requisition_code' => $requisition->requisition_code,
                    'title' => $requisition->title,
                    'employment_type' => $requisition->employment_type,
                    'hiring_type' => $requisition->hiring_type,
                    'priority' => $requisition->priority,
                    'openings_count' => $requisition->openings_count,
                    'recruiter_user_id' => $requisition->recruiter_user_id,
                    'hiring_manager_employee_id' => $requisition->hiring_manager_employee_id,
                ],
                entityType: 'job_requisition',
                entityId: (string) $requisition->id,
            );

            return $this->loadRequisition($requisition);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(User $actor, int $jobRequisitionId, array $payload): JobRequisition
    {
        $requisition = $this->accessScopeService->resolveAccessibleRequisition($actor, $jobRequisitionId);
        $action = $payload['action'] ?? null;

        if ($action) {
            return match ($action) {
                'submit' => $this->submit($actor, $requisition),
                'put_on_hold' => $this->putOnHold($actor, $requisition, $payload['comment'] ?? null),
                'resume' => $this->resume($actor, $requisition),
                'close' => $this->close($actor, $requisition, $payload['comment'] ?? null),
                'approve', 'reject', 'request_changes' => $this->actOnWorkflowTask($actor, $requisition, $action, $payload['comment'] ?? null),
                default => throw ValidationException::withMessages([
                    'action' => ['The requested requisition action is not supported.'],
                ]),
            };
        }

        return $this->updateDetails($actor, $requisition, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function updateDetails(User $actor, JobRequisition $requisition, array $payload): JobRequisition
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'requisition' => ['Editing requisition details requires recruitment management permission.'],
            ]);
        }

        if (! in_array($requisition->status, ['draft', 'rejected', 'changes_requested', 'on_hold'], true)) {
            throw ValidationException::withMessages([
                'requisition' => ['Only draft, on-hold, rejected, or changes-requested requisitions can be edited directly.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $requisition, $payload): JobRequisition {
            $before = $requisition->only([
                'requisition_code',
                'title',
                'employment_type',
                'hiring_type',
                'priority',
                'openings_count',
                'department_id',
                'designation_id',
                'location_id',
                'cost_center_id',
                'recruiter_user_id',
                'hiring_manager_employee_id',
                'status',
            ]);

            $requisition->fill([
                'requisition_code' => array_key_exists('requisition_code', $payload)
                    ? ($payload['requisition_code'] ? Str::upper(trim((string) $payload['requisition_code'])) : $this->resolveRequisitionCode(null))
                    : $requisition->requisition_code,
                'title' => array_key_exists('title', $payload) ? trim((string) $payload['title']) : $requisition->title,
                'employment_type' => $payload['employment_type'] ?? $requisition->employment_type,
                'hiring_type' => $payload['hiring_type'] ?? $requisition->hiring_type,
                'priority' => $payload['priority'] ?? $requisition->priority,
                'openings_count' => $payload['openings_count'] ?? $requisition->openings_count,
                'min_experience_years' => array_key_exists('min_experience_years', $payload) ? $payload['min_experience_years'] : $requisition->min_experience_years,
                'target_start_date' => array_key_exists('target_start_date', $payload) ? $payload['target_start_date'] : $requisition->target_start_date,
                'headcount_reference' => array_key_exists('headcount_reference', $payload) ? $payload['headcount_reference'] : $requisition->headcount_reference,
                'department_id' => array_key_exists('department_id', $payload) ? $payload['department_id'] : $requisition->department_id,
                'designation_id' => array_key_exists('designation_id', $payload) ? $payload['designation_id'] : $requisition->designation_id,
                'location_id' => array_key_exists('location_id', $payload) ? $payload['location_id'] : $requisition->location_id,
                'cost_center_id' => array_key_exists('cost_center_id', $payload) ? $payload['cost_center_id'] : $requisition->cost_center_id,
                'recruiter_user_id' => array_key_exists('recruiter_user_id', $payload) ? $payload['recruiter_user_id'] : $requisition->recruiter_user_id,
                'hiring_manager_employee_id' => array_key_exists('hiring_manager_employee_id', $payload) ? $payload['hiring_manager_employee_id'] : $requisition->hiring_manager_employee_id,
                'justification' => array_key_exists('justification', $payload) ? trim((string) $payload['justification']) : $requisition->justification,
                'notes' => array_key_exists('notes', $payload) ? (trim((string) $payload['notes']) ?: null) : $requisition->notes,
                'updated_by_user_id' => $actor->id,
            ]);
            $requisition->save();

            $this->auditLogger->record(
                eventType: 'recruitment.requisition.updated',
                actor: $actor,
                metadata: [
                    'job_requisition_id' => $requisition->id,
                    'before' => $before,
                    'after' => $requisition->only([
                        'requisition_code',
                        'title',
                        'employment_type',
                        'hiring_type',
                        'priority',
                        'openings_count',
                        'department_id',
                        'designation_id',
                        'location_id',
                        'cost_center_id',
                        'recruiter_user_id',
                        'hiring_manager_employee_id',
                        'status',
                    ]),
                ],
                entityType: 'job_requisition',
                entityId: (string) $requisition->id,
            );

            return $this->loadRequisition($requisition);
        });
    }

    private function submit(User $actor, JobRequisition $requisition): JobRequisition
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'requisition' => ['Submitting a requisition requires recruitment management permission.'],
            ]);
        }

        if (! in_array($requisition->status, ['draft', 'rejected', 'changes_requested'], true)) {
            throw ValidationException::withMessages([
                'requisition' => ['Only draft, rejected, or changes-requested requisitions can be submitted for approval.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $requisition): JobRequisition {
            $hiringManager = Employee::query()
                ->with('user')
                ->find($requisition->hiring_manager_employee_id);

            if (! $hiringManager?->user?->is_active) {
                throw ValidationException::withMessages([
                    'hiring_manager_employee_id' => ['The selected hiring manager must be linked to an active user account before approval can begin.'],
                ]);
            }

            $workflowInstance = $this->workflowService->startInstance($actor, [
                'workflow_key' => 'recruitment-requisition-approval',
                'reference_type' => 'job_requisition',
                'reference_id' => (string) $requisition->id,
                'payload' => [
                    'job_requisition_id' => $requisition->id,
                    'recruiter_user_id' => $requisition->recruiter_user_id,
                    'hiring_manager_employee_id' => $requisition->hiring_manager_employee_id,
                    'hiring_manager_user_id' => $hiringManager->user->id,
                    'requested_by_user_id' => $requisition->requested_by_user_id,
                ],
            ]);

            $requisition->forceFill([
                'workflow_instance_id' => $workflowInstance->id,
                'status' => 'submitted',
                'status_before_hold' => null,
                'submitted_at' => now(),
                'approved_at' => null,
                'on_hold_at' => null,
                'closed_at' => null,
                'closed_reason' => null,
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'recruitment.requisition.submitted',
                actor: $actor,
                metadata: [
                    'job_requisition_id' => $requisition->id,
                    'workflow_instance_id' => $workflowInstance->id,
                    'recruiter_user_id' => $requisition->recruiter_user_id,
                    'hiring_manager_employee_id' => $requisition->hiring_manager_employee_id,
                ],
                entityType: 'job_requisition',
                entityId: (string) $requisition->id,
            );

            return $this->loadRequisition($requisition);
        });
    }

    private function putOnHold(User $actor, JobRequisition $requisition, ?string $comment): JobRequisition
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'requisition' => ['Putting a requisition on hold requires recruitment management permission.'],
            ]);
        }

        if ($requisition->status === 'submitted') {
            throw ValidationException::withMessages([
                'requisition' => ['Submitted requisitions cannot be put on hold while approval tasks are active. Close or complete the approval workflow first.'],
            ]);
        }

        if (! in_array($requisition->status, ['draft', 'approved', 'rejected', 'changes_requested'], true)) {
            throw ValidationException::withMessages([
                'requisition' => ['Only draft, approved, rejected, or changes-requested requisitions can be moved on hold.'],
            ]);
        }

        $requisition->forceFill([
            'status_before_hold' => $requisition->status,
            'status' => 'on_hold',
            'on_hold_at' => now(),
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'recruitment.requisition.on_hold',
            actor: $actor,
            metadata: [
                'job_requisition_id' => $requisition->id,
                'previous_status' => $requisition->status_before_hold,
                'comment' => $comment,
            ],
            entityType: 'job_requisition',
            entityId: (string) $requisition->id,
        );

        return $this->loadRequisition($requisition);
    }

    private function resume(User $actor, JobRequisition $requisition): JobRequisition
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'requisition' => ['Resuming a requisition requires recruitment management permission.'],
            ]);
        }

        if ($requisition->status !== 'on_hold' || ! $requisition->status_before_hold) {
            throw ValidationException::withMessages([
                'requisition' => ['Only on-hold requisitions can be resumed.'],
            ]);
        }

        $previousStatus = $requisition->status_before_hold;

        $requisition->forceFill([
            'status' => $previousStatus,
            'status_before_hold' => null,
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'recruitment.requisition.resumed',
            actor: $actor,
            metadata: [
                'job_requisition_id' => $requisition->id,
                'resumed_to_status' => $previousStatus,
            ],
            entityType: 'job_requisition',
            entityId: (string) $requisition->id,
        );

        return $this->loadRequisition($requisition);
    }

    private function close(User $actor, JobRequisition $requisition, ?string $comment): JobRequisition
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'requisition' => ['Closing a requisition requires recruitment management permission.'],
            ]);
        }

        if ($requisition->status === 'closed') {
            throw ValidationException::withMessages([
                'requisition' => ['This requisition is already closed.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $requisition, $comment): JobRequisition {
            $this->cancelOpenWorkflowIfNeeded($requisition, $actor, $comment);

            $requisition->forceFill([
                'status' => 'closed',
                'status_before_hold' => null,
                'closed_reason' => $comment ? trim($comment) : $requisition->closed_reason,
                'closed_at' => now(),
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'recruitment.requisition.closed',
                actor: $actor,
                metadata: [
                    'job_requisition_id' => $requisition->id,
                    'closed_reason' => $requisition->closed_reason,
                ],
                entityType: 'job_requisition',
                entityId: (string) $requisition->id,
            );

            return $this->loadRequisition($requisition);
        });
    }

    private function actOnWorkflowTask(
        User $actor,
        JobRequisition $requisition,
        string $action,
        ?string $comment,
    ): JobRequisition {
        if (! $actor->can('recruitment.approve') && ! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'requisition' => ['Approving or rejecting a requisition requires recruitment approval permission.'],
            ]);
        }

        if (! $requisition->workflow_instance_id) {
            throw ValidationException::withMessages([
                'requisition' => ['This requisition is not linked to an approval workflow.'],
            ]);
        }

        $task = $requisition->workflowInstance?->tasks()
            ->where('status', 'open')
            ->orderBy('sequence')
            ->first();

        if (! $task) {
            throw ValidationException::withMessages([
                'requisition' => ['No open workflow task is available for this requisition.'],
            ]);
        }

        $this->workflowService->decideTask($task, $actor, [
            'action' => $action,
            'comment' => $comment,
        ]);

        return $this->loadRequisition(
            $this->accessScopeService->resolveAccessibleRequisition($actor, $requisition->id),
        );
    }

    private function cancelOpenWorkflowIfNeeded(JobRequisition $requisition, User $actor, ?string $comment): void
    {
        $requisition->loadMissing('workflowInstance.tasks', 'workflowInstance.starter');

        if (! $requisition->workflowInstance) {
            return;
        }

        if ($requisition->workflowInstance->tasks->where('status', 'open')->isEmpty()) {
            return;
        }

        $this->workflowService->cancelInstance($requisition->workflowInstance, $actor, $comment);
    }

    private function resolveRequisitionCode(?string $code): string
    {
        if ($code) {
            return Str::upper(trim($code));
        }

        $nextOrdinal = ((int) JobRequisition::query()->lockForUpdate()->max('id')) + 1;

        return sprintf('REQ-%04d', $nextOrdinal);
    }

    private function loadRequisition(JobRequisition $requisition): JobRequisition
    {
        return $requisition->load([
            'department',
            'designation',
            'location',
            'costCenter',
            'recruiter',
            'hiringManager',
            'requestedBy',
            'workflowInstance.definition',
            'workflowInstance.tasks.assignee',
            'workflowInstance.tasks.actor',
        ]);
    }
}
