<?php

namespace App\Modules\LeaveManagement\Services;

use App\Models\Employee;
use App\Models\LeaveAccrual;
use App\Models\LeaveBalance;
use App\Models\LeaveBalanceEntry;
use App\Models\LeaveEncashment;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LeaveBalanceService
{
    public function __construct(
        private readonly LeaveBalanceAccessScopeService $accessScopeService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function syncProjectedBalance(User $actor, LeaveAccrual $accrual): LeaveBalance
    {
        return DB::transaction(function () use ($actor, $accrual): LeaveBalance {
            $balance = LeaveBalance::query()->firstOrCreate(
                [
                    'employee_id' => $accrual->employee_id,
                    'leave_type_id' => $accrual->leave_type_id,
                ],
                [
                    'leave_policy_id' => $accrual->leave_policy_id,
                    'policy_version' => $accrual->policy_version,
                    'available_days' => 0,
                    'booked_days' => 0,
                    'used_days' => 0,
                    'accrued_days' => 0,
                    'carry_forward_days' => 0,
                    'projected_encashable_days' => 0,
                    'current_period_start' => $accrual->period_start,
                    'current_period_end' => $accrual->period_end,
                    'last_calculation_hash' => $accrual->calculation_hash,
                    'status' => 'active',
                ],
            );

            $this->removeSupersededEntries($accrual, $balance);
            $this->syncAccrualEntries($actor, $balance, $accrual);
            $this->recalculateBalanceSnapshot($balance, $accrual);

            $this->auditLogger->record(
                eventType: 'leave.balance.synced',
                actor: $actor,
                metadata: [
                    'employee_id' => $balance->employee_id,
                    'leave_type_id' => $balance->leave_type_id,
                    'leave_policy_id' => $balance->leave_policy_id,
                    'policy_version' => $balance->policy_version,
                    'available_days' => $balance->available_days,
                    'booked_days' => $balance->booked_days,
                    'used_days' => $balance->used_days,
                    'accrued_days' => $balance->accrued_days,
                    'carry_forward_days' => $balance->carry_forward_days,
                    'projected_encashable_days' => $balance->projected_encashable_days,
                    'last_calculation_hash' => $balance->last_calculation_hash,
                ],
                entityType: 'leave_balance',
                entityId: (string) $balance->id,
            );

            return $balance->refresh()->load(['employee', 'leaveType', 'leavePolicy']);
        });
    }

    public function reserveForLeaveRequest(User $actor, LeaveBalance $balance, LeaveRequest $leaveRequest): LeaveBalance
    {
        return DB::transaction(function () use ($actor, $balance, $leaveRequest): LeaveBalance {
            LeaveBalanceEntry::query()->updateOrCreate(
                [
                    'leave_balance_id' => $balance->id,
                    'entry_type' => 'booking',
                    'reference_type' => 'leave_request',
                    'reference_id' => $leaveRequest->id,
                ],
                [
                    'company_id' => $balance->company_id,
                    'employee_id' => $balance->employee_id,
                    'leave_type_id' => $balance->leave_type_id,
                    'leave_policy_id' => $balance->leave_policy_id,
                    'quantity_days' => round($leaveRequest->total_days * -1, 2),
                    'balance_before_days' => 0,
                    'balance_after_days' => 0,
                    'effective_on' => $leaveRequest->start_date,
                    'metadata' => [
                        'leave_request_id' => $leaveRequest->id,
                        'status' => $leaveRequest->status,
                        'start_date' => $leaveRequest->start_date?->toDateString(),
                        'end_date' => $leaveRequest->end_date?->toDateString(),
                        'policy_version' => $leaveRequest->policy_version,
                    ],
                    'created_by_user_id' => $actor->id,
                ],
            );

            LeaveBalanceEntry::query()
                ->where('leave_balance_id', $balance->id)
                ->where('entry_type', 'booking_release')
                ->where('reference_type', 'leave_request')
                ->where('reference_id', $leaveRequest->id)
                ->delete();

            $this->recalculateBalanceSnapshot($balance);

            return $balance->refresh()->load(['employee', 'leaveType', 'leavePolicy']);
        });
    }

    public function releaseLeaveRequestReservation(
        User $actor,
        LeaveBalance $balance,
        LeaveRequest $leaveRequest,
        array $metadata = [],
    ): LeaveBalance {
        return DB::transaction(function () use ($actor, $balance, $leaveRequest, $metadata): LeaveBalance {
            LeaveBalanceEntry::query()->updateOrCreate(
                [
                    'leave_balance_id' => $balance->id,
                    'entry_type' => 'booking_release',
                    'reference_type' => 'leave_request',
                    'reference_id' => $leaveRequest->id,
                ],
                [
                    'company_id' => $balance->company_id,
                    'employee_id' => $balance->employee_id,
                    'leave_type_id' => $balance->leave_type_id,
                    'leave_policy_id' => $balance->leave_policy_id,
                    'quantity_days' => round($leaveRequest->total_days, 2),
                    'balance_before_days' => 0,
                    'balance_after_days' => 0,
                    'effective_on' => $leaveRequest->start_date,
                    'metadata' => array_filter([
                        'leave_request_id' => $leaveRequest->id,
                        'status' => $leaveRequest->status,
                        'start_date' => $leaveRequest->start_date?->toDateString(),
                        'end_date' => $leaveRequest->end_date?->toDateString(),
                        'policy_version' => $leaveRequest->policy_version,
                        ...$metadata,
                    ], fn (mixed $value): bool => $value !== null),
                    'created_by_user_id' => $actor->id,
                ],
            );

            $this->recalculateBalanceSnapshot($balance);

            return $balance->refresh()->load(['employee', 'leaveType', 'leavePolicy']);
        });
    }

    /**
     * @return Collection<int, LeaveBalance>
     */
    public function listBalances(User $actor, array $filters): Collection
    {
        return $this->accessScopeService
            ->balancesQuery($actor)
            ->when(
                filled($filters['employee_id'] ?? null),
                fn ($query) => $query->where('employee_id', (int) $filters['employee_id']),
            )
            ->when(
                filled($filters['leave_type_id'] ?? null),
                fn ($query) => $query->where('leave_type_id', (int) $filters['leave_type_id']),
            )
            ->orderBy('employee_id')
            ->orderBy('leave_type_id')
            ->get();
    }

    /**
     * @return array{employee: array<string, mixed>, balances: Collection<int, LeaveBalance>, history: Collection<int, LeaveBalanceEntry>}
     */
    public function showEmployeeBalances(User $actor, int $employeeId, array $filters): array
    {
        $employee = $this->accessScopeService->resolveAccessibleEmployee($actor, $employeeId);

        $balances = $this->accessScopeService
            ->balancesQuery($actor, ['employee', 'leaveType', 'leavePolicy'])
            ->where('employee_id', $employee->id)
            ->when(
                filled($filters['leave_type_id'] ?? null),
                fn ($query) => $query->where('leave_type_id', (int) $filters['leave_type_id']),
            )
            ->orderBy('leave_type_id')
            ->get();

        $history = LeaveBalanceEntry::query()
            ->where('employee_id', $employee->id)
            ->when(
                filled($filters['leave_type_id'] ?? null),
                fn ($query) => $query->where('leave_type_id', (int) $filters['leave_type_id']),
            )
            ->orderByDesc('effective_on')
            ->orderByDesc('id')
            ->get();

        return [
            'employee' => (new EmployeeReferenceResource($employee))->resolve(),
            'balances' => $balances,
            'history' => $history,
        ];
    }

    private function removeSupersededEntries(LeaveAccrual $accrual, LeaveBalance $balance): void
    {
        $supersededAccrualIds = LeaveAccrual::query()
            ->where('employee_id', $accrual->employee_id)
            ->where('leave_policy_id', $accrual->leave_policy_id)
            ->where('period_start', '>=', $accrual->period_start?->toDateString())
            ->where('period_start', '<', $accrual->period_start?->copy()->addDay()->toDateString())
            ->where('period_end', '>=', $accrual->period_end?->toDateString())
            ->where('period_end', '<', $accrual->period_end?->copy()->addDay()->toDateString())
            ->whereKeyNot($accrual->id)
            ->pluck('id');

        if ($supersededAccrualIds->isEmpty()) {
            return;
        }

        LeaveAccrual::query()
            ->whereKey($supersededAccrualIds)
            ->update(['status' => 'superseded']);

        LeaveEncashment::query()
            ->whereIn('leave_accrual_id', $supersededAccrualIds)
            ->update(['status' => 'superseded']);

        LeaveBalanceEntry::query()
            ->where('leave_balance_id', $balance->id)
            ->where('reference_type', 'leave_accrual')
            ->whereIn('reference_id', $supersededAccrualIds)
            ->delete();
    }

    private function syncAccrualEntries(User $actor, LeaveBalance $balance, LeaveAccrual $accrual): void
    {
        $events = [
            'opening_balance' => [
                'quantity_days' => $accrual->opening_balance_days,
                'effective_on' => $accrual->period_start,
                'metadata' => ['period' => 'opening_balance_seed'],
            ],
            'carry_forward' => [
                'quantity_days' => $accrual->carry_forward_days,
                'effective_on' => $accrual->period_start,
                'metadata' => ['period' => 'carry_forward'],
            ],
            'accrual' => [
                'quantity_days' => $accrual->accrued_days,
                'effective_on' => $accrual->period_end,
                'metadata' => ['frequency' => $accrual->accrual_frequency],
            ],
            'usage_projection' => [
                'quantity_days' => round($accrual->used_days_in_period * -1, 2),
                'effective_on' => $accrual->period_end,
                'metadata' => ['source' => 'accrual_preview'],
            ],
        ];

        foreach ($events as $entryType => $event) {
            if (round((float) $event['quantity_days'], 2) === 0.0) {
                LeaveBalanceEntry::query()
                    ->where('leave_balance_id', $balance->id)
                    ->where('entry_type', $entryType)
                    ->where('reference_type', 'leave_accrual')
                    ->where('reference_id', $accrual->id)
                    ->delete();

                continue;
            }

            LeaveBalanceEntry::query()->updateOrCreate(
                [
                    'leave_balance_id' => $balance->id,
                    'entry_type' => $entryType,
                    'reference_type' => 'leave_accrual',
                    'reference_id' => $accrual->id,
                ],
                [
                    'employee_id' => $balance->employee_id,
                    'leave_type_id' => $balance->leave_type_id,
                    'leave_policy_id' => $balance->leave_policy_id,
                    'quantity_days' => round((float) $event['quantity_days'], 2),
                    'balance_before_days' => 0,
                    'balance_after_days' => 0,
                    'effective_on' => $event['effective_on'],
                    'metadata' => [
                        ...$event['metadata'],
                        'period_start' => $accrual->period_start?->toDateString(),
                        'period_end' => $accrual->period_end?->toDateString(),
                        'policy_version' => $accrual->policy_version,
                        'calculation_hash' => $accrual->calculation_hash,
                    ],
                    'created_by_user_id' => $actor->id,
                ],
            );
        }
    }

    private function recalculateBalanceSnapshot(LeaveBalance $balance, ?LeaveAccrual $accrual = null): void
    {
        $entries = LeaveBalanceEntry::query()
            ->where('leave_balance_id', $balance->id)
            ->get()
            ->sort(function (LeaveBalanceEntry $left, LeaveBalanceEntry $right): int {
                $leftPriority = $this->entryPriority($left->entry_type);
                $rightPriority = $this->entryPriority($right->entry_type);

                return [$left->effective_on?->toDateString(), $leftPriority, $left->id]
                    <=> [$right->effective_on?->toDateString(), $rightPriority, $right->id];
            })
            ->values();

        $runningBalance = 0.0;
        $bookedDays = 0.0;
        $usedDays = 0.0;
        $accruedDays = 0.0;
        $carryForwardDays = 0.0;

        foreach ($entries as $entry) {
            $before = round($runningBalance, 2);
            $runningBalance = round($runningBalance + (float) $entry->quantity_days, 2);

            $entry->forceFill([
                'balance_before_days' => $before,
                'balance_after_days' => $runningBalance,
            ])->save();

            if ($entry->entry_type === 'booking') {
                $bookedDays = round($bookedDays + abs((float) $entry->quantity_days), 2);
            }

            if ($entry->entry_type === 'booking_release') {
                $bookedDays = round(max(0, $bookedDays - abs((float) $entry->quantity_days)), 2);
            }

            if (in_array($entry->entry_type, ['usage', 'usage_projection'], true)) {
                $usedDays = round($usedDays + abs((float) $entry->quantity_days), 2);
            }

            if ($entry->entry_type === 'accrual') {
                $accruedDays = round($accruedDays + (float) $entry->quantity_days, 2);
            }

            if ($entry->entry_type === 'carry_forward') {
                $carryForwardDays = round($carryForwardDays + (float) $entry->quantity_days, 2);
            }
        }

        $balance->forceFill([
            'leave_policy_id' => $accrual?->leave_policy_id ?? $balance->leave_policy_id,
            'policy_version' => $accrual?->policy_version ?? $balance->policy_version,
            'available_days' => max($runningBalance, 0),
            'booked_days' => $bookedDays,
            'used_days' => $usedDays,
            'accrued_days' => $accruedDays,
            'carry_forward_days' => $carryForwardDays,
            'projected_encashable_days' => $accrual?->encashable_days ?? $balance->projected_encashable_days,
            'current_period_start' => $accrual?->period_start ?? $balance->current_period_start,
            'current_period_end' => $accrual?->period_end ?? $balance->current_period_end,
            'last_calculation_hash' => $accrual?->calculation_hash ?? $balance->last_calculation_hash,
            'status' => 'active',
        ])->save();
    }

    private function entryPriority(string $entryType): int
    {
        return match ($entryType) {
            'opening_balance' => 10,
            'carry_forward' => 20,
            'accrual' => 30,
            'booking' => 40,
            'booking_release' => 45,
            'usage_projection' => 50,
            'usage' => 60,
            default => 90,
        };
    }
}
