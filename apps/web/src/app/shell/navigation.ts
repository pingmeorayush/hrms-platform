import type { PermissionMatch } from '../../shared/auth/permissions'
import { attendanceSectionNavigation } from '../../modules/attendance/navigation'
import { employeeWorkspaceSectionNavigation } from '../../modules/employees/navigation'
import { leaveSectionNavigation } from '../../modules/leave/navigation'
import { learningSectionNavigation } from '../../modules/learning/navigation'
import { operationsSectionNavigation } from '../../modules/operations/navigation'
import { organizationSectionNavigation } from '../../modules/organization/navigation'
import { payrollSectionNavigation } from '../../modules/payroll/navigation'
import { performanceSectionNavigation } from '../../modules/performance/navigation'
import { reportingSectionNavigation } from '../../modules/reporting/navigation'
import { recruitmentSectionNavigation } from '../../modules/recruitment/navigation'
import { selfServiceSectionNavigation } from '../../modules/self-service/navigation'

export interface AppNavChildItem {
  id: string
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export interface AppNavItem {
  id: string
  label: string
  to: string
  description: string
  icon:
    | 'foundation'
    | 'organization'
    | 'employees'
    | 'recruitment'
    | 'performance'
    | 'learning'
    | 'operations'
    | 'attendance'
    | 'leave'
    | 'payroll'
    | 'reporting'
    | 'selfService'
    | 'access'
  requiredPermissions: string[]
  match?: PermissionMatch
  status: 'live' | 'planned'
  children?: AppNavChildItem[]
}

export const appNavigation: AppNavItem[] = [
  {
    id: 'foundation',
    label: 'Foundation',
    to: '/foundation',
    description: 'Session controls, workspace access, and environment posture.',
    icon: 'foundation',
    requiredPermissions: [],
    status: 'live',
  },
  {
    id: 'organization',
    label: 'Organization',
    to: '/admin/organization',
    description: 'Company profile, structure registries, locations, and cost centers.',
    icon: 'organization',
    requiredPermissions: ['organization.view', 'organization.manage'],
    match: 'any',
    status: 'live',
    children: organizationSectionNavigation,
  },
  {
    id: 'employees',
    label: 'Employees',
    to: '/employees',
    description: 'Manage the roster and open profile, lifecycle, onboarding, and document workspaces.',
    icon: 'employees',
    requiredPermissions: ['employee.view', 'employee.manage'],
    match: 'any',
    status: 'live',
    children: employeeWorkspaceSectionNavigation,
  },
  {
    id: 'recruitment',
    label: 'Recruitment',
    to: '/recruitment',
    description: 'Recruiter and hiring-manager workspace for requisitions, pipeline movement, interviews, offers, and hire handoff.',
    icon: 'recruitment',
    requiredPermissions: ['recruitment.view', 'recruitment.manage', 'recruitment.approve'],
    match: 'any',
    status: 'live',
    children: recruitmentSectionNavigation,
  },
  {
    id: 'performance',
    label: 'Performance',
    to: '/performance',
    description: 'Goal setup, review cycles, self and manager review execution, and calibration posture now share one routed talent module.',
    icon: 'performance',
    requiredPermissions: ['performance.view', 'performance.manage', 'performance.review', 'performance.calibrate'],
    match: 'any',
    status: 'live',
    children: performanceSectionNavigation,
  },
  {
    id: 'learning',
    label: 'Learning',
    to: '/learning',
    description: 'Compliance-ready learning operations for catalog setup, assignment targeting, renewal posture, and learner completion tracking.',
    icon: 'learning',
    requiredPermissions: ['learning.view', 'learning.manage', 'learning.assign', 'learning.complete'],
    match: 'any',
    status: 'live',
    children: learningSectionNavigation,
  },
  {
    id: 'operations',
    label: 'Operations',
    to: '/operations',
    description: 'HR and IT control tower for document governance, asset handoffs, and onboarding-offboarding progress.',
    icon: 'operations',
    requiredPermissions: ['document.view', 'document.manage', 'asset.view', 'asset.manage', 'employee.manage'],
    match: 'any',
    status: 'live',
    children: operationsSectionNavigation,
  },
  {
    id: 'attendance',
    label: 'Attendance',
    to: '/attendance',
    description: 'Employee attendance capture and history, plus policy, shift, and roster administration.',
    icon: 'attendance',
    requiredPermissions: [
      'attendance.view',
      'attendance.create',
      'attendance.edit',
      'attendance.approve',
      'attendance.manage_shift',
      'attendance.manage_roster',
    ],
    match: 'any',
    status: 'live',
    children: attendanceSectionNavigation,
  },
  {
    id: 'leave',
    label: 'Leave',
    to: '/leave',
    description: 'Employee self-service, manager approvals, and HR policy setup now share one leave module.',
    icon: 'leave',
    requiredPermissions: [
      'leave.view',
      'leave.request',
      'leave.approve',
      'leave.manage_policy',
      'leave.manage_balance',
      'employee.manage',
    ],
    match: 'any',
    status: 'live',
    children: leaveSectionNavigation,
  },
  {
    id: 'payroll',
    label: 'Payroll',
    to: '/payroll',
    description: 'Run payroll operations, inspect blocked or failed runs, and access employee payslip self-service from one module.',
    icon: 'payroll',
    requiredPermissions: [
      'payroll.view',
      'payroll.process',
      'payroll.approve',
      'payroll.lock',
      'payroll.reopen',
      'payslip.view',
      'compensation.view',
    ],
    match: 'any',
    status: 'live',
    children: payrollSectionNavigation,
  },
  {
    id: 'reporting',
    label: 'Reporting',
    to: '/reporting',
    description: 'Governed dashboards for workforce, team, payroll, recruiting, and leadership reporting posture.',
    icon: 'reporting',
    requiredPermissions: ['reporting.view', 'reporting.manage', 'reporting.certify', 'reporting.export'],
    match: 'any',
    status: 'live',
    children: reportingSectionNavigation,
  },
  {
    id: 'self-service',
    label: 'Self service',
    to: '/self-service',
    description: 'Review the linked employee profile, download approved files, and track issued assets from one personal workspace.',
    icon: 'selfService',
    requiredPermissions: [],
    status: 'live',
    children: selfServiceSectionNavigation,
  },
  {
    id: 'access',
    label: 'Access',
    to: '/access',
    description: 'Permission contract, backend visibility, and governance checks.',
    icon: 'access',
    requiredPermissions: ['auth.manage_roles', 'auth.manage_permissions'],
    match: 'any',
    status: 'live',
  },
]
