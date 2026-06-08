<?php

namespace App\Modules\AttendanceManagement\Services;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowTask;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Workflow\Services\WorkflowService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceCorrectionService
{
    private const WORKFLOW_KEY = 'attendance-correction-approval';

    public function __construct(
        private readonly AttendanceAccessScopeService $attendanceAccessScopeService,
        private readonly AttendanceCalculationService $attendanceCalculationService,
        private readonly AuditLogger $auditLogger,
        private readonly WorkflowService $workflowService,
    ) {}

    public function search(User $actor, array $filters): LengthAwarePaginator
    {
        $query = $this->attendanceAccessScopeService
            ->attendanceCorrectionsQuery($actor)
            ->when(
                array_key_exists('employee_id', $filters),
                fn (Builder $builder) => $builder->where('employee_id', $filters['employee_id']),
            )
            ->when(
                array_key_exists('attendance_record_id', $filters),
                fn (Builder $builder) => $builder->where('attendance_record_id', $filters['attendance_record_id']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', $filters['status']),
            )
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function create(User $actor, array $payload): AttendanceCorrection
    {
        return DB::transaction(function () use ($actor, $payload): AttendanceCorrection {
            $record = $this->resolveRecordForCorrection($actor, (int) $payload['attendance_record_id']);
            $normalizedCorrectedValues = $this->normalizeCorrectedValues($record, $payload['corrected'] ?? []);

            $existingPendingCorrection = AttendanceCorrection::query()
                ->where('attendance_record_id', $record->id)
                ->where('status', 'pending')
                ->exists();

            if ($existingPendingCorrection) {
                throw ValidationException::withMessages([
                    'attendance_record_id' => ['A pending correction already exists for this attendance record.'],
                ]);
            }

            $correction = AttendanceCorrection::query()->create([
                'attendance_record_id' => $record->id,
                'employee_id' => $record->employee_id,
                'requested_by_user_id' => $actor->id,
                'status' => 'pending',
                'reason' => $payload['reason'],
                'original_values' => $this->buildRecordSnapshot($record),
                'corrected_values' => $normalizedCorrectedValues,
            ]);

            $workflowDefinition = $this->ensureWorkflowDefinition($actor);
            $instance = $this->workflowService->startInstance($actor, [
                'workflow_definition_id' => $workflowDefinition->id,
                'reference_type' => 'attendance_correction',
                'reference_id' => (string) $correction->id,
                'payload' => [
                    'employee_id' => $record->employee_id,
                    'attendance_record_id' => $record->id,
                    'attendance_date' => $record->attendance_date?->toDateString(),
                    'requested_by_user_id' => $actor->id,
                ],
            ]);

            $correction->forceFill([
                'workflow_instance_id' => $instance->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'attendance.correction.submitted',
                actor: $actor,
                metadata: [
                    'attendance_correction_id' => $correction->id,
                    'attendance_record_id' => $record->id,
                    'employee_id' => $record->employee_id,
                    'workflow_instance_id' => $instance->id,
                    'corrected_values' => $normalizedCorrectedValues,
                ],
                entityType: 'attendance_correction',
                entityId: (string) $correction->id,
            );

            return $this->loadCorrection($correction->id);
        });
    }

    public function decide(AttendanceCorrection $correction, User $actor, array $payload): AttendanceCorrection
    {
        if (! in_array($correction->status, ['pending'], true)) {
            throw ValidationException::withMessages([
                'correction' => ['This attendance correction can no longer be updated.'],
            ]);
        }

        $workflowTask = WorkflowTask::query()
            ->where('workflow_instance_id', $correction->workflow_instance_id)
            ->where('status', 'open')
            ->when(
                ! $actor->can('workflow.admin'),
                fn (Builder $builder) => $builder->where('assigned_to_user_id', $actor->id),
            )
            ->orderBy('sequence')
            ->first();

        if (! $workflowTask) {
            throw ValidationException::withMessages([
                'correction' => ['No open approval task is available for the authenticated user.'],
            ]);
        }

        $this->workflowService->decideTask($workflowTask, $actor, $payload);

        return $this->loadCorrection($correction->id);
    }

    private function resolveRecordForCorrection(User $actor, int $attendanceRecordId): AttendanceRecord
    {
        $record = $this->attendanceAccessScopeService
            ->attendanceRecordsQuery(
                $actor,
                ['employee.manager.user', 'employee.company', 'shift'],
            )
            ->whereKey($attendanceRecordId)
            ->firstOrFail();

        if (! $record->employee) {
            throw ValidationException::withMessages([
                'attendance_record_id' => ['The attendance record is not linked to a valid employee.'],
            ]);
        }

        return $record;
    }

    private function normalizeCorrectedValues(AttendanceRecord $record, array $corrected): array
    {
        $company = $record->employee->company;
        $hasCheckIn = array_key_exists('check_in_at', $corrected) && filled($corrected['check_in_at']);
        $hasCheckOut = array_key_exists('check_out_at', $corrected) && filled($corrected['check_out_at']);

        if (! $hasCheckIn && ! $hasCheckOut) {
            throw ValidationException::withMessages([
                'corrected' => ['At least one corrected timestamp must be supplied.'],
            ]);
        }

        $normalized = [];
        $effectiveCheckIn = $record->check_in_at?->copy();
        $effectiveCheckOut = $record->check_out_at?->copy();

        if ($hasCheckIn) {
            $effectiveCheckIn = $this->parseTimestamp($company, (string) $corrected['check_in_at']);
            $normalized['check_in_at'] = $effectiveCheckIn->toIso8601String();
        }

        if ($hasCheckOut) {
            $effectiveCheckOut = $this->parseTimestamp($company, (string) $corrected['check_out_at']);
            $normalized['check_out_at'] = $effectiveCheckOut->toIso8601String();
        }

        if ($effectiveCheckIn !== null && $effectiveCheckOut !== null && $effectiveCheckOut->lte($effectiveCheckIn)) {
            throw ValidationException::withMessages([
                'corrected.check_out_at' => ['Corrected check-out time must be after the corrected check-in time.'],
            ]);
        }

        $isCheckInChanged = array_key_exists('check_in_at', $normalized)
            && $normalized['check_in_at'] !== $record->check_in_at?->toIso8601String();
        $isCheckOutChanged = array_key_exists('check_out_at', $normalized)
            && $normalized['check_out_at'] !== $record->check_out_at?->toIso8601String();

        if (! $isCheckInChanged && ! $isCheckOutChanged) {
            throw ValidationException::withMessages([
                'corrected' => ['The corrected timestamps must differ from the current attendance values.'],
            ]);
        }

        return $normalized;
    }

    private function ensureWorkflowDefinition(User $actor): WorkflowDefinition
    {
        $definition = WorkflowDefinition::query()
            ->with(['activeVersion.stages'])
            ->where('key', self::WORKFLOW_KEY)
            ->first();

        if ($definition?->activeVersion && $definition->status === 'published') {
            return $definition;
        }

        return DB::transaction(function () use ($actor, $definition): WorkflowDefinition {
            $definition ??= WorkflowDefinition::query()->create([
                'key' => self::WORKFLOW_KEY,
                'name' => 'Attendance Correction Approval Workflow',
                'module' => 'attendance',
                'description' => 'Sequential attendance correction approval through the employee manager and HR.',
                'is_template' => true,
                'status' => 'draft',
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $stages = [
                [
                    'key' => 'manager_review',
                    'name' => 'Manager Review',
                    'sequence' => 1,
                    'approver_type' => 'employee_manager',
                    'approver_value' => 'employee_manager',
                    'available_actions' => ['approve', 'reject', 'request_changes'],
                    'sla_hours' => 24,
                ],
                [
                    'key' => 'hr_review',
                    'name' => 'HR Review',
                    'sequence' => 2,
                    'approver_type' => 'role',
                    'approver_value' => 'hr.admin',
                    'available_actions' => ['approve', 'reject'],
                    'sla_hours' => 24,
                ],
            ];

            $version = $definition->versions()->create([
                'version' => ((int) $definition->versions()->max('version')) + 1,
                'status' => 'published',
                'definition' => [
                    'module' => 'attendance',
                    'stages' => $stages,
                ],
                'created_by' => $actor->id,
                'published_at' => now(),
            ]);

            foreach ($stages as $stage) {
                $version->stages()->create($stage);
            }

            $definition->forceFill([
                'name' => 'Attendance Correction Approval Workflow',
                'module' => 'attendance',
                'description' => 'Sequential attendance correction approval through the employee manager and HR.',
                'status' => 'published',
                'active_version_id' => $version->id,
                'updated_by' => $actor->id,
            ])->save();

            return $definition->refresh()->load('activeVersion.stages');
        });
    }

    private function parseTimestamp(Company $company, string $value): Carbon
    {
        if (preg_match('/(?:Z|[+\-]\d{2}:\d{2})$/', $value) === 1) {
            return Carbon::parse($value)->setTimezone($company->timezone);
        }

        return Carbon::parse($value, $company->timezone)->setTimezone($company->timezone);
    }

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

    private function loadCorrection(int $attendanceCorrectionId): AttendanceCorrection
    {
        return AttendanceCorrection::query()
            ->with([
                'employee',
                'requester',
                'latestActor',
                'workflowInstance.tasks.assignee',
                'workflowInstance.tasks.actor',
            ])
            ->findOrFail($attendanceCorrectionId);
    }
}
