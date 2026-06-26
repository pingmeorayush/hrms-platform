<?php

namespace App\Modules\ReportingAnalytics\Requests;

use App\Modules\ReportingAnalytics\Requests\Concerns\AuthorizesReportingRequests;
use App\Modules\ReportingAnalytics\Requests\Concerns\HasReportingCatalogRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportExportRequest extends FormRequest
{
    use AuthorizesReportingRequests;
    use HasReportingCatalogRules;

    public function authorize(): bool
    {
        return $this->canViewReportingWorkspace();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'dataset_key' => ['required', ...$this->catalogKeyRule()],
            'format' => ['sometimes', Rule::in($this->reportExportFormats())],
            'execution_mode' => ['sometimes', Rule::in($this->reportExportExecutionModes())],
            'delivery_target' => ['sometimes', Rule::in($this->reportExportDeliveryTargets())],
            'filters' => ['sometimes', 'array'],
            'filter_operators' => ['sometimes', 'array'],
            'filter_operators.*' => ['string', 'max:32'],
            'sort_by' => ['sometimes', ...$this->catalogKeyRule()],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'drilldown_path' => ['sometimes', ...$this->catalogKeyRule()],
        ];
    }
}
