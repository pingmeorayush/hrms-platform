<?php

namespace App\Modules\PayrollManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryStructureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'previous_version_id' => $this->previous_version_id,
            'code' => $this->code,
            'name' => $this->name,
            'currency' => $this->currency,
            'country_code' => $this->country_code,
            'pay_frequency' => $this->pay_frequency,
            'grade' => $this->grade,
            'band' => $this->band,
            'level' => $this->level,
            'annual_ctc_amount' => $this->annual_ctc_amount,
            'basic_salary_amount' => $this->basic_salary_amount,
            'gross_salary_amount' => $this->gross_salary_amount,
            'net_salary_amount' => $this->net_salary_amount,
            'effective_from' => $this->effective_from?->toDateString(),
            'revision_date' => $this->revision_date?->toDateString(),
            'version' => $this->version,
            'status' => $this->status,
            'notes' => $this->notes,
            'components' => SalaryStructureComponentResource::collection($this->whenLoaded('components')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
