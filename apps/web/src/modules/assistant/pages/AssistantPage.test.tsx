import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('AssistantPage', () => {
  it('lets an employee ask a governed question and record feedback in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/assistant'],
    })

    expect(await screen.findByRole('heading', { name: /governed ai assistant/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /review analytics/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /audit trail/i })).toBeInTheDocument()

    await user.clear(screen.getByLabelText(/ask a governed hr question/i))
    await user.type(screen.getByLabelText(/ask a governed hr question/i), 'Show my latest payslip.')
    await user.click(screen.getByRole('button', { name: /ask assistant/i }))

    expect((await screen.findAllByText(/psl-6204-pay-2103/i)).length).toBeGreaterThan(0)
    expect((await screen.findAllByText(/rank 1/i)).length).toBeGreaterThan(0)

    await user.type(
      screen.getByLabelText(/feedback notes for interaction/i),
      'Clear answer with the right source trail.',
    )
    await user.click(screen.getByRole('button', { name: /helpful/i }))

    expect(await screen.findByText(/feedback recorded for assistant quality review/i)).toBeInTheDocument()
  })

  it('lets a manager generate and accept a review-only recommendation in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/assistant'],
    })

    expect(await screen.findByRole('heading', { name: /governed ai assistant/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /review analytics/i })).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /generate recommendation/i }))

    expect(await screen.findByText(/latest review item/i)).toBeInTheDocument()

    await user.type(
      screen.getByLabelText(/decision notes for recommendation/i),
      'Manager reviewed the recommendation and will follow the governed route.',
    )
    await user.click(screen.getByRole('button', { name: /accept recommendation/i }))

    expect(await screen.findByText(/recommendation accepted and captured for audit review/i)).toBeInTheDocument()
    expect((await screen.findAllByText(/^accepted$/i)).length).toBeGreaterThan(0)
  })
})
