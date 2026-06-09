import { hasPermissions, type PermissionMatch } from '../../shared/auth/permissions'

export type PayrollWorkspaceSectionId = 'overview' | 'setup' | 'review' | 'runConsole' | 'myPay'

export interface PayrollWorkspaceSection {
  id: PayrollWorkspaceSectionId
  label: string
  to: string
  description: string
  requiredPermissions: string[]
  match?: PermissionMatch
}

export const payrollSectionNavigation: PayrollWorkspaceSection[] = [
  {
    id: 'overview',
    label: 'Overview',
    to: '/payroll/overview',
    description: 'Monitor payroll readiness, run pressure, payslip posture, and release blockers from one operations center.',
    requiredPermissions: ['payroll.view', 'payroll.process', 'payroll.approve', 'payroll.lock', 'payroll.reopen'],
    match: 'any',
  },
  {
    id: 'setup',
    label: 'Setup',
    to: '/payroll/setup',
    description:
      'Configure payroll calendars, salary components, structures, compensation assignments, and future payroll periods from one admin studio.',
    requiredPermissions: ['payroll.process', 'salary.manage', 'compensation.manage'],
    match: 'any',
  },
  {
    id: 'review',
    label: 'Review',
    to: '/payroll/review',
    description: 'Inspect payroll summaries, flagged exceptions, and variance signals before release.',
    requiredPermissions: ['payroll.view', 'payroll.process', 'payroll.approve', 'payroll.lock', 'payroll.reopen'],
    match: 'any',
  },
  {
    id: 'runConsole',
    label: 'Run console',
    to: '/payroll/run-console',
    description: 'Prepare, calculate, approve, lock, reopen, and close payroll with exception visibility in one workspace.',
    requiredPermissions: ['payroll.process', 'payroll.approve', 'payroll.lock', 'payroll.reopen'],
    match: 'any',
  },
  {
    id: 'myPay',
    label: 'My pay',
    to: '/payroll/my-pay',
    description: 'View finalized payslips, review payroll release history, and check compensation visibility for your profile.',
    requiredPermissions: ['payslip.view', 'compensation.view'],
    match: 'any',
  },
]

export function getVisiblePayrollSections(grantedPermissions: string[]) {
  return payrollSectionNavigation.filter((section) =>
    hasPermissions(grantedPermissions, section.requiredPermissions, section.match ?? 'all'),
  )
}

export function getDefaultPayrollSectionPath(grantedPermissions: string[]) {
  const visibleSections = getVisiblePayrollSections(grantedPermissions)

  if (visibleSections.some((section) => section.id === 'overview')) {
    return payrollSectionNavigation.find((section) => section.id === 'overview')?.to ?? '/payroll'
  }

  if (visibleSections.some((section) => section.id === 'setup')) {
    return payrollSectionNavigation.find((section) => section.id === 'setup')?.to ?? '/payroll'
  }

  if (visibleSections.some((section) => section.id === 'review')) {
    return payrollSectionNavigation.find((section) => section.id === 'review')?.to ?? '/payroll'
  }

  if (visibleSections.some((section) => section.id === 'runConsole')) {
    return payrollSectionNavigation.find((section) => section.id === 'runConsole')?.to ?? '/payroll'
  }

  if (visibleSections.some((section) => section.id === 'myPay')) {
    return payrollSectionNavigation.find((section) => section.id === 'myPay')?.to ?? '/payroll'
  }

  return '/payroll'
}

export function isPayrollSectionPath(pathname: string, section: PayrollWorkspaceSection) {
  return pathname === section.to || pathname.startsWith(`${section.to}/`)
}
