import { screen, waitFor, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { readCommandCenterEvents } from '../../../app/shell/commandCenterEvents'
import { readShellRecent } from '../../../app/shell/recent'
import { LeaveReviewWorkspace } from './LeaveReviewWorkspace'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('LeaveReviewWorkspace', () => {
  it('reopens a specific leave request when the hash targets a request record', async () => {
    window.localStorage.removeItem('phoenixhrms.shell.recent')

    renderWithProviders(<LeaveReviewWorkspace />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/leave/approvals#request-202'],
    })

    expect(await screen.findByRole('dialog', { name: /review leave request/i })).toBeInTheDocument()
    expect(screen.getAllByText(/rohit iyer/i).length).toBeGreaterThan(0)
    await waitFor(() => {
      expect(readShellRecent()?.[0]?.label).toMatch(/leave approval · pending/i)
    })
  })

  it('lets users pin the approvals workspace from a queue row', async () => {
    window.localStorage.removeItem('phoenixhrms.shell.favorites')
    const user = userEvent.setup()

    renderWithProviders(<LeaveReviewWorkspace />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/leave/approvals'],
    })

    const pinButtons = screen.getAllByRole('button', { name: /pin approvals workspace/i })
    await user.click(pinButtons[0])

    expect(screen.getAllByRole('button', { name: /unpin approvals workspace/i }).length).toBeGreaterThan(0)
  })

  it('limits manager review to direct reports in team scope', () => {
    renderWithProviders(<LeaveReviewWorkspace />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/leave/approvals'],
    })

    expect(screen.getByText(/team scope/i)).toBeInTheDocument()
    expect(screen.getAllByText(/rohit iyer/i).length).toBeGreaterThan(0)
    expect(screen.getAllByText(/naina kapoor/i).length).toBeGreaterThan(0)
    expect(screen.queryByText(/kabir malik/i)).not.toBeInTheDocument()
    expect(screen.queryByText(/meera sethi/i)).not.toBeInTheDocument()
  })

  it('approves a pending leave request and moves it into the calendar', async () => {
    window.localStorage.removeItem('phoenixhrms.command-center.events')
    const user = userEvent.setup()

    renderWithProviders(<LeaveReviewWorkspace />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/leave/approvals'],
    })

    await user.click(screen.getByRole('button', { name: /review rohit iyer/i }))
    const reviewDialog = await screen.findByRole('dialog', { name: /review leave request/i })
    const reviewScoped = within(reviewDialog)

    await user.type(
      reviewScoped.getByLabelText('Manager comment'),
      'Approved after confirming delivery coverage with the team.',
    )
    await user.click(reviewScoped.getByRole('button', { name: /approve request/i }))

    const confirmDialog = await screen.findByRole('alertdialog', { name: /approve this leave request\?/i })
    await user.click(within(confirmDialog).getByRole('button', { name: /approve request/i }))

    expect(await screen.findByText(/leave request approved/i)).toBeInTheDocument()
    expect(readCommandCenterEvents().activities[0]?.title).toMatch(/rohit iyer leave request approved/i)
    expect(readCommandCenterEvents().alerts.find((item) => item.module === 'leave' && item.id === 'pending-review')?.title).toMatch(/approval queue clear/i)
    expect(screen.getByText(/queue clear/i)).toBeInTheDocument()
    expect(screen.getByText(/approved after confirming delivery coverage with the team\./i)).toBeInTheDocument()
  }, 20000)

  it('supports requesting changes from the review queue', async () => {
    const user = userEvent.setup()

    renderWithProviders(<LeaveReviewWorkspace />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/leave/approvals'],
    })

    await user.click(screen.getByRole('button', { name: /review rohit iyer/i }))
    const reviewDialog = await screen.findByRole('dialog', { name: /review leave request/i })
    const reviewScoped = within(reviewDialog)

    await user.type(
      reviewScoped.getByLabelText('Manager comment'),
      'Please update the coverage handoff notes before this can be approved.',
    )
    await user.click(reviewScoped.getByRole('button', { name: /request changes/i }))

    const confirmDialog = await screen.findByRole('alertdialog', { name: /request changes on this leave request\?/i })
    await user.click(within(confirmDialog).getByRole('button', { name: /request changes/i }))

    expect(await screen.findByText(/changes requested/i)).toBeInTheDocument()
    expect(screen.getByText(/queue clear/i)).toBeInTheDocument()
    expect(screen.getByText(/no pending leave approvals/i)).toBeInTheDocument()
  }, 20000)
})
