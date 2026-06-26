<?php

namespace App\Modules\AttendanceManagement\Listeners;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\User;
use App\Models\WorkflowTask;
use App\Modules\AttendanceManagement\Services\AttendanceCalculationService;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Notifications\Services\NotificationService;
use App\Modules\Platform\Workflow\Events\WorkflowInstanceTransitioned;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncAttendanceCorrectionWorkflowState
{
    public function __construct(
        private readonly AttendanceCalculationService $attendanceCalculationService,
        private readonly AuditLogger $auditLogger,
        private readonly NotificationService $notificationService,
    ) {}

    public function handle(WorkflowInstanceTransitioned $event): void
    {
        if ($event->instance->reference_type !== 'attendance_correction') {
            return;
        }

        $correction = AttendanceCorrection::query()
            ->with([
                'attendanceRecord.employee.company',
                'requester',
                'workflowInstance.tasks.actor',
            ])
            ->find($event->instance->reference_id);

        if (! $correction) {
            return;
        }

        $decisionTask = WorkflowTask::query()
            ->with('actor')
            ->where('workflow_instance_id', $correction->workflow_instance_id)
            ->whereNotNull('acted_at')
            ->orderByDesc('acted_at')
            ->orderByDesc('id')
            ->first();

        $actor = $decisionTask?->actor;
        $comment = $decisionTask?->decision_comment;

        match ($event->transition) {
            'completed' => $this->markApproved($correction, $actor, $comment),
            'reject' => $this->markRejected($correction, $actor, $comment),
            'request_changes' => $this->markChangesRequested($correction, $actor, $comment),
            default => null,
        };
    }

    private function markApproved(AttendanceCorrection $correction, ?User $actor, ?string $comment): void
    {
        DB::transaction(function () use ($correction, $actor, $comment): void {
            $record = $correction->attendanceRecord;

            if (! $record) {
                return;
            }

            $correctedValues = $correction->corrected_values ?? [];
            $company = $record->employee->company;
            $timestamp = now($company->timezone)->toIso8601String();

            if (array_key_exists('check_in_at', $correctedValues)) {
                $record->setAttribute('check_in_at', $this->parseTimestamp($company, $correctedValues['check_in_at']));
                $record->check_in_channel = $record->check_in_channel ?? 'correction';
                $record->setAttribute('check_in_metadata', array_merge($record->check_in_metadata ?? [], [
                    'correction' => [
                        'attendance_correction_id' => $correction->id,
                        'applied_at' => $timestamp,
                    ],
                ]));
            }

            if (array_key_exists('check_out_at', $correctedValues)) {
                $record->setAttribute('check_out_at', $this->parseTimestamp($company, $correctedValues['check_out_at']));
                $record->check_out_channel = $record->check_out_channel ?? 'correction';
                $record->setAttribute('check_out_metadata', array_merge($record->check_out_metadata ?? [], [
                    'correction' => [
                        'attendance_correction_id' => $correction->id,
                        'applied_at' => $timestamp,
                    ],
                ]));
            }

            $record->updated_by_user_id = $actor?->id;
            $record->save();

            $record = $this->attendanceCalculationService->calculateRecord($record);

            $correction->forceFill([
                'status' => 'approved',
                'latest_action_by_user_id' => $actor?->id,
                'decision_comment' => $comment,
                'approved_at' => now(),
                'applied_values' => $this->buildRecordSnapshot($record),
            ])->save();

            $this->auditLogger->record(
                eventType: 'attendance.correction.approved',
                actor: $actor,
                metadata: [
                    'attendance_correction_id' => $correction->id,
                    'attendance_record_id' => $record->id,
                    'employee_id' => $record->employee_id,
                    'applied_values' => $correction->applied_values,
                ],
                entityType: 'attendance_correction',
                entityId: (string) $correction->id,
            );

            if ($correction->requester) {
                $this->notificationService->sendDirect($correction->requester, [
                    'type' => 'attendance',
                    'channel' => 'in_app',
                    'title' => 'Attendance correction approved',
                    'message' => 'Your attendance correction request has been approved and recalculated.',
                    'priority' => 'normal',
                    'deep_link' => '/attendance/corrections/'.$correction->id,
                    'data' => [
                        'attendance_correction_id' => $correction->id,
                        'attendance_record_id' => $record->id,
                        'status' => 'approved',
                    ],
                ], $actor);
            }
        });
    }

    private function markRejected(AttendanceCorrection $correction, ?User $actor, ?string $comment): void
    {
        $correction->forceFill([
            'status' => 'rejected',
            'latest_action_by_user_id' => $actor?->id,
            'decision_comment' => $comment,
            'rejected_at' => now(),
        ])->save();

        $this->auditLogger->record(
            eventType: 'attendance.correction.rejected',
            actor: $actor,
            metadata: [
                'attendance_correction_id' => $correction->id,
                'attendance_record_id' => $correction->attendance_record_id,
                'employee_id' => $correction->employee_id,
            ],
            entityType: 'attendance_correction',
            entityId: (string) $correction->id,
        );

        if ($correction->requester) {
            $this->notificationService->sendDirect($correction->requester, [
                'type' => 'attendance',
                'channel' => 'in_app',
                'title' => 'Attendance correction rejected',
                'message' => 'Your attendance correction request was rejected.',
                'priority' => 'high',
                'deep_link' => '/attendance/corrections/'.$correction->id,
                'data' => [
                    'attendance_correction_id' => $correction->id,
                    'attendance_record_id' => $correction->attendance_record_id,
                    'status' => 'rejected',
                ],
            ], $actor);
        }
    }

    private function markChangesRequested(AttendanceCorrection $correction, ?User $actor, ?string $comment): void
    {
        $correction->forceFill([
            'status' => 'changes_requested',
            'latest_action_by_user_id' => $actor?->id,
            'decision_comment' => $comment,
        ])->save();

        $this->auditLogger->record(
            eventType: 'attendance.correction.changes_requested',
            actor: $actor,
            metadata: [
                'attendance_correction_id' => $correction->id,
                'attendance_record_id' => $correction->attendance_record_id,
                'employee_id' => $correction->employee_id,
            ],
            entityType: 'attendance_correction',
            entityId: (string) $correction->id,
        );

        if ($correction->requester) {
            $this->notificationService->sendDirect($correction->requester, [
                'type' => 'attendance',
                'channel' => 'in_app',
                'title' => 'Attendance correction needs changes',
                'message' => 'Your attendance correction request needs changes before it can be approved.',
                'priority' => 'normal',
                'deep_link' => '/attendance/corrections/'.$correction->id,
                'data' => [
                    'attendance_correction_id' => $correction->id,
                    'attendance_record_id' => $correction->attendance_record_id,
                    'status' => 'changes_requested',
                ],
            ], $actor);
        }
    }

    private function parseTimestamp(Company $company, string $value): Carbon
    {
        if (preg_match('/(?:Z|[+\-]\d{2}:\d{2})$/', $value) === 1) {
            return Carbon::parse($value)->setTimezone($company->timezone);
        }

        return Carbon::parse($value, $company->timezone)->setTimezone($company->timezone);
    }

    /**
     * @return array{
     *   attendance_date: string|null,
     *   check_in_at: string|null,
     *   check_out_at: string|null,
     *   check_in_channel: string|null,
     *   check_out_channel: string|null,
     *   worked_minutes: int|null,
     *   primary_status: string|null,
     *   shift_id: int|null,
     *   shift_roster_id: int|null
     * }
     */
    private function buildRecordSnapshot(AttendanceRecord $record): array
    {
        return [
            'attendance_date' => $record->attendance_date?->toDateString(),
            'check_in_at' => $record->check_in_at?->toIso8601String(),
            'check_out_at' => $record->check_out_at?->toIso8601String(),
            'check_in_channel' => $record->check_in_channel,
            'check_out_channel' => $record->check_out_channel,
            'worked_minutes' => $record->worked_minutes,
            'primary_status' => $record->primary_status,
            'shift_id' => $record->shift_id,
            'shift_roster_id' => $record->shift_roster_id,
        ];
    }
}
