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
 * @property int $document_id
 * @property int $employee_id
 * @property string $policy_title
 * @property string $policy_version
 * @property string $status
 * @property int|null $assigned_by_user_id
 * @property Carbon|null $due_date
 * @property string|null $assignment_notes
 * @property Carbon|null $acknowledged_at
 * @property int|null $acknowledged_by_user_id
 * @property string|null $acknowledgement_notes
 * @property-read Document|null $document
 * @property-read Employee|null $employee
 * @property-read User|null $assignedBy
 * @property-read User|null $acknowledgedBy
 */
#[Fillable([
    'company_id',
    'document_id',
    'employee_id',
    'policy_title',
    'policy_version',
    'status',
    'assigned_by_user_id',
    'due_date',
    'assignment_notes',
    'acknowledged_at',
    'acknowledged_by_user_id',
    'acknowledgement_notes',
])]
class PolicyAcknowledgement extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Document, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'acknowledged_at' => 'datetime',
        ];
    }
}
