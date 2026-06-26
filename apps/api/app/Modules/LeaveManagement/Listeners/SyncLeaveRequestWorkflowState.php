<?php

namespace App\Modules\LeaveManagement\Listeners;

use App\Models\AttendanceRecord;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\WorkflowTask;
use App\Modules\AttendanceManagement\Services\AttendanceCalculationService;
use App\Modules\LeaveManagement\Services\LeaveBalanceService;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Workflow\Events\WorkflowInstanceTransitioned;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class SyncLeaveRequestWorkflowState
{
    public function __construct(
        private readonly AttendanceCalculationService $attendanceCalculationService,
        private readonly LeaveBalanceService $leaveBalanceService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function handle(WorkflowInstanceTransitioned $event): void
    {
        if ($event->instance->reference_type !== 'leave_request') {
            return;
        }

        $leaveRequest = LeaveRequest::query()
            ->with([
                'employee.company',
                'requestedBy',
                'workflowInstance.tasks.actor',
            ])
            ->find($event->instance->reference_id);

        if (! $leaveRequest) {
            return;
        }

        $decisionTask = WorkflowTask::query()
            ->with('actor')
            ->where('workflow_instance_id', $leaveRequest->workflow_instance_id)
            ->whereNotNull('acted_at')
            ->orderByDesc('acted_at')
            ->orderByDesc('id')
            ->first();

        $actor = $decisionTask?->actor;
        $comment = $decisionTask?->decision_comment;

        match ($event->transition) {
            'completed' => $this->markApproved($leaveRequest, $actor, $comment),
            'reject' => $this->markRejected($leaveRequest, $actor, $comment),
            'request_changes' => $this->markChangesRequested($leaveRequest, $actor, $comment),
            default => null,
        };
    }

    private function markApproved(LeaveRequest $leaveRequest, ?User $actor, ?string $comment): void
    {
        DB::transaction(function () use ($leaveRequest, $actor, $comment): void {
            $leaveRequest->forceFill([
                'status' => 'approved',
                'approver_comment' => $comment ?: 'Approved through the leave workflow.',
                'attendance_sync_status' => 'pending',
                'attendance_synced_at' => null,
                'approved_at' => now(),
                'rejected_at' => null,
                'updated_by_user_id' => $actor?->id,
            ])->save();

            $this->syncAttendanceForApprovedRequest($leaveRequest, $actor);

            $leaveRequest->forceFill([
                'attendance_sync_status' => 'synced',
                'attendance_synced_at' => now(),
            ])->save();

            $this->auditLogger->record(
                eventType: 'leave.request.approved',
                actor: $actor,
                metadata: [
                    'leave_request_id' => $leaveRequest->id,
                    'workflow_instance_id' => $leaveRequest->workflow_instance_id,
                    'employee_id' => $leaveRequest->employee_id,
                    'leave_type_id' => $leaveRequest->leave_type_id,
                ],
                entityType: 'leave_request',
                entityId: (string) $leaveRequest->id,
            );
        });
    }

    private function markRejected(LeaveRequest $leaveRequest, ?User $actor, ?string $comment): void
    {
        DB::transaction(function () use ($leaveRequest, $actor, $comment): void {
            $leaveRequest->forceFill([
                'status' => 'rejected',
                'approver_comment' => $comment ?: 'Rejected through the leave workflow.',
                'attendance_sync_status' => 'not_applicable',
                'rejected_at' => now(),
                'updated_by_user_id' => $actor?->id,
            ])->save();

            $this->releaseReservation($leaveRequest, $actor, $comment);

            $this->auditLogger->record(
                eventType: 'leave.request.rejected',
                actor: $actor,
                metadata: [
                    'leave_request_id' => $leaveRequest->id,
                    'workflow_instance_id' => $leaveRequest->workflow_instance_id,
                    'employee_id' => $leaveRequest->employee_id,
                    'leave_type_id' => $leaveRequest->leave_type_id,
                ],
                entityType: 'leave_request',
                entityId: (string) $leaveRequest->id,
            );
        });
    }

    private function markChangesRequested(LeaveRequest $leaveRequest, ?User $actor, ?string $comment): void
    {
        DB::transaction(function () use ($leaveRequest, $actor, $comment): void {
            $leaveRequest->forceFill([
                'status' => 'changes_requested',
                'approver_comment' => $comment ?: 'Changes were requested before approval.',
                'attendance_sync_status' => 'not_applicable',
                'updated_by_user_id' => $actor?->id,
            ])->save();

            $this->releaseReservation($leaveRequest, $actor, $comment);

            $this->auditLogger->record(
                eventType: 'leave.request.changes_requested',
                actor: $actor,
                metadata: [
                    'leave_request_id' => $leaveRequest->id,
                    'workflow_instance_id' => $leaveRequest->workflow_instance_id,
                    'employee_id' => $leaveRequest->employee_id,
                    'leave_type_id' => $leaveRequest->leave_type_id,
                ],
                entityType: 'leave_request',
                entityId: (string) $leaveRequest->id,
            );
        });
    }

    private function releaseReservation(LeaveRequest $leaveRequest, ?User $actor, ?string $comment): void
    {
        $balance = LeaveBalance::query()
            ->where('employee_id', $leaveRequest->employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->first();

        if (! $balance) {
            return;
        }

        $reservationActor = $actor ?? $leaveRequest->requestedBy;

        if (! $reservationActor) {
            return;
        }

        $this->leaveBalanceService->releaseLeaveRequestReservation(
            $reservationActor,
            $balance,
            $leaveRequest,
            ['comment' => $comment],
        );
    }

    private function syncAttendanceForApprovedRequest(LeaveRequest $leaveRequest, ?User $actor): void
    {
        $companyTimezone = $leaveRequest->employee->company->timezone;
        $period = CarbonPeriod::create(
            $leaveRequest->start_date?->toDateString(),
            $leaveRequest->end_date?->toDateString(),
        );

        foreach ($period as $workDate) {
            $attendanceDate = Carbon::parse($workDate->toDateString(), $companyTimezone)->startOfDay();

            $record = AttendanceRecord::query()
                ->where('employee_id', $leaveRequest->employee_id)
                ->where('attendance_date', '>=', $attendanceDate->toDateString())
                ->where('attendance_date', '<', $attendanceDate->copy()->addDay()->toDateString())
                ->first();

            if (! $record) {
                $record = AttendanceRecord::query()->create([
                    'employee_id' => $leaveRequest->employee_id,
                    'attendance_date' => $attendanceDate->toDateString(),
                    'created_by_user_id' => $actor->id ?? $leaveRequest->requested_by_user_id,
                    'updated_by_user_id' => $actor->id ?? $leaveRequest->requested_by_user_id,
                ]);
            }

            $record->updated_by_user_id = $actor->id ?? $leaveRequest->requested_by_user_id;
            $record->save();

            $this->attendanceCalculationService->calculateRecord($record);
        }
    }
}
