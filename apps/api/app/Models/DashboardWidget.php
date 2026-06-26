<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property string $dashboard_key
 * @property string $widget_key
 * @property string $name
 * @property string $widget_type
 * @property string|null $description
 * @property int $position
 * @property int|null $kpi_definition_id
 * @property int|null $report_dataset_id
 * @property array<string, mixed>|null $configuration
 * @property int|null $freshness_expectation_minutes
 * @property bool $is_active
 * @property-read Company|null $company
 * @property-read KpiDefinition|null $kpiDefinition
 * @property-read ReportDataset|null $reportDataset
 */
#[Fillable([
    'company_id',
    'dashboard_key',
    'widget_key',
    'name',
    'widget_type',
    'description',
    'position',
    'kpi_definition_id',
    'report_dataset_id',
    'configuration',
    'freshness_expectation_minutes',
    'is_active',
])]
class DashboardWidget extends Model
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
     * @return BelongsTo<KpiDefinition, $this>
     */
    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }

    /**
     * @return BelongsTo<ReportDataset, $this>
     */
    public function reportDataset(): BelongsTo
    {
        return $this->belongsTo(ReportDataset::class);
    }

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'freshness_expectation_minutes' => 'integer',
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
