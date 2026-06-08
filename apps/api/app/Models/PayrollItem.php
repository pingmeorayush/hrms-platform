<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'payroll_run_id',
    'employee_id',
    'employee_compensation_id',
    'status',
    'employment_days',
    'unpaid_days',
    'lop_days',
    'overtime_minutes',
    'overtime_earnings',
    'gross_salary',
    'total_earnings',
    'total_deductions',
    'net_salary',
    'employer_cost',
    'earnings_breakdown',
    'deductions_breakdown',
    'employer_contribution_breakdown',
    'input_snapshot',
    'validation_errors',
    'created_by_user_id',
    'updated_by_user_id',
])]
class PayrollItem extends Model
{
    use BelongsToCompany;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employeeCompensation(): BelongsTo
    {
        return $this->belongsTo(EmployeeCompensation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'employment_days' => 'decimal:2',
            'unpaid_days' => 'decimal:2',
            'lop_days' => 'decimal:2',
            'overtime_minutes' => 'integer',
            'overtime_earnings' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'employer_cost' => 'decimal:2',
            'earnings_breakdown' => 'array',
            'deductions_breakdown' => 'array',
            'employer_contribution_breakdown' => 'array',
            'input_snapshot' => 'array',
            'validation_errors' => 'array',
        ];
    }
}
