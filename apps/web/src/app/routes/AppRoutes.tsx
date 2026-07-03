import { Navigate, Route, Routes } from 'react-router-dom'
import { FoundationOverviewPage } from '../pages/FoundationOverviewPage'
import { AppShell } from '../shell/AppShell'
import { AppSessionGate } from './AppSessionGate'
import { RouteGuard } from './RouteGuard'
import { AssistantPage } from '../../modules/assistant/pages/AssistantPage'
import { LoginPage } from '../../modules/access/pages/LoginPage'
import { ResetPasswordPage } from '../../modules/access/pages/ResetPasswordPage'
import { AccessAdminPage } from '../../modules/access/pages/AccessAdminPage'
import { OrganizationAdminPage, OrganizationIndexRedirect } from '../../modules/organization/pages/OrganizationAdminPage'
import { EmployeeAdminPage, EmployeeIndexRedirect } from '../../modules/employees/pages/EmployeeAdminPage'
import { EmployeeDetailPage } from '../../modules/employees/pages/EmployeeDetailPage'
import {
  EmployeeDetailIndexRedirect,
  EmployeeDocumentsRouteSection,
  EmployeeHistoryRouteSection,
  EmployeeLifecycleRouteSection,
  EmployeeOnboardingRouteSection,
  EmployeeProfileRouteSection,
} from '../../modules/employees/components/EmployeeDetailShell'
import {
  EmployeeAuditPage,
  EmployeeDirectorySectionPage,
  EmployeeDocumentsPage,
  EmployeeLifecycleWatchPage,
  EmployeeOverviewPage,
  EmployeeOnboardingPage,
} from '../../modules/employees/pages/EmployeeSectionPages'
import { AttendanceAdminPage } from '../../modules/attendance/pages/AttendanceAdminPage'
import { AttendanceIndexRedirect } from '../../modules/attendance/pages/AttendancePage'
import { AttendanceOverviewPage } from '../../modules/attendance/pages/AttendanceOverviewPage'
import { AttendanceSelfServicePage } from '../../modules/attendance/pages/AttendanceSelfServicePage'
import { AttendanceOperationalReviewPage } from '../../modules/attendance/pages/AttendanceOperationalReviewPage'
import { AttendanceAdminSetupPage } from '../../modules/attendance/pages/AttendanceAdminSetupPage'
import { LeaveAdminPage } from '../../modules/leave/pages/LeaveAdminPage'
import { LeaveIndexRedirect } from '../../modules/leave/pages/LeavePage'
import { LeaveOverviewPage } from '../../modules/leave/pages/LeaveOverviewPage'
import { LeaveRequestsPage } from '../../modules/leave/pages/LeaveRequestsPage'
import { LeaveApprovalsPage } from '../../modules/leave/pages/LeaveApprovalsPage'
import { LeavePolicyAdminPage } from '../../modules/leave/pages/LeavePolicyAdminPage'
import { PayrollAdminPage } from '../../modules/payroll/pages/PayrollAdminPage'
import { PayrollIndexRedirect } from '../../modules/payroll/pages/PayrollPage'
import { PayrollOverviewPage } from '../../modules/payroll/pages/PayrollOverviewPage'
import { PayrollSetupPage } from '../../modules/payroll/pages/PayrollSetupPage'
import { PayrollReviewPage } from '../../modules/payroll/pages/PayrollReviewPage'
import { PayrollRunConsolePage } from '../../modules/payroll/pages/PayrollRunConsolePage'
import { PayrollSelfServicePage } from '../../modules/payroll/pages/PayrollSelfServicePage'
import { RecruitmentAdminPage } from '../../modules/recruitment/pages/RecruitmentAdminPage'
import { RecruitmentCandidateDetailPage } from '../../modules/recruitment/pages/RecruitmentCandidateDetailPage'
import { RecruitmentCandidatesPage } from '../../modules/recruitment/pages/RecruitmentCandidatesPage'
import { RecruitmentIndexRedirect } from '../../modules/recruitment/pages/RecruitmentPage'
import { RecruitmentOverviewPage } from '../../modules/recruitment/pages/RecruitmentOverviewPage'
import { RecruitmentRequisitionsPage } from '../../modules/recruitment/pages/RecruitmentRequisitionsPage'
import { PerformanceAdminPage } from '../../modules/performance/pages/PerformanceAdminPage'
import { PerformanceCyclesPage } from '../../modules/performance/pages/PerformanceCyclesPage'
import { PerformanceGoalsPage } from '../../modules/performance/pages/PerformanceGoalsPage'
import { PerformanceIndexRedirect } from '../../modules/performance/pages/PerformancePage'
import { PerformanceOverviewPage } from '../../modules/performance/pages/PerformanceOverviewPage'
import { PerformanceReviewsPage } from '../../modules/performance/pages/PerformanceReviewsPage'
import { LearningAdminPage } from '../../modules/learning/pages/LearningAdminPage'
import { LearningAssignmentsPage } from '../../modules/learning/pages/LearningAssignmentsPage'
import { LearningCatalogPage } from '../../modules/learning/pages/LearningCatalogPage'
import { LearningIndexRedirect } from '../../modules/learning/pages/LearningPage'
import { LearningMyLearningPage } from '../../modules/learning/pages/LearningMyLearningPage'
import { LearningOverviewPage } from '../../modules/learning/pages/LearningOverviewPage'
import { OperationsAdminPage } from '../../modules/operations/pages/OperationsAdminPage'
import { OperationsAssetsPage } from '../../modules/operations/pages/OperationsAssetsPage'
import { OperationsDocumentsPage } from '../../modules/operations/pages/OperationsDocumentsPage'
import { OperationsIntegrationsPage } from '../../modules/operations/pages/OperationsIntegrationsPage'
import { OperationsIndexRedirect } from '../../modules/operations/pages/OperationsPage'
import { OperationsObservabilityPage } from '../../modules/operations/pages/OperationsObservabilityPage'
import { OperationsReleaseReadinessPage } from '../../modules/operations/pages/OperationsReleaseReadinessPage'
import { OperationsResiliencePage } from '../../modules/operations/pages/OperationsResiliencePage'
import { OperationsReleasePage } from '../../modules/operations/pages/OperationsReleasePage'
import { OperationsLifecyclePage } from '../../modules/operations/pages/OperationsLifecyclePage'
import { OperationsOverviewPage } from '../../modules/operations/pages/OperationsOverviewPage'
import { ReportingAdminPage } from '../../modules/reporting/pages/ReportingAdminPage'
import { ReportingExplorerPage } from '../../modules/reporting/pages/ReportingExplorerPage'
import { ReportingExportsPage } from '../../modules/reporting/pages/ReportingExportsPage'
import {
  ReportingExecutivePage,
  ReportingOverviewPage,
  ReportingPayrollPage,
  ReportingRecruitmentPage,
  ReportingTeamPage,
  ReportingWorkforcePage,
} from '../../modules/reporting/pages/ReportingOverviewPage'
import { ReportingIndexRedirect } from '../../modules/reporting/pages/ReportingPage'
import { ReportingSubscriptionsPage } from '../../modules/reporting/pages/ReportingSubscriptionsPage'
import { SelfServiceAssetsPage } from '../../modules/self-service/pages/SelfServiceAssetsPage'
import { SelfServiceDocumentsPage } from '../../modules/self-service/pages/SelfServiceDocumentsPage'
import { SelfServiceIndexRedirect, SelfServicePage } from '../../modules/self-service/pages/SelfServicePage'
import { SelfServiceProfilePage } from '../../modules/self-service/pages/SelfServiceProfilePage'
import {
  OrganizationOverviewPage,
  OrganizationCompanyProfilePage,
  OrganizationCostCentersPage,
  OrganizationLocationsPage,
  OrganizationStructurePage,
} from '../../modules/organization/pages/OrganizationSectionPages'

export function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/reset-password" element={<ResetPasswordPage />} />
      <Route
        element={
          <AppSessionGate>
            <AppShell />
          </AppSessionGate>
        }
      >
        <Route index element={<Navigate replace to="/foundation" />} />
        <Route path="/foundation" element={<FoundationOverviewPage />} />
        <Route
          path="/assistant"
          element={
            <RouteGuard
              permissions={['ai.view', 'ai.recommend']}
              match="any"
              title="AI assistant unavailable"
              description="This route requires governed AI visibility or recommendation review permissions in the current session."
            >
              <AssistantPage />
            </RouteGuard>
          }
        />
        <Route
          path="/admin/organization"
          element={
            <RouteGuard
              permissions={['organization.view', 'organization.manage']}
              match="any"
              title="Organization workspace unavailable"
              description="This page requires organization visibility or management permissions in the current session."
            >
              <OrganizationAdminPage />
            </RouteGuard>
          }
        >
          <Route index element={<OrganizationIndexRedirect />} />
          <Route path="overview" element={<OrganizationOverviewPage />} />
          <Route path="company-profile" element={<OrganizationCompanyProfilePage />} />
          <Route path="structure" element={<OrganizationStructurePage />} />
          <Route path="locations" element={<OrganizationLocationsPage />} />
          <Route path="cost-centers" element={<OrganizationCostCentersPage />} />
        </Route>
        <Route
          path="/employees"
          element={
            <RouteGuard
              permissions={['employee.view', 'employee.manage']}
              match="any"
              title="Employee workspace unavailable"
              description="This page requires employee directory visibility or management access in the current session."
            >
              <EmployeeAdminPage />
            </RouteGuard>
          }
        >
          <Route index element={<EmployeeIndexRedirect />} />
          <Route path="overview" element={<EmployeeOverviewPage />} />
          <Route path="directory" element={<EmployeeDirectorySectionPage />} />
          <Route path="lifecycle-watch" element={<EmployeeLifecycleWatchPage />} />
          <Route path="onboarding" element={<EmployeeOnboardingPage />} />
          <Route path="documents" element={<EmployeeDocumentsPage />} />
          <Route path="audit" element={<EmployeeAuditPage />} />
        </Route>
        <Route
          path="/employees/:employeeId"
          element={
            <RouteGuard
              permissions={['employee.view', 'employee.manage']}
              match="any"
              title="Employee detail unavailable"
              description="This route requires employee directory visibility or management access in the current session."
            >
              <EmployeeDetailPage />
            </RouteGuard>
          }
        >
          <Route index element={<EmployeeDetailIndexRedirect />} />
          <Route path="profile" element={<EmployeeProfileRouteSection />} />
          <Route path="lifecycle" element={<EmployeeLifecycleRouteSection />} />
          <Route path="onboarding" element={<EmployeeOnboardingRouteSection />} />
          <Route path="documents" element={<EmployeeDocumentsRouteSection />} />
          <Route path="history" element={<EmployeeHistoryRouteSection />} />
        </Route>
        <Route
          path="/attendance"
          element={
            <RouteGuard
              permissions={[
                'attendance.view',
                'attendance.create',
                'attendance.edit',
                'attendance.approve',
                'attendance.manage_shift',
                'attendance.manage_roster',
              ]}
              match="any"
              title="Attendance workspace unavailable"
              description="This route requires attendance setup, review, or scheduling permissions in the current session."
            >
              <AttendanceAdminPage />
            </RouteGuard>
          }
        >
          <Route index element={<AttendanceIndexRedirect />} />
          <Route path="overview" element={<AttendanceOverviewPage />} />
          <Route path="my-attendance/*" element={<AttendanceSelfServicePage />} />
          <Route path="operational-review" element={<AttendanceOperationalReviewPage />} />
          <Route path="admin-setup/*" element={<AttendanceAdminSetupPage />} />
        </Route>
        <Route
          path="/leave"
          element={
            <RouteGuard
              permissions={[
                'leave.view',
                'leave.request',
                'leave.approve',
                'leave.manage_policy',
                'leave.manage_balance',
                'employee.manage',
              ]}
              match="any"
              title="Leave workspace unavailable"
              description="This route requires leave self-service, approval, or policy-management permissions in the current session."
            >
              <LeaveAdminPage />
            </RouteGuard>
          }
        >
          <Route index element={<LeaveIndexRedirect />} />
          <Route path="overview" element={<LeaveOverviewPage />} />
          <Route path="requests" element={<LeaveRequestsPage />} />
          <Route path="approvals" element={<LeaveApprovalsPage />} />
          <Route path="policy-admin" element={<LeavePolicyAdminPage />} />
        </Route>
        <Route
          path="/payroll"
          element={
            <RouteGuard
              permissions={[
                'payroll.view',
                'payroll.process',
                'payroll.approve',
                'payroll.lock',
                'payroll.reopen',
                'salary.manage',
                'compensation.manage',
                'payslip.view',
                'compensation.view',
              ]}
              match="any"
              title="Payroll workspace unavailable"
              description="This route requires payroll controls, payroll setup permissions, or self-service payslip and compensation visibility in the current session."
            >
              <PayrollAdminPage />
            </RouteGuard>
          }
        >
          <Route index element={<PayrollIndexRedirect />} />
          <Route
            path="setup"
            element={
              <RouteGuard
                permissions={['payroll.process', 'salary.manage', 'compensation.manage']}
                match="any"
                title="Payroll setup unavailable"
                description="This route is limited to payroll setup sessions that can manage payroll calendars, salary configuration, or employee compensation."
              >
                <PayrollSetupPage />
              </RouteGuard>
            }
          />
          <Route
            path="overview"
            element={
              <RouteGuard
                permissions={['payroll.view', 'payroll.process', 'payroll.approve', 'payroll.lock', 'payroll.reopen']}
                match="any"
                title="Payroll overview unavailable"
                description="This route is limited to payroll-authorized sessions that can inspect payroll operations."
              >
                <PayrollOverviewPage />
              </RouteGuard>
            }
          />
          <Route
            path="review"
            element={
              <RouteGuard
                permissions={['payroll.view', 'payroll.process', 'payroll.approve', 'payroll.lock', 'payroll.reopen']}
                match="any"
                title="Payroll review unavailable"
                description="This route is limited to payroll-authorized sessions that can inspect payroll summaries, variances, and exceptions."
              >
                <PayrollReviewPage />
              </RouteGuard>
            }
          />
          <Route
            path="run-console"
            element={
              <RouteGuard
                permissions={['payroll.view', 'payroll.process', 'payroll.approve', 'payroll.lock', 'payroll.reopen']}
                match="any"
                title="Payroll run console unavailable"
                description="This route is limited to payroll-authorized sessions that can operate or review payroll runs."
              >
                <PayrollRunConsolePage />
              </RouteGuard>
            }
          />
          <Route path="my-pay" element={<PayrollSelfServicePage />} />
        </Route>
        <Route
          path="/recruitment"
          element={
            <RouteGuard
              permissions={['recruitment.view', 'recruitment.manage', 'recruitment.approve']}
              match="any"
              title="Recruitment workspace unavailable"
              description="This route requires recruiter, hiring-manager, or recruitment approval visibility in the current session."
            >
              <RecruitmentAdminPage />
            </RouteGuard>
          }
        >
          <Route index element={<RecruitmentIndexRedirect />} />
          <Route path="overview" element={<RecruitmentOverviewPage />} />
          <Route path="requisitions" element={<RecruitmentRequisitionsPage />} />
          <Route path="candidates" element={<RecruitmentCandidatesPage />} />
          <Route path="candidates/:candidateId" element={<RecruitmentCandidateDetailPage />} />
        </Route>
        <Route
          path="/performance"
          element={
            <RouteGuard
              permissions={['performance.view', 'performance.manage', 'performance.review', 'performance.calibrate']}
              match="any"
              title="Performance workspace unavailable"
              description="This route requires performance visibility, review, or calibration access in the current session."
            >
              <PerformanceAdminPage />
            </RouteGuard>
          }
        >
          <Route index element={<PerformanceIndexRedirect />} />
          <Route path="overview" element={<PerformanceOverviewPage />} />
          <Route path="goals" element={<PerformanceGoalsPage />} />
          <Route path="cycles" element={<PerformanceCyclesPage />} />
          <Route path="reviews" element={<PerformanceReviewsPage />} />
        </Route>
        <Route
          path="/learning"
          element={
            <RouteGuard
              permissions={['learning.view', 'learning.manage', 'learning.assign', 'learning.complete']}
              match="any"
              title="Learning workspace unavailable"
              description="This route requires learning visibility, assignment operations, or learner completion access in the current session."
            >
              <LearningAdminPage />
            </RouteGuard>
          }
        >
          <Route index element={<LearningIndexRedirect />} />
          <Route
            path="overview"
            element={
              <RouteGuard
                permissions={['learning.view', 'learning.manage', 'learning.assign']}
                match="any"
                title="Learning overview unavailable"
                description="This route is limited to sessions that can review learning posture or administer the learning workspace."
              >
                <LearningOverviewPage />
              </RouteGuard>
            }
          />
          <Route
            path="catalog"
            element={
              <RouteGuard
                permissions={['learning.manage', 'learning.assign']}
                match="any"
                title="Learning catalog unavailable"
                description="This route is limited to learning administrators and assignment operators."
              >
                <LearningCatalogPage />
              </RouteGuard>
            }
          />
          <Route
            path="assignments"
            element={
              <RouteGuard
                permissions={['learning.manage', 'learning.assign']}
                match="any"
                title="Learning assignments unavailable"
                description="This route is limited to sessions that can assign or administer learning work."
              >
                <LearningAssignmentsPage />
              </RouteGuard>
            }
          />
          <Route path="my-learning" element={<LearningMyLearningPage />} />
        </Route>
        <Route
          path="/operations"
          element={
            <RouteGuard
              permissions={['document.view', 'document.manage', 'asset.view', 'asset.manage', 'employee.manage', 'integration.view', 'integration.manage', 'resilience.view', 'resilience.manage', 'release.view', 'release.manage']}
              match="any"
              title="Operations workspace unavailable"
              description="This route requires document governance, asset management, or employee lifecycle operations access in the current session."
            >
              <OperationsAdminPage />
            </RouteGuard>
          }
        >
          <Route index element={<OperationsIndexRedirect />} />
          <Route
            path="overview"
            element={
              <RouteGuard
                permissions={['document.view', 'document.manage', 'asset.view', 'asset.manage', 'employee.manage', 'integration.view', 'integration.manage', 'resilience.view', 'resilience.manage', 'release.view', 'release.manage']}
                match="any"
                title="Operations overview unavailable"
                description="This route is limited to HR and IT sessions that can review operations posture."
              >
                <OperationsOverviewPage />
              </RouteGuard>
            }
          />
          <Route
            path="documents"
            element={
              <RouteGuard
                permissions={['document.view', 'document.manage']}
                match="any"
                title="Document operations unavailable"
                description="This route requires document repository visibility or governance access."
              >
                <OperationsDocumentsPage />
              </RouteGuard>
            }
          />
          <Route
            path="assets"
            element={
              <RouteGuard
                permissions={['asset.view', 'asset.manage']}
                match="any"
                title="Asset operations unavailable"
                description="This route requires asset visibility or asset lifecycle management access."
              >
                <OperationsAssetsPage />
              </RouteGuard>
            }
          />
          <Route
            path="integrations"
            element={
              <RouteGuard
                permissions={['integration.view', 'integration.manage']}
                match="any"
                title="Integration operations unavailable"
                description="This route requires integration visibility or operator retry access in the current session."
              >
                <OperationsIntegrationsPage />
              </RouteGuard>
            }
          />
          <Route
            path="release"
            element={
              <RouteGuard
                permissions={['release.view', 'release.manage']}
                match="any"
                title="Release operations unavailable"
                description="This route requires release-quality visibility or release-operator access in the current session."
              >
                <OperationsReleasePage />
              </RouteGuard>
            }
          />
          <Route
            path="readiness"
            element={
              <RouteGuard
                permissions={['release.view', 'release.manage']}
                match="any"
                title="Launch readiness workspace unavailable"
                description="This route requires release-governance visibility or release-operator access in the current session."
              >
                <OperationsReleaseReadinessPage />
              </RouteGuard>
            }
          />
          <Route
            path="observability"
            element={
              <RouteGuard
                permissions={['observability.view', 'observability.manage']}
                match="any"
                title="Observability workspace unavailable"
                description="This route requires observability visibility or alert-routing operator access in the current session."
              >
                <OperationsObservabilityPage />
              </RouteGuard>
            }
          />
          <Route
            path="resilience"
            element={
              <RouteGuard
                permissions={['resilience.view', 'resilience.manage']}
                match="any"
                title="Resilience workspace unavailable"
                description="This route requires recovery-readiness visibility or resilience-operator access in the current session."
              >
                <OperationsResiliencePage />
              </RouteGuard>
            }
          />
          <Route
            path="lifecycle"
            element={
              <RouteGuard
                permissions={['employee.manage']}
                match="any"
                title="Lifecycle operations unavailable"
                description="This route is limited to employee-management sessions that can coordinate onboarding and offboarding tasks."
              >
                <OperationsLifecyclePage />
              </RouteGuard>
            }
          />
        </Route>
        <Route
          path="/reporting"
          element={
            <RouteGuard
              permissions={['reporting.view', 'reporting.manage', 'reporting.certify', 'reporting.export']}
              match="any"
              title="Reporting workspace unavailable"
              description="This route requires governed reporting visibility, certification, or reporting administration access in the current session."
            >
              <ReportingAdminPage />
            </RouteGuard>
          }
        >
          <Route index element={<ReportingIndexRedirect />} />
          <Route path="overview" element={<ReportingOverviewPage />} />
          <Route path="explorer" element={<ReportingExplorerPage />} />
          <Route path="exports" element={<ReportingExportsPage />} />
          <Route path="subscriptions" element={<ReportingSubscriptionsPage />} />
          <Route path="workforce" element={<ReportingWorkforcePage />} />
          <Route path="team" element={<ReportingTeamPage />} />
          <Route path="payroll" element={<ReportingPayrollPage />} />
          <Route path="recruitment" element={<ReportingRecruitmentPage />} />
          <Route path="executive" element={<ReportingExecutivePage />} />
        </Route>
        <Route
          path="/self-service"
          element={
            <RouteGuard
              title="Self-service workspace unavailable"
              description="This workspace expects an authenticated session so it can resolve the linked employee profile."
            >
              <SelfServicePage />
            </RouteGuard>
          }
        >
          <Route index element={<SelfServiceIndexRedirect />} />
          <Route path="profile" element={<SelfServiceProfilePage />} />
          <Route path="documents" element={<SelfServiceDocumentsPage />} />
          <Route path="assets" element={<SelfServiceAssetsPage />} />
        </Route>
        <Route
          path="/access"
          element={
            <RouteGuard
              permissions={['auth.manage_roles', 'auth.manage_permissions', 'auth.manage_users']}
              match="any"
              title="Access operations unavailable"
              description="This governance route is limited to sessions that can manage users, roles, or permission visibility."
            >
              <AccessAdminPage />
            </RouteGuard>
          }
        />
        <Route path="*" element={<Navigate replace to="/foundation" />} />
      </Route>
    </Routes>
  )
}
