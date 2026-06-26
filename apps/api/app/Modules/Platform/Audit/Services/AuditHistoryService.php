<?php

namespace App\Modules\Platform\Audit\Services;

use App\Models\AuditLog;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @phpstan-type OrganizationHistoryFilters array{
 *   entity_type?: string,
 *   entity_id?: int|string
 * }
 */
class AuditHistoryService
{
    private const EMPLOYEE_EVENT_TYPES = [
        'employee.record.created',
        'employee.record.updated',
        'employee.record.transferred',
        'employee.record.promoted',
        'employee.record.terminated',
    ];

    private const ORGANIZATION_ENTITY_TYPES = [
        'company',
        'department',
        'designation',
        'location',
        'cost_center',
    ];

    /**
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function paginateEmployeeHistory(Employee $employee, int $perPage = 25): LengthAwarePaginator
    {
        return AuditLog::query()
            ->with('user')
            ->where('entity_type', 'employee')
            ->where('entity_id', (string) $employee->id)
            ->whereIn('event_type', self::EMPLOYEE_EVENT_TYPES)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * @param  OrganizationHistoryFilters  $filters
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function paginateOrganizationHistory(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return AuditLog::query()
            ->with('user')
            ->whereIn('entity_type', self::ORGANIZATION_ENTITY_TYPES)
            ->where('event_type', 'like', 'organization.%')
            ->when(
                isset($filters['entity_type']),
                fn ($query) => $query->where('entity_type', $filters['entity_type']),
            )
            ->when(
                isset($filters['entity_id']),
                fn ($query) => $query->where('entity_id', (string) $filters['entity_id']),
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
