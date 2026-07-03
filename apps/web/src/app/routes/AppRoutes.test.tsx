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

  it('redirects a live protected route to the sign-in screen when no session is present', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { mode: 'live', token: '' },
      initialEntries: ['/assistant'],
    })

    expect(await screen.findByRole('heading', { name: /sign in to your workspace/i })).toBeInTheDocument()
    expect(screen.getByText(/use your assigned account to open the protected workspace/i)).toBeInTheDocument()
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

  it('opens the recruitment module for a recruiter session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'recruiter' },
      initialEntries: ['/recruitment'],
    })

    expect(await screen.findByRole('heading', { name: /^recruitment$/i })).toBeInTheDocument()
    const recruitmentSections = screen.getByRole('navigation', { name: /recruitment sections/i })
    expect(within(recruitmentSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(recruitmentSections).getByRole('link', { name: /requisitions/i })).toBeInTheDocument()
    expect(within(recruitmentSections).getByRole('link', { name: /candidates/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /recruitment operations center/i })).toBeInTheDocument()
  }, 15000)

  it('blocks an employee persona from the recruitment route', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/recruitment'],
    })

    expect(screen.getByRole('heading', { name: /recruitment workspace unavailable/i })).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: /recruitment operations center/i })).not.toBeInTheDocument()
  })

  it('opens the performance module for a tenant admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/performance'],
    })

    expect(await screen.findByRole('heading', { name: /^performance$/i })).toBeInTheDocument()
    const performanceSections = screen.getByRole('navigation', { name: /performance sections/i })
    expect(within(performanceSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(performanceSections).getByRole('link', { name: /goals/i })).toBeInTheDocument()
    expect(within(performanceSections).getByRole('link', { name: /cycles/i })).toBeInTheDocument()
    expect(within(performanceSections).getByRole('link', { name: /reviews/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /performance operations center/i })).toBeInTheDocument()
  }, 15000)

  it('opens the performance review workspace for an employee session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/performance/reviews'],
    })

    expect(await screen.findByRole('heading', { name: /^performance$/i })).toBeInTheDocument()
    const performanceSections = screen.getByRole('navigation', { name: /performance sections/i })
    expect(within(performanceSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(performanceSections).getByRole('link', { name: /goals/i })).toBeInTheDocument()
    expect(within(performanceSections).getByRole('link', { name: /cycles/i })).toBeInTheDocument()
    expect(within(performanceSections).getByRole('link', { name: /reviews/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /performance review cockpit/i })).toBeInTheDocument()
  }, 15000)

  it('opens the reporting module for a tenant admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/reporting'],
    })

    expect(await screen.findByRole('heading', { name: /^reporting$/i })).toBeInTheDocument()
    const reportingSections = screen.getByRole('navigation', { name: /reporting sections/i })
    expect(within(reportingSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(reportingSections).getByRole('link', { name: /explorer/i })).toBeInTheDocument()
    expect(within(reportingSections).getByRole('link', { name: /exports/i })).toBeInTheDocument()
    expect(within(reportingSections).getByRole('link', { name: /subscriptions/i })).toBeInTheDocument()
    expect(within(reportingSections).getByRole('link', { name: /workforce/i })).toBeInTheDocument()
    expect(within(reportingSections).getByRole('link', { name: /team/i })).toBeInTheDocument()
    expect(within(reportingSections).getByRole('link', { name: /payroll/i })).toBeInTheDocument()
    expect(within(reportingSections).getByRole('link', { name: /recruitment/i })).toBeInTheDocument()
    expect(within(reportingSections).getByRole('link', { name: /executive/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /reporting command center/i })).toBeInTheDocument()
  }, 15000)

  it('blocks an employee persona from the reporting route', () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/reporting'],
    })

    expect(screen.getByRole('heading', { name: /reporting workspace unavailable/i })).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: /reporting command center/i })).not.toBeInTheDocument()
  })

  it('opens the learning module for a tenant admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/learning'],
    })

    expect(await screen.findByRole('heading', { name: /^learning$/i })).toBeInTheDocument()
    const learningSections = screen.getByRole('navigation', { name: /learning sections/i })
    expect(within(learningSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(learningSections).getByRole('link', { name: /catalog/i })).toBeInTheDocument()
    expect(within(learningSections).getByRole('link', { name: /assignments/i })).toBeInTheDocument()
    expect(within(learningSections).getByRole('link', { name: /my learning/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /learning operations center/i })).toBeInTheDocument()
  }, 15000)

  it('opens learner self-service for an employee session inside the learning module', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/learning'],
    })

    expect(await screen.findByRole('heading', { name: /^learning$/i })).toBeInTheDocument()
    const learningSections = screen.getByRole('navigation', { name: /learning sections/i })
    expect(within(learningSections).getByRole('link', { name: /overview/i })).toBeInTheDocument()
    expect(within(learningSections).queryByRole('link', { name: /catalog/i })).not.toBeInTheDocument()
    expect(within(learningSections).queryByRole('link', { name: /assignments/i })).not.toBeInTheDocument()
    expect(within(learningSections).getByRole('link', { name: /my learning/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /my learning dashboard/i })).toBeInTheDocument()
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

  it('opens the self-service module for a linked tenant-admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/self-service'],
    })

    expect(await screen.findByRole('heading', { name: /my profile/i })).toBeInTheDocument()
    expect(screen.getAllByText(/meera\.sethi@phoenixhrms\.test/i).length).toBeGreaterThan(0)
    expect(screen.queryByRole('heading', { name: /no linked employee profile/i })).not.toBeInTheDocument()
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
    expect(within(operationSections).getByRole('link', { name: /integrations/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /release/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /readiness/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /observability/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /resilience/i })).toBeInTheDocument()
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
    expect(within(operationSections).getByRole('link', { name: /integrations/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /release/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /readiness/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /observability/i })).toBeInTheDocument()
    expect(within(operationSections).getByRole('link', { name: /resilience/i })).toBeInTheDocument()
    expect(within(operationSections).queryByRole('link', { name: /lifecycle/i })).not.toBeInTheDocument()
  }, 15000)

  it('opens the launch-readiness route for a tenant admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/operations/readiness'],
    })

    expect(await screen.findByRole('heading', { name: /go-live readiness/i })).toBeInTheDocument()
    expect(screen.getByText(/launch incident response runbook/i)).toBeInTheDocument()
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
