<?php

use App\Modules\EmployeeManagement\Controllers\EmployeeAddressController;
use App\Modules\EmployeeManagement\Controllers\EmployeeBankAccountController;
use App\Modules\EmployeeManagement\Controllers\EmployeeBulkImportController;
use App\Modules\EmployeeManagement\Controllers\EmployeeContactController;
use App\Modules\EmployeeManagement\Controllers\EmployeeController;
use App\Modules\EmployeeManagement\Controllers\EmployeeDocumentController;
use App\Modules\EmployeeManagement\Controllers\EmployeeEmergencyContactController;
use App\Modules\EmployeeManagement\Controllers\EmployeeOnboardingTaskController;
use App\Modules\OrganizationManagement\Controllers\CompanyProfileController;
use App\Modules\OrganizationManagement\Controllers\CostCenterController;
use App\Modules\OrganizationManagement\Controllers\DepartmentController;
use App\Modules\OrganizationManagement\Controllers\DesignationController;
use App\Modules\OrganizationManagement\Controllers\LocationController;
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

            Route::get('employees', [EmployeeController::class, 'index'])->middleware('permission:employee.view|employee.manage');
            Route::get('employees/onboarding-status', [EmployeeOnboardingTaskController::class, 'incompleteStatus'])->middleware('permission:employee.view|employee.manage');
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
