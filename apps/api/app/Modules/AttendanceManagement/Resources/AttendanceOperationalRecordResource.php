<?php

namespace App\Modules\AttendanceManagement\Resources;

use Illuminate\Http\Request;

class AttendanceOperationalRecordResource extends AttendanceRecordResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'exception_types' => $this->exceptionTypes(),
            'has_pending_correction' => $this->relationLoaded('corrections')
                ? $this->corrections->isNotEmpty()
                : false,
            'pending_corrections' => AttendanceCorrectionSummaryResource::collection(
                $this->whenLoaded('corrections'),
            ),
        ]);
    }

    /**
     * @return list<string>
     */
    private function exceptionTypes(): array
    {
        $exceptionTypes = [];

        if ($this->primary_status === 'absent') {
            $exceptionTypes[] = 'absent';
        }

        if ($this->primary_status === 'half_day') {
            $exceptionTypes[] = 'half_day';
        }

        if ($this->primary_status === 'incomplete') {
            $exceptionTypes[] = 'incomplete';
        }

        if ((bool) $this->is_late) {
            $exceptionTypes[] = 'late';
        }

        if ($this->relationLoaded('corrections') && $this->corrections->isNotEmpty()) {
            $exceptionTypes[] = 'pending_correction';
        }

        return $exceptionTypes;
    }
}
