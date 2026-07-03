import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('OperationsReleasePage', () => {
  it('shows release gates and production approval posture in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/operations/release'],
    })

    expect(await screen.findByRole('heading', { name: /release engineering baseline/i })).toBeInTheDocument()
    expect(screen.getByText(/protected branch main/i)).toBeInTheDocument()
    expect(screen.getByText(/authorized release approval is still required/i)).toBeInTheDocument()

    const gateRow = screen.getByText(/dependency security gate/i).closest('tr')
    expect(gateRow).not.toBeNull()

    await user.click(within(gateRow as HTMLTableRowElement).getByRole('button', { name: /review/i }))

    expect(screen.getByText(/composer audit --locked/i)).toBeInTheDocument()
    expect(screen.getByText(/apps\/api\/package-lock\.json/i)).toBeInTheDocument()
  })
})
