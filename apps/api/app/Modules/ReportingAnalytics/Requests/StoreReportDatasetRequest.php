<?php

namespace App\Modules\ReportingAnalytics\Requests;

use App\Models\User;
use App\Modules\ReportingAnalytics\Requests\Concerns\AuthorizesReportingRequests;
use App\Modules\ReportingAnalytics\Requests\Concerns\HasReportingCatalogRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportDatasetRequest extends FormRequest
{
    use AuthorizesReportingRequests;
    use HasReportingCatalogRules;

    public function authorize(): bool
    {
        return $this->canManageReportingCatalog();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'key' => [
                'required',
                ...$this->catalogKeyRule(),
                Rule::unique('report_datasets', 'key')->where('company_id', $companyId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'domain' => ['required', Rule::in($this->reportingDomains())],
            'description' => ['nullable', 'string', 'max:2000'],
            'grain' => ['required', 'string', 'max:64'],
            'freshness_expectation_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'certification_status' => ['sometimes', Rule::in($this->certificationStatuses())],
            'review_notes' => ['nullable', 'string', 'max:2000'],
            'owner_user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            ...$this->sourceReferenceRules('source_references'),
            ...$this->approvedFieldRules('approved_fields'),
            ...$this->approvedFilterRules('approved_filters'),
            ...$this->drilldownPathRules('drilldown_paths'),
            ...$this->maskingPostureRules('masking_posture'),
        ];
    }
}
