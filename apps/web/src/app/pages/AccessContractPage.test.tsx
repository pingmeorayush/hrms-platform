import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { readShellFavorites } from '../shell/favorites'
import { AccessContractPage } from './AccessContractPage'
import { renderWithProviders } from '../../shared/testing/renderWithProviders'

describe('AccessContractPage', () => {
  it('opens the action workspace when the hash targets actions', () => {
    window.localStorage.removeItem('phoenixhrms.shell.favorites')

    renderWithProviders(<AccessContractPage />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/access#actions'],
    })

    expect(screen.getByRole('tab', { name: /^actions$/i })).toHaveAttribute('aria-selected', 'true')
    expect(screen.getAllByText('Create Role').length).toBeGreaterThan(0)
  })

  it('pins the active access workspace from the header', async () => {
    window.localStorage.removeItem('phoenixhrms.shell.favorites')
    const user = userEvent.setup()

    renderWithProviders(<AccessContractPage />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/access#actions'],
    })

    await user.click(screen.getByRole('button', { name: /^pin workspace$/i }))

    expect(readShellFavorites()?.some((item) => item.path === '/access#actions')).toBe(true)
    expect(screen.getByRole('button', { name: /^pinned$/i })).toBeInTheDocument()
  })

  it('switches between navigation, action, and diagnostic contract views', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AccessContractPage />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/access'],
    })

    expect(screen.getByRole('tab', { name: /^routes$/i })).toHaveAttribute('aria-selected', 'true')
    expect(screen.getAllByText('Foundation Overview').length).toBeGreaterThan(0)

    await user.click(screen.getByRole('tab', { name: /^actions$/i }))

    expect(screen.getByRole('tab', { name: /^actions$/i })).toHaveAttribute('aria-selected', 'true')
    expect(screen.getAllByText('Create Role').length).toBeGreaterThan(0)
    expect(screen.queryByText('Foundation Overview')).not.toBeInTheDocument()

    await user.click(screen.getByRole('tab', { name: /diagnostics/i }))

    expect(screen.getByRole('tab', { name: /diagnostics/i })).toHaveAttribute('aria-selected', 'true')
    expect(screen.getAllByText('Backend enforcement').length).toBeGreaterThan(0)
    expect(screen.getAllByText(/server enforced/i).length).toBeGreaterThan(0)
  })
})
