<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'company_id',
    'employee_id',
    'leave_policy_id',
    'leave_type_id',
    'policy_version',
    'accrual_frequency',
    'period_start',
    'period_end',
    'opening_balance_days',
    'accrued_days',
    'carry_forward_days',
    'encashable_days',
    'used_days_in_period',
    'projected_closing_balance_days',
    'is_eligible',
    'calculation_hash',
    'status',
    'eligibility_snapshot',
    'generated_by_user_id',
])]
class LeaveAccrual extends Model
{
    use BelongsToCompany;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leavePolicy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }

    public function projectedEncashment(): HasOne
    {
        return $this->hasOne(LeaveEncashment::class);
    }

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'opening_balance_days' => 'float',
            'accrued_days' => 'float',
            'carry_forward_days' => 'float',
            'encashable_days' => 'float',
            'used_days_in_period' => 'float',
            'projected_closing_balance_days' => 'float',
            'is_eligible' => 'boolean',
            'eligibility_snapshot' => 'array',
        ];
    }
}
