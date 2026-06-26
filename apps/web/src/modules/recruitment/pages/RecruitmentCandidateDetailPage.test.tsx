import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('RecruitmentCandidateDetailPage', () => {
  it('lets a recruiter convert an accepted offer into a hire handoff in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'platformAdmin' },
      initialEntries: ['/recruitment/candidates/401'],
    })

    expect(await screen.findByRole('heading', { name: /rhea kapoor/i })).toBeInTheDocument()
    expect(screen.getByText(/this offer is accepted and ready for conversion into a hire handoff/i)).toBeInTheDocument()

    await user.type(screen.getByLabelText(/handoff notes/i), 'Launch onboarding immediately.')
    await user.click(screen.getByRole('button', { name: /create hire handoff/i }))

    expect(await screen.findByText(/hire handoff created successfully/i)).toBeInTheDocument()
    expect(screen.getByRole('link', { name: /open employee workspace/i })).toBeInTheDocument()
  })

  it('lets a recruiter advance a candidate from the board in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'recruiter' },
      initialEntries: ['/recruitment/candidates'],
    })

    expect(await screen.findByRole('heading', { name: /pipeline board and movement controls/i })).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /move to hired/i }))

    expect(await screen.findByText(/candidate stage updated/i)).toBeInTheDocument()
  })
})
