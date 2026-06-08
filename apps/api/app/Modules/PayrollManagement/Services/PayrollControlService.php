<?php

namespace App\Modules\PayrollManagement\Services;

use App\Models\PayrollCalendar;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollControlService
{
    public function __construct(
        private readonly PayrollPrerequisiteService $prerequisiteService,
        private readonly PayrollInputService $payrollInputService,
        private readonly PayrollCalculationService $payrollCalculationService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function searchPeriods(array $filters): LengthAwarePaginator
    {
        return PayrollPeriod::query()
            ->with(['payrollCalendar', 'latestRun'])
            ->when(
                array_key_exists('payroll_calendar_id', $filters),
                fn (Builder $builder) => $builder->where('payroll_calendar_id', $filters['payroll_calendar_id']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', $filters['status']),
            )
            ->when(
                array_key_exists('date_from', $filters),
                fn (Builder $builder) => $builder->whereDate('end_date', '>=', $filters['date_from']),
            )
            ->when(
                array_key_exists('date_to', $filters),
                fn (Builder $builder) => $builder->whereDate('start_date', '<=', $filters['date_to']),
            )
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function searchRuns(array $filters): LengthAwarePaginator
    {
        return PayrollRun::query()
            ->with(['payrollPeriod.payrollCalendar'])
            ->when(
                array_key_exists('payroll_period_id', $filters),
                fn (Builder $builder) => $builder->where('payroll_period_id', $filters['payroll_period_id']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', $filters['status']),
            )
            ->when(
                array_key_exists('date_from', $filters),
                fn (Builder $builder) => $builder->whereDate('end_date', '>=', $filters['date_from']),
            )
            ->when(
                array_key_exists('date_to', $filters),
                fn (Builder $builder) => $builder->whereDate('start_date', '<=', $filters['date_to']),
            )
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function createCalendar(User $actor, array $payload): PayrollCalendar
    {
        return DB::transaction(function () use ($actor, $payload): PayrollCalendar {
            $payload = $this->normalizeCalendarPayload($payload, $actor);

            $calendar = PayrollCalendar::query()->create([
                ...$payload,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            if ($calendar->is_default) {
                $this->clearOtherDefaultCalendars($calendar);
            }

            $this->auditLogger->record(
                eventType: 'payroll.calendar.created',
                actor: $actor,
                metadata: $calendar->only([
                    'name',
                    'frequency',
                    'timezone',
                    'payroll_day',
                    'payroll_weekday',
                    'is_default',
                    'status',
                ]),
                entityType: 'payroll_calendar',
                entityId: (string) $calendar->id,
            );

            return $calendar->refresh();
        });
    }

    public function updateCalendar(User $actor, PayrollCalendar $calendar, array $payload): PayrollCalendar
    {
        return DB::transaction(function () use ($actor, $calendar, $payload): PayrollCalendar {
            $before = $calendar->only([
                'name',
                'frequency',
                'timezone',
                'payroll_day',
                'payroll_weekday',
                'is_default',
                'status',
            ]);

            $calendar->fill([
                ...$this->normalizeCalendarPayload($payload, $actor),
                'updated_by_user_id' => $actor->id,
            ]);
            $calendar->save();

            if ($calendar->is_default) {
                $this->clearOtherDefaultCalendars($calendar);
            }

            $this->auditLogger->record(
                eventType: 'payroll.calendar.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $calendar->only([
                        'name',
                        'frequency',
                        'timezone',
                        'payroll_day',
                        'payroll_weekday',
                        'is_default',
                        'status',
                    ]),
                ],
                entityType: 'payroll_calendar',
                entityId: (string) $calendar->id,
            );

            return $calendar->refresh();
        });
    }

    public function createPeriod(User $actor, array $payload): PayrollPeriod
    {
        return DB::transaction(function () use ($actor, $payload): PayrollPeriod {
            $calendar = PayrollCalendar::query()->findOrFail((int) $payload['payroll_calendar_id']);
            $startDate = Carbon::parse((string) $payload['start_date'], $actor->company->timezone)->startOfDay();
            $endDate = Carbon::parse((string) $payload['end_date'], $actor->company->timezone)->startOfDay();
            $payrollDate = Carbon::parse((string) $payload['payroll_date'], $actor->company->timezone)->startOfDay();

            $duplicateExists = PayrollPeriod::query()
                ->where('payroll_calendar_id', $calendar->id)
                ->whereDate('start_date', $startDate->toDateString())
                ->whereDate('end_date', $endDate->toDateString())
                ->exists();

            if ($duplicateExists) {
                throw ValidationException::withMessages([
                    'start_date' => ['A payroll period already exists for the selected calendar and date range.'],
                ]);
            }

            $period = PayrollPeriod::query()->create([
                'company_id' => $actor->company_id,
                'payroll_calendar_id' => $calendar->id,
                'name' => trim((string) $payload['name']),
                'frequency' => $calendar->frequency,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'payroll_date' => $payrollDate->toDateString(),
                'status' => 'draft',
                'opened_at' => null,
                'prepared_at' => null,
                'closed_at' => null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'payroll.period.created',
                actor: $actor,
                metadata: $period->only([
                    'payroll_calendar_id',
                    'name',
                    'frequency',
                    'start_date',
                    'end_date',
                    'payroll_date',
                    'status',
                ]),
                entityType: 'payroll_period',
                entityId: (string) $period->id,
            );

            return $period->load(['payrollCalendar', 'latestRun']);
        });
    }

    public function openPeriod(User $actor, PayrollPeriod $period): PayrollPeriod
    {
        return DB::transaction(function () use ($actor, $period): PayrollPeriod {
            if ($period->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => ['Only draft payroll periods can be opened.'],
                ]);
            }

            $period->forceFill([
                'status' => 'open',
                'opened_at' => now(),
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'payroll.period.opened',
                actor: $actor,
                metadata: [
                    'status' => $period->status,
                    'opened_at' => $period->opened_at?->toIso8601String(),
                ],
                entityType: 'payroll_period',
                entityId: (string) $period->id,
            );

            return $period->refresh()->load(['payrollCalendar', 'latestRun']);
        });
    }

    public function preparePeriod(User $actor, PayrollPeriod $period): array
    {
        return DB::transaction(function () use ($actor, $period): array {
            if (! in_array($period->status, ['open', 'prepared'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Only open payroll periods can be prepared.'],
                ]);
            }

            $existingRun = $period->runs()->first();

            if ($existingRun && in_array($existingRun->status, ['approved', 'locked'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Approved or locked payroll runs must be reopened before preparation can run again.'],
                ]);
            }

            $this->ensureNoOverlappingRuns($period, $existingRun?->id);

            $snapshot = $this->prerequisiteService->buildSnapshot($period);
            $summary = $snapshot['summary'];
            $runStatus = $summary['ready_for_calculation'] ? 'ready' : 'blocked';

            if ($existingRun) {
                $existingRun->forceFill([
                    'name' => $this->makeRunName($period),
                    'frequency' => $period->frequency,
                    'start_date' => $period->start_date?->toDateString(),
                    'end_date' => $period->end_date?->toDateString(),
                    'status' => $runStatus,
                    'prerequisite_snapshot' => $snapshot,
                    'prerequisite_summary' => $summary,
                    'prepared_at' => now(),
                    'updated_by_user_id' => $actor->id,
                ])->save();

                $run = $existingRun->refresh();
            } else {
                $run = PayrollRun::query()->create([
                    'company_id' => $period->company_id,
                    'payroll_period_id' => $period->id,
                    'name' => $this->makeRunName($period),
                    'frequency' => $period->frequency,
                    'start_date' => $period->start_date?->toDateString(),
                    'end_date' => $period->end_date?->toDateString(),
                    'status' => $runStatus,
                    'prerequisite_snapshot' => $snapshot,
                    'prerequisite_summary' => $summary,
                    'prepared_at' => now(),
                    'closed_at' => null,
                    'created_by_user_id' => $actor->id,
                    'updated_by_user_id' => $actor->id,
                ]);

                $this->auditLogger->record(
                    eventType: 'payroll.run.created',
                    actor: $actor,
                    metadata: [
                        'payroll_period_id' => $period->id,
                        'name' => $run->name,
                        'status' => $run->status,
                        'prerequisite_summary' => $summary,
                    ],
                    entityType: 'payroll_run',
                    entityId: (string) $run->id,
                );
            }

            $period->forceFill([
                'status' => 'prepared',
                'prepared_at' => now(),
                'updated_by_user_id' => $actor->id,
            ])->save();

            if ($summary['ready_for_calculation']) {
                $summary['input_summary'] = $this->payrollInputService->syncRunInputs($actor, $run->fresh());
                $run = $run->fresh();
            } else {
                $this->payrollInputService->clearRunInputs($run);
                $run = $run->fresh();
            }

            $this->auditLogger->record(
                eventType: 'payroll.period.prepared',
                actor: $actor,
                metadata: [
                    'payroll_run_id' => $run->id,
                    'payroll_run_status' => $run->status,
                    'prerequisite_summary' => $summary,
                    'input_summary' => $run->input_summary,
                ],
                entityType: 'payroll_period',
                entityId: (string) $period->id,
            );

            return [
                'period' => $period->refresh()->load(['payrollCalendar', 'latestRun']),
                'prerequisites' => $snapshot,
                'run' => $run->refresh()->load(['payrollPeriod.payrollCalendar']),
            ];
        });
    }

    public function closePeriod(User $actor, PayrollPeriod $period): PayrollPeriod
    {
        return DB::transaction(function () use ($actor, $period): PayrollPeriod {
            if ($period->status !== 'prepared') {
                throw ValidationException::withMessages([
                    'status' => ['Only prepared payroll periods can be closed.'],
                ]);
            }

            $run = $period->latestRun()->first();

            if (! $run) {
                throw ValidationException::withMessages([
                    'status' => ['The payroll period must be prepared before it can be closed.'],
                ]);
            }

            if ($run->status !== 'locked') {
                throw ValidationException::withMessages([
                    'status' => ['The payroll run must be locked before the period can be closed.'],
                ]);
            }

            $period->forceFill([
                'status' => 'closed',
                'closed_at' => now(),
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'payroll.period.closed',
                actor: $actor,
                metadata: [
                    'payroll_run_id' => $run->id,
                    'payroll_run_status' => $run->status,
                    'closed_at' => $period->closed_at?->toIso8601String(),
                ],
                entityType: 'payroll_period',
                entityId: (string) $period->id,
            );

            return $period->refresh()->load(['payrollCalendar', 'latestRun']);
        });
    }

    public function previewPrerequisites(PayrollPeriod $period): array
    {
        return $this->prerequisiteService->buildSnapshot($period);
    }

    public function calculateRun(User $actor, PayrollRun $run): PayrollRun
    {
        return $this->payrollCalculationService->calculateRun($actor, $run);
    }

    public function approveRun(User $actor, PayrollRun $run, array $payload): PayrollRun
    {
        return $this->payrollCalculationService->approveRun($actor, $run, $payload);
    }

    public function lockRun(User $actor, PayrollRun $run): PayrollRun
    {
        return $this->payrollCalculationService->lockRun($actor, $run);
    }

    public function reopenRun(User $actor, PayrollRun $run, array $payload): PayrollRun
    {
        return $this->payrollCalculationService->reopenRun($actor, $run, $payload);
    }

    private function normalizeCalendarPayload(array $payload, User $actor): array
    {
        return [
            'company_id' => $actor->company_id,
            'name' => trim((string) $payload['name']),
            'frequency' => $payload['frequency'],
            'timezone' => trim((string) $payload['timezone']),
            'payroll_day' => $payload['payroll_day'] ?? null,
            'payroll_weekday' => $payload['payroll_weekday'] ?? null,
            'is_default' => (bool) ($payload['is_default'] ?? false),
            'status' => $payload['status'],
        ];
    }

    private function clearOtherDefaultCalendars(PayrollCalendar $calendar): void
    {
        PayrollCalendar::query()
            ->whereKeyNot($calendar->id)
            ->where('is_default', true)
            ->update([
                'is_default' => false,
                'updated_at' => now(),
            ]);
    }

    private function ensureNoOverlappingRuns(PayrollPeriod $period, ?int $currentRunId = null): void
    {
        $query = PayrollRun::query()
            ->whereDate('start_date', '<=', $period->end_date)
            ->whereDate('end_date', '>=', $period->start_date)
            ->where('payroll_period_id', '!=', $period->id);

        if ($currentRunId !== null) {
            $query->whereKeyNot($currentRunId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'start_date' => ['An overlapping payroll run already exists for the selected date range.'],
            ]);
        }
    }

    private function makeRunName(PayrollPeriod $period): string
    {
        return sprintf('%s Preparation Run', $period->name);
    }
}
