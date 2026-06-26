<?php

namespace App\Modules\ReportingAnalytics\Requests;

use App\Models\User;
use App\Modules\ReportingAnalytics\Requests\Concerns\AuthorizesReportingRequests;
use App\Modules\ReportingAnalytics\Requests\Concerns\HasReportingCatalogRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKpiDefinitionRequest extends FormRequest
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
            'formula' => ['sometimes', 'string', 'max:4000'],
            'grain' => ['sometimes', 'string', 'max:64'],
            'certification_status' => ['sometimes', Rule::in($this->certificationStatuses())],
            'review_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'owner_user_id' => ['sometimes', 'nullable', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'source_references' => ['sometimes', 'array', 'min:1'],
            'source_references.*.module' => ['required_with:source_references', 'string', 'max:64'],
            'source_references.*.entity' => ['required_with:source_references', 'string', 'max:128'],
            'source_references.*.field' => ['nullable', 'string', 'max:128'],
            'source_references.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
