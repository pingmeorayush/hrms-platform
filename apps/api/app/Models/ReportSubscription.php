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
 * @property int|null $report_dataset_id
 * @property int|null $saved_report_view_id
 * @property int $owner_user_id
 * @property string $subscription_uuid
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property string $delivery_channel
 * @property string $delivery_target
 * @property string $export_format
 * @property string $frequency
 * @property string $timezone
 * @property array<string, mixed> $schedule_config
 * @property array<string, mixed>|null $filters
 * @property array<string, string>|null $filter_operators
 * @property string|null $sort_by
 * @property string|null $sort_direction
 * @property string|null $drilldown_path
 * @property Carbon|null $next_delivery_at
 * @property Carbon|null $last_delivered_at
 * @property string|null $last_delivery_status
 * @property string|null $last_delivery_error
 * @property int|null $last_report_export_id
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property-read Company|null $company
 * @property-read ReportDataset|null $reportDataset
 * @property-read SavedReportView|null $savedReportView
 * @property-read User|null $owner
 * @property-read ReportExport|null $lastReportExport
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'report_dataset_id',
    'saved_report_view_id',
    'owner_user_id',
    'subscription_uuid',
    'name',
    'description',
    'status',
    'delivery_channel',
    'delivery_target',
    'export_format',
    'frequency',
    'timezone',
    'schedule_config',
    'filters',
    'filter_operators',
    'sort_by',
    'sort_direction',
    'drilldown_path',
    'next_delivery_at',
    'last_delivered_at',
    'last_delivery_status',
    'last_delivery_error',
    'last_report_export_id',
    'created_by_user_id',
    'updated_by_user_id',
])]
class ReportSubscription extends Model
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
     * @return BelongsTo<SavedReportView, $this>
     */
    public function savedReportView(): BelongsTo
    {
        return $this->belongsTo(SavedReportView::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * @return BelongsTo<ReportExport, $this>
     */
    public function lastReportExport(): BelongsTo
    {
        return $this->belongsTo(ReportExport::class, 'last_report_export_id');
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
            'schedule_config' => 'array',
            'filters' => 'array',
            'filter_operators' => 'array',
            'next_delivery_at' => 'datetime',
            'last_delivered_at' => 'datetime',
        ];
    }
}
