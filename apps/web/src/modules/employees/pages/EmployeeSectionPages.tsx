import { EmployeeDirectoryWorkspace } from '../components/EmployeeDirectoryWorkspace'
import { EmployeesOverviewPage } from './EmployeesOverviewPage'

export function EmployeeOverviewPage() {
  return <EmployeesOverviewPage />
}

export function EmployeeDirectorySectionPage() {
  return <EmployeeDirectoryWorkspace view="directory" />
}

export function EmployeeLifecycleWatchPage() {
  return <EmployeeDirectoryWorkspace view="lifecycle" />
}

export function EmployeeOnboardingPage() {
  return <EmployeeDirectoryWorkspace view="onboarding" />
}

export function EmployeeDocumentsPage() {
  return <EmployeeDirectoryWorkspace view="documents" />
}

export function EmployeeAuditPage() {
  return <EmployeeDirectoryWorkspace view="audit" />
}
