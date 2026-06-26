import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'

export type LearningWorkspaceSectionId = 'overview' | 'catalog' | 'assignments' | 'myLearning'

export interface LearningWorkspaceSection {
  id: LearningWorkspaceSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export const learningSectionNavigation: LearningWorkspaceSection[] = [
  {
    id: 'overview',
    label: 'Overview',
    to: '/learning/overview',
    description: 'Track compliance posture, assignment pressure, renewal exposure, and completion evidence visibility from one learning view.',
    requiredPermissions: ['learning.view', 'learning.manage', 'learning.assign'],
    match: 'any',
  },
  {
    id: 'catalog',
    label: 'Catalog',
    to: '/learning/catalog',
    description: 'Manage learning items, delivery modes, evidence requirements, and renewal rules from the routed catalog studio.',
    requiredPermissions: ['learning.manage', 'learning.assign'],
    match: 'any',
  },
  {
    id: 'assignments',
    label: 'Assignments',
    to: '/learning/assignments',
    description: 'Review assignment targeting, due-date pressure, and employee-level compliance state from one admin queue.',
    requiredPermissions: ['learning.manage', 'learning.assign'],
    match: 'any',
  },
  {
    id: 'myLearning',
    label: 'My learning',
    to: '/learning/my-learning',
    description: 'Review assigned learning, overdue items, renewal posture, and evidence-backed completions for the linked employee profile.',
    requiredPermissions: ['learning.view', 'learning.complete', 'learning.manage'],
    match: 'any',
  },
]

export function getVisibleLearningSections(grantedPermissions: string[]) {
  return learningSectionNavigation.filter((section) =>
    hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
  )
}

export function getDefaultLearningSectionPath(grantedPermissions: string[]) {
  const visibleSections = getVisibleLearningSections(grantedPermissions)
  const canManageOrAssign = ['learning.manage', 'learning.assign'].some((permission) => grantedPermissions.includes(permission))
  const learnerFocusedSession = grantedPermissions.includes('learning.complete') && !canManageOrAssign

  if (learnerFocusedSession && visibleSections.some((section) => section.id === 'myLearning')) {
    return learningSectionNavigation.find((section) => section.id === 'myLearning')?.to ?? '/learning'
  }

  if (visibleSections.some((section) => section.id === 'overview')) {
    return learningSectionNavigation.find((section) => section.id === 'overview')?.to ?? '/learning'
  }

  if (visibleSections.some((section) => section.id === 'catalog')) {
    return learningSectionNavigation.find((section) => section.id === 'catalog')?.to ?? '/learning'
  }

  if (visibleSections.some((section) => section.id === 'assignments')) {
    return learningSectionNavigation.find((section) => section.id === 'assignments')?.to ?? '/learning'
  }

  if (visibleSections.some((section) => section.id === 'myLearning')) {
    return learningSectionNavigation.find((section) => section.id === 'myLearning')?.to ?? '/learning'
  }

  return '/learning'
}

export function isLearningSectionPath(pathname: string, section: LearningWorkspaceSection) {
  return pathname === section.to || pathname.startsWith(`${section.to}/`)
}
