import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('OperationsAssetsPage', () => {
  it('lets a tenant admin issue an assigned asset in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/operations/assets'],
    })

    expect(await screen.findByRole('heading', { name: /asset operations/i })).toBeInTheDocument()

    const issueButton = screen.getByRole('button', { name: /^issue$/i })
    await user.click(issueButton)

    expect(await screen.findByRole('dialog', { name: /issue asset/i })).toBeInTheDocument()
    await user.click(screen.getByRole('button', { name: /issue asset/i }))

    expect(await screen.findByText(/issued successfully/i)).toBeInTheDocument()

    const badgeRow = screen.getByText('AST-BDG-1005').closest('tr')
    expect(badgeRow).not.toBeNull()
    expect(within(badgeRow as HTMLTableRowElement).getAllByText(/issued/i).length).toBeGreaterThan(0)
  })
})
