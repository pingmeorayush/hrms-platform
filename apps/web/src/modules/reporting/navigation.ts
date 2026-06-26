import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'
import { reportingSectionDefinitions } from './config'

export type ReportingWorkspaceSectionId =
  | 'overview'
  | 'workforce'
  | 'team'
  | 'payroll'
  | 'recruitment'
  | 'executive'

export interface ReportingWorkspaceSection {
  id: ReportingWorkspaceSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export const reportingSectionNavigation: ReportingWorkspaceSection[] = reportingSectionDefinitions.map(
  ({ id, label, to, description, requiredPermissions, match }) => ({
    id,
    label,
    to,
    description,
    requiredPermissions,
    match,
  }),
)

export function getVisibleReportingSections(grantedPermissions: string[]) {
  return reportingSectionNavigation.filter((section) =>
    hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
  )
}

export function getDefaultReportingSectionPath(grantedPermissions: string[]) {
  return getVisibleReportingSections(grantedPermissions)[0]?.to ?? '/reporting/overview'
}
