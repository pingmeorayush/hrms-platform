<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property int $report_dataset_id
 * @property int $owner_user_id
 * @property string $view_uuid
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property string $share_scope
 * @property array<int, string>|null $shared_role_names
 * @property array<string, mixed>|null $filters
 * @property array<string, string>|null $filter_operators
 * @property string|null $sort_by
 * @property string|null $sort_direction
 * @property string|null $drilldown_path
 * @property array<string, mixed>|null $presentation_preferences
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property-read Company|null $company
 * @property-read ReportDataset|null $reportDataset
 * @property-read User|null $owner
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 * @property-read Collection<int, ReportSubscription> $subscriptions
 */
#[Fillable([
    'company_id',
    'report_dataset_id',
    'owner_user_id',
    'view_uuid',
    'name',
    'description',
    'status',
    'share_scope',
    'shared_role_names',
    'filters',
    'filter_operators',
    'sort_by',
    'sort_direction',
    'drilldown_path',
    'presentation_preferences',
    'created_by_user_id',
    'updated_by_user_id',
])]
class SavedReportView extends Model
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
     * @return BelongsTo<ReportDataset, $this>
     */
    public function reportDataset(): BelongsTo
    {
        return $this->belongsTo(ReportDataset::class);
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

    /**
     * @return HasMany<ReportSubscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(ReportSubscription::class);
    }

    protected function casts(): array
    {
        return [
            'shared_role_names' => 'array',
            'filters' => 'array',
            'filter_operators' => 'array',
            'presentation_preferences' => 'array',
        ];
    }
}
