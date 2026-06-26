<?php

namespace App\Modules\ReportingAnalytics\Requests;

use App\Modules\ReportingAnalytics\Requests\Concerns\AuthorizesReportingRequests;
use App\Modules\ReportingAnalytics\Requests\Concerns\HasReportingCatalogRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSavedReportViewRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', Rule::in($this->savedViewStatuses())],
            'share_scope' => ['sometimes', Rule::in($this->savedViewShareScopes())],
            'shared_role_names' => ['sometimes', 'array'],
            'shared_role_names.*' => ['required', 'string', 'max:64'],
            'filters' => ['sometimes', 'array'],
            'filter_operators' => ['sometimes', 'array'],
            'filter_operators.*' => ['string', 'max:32'],
            'sort_by' => ['sometimes', ...$this->catalogKeyRule()],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'drilldown_path' => ['sometimes', ...$this->catalogKeyRule()],
            'presentation_preferences' => ['sometimes', 'array'],
        ];
    }
}
