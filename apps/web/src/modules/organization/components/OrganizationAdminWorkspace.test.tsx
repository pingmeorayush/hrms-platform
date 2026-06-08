import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { OrganizationAdminWorkspace } from './OrganizationAdminWorkspace'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('OrganizationAdminWorkspace', () => {
  it('creates a demo department record for a tenant admin persona', async () => {
    const user = userEvent.setup()

    renderWithProviders(<OrganizationAdminWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
    })

    await user.click(screen.getByRole('button', { name: /create department/i }))

    const editorDialog = screen.getByRole('dialog', { name: /create department/i })
    const dialogScoped = within(editorDialog)

    await user.type(dialogScoped.getByLabelText('Code'), 'OPS')
    await user.type(dialogScoped.getByLabelText('Name'), 'Operations')
    await user.type(dialogScoped.getByLabelText('Description'), 'Shared operations enablement.')
    await user.click(dialogScoped.getByRole('button', { name: /create record/i }))

    const confirmDialog = screen.getByRole('alertdialog', { name: /create department\?/i })
    await user.click(within(confirmDialog).getByRole('button', { name: /create record/i }))

    expect(await screen.findByText('Operations')).toBeInTheDocument()
    expect(screen.getByText(/record created/i)).toBeInTheDocument()
  }, 15000)
})
