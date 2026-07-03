<?php

use App\Modules\AssetManagement\Controllers\AssetCategoryController;
use App\Modules\AssetManagement\Controllers\AssetController;
use App\Modules\AIAssistant\Controllers\AiChatController;
use App\Modules\AIAssistant\Controllers\AiInteractionFeedbackController;
use App\Modules\AIAssistant\Controllers\AiRecommendationController;
use App\Modules\AIAssistant\Controllers\AiRecommendationDecisionController;
use App\Modules\AIAssistant\Controllers\AiWorkspaceController;
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
use App\Modules\GlobalizationLocalization\Controllers\LocalizationController;
use App\Modules\IntegrationsPlatform\Controllers\IntegrationCatalogController;
use App\Modules\IntegrationsPlatform\Controllers\IntegrationConnectionController;
use App\Modules\IntegrationsPlatform\Controllers\IntegrationEventDispatchController;
use App\Modules\IntegrationsPlatform\Controllers\IntegrationSyncJobController;
use App\Modules\IntegrationsPlatform\Controllers\PublicWebhookController;
use App\Modules\IntegrationsPlatform\Controllers\WebhookSubscriptionController;
use App\Modules\LearningManagement\Controllers\LearningAssignmentController;
use App\Modules\LearningManagement\Controllers\LearningAssignmentTargetController;
use App\Modules\LearningManagement\Controllers\LearningItemController;
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
use App\Modules\PerformanceManagement\Controllers\PerformanceCompetencyController;
use App\Modules\PerformanceManagement\Controllers\PerformanceGoalController;
use App\Modules\PerformanceManagement\Controllers\PerformanceReviewController;
use App\Modules\PerformanceManagement\Controllers\PerformanceReviewCycleController;
use App\Modules\Platform\Admin\Controllers\PermissionController;
use App\Modules\Platform\Admin\Controllers\RoleController;
use App\Modules\Platform\Admin\Controllers\UserController;
use App\Modules\Platform\Audit\Controllers\AuditLogController;
use App\Modules\Platform\Audit\Controllers\EmployeeAuditHistoryController;
use App\Modules\Platform\Audit\Controllers\OrganizationAuditHistoryController;
use App\Modules\Platform\Auth\Controllers\AuthController;
use App\Modules\Platform\Notifications\Controllers\NotificationController;
use App\Modules\Platform\Observability\Controllers\ObservabilityOverviewController;
use App\Modules\Platform\Resilience\Controllers\ResilienceReadinessController;
use App\Modules\Platform\Resilience\Controllers\ResilienceValidationRunController;
use App\Modules\Platform\Release\Controllers\ReleaseReadinessController;
use App\Modules\Platform\Release\Controllers\ReleaseReadinessDecisionController;
use App\Modules\Platform\Release\Controllers\ReleaseQualityGateController;
use App\Modules\Platform\UI\Controllers\UiVisibilityController;
use App\Modules\Platform\Workflow\Controllers\WorkflowDefinitionController;
use App\Modules\Platform\Workflow\Controllers\WorkflowInstanceController;
use App\Modules\Platform\Workflow\Controllers\WorkflowTaskController;
use App\Modules\RecruitmentManagement\Controllers\CandidateController;
use App\Modules\RecruitmentManagement\Controllers\InterviewController;
use App\Modules\RecruitmentManagement\Controllers\JobRequisitionController;
use App\Modules\RecruitmentManagement\Controllers\OfferController;
use App\Modules\RecruitmentManagement\Controllers\RecruitmentHireHandoffController;
use App\Modules\ReportingAnalytics\Controllers\KpiDefinitionController;
use App\Modules\ReportingAnalytics\Controllers\ReportDatasetController;
use App\Modules\ReportingAnalytics\Controllers\ReportExportController;
use App\Modules\ReportingAnalytics\Controllers\ReportingDashboardController;
use App\Modules\ReportingAnalytics\Controllers\ReportQueryController;
use App\Modules\ReportingAnalytics\Controllers\ReportSubscriptionController;
use App\Modules\ReportingAnalytics\Controllers\SavedReportViewController;
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

        Route::post('integrations/webhooks/{subscriptionKey}', [PublicWebhookController::class, 'store'])
            ->middleware('throttle:auth-sensitive');

        Route::middleware(['auth:sanctum', 'tenant'])->group(function (): void {
            Route::prefix('auth')->group(function (): void {
                Route::post('logout', [AuthController::class, 'logout']);
                Route::get('me', [AuthController::class, 'me']);
            });

            Route::get('ui/visibility', [UiVisibilityController::class, 'show']);
            Route::get('localization', [LocalizationController::class, 'show']);
            Route::patch('localization/preferences', [LocalizationController::class, 'updatePreferences']);
            Route::prefix('ai')->group(function (): void {
                Route::get('workspace', [AiWorkspaceController::class, 'show'])
                    ->middleware('permission:ai.view|ai.recommend');
                Route::post('chat', [AiChatController::class, 'store'])
                    ->middleware('permission:ai.view|ai.recommend');
                Route::post('recommendations', [AiRecommendationController::class, 'store'])
                    ->middleware('permission:ai.recommend');
                Route::post('recommendations/{recommendationId}/decisions', [AiRecommendationDecisionController::class, 'store'])
                    ->middleware('permission:ai.recommend');
                Route::post('interactions/{interactionId}/feedback', [AiInteractionFeedbackController::class, 'store'])
                    ->middleware('permission:ai.view|ai.recommend');
            });
            Route::get('resilience/readiness', [ResilienceReadinessController::class, 'show'])
                ->middleware('permission:resilience.view|resilience.manage');
            Route::post('resilience/validation-runs', [ResilienceValidationRunController::class, 'store'])
                ->middleware('permission:resilience.manage');
            Route::get('observability/overview', [ObservabilityOverviewController::class, 'show'])
                ->middleware('permission:observability.view|observability.manage');
            Route::get('release/quality-gates', [ReleaseQualityGateController::class, 'show'])
                ->middleware('permission:release.view|release.manage');
            Route::get('release/readiness', [ReleaseReadinessController::class, 'show'])
                ->middleware('permission:release.view|release.manage');
            Route::post('release/readiness/decisions', [ReleaseReadinessDecisionController::class, 'store'])
                ->middleware('permission:release.manage');
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
                Route::get('roles', [RoleController::class, 'index'])->middleware('permission:auth.manage_roles|auth.manage_permissions|auth.manage_users');
                Route::post('roles', [RoleController::class, 'store'])->middleware('permission:auth.manage_roles');
                Route::patch('roles/{role}', [RoleController::class, 'update'])->middleware('permission:auth.manage_roles');
                Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:auth.manage_permissions|auth.manage_roles');
                Route::get('users', [UserController::class, 'index'])->middleware('permission:auth.manage_users');
                Route::post('users', [UserController::class, 'store'])->middleware('permission:auth.manage_users');
                Route::patch('users/{user}', [UserController::class, 'update'])->middleware('permission:auth.manage_users');
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

            Route::prefix('integrations')->group(function (): void {
                Route::get('catalog', [IntegrationCatalogController::class, 'index'])
                    ->middleware('permission:integration.view|integration.manage');
                Route::get('connections', [IntegrationConnectionController::class, 'index'])
                    ->middleware('permission:integration.view|integration.manage');
                Route::post('connections', [IntegrationConnectionController::class, 'store'])
                    ->middleware('permission:integration.manage');
                Route::patch('connections/{integrationConnectionId}', [IntegrationConnectionController::class, 'update'])
                    ->middleware('permission:integration.manage');
                Route::get('webhook-subscriptions', [WebhookSubscriptionController::class, 'index'])
                    ->middleware('permission:integration.view|integration.manage');
                Route::post('webhook-subscriptions', [WebhookSubscriptionController::class, 'store'])
                    ->middleware('permission:integration.manage');
                Route::patch('webhook-subscriptions/{webhookSubscriptionId}', [WebhookSubscriptionController::class, 'update'])
                    ->middleware('permission:integration.manage');
                Route::post('events/dispatch', [IntegrationEventDispatchController::class, 'store'])
                    ->middleware('permission:integration.manage');
                Route::get('sync-jobs', [IntegrationSyncJobController::class, 'index'])
                    ->middleware('permission:integration.view|integration.manage');
                Route::get('sync-jobs/{integrationSyncJobId}', [IntegrationSyncJobController::class, 'show'])
                    ->middleware('permission:integration.view|integration.manage');
                Route::post('sync-jobs/{integrationSyncJobId}/process', [IntegrationSyncJobController::class, 'process'])
                    ->middleware('permission:integration.manage');
                Route::post('sync-jobs/{integrationSyncJobId}/retry', [IntegrationSyncJobController::class, 'retry'])
                    ->middleware('permission:integration.manage');
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

            Route::prefix('learning')->group(function (): void {
                Route::get('items', [LearningItemController::class, 'index'])
                    ->middleware('permission:learning.manage|learning.assign');
                Route::post('items', [LearningItemController::class, 'store'])
                    ->middleware('permission:learning.manage');
                Route::get('items/{learningItemId}', [LearningItemController::class, 'show'])
                    ->middleware('permission:learning.manage|learning.assign');
                Route::patch('items/{learningItemId}', [LearningItemController::class, 'update'])
                    ->middleware('permission:learning.manage');

                Route::get('assignments', [LearningAssignmentController::class, 'index'])
                    ->middleware('permission:learning.view|learning.manage|learning.assign');
                Route::post('assignments', [LearningAssignmentController::class, 'store'])
                    ->middleware('permission:learning.assign|learning.manage');
                Route::get('assignments/{learningAssignmentId}', [LearningAssignmentController::class, 'show'])
                    ->middleware('permission:learning.view|learning.manage|learning.assign');

                Route::get('targets', [LearningAssignmentTargetController::class, 'index'])
                    ->middleware('permission:learning.view|learning.manage|learning.assign');
                Route::get('targets/{learningAssignmentTargetId}', [LearningAssignmentTargetController::class, 'show'])
                    ->middleware('permission:learning.view|learning.manage|learning.assign');
                Route::patch('targets/{learningAssignmentTargetId}/complete', [LearningAssignmentTargetController::class, 'complete'])
                    ->middleware('permission:learning.complete|learning.manage');
                Route::get('my-assignments', [LearningAssignmentTargetController::class, 'mine'])
                    ->middleware('permission:learning.view|learning.complete|learning.manage');
            });

            Route::prefix('performance')->group(function (): void {
                Route::get('goals', [PerformanceGoalController::class, 'index'])
                    ->middleware('permission:performance.view|performance.manage|performance.review|performance.calibrate');
                Route::post('goals', [PerformanceGoalController::class, 'store'])
                    ->middleware('permission:performance.manage');
                Route::get('goals/{performanceGoalId}', [PerformanceGoalController::class, 'show'])
                    ->middleware('permission:performance.view|performance.manage|performance.review|performance.calibrate');
                Route::patch('goals/{performanceGoalId}', [PerformanceGoalController::class, 'update'])
                    ->middleware('permission:performance.manage');

                Route::get('competencies', [PerformanceCompetencyController::class, 'index'])
                    ->middleware('permission:performance.view|performance.manage|performance.review|performance.calibrate');
                Route::post('competencies', [PerformanceCompetencyController::class, 'store'])
                    ->middleware('permission:performance.manage');
                Route::get('competencies/{performanceCompetencyId}', [PerformanceCompetencyController::class, 'show'])
                    ->middleware('permission:performance.view|performance.manage|performance.review|performance.calibrate');
                Route::patch('competencies/{performanceCompetencyId}', [PerformanceCompetencyController::class, 'update'])
                    ->middleware('permission:performance.manage');

                Route::get('review-cycles', [PerformanceReviewCycleController::class, 'index'])
                    ->middleware('permission:performance.view|performance.manage|performance.review|performance.calibrate');
                Route::post('review-cycles', [PerformanceReviewCycleController::class, 'store'])
                    ->middleware('permission:performance.manage');
                Route::get('review-cycles/{performanceReviewCycleId}', [PerformanceReviewCycleController::class, 'show'])
                    ->middleware('permission:performance.view|performance.manage|performance.review|performance.calibrate');
                Route::patch('review-cycles/{performanceReviewCycleId}', [PerformanceReviewCycleController::class, 'update'])
                    ->middleware('permission:performance.manage');

                Route::get('reviews', [PerformanceReviewController::class, 'index'])
                    ->middleware('permission:performance.view|performance.manage|performance.review|performance.calibrate');
                Route::post('reviews', [PerformanceReviewController::class, 'store'])
                    ->middleware('permission:performance.manage');
                Route::get('reviews/{performanceReviewId}', [PerformanceReviewController::class, 'show'])
                    ->middleware('permission:performance.view|performance.manage|performance.review|performance.calibrate');
                Route::patch('reviews/{performanceReviewId}', [PerformanceReviewController::class, 'update'])
                    ->middleware('permission:performance.manage');
                Route::post('reviews/{performanceReviewId}/submit', [PerformanceReviewController::class, 'submit'])
                    ->middleware('permission:performance.view|performance.review|performance.manage');
                Route::post('reviews/{performanceReviewId}/calibrate', [PerformanceReviewController::class, 'calibrate'])
                    ->middleware('permission:performance.calibrate|performance.manage');
                Route::post('reviews/{performanceReviewId}/finalize', [PerformanceReviewController::class, 'finalize'])
                    ->middleware('permission:performance.calibrate|performance.manage');
                Route::post('reviews/{performanceReviewId}/publish', [PerformanceReviewController::class, 'publish'])
                    ->middleware('permission:performance.manage');
                Route::post('reviews/{performanceReviewId}/reopen', [PerformanceReviewController::class, 'reopen'])
                    ->middleware('permission:performance.calibrate|performance.manage');
            });

            Route::prefix('recruitment')->group(function (): void {
                Route::get('requisitions', [JobRequisitionController::class, 'index'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.approve');
                Route::post('requisitions', [JobRequisitionController::class, 'store'])
                    ->middleware('permission:recruitment.manage');
                Route::get('requisitions/{jobRequisitionId}', [JobRequisitionController::class, 'show'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.approve');
                Route::patch('requisitions/{jobRequisitionId}', [JobRequisitionController::class, 'update'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.approve');
                Route::get('candidates', [CandidateController::class, 'index'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.approve');
                Route::post('candidates', [CandidateController::class, 'store'])
                    ->middleware('permission:recruitment.manage');
                Route::get('candidates/{candidateId}', [CandidateController::class, 'show'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.approve');
                Route::patch('candidates/{candidateId}', [CandidateController::class, 'update'])
                    ->middleware('permission:recruitment.manage');
                Route::post('candidates/{candidateId}/resumes', [CandidateController::class, 'storeResume'])
                    ->middleware('permission:recruitment.manage');
                Route::get('candidates/{candidateId}/resumes/{candidateResumeId}/download', [CandidateController::class, 'downloadResume'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.approve')
                    ->name('recruitment.candidates.resumes.download');
                Route::post('candidates/{candidateId}/stage-transitions', [CandidateController::class, 'transitionStage'])
                    ->middleware('permission:recruitment.manage');
                Route::get('interviews', [InterviewController::class, 'index'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.interview|recruitment.approve');
                Route::post('interviews', [InterviewController::class, 'store'])
                    ->middleware('permission:recruitment.manage');
                Route::get('interviews/{interviewId}', [InterviewController::class, 'show'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.interview|recruitment.approve');
                Route::patch('interviews/{interviewId}', [InterviewController::class, 'update'])
                    ->middleware('permission:recruitment.manage');
                Route::post('interviews/{interviewId}/feedback', [InterviewController::class, 'storeFeedback'])
                    ->middleware('permission:recruitment.manage|recruitment.interview');
                Route::get('offers', [OfferController::class, 'index'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.approve');
                Route::post('offers', [OfferController::class, 'store'])
                    ->middleware('permission:recruitment.manage');
                Route::get('offers/{offerId}', [OfferController::class, 'show'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.approve');
                Route::patch('offers/{offerId}', [OfferController::class, 'update'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.approve');
                Route::get('handoffs', [RecruitmentHireHandoffController::class, 'index'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.approve|employee.view|employee.manage');
                Route::post('offers/{offerId}/handoff', [RecruitmentHireHandoffController::class, 'store'])
                    ->middleware('permission:employee.manage');
                Route::get('handoffs/{handoffId}', [RecruitmentHireHandoffController::class, 'show'])
                    ->middleware('permission:recruitment.view|recruitment.manage|recruitment.approve|employee.view|employee.manage');
            });

            Route::prefix('reporting')->group(function (): void {
                Route::get('kpis', [KpiDefinitionController::class, 'index'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::post('kpis', [KpiDefinitionController::class, 'store'])
                    ->middleware('permission:reporting.manage|reporting.certify');
                Route::get('kpis/{kpiDefinitionId}', [KpiDefinitionController::class, 'show'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::patch('kpis/{kpiDefinitionId}', [KpiDefinitionController::class, 'update'])
                    ->middleware('permission:reporting.manage|reporting.certify');

                Route::get('datasets', [ReportDatasetController::class, 'index'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::post('datasets', [ReportDatasetController::class, 'store'])
                    ->middleware('permission:reporting.manage|reporting.certify');
                Route::get('datasets/{reportDatasetId}', [ReportDatasetController::class, 'show'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::patch('datasets/{reportDatasetId}', [ReportDatasetController::class, 'update'])
                    ->middleware('permission:reporting.manage|reporting.certify');
                Route::get('reports/{datasetKey}', [ReportQueryController::class, 'show'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::get('dashboards/{dashboardKey}', [ReportingDashboardController::class, 'show'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::get('exports', [ReportExportController::class, 'index'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::post('exports', [ReportExportController::class, 'store'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::get('exports/{reportExportId}', [ReportExportController::class, 'show'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::post('exports/{reportExportId}/process', [ReportExportController::class, 'process'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::get('exports/{reportExportId}/download', [ReportExportController::class, 'download'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify')
                    ->name('reporting.exports.download');
                Route::get('saved-views', [SavedReportViewController::class, 'index'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::post('saved-views', [SavedReportViewController::class, 'store'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::get('saved-views/{savedReportViewId}', [SavedReportViewController::class, 'show'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::patch('saved-views/{savedReportViewId}', [SavedReportViewController::class, 'update'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::delete('saved-views/{savedReportViewId}', [SavedReportViewController::class, 'destroy'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::get('subscriptions', [ReportSubscriptionController::class, 'index'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::post('subscriptions', [ReportSubscriptionController::class, 'store'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::get('subscriptions/{reportSubscriptionId}', [ReportSubscriptionController::class, 'show'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::patch('subscriptions/{reportSubscriptionId}', [ReportSubscriptionController::class, 'update'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::delete('subscriptions/{reportSubscriptionId}', [ReportSubscriptionController::class, 'destroy'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
                Route::post('subscriptions/{reportSubscriptionId}/deliver', [ReportSubscriptionController::class, 'deliver'])
                    ->middleware('permission:reporting.view|reporting.manage|reporting.certify');
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
