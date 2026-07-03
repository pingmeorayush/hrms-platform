import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('OperationsResiliencePage', () => {
  it('shows recovery readiness and records a demo validation run', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/operations/resilience'],
    })

    expect(await screen.findByRole('heading', { name: /backup and recovery readiness/i })).toBeInTheDocument()
    expect(screen.getByText(/ap-south-1 -> ap-southeast-1/i)).toBeInTheDocument()
    expect(screen.getByText(/regional failover drills are required at least once per quarter/i)).toBeInTheDocument()

    const failoverRow = screen.getAllByText(/^Regional failover drill$/i)[0]?.closest('tr')
    expect(failoverRow).not.toBeNull()

    await user.click(within(failoverRow as HTMLTableRowElement).getByRole('button', { name: /review/i }))

    expect(screen.getByText(/Exercises failover sequencing, role handoffs, and launch-critical smoke checks/i)).toBeInTheDocument()

    await user.selectOptions(screen.getByLabelText(/outcome/i), 'passed')
    await user.clear(screen.getByLabelText(/evidence/i))
    await user.type(screen.getByLabelText(/evidence/i), 'failover-log-q3\nsmoke-check-q3')
    await user.type(screen.getByLabelText(/notes/i), 'Failover rerun completed cleanly.')
    await user.click(screen.getByRole('button', { name: /log validation/i }))

    expect(screen.getByText(/validation run recorded/i)).toBeInTheDocument()
    expect(screen.getByText(/Failover rerun completed cleanly\./i)).toBeInTheDocument()
  })
})
