import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('PayrollSetupPage', () => {
  it('shows payroll setup tabs and metrics for a tenant admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/payroll/setup'],
    })

    expect(await screen.findByRole('heading', { name: /payroll setup studio/i })).toBeInTheDocument()
    expect(screen.getByText(/active calendars/i)).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /calendars/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /structures/i })).toBeInTheDocument()
    expect(screen.getAllByText(/main monthly payroll/i).length).toBeGreaterThan(0)
  })

  it('lets a tenant admin create a payroll calendar in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/payroll/setup'],
    })

    expect(await screen.findByRole('heading', { name: /payroll setup studio/i })).toBeInTheDocument()

    await user.clear(screen.getByLabelText(/calendar name/i))
    await user.type(screen.getByLabelText(/calendar name/i), 'Field Payroll')
    await user.clear(screen.getByLabelText(/timezone/i))
    await user.type(screen.getByLabelText(/timezone/i), 'Asia/Kolkata')
    await user.click(screen.getByRole('button', { name: /create payroll calendar/i }))

    expect(await screen.findByText(/payroll calendar created successfully/i)).toBeInTheDocument()
    expect(screen.getByText(/field payroll/i)).toBeInTheDocument()
  })
})
