<?php

namespace App\Modules\LeaveManagement\Services;

use App\Models\Employee;
use App\Models\LeaveAccrual;
use App\Models\LeaveEncashment;
use App\Models\LeavePolicy;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-type LeaveAccrualPreviewPayload array{
 *   employee_id: int|string,
 *   period_start: string,
 *   unused_balance_days?: int|float|string|null,
 *   used_days_in_period?: int|float|string|null
 * }
 */
class LeaveAccrualService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly LeaveBalanceService $leaveBalanceService,
    ) {}

    /**
     * @param  LeaveAccrualPreviewPayload  $payload
     */
    public function previewAccrual(User $actor, LeavePolicy $policy, array $payload): LeaveAccrual
    {
        return DB::transaction(function () use ($actor, $policy, $payload): LeaveAccrual {
            $employee = Employee::query()->findOrFail((int) $payload['employee_id']);
            $periodStart = CarbonImmutable::parse((string) $payload['period_start'])->startOfDay();
            $cycle = $this->resolveCycle($policy->accrual_frequency, $periodStart);

            $unusedBalanceDays = round((float) ($payload['unused_balance_days'] ?? 0), 2);
            $usedDaysInPeriod = round((float) ($payload['used_days_in_period'] ?? 0), 2);
            $eligibility = $this->resolveEligibility($policy, $employee, $cycle['start']);

            $openingBalanceDays = $cycle['is_reset_cycle'] && $eligibility['is_eligible']
                ? round((float) $policy->opening_balance_days, 2)
                : 0.0;
            $accruedDays = $eligibility['is_eligible']
                ? $this->resolveAccruedDays($policy->accrual_frequency, (float) $policy->annual_allowance_days)
                : 0.0;
            $carryForwardDays = $cycle['is_reset_cycle'] && $eligibility['is_eligible']
                ? round(min($unusedBalanceDays, (float) $policy->carry_forward_limit_days), 2)
                : 0.0;
            $remainingForEncashment = $cycle['is_reset_cycle'] && $eligibility['is_eligible']
                ? max($unusedBalanceDays - $carryForwardDays, 0)
                : 0.0;
            $encashableDays = $cycle['is_reset_cycle'] && $eligibility['is_eligible']
                ? round(min($remainingForEncashment, (float) $policy->encashment_limit_days), 2)
                : 0.0;
            $projectedClosingBalanceDays = round(max(
                ($openingBalanceDays + $accruedDays + $carryForwardDays) - $usedDaysInPeriod,
                0,
            ), 2);

            $snapshot = [
                'scope' => [
                    'department_id' => $policy->applicable_department_id,
                    'location_id' => $policy->applicable_location_id,
                ],
                'cycle' => [
                    'start' => $cycle['start']->toDateString(),
                    'end' => $cycle['end']->toDateString(),
                    'is_reset_cycle' => $cycle['is_reset_cycle'],
                ],
                'employee' => [
                    'employment_type' => $employee->employment_type,
                    'employment_status' => $employee->employment_status,
                    'gender' => $employee->gender,
                    'marital_status' => $employee->marital_status,
                    'department_id' => $employee->department_id,
                    'location_id' => $employee->location_id,
                    'date_of_joining' => $employee->date_of_joining?->toDateString(),
                ],
                'applied_rules' => $policy->eligibility_rule ?? [],
                'inputs' => [
                    'unused_balance_days' => $unusedBalanceDays,
                    'used_days_in_period' => $usedDaysInPeriod,
                ],
                'ineligibility_reasons' => $eligibility['reasons'],
            ];

            $calculationHash = hash('sha256', json_encode([
                'employee_id' => $employee->id,
                'leave_policy_id' => $policy->id,
                'policy_version' => $policy->version,
                'period_start' => $cycle['start']->toDateString(),
                'period_end' => $cycle['end']->toDateString(),
                'unused_balance_days' => $unusedBalanceDays,
                'used_days_in_period' => $usedDaysInPeriod,
                'eligibility_snapshot' => $snapshot,
            ], JSON_THROW_ON_ERROR));

            $accrual = LeaveAccrual::query()->updateOrCreate(
                ['calculation_hash' => $calculationHash],
                [
                    'employee_id' => $employee->id,
                    'leave_policy_id' => $policy->id,
                    'leave_type_id' => $policy->leave_type_id,
                    'policy_version' => $policy->version,
                    'accrual_frequency' => $policy->accrual_frequency,
                    'period_start' => $cycle['start']->toDateString(),
                    'period_end' => $cycle['end']->toDateString(),
                    'opening_balance_days' => $openingBalanceDays,
                    'accrued_days' => $accruedDays,
                    'carry_forward_days' => $carryForwardDays,
                    'encashable_days' => $encashableDays,
                    'used_days_in_period' => $usedDaysInPeriod,
                    'projected_closing_balance_days' => $projectedClosingBalanceDays,
                    'is_eligible' => $eligibility['is_eligible'],
                    'status' => 'projected',
                    'eligibility_snapshot' => $snapshot,
                    'generated_by_user_id' => $actor->id,
                ],
            );

            $this->syncProjectedEncashment($actor, $accrual, $policy, $encashableDays, $snapshot, $cycle);

            $this->auditLogger->record(
                eventType: 'leave.accrual.previewed',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'leave_policy_id' => $policy->id,
                    'leave_type_id' => $policy->leave_type_id,
                    'policy_version' => $policy->version,
                    'period_start' => $cycle['start']->toDateString(),
                    'period_end' => $cycle['end']->toDateString(),
                    'opening_balance_days' => $openingBalanceDays,
                    'accrued_days' => $accruedDays,
                    'carry_forward_days' => $carryForwardDays,
                    'encashable_days' => $encashableDays,
                    'projected_closing_balance_days' => $projectedClosingBalanceDays,
                    'is_eligible' => $eligibility['is_eligible'],
                    'calculation_hash' => $calculationHash,
                ],
                entityType: 'leave_accrual',
                entityId: (string) $accrual->id,
            );

            $this->leaveBalanceService->syncProjectedBalance($actor, $accrual);

            return $accrual->load(['employee', 'leaveType', 'projectedEncashment']);
        });
    }

    /**
     * @return array{start: CarbonImmutable, end: CarbonImmutable, is_reset_cycle: bool}
     */
    private function resolveCycle(string $frequency, CarbonImmutable $periodStart): array
    {
        return match ($frequency) {
            'monthly' => [
                'start' => $periodStart->startOfMonth(),
                'end' => $periodStart->endOfMonth(),
                'is_reset_cycle' => $periodStart->startOfMonth()->month === 1,
            ],
            'quarterly' => [
                'start' => $periodStart->startOfQuarter(),
                'end' => $periodStart->endOfQuarter(),
                'is_reset_cycle' => $periodStart->startOfQuarter()->month === 1,
            ],
            'annual', 'none' => [
                'start' => $periodStart->startOfYear(),
                'end' => $periodStart->endOfYear(),
                'is_reset_cycle' => true,
            ],
            default => [
                'start' => $periodStart->startOfMonth(),
                'end' => $periodStart->endOfMonth(),
                'is_reset_cycle' => $periodStart->startOfMonth()->month === 1,
            ],
        };
    }

    /**
     * @return array{is_eligible: bool, reasons: array<int, string>}
     */
    private function resolveEligibility(LeavePolicy $policy, Employee $employee, CarbonImmutable $cycleStart): array
    {
        $reasons = [];
        $rule = $policy->eligibility_rule ?? [];

        if ($policy->applicable_department_id !== null && $employee->department_id !== $policy->applicable_department_id) {
            $reasons[] = 'Employee is outside the policy department scope.';
        }

        if ($policy->applicable_location_id !== null && $employee->location_id !== $policy->applicable_location_id) {
            $reasons[] = 'Employee is outside the policy location scope.';
        }

        if (($rule['employment_types'] ?? []) !== [] && ! in_array($employee->employment_type, $rule['employment_types'], true)) {
            $reasons[] = 'Employee employment type is not eligible for this policy.';
        }

        if (($rule['employment_statuses'] ?? []) !== [] && ! in_array($employee->employment_status, $rule['employment_statuses'], true)) {
            $reasons[] = 'Employee employment status is not eligible for this policy.';
        }

        if (($rule['genders'] ?? []) !== [] && ! in_array($employee->gender, $rule['genders'], true)) {
            $reasons[] = 'Employee gender is not eligible for this policy.';
        }

        if (($rule['marital_statuses'] ?? []) !== [] && ! in_array($employee->marital_status, $rule['marital_statuses'], true)) {
            $reasons[] = 'Employee marital status is not eligible for this policy.';
        }

        $minimumTenureDays = isset($rule['minimum_tenure_days']) && is_numeric($rule['minimum_tenure_days'])
            ? (int) $rule['minimum_tenure_days']
            : null;
        if ($minimumTenureDays !== null && $employee->date_of_joining !== null) {
            $tenureDays = $employee->date_of_joining->startOfDay()->diffInDays($cycleStart, false);

            if ($tenureDays < $minimumTenureDays) {
                $reasons[] = 'Employee has not met the minimum tenure requirement.';
            }
        }

        return [
            'is_eligible' => $reasons === [],
            'reasons' => $reasons,
        ];
    }

    private function resolveAccruedDays(string $frequency, float $annualAllowanceDays): float
    {
        $cyclesPerYear = match ($frequency) {
            'monthly' => 12,
            'quarterly' => 4,
            'annual' => 1,
            'none' => 0,
            default => 0,
        };

        if ($cyclesPerYear === 0) {
            return 0.0;
        }

        return round($annualAllowanceDays / $cyclesPerYear, 2);
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @param  array{start: CarbonImmutable, end: CarbonImmutable, is_reset_cycle: bool}  $cycle
     */
    private function syncProjectedEncashment(
        User $actor,
        LeaveAccrual $accrual,
        LeavePolicy $policy,
        float $encashableDays,
        array $snapshot,
        array $cycle,
    ): void {
        if ($encashableDays <= 0) {
            LeaveEncashment::query()->where('leave_accrual_id', $accrual->id)->delete();

            return;
        }

        LeaveEncashment::query()->updateOrCreate(
            ['leave_accrual_id' => $accrual->id],
            [
                'employee_id' => $accrual->employee_id,
                'leave_policy_id' => $policy->id,
                'leave_type_id' => $policy->leave_type_id,
                'policy_version' => $policy->version,
                'cycle_start' => $cycle['start']->toDateString(),
                'cycle_end' => $cycle['end']->toDateString(),
                'projected_days' => $encashableDays,
                'status' => 'projected',
                'metadata' => [
                    'eligibility_snapshot' => $snapshot,
                    'carry_forward_limit_days' => $policy->carry_forward_limit_days,
                    'encashment_limit_days' => $policy->encashment_limit_days,
                ],
                'generated_by_user_id' => $actor->id,
            ],
        );
    }
}
