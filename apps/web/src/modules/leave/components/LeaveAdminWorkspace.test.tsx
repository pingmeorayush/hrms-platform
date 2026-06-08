import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { LeaveAdminWorkspace } from './LeaveAdminWorkspace'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'
import { selectRadixOption } from '../../../shared/testing/selectRadixOption'

describe('LeaveAdminWorkspace', () => {
  it('supports leave type validation and creation for a tenant admin', async () => {
    const user = userEvent.setup()

    renderWithProviders(<LeaveAdminWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/leave'],
    })

    expect(screen.getByRole('heading', { name: /leave policy administration/i })).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /new leave type/i }))
    const typeDialog = screen.getByRole('dialog', { name: /create leave type/i })
    const typeScoped = within(typeDialog)
    await user.clear(typeScoped.getByLabelText('Code'))
    await user.clear(typeScoped.getByLabelText('Name'))
    await user.click(typeScoped.getByRole('button', { name: /create leave type/i }))

    expect(await screen.findByText(/code and name are required/i)).toBeInTheDocument()

    await user.type(typeScoped.getByLabelText('Code'), 'BRV')
    await user.type(typeScoped.getByLabelText('Name'), 'Bereavement Leave')
    await user.click(typeScoped.getByRole('button', { name: /create leave type/i }))

    const confirmDialog = screen.getByRole('alertdialog', { name: /create leave type\?/i })
    await user.click(within(confirmDialog).getByRole('button', { name: /create leave type/i }))

    expect(await screen.findByText(/leave type created/i)).toBeInTheDocument()
    expect(screen.getByText(/bereavement leave/i)).toBeInTheDocument()
  }, 15000)

  it('filters the organization leave calendar by status and department', async () => {
    const user = userEvent.setup()

    renderWithProviders(<LeaveAdminWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/leave'],
    })

    await user.click(screen.getByRole('tab', { name: /leave calendar/i }))
    await selectRadixOption(user, screen, 'Status', /approved/i)
    await selectRadixOption(user, screen, 'Department', /people operations/i)

    expect(screen.getByText(/meera sethi/i)).toBeInTheDocument()
    expect(screen.getByText(/kabir malik/i)).toBeInTheDocument()
    expect(screen.queryByText(/naina kapoor/i)).not.toBeInTheDocument()
    expect(screen.queryByText(/rohit iyer/i)).not.toBeInTheDocument()
  }, 15000)
})
