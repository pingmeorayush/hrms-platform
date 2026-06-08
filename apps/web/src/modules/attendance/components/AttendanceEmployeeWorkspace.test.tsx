import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { AttendanceEmployeeWorkspace } from './AttendanceEmployeeWorkspace'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('AttendanceEmployeeWorkspace', () => {
  it('supports employee check-in and check-out with clear capture feedback', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AttendanceEmployeeWorkspace />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/attendance/my-attendance/capture'],
    })

    expect(screen.getByRole('heading', { name: /check in \/ check out/i })).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /check in/i }))

    expect(await screen.findByText(/check-in recorded/i)).toBeInTheDocument()
    expect(screen.getByText(/check-in is already captured for today/i)).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /check out/i }))

    expect(await screen.findByText(/check-out recorded/i)).toBeInTheDocument()
    expect(screen.getByText(/today's attendance is complete/i)).toBeInTheDocument()
  })

  it('shows an empty history state when the selected filter window has no records', async () => {
    const user = userEvent.setup()

    renderWithProviders(<AttendanceEmployeeWorkspace />, {
      accessState: { demoPersona: 'employee' },
      initialEntries: ['/attendance/my-attendance/history'],
    })

    await user.type(screen.getByLabelText('Date from'), '2027-01-01')
    await user.type(screen.getByLabelText('Date to'), '2027-01-31')
    await user.click(screen.getByRole('button', { name: /apply filters/i }))

    expect(
      await screen.findByText(/no attendance records match this window/i),
    ).toBeInTheDocument()
  })

  it('keeps capture read only for a manager persona while still exposing history', () => {
    renderWithProviders(<AttendanceEmployeeWorkspace />, {
      accessState: { demoPersona: 'manager' },
      initialEntries: ['/attendance/my-attendance/capture'],
    })

    expect(screen.getByText(/attendance capture requires `attendance.create`/i)).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /check in/i })).toBeDisabled()
    expect(screen.getByRole('tab', { name: /check in \/ out/i })).toBeInTheDocument()
    expect(screen.getByRole('tab', { name: /history/i })).toBeInTheDocument()
  })
})
