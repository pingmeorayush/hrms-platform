<?php

namespace App\Modules\EmployeeManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyAcknowledgementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'employee_id' => $this->employee_id,
            'policy_title' => $this->policy_title,
            'policy_version' => $this->policy_version,
            'status' => $this->status,
            'due_date' => $this->due_date?->toDateString(),
            'assignment_notes' => $this->assignment_notes,
            'acknowledged_at' => $this->acknowledged_at?->toIso8601String(),
            'acknowledgement_notes' => $this->acknowledgement_notes,
            'document' => $this->document !== null ? [
                'id' => $this->document->id,
                'title' => $this->document->title,
                'repository_scope' => $this->document->repository_scope,
                'original_file_name' => $this->document->original_file_name,
                'mime_type' => $this->document->mime_type,
                'download_url' => route('policy.acknowledgements.download', [
                    'policyAcknowledgementId' => $this->id,
                ], false),
            ] : null,
            'employee' => $this->employee !== null ? [
                'id' => $this->employee->id,
                'employee_code' => $this->employee->employee_code,
                'full_name' => $this->employee->full_name,
            ] : null,
            'assigned_at' => $this->created_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
