import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'

export type EmployeeWorkspaceSectionId = 'overview' | 'directory' | 'lifecycleWatch' | 'onboarding' | 'documents' | 'audit'

export interface EmployeeWorkspaceSection {
  id: EmployeeWorkspaceSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export const employeeWorkspaceSectionNavigation: EmployeeWorkspaceSection[] = [
  {
    id: 'overview',
    label: 'Overview',
    to: '/employees/overview',
    description: 'Monitor workforce health, onboarding risk, document posture, and recent employee activity.',
    requiredPermissions: ['employee.view', 'employee.manage'],
    match: 'any',
  },
  {
    id: 'directory',
    label: 'Directory',
    to: '/employees/directory',
    description: 'Search the roster, open employee detail workspaces, and move into profile operations.',
    requiredPermissions: ['employee.view', 'employee.manage'],
    match: 'any',
  },
  {
    id: 'lifecycleWatch',
    label: 'Lifecycle watch',
    to: '/employees/lifecycle-watch',
    description: 'Track probation, notice period, inactive, and terminated records that need attention.',
    requiredPermissions: ['employee.view', 'employee.manage'],
    match: 'any',
  },
  {
    id: 'onboarding',
    label: 'Onboarding',
    to: '/employees/onboarding',
    description: 'Review onboarding completion, pending tasks, and move straight into employee onboarding detail.',
    requiredPermissions: ['employee.view', 'employee.manage'],
    match: 'any',
  },
  {
    id: 'documents',
    label: 'Documents',
    to: '/employees/documents',
    description: 'Inspect document coverage and open protected employee document workspaces.',
    requiredPermissions: ['employee.view', 'employee.manage'],
    match: 'any',
  },
  {
    id: 'audit',
    label: 'Audit',
    to: '/employees/audit',
    description: 'Inspect protected audit activity across employee records, documents, lifecycle, and onboarding.',
    requiredPermissions: ['audit.view', 'employee.view', 'employee.manage'],
    match: 'any',
  },
]

export function getVisibleEmployeeWorkspaceSections(grantedPermissions: string[]) {
  return employeeWorkspaceSectionNavigation.filter((section) =>
    hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
  )
}

export function getDefaultEmployeeSectionPath(grantedPermissions: string[]) {
  const visibleSections = getVisibleEmployeeWorkspaceSections(grantedPermissions)

  if (visibleSections.some((section) => section.id === 'overview')) {
    return employeeWorkspaceSectionNavigation.find((section) => section.id === 'overview')?.to ?? '/employees'
  }

  if (visibleSections.some((section) => section.id === 'directory')) {
    return employeeWorkspaceSectionNavigation.find((section) => section.id === 'directory')?.to ?? '/employees'
  }

  return visibleSections[0]?.to ?? '/employees'
}

export function isEmployeeWorkspaceSectionPath(pathname: string, section: EmployeeWorkspaceSection) {
  return pathname === section.to || pathname.startsWith(`${section.to}/`)
}

export type EmployeeDetailSectionId = 'profile' | 'lifecycle' | 'onboarding' | 'documents' | 'history'

export interface EmployeeDetailSection {
  id: EmployeeDetailSectionId
  label: string
  segment: string
  description: string
}

export const employeeDetailSectionNavigation: EmployeeDetailSection[] = [
  {
    id: 'profile',
    label: 'Profile',
    segment: 'profile',
    description: 'Review core identity, contact channels, addresses, and protected banking context.',
  },
  {
    id: 'lifecycle',
    label: 'Lifecycle',
    segment: 'lifecycle',
    description: 'Manage transfers, promotions, and terminations from one routed lifecycle surface.',
  },
  {
    id: 'onboarding',
    label: 'Onboarding',
    segment: 'onboarding',
    description: 'Track onboarding checklist progress, assignment, and task updates.',
  },
  {
    id: 'documents',
    label: 'Documents',
    segment: 'documents',
    description: 'Review document records, expiry posture, and protected upload controls.',
  },
  {
    id: 'history',
    label: 'Audit history',
    segment: 'history',
    description: 'Inspect protected audit events across profile, lifecycle, onboarding, and documents.',
  },
]

export function getVisibleEmployeeDetailSections(canViewAudit: boolean) {
  return employeeDetailSectionNavigation.filter((section) => canViewAudit || section.id !== 'history')
}

export function getEmployeeDetailSectionPath(
  employeeId: string | number,
  sectionId: EmployeeDetailSectionId,
) {
  const section =
    employeeDetailSectionNavigation.find((entry) => entry.id === sectionId) ??
    employeeDetailSectionNavigation[0]

  return `/employees/${employeeId}/${section.segment}`
}

export function isEmployeeDetailSectionPath(pathname: string, section: EmployeeDetailSection) {
  const match = pathname.match(/^\/employees\/([^/]+)\/([^/]+)/)

  return match?.[2] === section.segment
}

export function matchEmployeeDetailSection(pathname: string) {
  const match = pathname.match(/^\/employees\/([^/]+)\/([^/]+)/)

  if (!match) {
    return null
  }

  const [, employeeId, segment] = match
  const section = employeeDetailSectionNavigation.find((entry) => entry.segment === segment)

  if (!section) {
    return null
  }

  return {
    ...section,
    to: getEmployeeDetailSectionPath(employeeId, section.id),
  }
}
