import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'

export type RecruitmentWorkspaceSectionId = 'overview' | 'requisitions' | 'candidates'

export interface RecruitmentWorkspaceSection {
  id: RecruitmentWorkspaceSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export const recruitmentSectionNavigation: RecruitmentWorkspaceSection[] = [
  {
    id: 'overview',
    label: 'Overview',
    to: '/recruitment/overview',
    description: 'Track hiring demand, pipeline health, approvals, and accepted-offer handoff pressure from one control view.',
    requiredPermissions: ['recruitment.view', 'recruitment.manage', 'recruitment.approve'],
    match: 'any',
  },
  {
    id: 'requisitions',
    label: 'Requisitions',
    to: '/recruitment/requisitions',
    description: 'Review hiring requests, workflow posture, headcount context, and approval actions.',
    requiredPermissions: ['recruitment.view', 'recruitment.manage', 'recruitment.approve'],
    match: 'any',
  },
  {
    id: 'candidates',
    label: 'Candidates',
    to: '/recruitment/candidates',
    description: 'Work the pipeline board, candidate details, interviews, offers, and onboarding handoff states.',
    requiredPermissions: ['recruitment.view', 'recruitment.manage', 'recruitment.approve'],
    match: 'any',
  },
]

export function getVisibleRecruitmentSections(grantedPermissions: string[]) {
  return recruitmentSectionNavigation.filter((section) =>
    hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
  )
}

export function getDefaultRecruitmentSectionPath(grantedPermissions: string[]) {
  const visibleSections = getVisibleRecruitmentSections(grantedPermissions)

  if (visibleSections.some((section) => section.id === 'overview')) {
    return recruitmentSectionNavigation.find((section) => section.id === 'overview')?.to ?? '/recruitment'
  }

  if (visibleSections.some((section) => section.id === 'requisitions')) {
    return recruitmentSectionNavigation.find((section) => section.id === 'requisitions')?.to ?? '/recruitment'
  }

  if (visibleSections.some((section) => section.id === 'candidates')) {
    return recruitmentSectionNavigation.find((section) => section.id === 'candidates')?.to ?? '/recruitment'
  }

  return '/recruitment'
}
