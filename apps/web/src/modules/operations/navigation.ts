import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'

export type OperationsSectionId = 'overview' | 'documents' | 'assets' | 'lifecycle'

export interface OperationsSection {
  id: OperationsSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export const operationsSectionNavigation: OperationsSection[] = [
  {
    id: 'overview',
    label: 'Overview',
    to: '/operations/overview',
    description: 'Watch document posture, asset handoffs, and lifecycle risk from one HR and IT command surface.',
    requiredPermissions: ['document.view', 'document.manage', 'asset.view', 'asset.manage', 'employee.manage'],
    match: 'any',
  },
  {
    id: 'documents',
    label: 'Documents',
    to: '/operations/documents',
    description: 'Manage repository categories, retention defaults, and document governance posture.',
    requiredPermissions: ['document.view', 'document.manage'],
    match: 'any',
  },
  {
    id: 'assets',
    label: 'Assets',
    to: '/operations/assets',
    description: 'Track assignment, issue, return, and blocked asset states from one operations queue.',
    requiredPermissions: ['asset.view', 'asset.manage'],
    match: 'any',
  },
  {
    id: 'lifecycle',
    label: 'Lifecycle',
    to: '/operations/lifecycle',
    description: 'Review onboarding and offboarding progress, then update employee task completion safely.',
    requiredPermissions: ['employee.manage'],
    match: 'any',
  },
]

export function getVisibleOperationsSections(grantedPermissions: string[]) {
  return operationsSectionNavigation.filter((section) =>
    hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
  )
}

export function getDefaultOperationsSectionPath(grantedPermissions: string[]) {
  const visibleSections = getVisibleOperationsSections(grantedPermissions)

  if (visibleSections.some((section) => section.id === 'overview')) {
    return operationsSectionNavigation.find((section) => section.id === 'overview')?.to ?? '/operations'
  }

  if (visibleSections.some((section) => section.id === 'assets')) {
    return operationsSectionNavigation.find((section) => section.id === 'assets')?.to ?? '/operations'
  }

  if (visibleSections.some((section) => section.id === 'documents')) {
    return operationsSectionNavigation.find((section) => section.id === 'documents')?.to ?? '/operations'
  }

  if (visibleSections.some((section) => section.id === 'lifecycle')) {
    return operationsSectionNavigation.find((section) => section.id === 'lifecycle')?.to ?? '/operations'
  }

  return '/operations'
}

export function isOperationsSectionPath(pathname: string, section: OperationsSection) {
  return pathname === section.to || pathname.startsWith(`${section.to}/`)
}
