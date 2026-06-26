<?php

namespace App\Modules\PayrollManagement\Services;

use App\Models\Employee;
use App\Models\EmployeeCompensation;
use App\Models\PayrollInput;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-import-type PayrollComponentSnapshotLine from EmployeeCompensationService
 * @phpstan-import-type PayrollResolvedFormulaInputs from EmployeeCompensationService
 *
 * @phpstan-type PayrollRunActionPayload array{
 *   reason?: string|null,
 *   comment?: string|null
 * }
 * @phpstan-type PayrollComponentContext array<string, float>
 */
class PayrollCalculationService
{
    public function __construct(
        private readonly PayslipService $payslipService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function calculateRun(User $actor, PayrollRun $run): PayrollRun
    {
        return DB::transaction(function () use ($actor, $run): PayrollRun {
            $run->loadMissing('payrollPeriod');

            if (! in_array($run->status, ['ready', 'calculated', 'failed'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Only ready or reopened payroll runs can be calculated.'],
                ]);
            }

            $inputs = $run->inputs()
                ->with(['employee', 'employeeCompensation'])
                ->orderBy('employee_id')
                ->orderBy('source_type')
                ->orderBy('input_code')
                ->orderBy('id')
                ->get()
                ->groupBy('employee_id');

            PayrollItem::query()->where('payroll_run_id', $run->id)->delete();

            $summary = [
                'employee_count' => 0,
                'item_count' => 0,
                'error_count' => 0,
                'gross_salary_total' => 0.0,
                'total_earnings' => 0.0,
                'total_deductions' => 0.0,
                'net_salary_total' => 0.0,
                'employer_cost_total' => 0.0,
                'total_lop_days' => 0.0,
                'total_unpaid_days' => 0.0,
                'total_overtime_earnings' => 0.0,
            ];

            foreach ($inputs as $employeeInputs) {
                $firstInput = $employeeInputs->first();

                if (! $firstInput?->employee || ! $firstInput->employeeCompensation) {
                    continue;
                }

                $item = $this->calculateEmployeeItem(
                    $actor,
                    $run,
                    $firstInput->employee,
                    $firstInput->employeeCompensation,
                    $employeeInputs,
                );

                $summary['employee_count']++;
                $summary['item_count']++;
                $summary['gross_salary_total'] += (float) $item->gross_salary;
                $summary['total_earnings'] += (float) $item->total_earnings;
                $summary['total_deductions'] += (float) $item->total_deductions;
                $summary['net_salary_total'] += (float) $item->net_salary;
                $summary['employer_cost_total'] += (float) $item->employer_cost;
                $summary['total_lop_days'] += (float) $item->lop_days;
                $summary['total_unpaid_days'] += (float) $item->unpaid_days;
                $summary['total_overtime_earnings'] += (float) $item->overtime_earnings;

                if ($item->status === 'error') {
                    $summary['error_count']++;
                }
            }

            $summary = collect($summary)
                ->map(function (mixed $value, string $key): mixed {
                    if (in_array($key, ['employee_count', 'item_count', 'error_count'], true)) {
                        return $value;
                    }

                    return round((float) $value, 2);
                })
                ->all();

            $run->forceFill([
                'status' => $summary['error_count'] > 0 ? 'failed' : 'calculated',
                'calculation_summary' => $summary,
                'calculated_at' => now(),
                'approved_at' => null,
                'locked_at' => null,
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'payroll.run.calculated',
                actor: $actor,
                metadata: [
                    'payroll_run_id' => $run->id,
                    'status' => $run->status,
                    'calculation_summary' => $summary,
                ],
                entityType: 'payroll_run',
                entityId: (string) $run->id,
            );

            return $run->fresh(['payrollPeriod.payrollCalendar', 'items.employee']);
        });
    }

    /**
     * @param  PayrollRunActionPayload  $payload
     */
    public function approveRun(User $actor, PayrollRun $run, array $payload): PayrollRun
    {
        return DB::transaction(function () use ($actor, $run, $payload): PayrollRun {
            if ($run->status !== 'calculated') {
                throw ValidationException::withMessages([
                    'status' => ['Only calculated payroll runs can be approved.'],
                ]);
            }

            $run->forceFill([
                'status' => 'approved',
                'approved_at' => now(),
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'payroll.run.approved',
                actor: $actor,
                metadata: [
                    'payroll_run_id' => $run->id,
                    'comment' => $payload['comment'] ?? null,
                    'calculation_summary' => $run->calculation_summary,
                ],
                entityType: 'payroll_run',
                entityId: (string) $run->id,
            );

            return $run->fresh(['payrollPeriod.payrollCalendar', 'items.employee']);
        });
    }

    public function lockRun(User $actor, PayrollRun $run): PayrollRun
    {
        return DB::transaction(function () use ($actor, $run): PayrollRun {
            if ($run->status !== 'approved') {
                throw ValidationException::withMessages([
                    'status' => ['Only approved payroll runs can be locked.'],
                ]);
            }

            $run->forceFill([
                'status' => 'locked',
                'locked_at' => now(),
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'payroll.run.locked',
                actor: $actor,
                metadata: [
                    'payroll_run_id' => $run->id,
                    'locked_at' => $run->locked_at?->toIso8601String(),
                    'calculation_summary' => $run->calculation_summary,
                ],
                entityType: 'payroll_run',
                entityId: (string) $run->id,
            );

            return $run->fresh(['payrollPeriod.payrollCalendar', 'items.employee']);
        });
    }

    /**
     * @param  PayrollRunActionPayload  $payload
     */
    public function reopenRun(User $actor, PayrollRun $run, array $payload): PayrollRun
    {
        return DB::transaction(function () use ($actor, $run, $payload): PayrollRun {
            if (! in_array($run->status, ['approved', 'locked'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Only approved or locked payroll runs can be reopened.'],
                ]);
            }

            if (blank($payload['reason'] ?? null)) {
                throw ValidationException::withMessages([
                    'reason' => ['A reopen reason is required.'],
                ]);
            }

            $this->payslipService->purgeRunPayslips($run);
            PayrollItem::query()->where('payroll_run_id', $run->id)->delete();

            $run->forceFill([
                'status' => 'ready',
                'calculation_summary' => null,
                'calculated_at' => null,
                'approved_at' => null,
                'locked_at' => null,
                'reopened_at' => now(),
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'payroll.run.reopened',
                actor: $actor,
                metadata: [
                    'payroll_run_id' => $run->id,
                    'reason' => trim((string) $payload['reason']),
                ],
                entityType: 'payroll_run',
                entityId: (string) $run->id,
            );

            return $run->fresh(['payrollPeriod.payrollCalendar', 'items.employee']);
        });
    }

    /**
     * @param  Collection<int, PayrollInput>  $inputs
     */
    private function calculateEmployeeItem(
        User $actor,
        PayrollRun $run,
        Employee $employee,
        EmployeeCompensation $compensation,
        Collection $inputs,
    ): PayrollItem {
        $period = $run->payrollPeriod;
        $periodStart = $period->start_date->copy()->startOfDay();
        $periodEnd = $period->end_date->copy()->startOfDay();
        $employmentDays = $this->employmentDaysInPeriod($employee, $periodStart, $periodEnd);
        $payableDays = max($employmentDays, 1.0);

        $inputMap = $inputs->keyBy('input_code');
        $lopDays = (float) ($inputMap->get('attendance_lop_days')->quantity ?? 0);
        $unpaidLeaveDays = (float) ($inputMap->get('approved_unpaid_leave_days')->quantity ?? 0);
        $paidLeaveDays = (float) ($inputMap->get('approved_paid_leave_days')->quantity ?? 0);
        $overtimeMinutes = (int) ($inputMap->get('attendance_overtime_minutes')->quantity ?? 0);
        $manualAdjustmentAmount = (float) $inputs
            ->where('source_type', 'manual_adjustment')
            ->sum(fn ($input): float => (float) ($input->amount ?? 0));

        $unpaidDays = round($lopDays + $unpaidLeaveDays, 2);
        $dailyProrationFactor = max(0.0, ($payableDays - $unpaidDays) / $payableDays);

        $componentValues = [];
        $earningsBreakdown = [];
        $deductionsBreakdown = [];
        $employerContributionBreakdown = [];

        $componentSnapshot = $this->resolveComponentSnapshot($compensation);

        foreach ($componentSnapshot as $line) {
            $resolvedInputs = $line['resolved_formula_inputs'];
            $amount = $this->evaluateResolvedComponent(
                $resolvedInputs,
                $componentValues,
            );

            $isProratable = array_key_exists('is_proratable', $line)
                ? (bool) $line['is_proratable']
                : true;
            $proratedAmount = $isProratable
                ? $amount * $dailyProrationFactor
                : $amount;

            $proratedAmount = round($proratedAmount, 2);
            $componentValues[$line['code']] = $proratedAmount;

            $breakdownLine = [
                'code' => $line['code'],
                'name' => $line['name'],
                'base_amount' => round($amount, 2),
                'prorated_amount' => $proratedAmount,
                'is_proratable' => $isProratable,
            ];

            if (($line['category'] ?? null) === 'earning') {
                $earningsBreakdown[] = $breakdownLine;
            } elseif (($line['category'] ?? null) === 'deduction') {
                $deductionsBreakdown[] = $breakdownLine;
            } elseif (($line['category'] ?? null) === 'employer_contribution') {
                $employerContributionBreakdown[] = $breakdownLine;
            }
        }

        $grossSalary = round(collect($earningsBreakdown)->sum('prorated_amount'), 2);
        $structureDeductions = round(collect($deductionsBreakdown)->sum('prorated_amount'), 2);
        $employerContributions = round(collect($employerContributionBreakdown)->sum('prorated_amount'), 2);

        $overtimeHourlyBase = $grossSalary > 0
            ? $grossSalary / ($payableDays * 8)
            : 0.0;
        $overtimeEarnings = round(($overtimeMinutes / 60) * $overtimeHourlyBase * 1.5, 2);

        $positiveAdjustments = max($manualAdjustmentAmount, 0.0);
        $negativeAdjustments = abs(min($manualAdjustmentAmount, 0.0));

        $lopAmount = round(($grossSalary / $payableDays) * $unpaidDays, 2);

        $totalEarnings = round($grossSalary + $overtimeEarnings + $positiveAdjustments, 2);
        $totalDeductions = round($structureDeductions + $lopAmount + $negativeAdjustments, 2);
        $netSalary = round($totalEarnings - $totalDeductions, 2);
        $employerCost = round($grossSalary + $overtimeEarnings + $employerContributions + $positiveAdjustments, 2);

        $validationErrors = [];

        if ($employmentDays <= 0) {
            $validationErrors[] = 'Employee has no payable employment days in the payroll period.';
        }

        if ($netSalary < 0) {
            $validationErrors[] = 'Net salary cannot be negative.';
        }

        return PayrollItem::query()->create([
            'company_id' => $run->company_id,
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'employee_compensation_id' => $compensation->id,
            'status' => $validationErrors === [] ? 'calculated' : 'error',
            'employment_days' => round($employmentDays, 2),
            'unpaid_days' => $unpaidDays,
            'lop_days' => round($lopDays, 2),
            'overtime_minutes' => $overtimeMinutes,
            'overtime_earnings' => $overtimeEarnings,
            'gross_salary' => $grossSalary,
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'employer_cost' => $employerCost,
            'earnings_breakdown' => array_merge($earningsBreakdown, [[
                'code' => 'OVERTIME',
                'name' => 'Overtime Earnings',
                'base_amount' => $overtimeEarnings,
                'prorated_amount' => $overtimeEarnings,
                'is_proratable' => false,
            ], [
                'code' => 'MANUAL_POSITIVE',
                'name' => 'Manual Adjustments',
                'base_amount' => $positiveAdjustments,
                'prorated_amount' => $positiveAdjustments,
                'is_proratable' => false,
            ]]),
            'deductions_breakdown' => array_merge($deductionsBreakdown, [[
                'code' => 'LOP',
                'name' => 'Loss Of Pay',
                'base_amount' => $lopAmount,
                'prorated_amount' => $lopAmount,
                'is_proratable' => false,
            ], [
                'code' => 'MANUAL_NEGATIVE',
                'name' => 'Manual Adjustments',
                'base_amount' => $negativeAdjustments,
                'prorated_amount' => $negativeAdjustments,
                'is_proratable' => false,
            ]]),
            'employer_contribution_breakdown' => $employerContributionBreakdown,
            'input_snapshot' => [
                'payable_days' => $payableDays,
                'paid_leave_days' => round($paidLeaveDays, 2),
                'unpaid_leave_days' => round($unpaidLeaveDays, 2),
                'manual_adjustment_amount' => round($manualAdjustmentAmount, 2),
                'inputs' => $inputs->map(fn ($input): array => [
                    'source_type' => $input->source_type,
                    'input_code' => $input->input_code,
                    'quantity' => $input->quantity,
                    'amount' => $input->amount,
                    'metadata' => $input->metadata,
                ])->values()->all(),
            ],
            'validation_errors' => $validationErrors,
            'created_by_user_id' => $actor->id,
            'updated_by_user_id' => $actor->id,
        ]);
    }

    /**
     * @param  PayrollResolvedFormulaInputs  $resolvedInputs
     * @param  PayrollComponentContext  $context
     */
    private function evaluateResolvedComponent(array $resolvedInputs, array $context): float
    {
        $calculationType = $resolvedInputs['calculation_type'] ?? 'fixed';

        return match ($calculationType) {
            'fixed' => (float) ($resolvedInputs['flat_amount'] ?? 0),
            'percentage' => $this->evaluatePercentageComponent($resolvedInputs, $context),
            'expression' => $this->evaluateExpressionComponent($resolvedInputs, $context),
            default => 0.0,
        };
    }

    /**
     * @param  PayrollResolvedFormulaInputs  $resolvedInputs
     * @param  PayrollComponentContext  $context
     */
    private function evaluatePercentageComponent(array $resolvedInputs, array $context): float
    {
        $percentage = (float) ($resolvedInputs['percentage_value'] ?? 0);
        $basisCodes = array_values(array_filter(array_map(
            static fn (string $code): string => strtoupper($code),
            $resolvedInputs['percentage_basis_component_codes'],
        ), static fn (string $code): bool => $code !== ''));

        $basisAmount = array_sum(array_map(
            static fn (string $code): float => (float) ($context[$code] ?? 0),
            $basisCodes,
        ));

        return round($basisAmount * ($percentage / 100), 2);
    }

    /**
     * @param  PayrollResolvedFormulaInputs  $resolvedInputs
     * @param  PayrollComponentContext  $context
     */
    private function evaluateExpressionComponent(array $resolvedInputs, array $context): float
    {
        $expression = (string) ($resolvedInputs['expression_formula'] ?? '0');
        $expression = strtoupper(trim($expression));

        if ($expression === '') {
            return 0.0;
        }

        foreach ($context as $code => $value) {
            $expression = preg_replace('/\b'.preg_quote((string) $code, '/').'\b/', (string) $value, $expression);
        }

        $expression = preg_replace('/\s+/', '', $expression) ?? $expression;

        if (preg_match('/[^0-9+\-*\/().,A-Z]/', $expression)) {
            throw ValidationException::withMessages([
                'expression_formula' => ['Unsupported characters were found in a payroll formula expression.'],
            ]);
        }

        return round($this->evaluateNumericExpression($expression), 2);
    }

    /**
     * @return list<PayrollComponentSnapshotLine>
     */
    private function resolveComponentSnapshot(EmployeeCompensation $compensation): array
    {
        return is_array($compensation->component_snapshot)
            ? $compensation->component_snapshot
            : [];
    }

    private function evaluateNumericExpression(string $expression): float
    {
        while (preg_match('/(MIN|MAX)\(([^()]+)\)/', $expression, $matches) === 1) {
            $arguments = array_map(
                fn (string $value): float => $this->evaluateNumericExpression($value),
                explode(',', $matches[2]),
            );

            $replacement = $matches[1] === 'MIN'
                ? min($arguments)
                : max($arguments);

            $expression = preg_replace('/'.preg_quote($matches[0], '/').'/', (string) $replacement, $expression, 1) ?? $expression;
        }

        $index = 0;

        return $this->parseExpression($expression, $index);
    }

    private function parseExpression(string $expression, int &$index): float
    {
        $value = $this->parseTerm($expression, $index);
        $length = strlen($expression);

        while ($index < $length) {
            $operator = $expression[$index];

            if (! in_array($operator, ['+', '-'], true)) {
                break;
            }

            $index++;
            $term = $this->parseTerm($expression, $index);
            $value = $operator === '+'
                ? $value + $term
                : $value - $term;
        }

        return $value;
    }

    private function parseTerm(string $expression, int &$index): float
    {
        $value = $this->parseFactor($expression, $index);
        $length = strlen($expression);

        while ($index < $length) {
            $operator = $expression[$index];

            if (! in_array($operator, ['*', '/'], true)) {
                break;
            }

            $index++;
            $factor = $this->parseFactor($expression, $index);
            $value = $operator === '*'
                ? $value * $factor
                : ($factor === 0.0 ? 0.0 : $value / $factor);
        }

        return $value;
    }

    private function parseFactor(string $expression, int &$index): float
    {
        $length = strlen($expression);

        if ($index < $length && $expression[$index] === '(') {
            $index++;
            $value = $this->parseExpression($expression, $index);
            $index++;

            return $value;
        }

        $start = $index;

        if ($index < $length && in_array($expression[$index], ['+', '-'], true)) {
            $index++;
        }

        while ($index < $length && (ctype_digit($expression[$index]) || $expression[$index] === '.')) {
            $index++;
        }

        return (float) substr($expression, $start, $index - $start);
    }

    private function employmentDaysInPeriod(Employee $employee, Carbon $periodStart, Carbon $periodEnd): float
    {
        $start = $employee->date_of_joining?->copy()->startOfDay() ?? $periodStart->copy();
        $end = $employee->terminated_at?->copy()->startOfDay() ?? $periodEnd->copy();

        if ($start->gt($periodStart)) {
            $periodStart = $start;
        }

        if ($end->lt($periodEnd)) {
            $periodEnd = $end;
        }

        if ($periodEnd->lt($periodStart)) {
            return 0.0;
        }

        return (float) $periodStart->diffInDays($periodEnd) + 1;
    }
}
