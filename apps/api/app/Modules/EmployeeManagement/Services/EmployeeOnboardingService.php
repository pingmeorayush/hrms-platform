<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Employee;
use App\Models\EmployeeLifecycleTaskTemplate;
use App\Models\EmployeeOnboardingTask;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Workflow\Services\WorkflowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type EmployeeOnboardingTaskPayload array<string, mixed>
 * @phpstan-type EmployeeLifecycleTaskSummary array{
 *   total_count: int,
 *   completed_count: int,
 *   skipped_count: int,
 *   pending_count: int,
 *   in_progress_count: int,
 *   awaiting_approval_count: int,
 *   changes_requested_count: int,
 *   rejected_count: int,
 *   incomplete_count: int,
 *   progress_percentage: int,
 *   is_complete: bool
 * }
 * @phpstan-type EmployeeLifecycleTaskStatusEmployee array{
 *   id: int,
 *   employee_code: string,
 *   full_name: string,
 *   email: string|null,
 *   date_of_joining: string|null,
 *   department: string|null,
 *   designation: string|null
 * }
 * @phpstan-type EmployeeLifecycleTaskStatusSummary array{
 *   total_count: int,
 *   closed_count: int,
 *   incomplete_count: int,
 *   progress_percentage: int,
 *   is_complete: bool
 * }
 * @phpstan-type EmployeeLifecycleTaskStatus array{
 *   employee: EmployeeLifecycleTaskStatusEmployee,
 *   lifecycle_type: string,
 *   summary: EmployeeLifecycleTaskStatusSummary
 * }
 * @phpstan-type EmployeeLifecycleTaskList array{0: Collection<int, EmployeeOnboardingTask>, 1: EmployeeLifecycleTaskSummary}
 */
class EmployeeOnboardingService
{
    public const DEFAULT_LIFECYCLE_TYPE = 'onboarding';

    public const DEFAULT_OFFBOARDING_WORKFLOW_KEY = 'employee-offboarding-clearance';

    private const CLOSED_STATUSES = ['completed', 'skipped'];

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly WorkflowService $workflowService,
    ) {}

    /**
     * @return EmployeeLifecycleTaskList
     */
    public function listForEmployee(Employee $employee, User $actor, string $lifecycleType = self::DEFAULT_LIFECYCLE_TYPE): array
    {
        $tasks = $employee->lifecycleTasks()
            ->with(['template', 'workflowInstance.tasks.assignee', 'workflowInstance.tasks.actor', 'assignedTo'])
            ->where('lifecycle_type', $lifecycleType)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $summary = $this->summarize($tasks);

        $this->auditLogger->record(
            eventType: 'employee.lifecycle_task.viewed',
            actor: $actor,
            metadata: [
                'employee_id' => $employee->id,
                'lifecycle_type' => $lifecycleType,
                'task_count' => $summary['total_count'],
                'progress_percentage' => $summary['progress_percentage'],
            ],
            entityType: 'employee',
            entityId: (string) $employee->id,
        );

        if ($lifecycleType === self::DEFAULT_LIFECYCLE_TYPE) {
            $this->auditLogger->record(
                eventType: 'employee.onboarding.viewed',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'task_count' => $summary['total_count'],
                    'progress_percentage' => $summary['progress_percentage'],
                ],
                entityType: 'employee',
                entityId: (string) $employee->id,
            );
        }

        return [$tasks, $summary];
    }

    /**
     * @param  EmployeeOnboardingTaskPayload  $payload
     */
    public function create(
        Employee $employee,
        User $actor,
        array $payload,
        ?string $forcedLifecycleType = null,
    ): EmployeeOnboardingTask {
        return DB::transaction(function () use ($employee, $actor, $payload, $forcedLifecycleType): EmployeeOnboardingTask {
            $normalizedPayload = $this->normalizePayload($employee, $actor, $payload, null, $forcedLifecycleType);

            $task = $employee->lifecycleTasks()->create([
                ...$normalizedPayload,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
                'latest_action_by_user_id' => $actor->id,
            ]);

            if ($task->requires_approval && (($payload['status'] ?? null) === 'completed')) {
                $task = $this->submitForApproval($employee, $task, $actor);
            }

            $this->auditLogger->record(
                eventType: 'employee.lifecycle_task.created',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'task_id' => $task->id,
                    'lifecycle_type' => $task->lifecycle_type,
                    'status' => $task->status,
                    'category' => $task->category,
                    'requires_approval' => $task->requires_approval,
                    'template_id' => $task->template_id,
                ],
                entityType: 'employee_lifecycle_task',
                entityId: (string) $task->id,
            );

            if ($task->lifecycle_type === self::DEFAULT_LIFECYCLE_TYPE) {
                $this->auditLogger->record(
                    eventType: 'employee.onboarding_task.created',
                    actor: $actor,
                    metadata: [
                        'employee_id' => $employee->id,
                        'task_id' => $task->id,
                        'status' => $task->status,
                        'category' => $task->category,
                    ],
                    entityType: 'employee_onboarding_task',
                    entityId: (string) $task->id,
                );
            }

            return $this->loadTask($task->id);
        });
    }

    /**
     * @param  EmployeeOnboardingTaskPayload  $payload
     */
    public function update(
        Employee $employee,
        EmployeeOnboardingTask $task,
        User $actor,
        array $payload,
        ?string $forcedLifecycleType = null,
    ): EmployeeOnboardingTask {
        return DB::transaction(function () use ($employee, $task, $actor, $payload, $forcedLifecycleType): EmployeeOnboardingTask {
            $trackedFields = [
                'lifecycle_type',
                'template_id',
                'title',
                'category',
                'task_type',
                'assignee_type',
                'assigned_to_user_id',
                'requires_approval',
                'approval_workflow_key',
                'workflow_instance_id',
                'status',
                'sort_order',
                'due_date',
                'completed_at',
                'completed_by_user_id',
                'approved_at',
                'notes',
            ];
            $before = $task->only($trackedFields);
            $normalizedPayload = $this->normalizePayload($employee, $actor, $payload, $task, $forcedLifecycleType);

            $requestedStatus = $payload['status'] ?? null;
            $shouldSubmitForApproval = (($normalizedPayload['requires_approval'] ?? $task->requires_approval) === true)
                && $requestedStatus === 'completed';

            $task->fill([
                ...$normalizedPayload,
                'updated_by_user_id' => $actor->id,
                'latest_action_by_user_id' => $actor->id,
            ]);
            $task->save();

            if ($shouldSubmitForApproval) {
                $task = $this->submitForApproval($employee, $task, $actor);
            }

            $this->auditLogger->record(
                eventType: 'employee.lifecycle_task.updated',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'task_id' => $task->id,
                    'lifecycle_type' => $task->lifecycle_type,
                    'before' => $before,
                    'after' => $task->only($trackedFields),
                ],
                entityType: 'employee_lifecycle_task',
                entityId: (string) $task->id,
            );

            if ($task->lifecycle_type === self::DEFAULT_LIFECYCLE_TYPE) {
                $this->auditLogger->record(
                    eventType: 'employee.onboarding_task.updated',
                    actor: $actor,
                    metadata: [
                        'employee_id' => $employee->id,
                        'task_id' => $task->id,
                        'before' => $before,
                        'after' => $task->only($trackedFields),
                    ],
                    entityType: 'employee_onboarding_task',
                    entityId: (string) $task->id,
                );
            }

            return $this->loadTask($task->id);
        });
    }

    /**
     * @return Collection<int, Employee>
     */
    public function listIncompleteStatuses(User $actor, string $lifecycleType = self::DEFAULT_LIFECYCLE_TYPE): Collection
    {
        $employees = Employee::query()
            ->with(['department', 'designation'])
            ->withCount([
                'lifecycleTasks as lifecycle_task_count' => function (Builder $query) use ($lifecycleType): void {
                    $query->where('lifecycle_type', $lifecycleType);
                },
                'lifecycleTasks as closed_lifecycle_task_count' => function (Builder $query) use ($lifecycleType): void {
                    $query->where('lifecycle_type', $lifecycleType)
                        ->whereIn('status', self::CLOSED_STATUSES);
                },
                'lifecycleTasks as incomplete_lifecycle_task_count' => function (Builder $query) use ($lifecycleType): void {
                    $query->where('lifecycle_type', $lifecycleType)
                        ->whereNotIn('status', self::CLOSED_STATUSES);
                },
            ])
            ->whereHas('lifecycleTasks', function (Builder $query) use ($lifecycleType): void {
                $query->where('lifecycle_type', $lifecycleType)
                    ->whereNotIn('status', self::CLOSED_STATUSES);
            })
            ->orderBy('date_of_joining')
            ->orderBy('employee_code')
            ->get();

        $this->auditLogger->record(
            eventType: 'employee.lifecycle_task_status.viewed',
            actor: $actor,
            metadata: [
                'employee_count' => $employees->count(),
                'lifecycle_type' => $lifecycleType,
            ],
            entityType: 'employee_lifecycle_task_status',
            entityId: (string) $actor->company_id,
        );

        if ($lifecycleType === self::DEFAULT_LIFECYCLE_TYPE) {
            $this->auditLogger->record(
                eventType: 'employee.onboarding_status.viewed',
                actor: $actor,
                metadata: [
                    'employee_count' => $employees->count(),
                ],
                entityType: 'employee_onboarding_status',
                entityId: (string) $actor->company_id,
            );
        }

        return $employees;
    }

    /**
     * @param  Collection<int, EmployeeOnboardingTask>  $tasks
     * @return EmployeeLifecycleTaskSummary
     */
    public function summarize(Collection $tasks): array
    {
        $totalCount = $tasks->count();
        $completedCount = $tasks->where('status', 'completed')->count();
        $skippedCount = $tasks->where('status', 'skipped')->count();
        $pendingCount = $tasks->where('status', 'pending')->count();
        $inProgressCount = $tasks->where('status', 'in_progress')->count();
        $awaitingApprovalCount = $tasks->where('status', 'awaiting_approval')->count();
        $changesRequestedCount = $tasks->where('status', 'changes_requested')->count();
        $rejectedCount = $tasks->where('status', 'rejected')->count();
        $closedCount = $completedCount + $skippedCount;

        return [
            'total_count' => $totalCount,
            'completed_count' => $completedCount,
            'skipped_count' => $skippedCount,
            'pending_count' => $pendingCount,
            'in_progress_count' => $inProgressCount,
            'awaiting_approval_count' => $awaitingApprovalCount,
            'changes_requested_count' => $changesRequestedCount,
            'rejected_count' => $rejectedCount,
            'incomplete_count' => $pendingCount + $inProgressCount + $awaitingApprovalCount + $changesRequestedCount + $rejectedCount,
            'progress_percentage' => $totalCount === 0 ? 0 : (int) round(($closedCount / $totalCount) * 100),
            'is_complete' => $totalCount > 0 && ($pendingCount + $inProgressCount + $awaitingApprovalCount + $changesRequestedCount + $rejectedCount) === 0,
        ];
    }

    /**
     * @return EmployeeLifecycleTaskStatus
     */
    public function summarizeEmployee(Employee $employee, string $lifecycleType): array
    {
        $totalCount = (int) $employee->lifecycle_task_count;
        $closedCount = (int) $employee->closed_lifecycle_task_count;
        $incompleteCount = (int) $employee->incomplete_lifecycle_task_count;

        return [
            'employee' => [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
                'date_of_joining' => $employee->date_of_joining?->toDateString(),
                'department' => $employee->department?->name,
                'designation' => $employee->designation?->name,
            ],
            'lifecycle_type' => $lifecycleType,
            'summary' => [
                'total_count' => $totalCount,
                'closed_count' => $closedCount,
                'incomplete_count' => $incompleteCount,
                'progress_percentage' => $totalCount === 0 ? 0 : (int) round(($closedCount / $totalCount) * 100),
                'is_complete' => $totalCount > 0 && $incompleteCount === 0,
            ],
        ];
    }

    /**
     * @param  EmployeeOnboardingTaskPayload  $overrides
     * @return EmployeeOnboardingTaskPayload
     */
    public function buildTaskPayloadFromTemplate(
        Employee $employee,
        EmployeeLifecycleTaskTemplate $template,
        array $overrides = [],
    ): array {
        $lifecycleType = (string) ($overrides['lifecycle_type'] ?? $template->lifecycle_type);

        if ($template->lifecycle_type !== $lifecycleType) {
            throw ValidationException::withMessages([
                'template_id' => ['The selected task template does not match the requested lifecycle type.'],
            ]);
        }

        return [
            'template_id' => $template->id,
            'lifecycle_type' => $template->lifecycle_type,
            'title' => $overrides['title'] ?? $template->title,
            'category' => $overrides['category'] ?? $template->category,
            'task_type' => array_key_exists('task_type', $overrides) ? $overrides['task_type'] : $template->task_type,
            'assignee_type' => $overrides['assignee_type'] ?? $template->assignee_type,
            'requires_approval' => $overrides['requires_approval'] ?? $template->requires_approval,
            'approval_workflow_key' => $overrides['approval_workflow_key'] ?? $template->approval_workflow_key,
            'sort_order' => $overrides['sort_order'] ?? $template->sort_order,
            'notes' => $overrides['notes'] ?? $template->notes,
            'due_date' => $overrides['due_date'] ?? $this->deriveTemplateDueDate($employee, $template),
        ];
    }

    /**
     * @param  EmployeeOnboardingTaskPayload  $payload
     * @return EmployeeOnboardingTaskPayload
     */
    private function normalizePayload(
        Employee $employee,
        User $actor,
        array $payload,
        ?EmployeeOnboardingTask $existingTask = null,
        ?string $forcedLifecycleType = null,
    ): array {
        $template = null;

        if (array_key_exists('template_id', $payload) && filled($payload['template_id'])) {
            $template = EmployeeLifecycleTaskTemplate::query()
                ->whereKey((int) $payload['template_id'])
                ->where('is_active', true)
                ->firstOrFail();

            $payload = array_merge(
                $this->buildTaskPayloadFromTemplate($employee, $template),
                $payload,
            );
        }

        $lifecycleType = $forcedLifecycleType
            ?? $payload['lifecycle_type']
            ?? $existingTask->lifecycle_type
            ?? self::DEFAULT_LIFECYCLE_TYPE;

        if ($template && $template->lifecycle_type !== $lifecycleType) {
            throw ValidationException::withMessages([
                'template_id' => ['The selected task template does not match the requested lifecycle type.'],
            ]);
        }

        $assigneeType = $payload['assignee_type'] ?? $existingTask?->assignee_type;
        $assignedToUserId = array_key_exists('assigned_to_user_id', $payload)
            ? $payload['assigned_to_user_id']
            : $existingTask?->assigned_to_user_id;

        if ($assignedToUserId === null && filled($assigneeType)) {
            $assignedToUserId = $this->resolveAssignedToUserId($employee, (string) $assigneeType);
        }

        $requiresApproval = (bool) ($payload['requires_approval'] ?? $existingTask->requires_approval ?? false);
        $approvalWorkflowKey = $payload['approval_workflow_key'] ?? $existingTask->approval_workflow_key ?? null;

        if ($requiresApproval && blank($approvalWorkflowKey)) {
            $approvalWorkflowKey = self::DEFAULT_OFFBOARDING_WORKFLOW_KEY;
        }

        $status = $payload['status'] ?? $existingTask->status ?? 'pending';
        $normalized = [
            'lifecycle_type' => $lifecycleType,
            'template_id' => $payload['template_id'] ?? $existingTask?->template_id,
            'title' => $payload['title'] ?? $existingTask?->title,
            'category' => $payload['category'] ?? $existingTask?->category,
            'task_type' => array_key_exists('task_type', $payload) ? $payload['task_type'] : $existingTask?->task_type,
            'assignee_type' => $assigneeType,
            'assigned_to_user_id' => $assignedToUserId,
            'requires_approval' => $requiresApproval,
            'approval_workflow_key' => $approvalWorkflowKey,
            'status' => $status,
            'sort_order' => $payload['sort_order'] ?? $existingTask->sort_order ?? 0,
            'due_date' => $payload['due_date'] ?? $existingTask?->due_date,
            'notes' => array_key_exists('notes', $payload) ? $payload['notes'] : $existingTask?->notes,
        ];

        if (in_array($status, self::CLOSED_STATUSES, true)) {
            $normalized['completed_at'] = $existingTask->completed_at ?? Carbon::now();
            $normalized['completed_by_user_id'] = $existingTask->completed_by_user_id ?? $actor->id;
        } elseif (array_key_exists('status', $payload)) {
            $normalized['completed_at'] = null;
            $normalized['completed_by_user_id'] = null;
            $normalized['approved_at'] = null;
        }

        return $normalized;
    }

    private function submitForApproval(Employee $employee, EmployeeOnboardingTask $task, User $actor): EmployeeOnboardingTask
    {
        if ($task->status === 'awaiting_approval' && $task->workflow_instance_id) {
            throw ValidationException::withMessages([
                'status' => ['This lifecycle task is already awaiting approval.'],
            ]);
        }

        $workflowDefinition = $this->ensureWorkflowDefinition($actor, $task->approval_workflow_key ?: self::DEFAULT_OFFBOARDING_WORKFLOW_KEY);

        $instance = $this->workflowService->startInstance($actor, [
            'workflow_definition_id' => $workflowDefinition->id,
            'reference_type' => 'employee_lifecycle_task',
            'reference_id' => (string) $task->id,
            'payload' => [
                'employee_id' => $employee->id,
                'employee_lifecycle_task_id' => $task->id,
                'lifecycle_type' => $task->lifecycle_type,
                'category' => $task->category,
                'assignee_type' => $task->assignee_type,
            ],
        ]);

        $task->forceFill([
            'status' => 'awaiting_approval',
            'workflow_instance_id' => $instance->id,
            'completed_at' => null,
            'approved_at' => null,
            'completed_by_user_id' => $task->completed_by_user_id ?: $actor->id,
            'latest_action_by_user_id' => $actor->id,
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'employee.lifecycle_task.approval_requested',
            actor: $actor,
            metadata: [
                'employee_id' => $employee->id,
                'task_id' => $task->id,
                'workflow_instance_id' => $instance->id,
                'workflow_definition_id' => $workflowDefinition->id,
                'lifecycle_type' => $task->lifecycle_type,
            ],
            entityType: 'employee_lifecycle_task',
            entityId: (string) $task->id,
        );

        return $task->refresh();
    }

    private function ensureWorkflowDefinition(User $actor, string $workflowKey): WorkflowDefinition
    {
        $definition = WorkflowDefinition::query()
            ->with(['activeVersion.stages'])
            ->where('key', $workflowKey)
            ->first();

        if ($definition?->activeVersion && $definition->status === 'published') {
            return $definition;
        }

        if ($workflowKey !== self::DEFAULT_OFFBOARDING_WORKFLOW_KEY) {
            throw ValidationException::withMessages([
                'approval_workflow_key' => ['The selected approval workflow is not published.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $definition, $workflowKey): WorkflowDefinition {
            $definition ??= WorkflowDefinition::query()->create([
                'key' => $workflowKey,
                'name' => 'Employee Offboarding Clearance Workflow',
                'module' => 'employee',
                'description' => 'Sequential offboarding approval through the employee manager and HR.',
                'is_template' => true,
                'status' => 'draft',
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $stages = [
                [
                    'key' => 'manager_clearance',
                    'name' => 'Manager Clearance',
                    'sequence' => 1,
                    'approver_type' => 'employee_manager',
                    'approver_value' => 'employee_manager',
                    'available_actions' => ['approve', 'reject', 'request_changes'],
                    'sla_hours' => 24,
                ],
                [
                    'key' => 'hr_clearance',
                    'name' => 'HR Clearance',
                    'sequence' => 2,
                    'approver_type' => 'role',
                    'approver_value' => 'hr.admin',
                    'available_actions' => ['approve', 'reject'],
                    'sla_hours' => 24,
                ],
            ];

            $version = $definition->versions()->create([
                'version' => ((int) $definition->versions()->max('version')) + 1,
                'status' => 'published',
                'definition' => [
                    'module' => 'employee',
                    'stages' => $stages,
                ],
                'created_by' => $actor->id,
                'published_at' => now(),
            ]);

            foreach ($stages as $stage) {
                $version->stages()->create($stage);
            }

            $definition->forceFill([
                'status' => 'published',
                'active_version_id' => $version->id,
                'updated_by' => $actor->id,
            ])->save();

            return $definition->refresh()->load('activeVersion.stages');
        });
    }

    private function resolveAssignedToUserId(Employee $employee, string $assigneeType): ?int
    {
        return match ($assigneeType) {
            'employee' => $employee->user_id,
            'manager' => $employee->manager?->user_id,
            'hr' => User::query()
                ->where('company_id', $employee->company_id)
                ->where('is_active', true)
                ->role('hr.admin')
                ->orderBy('id')
                ->value('id'),
            default => null,
        };
    }

    private function deriveTemplateDueDate(Employee $employee, EmployeeLifecycleTaskTemplate $template): ?string
    {
        if ($template->due_offset_days === null) {
            return null;
        }

        $referenceDate = $template->lifecycle_type === 'offboarding'
            ? ($employee->terminated_at?->copy()->startOfDay() ?? Carbon::now()->startOfDay())
            : ($employee->date_of_joining?->copy()->startOfDay() ?? Carbon::now()->startOfDay());

        return $referenceDate->addDays($template->due_offset_days)->toDateString();
    }

    private function loadTask(int $taskId): EmployeeOnboardingTask
    {
        return EmployeeOnboardingTask::query()
            ->with(['template', 'workflowInstance.tasks.assignee', 'workflowInstance.tasks.actor', 'assignedTo'])
            ->findOrFail($taskId);
    }
}
