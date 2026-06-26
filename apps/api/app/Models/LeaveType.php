<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property string $code
 * @property string $name
 * @property string $category
 * @property string|null $description
 * @property bool $is_paid
 * @property bool $requires_approval
 * @property bool $allows_half_day
 * @property string|null $color_token
 * @property string $status
 * @property-read Company|null $company
 * @property-read EloquentCollection<int, LeavePolicy> $policies
 */
#[Fillable([
    'company_id',
    'code',
    'name',
    'category',
    'description',
    'is_paid',
    'requires_approval',
    'allows_half_day',
    'color_token',
    'status',
])]
class LeaveType extends Model
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
     * @return HasMany<LeavePolicy, $this>
     */
    public function policies(): HasMany
    {
        return $this->hasMany(LeavePolicy::class);
    }

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'allows_half_day' => 'boolean',
        ];
    }
}
