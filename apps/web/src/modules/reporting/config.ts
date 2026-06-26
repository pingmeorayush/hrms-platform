import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'
import type { ReportingDashboardKey, ReportingSectionId } from './types'

interface ReportingSectionDefinition {
  id: ReportingSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
  dashboardKey?: ReportingDashboardKey
}

export const reportingSectionDefinitions: ReportingSectionDefinition[] = [
  {
    id: 'overview',
    label: 'Overview',
    to: '/reporting/overview',
    description:
      'Cross-domain reporting command center for freshness, blocked widgets, masking posture, and governed dashboard coverage.',
    requiredPermissions: ['reporting.view', 'reporting.manage', 'reporting.certify', 'reporting.export'],
    match: 'any',
  },
  {
    id: 'explorer',
    label: 'Explorer',
    to: '/reporting/explorer',
    description:
      'Governed report explorer for dataset discovery, approved filters, drilldowns, and saved-view consumption.',
    requiredPermissions: ['reporting.view', 'reporting.manage', 'reporting.certify', 'reporting.export'],
    match: 'any',
  },
  {
    id: 'exports',
    label: 'Exports',
    to: '/reporting/exports',
    description:
      'Queue visibility for requested exports, blocked delivery, retention posture, and governed downloads.',
    requiredPermissions: ['reporting.view', 'reporting.manage', 'reporting.certify', 'reporting.export'],
    match: 'any',
  },
  {
    id: 'subscriptions',
    label: 'Subscriptions',
    to: '/reporting/subscriptions',
    description:
      'Recurring report delivery setup with owner scope, saved-view sources, and blocked-state visibility.',
    requiredPermissions: ['reporting.view', 'reporting.manage', 'reporting.certify', 'reporting.export'],
    match: 'any',
  },
  {
    id: 'workforce',
    label: 'Workforce',
    to: '/reporting/workforce',
    description:
      'HR dashboard for workforce, attendance, leave, and recruiting posture sourced from certified datasets.',
    requiredPermissions: ['reporting.manage', 'reporting.certify', 'organization.manage', 'employee.manage', 'reporting.view'],
    match: 'any',
    dashboardKey: 'hr_overview',
  },
  {
    id: 'team',
    label: 'Team',
    to: '/reporting/team',
    description:
      'Manager dashboard for team-scoped headcount, leave, attendance, and review follow-up.',
    requiredPermissions: ['reporting.view', 'attendance.approve', 'leave.approve', 'performance.review'],
    match: 'all',
    dashboardKey: 'manager_overview',
  },
  {
    id: 'payroll',
    label: 'Payroll',
    to: '/reporting/payroll',
    description:
      'Payroll dashboard for run-state visibility, blocked processing, and release readiness.',
    requiredPermissions: ['reporting.view', 'payroll.view'],
    match: 'all',
    dashboardKey: 'payroll_overview',
  },
  {
    id: 'recruitment',
    label: 'Recruitment',
    to: '/reporting/recruitment',
    description:
      'Recruitment dashboard for active sourcing, interview pressure, and offer-stage movement.',
    requiredPermissions: ['reporting.view', 'recruitment.view'],
    match: 'all',
    dashboardKey: 'recruiter_overview',
  },
  {
    id: 'executive',
    label: 'Executive',
    to: '/reporting/executive',
    description:
      'Leadership dashboard for enterprise headcount, pipeline, learning, and performance operating signals.',
    requiredPermissions: ['reporting.manage', 'reporting.certify'],
    match: 'any',
    dashboardKey: 'leadership_overview',
  },
]

export const dashboardRouteMap: Record<ReportingDashboardKey, string> = {
  hr_overview: '/reporting/workforce',
  manager_overview: '/reporting/team',
  payroll_overview: '/reporting/payroll',
  recruiter_overview: '/reporting/recruitment',
  leadership_overview: '/reporting/executive',
}

export function getAccessibleReportingDashboardKeys(grantedPermissions: string[]) {
  return reportingSectionDefinitions
    .filter((section) => section.dashboardKey)
    .filter((section) =>
      hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
    )
    .map((section) => section.dashboardKey!)
}
