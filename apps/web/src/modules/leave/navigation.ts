import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'

export type LeaveWorkspaceSectionId = 'overview' | 'requests' | 'approvals' | 'policyAdmin'

export interface LeaveWorkspaceSection {
  id: LeaveWorkspaceSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export const leaveSectionNavigation: LeaveWorkspaceSection[] = [
  {
    id: 'overview',
    label: 'Overview',
    to: '/leave/overview',
    description: 'Monitor approval pressure, upcoming leave windows, policy posture, and recent leave activity.',
    requiredPermissions: ['leave.approve', 'leave.manage_policy', 'leave.manage_balance', 'leave.manage_accrual', 'employee.manage'],
    match: 'any',
  },
  {
    id: 'requests',
    label: 'Requests',
    to: '/leave/requests',
    description: 'Review balances, submit leave, and track the personal request history.',
    requiredPermissions: [
      'leave.view',
      'leave.request',
      'leave.approve',
      'leave.manage_balance',
      'employee.manage',
    ],
    match: 'any',
  },
  {
    id: 'approvals',
    label: 'Approvals',
    to: '/leave/approvals',
    description: 'Inspect the pending approval queue, coverage context, and the manager review backlog.',
    requiredPermissions: ['leave.approve', 'employee.manage'],
    match: 'any',
  },
  {
    id: 'policyAdmin',
    label: 'Policy admin',
    to: '/leave/policy-admin',
    description: 'Manage leave types, accrual rules, and the organization leave calendar.',
    requiredPermissions: [
      'leave.manage_policy',
      'leave.manage_balance',
      'leave.manage_accrual',
      'employee.manage',
    ],
    match: 'any',
  },
]

export function getVisibleLeaveSections(grantedPermissions: string[]) {
  return leaveSectionNavigation.filter((section) =>
    hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
  )
}

export function getDefaultLeaveSectionPath(grantedPermissions: string[]) {
  const visibleSections = getVisibleLeaveSections(grantedPermissions)

  if (visibleSections.some((section) => section.id === 'overview')) {
    return leaveSectionNavigation.find((section) => section.id === 'overview')?.to ?? '/leave'
  }

  if (visibleSections.some((section) => section.id === 'policyAdmin')) {
    return leaveSectionNavigation.find((section) => section.id === 'policyAdmin')?.to ?? '/leave'
  }

  if (visibleSections.some((section) => section.id === 'approvals')) {
    return leaveSectionNavigation.find((section) => section.id === 'approvals')?.to ?? '/leave'
  }

  if (visibleSections.some((section) => section.id === 'requests')) {
    return leaveSectionNavigation.find((section) => section.id === 'requests')?.to ?? '/leave'
  }

  return '/leave'
}

export function isLeaveSectionPath(pathname: string, section: LeaveWorkspaceSection) {
  return pathname === section.to || pathname.startsWith(`${section.to}/`)
}
