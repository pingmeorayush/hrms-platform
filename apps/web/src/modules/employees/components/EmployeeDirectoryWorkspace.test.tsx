import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { EmployeeDirectoryWorkspace } from './EmployeeDirectoryWorkspace'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('EmployeeDirectoryWorkspace', () => {
  it('filters the demo directory by employee search', async () => {
    const user = userEvent.setup()

    renderWithProviders(<EmployeeDirectoryWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/employees'],
    })

    expect(screen.getByRole('heading', { name: /employee directory/i })).toBeInTheDocument()

    await user.type(screen.getByLabelText('Search'), 'Kabir')

    expect((await screen.findAllByText('Kabir Malik')).length).toBeGreaterThan(0)
    expect(screen.queryByText(/EMP-1001/i)).not.toBeInTheDocument()
    expect(screen.getByRole('link', { name: /^open$/i })).toBeInTheDocument()
  }, 15000)

  it('lets users pin an employee profile from row actions', async () => {
    const user = userEvent.setup()

    renderWithProviders(<EmployeeDirectoryWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/employees/directory'],
    })

    await user.click(screen.getAllByRole('button', { name: /more actions for/i })[0])
    expect(await screen.findByRole('menuitem', { name: /pin profile workspace/i })).toBeInTheDocument()

    await user.click(screen.getByRole('menuitem', { name: /pin profile workspace/i }))

    await user.click(screen.getAllByRole('button', { name: /more actions for/i })[0])
    expect(await screen.findByRole('menuitem', { name: /unpin profile workspace/i })).toBeInTheDocument()
  }, 15000)

  it('lets users pin the current employee workspace from the header', async () => {
    window.localStorage.removeItem('phoenixhrms.shell.favorites')
    const user = userEvent.setup()

    renderWithProviders(<EmployeeDirectoryWorkspace />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/employees/directory'],
    })

    await user.click(screen.getByRole('button', { name: /^pin workspace$/i }))

    expect(screen.getByRole('button', { name: /^pinned$/i })).toBeInTheDocument()
  }, 15000)
})
