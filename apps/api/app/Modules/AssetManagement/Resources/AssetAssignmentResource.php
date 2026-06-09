<?php

namespace App\Modules\AssetManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $employee = $this->whenLoaded('employee');

        return [
            'id' => $this->id,
            'asset_id' => $this->asset_id,
            'employee_id' => $this->employee_id,
            'employee' => $employee ? [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
            ] : null,
            'status' => $this->status,
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'issued_at' => $this->issued_at?->toIso8601String(),
            'expected_return_date' => $this->expected_return_date?->toDateString(),
            'returned_at' => $this->returned_at?->toIso8601String(),
            'handover_condition' => $this->handover_condition,
            'return_condition' => $this->return_condition,
            'assignment_notes' => $this->assignment_notes,
            'issue_notes' => $this->issue_notes,
            'return_notes' => $this->return_notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
