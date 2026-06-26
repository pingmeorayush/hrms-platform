<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Employee;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-type EmployeeProfileDetailOrdering array<string, string>
 * @phpstan-type EmployeeProfileDetailPayload array<string, mixed>
 */
class EmployeeProfileDetailService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  EmployeeProfileDetailOrdering  $ordering
     * @return Collection<int, Model>
     */
    public function list(Employee $employee, string $relation, array $ordering): Collection
    {
        $query = $employee->{$relation}();

        foreach ($ordering as $column => $direction) {
            $query->orderBy($column, $direction);
        }

        return $query->get();
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  EmployeeProfileDetailPayload  $payload
     */
    public function create(
        Employee $employee,
        User $actor,
        string $modelClass,
        array $payload,
        string $eventType,
        string $entityType,
        ?callable $beforeSave = null,
    ): Model {
        return DB::transaction(function () use ($employee, $actor, $modelClass, $payload, $eventType, $entityType, $beforeSave): Model {
            if ($beforeSave) {
                $beforeSave($employee, $payload);
            }

            /** @var Model $record */
            $record = $modelClass::query()->create([
                ...$payload,
                'employee_id' => $employee->id,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: $eventType,
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'record_id' => $record->getKey(),
                ],
                entityType: $entityType,
                entityId: (string) $record->getKey(),
            );

            return $record->refresh();
        });
    }

    /**
     * @param  EmployeeProfileDetailPayload  $payload
     * @param  list<string>  $trackedFields
     */
    public function update(
        Employee $employee,
        Model $record,
        User $actor,
        array $payload,
        string $eventType,
        string $entityType,
        array $trackedFields,
        ?callable $beforeSave = null,
    ): Model {
        return DB::transaction(function () use ($employee, $record, $actor, $payload, $eventType, $entityType, $trackedFields, $beforeSave): Model {
            $before = $record->only($trackedFields);

            if ($beforeSave) {
                $beforeSave($employee, $payload, $record);
            }

            $record->fill([
                ...$payload,
                'updated_by_user_id' => $actor->id,
            ]);
            $record->save();

            $this->auditLogger->record(
                eventType: $eventType,
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'record_id' => $record->getKey(),
                    'before' => $before,
                    'after' => $record->only($trackedFields),
                ],
                entityType: $entityType,
                entityId: (string) $record->getKey(),
            );

            return $record->refresh();
        });
    }
}
