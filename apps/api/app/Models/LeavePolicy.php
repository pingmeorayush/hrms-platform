<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $leave_type_id
 * @property int $version
 * @property string $scope_key
 * @property float $annual_allowance_days
 * @property float $opening_balance_days
 * @property string $accrual_frequency
 * @property float $carry_forward_limit_days
 * @property float $encashment_limit_days
 * @property float $max_consecutive_days
 * @property int $min_notice_days
 * @property int|null $requires_documentation_after_days
 * @property int|null $applicable_department_id
 * @property int|null $applicable_location_id
 * @property array<string, mixed>|null $eligibility_rule
 * @property string $status
 * @property-read Company|null $company
 * @property-read LeaveType|null $leaveType
 * @property-read Department|null $applicableDepartment
 * @property-read Location|null $applicableLocation
 */
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

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<LeaveType, $this>
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function applicableDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'applicable_department_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
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
