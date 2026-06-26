<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $start_time
 * @property string $end_time
 * @property int $break_duration_minutes
 * @property int $grace_minutes
 * @property int|null $working_hours_minutes
 * @property bool $is_overnight
 * @property string $status
 * @property-read Company|null $company
 */
#[Fillable([
    'company_id',
    'code',
    'name',
    'description',
    'start_time',
    'end_time',
    'break_duration_minutes',
    'grace_minutes',
    'working_hours_minutes',
    'is_overnight',
    'status',
])]
class Shift extends Model
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
     * @return HasMany<ShiftAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /**
     * @return HasMany<ShiftRoster, $this>
     */
    public function rosters(): HasMany
    {
        return $this->hasMany(ShiftRoster::class);
    }

    protected function casts(): array
    {
        return [
            'is_overnight' => 'boolean',
        ];
    }
}
