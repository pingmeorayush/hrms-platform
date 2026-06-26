import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('LearningMyLearningPage', () => {
  it('lets an employee complete an evidence-backed learning item in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/learning/my-learning'],
    })

    expect(await screen.findByRole('heading', { name: /my learning dashboard/i })).toBeInTheDocument()

    await user.click(screen.getByText(/secure coding essentials/i))
    await user.type(screen.getByLabelText(/completion notes/i), 'Completed the secure coding walkthrough.')
    await user.type(screen.getByLabelText(/evidence type/i), 'certificate')
    await user.type(screen.getByLabelText(/evidence reference/i), 'CERT-SCE-2103')
    await user.click(screen.getByRole('button', { name: /complete learning item/i }))

    expect(await screen.findByText(/learning completion recorded/i)).toBeInTheDocument()
    expect(screen.getAllByText(/completed/i).length).toBeGreaterThan(0)
  })
})
