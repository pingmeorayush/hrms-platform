import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('SelfServiceDocumentsPage', () => {
  it('supports pending acknowledgement and download flows for the employee demo persona', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/self-service/documents'],
    })

    expect(await screen.findByRole('heading', { name: /my documents/i })).toBeInTheDocument()
    expect(screen.getByText(/pending acknowledgement/i)).toBeInTheDocument()

    await user.click(screen.getAllByRole('button', { name: /download/i })[0])
    expect(await screen.findByText(/demo download ready for/i)).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /acknowledge policy/i }))
    expect(await screen.findByText(/policy acknowledgement recorded successfully/i)).toBeInTheDocument()
    expect(screen.getByText(/all policy items acknowledged/i)).toBeInTheDocument()
  })

  it('resolves self-service documents for a linked tenant-admin demo persona', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'tenantAdmin' },
      initialEntries: ['/self-service/documents'],
    })

    expect(await screen.findByRole('heading', { name: /my documents/i })).toBeInTheDocument()
    expect(screen.getAllByText(/employee file/i).length).toBeGreaterThan(0)
    expect(screen.queryByRole('heading', { name: /no linked employee profile/i })).not.toBeInTheDocument()
  })

  it('shows the empty state when the current session has no linked employee profile', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'itOperator' },
      initialEntries: ['/self-service/documents'],
    })

    expect(await screen.findByRole('heading', { name: /no linked employee profile/i })).toBeInTheDocument()
    expect(screen.queryByRole('heading', { name: /my documents/i })).not.toBeInTheDocument()
  })
})
