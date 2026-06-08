<?php

namespace App\Modules\Platform\Workflow\Services;

use App\Models\Employee;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStage;
use App\Models\WorkflowTask;
use App\Models\WorkflowVersion;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Workflow\Events\WorkflowInstanceTransitioned;
use App\Modules\Platform\Workflow\Events\WorkflowTaskAssigned;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly Dispatcher $events,
    ) {}

    public function createDefinition(User $actor, array $payload): WorkflowDefinition
    {
        return DB::transaction(function () use ($actor, $payload): WorkflowDefinition {
            $definition = WorkflowDefinition::query()->create([
                'key' => $payload['key'],
                'name' => $payload['name'],
                'module' => $payload['module'],
                'description' => $payload['description'] ?? null,
                'is_template' => $payload['is_template'] ?? false,
                'status' => ($payload['publish'] ?? false) ? 'published' : 'draft',
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $version = $this->createVersion($definition, $payload['stages'], $actor);

            if ($payload['publish'] ?? false) {
                $this->publishDefinition($definition, $version, $actor);
            }

            $this->auditLogger->record(
                eventType: 'workflow.definition.created',
                actor: $actor,
                metadata: ['workflow_definition_id' => $definition->id, 'version' => $version->version],
                entityType: 'workflow_definition',
                entityId: (string) $definition->id,
            );

            return $definition->load('activeVersion.stages', 'versions');
        });
    }

    public function updateDefinition(WorkflowDefinition $definition, User $actor, array $payload): WorkflowDefinition
    {
        return DB::transaction(function () use ($definition, $actor, $payload): WorkflowDefinition {
            $action = $payload['action'];

            if ($action === 'publish') {
                $version = $definition->versions()
                    ->when(
                        isset($payload['version']),
                        fn (Builder $query) => $query->where('version', $payload['version']),
                    )
                    ->orderByDesc('version')
                    ->firstOrFail();

                $this->publishDefinition($definition, $version, $actor);
            }

            if ($action === 'new_version') {
                $stages = $payload['stages'] ?? $definition->activeVersion?->stages?->map(fn (WorkflowStage $stage) => [
                    'key' => $stage->key,
                    'name' => $stage->name,
                    'sequence' => $stage->sequence,
                    'approver_type' => $stage->approver_type,
                    'approver_value' => $stage->approver_value,
                    'available_actions' => $stage->available_actions,
                    'sla_hours' => $stage->sla_hours,
                    'metadata' => $stage->metadata,
                ])->all();

                if (empty($stages)) {
                    throw ValidationException::withMessages([
                        'stages' => ['A new workflow version requires at least one stage.'],
                    ]);
                }

                $version = $this->createVersion($definition, $stages, $actor);

                if ($payload['publish'] ?? false) {
                    $this->publishDefinition($definition, $version, $actor);
                }

                $this->auditLogger->record(
                    eventType: 'workflow.definition.versioned',
                    actor: $actor,
                    metadata: ['workflow_definition_id' => $definition->id, 'version' => $version->version],
                    entityType: 'workflow_definition',
                    entityId: (string) $definition->id,
                );
            }

            if ($action === 'archive') {
                $definition->forceFill([
                    'status' => 'archived',
                    'updated_by' => $actor->id,
                ])->save();

                $this->auditLogger->record(
                    eventType: 'workflow.definition.archived',
                    actor: $actor,
                    metadata: ['workflow_definition_id' => $definition->id],
                    entityType: 'workflow_definition',
                    entityId: (string) $definition->id,
                );
            }

            return $definition->refresh()->load('activeVersion.stages', 'versions');
        });
    }

    public function startInstance(User $actor, array $payload): WorkflowInstance
    {
        return DB::transaction(function () use ($actor, $payload): WorkflowInstance {
            $definition = WorkflowDefinition::query()
                ->with(['activeVersion.stages'])
                ->when(
                    isset($payload['workflow_definition_id']),
                    fn (Builder $query) => $query->where('id', $payload['workflow_definition_id']),
                    fn (Builder $query) => $query->where('key', $payload['workflow_key']),
                )
                ->firstOrFail();

            if (! $definition->activeVersion || $definition->status !== 'published') {
                throw ValidationException::withMessages([
                    'workflow' => ['The selected workflow does not have a published active version.'],
                ]);
            }

            $firstStage = $definition->activeVersion->stages->sortBy('sequence')->first();

            if (! $firstStage) {
                throw ValidationException::withMessages([
                    'workflow' => ['The selected workflow version does not contain any stages.'],
                ]);
            }

            $instance = WorkflowInstance::query()->create([
                'workflow_definition_id' => $definition->id,
                'workflow_version_id' => $definition->activeVersion->id,
                'reference_type' => $payload['reference_type'],
                'reference_id' => $payload['reference_id'],
                'status' => 'running',
                'current_stage_sequence' => $firstStage->sequence,
                'started_by_user_id' => $actor->id,
                'payload' => $payload['payload'] ?? [],
            ]);

            $this->createTaskForStage($instance, $firstStage);

            $this->auditLogger->record(
                eventType: 'workflow.instance.started',
                actor: $actor,
                metadata: [
                    'workflow_instance_id' => $instance->id,
                    'workflow_definition_id' => $definition->id,
                ],
                entityType: 'workflow_instance',
                entityId: (string) $instance->id,
            );

            return $instance->load('definition.activeVersion.stages', 'tasks.assignee', 'starter');
        });
    }

    public function decideTask(WorkflowTask $task, User $actor, array $payload): WorkflowTask
    {
        return DB::transaction(function () use ($task, $actor, $payload): WorkflowTask {
            $task->loadMissing('instance.definition', 'instance.version.stages', 'instance.starter', 'assignee');

            if ($task->status !== 'open') {
                throw ValidationException::withMessages([
                    'task' => ['This workflow task is no longer open.'],
                ]);
            }

            if ($task->assigned_to_user_id !== $actor->id && ! $actor->can('workflow.admin')) {
                throw ValidationException::withMessages([
                    'task' => ['You are not allowed to act on this task.'],
                ]);
            }

            $action = $payload['action'];

            if (! in_array($action, $task->available_actions ?? [], true)) {
                throw ValidationException::withMessages([
                    'action' => ['This action is not available for the selected task.'],
                ]);
            }

            $task->forceFill([
                'status' => $action === 'approve' ? 'completed' : 'closed',
                'decision' => $action,
                'decision_comment' => $payload['comment'] ?? null,
                'acted_by_user_id' => $actor->id,
                'acted_at' => now(),
            ])->save();

            $instance = $task->instance;

            if ($action === 'approve') {
                $nextStage = $instance->version->stages
                    ->sortBy('sequence')
                    ->firstWhere('sequence', '>', $task->sequence);

                if ($nextStage) {
                    $instance->forceFill([
                        'status' => 'running',
                        'current_stage_sequence' => $nextStage->sequence,
                    ])->save();

                    $this->createTaskForStage($instance, $nextStage);
                } else {
                    $instance->forceFill([
                        'status' => 'completed',
                        'current_stage_sequence' => null,
                        'completed_at' => now(),
                    ])->save();

                    $this->events->dispatch(new WorkflowInstanceTransitioned($instance, 'completed'));

                    $this->auditLogger->record(
                        eventType: 'workflow.instance.completed',
                        actor: $actor,
                        metadata: ['workflow_instance_id' => $instance->id],
                        entityType: 'workflow_instance',
                        entityId: (string) $instance->id,
                    );
                }
            }

            if (in_array($action, ['reject', 'request_changes'], true)) {
                $instance->forceFill([
                    'status' => $action === 'reject' ? 'rejected' : 'waiting',
                    'current_stage_sequence' => null,
                    'rejected_at' => now(),
                ])->save();

                $this->events->dispatch(new WorkflowInstanceTransitioned($instance, $action));

                $this->auditLogger->record(
                    eventType: 'workflow.instance.'.$action,
                    actor: $actor,
                    metadata: ['workflow_instance_id' => $instance->id],
                    entityType: 'workflow_instance',
                    entityId: (string) $instance->id,
                );
            }

            $this->auditLogger->record(
                eventType: 'workflow.task.'.$action,
                actor: $actor,
                metadata: ['workflow_task_id' => $task->id, 'workflow_instance_id' => $instance->id],
                entityType: 'workflow_task',
                entityId: (string) $task->id,
            );

            return $task->refresh()->load('instance.tasks.assignee', 'assignee', 'actor');
        });
    }

    public function cancelInstance(WorkflowInstance $instance, User $actor, ?string $comment = null): WorkflowInstance
    {
        return DB::transaction(function () use ($instance, $actor, $comment): WorkflowInstance {
            $instance->loadMissing('tasks', 'starter');

            $instance->tasks
                ->where('status', 'open')
                ->each(function (WorkflowTask $task) use ($actor, $comment): void {
                    $task->forceFill([
                        'status' => 'closed',
                        'decision' => 'cancelled',
                        'decision_comment' => $comment,
                        'acted_by_user_id' => $actor->id,
                        'acted_at' => now(),
                    ])->save();
                });

            $instance->forceFill([
                'status' => 'cancelled',
                'current_stage_sequence' => null,
            ])->save();

            $this->auditLogger->record(
                eventType: 'workflow.instance.cancelled',
                actor: $actor,
                metadata: ['workflow_instance_id' => $instance->id],
                entityType: 'workflow_instance',
                entityId: (string) $instance->id,
            );

            return $instance->refresh()->load('definition', 'tasks.assignee', 'starter');
        });
    }

    private function createVersion(WorkflowDefinition $definition, array $stages, User $actor): WorkflowVersion
    {
        $nextVersion = ((int) $definition->versions()->max('version')) + 1;

        $version = $definition->versions()->create([
            'version' => $nextVersion,
            'status' => 'draft',
            'definition' => [
                'module' => $definition->module,
                'stages' => $stages,
            ],
            'created_by' => $actor->id,
        ]);

        foreach ($stages as $stage) {
            $version->stages()->create([
                'key' => $stage['key'],
                'name' => $stage['name'],
                'sequence' => $stage['sequence'],
                'approver_type' => $stage['approver_type'],
                'approver_value' => (string) $stage['approver_value'],
                'available_actions' => $stage['available_actions'] ?? ['approve', 'reject'],
                'sla_hours' => $stage['sla_hours'] ?? null,
                'metadata' => $stage['metadata'] ?? [],
            ]);
        }

        return $version;
    }

    private function publishDefinition(WorkflowDefinition $definition, WorkflowVersion $version, User $actor): void
    {
        $version->forceFill([
            'status' => 'published',
            'published_at' => now(),
        ])->save();

        $definition->forceFill([
            'status' => 'published',
            'active_version_id' => $version->id,
            'updated_by' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'workflow.definition.published',
            actor: $actor,
            metadata: ['workflow_definition_id' => $definition->id, 'version' => $version->version],
            entityType: 'workflow_definition',
            entityId: (string) $definition->id,
        );
    }

    private function createTaskForStage(WorkflowInstance $instance, WorkflowStage $stage): WorkflowTask
    {
        $assignee = $this->resolveApprover($instance, $stage);

        $task = WorkflowTask::query()->create([
            'workflow_instance_id' => $instance->id,
            'workflow_stage_id' => $stage->id,
            'stage_key' => $stage->key,
            'stage_name' => $stage->name,
            'sequence' => $stage->sequence,
            'assigned_to_user_id' => $assignee->id,
            'assigned_to_role' => $stage->approver_type === 'role' ? $stage->approver_value : null,
            'status' => 'open',
            'available_actions' => $stage->available_actions ?? ['approve', 'reject'],
            'due_at' => $stage->sla_hours ? now()->addHours($stage->sla_hours) : null,
            'metadata' => $stage->metadata ?? [],
        ]);

        $this->events->dispatch(new WorkflowTaskAssigned($task));

        $this->auditLogger->record(
            eventType: 'workflow.task.assigned',
            actor: $instance->starter,
            metadata: [
                'workflow_task_id' => $task->id,
                'assigned_to_user_id' => $assignee->id,
            ],
            entityType: 'workflow_task',
            entityId: (string) $task->id,
        );

        return $task;
    }

    private function resolveApprover(WorkflowInstance $instance, WorkflowStage $stage): User
    {
        $assignee = match ($stage->approver_type) {
            'user' => User::query()->find($stage->approver_value),
            'employee_manager' => $this->resolveEmployeeManagerApprover($instance),
            default => User::query()
                ->where('is_active', true)
                ->role($stage->approver_value)
                ->orderBy('id')
                ->first(),
        };

        if (! $assignee) {
            throw ValidationException::withMessages([
                'workflow' => ["No approver could be resolved for stage {$stage->name}."],
            ]);
        }

        return $assignee;
    }

    private function resolveEmployeeManagerApprover(WorkflowInstance $instance): ?User
    {
        $employeeId = $instance->payload['employee_id'] ?? null;

        if ($employeeId === null) {
            return null;
        }

        $employee = Employee::query()
            ->with('manager.user')
            ->find($employeeId);

        $managerUser = $employee?->manager?->user;

        if (! $managerUser?->is_active) {
            return null;
        }

        return $managerUser;
    }
}
