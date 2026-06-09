import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('PayrollRunConsolePage', () => {
  it('shows processing and success feedback when a ready run is calculated in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/payroll/run-console'],
    })

    expect(await screen.findByRole('heading', { name: /payroll run console/i })).toBeInTheDocument()
    expect(screen.getByText(/run ready for calculation/i)).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /calculate payroll/i }))

    expect(await screen.findByText(/calculating payroll run/i)).toBeInTheDocument()
    expect(await screen.findByText(/payroll run calculated successfully/i)).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /approve run/i })).toBeEnabled()
  })

  it('surfaces failed-run issues and empty search state in the console', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/payroll/run-console'],
    })

    expect(await screen.findByRole('heading', { name: /payroll run console/i })).toBeInTheDocument()

    await user.click(screen.getByRole('tab', { name: /failed/i }))
    await user.click(screen.getByText('July 2026 Payroll'))

    expect(screen.getByText(/review failed items before rerunning or reopening this payroll set/i)).toBeInTheDocument()
    expect(screen.getByText(/net salary cannot be negative/i)).toBeInTheDocument()

    await user.clear(screen.getByPlaceholderText(/search payroll periods, runs, or statuses/i))
    await user.type(screen.getByPlaceholderText(/search payroll periods, runs, or statuses/i), 'zzz-no-payroll-match')

    expect(await screen.findByText(/no payroll periods match this view/i)).toBeInTheDocument()
  })

  it('lets a tenant admin create a payroll adjustment for a ready demo run', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/payroll/run-console'],
    })

    expect(await screen.findByRole('heading', { name: /payroll run console/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /manual adjustments/i })).toBeInTheDocument()

    await user.clear(screen.getByLabelText(/adjustment code/i))
    await user.type(screen.getByLabelText(/adjustment code/i), 'SPOT_BONUS')
    await user.clear(screen.getByLabelText(/adjustment name/i))
    await user.type(screen.getByLabelText(/adjustment name/i), 'Spot bonus')
    await user.clear(screen.getByLabelText(/^amount$/i))
    await user.type(screen.getByLabelText(/^amount$/i), '1250')
    await user.click(screen.getByRole('button', { name: /create adjustment/i }))

    expect(await screen.findByText(/payroll adjustment created successfully/i)).toBeInTheDocument()
    expect(screen.getByText(/spot bonus/i)).toBeInTheDocument()
  })
})
