import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { FoundationOverviewPage } from './FoundationOverviewPage'
import { renderWithProviders } from '../../shared/testing/renderWithProviders'

describe('FoundationOverviewPage', () => {
  it('switches the workspace collection between visible and restricted views', async () => {
    const user = userEvent.setup()

    renderWithProviders(<FoundationOverviewPage />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/foundation'],
    })

    expect(screen.getByRole('tab', { name: /visible now/i })).toHaveAttribute('aria-selected', 'true')
    expect(screen.getByText('Organization')).toBeInTheDocument()
    expect(screen.getByText('Employees')).toBeInTheDocument()
    expect(screen.getByText('Leave')).toBeInTheDocument()
    expect(screen.queryByText('Access')).not.toBeInTheDocument()

    await user.click(screen.getByRole('tab', { name: /restricted here/i }))

    expect(screen.getByRole('tab', { name: /restricted here/i })).toHaveAttribute('aria-selected', 'true')
    expect(screen.getByText('Access')).toBeInTheDocument()
    expect(screen.getByText(/needs a broader role/i)).toBeInTheDocument()
    expect(screen.queryByText('Organization')).not.toBeInTheDocument()
    expect(screen.queryByText('Employees')).not.toBeInTheDocument()
    expect(screen.queryByText('Attendance')).not.toBeInTheDocument()
    expect(screen.queryByText('Leave')).not.toBeInTheDocument()
  })
})
