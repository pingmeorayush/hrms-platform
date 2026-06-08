import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'

export type AttendanceWorkspaceSectionId = 'overview' | 'selfService' | 'operationalReview' | 'adminSetup'

export interface AttendanceWorkspaceSection {
  id: AttendanceWorkspaceSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export const attendanceSectionNavigation: AttendanceWorkspaceSection[] = [
  {
    id: 'overview',
    label: 'Overview',
    to: '/attendance/overview',
    description: 'Monitor live attendance operations, expiring setup, and review pressure from one command center.',
    requiredPermissions: ['attendance.approve', 'attendance.edit', 'attendance.manage_shift', 'attendance.manage_roster'],
    match: 'any',
  },
  {
    id: 'selfService',
    label: 'My attendance',
    to: '/attendance/my-attendance',
    description: 'Check in, check out, review personal history, and start correction requests.',
    requiredPermissions: ['attendance.view', 'attendance.create', 'attendance.correct', 'attendance.approve'],
    match: 'any',
  },
  {
    id: 'operationalReview',
    label: 'Operational review',
    to: '/attendance/operational-review',
    description: 'Inspect exception queues, review correction decisions, and work through scoped operational dashboards.',
    requiredPermissions: ['attendance.approve'],
    match: 'any',
  },
  {
    id: 'adminSetup',
    label: 'Admin setup',
    to: '/attendance/admin-setup',
    description: 'Manage policy, holiday calendars, shifts, assignments, and roster configuration.',
    requiredPermissions: ['attendance.edit', 'attendance.manage_shift', 'attendance.manage_roster'],
    match: 'any',
  },
]

export function getVisibleAttendanceSections(grantedPermissions: string[]) {
  return attendanceSectionNavigation.filter((section) =>
    hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
  )
}

export function getDefaultAttendanceSectionPath(grantedPermissions: string[]) {
  const visibleSections = getVisibleAttendanceSections(grantedPermissions)

  if (visibleSections.some((section) => section.id === 'overview')) {
    return attendanceSectionNavigation.find((section) => section.id === 'overview')?.to ?? '/attendance'
  }

  if (visibleSections.some((section) => section.id === 'adminSetup')) {
    return `${attendanceSectionNavigation.find((section) => section.id === 'adminSetup')?.to ?? '/attendance'}/policy`
  }

  if (visibleSections.some((section) => section.id === 'operationalReview')) {
    return attendanceSectionNavigation.find((section) => section.id === 'operationalReview')?.to ?? '/attendance'
  }

  if (visibleSections.some((section) => section.id === 'selfService')) {
    return attendanceSectionNavigation.find((section) => section.id === 'selfService')?.to ?? '/attendance'
  }

  return '/attendance'
}

export function isAttendanceSectionPath(pathname: string, section: AttendanceWorkspaceSection) {
  return pathname === section.to || pathname.startsWith(`${section.to}/`)
}
