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
