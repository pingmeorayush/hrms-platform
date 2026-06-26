<?php

namespace App\Modules\PayrollManagement\Services;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @phpstan-type PayslipSearchFilters array{
 *   employee_id?: int|string,
 *   payroll_run_id?: int|string,
 *   payroll_period_id?: int|string,
 *   per_page?: int|string
 * }
 */
class PayslipAccessScopeService
{
    /**
     * @param  PayslipSearchFilters  $filters
     * @return LengthAwarePaginator<int, Payslip>
     */
    public function searchPayslips(User $actor, array $filters): LengthAwarePaginator
    {
        return $this->payslipsQuery($actor, ['employee', 'payrollRun.payrollPeriod'])
            ->when(
                array_key_exists('employee_id', $filters),
                fn (Builder $builder) => $builder->where('employee_id', $filters['employee_id']),
            )
            ->when(
                array_key_exists('payroll_run_id', $filters),
                fn (Builder $builder) => $builder->where('payroll_run_id', $filters['payroll_run_id']),
            )
            ->when(
                array_key_exists('payroll_period_id', $filters),
                fn (Builder $builder) => $builder->where('payroll_period_id', $filters['payroll_period_id']),
            )
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    /**
     * @param  list<string>  $with
     */
    public function resolveAccessiblePayslip(User $actor, int $payslipId, array $with = ['employee', 'payrollRun.payrollPeriod']): Payslip
    {
        $payslip = $this->payslipsQuery($actor, $with)->find($payslipId);

        if (! $payslip) {
            throw new NotFoundHttpException;
        }

        return $payslip;
    }

    /**
     * @param  list<string>  $with
     * @return Builder<Payslip>
     */
    public function payslipsQuery(User $actor, array $with = []): Builder
    {
        $query = Payslip::query()
            ->with($with)
            ->where('status', 'generated')
            ->whereHas('payrollRun', fn (Builder $builder) => $builder->where('status', 'locked'));

        if ($this->canViewAllTenantPayslips($actor)) {
            return $query;
        }

        $linkedEmployee = $this->findLinkedEmployee($actor);

        if (! $linkedEmployee) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('employee_id', $linkedEmployee->id);
    }

    public function canViewAllTenantPayslips(User $actor): bool
    {
        return $actor->can('payroll.view') || $actor->can('compensation.view');
    }

    public function findLinkedEmployee(User $actor): ?Employee
    {
        return Employee::query()
            ->where('user_id', $actor->id)
            ->first();
    }
}
