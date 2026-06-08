import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AttendanceAdminWorkspace } from './AttendanceAdminWorkspace'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'
import { selectRadixOption } from '../../../shared/testing/selectRadixOption'

describe('AttendanceAdminWorkspace', () => {
  it('shows a roster conflict clearly for an admin session', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AttendanceAdminWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/attendance'],
    })

    await user.click(screen.getByRole('tab', { name: /rosters/i }))
    await user.click(screen.getByRole('button', { name: /new roster/i }))
    const rosterDialog = screen.getByRole('dialog', { name: /schedule roster entry/i })
    const rosterScoped = within(rosterDialog)

    await selectRadixOption(user, rosterScoped, 'Employee', /kabir malik/i)
    await selectRadixOption(user, rosterScoped, 'Shift', /night support shift/i)
    await user.clear(rosterScoped.getByLabelText('Work date'))
    await user.type(rosterScoped.getByLabelText('Work date'), '2026-06-05')
    await user.click(rosterScoped.getByRole('button', { name: /schedule roster entry/i }))

    const confirmDialog = screen.getByRole('alertdialog', { name: /schedule roster entry\?/i })
    await user.click(within(confirmDialog).getByRole('button', { name: /schedule roster/i }))

    expect(
      await screen.findByText(/already has a scheduled roster entry on the selected date/i),
    ).toBeInTheDocument()
  }, 15000)

  it('keeps attendance setup read only for a manager persona', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AttendanceAdminWorkspace />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/attendance'],
    })

    expect(screen.getByText(/admin actions are permission restricted/i)).toBeInTheDocument()
    await user.click(screen.getByRole('button', { name: /edit in modal/i }))
    expect(screen.getByText(/attendance policy editing is restricted in this session/i)).toBeInTheDocument()
    await user.click(screen.getByRole('button', { name: /^close$/i }))

    await user.click(screen.getByRole('tab', { name: /shifts/i }))
    await user.click(screen.getByRole('button', { name: /new shift/i }))

    expect(screen.getByText(/shift definition changes require shift-management access/i)).toBeInTheDocument()
  })
})
