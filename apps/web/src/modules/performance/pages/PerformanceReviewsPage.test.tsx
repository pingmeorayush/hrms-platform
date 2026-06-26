import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('PerformanceReviewsPage', () => {
  it('lets a manager submit review input in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/performance/reviews'],
    })

    expect(await screen.findByRole('heading', { name: /performance review cockpit/i })).toBeInTheDocument()
    expect(await screen.findByRole('heading', { name: /kabir malik/i })).toBeInTheDocument()

    await user.type(screen.getByLabelText(/section comment delivery impact/i), 'Delivered the migration plan and launch checklist.')
    await user.type(screen.getByLabelText(/^overall rating$/i), '4.4')
    await user.type(screen.getByLabelText(/review summary/i), 'Strong half with clear delivery and better stakeholder clarity.')
    await user.click(await screen.findByRole('button', { name: /submit review/i }))

    expect(await screen.findByText(/review input saved/i)).toBeInTheDocument()
    expect(screen.getByText(/strong half with clear delivery and better stakeholder clarity/i)).toBeInTheDocument()
  })
})
