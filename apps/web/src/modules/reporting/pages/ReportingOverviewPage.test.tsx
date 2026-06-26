import { render, screen } from '@testing-library/react'
import type { ReactElement } from 'react'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it, vi } from 'vitest'
import { dashboardRouteMap } from '../config'
import {
  ReportingOverviewPage,
  ReportingTeamPage,
} from './ReportingOverviewPage'
import type { ReportingWorkspaceData } from '../types'

let mockWorkspace: {
  data: ReportingWorkspaceData | null
  isLoading: boolean
  error: Error | null
  source: 'demo' | 'live'
  canViewReporting: boolean
  accessibleDashboardKeys: Array<keyof typeof dashboardRouteMap>
} = {
  data: null,
  isLoading: false,
  error: null,
  source: 'demo',
  canViewReporting: true,
  accessibleDashboardKeys: [],
}

vi.mock('./useReportingRouteWorkspace', () => ({
  useReportingRouteWorkspace: () => mockWorkspace,
}))

describe('ReportingOverviewPage', () => {
  function renderWithRouter(element: ReactElement) {
    return render(<MemoryRouter>{element}</MemoryRouter>)
  }

  it('shows a loading state while the reporting workspace resolves', () => {
    mockWorkspace = {
      data: null,
      isLoading: true,
      error: null,
      source: 'live',
      canViewReporting: true,
      accessibleDashboardKeys: [],
    }

    renderWithRouter(<ReportingOverviewPage />)

    expect(screen.getByRole('heading', { name: /loading reporting command center/i })).toBeInTheDocument()
  })

  it('shows an empty state when no governed dashboard is available in scope', () => {
    mockWorkspace = {
      data: { dashboards: {}, failures: [], activity: [] },
      isLoading: false,
      error: null,
      source: 'demo',
      canViewReporting: true,
      accessibleDashboardKeys: [],
    }

    renderWithRouter(<ReportingOverviewPage />)

    expect(screen.getByRole('heading', { name: /no dashboards in scope yet/i })).toBeInTheDocument()
  })

  it('surfaces stale dashboard posture in the command center', () => {
    mockWorkspace = {
      data: {
        dashboards: {
          leadership_overview: {
            dashboard: {
              key: 'leadership_overview',
              name: 'Leadership overview',
              persona: 'leadership',
              description: 'Executive operating dashboard.',
            },
            snapshot: {
              id: 1,
              cache_hit: true,
              generated_at: new Date().toISOString(),
              expires_at: new Date().toISOString(),
              scope_signature: 'scope',
              source_signature: 'source',
            },
            freshness: {
              generated_at: new Date().toISOString(),
              expires_at: new Date().toISOString(),
              expectation_minutes: 240,
              is_stale: true,
            },
            widgets: [],
          },
        },
        failures: [],
        activity: [],
      },
      isLoading: false,
      error: null,
      source: 'demo',
      canViewReporting: true,
      accessibleDashboardKeys: ['leadership_overview'],
    }

    renderWithRouter(<ReportingOverviewPage />)

    expect(screen.getByText(/leadership overview is stale/i)).toBeInTheDocument()
    expect(screen.getAllByText(/stale/i).length).toBeGreaterThan(0)
  })

  it('shows masked-data posture for the manager dashboard', () => {
    mockWorkspace = {
      data: {
        dashboards: {
          manager_overview: {
            dashboard: {
              key: 'manager_overview',
              name: 'Manager overview',
              persona: 'manager',
              description: 'Team-scoped dashboard.',
            },
            snapshot: {
              id: 2,
              cache_hit: true,
              generated_at: new Date().toISOString(),
              expires_at: new Date().toISOString(),
              scope_signature: 'scope',
              source_signature: 'source',
            },
            freshness: {
              generated_at: new Date().toISOString(),
              expires_at: new Date().toISOString(),
              expectation_minutes: 60,
              is_stale: false,
            },
            widgets: [
              {
                key: 'team_headcount_card',
                name: 'Team headcount',
                widget_type: 'metric',
                description: 'Team headcount',
                status: 'ready',
                blocked_reason: null,
                value: 14,
                unit: 'count',
                drilldown: null,
                governance: {
                  kpi: {
                    key: 'active_headcount',
                    source_references: [],
                  },
                  dataset: {
                    key: 'workforce_headcount_snapshot',
                    masking_posture: { employee_email: 'masked' },
                  },
                },
                freshness: {
                  generated_at: new Date().toISOString(),
                  expires_at: new Date().toISOString(),
                  expectation_minutes: 60,
                  is_stale: false,
                },
              },
            ],
          },
        },
        failures: [],
        activity: [],
      },
      isLoading: false,
      error: null,
      source: 'demo',
      canViewReporting: true,
      accessibleDashboardKeys: ['manager_overview'],
    }

    renderWithRouter(<ReportingTeamPage />)

    expect(screen.getAllByText(/masked field rules apply/i).length).toBeGreaterThan(0)
    expect(screen.getByText(/masked data state is active/i)).toBeInTheDocument()
  })

  it('shows a permission-style empty state when the dashboard is outside the current scope', () => {
    mockWorkspace = {
      data: {
        dashboards: {},
        failures: [],
        activity: [],
      },
      isLoading: false,
      error: null,
      source: 'demo',
      canViewReporting: true,
      accessibleDashboardKeys: ['recruiter_overview'],
    }

    renderWithRouter(<ReportingTeamPage />)

    expect(screen.getByRole('heading', { name: /team reporting unavailable/i })).toBeInTheDocument()
    expect(
      screen.getByText(/this persona-specific dashboard is not available in the current reporting scope/i),
    ).toBeInTheDocument()
  })
})
