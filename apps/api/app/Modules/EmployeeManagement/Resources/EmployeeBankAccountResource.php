<?php

namespace App\Modules\EmployeeManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeBankAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canViewFull = $request->user()?->can('employee.bank.manage') ?? false;

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'account_holder_name' => $this->account_holder_name,
            'bank_name' => $this->bank_name,
            'branch_name' => $this->branch_name,
            'account_number' => $this->formatSensitiveValue($this->account_number, $canViewFull),
            'ifsc_code' => $this->formatSensitiveValue($this->ifsc_code, $canViewFull),
            'routing_number' => $this->formatSensitiveValue($this->routing_number, $canViewFull),
            'iban' => $this->formatSensitiveValue($this->iban, $canViewFull),
            'swift_code' => $this->formatSensitiveValue($this->swift_code, $canViewFull),
            'status' => $this->status,
            'is_primary' => $this->is_primary,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'notes' => $this->notes,
            'sensitive_access' => $canViewFull ? 'full' : 'masked',
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function formatSensitiveValue(?string $value, bool $canViewFull): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if ($canViewFull) {
            return $value;
        }

        $visibleCharacters = min(4, strlen($value));

        return str_repeat('*', max(strlen($value) - $visibleCharacters, 0)).substr($value, -$visibleCharacters);
    }
}
