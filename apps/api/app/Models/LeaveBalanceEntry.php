<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $leave_balance_id
 * @property int $employee_id
 * @property int $leave_type_id
 * @property int $leave_policy_id
 * @property string $entry_type
 * @property float $quantity_days
 * @property float $balance_before_days
 * @property float $balance_after_days
 * @property Carbon|null $effective_on
 * @property string|null $reference_type
 * @property int|string|null $reference_id
 * @property array<string, mixed>|null $metadata
 * @property int|null $created_by_user_id
 * @property-read Company|null $company
 * @property-read LeaveBalance|null $leaveBalance
 * @property-read Employee|null $employee
 * @property-read LeaveType|null $leaveType
 * @property-read LeavePolicy|null $leavePolicy
 * @property-read User|null $createdBy
 */
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

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<LeaveBalance, $this>
     */
    public function leaveBalance(): BelongsTo
    {
        return $this->belongsTo(LeaveBalance::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<LeaveType, $this>
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * @return BelongsTo<LeavePolicy, $this>
     */
    public function leavePolicy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
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
