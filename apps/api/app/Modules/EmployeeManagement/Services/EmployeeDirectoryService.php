<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EmployeeDirectoryService
{
    public function search(array $filters): LengthAwarePaginator
    {
        $employees = Employee::query()
            ->with(['department', 'designation', 'manager', 'location', 'costCenter'])
            ->when(
                filled($filters['search'] ?? null),
                fn (Builder $query) => $this->applySearch($query, (string) $filters['search']),
            )
            ->when(
                filled($filters['employment_status'] ?? null),
                fn (Builder $query) => $query->where('employment_status', (string) $filters['employment_status']),
            )
            ->when(
                filled($filters['department_id'] ?? null),
                fn (Builder $query) => $query->where('department_id', (int) $filters['department_id']),
            )
            ->when(
                filled($filters['designation_id'] ?? null),
                fn (Builder $query) => $query->where('designation_id', (int) $filters['designation_id']),
            )
            ->when(
                filled($filters['manager_id'] ?? null),
                fn (Builder $query) => $query->where('manager_id', (int) $filters['manager_id']),
            )
            ->orderBy('employee_code');

        return $employees->paginate((int) min((int) ($filters['per_page'] ?? 25), 100));
    }

    private function applySearch(Builder $query, string $search): void
    {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        $tokens = $this->extractNameTokens($search);

        $query->where(function (Builder $builder) use ($search, $tokens): void {
            $prefix = $search.'%';

            $builder
                ->where('employee_code', 'like', $prefix)
                ->orWhere('email', 'like', $prefix)
                ->orWhere('first_name', 'like', $prefix)
                ->orWhere('last_name', 'like', $prefix);

            if ($tokens->count() >= 2) {
                $builder->orWhere(function (Builder $nameQuery) use ($tokens): void {
                    $nameQuery
                        ->where('first_name', 'like', $tokens[0].'%')
                        ->where('last_name', 'like', $tokens[1].'%');
                });
            }
        });
    }

    /**
     * @return Collection<int, string>
     */
    private function extractNameTokens(string $search): Collection
    {
        /** @var array<int, string> $parts */
        $parts = preg_split('/[\s._-]+/', trim($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return collect($parts)->take(2)->values();
    }
}
