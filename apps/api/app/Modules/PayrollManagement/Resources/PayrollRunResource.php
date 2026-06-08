<?php

namespace App\Modules\PayrollManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payroll_period_id' => $this->payroll_period_id,
            'name' => $this->name,
            'frequency' => $this->frequency,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->status,
            'prerequisite_summary' => $this->prerequisite_summary ?? [
                'ready_for_calculation' => false,
                'blocking_count' => 0,
                'warning_count' => 0,
                'passed_count' => 0,
            ],
            'prerequisite_snapshot' => $this->prerequisite_snapshot ?? [
                'checks' => [],
                'summary' => [
                    'ready_for_calculation' => false,
                    'blocking_count' => 0,
                    'warning_count' => 0,
                    'passed_count' => 0,
                ],
            ],
            'input_summary' => $this->input_summary ?? [],
            'calculation_summary' => $this->calculation_summary ?? [],
            'items' => PayrollItemResource::collection($this->whenLoaded('items')),
            'prepared_at' => $this->prepared_at?->toIso8601String(),
            'inputs_generated_at' => $this->inputs_generated_at?->toIso8601String(),
            'calculated_at' => $this->calculated_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'locked_at' => $this->locked_at?->toIso8601String(),
            'reopened_at' => $this->reopened_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
