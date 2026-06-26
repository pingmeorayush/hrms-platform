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
 * @property int $attendance_record_id
 * @property int $employee_id
 * @property int|null $workflow_instance_id
 * @property int $requested_by_user_id
 * @property int|null $latest_action_by_user_id
 * @property string $status
 * @property string $reason
 * @property array<string, mixed>|null $original_values
 * @property array<string, string>|null $corrected_values
 * @property array<string, mixed>|null $applied_values
 * @property string|null $decision_comment
 * @property Carbon|null $approved_at
 * @property Carbon|null $rejected_at
 * @property-read AttendanceRecord|null $attendanceRecord
 * @property-read Employee|null $employee
 * @property-read WorkflowInstance|null $workflowInstance
 * @property-read User|null $requester
 * @property-read User|null $latestActor
 */
#[Fillable([
    'company_id',
    'attendance_record_id',
    'employee_id',
    'workflow_instance_id',
    'requested_by_user_id',
    'latest_action_by_user_id',
    'status',
    'reason',
    'original_values',
    'corrected_values',
    'applied_values',
    'decision_comment',
    'approved_at',
    'rejected_at',
])]
class AttendanceCorrection extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'original_values' => 'array',
            'corrected_values' => 'array',
            'applied_values' => 'array',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<AttendanceRecord, $this>
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function latestActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'latest_action_by_user_id');
    }
}
