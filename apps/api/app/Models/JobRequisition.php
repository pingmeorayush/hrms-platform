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
 * @property string $requisition_code
 * @property string $title
 * @property string $employment_type
 * @property string $hiring_type
 * @property string $priority
 * @property int $openings_count
 * @property float|null $min_experience_years
 * @property Carbon|null $target_start_date
 * @property string|null $headcount_reference
 * @property int|null $department_id
 * @property int|null $designation_id
 * @property int|null $location_id
 * @property int|null $cost_center_id
 * @property int|null $recruiter_user_id
 * @property int|null $hiring_manager_employee_id
 * @property int|null $requested_by_user_id
 * @property int|null $workflow_instance_id
 * @property string $status
 * @property string|null $status_before_hold
 * @property string $justification
 * @property string|null $notes
 * @property string|null $closed_reason
 * @property Carbon|null $submitted_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $on_hold_at
 * @property Carbon|null $closed_at
 * @property-read Company|null $company
 * @property-read Department|null $department
 * @property-read Designation|null $designation
 * @property-read Location|null $location
 * @property-read CostCenter|null $costCenter
 * @property-read User|null $recruiter
 * @property-read Employee|null $hiringManager
 * @property-read User|null $requestedBy
 * @property-read WorkflowInstance|null $workflowInstance
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'requisition_code',
    'title',
    'employment_type',
    'hiring_type',
    'priority',
    'openings_count',
    'min_experience_years',
    'target_start_date',
    'headcount_reference',
    'department_id',
    'designation_id',
    'location_id',
    'cost_center_id',
    'recruiter_user_id',
    'hiring_manager_employee_id',
    'requested_by_user_id',
    'workflow_instance_id',
    'status',
    'status_before_hold',
    'justification',
    'notes',
    'closed_reason',
    'submitted_at',
    'approved_at',
    'on_hold_at',
    'closed_at',
    'created_by_user_id',
    'updated_by_user_id',
])]
class JobRequisition extends Model
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
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Designation, $this>
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<CostCenter, $this>
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_user_id');
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function hiringManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'hiring_manager_employee_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
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
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'openings_count' => 'integer',
            'min_experience_years' => 'float',
            'target_start_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'on_hold_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
