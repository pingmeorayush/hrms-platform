import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AppRoutes } from '../../../app/routes/AppRoutes'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('SelfServiceProfilePage', () => {
  it('lets an employee demo persona save personal regional overrides', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/self-service/profile'],
    })

    expect(await screen.findByRole('heading', { name: /my profile/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /regional preferences/i })).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /^regional preferences$/i }))

    const dialog = screen.getByRole('dialog', { name: /regional preferences/i })
    const dialogScoped = within(dialog)

    await user.click(dialogScoped.getByRole('combobox', { name: /^locale$/i }))
    await user.click(await screen.findByRole('option', { name: 'en-US (United States)' }))

    await user.click(dialogScoped.getByRole('combobox', { name: /^currency$/i }))
    await user.click(await screen.findByRole('option', { name: 'USD (United States)' }))

    await user.click(dialogScoped.getByRole('combobox', { name: /clock format/i }))
    await user.click(await screen.findByRole('option', { name: '12-hour' }))

    await user.clear(dialogScoped.getByLabelText(/timezone/i))
    await user.type(dialogScoped.getByLabelText(/timezone/i), 'America/New_York')
    await user.click(dialogScoped.getByRole('button', { name: /save preferences/i }))

    expect(await screen.findByText(/regional preferences updated successfully/i)).toBeInTheDocument()
    expect(screen.getByText('America/New_York')).toBeInTheDocument()
    expect(screen.getByText('USD')).toBeInTheDocument()
    expect(screen.getAllByText(/personal override/i).length).toBeGreaterThan(0)
  })

  it('still exposes regional preferences when no employee profile is linked', async () => {
    renderWithProviders(<AppRoutes />, {
      accessState: { demoPersona: 'itOperator' },
      initialEntries: ['/self-service/profile'],
    })

    expect(await screen.findByRole('heading', { name: /my profile/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /regional preferences/i })).toBeInTheDocument()
    expect(screen.getByRole('heading', { name: /no linked employee profile/i })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /edit regional preferences/i })).toBeInTheDocument()
  })
})
