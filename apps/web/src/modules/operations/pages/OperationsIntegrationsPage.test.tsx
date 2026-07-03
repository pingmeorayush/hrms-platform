import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('OperationsIntegrationsPage', () => {
  it('retries a failed sync job in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/operations/integrations'],
    })

    expect(await screen.findByRole('heading', { name: /integration operations/i })).toBeInTheDocument()

    const failedRow = screen
      .getAllByText(/EMP-1005/i)
      .map((element) => element.closest('tr'))
      .find((row): row is HTMLTableRowElement => row instanceof HTMLTableRowElement)
    if (!failedRow) {
      throw new Error('Expected to find the failed sync-job row.')
    }

    await user.click(within(failedRow).getByRole('button', { name: /^retry$/i }))

    expect(await screen.findByText(/sync job retried successfully/i)).toBeInTheDocument()
    expect(within(failedRow).getByText(/retried/i)).toBeInTheDocument()
  })

  it('reviews and processes a queued sync job in demo mode', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/operations/integrations'],
    })

    expect(await screen.findByRole('heading', { name: /integration operations/i })).toBeInTheDocument()

    const queuedRow = screen.getByText(/EMP-1012/i).closest('tr')
    expect(queuedRow).not.toBeNull()

    await user.click(within(queuedRow as HTMLTableRowElement).getByRole('button', { name: /review/i }))
    expect(await screen.findByRole('button', { name: /process selected job/i })).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /process selected job/i }))

    expect(await screen.findByText(/sync job processed successfully/i)).toBeInTheDocument()
    expect(within(queuedRow as HTMLTableRowElement).getByText(/completed/i)).toBeInTheDocument()
    expect(screen.getByText(/queued job completed/i)).toBeInTheDocument()
  })
})
