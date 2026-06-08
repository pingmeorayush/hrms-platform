import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { Route, Routes } from 'react-router-dom'
import {
  EmployeeDetailIndexRedirect,
  EmployeeDetailShell,
  EmployeeDocumentsRouteSection,
  EmployeeHistoryRouteSection,
  EmployeeLifecycleRouteSection,
  EmployeeOnboardingRouteSection,
  EmployeeProfileRouteSection,
} from './EmployeeDetailShell'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

describe('EmployeeDetailShell', () => {
  it('lets a tenant admin trigger a demo transfer and open document controls', async () => {
    const user = userEvent.setup()

    renderWithProviders(
      <Routes>
        <Route path="/employees/:employeeId" element={<EmployeeDetailShell />}>
          <Route index element={<EmployeeDetailIndexRedirect />} />
          <Route path="profile" element={<EmployeeProfileRouteSection />} />
          <Route path="lifecycle" element={<EmployeeLifecycleRouteSection />} />
          <Route path="onboarding" element={<EmployeeOnboardingRouteSection />} />
          <Route path="documents" element={<EmployeeDocumentsRouteSection />} />
          <Route path="history" element={<EmployeeHistoryRouteSection />} />
        </Route>
      </Routes>,
      {
        accessState: { demoPersona: 'tenantAdmin' },
        initialEntries: ['/employees/1005/profile'],
      },
    )

    await user.click(screen.getByRole('link', { name: /lifecycle/i }))
    await user.click(screen.getAllByRole('button', { name: /open modal/i })[0])
    const transferDialog = screen.getByRole('dialog', { name: /transfer employee/i })
    await user.click(within(transferDialog).getByRole('button', { name: /record transfer/i }))

    const confirmDialog = screen.getByRole('alertdialog', { name: /confirm employee transfer\?/i })
    await user.click(within(confirmDialog).getByRole('button', { name: /confirm transfer/i }))

    expect(await screen.findByText(/employee transferred/i)).toBeInTheDocument()

    await user.click(screen.getByRole('link', { name: /documents/i }))

    expect(screen.getByRole('heading', { name: /employee documents/i })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /upload in modal/i })).toBeInTheDocument()

    await user.click(screen.getByRole('link', { name: /audit history/i }))

    expect(screen.getByText(/employee · record · transferred/i)).toBeInTheDocument()
  }, 10000)

  it('keeps manager access read only and hides sensitive banking', async () => {
    const user = userEvent.setup()

    renderWithProviders(
      <Routes>
        <Route path="/employees/:employeeId" element={<EmployeeDetailShell />}>
          <Route index element={<EmployeeDetailIndexRedirect />} />
          <Route path="profile" element={<EmployeeProfileRouteSection />} />
          <Route path="lifecycle" element={<EmployeeLifecycleRouteSection />} />
          <Route path="onboarding" element={<EmployeeOnboardingRouteSection />} />
          <Route path="documents" element={<EmployeeDocumentsRouteSection />} />
          <Route path="history" element={<EmployeeHistoryRouteSection />} />
        </Route>
      </Routes>,
      {
        accessState: { demoPersona: 'manager' },
        initialEntries: ['/employees/1005/profile'],
      },
    )

    expect(screen.getByText(/sensitive banking is hidden/i)).toBeInTheDocument()

    await user.click(screen.getByRole('link', { name: /lifecycle/i }))

    expect(screen.getByText(/lifecycle actions are read only in this session/i)).toBeInTheDocument()
  })
})
