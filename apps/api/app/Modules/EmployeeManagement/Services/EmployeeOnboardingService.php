<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Employee;
use App\Models\EmployeeOnboardingTask;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeOnboardingService
{
    private const CLOSED_STATUSES = ['completed', 'skipped'];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function listForEmployee(Employee $employee, User $actor): array
    {
        $tasks = $employee->onboardingTasks()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $summary = $this->summarize($tasks);

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

        return [$tasks, $summary];
    }

    public function create(Employee $employee, User $actor, array $payload): EmployeeOnboardingTask
    {
        return DB::transaction(function () use ($employee, $actor, $payload): EmployeeOnboardingTask {
            $normalizedPayload = $this->normalizePayload($payload);

            $task = $employee->onboardingTasks()->create([
                ...$normalizedPayload,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

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

            return $task->refresh();
        });
    }

    public function update(Employee $employee, EmployeeOnboardingTask $task, User $actor, array $payload): EmployeeOnboardingTask
    {
        return DB::transaction(function () use ($employee, $task, $actor, $payload): EmployeeOnboardingTask {
            $trackedFields = [
                'title',
                'category',
                'task_type',
                'assignee_type',
                'status',
                'sort_order',
                'due_date',
                'completed_at',
                'notes',
            ];
            $before = $task->only($trackedFields);
            $normalizedPayload = $this->normalizePayload($payload, $task);

            $task->fill([
                ...$normalizedPayload,
                'updated_by_user_id' => $actor->id,
            ]);
            $task->save();

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

            return $task->refresh();
        });
    }

    public function listIncompleteStatuses(User $actor): Collection
    {
        $employees = Employee::query()
            ->with(['department', 'designation'])
            ->withCount([
                'onboardingTasks as onboarding_task_count',
                'onboardingTasks as closed_onboarding_task_count' => function (Builder $query): void {
                    $query->whereIn('status', self::CLOSED_STATUSES);
                },
                'onboardingTasks as incomplete_onboarding_task_count' => function (Builder $query): void {
                    $query->whereNotIn('status', self::CLOSED_STATUSES);
                },
            ])
            ->has('onboardingTasks')
            ->whereHas('onboardingTasks', function (Builder $query): void {
                $query->whereNotIn('status', self::CLOSED_STATUSES);
            })
            ->orderBy('date_of_joining')
            ->orderBy('employee_code')
            ->get();

        $this->auditLogger->record(
            eventType: 'employee.onboarding_status.viewed',
            actor: $actor,
            metadata: [
                'employee_count' => $employees->count(),
            ],
            entityType: 'employee_onboarding_status',
            entityId: (string) $actor->company_id,
        );

        return $employees;
    }

    public function summarize(Collection $tasks): array
    {
        $totalCount = $tasks->count();
        $completedCount = $tasks->where('status', 'completed')->count();
        $skippedCount = $tasks->where('status', 'skipped')->count();
        $pendingCount = $tasks->where('status', 'pending')->count();
        $inProgressCount = $tasks->where('status', 'in_progress')->count();
        $closedCount = $completedCount + $skippedCount;

        return [
            'total_count' => $totalCount,
            'completed_count' => $completedCount,
            'skipped_count' => $skippedCount,
            'pending_count' => $pendingCount,
            'in_progress_count' => $inProgressCount,
            'incomplete_count' => $pendingCount + $inProgressCount,
            'progress_percentage' => $totalCount === 0 ? 0 : (int) round(($closedCount / $totalCount) * 100),
            'is_complete' => $totalCount > 0 && ($pendingCount + $inProgressCount) === 0,
        ];
    }

    public function summarizeEmployee(Employee $employee): array
    {
        $totalCount = (int) $employee->onboarding_task_count;
        $closedCount = (int) $employee->closed_onboarding_task_count;
        $incompleteCount = (int) $employee->incomplete_onboarding_task_count;

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
            'summary' => [
                'total_count' => $totalCount,
                'closed_count' => $closedCount,
                'incomplete_count' => $incompleteCount,
                'progress_percentage' => $totalCount === 0 ? 0 : (int) round(($closedCount / $totalCount) * 100),
                'is_complete' => $totalCount > 0 && $incompleteCount === 0,
            ],
        ];
    }

    private function normalizePayload(array $payload, ?EmployeeOnboardingTask $existingTask = null): array
    {
        $status = $payload['status'] ?? $existingTask?->status ?? 'pending';
        $normalized = $payload;

        if (in_array($status, self::CLOSED_STATUSES, true)) {
            $normalized['completed_at'] = $existingTask?->completed_at ?? Carbon::now();
        } elseif (array_key_exists('status', $payload)) {
            $normalized['completed_at'] = null;
        }

        if (! array_key_exists('status', $normalized) && $existingTask === null) {
            $normalized['status'] = 'pending';
        }

        return $normalized;
    }
}
