import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { FeaturePlaceholderPage } from './FeaturePlaceholderPage'
import { renderWithProviders } from '../../shared/testing/renderWithProviders'

describe('FeaturePlaceholderPage', () => {
  it('keeps queued modules in the same tabbed collection pattern as live pages', async () => {
    const user = userEvent.setup()

    renderWithProviders(
      <FeaturePlaceholderPage
        eyebrow="Queued release slice"
        title="Attendance UI is planned after employee operations"
        description="Shift and policy admin will reuse the shared shell and collection pattern."
        plannedStories={['S03-008', 'S03-009']}
        nextStep="Start attendance once the employee workflows are stable."
      />,
      {
        initialEntries: ['/attendance'],
      },
    )

    expect(screen.getByRole('tab', { name: /planned stories/i })).toHaveAttribute('aria-selected', 'true')
    expect(screen.getByText('S03-008')).toBeInTheDocument()

    await user.click(screen.getByRole('tab', { name: /release path/i }))

    expect(screen.getByRole('tab', { name: /release path/i })).toHaveAttribute('aria-selected', 'true')
    expect(screen.getByText('Foundation workspace')).toBeInTheDocument()
    expect(screen.getByRole('link', { name: /go back to foundation/i })).toHaveAttribute('href', '/foundation')
    expect(screen.queryByText('S03-008')).not.toBeInTheDocument()
  })
})
