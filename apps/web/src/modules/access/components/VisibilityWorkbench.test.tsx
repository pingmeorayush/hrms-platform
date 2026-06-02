import { screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import { VisibilityWorkbench } from './VisibilityWorkbench'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('VisibilityWorkbench', () => {
  it('shows governance actions for a platform admin persona', () => {
    renderWithProviders(<VisibilityWorkbench />, {
      accessState: { demoPersona: 'platformAdmin' },
      initialEntries: ['/foundation'],
    })

    expect(screen.getByRole('link', { name: /access control/i })).toBeInTheDocument()
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
})
