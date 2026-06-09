<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Employee;
use App\Models\EmployeeLifecycleTaskTemplate;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeLifecycleTaskTemplateService
{
    public function __construct(
        private readonly EmployeeOnboardingService $employeeOnboardingService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function list(array $filters = []): Collection
    {
        return EmployeeLifecycleTaskTemplate::query()
            ->when(
                array_key_exists('lifecycle_type', $filters),
                fn ($query) => $query->where('lifecycle_type', $filters['lifecycle_type']),
            )
            ->when(
                array_key_exists('is_active', $filters),
                fn ($query) => $query->where('is_active', (bool) $filters['is_active']),
            )
            ->orderBy('lifecycle_type')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function create(User $actor, array $payload): EmployeeLifecycleTaskTemplate
    {
        return DB::transaction(function () use ($actor, $payload): EmployeeLifecycleTaskTemplate {
            $template = EmployeeLifecycleTaskTemplate::query()->create([
                ...$payload,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
                'requires_approval' => (bool) ($payload['requires_approval'] ?? false),
                'is_active' => (bool) ($payload['is_active'] ?? true),
            ]);

            $this->auditLogger->record(
                eventType: 'employee.lifecycle_task_template.created',
                actor: $actor,
                metadata: [
                    'template_id' => $template->id,
                    'lifecycle_type' => $template->lifecycle_type,
                    'requires_approval' => $template->requires_approval,
                ],
                entityType: 'employee_lifecycle_task_template',
                entityId: (string) $template->id,
            );

            return $template->refresh();
        });
    }

    public function update(EmployeeLifecycleTaskTemplate $template, User $actor, array $payload): EmployeeLifecycleTaskTemplate
    {
        return DB::transaction(function () use ($template, $actor, $payload): EmployeeLifecycleTaskTemplate {
            $before = $template->only([
                'name',
                'lifecycle_type',
                'title',
                'category',
                'task_type',
                'assignee_type',
                'requires_approval',
                'approval_workflow_key',
                'due_offset_days',
                'sort_order',
                'notes',
                'is_active',
            ]);

            $template->fill([
                ...$payload,
                'updated_by_user_id' => $actor->id,
            ]);
            $template->save();

            $this->auditLogger->record(
                eventType: 'employee.lifecycle_task_template.updated',
                actor: $actor,
                metadata: [
                    'template_id' => $template->id,
                    'before' => $before,
                    'after' => $template->only(array_keys($before)),
                ],
                entityType: 'employee_lifecycle_task_template',
                entityId: (string) $template->id,
            );

            return $template->refresh();
        });
    }

    public function apply(Employee $employee, User $actor, array $payload): Collection
    {
        return DB::transaction(function () use ($employee, $actor, $payload): Collection {
            $templates = EmployeeLifecycleTaskTemplate::query()
                ->whereIn('id', $payload['template_ids'])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($templates->count() !== count($payload['template_ids'])) {
                throw ValidationException::withMessages([
                    'template_ids' => ['One or more selected lifecycle task templates are invalid or inactive.'],
                ]);
            }

            $tasks = $templates->map(function (EmployeeLifecycleTaskTemplate $template) use ($employee, $actor) {
                return $this->employeeOnboardingService->create(
                    $employee,
                    $actor,
                    $this->employeeOnboardingService->buildTaskPayloadFromTemplate($employee, $template),
                    $template->lifecycle_type,
                );
            });

            $this->auditLogger->record(
                eventType: 'employee.lifecycle_task_template.applied',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'template_ids' => $templates->pluck('id')->all(),
                    'task_ids' => $tasks->pluck('id')->all(),
                ],
                entityType: 'employee',
                entityId: (string) $employee->id,
            );

            return $tasks;
        });
    }
}
