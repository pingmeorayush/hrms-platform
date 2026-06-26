import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'

export type PerformanceWorkspaceSectionId = 'overview' | 'goals' | 'cycles' | 'reviews'

export interface PerformanceWorkspaceSection {
  id: PerformanceWorkspaceSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export const performanceSectionNavigation: PerformanceWorkspaceSection[] = [
  {
    id: 'overview',
    label: 'Overview',
    to: '/performance/overview',
    description: 'Track cycle posture, pending review work, calibration pressure, and employee goal delivery in one talent view.',
    requiredPermissions: ['performance.view', 'performance.manage', 'performance.review', 'performance.calibrate'],
    match: 'any',
  },
  {
    id: 'goals',
    label: 'Goals',
    to: '/performance/goals',
    description: 'Review assigned goals, weight balance, due dates, and goal-library configuration.',
    requiredPermissions: ['performance.view', 'performance.manage', 'performance.review', 'performance.calibrate'],
    match: 'any',
  },
  {
    id: 'cycles',
    label: 'Cycles',
    to: '/performance/cycles',
    description: 'Inspect competencies, review templates, participant rules, and cycle timing posture.',
    requiredPermissions: ['performance.view', 'performance.manage', 'performance.review', 'performance.calibrate'],
    match: 'any',
  },
  {
    id: 'reviews',
    label: 'Reviews',
    to: '/performance/reviews',
    description: 'Complete self and manager reviews, monitor calibration, and inspect final published performance posture.',
    requiredPermissions: ['performance.view', 'performance.manage', 'performance.review', 'performance.calibrate'],
    match: 'any',
  },
]

export function getVisiblePerformanceSections(grantedPermissions: string[]) {
  return performanceSectionNavigation.filter((section) =>
    hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
  )
}

export function getDefaultPerformanceSectionPath(grantedPermissions: string[]) {
  const visibleSections = getVisiblePerformanceSections(grantedPermissions)

  if (visibleSections.some((section) => section.id === 'overview')) {
    return performanceSectionNavigation.find((section) => section.id === 'overview')?.to ?? '/performance'
  }

  if (visibleSections.some((section) => section.id === 'goals')) {
    return performanceSectionNavigation.find((section) => section.id === 'goals')?.to ?? '/performance'
  }

  if (visibleSections.some((section) => section.id === 'reviews')) {
    return performanceSectionNavigation.find((section) => section.id === 'reviews')?.to ?? '/performance'
  }

  return '/performance'
}
