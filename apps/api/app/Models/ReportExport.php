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
 * @property int $report_dataset_id
 * @property int $requested_by_user_id
 * @property string $export_uuid
 * @property string $status
 * @property string $format
 * @property string $execution_mode
 * @property string $delivery_target
 * @property array<string, mixed>|null $requested_filters
 * @property array<string, string>|null $requested_filter_operators
 * @property string|null $sort_by
 * @property string|null $sort_direction
 * @property string|null $drilldown_path
 * @property int|null $estimated_row_count
 * @property int|null $exported_row_count
 * @property array<string, mixed>|null $visibility_posture
 * @property array<string, mixed>|null $freshness_snapshot
 * @property string|null $disk
 * @property string|null $file_path
 * @property string|null $file_name
 * @property int|null $file_size_bytes
 * @property string|null $checksum_sha256
 * @property Carbon $requested_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $failed_at
 * @property Carbon|null $retention_expires_at
 * @property Carbon|null $notified_at
 * @property string|null $last_error
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property-read Company|null $company
 * @property-read ReportDataset|null $reportDataset
 * @property-read User|null $requestedBy
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'report_dataset_id',
    'requested_by_user_id',
    'export_uuid',
    'status',
    'format',
    'execution_mode',
    'delivery_target',
    'requested_filters',
    'requested_filter_operators',
    'sort_by',
    'sort_direction',
    'drilldown_path',
    'estimated_row_count',
    'exported_row_count',
    'visibility_posture',
    'freshness_snapshot',
    'disk',
    'file_path',
    'file_name',
    'file_size_bytes',
    'checksum_sha256',
    'requested_at',
    'started_at',
    'completed_at',
    'failed_at',
    'retention_expires_at',
    'notified_at',
    'last_error',
    'created_by_user_id',
    'updated_by_user_id',
])]
class ReportExport extends Model
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
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
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
            'requested_filters' => 'array',
            'requested_filter_operators' => 'array',
            'visibility_posture' => 'array',
            'freshness_snapshot' => 'array',
            'estimated_row_count' => 'integer',
            'exported_row_count' => 'integer',
            'file_size_bytes' => 'integer',
            'requested_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'retention_expires_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }
}
