import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'

export type OrganizationWorkspaceSectionId = 'overview' | 'companyProfile' | 'structure' | 'locations' | 'costCenters'

export interface OrganizationWorkspaceSection {
  id: OrganizationWorkspaceSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export const organizationSectionNavigation: OrganizationWorkspaceSection[] = [
  {
    id: 'overview',
    label: 'Overview',
    to: '/admin/organization/overview',
    description: 'Monitor structure health, location coverage, and master-data posture from one operations center.',
    requiredPermissions: ['organization.view', 'organization.manage'],
    match: 'any',
  },
  {
    id: 'companyProfile',
    label: 'Company profile',
    to: '/admin/organization/company-profile',
    description: 'Review and update the tenant identity, plan, timezone, and currency defaults.',
    requiredPermissions: ['organization.view', 'organization.manage'],
    match: 'any',
  },
  {
    id: 'structure',
    label: 'Structure',
    to: '/admin/organization/structure',
    description: 'Maintain departments and designations used throughout employee and attendance workflows.',
    requiredPermissions: ['organization.view', 'organization.manage'],
    match: 'any',
  },
  {
    id: 'locations',
    label: 'Locations',
    to: '/admin/organization/locations',
    description: 'Manage reusable site, timezone, and address records for the current tenant.',
    requiredPermissions: ['organization.view', 'organization.manage'],
    match: 'any',
  },
  {
    id: 'costCenters',
    label: 'Cost centers',
    to: '/admin/organization/cost-centers',
    description: 'Administer cost center records that downstream HR workflows can inherit.',
    requiredPermissions: ['organization.view', 'organization.manage'],
    match: 'any',
  },
]

export function getVisibleOrganizationSections(grantedPermissions: string[]) {
  return organizationSectionNavigation.filter((section) =>
    hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
  )
}

export function getDefaultOrganizationSectionPath(grantedPermissions: string[]) {
  const visibleSections = getVisibleOrganizationSections(grantedPermissions)

  if (visibleSections.some((section) => section.id === 'overview')) {
    return (
      organizationSectionNavigation.find((section) => section.id === 'overview')?.to ??
      '/admin/organization'
    )
  }

  if (visibleSections.some((section) => section.id === 'companyProfile')) {
    return (
      organizationSectionNavigation.find((section) => section.id === 'companyProfile')?.to ??
      '/admin/organization'
    )
  }

  if (visibleSections.some((section) => section.id === 'structure')) {
    return organizationSectionNavigation.find((section) => section.id === 'structure')?.to ?? '/admin/organization'
  }

  if (visibleSections.some((section) => section.id === 'locations')) {
    return organizationSectionNavigation.find((section) => section.id === 'locations')?.to ?? '/admin/organization'
  }

  if (visibleSections.some((section) => section.id === 'costCenters')) {
    return (
      organizationSectionNavigation.find((section) => section.id === 'costCenters')?.to ??
      '/admin/organization'
    )
  }

  return '/admin/organization'
}

export function isOrganizationSectionPath(pathname: string, section: OrganizationWorkspaceSection) {
  return pathname === section.to || pathname.startsWith(`${section.to}/`)
}
