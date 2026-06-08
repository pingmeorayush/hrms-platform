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
})
