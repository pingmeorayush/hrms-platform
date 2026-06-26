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
 * @property string $key
 * @property string $name
 * @property string $domain
 * @property string|null $description
 * @property string $formula
 * @property array<int, array<string, mixed>> $source_references
 * @property string $grain
 * @property string $certification_status
 * @property string|null $review_notes
 * @property int|null $owner_user_id
 * @property int|null $reviewed_by_user_id
 * @property Carbon|null $reviewed_at
 * @property int|null $certified_by_user_id
 * @property Carbon|null $certified_at
 * @property int $version
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property-read User|null $owner
 * @property-read User|null $reviewedBy
 * @property-read User|null $certifiedBy
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'key',
    'name',
    'domain',
    'description',
    'formula',
    'source_references',
    'grain',
    'certification_status',
    'review_notes',
    'owner_user_id',
    'reviewed_by_user_id',
    'reviewed_at',
    'certified_by_user_id',
    'certified_at',
    'version',
    'created_by_user_id',
    'updated_by_user_id',
])]
class KpiDefinition extends Model
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
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function certifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certified_by_user_id');
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
            'source_references' => 'array',
            'reviewed_at' => 'datetime',
            'certified_at' => 'datetime',
            'version' => 'integer',
        ];
    }
}
