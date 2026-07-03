import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('OperationsObservabilityPage', () => {
  it('shows routed alerts and service drill-in details in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/operations/observability'],
    })

    expect(await screen.findByRole('heading', { name: /operational observability baseline/i })).toBeInTheDocument()
    expect(screen.getByText(/sev1 platform command/i)).toBeInTheDocument()
    expect(screen.getByText(/\/up health endpoint/i)).toBeInTheDocument()

    const payrollRow = screen.getByText(/payroll controls/i).closest('tr')
    expect(payrollRow).not.toBeNull()

    await user.click(within(payrollRow as HTMLTableRowElement).getByRole('button', { name: /review/i }))

    expect(screen.getByText(/^blocked or failed payroll runs$/i)).toBeInTheDocument()
    expect(screen.getByRole('link', { name: /open payroll review/i })).toBeInTheDocument()
    expect(screen.getByText(/payroll control lane is blocked/i)).toBeInTheDocument()
  })
})
