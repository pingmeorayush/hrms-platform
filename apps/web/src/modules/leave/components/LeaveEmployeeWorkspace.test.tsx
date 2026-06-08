import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { LeaveEmployeeWorkspace } from './LeaveEmployeeWorkspace'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'
import { selectRadixOption } from '../../../shared/testing/selectRadixOption'

describe('LeaveEmployeeWorkspace', () => {
  it('submits and cancels a leave request for an employee session', async () => {
    const user = userEvent.setup()

    renderWithProviders(<LeaveEmployeeWorkspace />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/leave'],
    })

    expect(screen.getByRole('heading', { name: /leave balances/i })).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /request leave/i }))
    const requestDialog = screen.getByRole('dialog', { name: /submit leave request/i })
    const requestScoped = within(requestDialog)

    await selectRadixOption(user, requestScoped, 'Leave type', /annual leave/i)
    await user.type(requestScoped.getByLabelText('Start date'), '2026-06-12')
    await user.type(requestScoped.getByLabelText('End date'), '2026-06-13')
    await user.type(requestScoped.getByLabelText('Reason'), 'Family event travel.')
    await user.click(requestScoped.getByRole('button', { name: /submit leave request/i }))

    const confirmDialog = screen.getByRole('alertdialog', { name: /submit leave request\?/i })
    await user.click(within(confirmDialog).getByRole('button', { name: /submit request/i }))

    expect(await screen.findByText(/leave request submitted/i)).toBeInTheDocument()
    expect(screen.getByText(/family event travel\./i)).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /cancel request/i }))
    const cancelDialog = screen.getByRole('alertdialog', { name: /cancel this leave request\?/i })
    await user.click(within(cancelDialog).getByRole('button', { name: /cancel request/i }))

    expect(await screen.findByText(/cancelled by the employee before approval\./i)).toBeInTheDocument()
    expect(screen.queryByRole('button', { name: /cancel request/i })).not.toBeInTheDocument()
  }, 15000)

  it('blocks overlapping leave requests in the demo workspace', async () => {
    const user = userEvent.setup()

    renderWithProviders(<LeaveEmployeeWorkspace />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/leave'],
    })

    await user.click(screen.getByRole('button', { name: /request leave/i }))
    const requestDialog = screen.getByRole('dialog', { name: /submit leave request/i })
    const requestScoped = within(requestDialog)

    await selectRadixOption(user, requestScoped, 'Leave type', /optional holiday/i)
    await user.type(requestScoped.getByLabelText('Start date'), '2026-08-28')
    await user.type(requestScoped.getByLabelText('End date'), '2026-08-28')
    await user.type(requestScoped.getByLabelText('Reason'), 'Festival travel overlap check.')
    await user.click(requestScoped.getByRole('button', { name: /submit leave request/i }))

    const confirmDialog = screen.getByRole('alertdialog', { name: /submit leave request\?/i })
    await user.click(within(confirmDialog).getByRole('button', { name: /submit request/i }))

    expect((await screen.findAllByText(/leave dates overlap with an existing request\./i)).length).toBeGreaterThan(0)
  }, 15000)
})
