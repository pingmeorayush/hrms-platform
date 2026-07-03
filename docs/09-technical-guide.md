# PhoenixHRMS Technical Guide

Last updated: 2026-07-03

Companion document:

- [Product Bible](./08-software-bible.md)

## 1. Purpose

This guide is the technical companion to the Product Bible.

Use it when you need:

- repository structure
- runtime behavior
- frontend route inventory
- backend module inventory
- API domain maps
- OpenAPI contract references
- test coverage references
- seeded-account guidance
- implementation notes and update checklists

If you want to understand the product in business language, use the [Product Bible](./08-software-bible.md) first.

## 2. Source-of-truth hierarchy

PhoenixHRMS now uses a layered truth model:

### 2.1 Business truth

- `docs/08-software-bible.md`

Use this for:

- customer explanation
- internal onboarding for non-engineers
- demo storylines
- module meaning

### 2.2 Technical truth

- `docs/09-technical-guide.md`

Use this for:

- architecture orientation
- codebase orientation
- route and API mapping
- tests and contracts

### 2.3 Contract truth

- `apps/api/openapi/*.yaml`
- `apps/api/openapi/README.md`

Use this for:

- integration-safe endpoint and schema review
- cross-team API review
- contract linting and CI validation

### 2.4 Code truth

When docs and code drift, the code is the immediate implementation truth.

Primary files to check first:

- `apps/web/src/app/routes/AppRoutes.tsx`
- `apps/web/src/app/shell/navigation.ts`
- `apps/api/routes/api.php`
- module service, controller, and test files

The fix should be:

1. confirm the intended implementation
2. update the code if the code is wrong
3. update this guide and the Product Bible if the docs are wrong

## 3. Repository layout

Top-level structure:

- `apps/api`
  - Laravel API application
- `apps/web`
  - React + Vite web application
- `docs`
  - product, architecture, module, sprint, runbook, and supporting documentation

Important doc subfolders:

- `docs/modules`
  - module-level implementation summaries
- `docs/sprints`
  - sprint delivery history
- `docs/files`
  - imported source specifications
- `docs/runbooks`
  - operational and implementation runbooks

## 4. Runtime topology

## 4.1 Current local stack

The repo currently runs with:

- API container
- queue worker container
- web container

Defined in:

- `docker-compose.yml`

Default local URLs:

- API: `http://localhost:8000`
- Web: `http://localhost:5173`

## 4.2 Local persistence behavior

Current local defaults are:

- `DB_CONNECTION=sqlite`
- database-backed queue
- database-backed session
- database-backed cache

This is important because some older architecture documents describe:

- PostgreSQL
- Redis
- S3

Those may still reflect target or broader deployment intent, but the working repo baseline is the Docker Compose configuration.

## 4.3 Backend package baseline

From `apps/api/composer.json`:

- PHP `^8.3`
- Laravel `^13.8`
- Sanctum
- Spatie Permission
- PHPUnit
- Larastan
- PHPStan
- Pint

## 4.4 Frontend package baseline

From `apps/web/package.json`:

- React `19`
- TypeScript
- Vite
- React Router
- Redux Toolkit
- TanStack Query
- Vitest

## 5. Access and session architecture

This is one of the most important changes since the earlier version of the documentation.

## 5.1 Public frontend routes

The web app now has dedicated public auth routes:

- `/login`
- `/reset-password`

Implemented in:

- `apps/web/src/modules/access/pages/LoginPage.tsx`
- `apps/web/src/modules/access/pages/ResetPasswordPage.tsx`

## 5.2 Protected shell gating

Protected application routes now sit behind:

- `AppSessionGate`

Implemented in:

- `apps/web/src/app/routes/AppSessionGate.tsx`

Behavior:

- allows demo mode immediately when demo access is enabled
- redirects to `/login` if a live session is required and no token is present
- redirects to `/login?reason=session-expired` if a live session returns `401`
- shows session-state cards while validating the protected workspace

## 5.3 Frontend access state

Frontend access state is managed in:

- `apps/web/src/app/store/accessSlice.ts`

State shape:

- `mode`: `demo` or `live`
- `demoPersona`
- `apiBaseUrl`
- `token`

Additional behavior:

- access state persists to local storage
- `setLiveSession` forces `mode` to `live`
- `clearLiveSession` preserves live mode but clears the bearer token

## 5.4 Demo mode availability

Demo mode is not unconditional anymore.

Defined in:

- `apps/web/src/modules/access/runtime.ts`

Current rule:

- demo access is enabled when `MODE === "test"` or `VITE_ENABLE_DEMO_ACCESS === "true"`

This means:

- test runs always have demo access
- non-test environments can disable demo mode entirely

## 5.5 Access snapshot model

Access resolution is centralized in:

- `apps/web/src/modules/access/hooks/useAccessSnapshot.ts`

Behavior:

- in demo mode, the hook returns seeded snapshot data
- in live mode, it calls:
  - `GET /auth/me`
  - `GET /ui/visibility`

Data model is defined in:

- `apps/web/src/modules/access/types.ts`

Core returned concepts:

- user identity
- linked employee summary
- tenant information
- regional settings
- visibility contract

## 5.6 Route-level permission guards

Route gating after session validation is handled by:

- `apps/web/src/app/routes/RouteGuard.tsx`

Behavior:

- shows loading, session-required, access-error, and access-blocked states
- checks permissions against the current visibility contract
- is used across all protected module routes

## 5.7 Access administration surface

The routed `/access` page is now:

- `AccessAdminPage`

Implemented in:

- `apps/web/src/modules/access/pages/AccessAdminPage.tsx`

This replaces the older routed use of `AccessContractPage`, which still exists in:

- `apps/web/src/app/pages/AccessContractPage.tsx`

Current routed behavior:

- `/access` points to `AccessAdminPage`

Current tabs in the access workspace:

- `Users`
- `Roles`
- `Routes`
- `Actions`
- `Diagnostics`

## 6. Frontend route inventory

This section lists the active routed surface in the current web app.

## 6.1 Public routes

| Route | Primary page | Notes |
| --- | --- | --- |
| `/login` | `LoginPage` | Sign in, MFA challenge, and forgot-password initiation |
| `/reset-password` | `ResetPasswordPage` | Controlled password-reset completion |

## 6.2 Protected root shell

Protected routes are wrapped in:

- `AppSessionGate`
- `AppShell`

Default redirect:

- `/` -> `/foundation`

Fallback redirect:

- `*` -> `/foundation`

## 6.3 Foundation and governance routes

| Route | Primary page |
| --- | --- |
| `/foundation` | `FoundationOverviewPage` |
| `/assistant` | `AssistantPage` |
| `/access` | `AccessAdminPage` |

## 6.4 Organization routes

| Route | Primary page |
| --- | --- |
| `/admin/organization` | `OrganizationAdminPage` with redirect |
| `/admin/organization/overview` | `OrganizationOverviewPage` |
| `/admin/organization/company-profile` | `OrganizationCompanyProfilePage` |
| `/admin/organization/structure` | `OrganizationStructurePage` |
| `/admin/organization/locations` | `OrganizationLocationsPage` |
| `/admin/organization/cost-centers` | `OrganizationCostCentersPage` |

## 6.5 Employee routes

| Route | Primary page |
| --- | --- |
| `/employees` | `EmployeeAdminPage` with redirect |
| `/employees/overview` | `EmployeeOverviewPage` |
| `/employees/directory` | `EmployeeDirectorySectionPage` |
| `/employees/lifecycle-watch` | `EmployeeLifecycleWatchPage` |
| `/employees/onboarding` | `EmployeeOnboardingPage` |
| `/employees/documents` | `EmployeeDocumentsPage` |
| `/employees/audit` | `EmployeeAuditPage` |
| `/employees/:employeeId` | `EmployeeDetailPage` with redirect |
| `/employees/:employeeId/profile` | `EmployeeProfileRouteSection` |
| `/employees/:employeeId/lifecycle` | `EmployeeLifecycleRouteSection` |
| `/employees/:employeeId/onboarding` | `EmployeeOnboardingRouteSection` |
| `/employees/:employeeId/documents` | `EmployeeDocumentsRouteSection` |
| `/employees/:employeeId/history` | `EmployeeHistoryRouteSection` |

## 6.6 Attendance routes

| Route | Primary page |
| --- | --- |
| `/attendance` | `AttendanceAdminPage` with redirect |
| `/attendance/overview` | `AttendanceOverviewPage` |
| `/attendance/my-attendance/*` | `AttendanceSelfServicePage` |
| `/attendance/operational-review` | `AttendanceOperationalReviewPage` |
| `/attendance/admin-setup/*` | `AttendanceAdminSetupPage` |

Important internal sub-route behavior:

- `/attendance/my-attendance` redirects to `/attendance/my-attendance/history`
- `/attendance/admin-setup` redirects to `/attendance/admin-setup/policy`

## 6.7 Leave routes

| Route | Primary page |
| --- | --- |
| `/leave` | `LeaveAdminPage` with redirect |
| `/leave/overview` | `LeaveOverviewPage` |
| `/leave/requests` | `LeaveRequestsPage` |
| `/leave/approvals` | `LeaveApprovalsPage` |
| `/leave/policy-admin` | `LeavePolicyAdminPage` |

## 6.8 Payroll routes

| Route | Primary page |
| --- | --- |
| `/payroll` | `PayrollAdminPage` with redirect |
| `/payroll/setup` | `PayrollSetupPage` |
| `/payroll/overview` | `PayrollOverviewPage` |
| `/payroll/review` | `PayrollReviewPage` |
| `/payroll/run-console` | `PayrollRunConsolePage` |
| `/payroll/my-pay` | `PayrollSelfServicePage` |

## 6.9 Recruitment routes

| Route | Primary page |
| --- | --- |
| `/recruitment` | `RecruitmentAdminPage` with redirect |
| `/recruitment/overview` | `RecruitmentOverviewPage` |
| `/recruitment/requisitions` | `RecruitmentRequisitionsPage` |
| `/recruitment/candidates` | `RecruitmentCandidatesPage` |
| `/recruitment/candidates/:candidateId` | `RecruitmentCandidateDetailPage` |

## 6.10 Performance routes

| Route | Primary page |
| --- | --- |
| `/performance` | `PerformanceAdminPage` with redirect |
| `/performance/overview` | `PerformanceOverviewPage` |
| `/performance/goals` | `PerformanceGoalsPage` |
| `/performance/cycles` | `PerformanceCyclesPage` |
| `/performance/reviews` | `PerformanceReviewsPage` |

## 6.11 Learning routes

| Route | Primary page |
| --- | --- |
| `/learning` | `LearningAdminPage` with redirect |
| `/learning/overview` | `LearningOverviewPage` |
| `/learning/catalog` | `LearningCatalogPage` |
| `/learning/assignments` | `LearningAssignmentsPage` |
| `/learning/my-learning` | `LearningMyLearningPage` |

## 6.12 Operations routes

| Route | Primary page |
| --- | --- |
| `/operations` | `OperationsAdminPage` with redirect |
| `/operations/overview` | `OperationsOverviewPage` |
| `/operations/documents` | `OperationsDocumentsPage` |
| `/operations/assets` | `OperationsAssetsPage` |
| `/operations/integrations` | `OperationsIntegrationsPage` |
| `/operations/release` | `OperationsReleasePage` |
| `/operations/readiness` | `OperationsReleaseReadinessPage` |
| `/operations/observability` | `OperationsObservabilityPage` |
| `/operations/resilience` | `OperationsResiliencePage` |
| `/operations/lifecycle` | `OperationsLifecyclePage` |

## 6.13 Reporting routes

| Route | Primary page |
| --- | --- |
| `/reporting` | `ReportingAdminPage` with redirect |
| `/reporting/overview` | `ReportingOverviewPage` |
| `/reporting/explorer` | `ReportingExplorerPage` |
| `/reporting/exports` | `ReportingExportsPage` |
| `/reporting/subscriptions` | `ReportingSubscriptionsPage` |
| `/reporting/workforce` | `ReportingWorkforcePage` |
| `/reporting/team` | `ReportingTeamPage` |
| `/reporting/payroll` | `ReportingPayrollPage` |
| `/reporting/recruitment` | `ReportingRecruitmentPage` |
| `/reporting/executive` | `ReportingExecutivePage` |

## 6.14 Self-service routes

| Route | Primary page |
| --- | --- |
| `/self-service` | `SelfServicePage` with redirect |
| `/self-service/profile` | `SelfServiceProfilePage` |
| `/self-service/documents` | `SelfServiceDocumentsPage` |
| `/self-service/assets` | `SelfServiceAssetsPage` |

## 7. Frontend navigation model

Top-level navigation is defined in:

- `apps/web/src/app/shell/navigation.ts`

Current top-level nav items:

- Foundation
- Assistant
- Organization
- Employees
- Recruitment
- Performance
- Learning
- Operations
- Attendance
- Leave
- Payroll
- Reporting
- Self service
- Access

The `Access` nav item has changed meaning.

It now describes:

- session auth
- user access
- role governance
- backend visibility checks

Required permissions:

- `auth.manage_roles`
- `auth.manage_permissions`
- `auth.manage_users`

## 8. Backend module inventory

The API is organized by domain modules under:

- `apps/api/app/Modules`

Current top-level backend modules:

- `AIAssistant`
- `AssetManagement`
- `AttendanceManagement`
- `DocumentManagement`
- `EmployeeManagement`
- `GlobalizationLocalization`
- `IntegrationsPlatform`
- `LearningManagement`
- `LeaveManagement`
- `OrganizationManagement`
- `PayrollManagement`
- `PerformanceManagement`
- `Platform`
- `RecruitmentManagement`
- `ReportingAnalytics`

## 8.1 Platform module

Subdomains:

- `Auth`
- `Admin`
- `Audit`
- `Notifications`
- `Observability`
- `Release`
- `Resilience`
- `UI`
- `Workflow`
- `Tenancy`
- `Shared`

Important recent change:

- `Platform/Admin` now includes user administration in addition to role and permission admin.

Files:

- `Controllers/UserController.php`
- `Requests/StoreAdminUserRequest.php`
- `Requests/UpdateAdminUserRequest.php`
- `Resources/AdminUserResource.php`
- `Services/AccessAdministrationService.php`

## 8.2 Organization Management

Primary service:

- `OrganizationStructureService.php`

Primary models:

- `Company`
- `Department`
- `Designation`
- `Location`
- `CostCenter`

## 8.3 Employee Management

Primary services:

- `EmployeeService.php`
- `EmployeeDirectoryService.php`
- `EmployeeProfileDetailService.php`
- `EmployeeOnboardingService.php`
- `EmployeeDocumentService.php`
- `EmployeeBankAccountService.php`
- `EmployeeTaskCenterService.php`
- `EmployeeSelfServiceWorkspaceService.php`
- `EmployeeLifecycleTaskTemplateService.php`
- `PolicyAcknowledgementService.php`
- `EmployeeBulkImportValidationService.php`

Primary models:

- `Employee`
- `EmploymentHistory`
- `EmployeeContact`
- `EmployeeAddress`
- `EmployeeEmergencyContact`
- `EmployeeBankAccount`
- `EmployeeDocument`
- `EmployeeOnboardingTask`
- `EmployeeLifecycleTaskTemplate`
- `PolicyAcknowledgement`

## 8.4 Attendance Management

Primary services:

- `AttendanceConfigurationService.php`
- `AttendanceSchedulingService.php`
- `AttendanceRecordService.php`
- `AttendanceCalculationService.php`
- `AttendanceCorrectionService.php`
- `AttendanceOperationalReviewService.php`
- `AttendanceAccessScopeService.php`
- `AttendanceContextResolver.php`

Primary models:

- `AttendancePolicy`
- `HolidayCalendar`
- `Holiday`
- `Shift`
- `ShiftAssignment`
- `ShiftRoster`
- `AttendanceRecord`
- `AttendanceCorrection`

## 8.5 Leave Management

Primary services:

- `LeaveConfigurationService.php`
- `LeaveAccrualService.php`
- `LeaveBalanceService.php`
- `LeaveRequestService.php`
- `LeaveBalanceAccessScopeService.php`
- `LeaveRequestAccessScopeService.php`

Primary models:

- `LeaveType`
- `LeavePolicy`
- `LeaveBalance`
- `LeaveBalanceEntry`
- `LeaveAccrual`
- `LeaveRequest`
- `LeaveEncashment`

## 8.6 Payroll Management

Primary services:

- `SalaryConfigurationService.php`
- `EmployeeCompensationService.php`
- `PayrollPrerequisiteService.php`
- `PayrollInputService.php`
- `PayrollCalculationService.php`
- `PayrollControlService.php`
- `PayslipService.php`
- `PayslipAccessScopeService.php`

Primary models:

- `PayrollCalendar`
- `PayrollPeriod`
- `PayrollRun`
- `SalaryComponent`
- `SalaryStructure`
- `SalaryStructureComponent`
- `EmployeeCompensation`
- `PayrollInput`
- `PayrollAdjustment`
- `PayrollItem`
- `Payslip`

## 8.7 Recruitment Management

Primary services:

- `JobRequisitionService.php`
- `CandidateService.php`
- `InterviewService.php`
- `OfferService.php`
- `RecruitmentHireHandoffService.php`

Scope services also exist for:

- requisitions
- candidates
- interviews
- offers
- hire handoffs

Primary models:

- `JobRequisition`
- `Candidate`
- `CandidateResume`
- `CandidateStageTransition`
- `Interview`
- `InterviewFeedback`
- `Offer`
- `OfferDecision`
- `RecruitmentHireHandoff`

## 8.8 Performance Management

Primary services:

- `PerformanceConfigurationService.php`
- `PerformanceReviewExecutionService.php`
- `PerformanceAccessScopeService.php`

Primary models:

- `PerformanceGoal`
- `PerformanceCompetency`
- `PerformanceReviewCycle`
- `PerformanceReview`
- `PerformanceReviewSubmission`

## 8.9 Learning Management

Primary services:

- `LearningManagementService.php`
- `LearningTrackingStateResolver.php`
- `LearningAccessScopeService.php`

Primary models:

- `LearningItem`
- `LearningAssignment`
- `LearningAssignmentTarget`

## 8.10 Document Management

Primary services:

- `DocumentRepositoryService.php`
- `DocumentCategoryService.php`

Primary models:

- `Document`
- `DocumentCategory`

Related employee-facing model:

- `EmployeeDocument`

## 8.11 Asset Management

Primary services:

- `AssetCatalogService.php`
- `AssetLifecycleService.php`

Primary models:

- `Asset`
- `AssetCategory`
- `AssetAssignment`

## 8.12 Integrations Platform

Primary service:

- `IntegrationPlatformService.php`

Primary models:

- `IntegrationConnection`
- `WebhookSubscription`
- `IntegrationSyncJob`
- `IntegrationSyncError`

## 8.13 Reporting Analytics

Primary services:

- `ReportingCatalogService.php`
- `ReportingQueryService.php`
- `ReportingDashboardService.php`
- `ReportingExportService.php`
- `ReportingSavedViewService.php`
- `ReportingSubscriptionService.php`
- `ReportingAccessScopeService.php`

Primary models:

- `KpiDefinition`
- `ReportDataset`
- `DashboardSnapshot`
- `DashboardWidget`
- `ReportExport`
- `SavedReportView`
- `ReportSubscription`

## 8.14 AI Assistant

Primary service:

- `AiAssistantService.php`

Primary models:

- `AiConversation`
- `AiInteraction`
- `AiRecommendation`

## 8.15 Globalization and Localization

Primary service:

- `LocalizationService.php`

Primary data surface:

- company-level regional defaults
- user-level regional overrides

## 9. API domain map

Primary route file:

- `apps/api/routes/api.php`

The API is versioned under:

- `/api/v1`

## 9.1 Auth and session routes

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/verify-mfa`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`

## 9.2 Access administration routes

These are the most important recent additions or expansions:

- `GET /api/v1/admin/roles`
- `POST /api/v1/admin/roles`
- `PATCH /api/v1/admin/roles/{role}`
- `GET /api/v1/admin/permissions`
- `GET /api/v1/admin/users`
- `POST /api/v1/admin/users`
- `PATCH /api/v1/admin/users/{user}`

Business rule enforced in code:

- tenant admins can manage tenant-scoped users
- only platform super admins can change shared role definitions
- non-platform admins cannot manage platform-level admin users

## 9.3 Visibility and localization routes

- `GET /api/v1/ui/visibility`
- `GET /api/v1/localization`
- `PATCH /api/v1/localization/preferences`

## 9.4 AI routes

- `GET /api/v1/ai/workspace`
- `POST /api/v1/ai/chat`
- `POST /api/v1/ai/recommendations`
- `POST /api/v1/ai/recommendations/{recommendationId}/decisions`
- `POST /api/v1/ai/interactions/{interactionId}/feedback`

## 9.5 Organization routes

- `GET/PATCH /api/v1/organization/company-profile`
- `GET/POST/PATCH` department routes
- `GET/POST/PATCH` designation routes
- `GET/POST/PATCH` location routes
- `GET/POST/PATCH` cost-center routes
- `GET /api/v1/organization/audit-history`

## 9.6 Employee routes

The employee surface includes:

- employee directory and detail
- contacts
- addresses
- emergency contacts
- bank accounts
- lifecycle actions
- onboarding tasks
- lifecycle task templates
- employee documents
- bulk import validation
- audit history
- task center
- self-service workspace
- policy acknowledgements

## 9.7 Attendance routes

The attendance surface includes:

- policy
- records
- check-in
- check-out
- recalculation
- operational review
- pending exceptions
- corrections
- holiday calendars
- holidays
- shifts
- assignments
- rosters

## 9.8 Leave routes

The leave surface includes:

- leave types
- leave policies
- accrual preview
- balances
- requests

## 9.9 Payroll routes

The payroll surface includes:

- calendars
- periods
- runs
- run calculation lifecycle
- inputs
- adjustments
- salary components
- salary structures
- compensations
- payslips

## 9.10 Learning routes

The learning surface includes:

- items
- assignments
- targets
- my assignments

## 9.11 Performance routes

The performance surface includes:

- goals
- competencies
- review cycles
- reviews
- review transitions such as submit, calibrate, finalize, publish, and reopen

## 9.12 Recruitment routes

The recruitment surface includes:

- requisitions
- candidates
- resumes
- stage transitions
- interviews
- interview feedback
- offers
- hire handoffs

## 9.13 Reporting routes

The reporting surface includes:

- kpis
- datasets
- reports by dataset key
- dashboards by dashboard key
- exports
- saved views
- subscriptions

## 9.14 Operations-related platform routes

- `GET /api/v1/observability/overview`
- `GET /api/v1/release/quality-gates`
- `GET /api/v1/release/readiness`
- `POST /api/v1/release/readiness/decisions`
- `GET /api/v1/resilience/readiness`
- `POST /api/v1/resilience/validation-runs`

## 9.15 Workflow, notifications, and audit routes

These do not yet have the same dedicated first-class routed web presence as the main module workspaces, but they are live in the API:

- `GET /api/v1/workflows`
- `POST /api/v1/workflows`
- `PATCH /api/v1/workflows/{workflow}`
- `GET /api/v1/workflow-instances`
- `POST /api/v1/workflow-instances`
- `GET /api/v1/tasks`
- `PATCH /api/v1/tasks/{task}`
- `GET /api/v1/notifications`
- `POST /api/v1/notifications`
- `PATCH /api/v1/notifications/{notification}/read`
- `POST /api/v1/notifications/{notification}/retry`
- `GET /api/v1/audit-logs`

## 10. OpenAPI contract inventory

Current published contract files:

- `sprint-01-platform-foundation.yaml`
- `sprint-02-employee-organization-management.yaml`
- `sprint-03-attendance-shift-operations.yaml`
- `sprint-04-leave-manager-workflows.yaml`
- `sprint-05-payroll-compensation.yaml`
- `sprint-06-documents-assets-ess-onoffboarding.yaml`
- `sprint-07-recruitment-operations.yaml`
- `sprint-07-performance-management.yaml`
- `sprint-07-learning-management.yaml`
- `sprint-08-reporting-governance-query.yaml`
- `sprint-08-reporting-dashboards.yaml`
- `sprint-08-reporting-delivery.yaml`
- `sprint-09-mobile-ess-globalization.yaml`
- `sprint-09-integrations.yaml`
- `sprint-10-operations-release-controls.yaml`
- `sprint-10-ai-assistant.yaml`

Reference:

- `apps/api/openapi/README.md`

## 11. Test inventory

## 11.1 Backend feature tests

Current backend feature test files:

- `AccessAdministrationApiTest.php`
- `AiAssistantApiTest.php`
- `AssetManagementApiTest.php`
- `AttendanceApiTest.php`
- `AuthApiTest.php`
- `DocumentRepositoryApiTest.php`
- `EmployeeCompensationApiTest.php`
- `EmployeeLifecycleTaskApiTest.php`
- `EmployeeOrganizationApiTest.php`
- `EmployeeSelfServiceApiTest.php`
- `EmployeeTaskCenterApiTest.php`
- `IntegrationsPlatformApiTest.php`
- `LearningManagementApiTest.php`
- `LeaveApiTest.php`
- `ObservabilityOverviewApiTest.php`
- `PayrollApiTest.php`
- `PayrollInputApiTest.php`
- `PayrollRunCalculationApiTest.php`
- `PayslipApiTest.php`
- `PerformanceConfigurationApiTest.php`
- `PerformanceReviewExecutionApiTest.php`
- `RecruitmentApiTest.php`
- `RecruitmentCandidateApiTest.php`
- `RecruitmentHireHandoffApiTest.php`
- `RecruitmentInterviewOfferApiTest.php`
- `ReleaseQualityGateApiTest.php`
- `ReleaseReadinessApiTest.php`
- `ReportingCatalogApiTest.php`
- `ReportingDashboardApiTest.php`
- `ReportingExportApiTest.php`
- `ReportingQueryApiTest.php`
- `ReportingSavedViewSubscriptionApiTest.php`
- `ResilienceReadinessApiTest.php`
- `SalaryConfigurationApiTest.php`
- `TenantAuthorizationTest.php`
- `WorkflowNotificationApiTest.php`

Important recent addition:

- `AccessAdministrationApiTest.php`

It verifies:

- tenant-scoped user listing
- user creation
- user update
- platform-role restrictions
- shared-role-definition restrictions

## 11.2 Frontend tests

Current frontend test files include:

- `src/app/pages/FoundationOverviewPage.test.tsx`
- `src/app/routes/AppRoutes.test.tsx`
- `src/modules/access/components/Can.test.tsx`
- `src/modules/access/components/VisibilityWorkbench.test.tsx`
- `src/modules/assistant/pages/AssistantPage.test.tsx`
- `src/modules/attendance/components/AttendanceAdminWorkspace.test.tsx`
- `src/modules/attendance/components/AttendanceEmployeeWorkspace.test.tsx`
- `src/modules/attendance/components/AttendanceReviewWorkspace.test.tsx`
- `src/modules/employees/components/EmployeeDirectoryWorkspace.test.tsx`
- `src/modules/employees/components/EmployeeDetailShell.test.tsx`
- `src/modules/learning/pages/LearningCatalogPage.test.tsx`
- `src/modules/learning/pages/LearningMyLearningPage.test.tsx`
- `src/modules/leave/components/LeaveAdminWorkspace.test.tsx`
- `src/modules/leave/components/LeaveEmployeeWorkspace.test.tsx`
- `src/modules/leave/components/LeaveReviewWorkspace.test.tsx`
- `src/modules/operations/pages/OperationsAssetsPage.test.tsx`
- `src/modules/operations/pages/OperationsIntegrationsPage.test.tsx`
- `src/modules/operations/pages/OperationsLifecyclePage.test.tsx`
- `src/modules/operations/pages/OperationsObservabilityPage.test.tsx`
- `src/modules/operations/pages/OperationsReleasePage.test.tsx`
- `src/modules/operations/pages/OperationsReleaseReadinessPage.test.tsx`
- `src/modules/operations/pages/OperationsResiliencePage.test.tsx`
- `src/modules/organization/components/OrganizationAdminWorkspace.test.tsx`
- `src/modules/payroll/pages/PayrollReviewPage.test.tsx`
- `src/modules/payroll/pages/PayrollRunConsolePage.test.tsx`
- `src/modules/payroll/pages/PayrollSelfServicePage.test.tsx`
- `src/modules/payroll/pages/PayrollSetupPage.test.tsx`
- `src/modules/performance/pages/PerformanceReviewsPage.test.tsx`
- `src/modules/recruitment/pages/RecruitmentCandidateDetailPage.test.tsx`
- `src/modules/reporting/pages/ReportingOperationsPages.test.tsx`
- `src/modules/reporting/pages/ReportingOverviewPage.test.tsx`
- `src/modules/self-service/pages/SelfServiceDocumentsPage.test.tsx`
- `src/modules/self-service/pages/SelfServiceProfilePage.test.tsx`
- `src/shared/regionalization/formatters.test.ts`

## 11.3 Current test notes

Useful current observations:

- `AppRoutes.test.tsx` now covers redirect-to-sign-in behavior for protected live routes with no session.
- There is backend coverage for access administration, but there are not yet dedicated frontend test files for:
  - `LoginPage`
  - `ResetPasswordPage`
  - `AccessAdminPage`
  - `AppSessionGate`
- `apps/web/src/app/pages/AccessContractPage.tsx` and its test still exist as legacy artifacts even though `/access` now routes to `AccessAdminPage`.

These are documentation-worthy notes because they affect how we talk about the implementation truth today.

## 12. Seeded accounts and live smoke testing

Reference runbook:

- `docs/runbooks/seeded-access-credentials.md`

Current deterministic local seeded roles include:

- `platform.super_admin`
- `platform.support`
- `platform.auditor`
- `tenant.admin`
- `hr.admin`
- `manager`
- `it.admin`
- `learning.admin`
- `recruiter`
- `interviewer`
- `employee`

Current default local password:

- `Password@12345`

Recommended smoke accounts:

- `admin@phoenixhrms.test`
  - broadest surface
- `tenant.admin@phoenixhrms.test`
  - strong tenant-wide operational coverage
- `manager@phoenixhrms.test`
  - manager visibility and approvals
- `employee@phoenixhrms.test`
  - self-service validation

## 12.1 Recommended live smoke order

1. Seed the local database.
2. Sign in as `admin@phoenixhrms.test`.
3. Verify Foundation and Access.
4. Verify Organization and Employees.
5. Verify Attendance, Leave, and Payroll.
6. Verify Recruitment, Performance, and Learning.
7. Verify Operations, Reporting, and Assistant.
8. Switch to manager and employee accounts for scoped visibility checks.

## 13. Quality commands

## 13.1 API

From `apps/api`:

```bash
composer lint
composer analyse
composer test
composer ci
```

## 13.2 Web

From `apps/web`:

```bash
npm run typecheck
npm run lint
npm run test:run
npm run build
npm run quality
```

## 14. Change checklist for future product changes

Whenever product behavior changes, use this checklist.

### 14.1 If a new routed page is added

Update:

- `apps/web/src/app/routes/AppRoutes.tsx`
- `apps/web/src/app/shell/navigation.ts` if visible in nav
- `docs/08-software-bible.md`
- `docs/09-technical-guide.md`
- relevant frontend tests

### 14.2 If auth or access behavior changes

Update:

- auth API routes
- `LoginPage`
- `ResetPasswordPage`
- `AppSessionGate`
- `RouteGuard`
- access documentation in both guides
- seeded-credentials or runbook docs if required

### 14.3 If a new backend endpoint is added

Update:

- `apps/api/routes/api.php`
- relevant module controllers, requests, resources, and services
- OpenAPI contract file
- backend feature tests
- technical guide API section

### 14.4 If a module changes meaning from a business point of view

Update:

- `docs/08-software-bible.md` first
- `docs/modules/<module>.md`
- `docs/09-technical-guide.md`

### 14.5 If a feature is contract-ready but not fully surfaced in the web app

Document it explicitly.

Do not let readers guess whether a feature is:

- fully live
- backend-only
- embedded in another workspace
- planned but not yet surfaced

## 15. Final technical orientation

If you only remember five technical truths about this codebase, remember these:

1. The current web app now has first-class auth pages and a real session gate in front of protected workspaces.
2. `/access` is now an access-administration workspace, not only a visibility explainer.
3. Access state is split between guided demo sessions and authenticated live sessions, with demo availability controlled by environment.
4. The backend is strongly domain-organized and the published OpenAPI contract inventory maps well to the sprint delivery shape.
5. The most reliable way to verify a product claim is to triangulate `AppRoutes.tsx`, `api.php`, the relevant feature tests, and the matching OpenAPI contract file.
