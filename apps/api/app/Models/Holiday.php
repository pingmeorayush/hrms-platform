<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

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
