import { Navigate, Route, Routes } from 'react-router-dom'
import { FoundationOverviewPage } from '../pages/FoundationOverviewPage'
import { AccessContractPage } from '../pages/AccessContractPage'
import { AppShell } from '../shell/AppShell'
import { RouteGuard } from './RouteGuard'
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
import { OperationsAdminPage } from '../../modules/operations/pages/OperationsAdminPage'
import { OperationsAssetsPage } from '../../modules/operations/pages/OperationsAssetsPage'
import { OperationsDocumentsPage } from '../../modules/operations/pages/OperationsDocumentsPage'
import { OperationsIndexRedirect } from '../../modules/operations/pages/OperationsPage'
import { OperationsLifecyclePage } from '../../modules/operations/pages/OperationsLifecyclePage'
import { OperationsOverviewPage } from '../../modules/operations/pages/OperationsOverviewPage'
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
      <Route element={<AppShell />}>
        <Route index element={<Navigate replace to="/foundation" />} />
        <Route path="/foundation" element={<FoundationOverviewPage />} />
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
          path="/operations"
          element={
            <RouteGuard
              permissions={['document.view', 'document.manage', 'asset.view', 'asset.manage', 'employee.manage']}
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
                permissions={['document.view', 'document.manage', 'asset.view', 'asset.manage', 'employee.manage']}
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
              permissions={['auth.manage_roles', 'auth.manage_permissions']}
              match="any"
              title="Access contract unavailable"
              description="This governance route is limited to roles that can manage access controls."
            >
              <AccessContractPage />
            </RouteGuard>
          }
        />
        <Route path="*" element={<Navigate replace to="/foundation" />} />
      </Route>
    </Routes>
  )
}
