import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'

export type SelfServiceSectionId = 'profile' | 'documents' | 'assets'

export interface SelfServiceSection {
  id: SelfServiceSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export const selfServiceSectionNavigation: SelfServiceSection[] = [
  {
    id: 'profile',
    label: 'Profile',
    to: '/self-service/profile',
    description: 'Review the linked employee profile, work contacts, addresses, and sensitive-panel posture.',
    requiredPermissions: [],
  },
  {
    id: 'documents',
    label: 'Documents',
    to: '/self-service/documents',
    description: 'Download allowed employee and repository files, then acknowledge assigned policy documents.',
    requiredPermissions: [],
  },
  {
    id: 'assets',
    label: 'Assigned assets',
    to: '/self-service/assets',
    description: 'Track issued devices, handover context, and expected return windows for the linked profile.',
    requiredPermissions: [],
  },
]

export function getVisibleSelfServiceSections(grantedPermissions: string[]) {
  return selfServiceSectionNavigation.filter((section) =>
    hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
  )
}

export function getDefaultSelfServiceSectionPath(grantedPermissions: string[]) {
  const visibleSections = getVisibleSelfServiceSections(grantedPermissions)

  if (visibleSections.some((section) => section.id === 'profile')) {
    return selfServiceSectionNavigation.find((section) => section.id === 'profile')?.to ?? '/self-service'
  }

  if (visibleSections.some((section) => section.id === 'documents')) {
    return selfServiceSectionNavigation.find((section) => section.id === 'documents')?.to ?? '/self-service'
  }

  if (visibleSections.some((section) => section.id === 'assets')) {
    return selfServiceSectionNavigation.find((section) => section.id === 'assets')?.to ?? '/self-service'
  }

  return '/self-service'
}

export function isSelfServiceSectionPath(pathname: string, section: SelfServiceSection) {
  return pathname === section.to || pathname.startsWith(`${section.to}/`)
}
