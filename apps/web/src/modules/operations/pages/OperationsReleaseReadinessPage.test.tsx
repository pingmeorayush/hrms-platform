import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('OperationsReleaseReadinessPage', () => {
  it('shows launch readiness and records a demo go-live decision', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/operations/readiness'],
    })

    expect(await screen.findByRole('heading', { name: /go-live readiness/i })).toBeInTheDocument()
    expect(screen.getByText(/launch incident response runbook/i)).toBeInTheDocument()
    expect(screen.getAllByText(/launch can proceed only if recovery evidence is refreshed/i).length).toBeGreaterThan(0)

    const areaRow = screen.getAllByText(/^Backups and recovery evidence$/i)[0]?.closest('tr')
    expect(areaRow).not.toBeNull()

    await user.click(within(areaRow as HTMLTableRowElement).getByRole('button', { name: /review/i }))

    expect(screen.getByText(/backup, restore, and failover evidence must be current enough/i)).toBeInTheDocument()

    await user.type(screen.getByLabelText(/release window/i), 'FY26 payroll launch wave 2')
    await user.clear(screen.getByLabelText(/decision summary/i))
    await user.type(
      screen.getByLabelText(/decision summary/i),
      'Launch is conditionally approved while payroll and recovery owners close the remaining evidence.',
    )
    await user.clear(screen.getByLabelText(/blockers/i))
    await user.type(
      screen.getByLabelText(/blockers/i),
      'backups | platform.super_admin | Monthly restore evidence is still being rerun | Final validation closes today',
    )
    await user.type(
      screen.getByLabelText(/decision notes/i),
      'Ops lead will confirm the rerun before the production window opens.',
    )
    await user.click(screen.getByRole('button', { name: /record decision/i }))

    expect(screen.getByText(/release readiness decision recorded/i)).toBeInTheDocument()
    expect(screen.getAllByText(/FY26 payroll launch wave 2/i).length).toBeGreaterThan(0)
    expect(screen.getByText(/Ops lead will confirm the rerun/i)).toBeInTheDocument()
  })
})
