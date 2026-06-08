import { screen, waitFor, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { readCommandCenterEvents } from '../../../app/shell/commandCenterEvents'
import { readShellFavorites } from '../../../app/shell/favorites'
import { readShellRecent } from '../../../app/shell/recent'
import { AttendanceReviewWorkspace } from './AttendanceReviewWorkspace'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('AttendanceReviewWorkspace', () => {
  it('opens the exception workspace when the hash targets exceptions', () => {
    window.localStorage.removeItem('phoenixhrms.shell.favorites')

    renderWithProviders(<AttendanceReviewWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/attendance/operational-review#exceptions'],
    })

    expect(screen.getByRole('tab', { name: /exception records/i })).toHaveAttribute('aria-selected', 'true')
  })

  it('pins the active exception workspace from the header', async () => {
    window.localStorage.removeItem('phoenixhrms.shell.favorites')
    const user = userEvent.setup()

    renderWithProviders(<AttendanceReviewWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/attendance/operational-review#exceptions'],
    })

    await user.click(screen.getByRole('button', { name: /^pin workspace$/i }))

    expect(readShellFavorites()?.some((item) => item.path === '/attendance/operational-review#exceptions')).toBe(true)
    expect(screen.getByRole('button', { name: /^pinned$/i })).toBeInTheDocument()
  })

  it('reopens a specific correction when the hash targets a correction record', async () => {
    window.localStorage.removeItem('phoenixhrms.shell.recent')

    renderWithProviders(<AttendanceReviewWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/attendance/operational-review#correction-9301'],
    })

    expect(await screen.findByRole('dialog', { name: /review attendance correction/i })).toBeInTheDocument()
    expect(screen.getByText(/kabir malik/i)).toBeInTheDocument()
    await waitFor(() => {
      expect(readShellRecent()?.[0]?.label).toMatch(/attendance correction · pending/i)
    })
  })

  it('keeps manager review scoped to team exceptions only', () => {
    renderWithProviders(<AttendanceReviewWorkspace />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/attendance'],
    })

    expect(screen.getByText(/^team$/i)).toBeInTheDocument()
    expect(screen.getByText(/managers only see their direct-report exception queue/i)).toBeInTheDocument()
    expect(screen.queryByRole('button', { name: /kabir malik/i })).not.toBeInTheDocument()
    expect(screen.getAllByRole('button', { name: /rohit iyer/i }).length).toBeGreaterThan(0)
  })

  it('lets a tenant admin approve a pending correction and removes it from the pending queue', async () => {
    window.localStorage.removeItem('phoenixhrms.command-center.events')
    const user = userEvent.setup()

    renderWithProviders(<AttendanceReviewWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/attendance'],
    })

    await user.click(screen.getByRole('button', { name: /kabir malik/i }))
    const reviewDialog = screen.getByRole('dialog', { name: /review attendance correction/i })
    await user.click(within(reviewDialog).getByRole('button', { name: /approve correction/i }))

    const confirmDialog = screen.getByRole('alertdialog', { name: /approve this correction\?/i })
    await user.click(within(confirmDialog).getByRole('button', { name: /approve correction/i }))

    expect(await screen.findByText(/correction approved/i)).toBeInTheDocument()
    expect(readCommandCenterEvents().activities[0]?.title).toMatch(/kabir malik correction approved/i)
    expect(readCommandCenterEvents().alerts.find((item) => item.module === 'attendance' && item.id === 'pending-corrections')?.title).toMatch(/1 correction request\(s\) need a decision/i)

    await user.click(screen.getByRole('tab', { name: /pending/i }))

    expect(screen.queryByRole('button', { name: /kabir malik/i })).not.toBeInTheDocument()
    expect(screen.getByRole('button', { name: /rohit iyer/i })).toBeInTheDocument()
  })

  it('surfaces rejected decisions with recorded reviewer history', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AttendanceReviewWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/attendance'],
    })

    await user.click(screen.getByRole('tab', { name: /rejected/i }))

    expect(screen.getByRole('button', { name: /naina kapoor/i })).toBeInTheDocument()
    await user.click(screen.getByRole('button', { name: /naina kapoor/i }))
    const reviewDialog = screen.getByRole('dialog', { name: /review attendance correction/i })

    expect(
      within(reviewDialog).getByText(/rejected because the proposed arrival time could not be validated/i),
    ).toBeInTheDocument()
    expect(within(reviewDialog).getByText(/workflow history/i)).toBeInTheDocument()
  })
})
