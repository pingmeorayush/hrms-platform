<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function latestActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'latest_action_by_user_id');
    }
}
