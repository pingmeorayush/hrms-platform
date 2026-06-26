<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Employee;
use App\Models\TenantSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type EmployeeCodePolicy array{
 *   mode: string,
 *   prefix: string,
 *   number_padding: int
 * }
 */
class EmployeeCodeService
{
    public function resolveCodeForCreate(int $companyId, ?string $manualCode): string
    {
        $policy = $this->resolvePolicy($companyId);
        $mode = $policy['mode'];

        if ($mode === 'manual') {
            if (! $manualCode) {
                throw ValidationException::withMessages([
                    'employee_code' => ['Employee code is required when the tenant uses manual employee codes.'],
                ]);
            }

            return Str::upper(trim($manualCode));
        }

        if ($manualCode) {
            throw ValidationException::withMessages([
                'employee_code' => ['Manual employee code entry is disabled for this tenant.'],
            ]);
        }

        return $this->generateNextCode($companyId, $policy['prefix'], (int) $policy['number_padding']);
    }

    /**
     * @return EmployeeCodePolicy
     */
    public function resolvePolicy(int $companyId): array
    {
        /** @var array{mode:string, prefix:string, number_padding:int} $defaults */
        $defaults = config('employee_management.code_policy');
        $setting = TenantSetting::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('key', 'employee.code_policy')
            ->first();

        $value = $setting->value ?? [];

        return [
            'mode' => $value['mode'] ?? $defaults['mode'],
            'prefix' => strtoupper((string) ($value['prefix'] ?? $defaults['prefix'])),
            'number_padding' => (int) ($value['number_padding'] ?? $defaults['number_padding']),
        ];
    }

    public function isManualMode(int $companyId): bool
    {
        return $this->resolvePolicy($companyId)['mode'] === 'manual';
    }

    private function generateNextCode(int $companyId, string $prefix, int $padding): string
    {
        $normalizedPrefix = preg_replace('/[^A-Z0-9]/', '', strtoupper($prefix)) ?: 'EMP';

        $existingCodes = Employee::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('employee_code', 'like', $normalizedPrefix.'%')
            ->pluck('employee_code');

        $nextNumber = $this->extractSequenceNumbers($existingCodes, $normalizedPrefix)->max() + 1;

        return $normalizedPrefix.str_pad((string) $nextNumber, max($padding, 1), '0', STR_PAD_LEFT);
    }

    /**
     * @param  Collection<int, string>  $codes
     * @return Collection<int, int>
     */
    private function extractSequenceNumbers(Collection $codes, string $prefix): Collection
    {
        return $codes
            ->map(function (string $code) use ($prefix): ?int {
                if (! preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', strtoupper($code), $matches)) {
                    return null;
                }

                return (int) $matches[1];
            })
            ->filter(static fn (?int $value): bool => $value !== null)
            ->values()
            ->prepend(0);
    }
}
