<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $status
 * @property string|null $subscription_plan
 * @property string $timezone
 * @property string|null $currency
 * @property string $country_code
 * @property string $locale
 * @property string $language
 * @property string $time_format
 * @property array<int, string>|null $expansion_country_codes
 */
#[Fillable([
    'uuid',
    'name',
    'slug',
    'status',
    'subscription_plan',
    'timezone',
    'currency',
    'country_code',
    'locale',
    'language',
    'time_format',
    'expansion_country_codes',
])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    protected function casts(): array
    {
        return [
            'expansion_country_codes' => 'array',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
