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
 * @property int $employee_id
 * @property int $leave_type_id
 * @property int $leave_policy_id
 * @property int $policy_version
 * @property int|null $workflow_instance_id
 * @property int $requested_by_user_id
 * @property int|null $department_id
 * @property int|null $location_id
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property float $total_days
 * @property string $status
 * @property string $reason
 * @property string|null $approver_comment
 * @property bool $is_auto_approved
 * @property string $attendance_sync_status
 * @property Carbon|null $attendance_synced_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $rejected_at
 * @property Carbon|null $cancelled_at
 * @property-read Company|null $company
 * @property-read Employee|null $employee
 * @property-read LeaveType|null $leaveType
 * @property-read LeavePolicy|null $leavePolicy
 * @property-read WorkflowInstance|null $workflowInstance
 * @property-read User|null $requestedBy
 * @property-read Department|null $department
 * @property-read Location|null $location
 */
#[Fillable([
    'company_id',
    'employee_id',
    'leave_type_id',
    'leave_policy_id',
    'policy_version',
    'workflow_instance_id',
    'requested_by_user_id',
    'department_id',
    'location_id',
    'start_date',
    'end_date',
    'total_days',
    'status',
    'reason',
    'approver_comment',
    'is_auto_approved',
    'attendance_sync_status',
    'attendance_synced_at',
    'approved_at',
    'rejected_at',
    'cancelled_at',
    'created_by_user_id',
    'updated_by_user_id',
])]
class LeaveRequest extends Model
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
     * @return BelongsTo<WorkflowInstance, $this>
     */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_days' => 'float',
            'is_auto_approved' => 'boolean',
            'attendance_synced_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
