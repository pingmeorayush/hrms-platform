<?php

use App\Modules\AssetManagement\Controllers\AssetCategoryController;
use App\Modules\AssetManagement\Controllers\AssetController;
use App\Modules\AttendanceManagement\Controllers\AttendanceCorrectionController;
use App\Modules\AttendanceManagement\Controllers\AttendanceOperationalReviewController;
use App\Modules\AttendanceManagement\Controllers\AttendancePolicyController;
use App\Modules\AttendanceManagement\Controllers\AttendanceRecordController;
use App\Modules\AttendanceManagement\Controllers\HolidayCalendarController;
use App\Modules\AttendanceManagement\Controllers\HolidayController;
use App\Modules\AttendanceManagement\Controllers\ShiftAssignmentController;
use App\Modules\AttendanceManagement\Controllers\ShiftController;
use App\Modules\AttendanceManagement\Controllers\ShiftRosterController;
use App\Modules\DocumentManagement\Controllers\DocumentCategoryController;
use App\Modules\DocumentManagement\Controllers\DocumentController;
use App\Modules\EmployeeManagement\Controllers\EmployeeAddressController;
use App\Modules\EmployeeManagement\Controllers\EmployeeBankAccountController;
use App\Modules\EmployeeManagement\Controllers\EmployeeBulkImportController;
use App\Modules\EmployeeManagement\Controllers\EmployeeContactController;
use App\Modules\EmployeeManagement\Controllers\EmployeeController;
use App\Modules\EmployeeManagement\Controllers\EmployeeDocumentController;
use App\Modules\EmployeeManagement\Controllers\EmployeeEmergencyContactController;
use App\Modules\EmployeeManagement\Controllers\EmployeeLifecycleTaskTemplateController;
use App\Modules\EmployeeManagement\Controllers\EmployeeOnboardingTaskController;
use App\Modules\EmployeeManagement\Controllers\EmployeeSelfServiceController;
use App\Modules\EmployeeManagement\Controllers\EmployeeTaskCenterController;
use App\Modules\EmployeeManagement\Controllers\PolicyAcknowledgementController;
use App\Modules\LeaveManagement\Controllers\LeaveAccrualController;
use App\Modules\LeaveManagement\Controllers\LeaveBalanceController;
use App\Modules\LeaveManagement\Controllers\LeavePolicyController;
use App\Modules\LeaveManagement\Controllers\LeaveRequestController;
use App\Modules\LeaveManagement\Controllers\LeaveTypeController;
use App\Modules\OrganizationManagement\Controllers\CompanyProfileController;
use App\Modules\OrganizationManagement\Controllers\CostCenterController;
use App\Modules\OrganizationManagement\Controllers\DepartmentController;
use App\Modules\OrganizationManagement\Controllers\DesignationController;
use App\Modules\OrganizationManagement\Controllers\LocationController;
use App\Modules\PayrollManagement\Controllers\EmployeeCompensationController;
use App\Modules\PayrollManagement\Controllers\PayrollAdjustmentController;
use App\Modules\PayrollManagement\Controllers\PayrollCalendarController;
use App\Modules\PayrollManagement\Controllers\PayrollInputController;
use App\Modules\PayrollManagement\Controllers\PayrollPeriodController;
use App\Modules\PayrollManagement\Controllers\PayrollRunController;
use App\Modules\PayrollManagement\Controllers\PayslipController;
use App\Modules\PayrollManagement\Controllers\SalaryComponentController;
use App\Modules\PayrollManagement\Controllers\SalaryStructureController;
use App\Modules\Platform\Admin\Controllers\PermissionController;
use App\Modules\Platform\Admin\Controllers\RoleController;
use App\Modules\Platform\Audit\Controllers\AuditLogController;
use App\Modules\Platform\Audit\Controllers\EmployeeAuditHistoryController;
use App\Modules\Platform\Audit\Controllers\OrganizationAuditHistoryController;
use App\Modules\Platform\Auth\Controllers\AuthController;
use App\Modules\Platform\Notifications\Controllers\NotificationController;
use App\Modules\Platform\UI\Controllers\UiVisibilityController;
use App\Modules\Platform\Workflow\Controllers\WorkflowDefinitionController;
use App\Modules\Platform\Workflow\Controllers\WorkflowInstanceController;
use App\Modules\Platform\Workflow\Controllers\WorkflowTaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('throttle:api-general')
    ->group(function (): void {
        Route::prefix('auth')->group(function (): void {
            Route::post('login', [AuthController::class, 'login'])->middleware('throttle:auth-login');
            Route::post('verify-mfa', [AuthController::class, 'verifyMfa'])->middleware('throttle:auth-sensitive');
            Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:auth-sensitive');
            Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:auth-sensitive');
        });

        Route::middleware(['auth:sanctum', 'tenant'])->group(function (): void {
            Route::prefix('auth')->group(function (): void {
                Route::post('logout', [AuthController::class, 'logout']);
                Route::get('me', [AuthController::class, 'me']);
            });

            Route::get('ui/visibility', [UiVisibilityController::class, 'show']);
            Route::get('task-center', [EmployeeTaskCenterController::class, 'index']);
            Route::patch('task-center/lifecycle-tasks/{taskId}', [EmployeeTaskCenterController::class, 'updateLifecycleTask']);
            Route::get('policy-acknowledgements', [PolicyAcknowledgementController::class, 'index']);
            Route::post('policy-acknowledgements', [PolicyAcknowledgementController::class, 'store'])->middleware('permission:employee.manage');
            Route::patch('policy-acknowledgements/{policyAcknowledgementId}/acknowledge', [PolicyAcknowledgementController::class, 'acknowledge']);
            Route::get('policy-acknowledgements/{policyAcknowledgementId}/download', [PolicyAcknowledgementController::class, 'download'])
                ->name('policy.acknowledgements.download');
            Route::prefix('self-service')->group(function (): void {
                Route::get('workspace', [EmployeeSelfServiceController::class, 'show']);
                Route::get('employee-documents/{employeeDocumentId}/download', [EmployeeSelfServiceController::class, 'downloadEmployeeDocument'])
                    ->name('self-service.employee-documents.download');
                Route::get('repository-documents/{documentId}/download', [EmployeeSelfServiceController::class, 'downloadRepositoryDocument'])
                    ->name('self-service.repository-documents.download');
            });
            Route::prefix('admin')->group(function (): void {
                Route::get('roles', [RoleController::class, 'index'])->middleware('permission:auth.manage_roles');
                Route::post('roles', [RoleController::class, 'store'])->middleware('permission:auth.manage_roles');
                Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:auth.manage_permissions');
            });

            Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit.view');
            Route::prefix('organization')->group(function (): void {
                Route::get('audit-history', [OrganizationAuditHistoryController::class, 'index'])->middleware('permission:organization.view|organization.manage');
                Route::get('company-profile', [CompanyProfileController::class, 'show'])->middleware('permission:organization.view');
                Route::patch('company-profile', [CompanyProfileController::class, 'update'])->middleware('permission:organization.manage');

                Route::get('departments', [DepartmentController::class, 'index'])->middleware('permission:organization.view');
                Route::post('departments', [DepartmentController::class, 'store'])->middleware('permission:organization.manage');
                Route::patch('departments/{departmentId}', [DepartmentController::class, 'update'])->middleware('permission:organization.manage');

                Route::get('designations', [DesignationController::class, 'index'])->middleware('permission:organization.view');
                Route::post('designations', [DesignationController::class, 'store'])->middleware('permission:organization.manage');
                Route::patch('designations/{designationId}', [DesignationController::class, 'update'])->middleware('permission:organization.manage');

                Route::get('locations', [LocationController::class, 'index'])->middleware('permission:organization.view');
                Route::post('locations', [LocationController::class, 'store'])->middleware('permission:organization.manage');
                Route::patch('locations/{locationId}', [LocationController::class, 'update'])->middleware('permission:organization.manage');

                Route::get('cost-centers', [CostCenterController::class, 'index'])->middleware('permission:organization.view');
                Route::post('cost-centers', [CostCenterController::class, 'store'])->middleware('permission:organization.manage');
                Route::patch('cost-centers/{costCenterId}', [CostCenterController::class, 'update'])->middleware('permission:organization.manage');
            });

            Route::prefix('attendance')->group(function (): void {
                Route::get('policy', [AttendancePolicyController::class, 'show'])->middleware('permission:attendance.view|attendance.edit');
                Route::patch('policy', [AttendancePolicyController::class, 'update'])->middleware('permission:attendance.edit');
                Route::get('/', [AttendanceRecordController::class, 'index'])->middleware('permission:attendance.view');
                Route::post('check-in', [AttendanceRecordController::class, 'checkIn'])->middleware('permission:attendance.create');
                Route::post('check-out', [AttendanceRecordController::class, 'checkOut'])->middleware('permission:attendance.create');
                Route::post('recalculate', [AttendanceRecordController::class, 'recalculate'])->middleware('permission:attendance.edit');
                Route::get('operational-review', [AttendanceOperationalReviewController::class, 'overview'])
                    ->middleware('permission:attendance.approve|attendance.edit|attendance.manage_shift|attendance.manage_roster');
                Route::get('pending-exceptions', [AttendanceOperationalReviewController::class, 'pendingExceptions'])
                    ->middleware('permission:attendance.approve|attendance.edit|attendance.manage_shift|attendance.manage_roster');
                Route::get('corrections', [AttendanceCorrectionController::class, 'index'])->middleware('permission:attendance.correct|attendance.approve|attendance.edit');
                Route::post('corrections', [AttendanceCorrectionController::class, 'store'])->middleware('permission:attendance.correct');
                Route::patch('corrections/{attendanceCorrectionId}', [AttendanceCorrectionController::class, 'update'])->middleware('permission:attendance.approve|attendance.edit');
                Route::get('holiday-calendars', [HolidayCalendarController::class, 'index'])->middleware('permission:attendance.view|attendance.edit');
                Route::post('holiday-calendars', [HolidayCalendarController::class, 'store'])->middleware('permission:attendance.edit');
                Route::patch('holiday-calendars/{holidayCalendarId}', [HolidayCalendarController::class, 'update'])->middleware('permission:attendance.edit');
                Route::post('holiday-calendars/{holidayCalendarId}/holidays', [HolidayController::class, 'store'])->middleware('permission:attendance.edit');
                Route::patch('holiday-calendars/{holidayCalendarId}/holidays/{holidayId}', [HolidayController::class, 'update'])->middleware('permission:attendance.edit');
                Route::get('shifts', [ShiftController::class, 'index'])->middleware('permission:attendance.view|attendance.manage_shift');
                Route::post('shifts', [ShiftController::class, 'store'])->middleware('permission:attendance.manage_shift');
                Route::patch('shifts/{shiftId}', [ShiftController::class, 'update'])->middleware('permission:attendance.manage_shift');
                Route::get('shift-assignments', [ShiftAssignmentController::class, 'index'])->middleware('permission:attendance.view|attendance.manage_shift');
                Route::post('shift-assignments', [ShiftAssignmentController::class, 'store'])->middleware('permission:attendance.manage_shift');
                Route::patch('shift-assignments/{shiftAssignmentId}', [ShiftAssignmentController::class, 'update'])->middleware('permission:attendance.manage_shift');
                Route::get('rosters', [ShiftRosterController::class, 'index'])->middleware('permission:attendance.view|attendance.manage_roster');
                Route::post('rosters', [ShiftRosterController::class, 'store'])->middleware('permission:attendance.manage_roster');
                Route::patch('rosters/{shiftRosterId}', [ShiftRosterController::class, 'update'])->middleware('permission:attendance.manage_roster');
                Route::get('{attendanceRecordId}', [AttendanceRecordController::class, 'show'])->middleware('permission:attendance.view');
            });

            Route::prefix('documents')->group(function (): void {
                Route::get('/', [DocumentController::class, 'index'])->middleware('permission:document.view|document.manage');
                Route::post('/', [DocumentController::class, 'store'])->middleware('permission:document.manage');
                Route::get('categories', [DocumentCategoryController::class, 'index'])->middleware('permission:document.view|document.manage');
                Route::post('categories', [DocumentCategoryController::class, 'store'])->middleware('permission:document.manage');
                Route::patch('categories/{documentCategoryId}', [DocumentCategoryController::class, 'update'])->middleware('permission:document.manage');
                Route::get('{documentId}', [DocumentController::class, 'show'])->middleware('permission:document.view|document.manage');
                Route::get('{documentId}/download', [DocumentController::class, 'download'])
                    ->middleware('permission:document.view|document.manage')
                    ->name('documents.download');
            });

            Route::prefix('assets')->group(function (): void {
                Route::get('/', [AssetController::class, 'index'])->middleware('permission:asset.view|asset.manage');
                Route::post('/', [AssetController::class, 'store'])->middleware('permission:asset.manage');
                Route::get('categories', [AssetCategoryController::class, 'index'])->middleware('permission:asset.view|asset.manage');
                Route::post('categories', [AssetCategoryController::class, 'store'])->middleware('permission:asset.manage');
                Route::patch('categories/{assetCategoryId}', [AssetCategoryController::class, 'update'])->middleware('permission:asset.manage');
                Route::get('{assetId}', [AssetController::class, 'show'])->middleware('permission:asset.view|asset.manage');
                Route::post('{assetId}/assign', [AssetController::class, 'assign'])->middleware('permission:asset.manage');
                Route::post('{assetId}/issue', [AssetController::class, 'issue'])->middleware('permission:asset.manage');
                Route::post('{assetId}/return', [AssetController::class, 'return'])->middleware('permission:asset.manage');
            });

            Route::prefix('leave')->group(function (): void {
                Route::get('types', [LeaveTypeController::class, 'index'])->middleware('permission:leave.view|leave.manage_policy');
                Route::post('types', [LeaveTypeController::class, 'store'])->middleware('permission:leave.manage_policy');
                Route::patch('types/{leaveTypeId}', [LeaveTypeController::class, 'update'])->middleware('permission:leave.manage_policy');
                Route::get('policies', [LeavePolicyController::class, 'index'])->middleware('permission:leave.view|leave.manage_policy');
                Route::post('policies', [LeavePolicyController::class, 'store'])->middleware('permission:leave.manage_policy');
                Route::patch('policies/{leavePolicyId}', [LeavePolicyController::class, 'update'])->middleware('permission:leave.manage_policy');
                Route::post('policies/{leavePolicyId}/accrual-preview', [LeaveAccrualController::class, 'preview'])
                    ->middleware('permission:leave.manage_accrual|leave.manage_balance|leave.manage_policy');
                Route::get('balances', [LeaveBalanceController::class, 'index'])
                    ->middleware('permission:leave.view|leave.manage_balance|leave.approve');
                Route::get('balances/{employeeId}', [LeaveBalanceController::class, 'show'])
                    ->middleware('permission:leave.view|leave.manage_balance|leave.approve');
                Route::get('requests', [LeaveRequestController::class, 'index'])
                    ->middleware('permission:leave.view|leave.request|leave.approve|leave.manage_balance|leave.manage_policy');
                Route::post('requests', [LeaveRequestController::class, 'store'])
                    ->middleware('permission:leave.request');
                Route::get('requests/{leaveRequestId}', [LeaveRequestController::class, 'show'])
                    ->middleware('permission:leave.view|leave.request|leave.approve|leave.manage_balance|leave.manage_policy');
                Route::patch('requests/{leaveRequestId}', [LeaveRequestController::class, 'update'])
                    ->middleware('permission:leave.request|leave.approve|employee.manage');
            });

            Route::prefix('payroll')->group(function (): void {
                Route::get('calendars', [PayrollCalendarController::class, 'index'])
                    ->middleware('permission:payroll.view|payroll.process');
                Route::post('calendars', [PayrollCalendarController::class, 'store'])
                    ->middleware('permission:payroll.process');
                Route::patch('calendars/{payrollCalendarId}', [PayrollCalendarController::class, 'update'])
                    ->middleware('permission:payroll.process');
                Route::get('periods', [PayrollPeriodController::class, 'index'])
                    ->middleware('permission:payroll.view|payroll.process');
                Route::post('periods', [PayrollPeriodController::class, 'store'])
                    ->middleware('permission:payroll.process');
                Route::get('periods/{payrollPeriodId}', [PayrollPeriodController::class, 'show'])
                    ->middleware('permission:payroll.view|payroll.process');
                Route::post('periods/{payrollPeriodId}/open', [PayrollPeriodController::class, 'open'])
                    ->middleware('permission:payroll.process');
                Route::post('periods/{payrollPeriodId}/prepare', [PayrollPeriodController::class, 'prepare'])
                    ->middleware('permission:payroll.process');
                Route::post('periods/{payrollPeriodId}/close', [PayrollPeriodController::class, 'close'])
                    ->middleware('permission:payroll.lock|payroll.process');
                Route::get('runs', [PayrollRunController::class, 'index'])
                    ->middleware('permission:payroll.view|payroll.process');
                Route::get('runs/{payrollRunId}', [PayrollRunController::class, 'show'])
                    ->middleware('permission:payroll.view|payroll.process');
                Route::post('runs/{payrollRunId}/calculate', [PayrollRunController::class, 'calculate'])
                    ->middleware('permission:payroll.process');
                Route::post('runs/{payrollRunId}/approve', [PayrollRunController::class, 'approve'])
                    ->middleware('permission:payroll.approve|payroll.process');
                Route::post('runs/{payrollRunId}/lock', [PayrollRunController::class, 'lock'])
                    ->middleware('permission:payroll.lock|payroll.process');
                Route::post('runs/{payrollRunId}/reopen', [PayrollRunController::class, 'reopen'])
                    ->middleware('permission:payroll.reopen');
                Route::post('runs/{payrollRunId}/generate-payslips', [PayslipController::class, 'generate'])
                    ->middleware('permission:payroll.lock|payroll.process');
                Route::get('runs/{payrollRunId}/inputs', [PayrollInputController::class, 'index'])
                    ->middleware('permission:payroll.view|payroll.process');
                Route::get('runs/{payrollRunId}/adjustments', [PayrollAdjustmentController::class, 'index'])
                    ->middleware('permission:payroll.view|payroll.process');
                Route::post('runs/{payrollRunId}/adjustments', [PayrollAdjustmentController::class, 'store'])
                    ->middleware('permission:payroll.process');
                Route::patch('runs/{payrollRunId}/adjustments/{payrollAdjustmentId}', [PayrollAdjustmentController::class, 'update'])
                    ->middleware('permission:payroll.process');
                Route::get('salary-components', [SalaryComponentController::class, 'index'])
                    ->middleware('permission:payroll.view|salary.manage');
                Route::post('salary-components', [SalaryComponentController::class, 'store'])
                    ->middleware('permission:salary.manage');
                Route::patch('salary-components/{salaryComponentId}', [SalaryComponentController::class, 'update'])
                    ->middleware('permission:salary.manage');
                Route::get('salary-structures', [SalaryStructureController::class, 'index'])
                    ->middleware('permission:payroll.view|salary.manage');
                Route::post('salary-structures', [SalaryStructureController::class, 'store'])
                    ->middleware('permission:salary.manage');
                Route::patch('salary-structures/{salaryStructureId}', [SalaryStructureController::class, 'update'])
                    ->middleware('permission:salary.manage');
                Route::get('compensations', [EmployeeCompensationController::class, 'index'])
                    ->middleware('permission:compensation.view|compensation.manage');
                Route::post('compensations', [EmployeeCompensationController::class, 'store'])
                    ->middleware('permission:compensation.manage');
                Route::get('compensations/{employeeId}', [EmployeeCompensationController::class, 'show'])
                    ->middleware('permission:compensation.view|compensation.manage');
                Route::get('payslips', [PayslipController::class, 'index'])
                    ->middleware('permission:payroll.view|compensation.view|payslip.view');
                Route::get('payslips/{payslipId}', [PayslipController::class, 'show'])
                    ->middleware('permission:payroll.view|compensation.view|payslip.view');
                Route::get('payslips/{payslipId}/download', [PayslipController::class, 'download'])
                    ->middleware('permission:payroll.view|compensation.view|payslip.view')
                    ->name('payroll.payslips.download');
            });

            Route::get('employees', [EmployeeController::class, 'index'])->middleware('permission:employee.view|employee.manage');
            Route::get('employees/onboarding-status', [EmployeeOnboardingTaskController::class, 'incompleteStatus'])->middleware('permission:employee.view|employee.manage');
            Route::get('employees/lifecycle-task-status', [EmployeeOnboardingTaskController::class, 'lifecycleStatus'])->middleware('permission:employee.view|employee.manage');
            Route::get('employee-task-templates', [EmployeeLifecycleTaskTemplateController::class, 'index'])->middleware('permission:employee.view|employee.manage');
            Route::post('employee-task-templates', [EmployeeLifecycleTaskTemplateController::class, 'store'])->middleware('permission:employee.manage');
            Route::patch('employee-task-templates/{template}', [EmployeeLifecycleTaskTemplateController::class, 'update'])->middleware('permission:employee.manage');
            Route::post('employees/bulk-import/validate', [EmployeeBulkImportController::class, 'validate'])->middleware('permission:employee.manage');
            Route::post('employees', [EmployeeController::class, 'store'])->middleware('permission:employee.manage');
            Route::patch('employees/{employeeId}', [EmployeeController::class, 'update'])->middleware('permission:employee.manage');
            Route::post('employees/{employeeId}/transfer', [EmployeeController::class, 'transfer'])->middleware('permission:employee.manage');
            Route::post('employees/{employeeId}/promote', [EmployeeController::class, 'promote'])->middleware('permission:employee.manage');
            Route::post('employees/{employeeId}/terminate', [EmployeeController::class, 'terminate'])->middleware('permission:employee.manage');
            Route::get('employees/{employeeId}/audit-history', [EmployeeAuditHistoryController::class, 'index'])->middleware('permission:employee.view|employee.manage');
            Route::get('employees/{employeeId}/contacts', [EmployeeContactController::class, 'index'])->middleware('permission:employee.view|employee.manage');
            Route::post('employees/{employeeId}/contacts', [EmployeeContactController::class, 'store'])->middleware('permission:employee.manage');
            Route::patch('employees/{employeeId}/contacts/{contactId}', [EmployeeContactController::class, 'update'])->middleware('permission:employee.manage');
            Route::get('employees/{employeeId}/addresses', [EmployeeAddressController::class, 'index'])->middleware('permission:employee.view|employee.manage');
            Route::post('employees/{employeeId}/addresses', [EmployeeAddressController::class, 'store'])->middleware('permission:employee.manage');
            Route::patch('employees/{employeeId}/addresses/{addressId}', [EmployeeAddressController::class, 'update'])->middleware('permission:employee.manage');
            Route::get('employees/{employeeId}/emergency-contacts', [EmployeeEmergencyContactController::class, 'index'])->middleware('permission:employee.view|employee.manage');
            Route::post('employees/{employeeId}/emergency-contacts', [EmployeeEmergencyContactController::class, 'store'])->middleware('permission:employee.manage');
            Route::patch('employees/{employeeId}/emergency-contacts/{emergencyContactId}', [EmployeeEmergencyContactController::class, 'update'])->middleware('permission:employee.manage');
            Route::get('employees/{employeeId}/lifecycle-tasks', [EmployeeOnboardingTaskController::class, 'lifecycleIndex'])->middleware('permission:employee.view|employee.manage');
            Route::post('employees/{employeeId}/lifecycle-tasks', [EmployeeOnboardingTaskController::class, 'lifecycleStore'])->middleware('permission:employee.manage');
            Route::patch('employees/{employeeId}/lifecycle-tasks/{taskId}', [EmployeeOnboardingTaskController::class, 'lifecycleUpdate'])->middleware('permission:employee.manage');
            Route::post('employees/{employeeId}/lifecycle-tasks/apply-templates', [EmployeeLifecycleTaskTemplateController::class, 'apply'])->middleware('permission:employee.manage');
            Route::get('employees/{employeeId}/onboarding-tasks', [EmployeeOnboardingTaskController::class, 'index'])->middleware('permission:employee.view|employee.manage');
            Route::post('employees/{employeeId}/onboarding-tasks', [EmployeeOnboardingTaskController::class, 'store'])->middleware('permission:employee.manage');
            Route::patch('employees/{employeeId}/onboarding-tasks/{taskId}', [EmployeeOnboardingTaskController::class, 'update'])->middleware('permission:employee.manage');
            Route::get('employees/{employeeId}/documents', [EmployeeDocumentController::class, 'index'])->middleware('permission:employee.view|employee.manage');
            Route::post('employees/{employeeId}/documents', [EmployeeDocumentController::class, 'store'])->middleware('permission:employee.manage');
            Route::get('employees/{employeeId}/documents/{documentId}/download', [EmployeeDocumentController::class, 'download'])
                ->middleware('permission:employee.view|employee.manage')
                ->name('employees.documents.download');
            Route::get('employees/{employeeId}/bank-accounts', [EmployeeBankAccountController::class, 'index'])->middleware('permission:employee.bank.view|employee.bank.manage');
            Route::post('employees/{employeeId}/bank-accounts', [EmployeeBankAccountController::class, 'store'])->middleware('permission:employee.bank.manage');
            Route::patch('employees/{employeeId}/bank-accounts/{bankAccountId}', [EmployeeBankAccountController::class, 'update'])->middleware('permission:employee.bank.manage');
            Route::get('employees/{employeeId}', [EmployeeController::class, 'show'])->middleware('permission:employee.view|employee.manage');
            Route::get('workflows', [WorkflowDefinitionController::class, 'index'])->middleware('permission:workflow.view');
            Route::post('workflows', [WorkflowDefinitionController::class, 'store'])->middleware('permission:workflow.create');
            Route::patch('workflows/{workflow}', [WorkflowDefinitionController::class, 'update'])->middleware('permission:workflow.publish|workflow.edit');
            Route::get('workflow-instances', [WorkflowInstanceController::class, 'index'])->middleware('permission:workflow.monitor');
            Route::post('workflow-instances', [WorkflowInstanceController::class, 'store'])->middleware('permission:workflow.execute');
            Route::get('tasks', [WorkflowTaskController::class, 'index']);
            Route::patch('tasks/{task}', [WorkflowTaskController::class, 'update']);
            Route::get('notifications', [NotificationController::class, 'index'])->middleware('permission:notification.view');
            Route::post('notifications', [NotificationController::class, 'store'])->middleware('permission:notification.manage');
            Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->middleware('permission:notification.view');
            Route::post('notifications/{notification}/retry', [NotificationController::class, 'retry'])->middleware('permission:notification.manage');
        });
    });
