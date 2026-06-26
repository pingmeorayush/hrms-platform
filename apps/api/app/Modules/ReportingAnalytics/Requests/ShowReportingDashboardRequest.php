<?php

namespace App\Modules\ReportingAnalytics\Requests;

use App\Modules\ReportingAnalytics\Requests\Concerns\AuthorizesReportingRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShowReportingDashboardRequest extends FormRequest
{
    use AuthorizesReportingRequests;

    public function authorize(): bool
    {
        return $this->canViewReportingWorkspace();
    }

    /**
     * @return array<string, ValidationRule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'force_refresh' => ['sometimes', 'boolean'],
        ];
    }
}
