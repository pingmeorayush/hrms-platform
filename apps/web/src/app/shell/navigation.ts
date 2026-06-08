import type { PermissionMatch } from '../../shared/auth/permissions'
import { attendanceSectionNavigation } from '../../modules/attendance/navigation'
import { employeeWorkspaceSectionNavigation } from '../../modules/employees/navigation'
import { leaveSectionNavigation } from '../../modules/leave/navigation'
import { organizationSectionNavigation } from '../../modules/organization/navigation'

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
  icon: 'foundation' | 'organization' | 'employees' | 'attendance' | 'leave' | 'access'
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
