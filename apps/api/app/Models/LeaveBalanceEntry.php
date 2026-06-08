<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'leave_balance_id',
    'employee_id',
    'leave_type_id',
    'leave_policy_id',
    'entry_type',
    'quantity_days',
    'balance_before_days',
    'balance_after_days',
    'effective_on',
    'reference_type',
    'reference_id',
    'metadata',
    'created_by_user_id',
])]
class LeaveBalanceEntry extends Model
{
    use BelongsToCompany;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function leaveBalance(): BelongsTo
    {
        return $this->belongsTo(LeaveBalance::class);
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'quantity_days' => 'float',
            'balance_before_days' => 'float',
            'balance_after_days' => 'float',
            'effective_on' => 'date',
            'metadata' => 'array',
        ];
    }
}
