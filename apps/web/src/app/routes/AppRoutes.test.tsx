import { screen, within } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from './AppRoutes'
import { renderWithProviders } from '../../shared/testing/renderWithProviders'

describe('AppRoutes', () => {
  it('shows the routed foundation shell and employee navigation for a tenant admin', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/foundation'],
    })

    expect(await screen.findByRole('heading', { name: /workspace access/i })).toBeInTheDocument()
    const primaryNavigation = screen.getByRole('navigation', { name: /primary/i })
    expect(within(primaryNavigation).getByRole('link', { name: /^organization$/i })).toBeInTheDocument()
    expect(within(primaryNavigation).getByRole('link', { name: /^employees$/i })).toBeInTheDocument()
  }, 15000)

  it('blocks an employee persona from the organization admin route', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/admin/organization'],
    })

    expect(screen.getByRole('heading', { name: /organization workspace unavailable/i })).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: /organization master admin workspace/i })).not.toBeInTheDocument()
  })

  it('opens the routed organization module for a tenant admin session', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/admin/organization'],
    })

    expect(screen.getByRole('heading', { name: /^organization$/i })).toBeInTheDocument()
    const organizationSections = screen.getByRole('navigation', { name: /organization sections/i })
    expect(within(organizationSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(organizationSections).getByRole('link', { name: /company profile/i })).toBeInTheDocument()
    expect(within(organizationSections).getByRole('link', { name: /structure/i })).toBeInTheDocument()
    expect(within(organizationSections).getByRole('link', { name: /locations/i })).toBeInTheDocument()
    expect(within(organizationSections).getByRole('link', { name: /cost centers/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /organization operations center/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /company profile/i })).toBeInTheDocument()
  })

  it('opens the employee detail route for a manager session', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/employees/1005'],
    })

    expect(screen.getByRole('heading', { name: /kabir malik/i })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: /^profile$/i })).toBeInTheDocument()
    expect(screen.getByText(/sensitive banking is hidden/i)).toBeInTheDocument()
  })

  it('opens the routed employee module for a tenant admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/employees'],
    })

    expect(await screen.findByRole('heading', { name: /^employees$/i })).toBeInTheDocument()
    const employeeSections = screen.getByRole('navigation', { name: /employees sections/i })
    expect(within(employeeSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(employeeSections).getByRole('link', { name: /directory/i })).toBeInTheDocument()
    expect(within(employeeSections).getByRole('link', { name: /lifecycle watch/i })).toBeInTheDocument()
    expect(within(employeeSections).getByRole('link', { name: /onboarding/i })).toBeInTheDocument()
    expect(within(employeeSections).getByRole('link', { name: /documents/i })).toBeInTheDocument()
    expect(within(employeeSections).getByRole('link', { name: /audit/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /employees operations center/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /directory/i })).toBeInTheDocument()
  }, 15000)

  it('opens the attendance admin route for a tenant admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/attendance'],
    })

    expect(await screen.findByRole('heading', { name: /^attendance$/i })).toBeInTheDocument()
    const attendanceSections = screen.getByRole('navigation', { name: /attendance sections/i })
    expect(within(attendanceSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(attendanceSections).getByRole('link', { name: /my attendance/i })).toBeInTheDocument()
    expect(within(attendanceSections).getByRole('link', { name: /operational review/i })).toBeInTheDocument()
    expect(within(attendanceSections).getByRole('link', { name: /admin setup/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /attendance operations center/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /assignments/i })).toBeInTheDocument()
  }, 15000)

  it('opens the attendance operational review route for a manager session', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/attendance'],
    })

    expect(screen.getByRole('heading', { name: /^attendance$/i })).toBeInTheDocument()
    const attendanceSections = screen.getByRole('navigation', { name: /attendance sections/i })
    expect(within(attendanceSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(attendanceSections).getByRole('link', { name: /my attendance/i })).toBeInTheDocument()
    expect(within(attendanceSections).getByRole('link', { name: /operational review/i })).toBeInTheDocument()
    expect(within(attendanceSections).queryByRole('link', { name: /admin setup/i })).not.toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /attendance operations center/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /review queue/i })).toBeInTheDocument()
  })

  it('opens the attendance self-service route for an employee session', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/attendance'],
    })

    expect(screen.getByRole('heading', { name: /^attendance$/i })).toBeInTheDocument()
    const attendanceSections = screen.getByRole('navigation', { name: /attendance sections/i })
    expect(within(attendanceSections).queryByRole('link', { name: /overview/i })).not.toBeInTheDocument()
    expect(within(attendanceSections).getByRole('link', { name: /my attendance/i })).toBeInTheDocument()
    expect(within(attendanceSections).queryByRole('link', { name: /operational review/i })).not.toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /attendance history/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /correction requests/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /check in \/ out/i })).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: /attendance administration/i })).not.toBeInTheDocument()
  })

  it('opens the leave admin route for a tenant admin session', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/leave'],
    })

    expect(screen.getByRole('heading', { name: /^leave$/i })).toBeInTheDocument()
    const leaveSections = screen.getByRole('navigation', { name: /leave sections/i })
    expect(within(leaveSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(leaveSections).getByRole('link', { name: /requests/i })).toBeInTheDocument()
    expect(within(leaveSections).getByRole('link', { name: /approvals/i })).toBeInTheDocument()
    expect(within(leaveSections).getByRole('link', { name: /policy admin/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /leave operations center/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /requests/i })).toBeInTheDocument()
  })

  it('opens the leave self-service route for a manager session', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/leave'],
    })

    expect(screen.getByRole('heading', { name: /^leave$/i })).toBeInTheDocument()
    const leaveSections = screen.getByRole('navigation', { name: /leave sections/i })
    expect(within(leaveSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(leaveSections).getByRole('link', { name: /requests/i })).toBeInTheDocument()
    expect(within(leaveSections).getByRole('link', { name: /approvals/i })).toBeInTheDocument()
    expect(within(leaveSections).queryByRole('link', { name: /policy admin/i })).not.toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /leave operations center/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /approvals/i })).toBeInTheDocument()
  })

  it('opens the leave self-service route for an employee session', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/leave'],
    })

    expect(screen.getByRole('heading', { name: /^leave$/i })).toBeInTheDocument()
    const leaveSections = screen.getByRole('navigation', { name: /leave sections/i })
    expect(within(leaveSections).queryByRole('link', { name: /overview/i })).not.toBeInTheDocument()
    expect(within(leaveSections).getByRole('link', { name: /requests/i })).toBeInTheDocument()
    expect(within(leaveSections).queryByRole('link', { name: /approvals/i })).not.toBeInTheDocument()
    expect(within(leaveSections).queryByRole('link', { name: /policy admin/i })).not.toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /submit leave request/i })).toBeInTheDocument()
  })

  it('opens the payroll module for a tenant admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/payroll'],
    })

    expect(await screen.findByRole('heading', { name: /^payroll$/i })).toBeInTheDocument()
    expect(screen.queryByText(/operations console/i)).not.toBeInTheDocument()
    const payrollSections = screen.getByRole('navigation', { name: /payroll sections/i })
    expect(within(payrollSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(payrollSections).getByRole('link', { name: /setup/i })).toBeInTheDocument()
    expect(within(payrollSections).getByRole('link', { name: /review/i })).toBeInTheDocument()
    expect(within(payrollSections).getByRole('link', { name: /run console/i })).toBeInTheDocument()
    expect(within(payrollSections).getByRole('link', { name: /my pay/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /payroll operations center/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /runs/i })).toBeInTheDocument()
  }, 15000)

  it('opens the payroll setup route for a tenant admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/payroll/setup'],
    })

    expect(await screen.findByRole('heading', { name: /payroll setup studio/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /calendars/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /compensation/i })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /create payroll calendar/i })).toBeInTheDocument()
  })

  it('opens payroll self-service for an employee session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/payroll'],
    })

    expect(await screen.findByRole('heading', { name: /^payroll$/i })).toBeInTheDocument()
    const payrollSections = screen.getByRole('navigation', { name: /payroll sections/i })
    expect(within(payrollSections).queryByRole('link', { name: /overview/i })).not.toBeInTheDocument()
    expect(within(payrollSections).queryByRole('link', { name: /setup/i })).not.toBeInTheDocument()
    expect(within(payrollSections).queryByRole('link', { name: /review/i })).not.toBeInTheDocument()
    expect(within(payrollSections).queryByRole('link', { name: /run console/i })).not.toBeInTheDocument()
    expect(within(payrollSections).getByRole('link', { name: /my pay/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /my pay/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /payslips/i })).toBeInTheDocument()
  }, 15000)

  it('blocks an employee persona from the payroll review route', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/payroll/review'],
    })

    expect(screen.getByRole('heading', { name: /payroll review unavailable/i })).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: /^payroll review$/i })).not.toBeInTheDocument()
  })

  it('blocks a manager persona from the payroll route', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/payroll'],
    })

    expect(screen.getByRole('heading', { name: /payroll workspace unavailable/i })).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: /payroll operations center/i })).not.toBeInTheDocument()
  })

  it('opens the self-service module for an employee session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/self-service'],
    })

    expect(await screen.findByRole('heading', { name: /my profile/i })).toBeInTheDocument()
    const selfServiceSections = screen.getByRole('navigation', { name: /self service sections/i })
    expect(within(selfServiceSections).getByRole('link', { name: /profile/i })).toBeInTheDocument()
    expect(within(selfServiceSections).getByRole('link', { name: /documents/i })).toBeInTheDocument()
    expect(within(selfServiceSections).getByRole('link', { name: /assigned assets/i })).toBeInTheDocument()
    expect(screen.getByText(/sensitive banking is hidden/i)).toBeInTheDocument()
  })

  it('opens the operations module for a tenant admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/operations'],
    })

    expect(await screen.findByRole('heading', { name: /hr and it operations center/i })).toBeInTheDocument()
    const operationSections = screen.getByRole('navigation', { name: /operations sections/i })
    expect(within(operationSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /documents/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /assets/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /lifecycle/i })).toBeInTheDocument()
  }, 15000)

  it('limits the operations module to document and asset sections for an IT operator', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'itOperator' },
      initialEntries: ['/operations'],
    })

    expect(await screen.findByRole('heading', { name: /hr and it operations center/i })).toBeInTheDocument()
    const operationSections = screen.getByRole('navigation', { name: /operations sections/i })
    expect(within(operationSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /documents/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /assets/i })).toBeInTheDocument()
    expect(within(operationSections).queryByRole('link', { name: /lifecycle/i })).not.toBeInTheDocument()
  }, 15000)

  it('blocks an employee persona from the operations route', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/operations'],
    })

    expect(screen.getByRole('heading', { name: /operations workspace unavailable/i })).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: /hr and it operations center/i })).not.toBeInTheDocument()
  })
})
