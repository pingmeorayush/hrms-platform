import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('OperationsLifecyclePage', () => {
  it('switches to offboarding and completes a selected lifecycle task in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/operations/lifecycle'],
    })

    expect(await screen.findByRole('heading', { name: /lifecycle operations/i })).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /offboarding/i }))
    expect(await screen.findByRole('heading', { name: /kabir malik · task detail/i })).toBeInTheDocument()

    const taskRow = screen.getByText(/collect laptop and charger/i).closest('tr')
    expect(taskRow).not.toBeNull()

    await user.click(within(taskRow as HTMLTableRowElement).getByRole('button', { name: /complete/i }))

    expect(await screen.findByText(/lifecycle task updated/i)).toBeInTheDocument()
    expect(within(taskRow as HTMLTableRowElement).getAllByText(/completed/i).length).toBeGreaterThan(0)
  })
})
