import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { VisibilityWorkbench } from './VisibilityWorkbench'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('VisibilityWorkbench', () => {
  it('shows governance actions for a platform admin persona', async () => {
    const user = userEvent.setup()

    renderWithProviders(<VisibilityWorkbench />, {
      accessState: { demoPersona: 'platformAdmin' },
      initialEntries: ['/foundation'],
    })

    expect(screen.getByRole('link', { name: /access control/i })).toBeInTheDocument()

    await user.click(screen.getByRole('tab', { name: /governance and security/i }))

    expect(screen.getByText('Create Role')).toBeInTheDocument()
    expect(screen.getByText('Review Audit Logs')).toBeInTheDocument()
  })

  it('hides protected admin navigation and actions for an employee persona', () => {
    renderWithProviders(<VisibilityWorkbench />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/foundation'],
    })

    expect(screen.queryByRole('link', { name: /access control/i })).not.toBeInTheDocument()
    expect(screen.queryByText('Create Role')).not.toBeInTheDocument()
    expect(screen.getByRole('link', { name: /notification center/i })).toBeInTheDocument()
  })

  it('keeps overview summary sections off route-specific pages', () => {
    renderWithProviders(<VisibilityWorkbench />, {
      accessState: { demoPersona: 'platformAdmin' },
      initialEntries: ['/workflows'],
    })

    expect(screen.getAllByText('Workflow Console').length).toBeGreaterThan(0)
    expect(screen.queryByText('Visible navigation')).not.toBeInTheDocument()
    expect(screen.queryByText('Contract diagnostics')).not.toBeInTheDocument()
  })

  it('switches action groups through overview tabs', async () => {
    const user = userEvent.setup()

    renderWithProviders(<VisibilityWorkbench />, {
      accessState: { demoPersona: 'platformAdmin' },
      initialEntries: ['/foundation'],
    })

    expect(screen.getByRole('tab', { name: /workflow administration/i })).toHaveAttribute(
      'aria-selected',
      'true',
    )
    expect(screen.getByText('Create Workflow')).toBeInTheDocument()

    await user.click(screen.getByRole('tab', { name: /governance and security/i }))

    expect(screen.getByRole('tab', { name: /governance and security/i })).toHaveAttribute(
      'aria-selected',
      'true',
    )
    expect(screen.getByText('Create Role')).toBeInTheDocument()
    expect(screen.queryByText('Create Workflow')).not.toBeInTheDocument()
  })
})
