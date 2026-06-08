<?php

namespace App\Modules\PayrollManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payroll_calendar_id' => $this->payroll_calendar_id,
            'payroll_calendar' => new PayrollCalendarResource($this->whenLoaded('payrollCalendar')),
            'name' => $this->name,
            'frequency' => $this->frequency,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'payroll_date' => $this->payroll_date?->toDateString(),
            'status' => $this->status,
            'opened_at' => $this->opened_at?->toIso8601String(),
            'prepared_at' => $this->prepared_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'latest_run' => new PayrollRunResource($this->whenLoaded('latestRun')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
