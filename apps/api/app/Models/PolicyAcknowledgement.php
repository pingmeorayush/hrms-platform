<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

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
