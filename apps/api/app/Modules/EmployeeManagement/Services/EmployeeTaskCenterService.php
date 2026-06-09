<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\AssetAssignment;
use App\Models\EmployeeOnboardingTask;
use App\Models\PolicyAcknowledgement;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EmployeeTaskCenterService
{
    private const CLOSED_TASK_STATUSES = ['completed', 'skipped'];

    public function __construct(
        private readonly EmployeeSelfServiceAccessScopeService $selfServiceAccessScopeService,
        private readonly EmployeeOnboardingService $employeeOnboardingService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function overview(User $actor): array
    {
        $employee = $this->selfServiceAccessScopeService->resolveLinkedEmployee($actor);

        $policyAcknowledgements = PolicyAcknowledgement::query()
            ->with('document')
            ->where('employee_id', $employee->id)
            ->orderByRaw("case when status = 'assigned' then 0 else 1 end")
            ->orderBy('due_date')
            ->orderByDesc('id')
            ->get();

        $lifecycleTasks = EmployeeOnboardingTask::query()
            ->with(['assignedTo', 'workflowInstance.tasks.assignee', 'workflowInstance.tasks.actor'])
            ->where('employee_id', $employee->id)
            ->where(function ($query) use ($actor): void {
                $query->where('assigned_to_user_id', $actor->id)
                    ->orWhere(function ($nested): void {
                        $nested->whereNull('assigned_to_user_id')
                            ->where('assignee_type', 'employee');
                    });
            })
            ->whereNotIn('status', self::CLOSED_TASK_STATUSES)
            ->orderBy('due_date')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $assetAssignments = AssetAssignment::query()
            ->with(['asset.assetCategory'])
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['assigned', 'issued'])
            ->orderBy('expected_return_date')
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->get();

        $items = collect()
            ->concat($policyAcknowledgements->map(fn (PolicyAcknowledgement $acknowledgement): array => $this->mapPolicyAcknowledgement($acknowledgement)))
            ->concat($lifecycleTasks->map(fn (EmployeeOnboardingTask $task): array => $this->mapLifecycleTask($task)))
            ->concat($assetAssignments->map(fn (AssetAssignment $assignment): array => $this->mapAssetAssignment($assignment)))
            ->sortBy([
                fn (array $item) => $item['status_priority'],
                fn (array $item) => $item['due_date_sort'] ?? '9999-12-31',
                fn (array $item) => $item['title'],
            ])
            ->values()
            ->map(function (array $item): array {
                unset($item['status_priority'], $item['due_date_sort']);

                return $item;
            })
            ->all();

        $summary = [
            'total_count' => count($items),
            'pending_count' => collect($items)->whereIn('status', ['assigned', 'pending', 'in_progress', 'awaiting_approval', 'changes_requested', 'rejected'])->count(),
            'policy_count' => $policyAcknowledgements->count(),
            'lifecycle_task_count' => $lifecycleTasks->count(),
            'asset_count' => $assetAssignments->count(),
        ];

        $this->auditLogger->record(
            eventType: 'employee.task_center.viewed',
            actor: $actor,
            metadata: [
                'employee_id' => $employee->id,
                'summary' => $summary,
            ],
            entityType: 'employee_task_center',
            entityId: (string) $employee->id,
        );

        return [
            'employee' => [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
            ],
            'summary' => $summary,
            'items' => $items,
        ];
    }

    public function updateLifecycleTask(User $actor, int $taskId, array $payload): EmployeeOnboardingTask
    {
        $employee = $this->selfServiceAccessScopeService->resolveLinkedEmployee($actor);

        $task = EmployeeOnboardingTask::query()
            ->where('employee_id', $employee->id)
            ->whereKey($taskId)
            ->where(function ($query) use ($actor): void {
                $query->where('assigned_to_user_id', $actor->id)
                    ->orWhere(function ($nested): void {
                        $nested->whereNull('assigned_to_user_id')
                            ->where('assignee_type', 'employee');
                    });
            })
            ->firstOrFail();

        if (in_array($task->status, self::CLOSED_TASK_STATUSES, true)) {
            throw ValidationException::withMessages([
                'task' => ['This lifecycle task is already closed.'],
            ]);
        }

        return $this->employeeOnboardingService->update(
            $employee,
            $task,
            $actor,
            $payload,
            $task->lifecycle_type,
        );
    }

    private function mapPolicyAcknowledgement(PolicyAcknowledgement $acknowledgement): array
    {
        return [
            'id' => 'policy-'.$acknowledgement->id,
            'source_type' => 'policy_acknowledgement',
            'source_id' => $acknowledgement->id,
            'action_domain' => 'document',
            'lifecycle_type' => null,
            'title' => $acknowledgement->policy_title,
            'subtitle' => $acknowledgement->policy_version ? 'Version '.$acknowledgement->policy_version : 'Policy acknowledgement',
            'status' => $acknowledgement->status,
            'due_date' => $acknowledgement->due_date?->toDateString(),
            'due_state' => $this->resolveDueState($acknowledgement->due_date, $acknowledgement->status),
            'actionable' => $acknowledgement->status === 'assigned',
            'action' => $acknowledgement->status === 'assigned' ? 'acknowledge_policy' : null,
            'links' => [
                'download' => route('policy.acknowledgements.download', [
                    'policyAcknowledgementId' => $acknowledgement->id,
                ], false),
                'acknowledge' => $acknowledgement->status === 'assigned'
                    ? '/api/v1/policy-acknowledgements/'.$acknowledgement->id.'/acknowledge'
                    : null,
            ],
            'metadata' => [
                'document_id' => $acknowledgement->document_id,
                'original_file_name' => $acknowledgement->document?->original_file_name,
            ],
            'status_priority' => $acknowledgement->status === 'assigned' ? 0 : 3,
            'due_date_sort' => $acknowledgement->due_date?->toDateString(),
        ];
    }

    private function mapLifecycleTask(EmployeeOnboardingTask $task): array
    {
        return [
            'id' => 'lifecycle-task-'.$task->id,
            'source_type' => 'lifecycle_task',
            'source_id' => $task->id,
            'action_domain' => $this->resolveTaskActionDomain($task),
            'lifecycle_type' => $task->lifecycle_type,
            'title' => $task->title,
            'subtitle' => ucfirst($task->lifecycle_type).' task',
            'status' => $task->status,
            'due_date' => $task->due_date?->toDateString(),
            'due_state' => $this->resolveDueState($task->due_date, $task->status),
            'actionable' => true,
            'action' => 'update_lifecycle_task',
            'links' => [
                'update' => '/api/v1/task-center/lifecycle-tasks/'.$task->id,
            ],
            'metadata' => [
                'category' => $task->category,
                'task_type' => $task->task_type,
                'requires_approval' => (bool) $task->requires_approval,
                'workflow_instance_id' => $task->workflow_instance_id,
            ],
            'status_priority' => $task->status === 'rejected' ? 0 : ($task->status === 'changes_requested' ? 1 : 2),
            'due_date_sort' => $task->due_date?->toDateString(),
        ];
    }

    private function mapAssetAssignment(AssetAssignment $assignment): array
    {
        $asset = $assignment->asset;

        return [
            'id' => 'asset-assignment-'.$assignment->id,
            'source_type' => 'asset_assignment',
            'source_id' => $assignment->id,
            'action_domain' => 'asset',
            'lifecycle_type' => null,
            'title' => $asset?->name ?? 'Assigned asset',
            'subtitle' => $asset?->asset_tag ? 'Asset tag '.$asset->asset_tag : 'Assigned asset',
            'status' => $assignment->status,
            'due_date' => $assignment->expected_return_date?->toDateString(),
            'due_state' => $this->resolveDueState($assignment->expected_return_date, $assignment->status),
            'actionable' => false,
            'action' => null,
            'links' => [],
            'metadata' => [
                'asset_id' => $assignment->asset_id,
                'asset_tag' => $asset?->asset_tag,
                'asset_type' => $asset?->asset_type,
                'category' => $asset?->assetCategory?->name,
                'assigned_at' => $assignment->assigned_at?->toIso8601String(),
                'issued_at' => $assignment->issued_at?->toIso8601String(),
            ],
            'status_priority' => 4,
            'due_date_sort' => $assignment->expected_return_date?->toDateString(),
        ];
    }

    private function resolveTaskActionDomain(EmployeeOnboardingTask $task): string
    {
        if (in_array($task->task_type, ['submit_documents', 'read_policy', 'complete_forms'], true)) {
            return 'document';
        }

        if (in_array($task->task_type, ['setup_equipment'], true) || in_array($task->category, ['it', 'facilities', 'security'], true)) {
            return 'asset';
        }

        return $task->lifecycle_type;
    }

    private function resolveDueState(?Carbon $dueDate, string $status): string
    {
        if ($dueDate === null) {
            return 'no_due_date';
        }

        if (in_array($status, ['completed', 'skipped', 'acknowledged'], true)) {
            return 'closed';
        }

        $today = Carbon::today();

        if ($dueDate->lt($today)) {
            return 'overdue';
        }

        if ($dueDate->equalTo($today)) {
            return 'due_today';
        }

        return 'upcoming';
    }
}
