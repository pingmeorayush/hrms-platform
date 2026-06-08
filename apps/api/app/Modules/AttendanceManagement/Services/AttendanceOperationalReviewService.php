<?php

namespace App\Modules\AttendanceManagement\Services;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceOperationalReviewService
{
    public function __construct(
        private readonly AttendanceAccessScopeService $attendanceAccessScopeService,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @return array{window_date:string, summary:array<string,int>, items:Collection<int, AttendanceRecord>}
     */
    public function overview(User $actor, array $filters): array
    {
        $windowDate = $this->resolveWindowDate($actor->company, $filters['date'] ?? null);
        $records = $this->recordsForWindow($actor, $windowDate, $filters);

        $summary = [
            'total_records' => $records->count(),
            'present_count' => $records->where('primary_status', 'present')->count(),
            'absent_count' => $records->where('primary_status', 'absent')->count(),
            'half_day_count' => $records->where('primary_status', 'half_day')->count(),
            'incomplete_count' => $records->where('primary_status', 'incomplete')->count(),
            'holiday_count' => $records->where('primary_status', 'holiday')->count(),
            'weekend_count' => $records->where('primary_status', 'weekend')->count(),
            'late_count' => $records->filter(fn (AttendanceRecord $record): bool => (bool) $record->is_late)->count(),
            'pending_correction_count' => $records->filter(
                fn (AttendanceRecord $record): bool => $record->corrections->isNotEmpty(),
            )->count(),
            'checked_in_count' => $records->filter(
                fn (AttendanceRecord $record): bool => $record->check_in_at !== null && $record->check_out_at === null,
            )->count(),
            'checked_out_count' => $records->filter(
                fn (AttendanceRecord $record): bool => $record->check_in_at !== null && $record->check_out_at !== null,
            )->count(),
        ];

        $this->auditLogger->record(
            eventType: 'attendance.review.operational_viewed',
            actor: $actor,
            metadata: [
                'window_date' => $windowDate->toDateString(),
                'employee_id' => $filters['employee_id'] ?? null,
                'record_count' => $records->count(),
            ],
            entityType: 'attendance_review',
            entityId: $windowDate->toDateString(),
        );

        return [
            'window_date' => $windowDate->toDateString(),
            'summary' => $summary,
            'items' => $records,
        ];
    }

    /**
     * @return array{window_date:string, summary:array<string,int>, attendance_items:Collection<int, AttendanceRecord>, correction_items:Collection<int, AttendanceCorrection>}
     */
    public function pendingExceptions(User $actor, array $filters): array
    {
        $windowDate = $this->resolveWindowDate($actor->company, $filters['date'] ?? null);
        $records = $this->recordsForWindow($actor, $windowDate, $filters)->filter(
            fn (AttendanceRecord $record): bool => $this->isExceptionRecord($record),
        )->values();

        $pendingCorrections = $this->attendanceAccessScopeService
            ->attendanceCorrectionsQuery(
                $actor,
                [
                    'employee',
                    'requester',
                    'latestActor',
                    'workflowInstance.tasks.assignee',
                    'workflowInstance.tasks.actor',
                ],
                includeSelfForApprovers: false,
            )
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $summary = [
            'exception_record_count' => $records->count(),
            'late_record_count' => $records->filter(fn (AttendanceRecord $record): bool => (bool) $record->is_late)->count(),
            'absent_record_count' => $records->where('primary_status', 'absent')->count(),
            'half_day_record_count' => $records->where('primary_status', 'half_day')->count(),
            'incomplete_record_count' => $records->where('primary_status', 'incomplete')->count(),
            'pending_correction_record_count' => $records->filter(
                fn (AttendanceRecord $record): bool => $record->corrections->isNotEmpty(),
            )->count(),
            'pending_correction_request_count' => $pendingCorrections->count(),
        ];

        $this->auditLogger->record(
            eventType: 'attendance.review.pending_exceptions_viewed',
            actor: $actor,
            metadata: [
                'window_date' => $windowDate->toDateString(),
                'employee_id' => $filters['employee_id'] ?? null,
                'exception_record_count' => $records->count(),
                'pending_correction_request_count' => $pendingCorrections->count(),
            ],
            entityType: 'attendance_review',
            entityId: $windowDate->toDateString(),
        );

        return [
            'window_date' => $windowDate->toDateString(),
            'summary' => $summary,
            'attendance_items' => $records,
            'correction_items' => $pendingCorrections,
        ];
    }

    private function resolveWindowDate(Company $company, ?string $value): Carbon
    {
        return $value !== null
            ? Carbon::parse($value, $company->timezone)->startOfDay()
            : now($company->timezone)->startOfDay();
    }

    /**
     * @return Collection<int, AttendanceRecord>
     */
    private function recordsForWindow(User $actor, Carbon $windowDate, array $filters): Collection
    {
        return $this->attendanceAccessScopeService
            ->attendanceRecordsQuery(
                $actor,
                [
                    'employee',
                    'shift',
                    'corrections' => fn ($query) => $query
                        ->where('status', 'pending')
                        ->with('requester'),
                ],
                includeSelfForApprovers: false,
            )
            ->where('attendance_date', '>=', $windowDate->toDateString())
            ->where('attendance_date', '<', $windowDate->copy()->addDay()->toDateString())
            ->when(
                array_key_exists('employee_id', $filters),
                fn ($query) => $query->where('employee_id', $filters['employee_id']),
            )
            ->orderBy('employee_id')
            ->orderBy('id')
            ->get();
    }

    private function isExceptionRecord(AttendanceRecord $record): bool
    {
        return in_array($record->primary_status, ['absent', 'half_day', 'incomplete'], true)
            || (bool) $record->is_late
            || $record->corrections->isNotEmpty();
    }
}
