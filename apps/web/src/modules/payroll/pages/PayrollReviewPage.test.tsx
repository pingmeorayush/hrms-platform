import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('PayrollReviewPage', () => {
  it('shows payroll summaries and review metrics for a tenant admin session', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/payroll/review'],
    })

    expect(await screen.findByRole('heading', { name: /payroll review/i })).toBeInTheDocument()
    expect(screen.getByText(/variance watchlist/i)).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /run summaries/i })).toBeInTheDocument()
    expect(screen.getByText(/may 2026 preparation run/i)).toBeInTheDocument()
    expect(screen.getByRole('link', { name: /open run console/i })).toBeInTheDocument()
  })

  it('surfaces exception and variance review states in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/payroll/review'],
    })

    expect(await screen.findByRole('heading', { name: /payroll review/i })).toBeInTheDocument()

    await user.click(screen.getByRole('tab', { name: /exceptions/i }))
    expect(screen.getByText(/attendance finalization/i)).toBeInTheDocument()
    expect(screen.getByText(/net salary cannot be negative/i)).toBeInTheDocument()

    await user.click(screen.getByRole('tab', { name: /variances/i }))
    expect(screen.getAllByText(/needs review/i).length).toBeGreaterThan(0)
    expect(
      screen.getAllByText(/awaiting calculation data before variance comparison can be trusted/i).length,
    ).toBeGreaterThan(0)
  })
})
