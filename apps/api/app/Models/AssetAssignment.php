<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'asset_id',
    'employee_id',
    'status',
    'assigned_at',
    'issued_at',
    'expected_return_date',
    'returned_at',
    'handover_condition',
    'return_condition',
    'assignment_notes',
    'issue_notes',
    'return_notes',
    'assigned_by_user_id',
    'issued_by_user_id',
    'returned_by_user_id',
])]
class AssetAssignment extends Model
{
    use BelongsToCompany;

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'issued_at' => 'datetime',
            'expected_return_date' => 'date',
            'returned_at' => 'datetime',
        ];
    }
}
