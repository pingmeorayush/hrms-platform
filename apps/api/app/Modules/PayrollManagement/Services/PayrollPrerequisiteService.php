<?php

namespace App\Modules\PayrollManagement\Services;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\EmployeeCompensation;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Schema;

/**
 * @phpstan-type PayrollPrerequisiteMetrics array<string, bool|int>
 * @phpstan-type PayrollPrerequisiteCheck array{
 *   code: string,
 *   label: string,
 *   status: 'passed'|'warning'|'failed',
 *   blocking: bool,
 *   message: string,
 *   metrics: PayrollPrerequisiteMetrics
 * }
 * @phpstan-type PayrollPrerequisiteSummary array{
 *   ready_for_calculation: bool,
 *   blocking_count: int,
 *   warning_count: int,
 *   passed_count: int
 * }
 * @phpstan-type PayrollPrerequisiteSnapshot array{
 *   checks: list<PayrollPrerequisiteCheck>,
 *   summary: PayrollPrerequisiteSummary
 * }
 */
class PayrollPrerequisiteService
{
    /**
     * @return PayrollPrerequisiteSnapshot
     */
    public function buildSnapshot(PayrollPeriod $period): array
    {
        $activeEmployeeIds = Employee::query()
            ->where('employment_status', 'active')
            ->whereDate('date_of_joining', '<=', $period->end_date)
            ->pluck('id');

        $activeEmployeeCount = $activeEmployeeIds->count();
        $attendanceRecordsCount = 0;
        $incompleteAttendanceCount = 0;
        $pendingAttendanceCorrectionsCount = 0;
        $assignedEmployeeCount = 0;

        if ($activeEmployeeCount > 0) {
            $attendanceQuery = AttendanceRecord::query()
                ->whereIn('employee_id', $activeEmployeeIds)
                ->whereBetween('attendance_date', [
                    $period->start_date->toDateString(),
                    $period->end_date->toDateString(),
                ]);

            $attendanceRecordsCount = (clone $attendanceQuery)->count();

            $incompleteAttendanceCount = (clone $attendanceQuery)
                ->where('primary_status', 'incomplete')
                ->count();

            $pendingAttendanceCorrectionsCount = AttendanceCorrection::query()
                ->whereIn('employee_id', $activeEmployeeIds)
                ->where('status', 'pending')
                ->whereHas('attendanceRecord', function ($query) use ($period): void {
                    $query->whereBetween('attendance_date', [
                        $period->start_date->toDateString(),
                        $period->end_date->toDateString(),
                    ]);
                })
                ->count();
        }

        $pendingLeaveRequestsCount = $activeEmployeeCount > 0
            ? LeaveRequest::query()
                ->whereIn('employee_id', $activeEmployeeIds)
                ->whereIn('status', ['pending', 'changes_requested'])
                ->whereDate('start_date', '<=', $period->end_date)
                ->whereDate('end_date', '>=', $period->start_date)
                ->count()
            : 0;

        $salaryStructuresReady = Schema::hasTable('salary_structures');
        $employeeCompensationsReady = Schema::hasTable('employee_compensations');

        if ($activeEmployeeCount > 0 && $employeeCompensationsReady) {
            $assignedEmployeeCount = EmployeeCompensation::query()
                ->whereIn('employee_id', $activeEmployeeIds)
                ->whereDate('effective_from', '<=', $period->end_date)
                ->pluck('employee_id')
                ->unique()
                ->count();
        }

        $checks = [
            $this->activeEmployeeRosterCheck($activeEmployeeCount),
            $this->attendanceCompletionCheck(
                $activeEmployeeCount,
                $attendanceRecordsCount,
                $incompleteAttendanceCount,
                $pendingAttendanceCorrectionsCount,
            ),
            $this->leaveApprovalCheck($pendingLeaveRequestsCount),
            $this->compensationReadinessCheck(
                $activeEmployeeCount,
                $salaryStructuresReady,
                $employeeCompensationsReady,
                $assignedEmployeeCount,
            ),
        ];

        $blockingCount = collect($checks)
            ->where('blocking', true)
            ->count();

        $warningCount = collect($checks)
            ->where('status', 'warning')
            ->count();

        $passedCount = collect($checks)
            ->where('status', 'passed')
            ->count();

        return [
            'checks' => $checks,
            'summary' => [
                'ready_for_calculation' => $blockingCount === 0,
                'blocking_count' => $blockingCount,
                'warning_count' => $warningCount,
                'passed_count' => $passedCount,
            ],
        ];
    }

    /**
     * @return PayrollPrerequisiteCheck
     */
    private function activeEmployeeRosterCheck(int $activeEmployeeCount): array
    {
        if ($activeEmployeeCount === 0) {
            return [
                'code' => 'active_employee_roster',
                'label' => 'Active employee roster',
                'status' => 'warning',
                'blocking' => false,
                'message' => 'No active employees are currently in scope for this payroll period.',
                'metrics' => [
                    'active_employee_count' => 0,
                ],
            ];
        }

        return [
            'code' => 'active_employee_roster',
            'label' => 'Active employee roster',
            'status' => 'passed',
            'blocking' => false,
            'message' => 'Active employees are available for payroll preparation.',
            'metrics' => [
                'active_employee_count' => $activeEmployeeCount,
            ],
        ];
    }

    /**
     * @return PayrollPrerequisiteCheck
     */
    private function attendanceCompletionCheck(
        int $activeEmployeeCount,
        int $attendanceRecordsCount,
        int $incompleteAttendanceCount,
        int $pendingAttendanceCorrectionsCount,
    ): array {
        if ($activeEmployeeCount === 0) {
            return [
                'code' => 'attendance_finalization',
                'label' => 'Attendance finalization',
                'status' => 'passed',
                'blocking' => false,
                'message' => 'No attendance dependency applies because there are no active employees in scope.',
                'metrics' => [
                    'attendance_records_count' => 0,
                    'incomplete_records_count' => 0,
                    'pending_corrections_count' => 0,
                ],
            ];
        }

        if ($attendanceRecordsCount === 0) {
            return [
                'code' => 'attendance_finalization',
                'label' => 'Attendance finalization',
                'status' => 'failed',
                'blocking' => true,
                'message' => 'Attendance records are missing for the payroll period.',
                'metrics' => [
                    'attendance_records_count' => 0,
                    'incomplete_records_count' => 0,
                    'pending_corrections_count' => 0,
                ],
            ];
        }

        if ($incompleteAttendanceCount > 0 || $pendingAttendanceCorrectionsCount > 0) {
            return [
                'code' => 'attendance_finalization',
                'label' => 'Attendance finalization',
                'status' => 'failed',
                'blocking' => true,
                'message' => 'Attendance must be finalized before payroll calculation can begin.',
                'metrics' => [
                    'attendance_records_count' => $attendanceRecordsCount,
                    'incomplete_records_count' => $incompleteAttendanceCount,
                    'pending_corrections_count' => $pendingAttendanceCorrectionsCount,
                ],
            ];
        }

        return [
            'code' => 'attendance_finalization',
            'label' => 'Attendance finalization',
            'status' => 'passed',
            'blocking' => false,
            'message' => 'Attendance records are ready for payroll preparation.',
            'metrics' => [
                'attendance_records_count' => $attendanceRecordsCount,
                'incomplete_records_count' => 0,
                'pending_corrections_count' => 0,
            ],
        ];
    }

    /**
     * @return PayrollPrerequisiteCheck
     */
    private function leaveApprovalCheck(int $pendingLeaveRequestsCount): array
    {
        if ($pendingLeaveRequestsCount > 0) {
            return [
                'code' => 'leave_approval_completion',
                'label' => 'Leave approval completion',
                'status' => 'failed',
                'blocking' => true,
                'message' => 'Pending or changes-requested leave records still overlap the payroll period.',
                'metrics' => [
                    'pending_leave_requests_count' => $pendingLeaveRequestsCount,
                ],
            ];
        }

        return [
            'code' => 'leave_approval_completion',
            'label' => 'Leave approval completion',
            'status' => 'passed',
            'blocking' => false,
            'message' => 'No unresolved leave approvals are blocking payroll preparation.',
            'metrics' => [
                'pending_leave_requests_count' => 0,
            ],
        ];
    }

    /**
     * @return PayrollPrerequisiteCheck
     */
    private function compensationReadinessCheck(
        int $activeEmployeeCount,
        bool $salaryStructuresReady,
        bool $employeeCompensationsReady,
        int $assignedEmployeeCount,
    ): array {
        if ($activeEmployeeCount === 0) {
            return [
                'code' => 'compensation_assignment_readiness',
                'label' => 'Compensation assignment readiness',
                'status' => 'passed',
                'blocking' => false,
                'message' => 'No compensation assignment dependency applies because there are no active employees in scope.',
                'metrics' => [
                    'salary_structures_ready' => $salaryStructuresReady,
                    'employee_compensations_ready' => $employeeCompensationsReady,
                    'assigned_active_employee_count' => 0,
                    'unassigned_active_employee_count' => 0,
                ],
            ];
        }

        if (! $salaryStructuresReady || ! $employeeCompensationsReady) {
            return [
                'code' => 'compensation_assignment_readiness',
                'label' => 'Compensation assignment readiness',
                'status' => 'failed',
                'blocking' => true,
                'message' => $salaryStructuresReady
                    ? 'Employee compensation assignment setup is not available yet for payroll processing.'
                    : 'Salary structure and compensation assignment setup are not available yet for payroll processing.',
                'metrics' => [
                    'salary_structures_ready' => $salaryStructuresReady,
                    'employee_compensations_ready' => $employeeCompensationsReady,
                    'assigned_active_employee_count' => 0,
                    'unassigned_active_employee_count' => $activeEmployeeCount,
                ],
            ];
        }

        $unassignedActiveEmployeeCount = max($activeEmployeeCount - $assignedEmployeeCount, 0);

        if ($unassignedActiveEmployeeCount > 0) {
            return [
                'code' => 'compensation_assignment_readiness',
                'label' => 'Compensation assignment readiness',
                'status' => 'failed',
                'blocking' => true,
                'message' => 'One or more active employees still do not have compensation assigned for the payroll period.',
                'metrics' => [
                    'salary_structures_ready' => true,
                    'employee_compensations_ready' => true,
                    'assigned_active_employee_count' => $assignedEmployeeCount,
                    'unassigned_active_employee_count' => $unassignedActiveEmployeeCount,
                ],
            ];
        }

        return [
            'code' => 'compensation_assignment_readiness',
            'label' => 'Compensation assignment readiness',
            'status' => 'passed',
            'blocking' => false,
            'message' => 'Compensation assignments are available for payroll processing.',
            'metrics' => [
                'salary_structures_ready' => true,
                'employee_compensations_ready' => true,
                'assigned_active_employee_count' => $assignedEmployeeCount,
                'unassigned_active_employee_count' => 0,
            ],
        ];
    }
}
