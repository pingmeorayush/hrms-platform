<?php

namespace App\Modules\LeaveManagement\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Modules\AttendanceManagement\Services\AttendanceCalculationService;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Workflow\Services\WorkflowService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type LeaveRequestFilters array{
 *   employee_id?: int|string,
 *   leave_type_id?: int|string,
 *   status?: string,
 *   date_from?: string,
 *   date_to?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type LeaveRequestSubmitPayload array{
 *   leave_type_id: int|string,
 *   start_date: string,
 *   end_date: string,
 *   reason: string
 * }
 * @phpstan-type LeaveRequestActionPayload array{
 *   action: string,
 *   comment?: string|null
 * }
 */
class LeaveRequestService
{
    public function __construct(
        private readonly LeaveRequestAccessScopeService $accessScopeService,
        private readonly LeaveBalanceService $leaveBalanceService,
        private readonly AttendanceCalculationService $attendanceCalculationService,
        private readonly AuditLogger $auditLogger,
        private readonly WorkflowService $workflowService,
    ) {}

    /**
     * @param  LeaveRequestFilters  $filters
     * @return LengthAwarePaginator<int, LeaveRequest>
     */
    public function search(User $actor, array $filters): LengthAwarePaginator
    {
        $query = $this->accessScopeService
            ->requestsQuery($actor)
            ->when(
                array_key_exists('employee_id', $filters),
                fn (Builder $builder) => $builder->where('employee_id', $filters['employee_id']),
            )
            ->when(
                array_key_exists('leave_type_id', $filters),
                fn (Builder $builder) => $builder->where('leave_type_id', $filters['leave_type_id']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', $filters['status']),
            )
            ->when(
                array_key_exists('date_from', $filters),
                fn (Builder $builder) => $builder->where('end_date', '>=', $filters['date_from']),
            )
            ->when(
                array_key_exists('date_to', $filters),
                fn (Builder $builder) => $builder->where('start_date', '<', $this->nextDate((string) $filters['date_to'])),
            )
            ->orderByDesc('start_date')
            ->orderByDesc('id');

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findForView(User $actor, int $leaveRequestId): LeaveRequest
    {
        return $this->accessScopeService->resolveAccessibleRequest($actor, $leaveRequestId);
    }

    /**
     * @param  LeaveRequestSubmitPayload  $payload
     */
    public function submit(User $actor, array $payload): LeaveRequest
    {
        return DB::transaction(function () use ($actor, $payload): LeaveRequest {
            $employee = $this->accessScopeService->resolveLinkedEmployee($actor);
            $leaveType = LeaveType::query()->findOrFail((int) $payload['leave_type_id']);
            $policy = $this->resolvePolicy($employee, $leaveType);
            $balance = LeaveBalance::query()
                ->where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->first();

            if (! $balance) {
                throw ValidationException::withMessages([
                    'leave_type_id' => ['No leave balance is available for the selected leave type.'],
                ]);
            }

            $this->ensureEmployeeCanRequestLeave($employee);
            $this->ensureDatesArePolicyCompliant($actor, $policy, $payload);

            $startDate = Carbon::parse((string) $payload['start_date'], $actor->company->timezone)->startOfDay();
            $endDate = Carbon::parse((string) $payload['end_date'], $actor->company->timezone)->startOfDay();
            $totalDays = (float) $startDate->diffInDays($endDate) + 1;

            if ($balance->available_days < $totalDays) {
                throw ValidationException::withMessages([
                    'start_date' => ['Requested leave exceeds the available balance.'],
                ]);
            }

            $this->ensureNoOverlap($employee, $startDate->toDateString(), $endDate->toDateString());

            $status = $leaveType->requires_approval ? 'pending' : 'approved';

            $leaveRequest = LeaveRequest::query()->create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'leave_policy_id' => $policy->id,
                'policy_version' => $policy->version,
                'workflow_instance_id' => null,
                'requested_by_user_id' => $actor->id,
                'department_id' => $employee->department_id,
                'location_id' => $employee->location_id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_days' => $totalDays,
                'status' => $status,
                'reason' => trim((string) $payload['reason']),
                'approver_comment' => $status === 'approved'
                    ? 'Automatically approved because the leave type does not require workflow approval.'
                    : null,
                'is_auto_approved' => ! $leaveType->requires_approval,
                'attendance_sync_status' => $status === 'approved' ? 'pending' : 'not_applicable',
                'attendance_synced_at' => null,
                'approved_at' => $status === 'approved' ? now() : null,
                'rejected_at' => null,
                'cancelled_at' => null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->leaveBalanceService->reserveForLeaveRequest($actor, $balance, $leaveRequest);

            if ($status === 'pending') {
                $workflowInstance = $this->workflowService->startInstance($actor, [
                    'workflow_key' => 'leave-approval',
                    'reference_type' => 'leave_request',
                    'reference_id' => (string) $leaveRequest->id,
                    'payload' => [
                        'employee_id' => $employee->id,
                        'leave_request_id' => $leaveRequest->id,
                        'leave_type_id' => $leaveType->id,
                    ],
                ]);

                $leaveRequest->forceFill([
                    'workflow_instance_id' => $workflowInstance->id,
                ])->save();
            }

            if ($status === 'approved') {
                $this->syncAttendanceForApprovedRequest($actor, $leaveRequest);

                $leaveRequest->forceFill([
                    'attendance_sync_status' => 'synced',
                    'attendance_synced_at' => now(),
                ])->save();
            }

            $this->auditLogger->record(
                eventType: 'leave.request.submitted',
                actor: $actor,
                metadata: [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'leave_policy_id' => $policy->id,
                    'start_date' => $leaveRequest->start_date?->toDateString(),
                    'end_date' => $leaveRequest->end_date?->toDateString(),
                    'total_days' => $leaveRequest->total_days,
                    'status' => $leaveRequest->status,
                    'attendance_sync_status' => $leaveRequest->attendance_sync_status,
                ],
                entityType: 'leave_request',
                entityId: (string) $leaveRequest->id,
            );

            return $leaveRequest->load([
                'employee',
                'department',
                'location',
                'leaveType',
                'workflowInstance.definition',
                'workflowInstance.tasks.assignee',
                'workflowInstance.tasks.actor',
            ]);
        });
    }

    /**
     * @param  LeaveRequestActionPayload  $payload
     */
    public function update(User $actor, int $leaveRequestId, array $payload): LeaveRequest
    {
        $leaveRequest = $this->accessScopeService->resolveAccessibleRequest($actor, $leaveRequestId);
        $action = $payload['action'];

        if ($action === 'cancel') {
            return $this->cancel($actor, $leaveRequest, $payload);
        }

        if (! in_array($action, ['approve', 'reject', 'request_changes'], true)) {
            throw ValidationException::withMessages([
                'action' => ['The requested leave action is not supported.'],
            ]);
        }

        if (! $leaveRequest->workflow_instance_id) {
            throw ValidationException::withMessages([
                'action' => ['This leave request is not linked to an approval workflow.'],
            ]);
        }

        $task = $leaveRequest->workflowInstance?->tasks()
            ->where('status', 'open')
            ->orderBy('sequence')
            ->first();

        if (! $task) {
            throw ValidationException::withMessages([
                'action' => ['No open workflow task is available for this leave request.'],
            ]);
        }

        $this->workflowService->decideTask($task, $actor, [
            'action' => $action,
            'comment' => $payload['comment'] ?? null,
        ]);

        return $this->accessScopeService
            ->resolveAccessibleRequest($actor, $leaveRequestId)
            ->load([
                'employee',
                'department',
                'location',
                'leaveType',
                'workflowInstance.definition',
                'workflowInstance.tasks.assignee',
                'workflowInstance.tasks.actor',
            ]);
    }

    /**
     * @param  LeaveRequestActionPayload  $payload
     */
    private function cancel(User $actor, LeaveRequest $leaveRequest, array $payload): LeaveRequest
    {
        return DB::transaction(function () use ($actor, $leaveRequest, $payload): LeaveRequest {

            $linkedEmployee = $this->accessScopeService->findLinkedEmployee($actor);
            $canCancelOwnRequest = $linkedEmployee?->id === $leaveRequest->employee_id;

            if (! $canCancelOwnRequest && ! $actor->can('employee.manage')) {
                throw ValidationException::withMessages([
                    'leave' => ['You are not allowed to cancel this leave request.'],
                ]);
            }

            if (! in_array($leaveRequest->status, ['pending', 'approved'], true)) {
                throw ValidationException::withMessages([
                    'leave' => ['Only pending or approved leave requests can be cancelled directly.'],
                ]);
            }

            $balance = LeaveBalance::query()
                ->where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->firstOrFail();

            $comment = trim((string) ($payload['comment'] ?? ''));
            $requiresAttendanceReversal = $leaveRequest->status === 'approved';

            $leaveRequest->forceFill([
                'status' => 'cancelled',
                'approver_comment' => $comment !== '' ? $comment : 'Cancelled by the employee before approval.',
                'attendance_sync_status' => $requiresAttendanceReversal ? 'reversed' : 'not_applicable',
                'attendance_synced_at' => $requiresAttendanceReversal ? now() : $leaveRequest->attendance_synced_at,
                'cancelled_at' => now(),
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->leaveBalanceService->releaseLeaveRequestReservation($actor, $balance, $leaveRequest, [
                'comment' => $leaveRequest->approver_comment,
            ]);

            if ($leaveRequest->workflow_instance_id !== null && $leaveRequest->status === 'cancelled' && ! $requiresAttendanceReversal) {
                $leaveRequest->loadMissing('workflowInstance');

                if ($leaveRequest->workflowInstance && $leaveRequest->workflowInstance->status === 'running') {
                    $this->workflowService->cancelInstance(
                        $leaveRequest->workflowInstance,
                        $actor,
                        $leaveRequest->approver_comment,
                    );
                }
            }

            if ($requiresAttendanceReversal) {
                $this->syncAttendanceForCancelledApprovedRequest($actor, $leaveRequest);
            }

            $this->auditLogger->record(
                eventType: 'leave.request.cancelled',
                actor: $actor,
                metadata: [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id' => $leaveRequest->employee_id,
                    'leave_type_id' => $leaveRequest->leave_type_id,
                    'start_date' => $leaveRequest->start_date?->toDateString(),
                    'end_date' => $leaveRequest->end_date?->toDateString(),
                ],
                entityType: 'leave_request',
                entityId: (string) $leaveRequest->id,
            );

            return $leaveRequest->refresh()->load([
                'employee',
                'department',
                'location',
                'leaveType',
                'workflowInstance.definition',
                'workflowInstance.tasks.assignee',
                'workflowInstance.tasks.actor',
            ]);
        });
    }

    private function ensureEmployeeCanRequestLeave(Employee $employee): void
    {
        if ($employee->terminated_at !== null || $employee->employment_status === 'terminated') {
            throw ValidationException::withMessages([
                'employee' => ['Terminated employees cannot submit leave requests.'],
            ]);
        }
    }

    /**
     * @param  LeaveRequestSubmitPayload  $payload
     */
    private function ensureDatesArePolicyCompliant(User $actor, LeavePolicy $policy, array $payload): void
    {
        $startDate = Carbon::parse((string) $payload['start_date'], $actor->company->timezone)->startOfDay();
        $endDate = Carbon::parse((string) $payload['end_date'], $actor->company->timezone)->startOfDay();
        $totalDays = (float) $startDate->diffInDays($endDate) + 1;
        $today = now($actor->company->timezone)->startOfDay();

        if ($totalDays > (float) $policy->max_consecutive_days) {
            throw ValidationException::withMessages([
                'end_date' => ['Requested leave exceeds the maximum consecutive days allowed by the policy.'],
            ]);
        }

        if ($policy->min_notice_days > 0 && $today->diffInDays($startDate, false) < $policy->min_notice_days) {
            throw ValidationException::withMessages([
                'start_date' => ['The selected leave request does not meet the policy notice period requirement.'],
            ]);
        }
    }

    private function ensureNoOverlap(Employee $employee, string $startDate, string $endDate): void
    {
        $overlapExists = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->whereNotIn('status', ['rejected', 'cancelled', 'changes_requested'])
            ->where('start_date', '<', $this->nextDate($endDate))
            ->where('end_date', '>=', $startDate)
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'start_date' => ['Leave dates overlap with an existing request.'],
            ]);
        }
    }

    private function resolvePolicy(Employee $employee, LeaveType $leaveType): LeavePolicy
    {
        $policies = LeavePolicy::query()
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'active')
            ->orderByDesc('applicable_department_id')
            ->orderByDesc('applicable_location_id')
            ->orderByDesc('version')
            ->get();

        $policy = $policies->first(function (LeavePolicy $policy) use ($employee): bool {
            if ($policy->applicable_department_id !== null && $employee->department_id !== $policy->applicable_department_id) {
                return false;
            }

            if ($policy->applicable_location_id !== null && $employee->location_id !== $policy->applicable_location_id) {
                return false;
            }

            $rule = $policy->eligibility_rule ?? [];

            if (($rule['employment_types'] ?? []) !== [] && ! in_array($employee->employment_type, $rule['employment_types'], true)) {
                return false;
            }

            if (($rule['employment_statuses'] ?? []) !== [] && ! in_array($employee->employment_status, $rule['employment_statuses'], true)) {
                return false;
            }

            return true;
        });

        if (! $policy) {
            throw ValidationException::withMessages([
                'leave_type_id' => ['No active leave policy could be resolved for the selected leave type.'],
            ]);
        }

        return $policy;
    }

    private function syncAttendanceForApprovedRequest(User $actor, LeaveRequest $leaveRequest): void
    {
        $period = CarbonPeriod::create(
            $leaveRequest->start_date?->toDateString(),
            $leaveRequest->end_date?->toDateString(),
        );

        foreach ($period as $workDate) {
            $attendanceDate = Carbon::parse($workDate->toDateString(), $actor->company->timezone)->startOfDay();

            $record = AttendanceRecord::query()
                ->where('employee_id', $leaveRequest->employee_id)
                ->where('attendance_date', '>=', $attendanceDate->toDateString())
                ->where('attendance_date', '<', $this->nextDate($attendanceDate->toDateString()))
                ->first();

            if (! $record) {
                $record = AttendanceRecord::query()->create([
                    'employee_id' => $leaveRequest->employee_id,
                    'attendance_date' => $attendanceDate->toDateString(),
                    'created_by_user_id' => $actor->id,
                    'updated_by_user_id' => $actor->id,
                ]);
            }

            $record->updated_by_user_id = $actor->id;
            $record->save();

            $this->attendanceCalculationService->calculateRecord($record);
        }
    }

    private function syncAttendanceForCancelledApprovedRequest(User $actor, LeaveRequest $leaveRequest): void
    {
        $period = CarbonPeriod::create(
            $leaveRequest->start_date?->toDateString(),
            $leaveRequest->end_date?->toDateString(),
        );

        foreach ($period as $workDate) {
            $attendanceDate = Carbon::parse($workDate->toDateString(), $actor->company->timezone)->startOfDay();

            $record = AttendanceRecord::query()
                ->where('employee_id', $leaveRequest->employee_id)
                ->where('attendance_date', '>=', $attendanceDate->toDateString())
                ->where('attendance_date', '<', $this->nextDate($attendanceDate->toDateString()))
                ->first();

            if (! $record) {
                continue;
            }

            $record->updated_by_user_id = $actor->id;
            $record->save();

            $this->attendanceCalculationService->calculateRecord($record);
        }
    }

    private function nextDate(string $date): string
    {
        return Carbon::parse($date)->addDay()->toDateString();
    }
}
