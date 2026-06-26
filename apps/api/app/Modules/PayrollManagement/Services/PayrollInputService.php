<?php

namespace App\Modules\PayrollManagement\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\EmployeeCompensation;
use App\Models\LeaveRequest;
use App\Models\PayrollAdjustment;
use App\Models\PayrollInput;
use App\Models\PayrollRun;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type PayrollInputFilters array{
 *   employee_id?: int|string,
 *   source_type?: string
 * }
 * @phpstan-type PayrollAdjustmentFilters array{
 *   employee_id?: int|string,
 *   status?: string
 * }
 * @phpstan-type PayrollAdjustmentPayload array{
 *   employee_id: int|string,
 *   adjustment_code: string,
 *   name: string,
 *   category: string,
 *   amount: int|float|string,
 *   effective_date: string,
 *   status?: string,
 *   notes?: string|null
 * }
 * @phpstan-type PayrollInputRowMetadata array<string, bool|int|float|string|null>
 * @phpstan-type PayrollInputRowAttributes array{
 *   payroll_adjustment_id?: int|null,
 *   source_type: string,
 *   input_code: string,
 *   unit?: string|null,
 *   quantity?: int|float|string|null,
 *   amount?: int|float|string|null,
 *   effective_date?: string|null,
 *   source_record_id?: int|null,
 *   metadata?: PayrollInputRowMetadata
 * }
 * @phpstan-type PayrollInputSummary array{
 *   employee_count: int,
 *   input_count: int,
 *   manual_adjustment_count: int,
 *   attendance_record_count: int,
 *   approved_leave_request_count: int,
 *   total_worked_minutes: int,
 *   total_overtime_minutes: int,
 *   total_lop_days: float,
 *   total_paid_leave_days: float,
 *   total_unpaid_leave_days: float,
 *   total_manual_adjustment_amount: float
 * }
 */
class PayrollInputService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  PayrollInputFilters  $filters
     * @return Collection<int, PayrollInput>
     */
    public function listInputs(PayrollRun $run, array $filters): Collection
    {
        return PayrollInput::query()
            ->with('employee')
            ->where('payroll_run_id', $run->id)
            ->when(
                array_key_exists('employee_id', $filters),
                fn ($query) => $query->where('employee_id', $filters['employee_id']),
            )
            ->when(
                array_key_exists('source_type', $filters),
                fn ($query) => $query->where('source_type', $filters['source_type']),
            )
            ->orderBy('employee_id')
            ->orderBy('source_type')
            ->orderBy('input_code')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  PayrollAdjustmentFilters  $filters
     * @return Collection<int, PayrollAdjustment>
     */
    public function listAdjustments(PayrollRun $run, array $filters): Collection
    {
        return PayrollAdjustment::query()
            ->with('employee')
            ->where('payroll_run_id', $run->id)
            ->when(
                array_key_exists('employee_id', $filters),
                fn ($query) => $query->where('employee_id', $filters['employee_id']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn ($query) => $query->where('status', $filters['status']),
            )
            ->orderBy('employee_id')
            ->orderBy('effective_date')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  PayrollAdjustmentPayload  $payload
     */
    public function createAdjustment(User $actor, PayrollRun $run, array $payload): PayrollAdjustment
    {
        return DB::transaction(function () use ($actor, $run, $payload): PayrollAdjustment {
            $this->ensureRunCanAcceptAdjustments($run);

            $employee = Employee::query()->findOrFail((int) $payload['employee_id']);
            $effectiveDate = Carbon::parse((string) $payload['effective_date'], $actor->company->timezone)->startOfDay();
            $this->ensureDateWithinRun($run, $effectiveDate);

            $adjustment = PayrollAdjustment::query()->create([
                'company_id' => $run->company_id,
                'payroll_run_id' => $run->id,
                'employee_id' => $employee->id,
                'adjustment_code' => strtoupper(trim((string) $payload['adjustment_code'])),
                'name' => trim((string) $payload['name']),
                'category' => $payload['category'],
                'amount' => round((float) $payload['amount'], 2),
                'effective_date' => $effectiveDate->toDateString(),
                'status' => $payload['status'] ?? 'active',
                'notes' => filled($payload['notes'] ?? null) ? trim((string) $payload['notes']) : null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->syncRunInputs($actor, $run->fresh());

            $this->auditLogger->record(
                eventType: 'payroll.adjustment.created',
                actor: $actor,
                metadata: [
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'adjustment_code' => $adjustment->adjustment_code,
                    'category' => $adjustment->category,
                    'amount' => $adjustment->amount,
                    'status' => $adjustment->status,
                ],
                entityType: 'payroll_adjustment',
                entityId: (string) $adjustment->id,
            );

            return $adjustment->load('employee');
        });
    }

    /**
     * @param  PayrollAdjustmentPayload  $payload
     */
    public function updateAdjustment(User $actor, PayrollRun $run, PayrollAdjustment $adjustment, array $payload): PayrollAdjustment
    {
        return DB::transaction(function () use ($actor, $run, $adjustment, $payload): PayrollAdjustment {
            $this->ensureRunCanAcceptAdjustments($run);

            $employee = Employee::query()->findOrFail((int) $payload['employee_id']);
            $effectiveDate = Carbon::parse((string) $payload['effective_date'], $actor->company->timezone)->startOfDay();
            $this->ensureDateWithinRun($run, $effectiveDate);

            $before = $adjustment->only([
                'employee_id',
                'adjustment_code',
                'name',
                'category',
                'amount',
                'effective_date',
                'status',
                'notes',
            ]);

            $adjustment->fill([
                'employee_id' => $employee->id,
                'adjustment_code' => strtoupper(trim((string) $payload['adjustment_code'])),
                'name' => trim((string) $payload['name']),
                'category' => $payload['category'],
                'amount' => round((float) $payload['amount'], 2),
                'effective_date' => $effectiveDate->toDateString(),
                'status' => $payload['status'] ?? $adjustment->status,
                'notes' => filled($payload['notes'] ?? null) ? trim((string) $payload['notes']) : null,
                'updated_by_user_id' => $actor->id,
            ]);
            $adjustment->save();

            $this->syncRunInputs($actor, $run->fresh());

            $this->auditLogger->record(
                eventType: 'payroll.adjustment.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $adjustment->only([
                        'employee_id',
                        'adjustment_code',
                        'name',
                        'category',
                        'amount',
                        'effective_date',
                        'status',
                        'notes',
                    ]),
                ],
                entityType: 'payroll_adjustment',
                entityId: (string) $adjustment->id,
            );

            return $adjustment->load('employee');
        });
    }

    /**
     * @return PayrollInputSummary
     */
    public function syncRunInputs(User $actor, PayrollRun $run): array
    {
        return DB::transaction(function () use ($actor, $run): array {
            $run->loadMissing('payrollPeriod');

            $period = $run->payrollPeriod;
            $periodEnd = $period->end_date->toDateString();

            $employees = Employee::query()
                ->where(function ($query) use ($period): void {
                    $query->where('employment_status', 'active')
                        ->orWhere(function ($terminatedQuery) use ($period): void {
                            $terminatedQuery->whereNotNull('terminated_at')
                                ->whereDate('terminated_at', '>=', $period->start_date->toDateString());
                        });
                })
                ->whereDate('date_of_joining', '<=', $periodEnd)
                ->orderBy('id')
                ->get();

            $compensations = $this->resolveCurrentCompensations($employees, $periodEnd);

            $scopedEmployees = $employees
                ->filter(fn (Employee $employee): bool => $compensations->has($employee->id))
                ->values();

            $attendanceRecords = AttendanceRecord::query()
                ->whereIn('employee_id', $scopedEmployees->pluck('id'))
                ->whereBetween('attendance_date', [
                    $period->start_date->toDateString(),
                    $periodEnd,
                ])
                ->get()
                ->groupBy('employee_id');

            $approvedLeaveRequests = LeaveRequest::query()
                ->with('leaveType')
                ->whereIn('employee_id', $scopedEmployees->pluck('id'))
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $periodEnd)
                ->whereDate('end_date', '>=', $period->start_date->toDateString())
                ->get()
                ->groupBy('employee_id');

            $adjustments = PayrollAdjustment::query()
                ->where('payroll_run_id', $run->id)
                ->where('status', 'active')
                ->get()
                ->groupBy('employee_id');

            PayrollInput::query()->where('payroll_run_id', $run->id)->delete();

            $summary = [
                'employee_count' => $scopedEmployees->count(),
                'input_count' => 0,
                'manual_adjustment_count' => 0,
                'attendance_record_count' => 0,
                'approved_leave_request_count' => 0,
                'total_worked_minutes' => 0,
                'total_overtime_minutes' => 0,
                'total_lop_days' => 0.0,
                'total_paid_leave_days' => 0.0,
                'total_unpaid_leave_days' => 0.0,
                'total_manual_adjustment_amount' => 0.0,
            ];

            foreach ($scopedEmployees as $employee) {
                $compensation = $compensations->get($employee->id);
                if (! $compensation) {
                    continue;
                }
                $employeeAttendance = $attendanceRecords->get($employee->id, collect());
                $employeeLeaves = $approvedLeaveRequests->get($employee->id, collect());
                $employeeAdjustments = $adjustments->get($employee->id, collect());

                $summary['attendance_record_count'] += $employeeAttendance->count();
                $summary['approved_leave_request_count'] += $employeeLeaves->count();
                $summary['manual_adjustment_count'] += $employeeAdjustments->count();

                $workedMinutes = (int) $employeeAttendance->sum('worked_minutes');
                $overtimeMinutes = (int) $employeeAttendance->sum('overtime_minutes');
                $lateMinutes = (int) $employeeAttendance->sum('late_minutes');
                $absentCount = $employeeAttendance->where('primary_status', 'absent')->count();
                $incompleteCount = $employeeAttendance->where('primary_status', 'incomplete')->count();
                $halfDayCount = $employeeAttendance->where('primary_status', 'half_day')->count();
                $lopDays = (float) $absentCount + (float) $incompleteCount + ($halfDayCount * 0.5);

                $paidLeaveDays = 0.0;
                $unpaidLeaveDays = 0.0;

                foreach ($employeeLeaves as $leaveRequest) {
                    $days = $this->overlapDays($leaveRequest->start_date, $leaveRequest->end_date, $period->start_date, $period->end_date);

                    if (($leaveRequest->leaveType->is_paid ?? true) === true) {
                        $paidLeaveDays += $days;
                    } else {
                        $unpaidLeaveDays += $days;
                    }
                }

                $summary['total_worked_minutes'] += $workedMinutes;
                $summary['total_overtime_minutes'] += $overtimeMinutes;
                $summary['total_lop_days'] += $lopDays;
                $summary['total_paid_leave_days'] += $paidLeaveDays;
                $summary['total_unpaid_leave_days'] += $unpaidLeaveDays;

                $this->createInputRow($actor, $run, $employee, $compensation, [
                    'source_type' => 'attendance_summary',
                    'input_code' => 'attendance_worked_minutes',
                    'unit' => 'minutes',
                    'quantity' => $workedMinutes,
                    'amount' => null,
                    'effective_date' => $periodEnd,
                    'metadata' => [
                        'attendance_record_count' => $employeeAttendance->count(),
                    ],
                ]);

                $this->createInputRow($actor, $run, $employee, $compensation, [
                    'source_type' => 'attendance_summary',
                    'input_code' => 'attendance_overtime_minutes',
                    'unit' => 'minutes',
                    'quantity' => $overtimeMinutes,
                    'amount' => null,
                    'effective_date' => $periodEnd,
                    'metadata' => [
                        'attendance_record_count' => $employeeAttendance->count(),
                    ],
                ]);

                $this->createInputRow($actor, $run, $employee, $compensation, [
                    'source_type' => 'attendance_summary',
                    'input_code' => 'attendance_late_minutes',
                    'unit' => 'minutes',
                    'quantity' => $lateMinutes,
                    'amount' => null,
                    'effective_date' => $periodEnd,
                    'metadata' => [
                        'attendance_record_count' => $employeeAttendance->count(),
                    ],
                ]);

                $this->createInputRow($actor, $run, $employee, $compensation, [
                    'source_type' => 'attendance_summary',
                    'input_code' => 'attendance_lop_days',
                    'unit' => 'days',
                    'quantity' => $lopDays,
                    'amount' => null,
                    'effective_date' => $periodEnd,
                    'metadata' => [
                        'absent_count' => $absentCount,
                        'incomplete_count' => $incompleteCount,
                        'half_day_count' => $halfDayCount,
                    ],
                ]);

                $this->createInputRow($actor, $run, $employee, $compensation, [
                    'source_type' => 'leave_summary',
                    'input_code' => 'approved_paid_leave_days',
                    'unit' => 'days',
                    'quantity' => round($paidLeaveDays, 2),
                    'amount' => null,
                    'effective_date' => $periodEnd,
                    'metadata' => [
                        'approved_leave_request_count' => $employeeLeaves->count(),
                    ],
                ]);

                $this->createInputRow($actor, $run, $employee, $compensation, [
                    'source_type' => 'leave_summary',
                    'input_code' => 'approved_unpaid_leave_days',
                    'unit' => 'days',
                    'quantity' => round($unpaidLeaveDays, 2),
                    'amount' => null,
                    'effective_date' => $periodEnd,
                    'metadata' => [
                        'approved_leave_request_count' => $employeeLeaves->count(),
                    ],
                ]);

                foreach ($employeeAdjustments as $adjustment) {
                    $signedAmount = $this->signedAdjustmentAmount($adjustment);
                    $summary['total_manual_adjustment_amount'] += $signedAmount;

                    $this->createInputRow($actor, $run, $employee, $compensation, [
                        'payroll_adjustment_id' => $adjustment->id,
                        'source_type' => 'manual_adjustment',
                        'input_code' => 'manual_adjustment_'.$adjustment->adjustment_code,
                        'unit' => 'currency',
                        'quantity' => null,
                        'amount' => $signedAmount,
                        'effective_date' => $adjustment->effective_date?->toDateString(),
                        'source_record_id' => $adjustment->id,
                        'metadata' => [
                            'adjustment_code' => $adjustment->adjustment_code,
                            'category' => $adjustment->category,
                            'raw_amount' => $adjustment->amount,
                            'notes' => $adjustment->notes,
                        ],
                    ]);
                }
            }

            $summary['input_count'] = PayrollInput::query()->where('payroll_run_id', $run->id)->count();
            $summary['total_lop_days'] = round($summary['total_lop_days'], 2);
            $summary['total_paid_leave_days'] = round($summary['total_paid_leave_days'], 2);
            $summary['total_unpaid_leave_days'] = round($summary['total_unpaid_leave_days'], 2);
            $summary['total_manual_adjustment_amount'] = round($summary['total_manual_adjustment_amount'], 2);

            $run->forceFill([
                'input_summary' => $summary,
                'inputs_generated_at' => now(),
            ])->save();

            $this->auditLogger->record(
                eventType: 'payroll.inputs.synced',
                actor: $actor,
                metadata: [
                    'payroll_run_id' => $run->id,
                    'summary' => $summary,
                ],
                entityType: 'payroll_run',
                entityId: (string) $run->id,
            );

            return $summary;
        });
    }

    public function clearRunInputs(PayrollRun $run): void
    {
        DB::transaction(function () use ($run): void {
            PayrollInput::query()->where('payroll_run_id', $run->id)->delete();

            $run->forceFill([
                'input_summary' => null,
                'inputs_generated_at' => null,
            ])->save();
        });
    }

    /**
     * @param  PayrollInputRowAttributes  $attributes
     */
    private function createInputRow(User $actor, PayrollRun $run, Employee $employee, EmployeeCompensation $compensation, array $attributes): void
    {
        PayrollInput::query()->create([
            'company_id' => $run->company_id,
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'employee_compensation_id' => $compensation->id,
            'payroll_adjustment_id' => $attributes['payroll_adjustment_id'] ?? null,
            'source_type' => $attributes['source_type'],
            'input_code' => $attributes['input_code'],
            'unit' => $attributes['unit'] ?? null,
            'quantity' => isset($attributes['quantity']) ? round((float) $attributes['quantity'], 2) : null,
            'amount' => isset($attributes['amount'])
                ? round((float) $attributes['amount'], 2)
                : null,
            'effective_date' => $attributes['effective_date'] ?? null,
            'source_record_id' => $attributes['source_record_id'] ?? null,
            'metadata' => $attributes['metadata'] ?? [],
            'created_by_user_id' => $actor->id,
            'updated_by_user_id' => $actor->id,
        ]);
    }

    /**
     * @param  Collection<int, Employee>  $employees
     * @return Collection<int, EmployeeCompensation>
     */
    private function resolveCurrentCompensations(Collection $employees, string $periodEnd): Collection
    {
        return EmployeeCompensation::query()
            ->whereIn('employee_id', $employees->pluck('id'))
            ->whereDate('effective_from', '<=', $periodEnd)
            ->orderBy('employee_id')
            ->orderByDesc('effective_from')
            ->orderByDesc('revision_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('employee_id')
            ->map(fn (Collection $items): ?EmployeeCompensation => $items->first())
            ->filter(fn (?EmployeeCompensation $compensation): bool => $compensation !== null);
    }

    private function overlapDays(Carbon $startDate, Carbon $endDate, Carbon $periodStart, Carbon $periodEnd): float
    {
        $overlapStart = $startDate->greaterThan($periodStart) ? $startDate->copy() : $periodStart->copy();
        $overlapEnd = $endDate->lessThan($periodEnd) ? $endDate->copy() : $periodEnd->copy();

        if ($overlapEnd->lt($overlapStart)) {
            return 0.0;
        }

        return (float) $overlapStart->diffInDays($overlapEnd) + 1;
    }

    private function signedAdjustmentAmount(PayrollAdjustment $adjustment): float
    {
        $amount = (float) $adjustment->amount;

        return $adjustment->category === 'deduction'
            ? $amount * -1
            : $amount;
    }

    private function ensureRunCanAcceptAdjustments(PayrollRun $run): void
    {
        $run->loadMissing('payrollPeriod');

        if ($run->payrollPeriod?->status !== 'prepared') {
            throw ValidationException::withMessages([
                'payroll_run_id' => ['Payroll adjustments can only be managed for prepared payroll runs.'],
            ]);
        }

        if (! in_array($run->status, ['ready', 'blocked'], true)) {
            throw ValidationException::withMessages([
                'payroll_run_id' => ['Payroll adjustments require the run to be ready or reopened.'],
            ]);
        }
    }

    private function ensureDateWithinRun(PayrollRun $run, Carbon $effectiveDate): void
    {
        if ($effectiveDate->lt($run->start_date->copy()->startOfDay()) || $effectiveDate->gt($run->end_date->copy()->startOfDay())) {
            throw ValidationException::withMessages([
                'effective_date' => ['Adjustment effective date must fall within the payroll run date range.'],
            ]);
        }
    }
}
