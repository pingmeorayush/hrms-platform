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
 * @property int $holiday_calendar_id
 * @property string $name
 * @property Carbon|null $holiday_date
 * @property string $type
 * @property bool $is_optional
 * @property string|null $description
 * @property-read Company|null $company
 * @property-read HolidayCalendar|null $holidayCalendar
 */
#[Fillable([
    'company_id',
    'holiday_calendar_id',
    'name',
    'holiday_date',
    'type',
    'is_optional',
    'description',
])]
class Holiday extends Model
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
     * @return BelongsTo<HolidayCalendar, $this>
     */
    public function holidayCalendar(): BelongsTo
    {
        return $this->belongsTo(HolidayCalendar::class);
    }

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_optional' => 'boolean',
        ];
    }
}
