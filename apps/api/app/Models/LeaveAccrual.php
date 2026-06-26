<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property int $leave_policy_id
 * @property int $leave_type_id
 * @property int $policy_version
 * @property string $accrual_frequency
 * @property Carbon|null $period_start
 * @property Carbon|null $period_end
 * @property float $opening_balance_days
 * @property float $accrued_days
 * @property float $carry_forward_days
 * @property float $encashable_days
 * @property float $used_days_in_period
 * @property float $projected_closing_balance_days
 * @property bool $is_eligible
 * @property string $calculation_hash
 * @property string $status
 * @property array<string, mixed>|null $eligibility_snapshot
 * @property int|null $generated_by_user_id
 * @property-read Company|null $company
 * @property-read Employee|null $employee
 * @property-read LeavePolicy|null $leavePolicy
 * @property-read LeaveType|null $leaveType
 * @property-read User|null $generatedBy
 * @property-read LeaveEncashment|null $projectedEncashment
 */
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

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<LeavePolicy, $this>
     */
    public function leavePolicy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class);
    }

    /**
     * @return BelongsTo<LeaveType, $this>
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }

    /**
     * @return HasOne<LeaveEncashment, $this>
     */
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
