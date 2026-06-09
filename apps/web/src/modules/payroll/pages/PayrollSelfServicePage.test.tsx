import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('PayrollSelfServicePage', () => {
  it('shows finalized payslips while keeping compensation hidden for the employee demo persona', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/payroll/my-pay'],
    })

    expect(await screen.findByRole('heading', { name: /my pay/i })).toBeInTheDocument()
    expect(screen.getAllByText(/psl-6204-pay-2103/i).length).toBeGreaterThan(0)
    expect(screen.getByText(/sensitive compensation fields are hidden/i)).toBeInTheDocument()
  })

  it('shows the no-payslip state when the resolved payroll profile has no finalized release yet', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/payroll/my-pay'],
    })

    expect(await screen.findByRole('heading', { name: /my pay/i })).toBeInTheDocument()
    expect(screen.getAllByText(/no finalized payslips yet/i).length).toBeGreaterThan(0)
    await user.click(screen.getByRole('tab', { name: /compensation/i }))
    expect(screen.getByText(/compensation unlocks after finalized payroll/i)).toBeInTheDocument()
  })

  it('shows approved compensation visibility when the session and payroll state both allow it', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'platformAdmin' },
      initialEntries: ['/payroll/my-pay'],
    })

    expect(await screen.findByRole('heading', { name: /my pay/i })).toBeInTheDocument()
    await user.click(screen.getByRole('tab', { name: /compensation/i }))

    expect(screen.getByText(/annual ctc/i)).toBeInTheDocument()
    expect(screen.getAllByText(/eng-l6-2026 v2/i).length).toBeGreaterThan(0)
    expect(screen.getByText(/basic salary/i)).toBeInTheDocument()
  })
})
