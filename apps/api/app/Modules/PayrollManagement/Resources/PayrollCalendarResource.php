<?php

namespace App\Modules\PayrollManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollCalendarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'frequency' => $this->frequency,
            'timezone' => $this->timezone,
            'payroll_day' => $this->payroll_day,
            'payroll_weekday' => $this->payroll_weekday,
            'is_default' => (bool) $this->is_default,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
