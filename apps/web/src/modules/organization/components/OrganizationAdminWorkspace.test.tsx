import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import {
  OrganizationAdminWorkspace,
  OrganizationCompanyProfileWorkspaceView,
} from './OrganizationAdminWorkspace'
import { useOrganizationWorkspace } from '../hooks/useOrganizationWorkspace'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

function DemoCompanyProfileHarness() {
  const workspace = useOrganizationWorkspace()
  return <OrganizationCompanyProfileWorkspaceView workspace={workspace} />
}

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

  it('shows regional company settings and edit controls for the demo tenant profile', async () => {
    const user = userEvent.setup()

    renderWithProviders(<DemoCompanyProfileHarness />, {
      accessState: { demoPersona: 'tenantAdmin' },
    })

    expect(await screen.findByText('Launch country')).toBeInTheDocument()
    expect(screen.getByText('Expansion placeholders')).toBeInTheDocument()
    expect(screen.getByText('Regional preview')).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /edit company profile/i }))

    const editorDialog = screen.getByRole('dialog', { name: /edit company profile/i })
    const dialogScoped = within(editorDialog)

    expect(dialogScoped.getByRole('combobox', { name: /launch country/i })).toBeInTheDocument()
    expect(dialogScoped.getByRole('combobox', { name: /locale/i })).toBeInTheDocument()
    expect(dialogScoped.getByRole('combobox', { name: /language/i })).toBeInTheDocument()
    expect(dialogScoped.getByRole('combobox', { name: /currency/i })).toBeInTheDocument()
    expect(dialogScoped.getByDisplayValue('US, DE')).toBeInTheDocument()
  })
})
