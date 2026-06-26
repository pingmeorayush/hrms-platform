<?php

namespace App\Modules\PerformanceManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceReviewSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $submittedAt = data_get($this->resource, 'submitted_at');

        return [
            'id' => data_get($this->resource, 'id'),
            'role_type' => data_get($this->resource, 'role_type'),
            'submitted_by' => data_get($this->resource, 'submitted_by'),
            'is_anonymous_to_current_user' => (bool) data_get($this->resource, 'is_anonymous_to_current_user', false),
            'overall_rating' => data_get($this->resource, 'overall_rating'),
            'summary' => data_get($this->resource, 'summary'),
            'confidential_notes' => data_get($this->resource, 'confidential_notes'),
            'sections' => data_get($this->resource, 'section_payload'),
            'competencies' => data_get($this->resource, 'competency_payload'),
            'submitted_at' => method_exists($submittedAt, 'toIso8601String') ? $submittedAt->toIso8601String() : $submittedAt,
        ];
    }
}
