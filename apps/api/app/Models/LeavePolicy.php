<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'leave_type_id',
    'version',
    'scope_key',
    'annual_allowance_days',
    'opening_balance_days',
    'accrual_frequency',
    'carry_forward_limit_days',
    'encashment_limit_days',
    'max_consecutive_days',
    'min_notice_days',
    'requires_documentation_after_days',
    'applicable_department_id',
    'applicable_location_id',
    'eligibility_rule',
    'status',
])]
class LeavePolicy extends Model
{
    use BelongsToCompany;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function applicableDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'applicable_department_id');
    }

    public function applicableLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'applicable_location_id');
    }

    protected function casts(): array
    {
        return [
            'annual_allowance_days' => 'float',
            'opening_balance_days' => 'float',
            'carry_forward_limit_days' => 'float',
            'encashment_limit_days' => 'float',
            'max_consecutive_days' => 'float',
            'requires_documentation_after_days' => 'integer',
            'min_notice_days' => 'integer',
            'eligibility_rule' => 'array',
        ];
    }
}
