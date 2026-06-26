<?php

namespace App\Modules\ReportingAnalytics\Requests;

use App\Modules\ReportingAnalytics\Requests\Concerns\AuthorizesReportingRequests;
use App\Modules\ReportingAnalytics\Requests\Concerns\HasReportingCatalogRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportSubscriptionRequest extends FormRequest
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
            'dataset_key' => ['sometimes', ...$this->catalogKeyRule()],
            'saved_report_view_id' => ['sometimes', 'integer', 'exists:saved_report_views,id'],
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', Rule::in($this->reportSubscriptionStatuses())],
            'delivery_channel' => ['sometimes', Rule::in($this->reportSubscriptionChannels())],
            'delivery_target' => ['sometimes', Rule::in($this->reportSubscriptionDeliveryTargets())],
            'export_format' => ['sometimes', Rule::in($this->reportExportFormats())],
            'frequency' => ['sometimes', Rule::in($this->reportSubscriptionFrequencies())],
            'timezone' => ['sometimes', 'string', 'max:64'],
            'schedule_config' => ['sometimes', 'array'],
            'schedule_config.time_of_day' => ['sometimes', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'schedule_config.weekday' => ['sometimes', 'integer', 'min:0', 'max:6'],
            'schedule_config.day_of_month' => ['sometimes', 'integer', 'min:1', 'max:28'],
            'filters' => ['sometimes', 'array'],
            'filter_operators' => ['sometimes', 'array'],
            'filter_operators.*' => ['string', 'max:32'],
            'sort_by' => ['sometimes', ...$this->catalogKeyRule()],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'drilldown_path' => ['sometimes', ...$this->catalogKeyRule()],
        ];
    }
}
