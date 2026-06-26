<?php

namespace App\Modules\ReportingAnalytics\Requests;

use App\Models\User;
use App\Modules\ReportingAnalytics\Requests\Concerns\AuthorizesReportingRequests;
use App\Modules\ReportingAnalytics\Requests\Concerns\HasReportingCatalogRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportDatasetRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:150'],
            'domain' => ['sometimes', Rule::in($this->reportingDomains())],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'grain' => ['sometimes', 'string', 'max:64'],
            'freshness_expectation_minutes' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10080'],
            'certification_status' => ['sometimes', Rule::in($this->certificationStatuses())],
            'review_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'owner_user_id' => ['sometimes', 'nullable', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'source_references' => ['sometimes', 'array', 'min:1'],
            'source_references.*.module' => ['required_with:source_references', 'string', 'max:64'],
            'source_references.*.entity' => ['required_with:source_references', 'string', 'max:128'],
            'source_references.*.field' => ['nullable', 'string', 'max:128'],
            'source_references.*.notes' => ['nullable', 'string', 'max:500'],
            'approved_fields' => ['sometimes', 'array', 'min:1'],
            'approved_fields.*.key' => ['required_with:approved_fields', ...$this->catalogKeyRule()],
            'approved_fields.*.label' => ['required_with:approved_fields', 'string', 'max:120'],
            'approved_fields.*.type' => ['required_with:approved_fields', Rule::in($this->fieldTypes())],
            'approved_fields.*.description' => ['nullable', 'string', 'max:500'],
            'approved_fields.*.sensitive' => ['required_with:approved_fields', 'boolean'],
            'approved_fields.*.masking_strategy' => ['nullable', Rule::in($this->maskingStrategies())],
            'approved_filters' => ['sometimes', 'array'],
            'approved_filters.*.key' => ['required_with:approved_filters', ...$this->catalogKeyRule()],
            'approved_filters.*.label' => ['required_with:approved_filters', 'string', 'max:120'],
            'approved_filters.*.type' => ['required_with:approved_filters', Rule::in($this->filterTypes())],
            'approved_filters.*.required' => ['required_with:approved_filters', 'boolean'],
            'approved_filters.*.operators' => ['required_with:approved_filters', 'array', 'min:1'],
            'approved_filters.*.operators.*' => ['required', Rule::in($this->filterOperators())],
            'drilldown_paths' => ['sometimes', 'array'],
            'drilldown_paths.*.key' => ['required_with:drilldown_paths', ...$this->catalogKeyRule()],
            'drilldown_paths.*.label' => ['required_with:drilldown_paths', 'string', 'max:120'],
            'drilldown_paths.*.target_dataset_key' => ['nullable', ...$this->catalogKeyRule()],
            'drilldown_paths.*.description' => ['nullable', 'string', 'max:500'],
            'drilldown_paths.*.allowed_filter_keys' => ['sometimes', 'array'],
            'drilldown_paths.*.allowed_filter_keys.*' => ['required', ...$this->catalogKeyRule()],
            'masking_posture' => ['sometimes', 'array'],
            'masking_posture.default_strategy' => ['required_with:masking_posture', Rule::in($this->maskingStrategies())],
            'masking_posture.sensitive_field_keys' => ['sometimes', 'array'],
            'masking_posture.sensitive_field_keys.*' => ['required', ...$this->catalogKeyRule()],
            'masking_posture.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
