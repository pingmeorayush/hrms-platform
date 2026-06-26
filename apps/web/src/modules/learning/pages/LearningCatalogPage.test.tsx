import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('LearningCatalogPage', () => {
  it('lets a tenant admin create a learning item in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/learning/catalog'],
    })

    expect(await screen.findByRole('heading', { name: /learning catalog studio/i })).toBeInTheDocument()

    await user.clear(screen.getByLabelText(/item code/i))
    await user.type(screen.getByLabelText(/item code/i), 'SAFE-OPS')
    await user.clear(screen.getByLabelText(/^title$/i))
    await user.type(screen.getByLabelText(/^title$/i), 'Safe Operations Fundamentals')
    await user.click(screen.getByRole('button', { name: /create learning item/i }))

    expect(await screen.findByText(/learning item created successfully/i)).toBeInTheDocument()
    expect(screen.getByText(/safe operations fundamentals/i)).toBeInTheDocument()
  })
})
