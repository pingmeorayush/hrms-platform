<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'employee_id',
    'leave_type_id',
    'leave_policy_id',
    'policy_version',
    'available_days',
    'booked_days',
    'used_days',
    'accrued_days',
    'carry_forward_days',
    'projected_encashable_days',
    'current_period_start',
    'current_period_end',
    'last_calculation_hash',
    'status',
])]
class LeaveBalance extends Model
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

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function leavePolicy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LeaveBalanceEntry::class)->orderByDesc('effective_on')->orderByDesc('id');
    }

    protected function casts(): array
    {
        return [
            'available_days' => 'float',
            'booked_days' => 'float',
            'used_days' => 'float',
            'accrued_days' => 'float',
            'carry_forward_days' => 'float',
            'projected_encashable_days' => 'float',
            'current_period_start' => 'date',
            'current_period_end' => 'date',
        ];
    }
}
