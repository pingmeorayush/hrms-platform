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
 * @property int $asset_id
 * @property int $employee_id
 * @property string $status
 * @property Carbon|null $assigned_at
 * @property Carbon|null $issued_at
 * @property Carbon|null $expected_return_date
 * @property Carbon|null $returned_at
 * @property string|null $handover_condition
 * @property string|null $return_condition
 * @property string|null $assignment_notes
 * @property string|null $issue_notes
 * @property string|null $return_notes
 * @property-read Asset|null $asset
 * @property-read Employee|null $employee
 * @property-read User|null $assignedBy
 * @property-read User|null $issuedBy
 * @property-read User|null $returnedBy
 */
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

    /**
     * @return BelongsTo<Asset, $this>
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
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
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
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
