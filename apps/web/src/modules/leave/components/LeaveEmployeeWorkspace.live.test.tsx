import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { screen } from '@testing-library/react'
import { LeaveEmployeeWorkspace } from './LeaveEmployeeWorkspace'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'

vi.mock('../../access/hooks/useAccessSnapshot', () => ({
  useAccessSnapshot: () => ({
    source: 'live' as const,
    isLoading: false,
    error: null,
    snapshot: {
      user: {
        id: 44,
        name: 'Employee Viewer',
        initials: 'EV',
        email: 'employee@phoenixhrms.test',
        employee: {
          id: 1005,
          employee_code: 'EMP-1005',
          full_name: 'Employee Viewer',
          email: 'employee@phoenixhrms.test',
        },
        roles: ['employee'],
        permissions: ['leave.view', 'leave.request'],
        tenant: {
          company_id: 1,
          company_name: 'Phoenix Demo Company',
          subscription_plan: 'enterprise',
          timezone: 'Asia/Kolkata',
          currency: 'INR',
        },
      },
      visibility: {
        navigation: [],
        action_groups: [],
        meta: {
          visible_navigation_count: 0,
          hidden_navigation_count: 0,
          backend_enforcement_note: 'Backend remains the source of truth.',
        },
      },
    },
  }),
}))

describe('LeaveEmployeeWorkspace live mode', () => {
  beforeEach(() => {
    vi.stubGlobal(
      'fetch',
      vi.fn(async (input: string | URL) => {
        const url = String(input)

        if (url.endsWith('/leave/types')) {
          return jsonResponse({
            data: [
              {
                id: 1,
                code: 'AL',
                name: 'Annual Leave',
                category: 'earned',
                description: 'Primary annual leave bucket.',
                is_paid: true,
                requires_approval: true,
                allows_half_day: true,
                color_token: '#0972d3',
                status: 'active',
                created_at: '2026-06-01T09:00:00Z',
                updated_at: '2026-06-03T09:00:00Z',
              },
            ],
          })
        }

        if (url.endsWith('/leave/policies')) {
          return jsonResponse({
            data: [
              {
                id: 10,
                leave_type_id: 1,
                leave_type: {
                  id: 1,
                  code: 'AL',
                  name: 'Annual Leave',
                  category: 'earned',
                  description: 'Primary annual leave bucket.',
                  is_paid: true,
                  requires_approval: true,
                  allows_half_day: true,
                  color_token: '#0972d3',
                  status: 'active',
                  created_at: '2026-06-01T09:00:00Z',
                  updated_at: '2026-06-03T09:00:00Z',
                },
                version: 2,
                scope_key: 'company:1',
                annual_allowance_days: 18,
                opening_balance_days: 2,
                accrual_frequency: 'monthly',
                carry_forward_limit_days: 8,
                encashment_limit_days: 5,
                max_consecutive_days: 10,
                min_notice_days: 2,
                requires_documentation_after_days: 4,
                applicable_department: null,
                applicable_location: null,
                eligibility_rule: {
                  employment_types: [],
                  employment_statuses: [],
                  genders: [],
                  marital_statuses: [],
                  minimum_tenure_days: null,
                },
                status: 'active',
                created_at: '2026-06-01T09:00:00Z',
                updated_at: '2026-06-03T09:00:00Z',
              },
            ],
          })
        }

        if (url.endsWith('/leave/balances')) {
          return jsonResponse({
            data: [
              {
                id: 5001,
                employee_id: 1005,
                employee: {
                  id: 1005,
                  employee_code: 'EMP-1005',
                  full_name: 'Employee Viewer',
                  email: 'employee@phoenixhrms.test',
                },
                leave_type: {
                  id: 1,
                  code: 'AL',
                  name: 'Annual Leave',
                  category: 'earned',
                  description: 'Primary annual leave bucket.',
                  is_paid: true,
                  requires_approval: true,
                  allows_half_day: true,
                  color_token: '#0972d3',
                  status: 'active',
                  created_at: '2026-06-01T09:00:00Z',
                  updated_at: '2026-06-03T09:00:00Z',
                },
                leave_policy_id: 10,
                policy_version: 2,
                available_days: 9,
                booked_days: 2,
                used_days: 5,
                accrued_days: 11,
                carry_forward_days: 2,
                projected_encashable_days: 0,
                current_period_start: '2026-01-01',
                current_period_end: '2026-12-31',
                last_calculation_hash: 'hash-1',
                status: 'active',
                updated_at: '2026-06-03T09:00:00Z',
              },
            ],
          })
        }

        if (url.includes('/leave/requests?')) {
          return jsonResponse({
            data: {
              items: [
                {
                  id: 201,
                  employee: {
                    id: 1005,
                    employee_code: 'EMP-1005',
                    full_name: 'Employee Viewer',
                    email: 'employee@phoenixhrms.test',
                  },
                  department: {
                    id: 11,
                    code: 'PEOPLE',
                    name: 'People Operations',
                    description: null,
                    status: 'active',
                    created_at: '2026-06-01T09:00:00Z',
                    updated_at: '2026-06-01T09:00:00Z',
                  },
                  location: null,
                  leave_type: {
                    id: 1,
                    code: 'AL',
                    name: 'Annual Leave',
                    category: 'earned',
                    description: 'Primary annual leave bucket.',
                    is_paid: true,
                    requires_approval: true,
                    allows_half_day: true,
                    color_token: '#0972d3',
                    status: 'active',
                    created_at: '2026-06-01T09:00:00Z',
                    updated_at: '2026-06-03T09:00:00Z',
                  },
                  leave_policy_id: 10,
                  policy_version: 2,
                  workflow_instance_id: 9001,
                  workflow: null,
                  start_date: '2026-06-17',
                  end_date: '2026-06-19',
                  total_days: 3,
                  status: 'pending',
                  reason: 'Family travel and pre-booked time off.',
                  approver_comment: null,
                  can_cancel: true,
                  is_auto_approved: false,
                  attendance_sync_status: 'not_applicable',
                  attendance_synced_at: null,
                  approved_at: null,
                  rejected_at: null,
                  cancelled_at: null,
                  created_at: '2026-06-03T09:00:00Z',
                  updated_at: '2026-06-03T09:00:00Z',
                },
              ],
              meta: {
                page: 1,
                per_page: 100,
                total: 1,
                last_page: 1,
              },
            },
          })
        }

        if (url.endsWith('/organization/company-profile')) {
          return jsonResponse({
            data: {
              id: 1,
              uuid: 'company-1',
              name: 'Phoenix Demo Company',
              slug: 'phoenix-demo-company',
              status: 'active',
              subscription_plan: 'enterprise',
              timezone: 'Asia/Kolkata',
              currency: 'INR',
              created_at: '2026-06-01T09:00:00Z',
              updated_at: '2026-06-03T09:00:00Z',
            },
          })
        }

        if (
          url.endsWith('/organization/departments') ||
          url.endsWith('/organization/designations') ||
          url.endsWith('/organization/locations') ||
          url.endsWith('/organization/cost-centers')
        ) {
          return jsonResponse({ data: [] })
        }

        throw new Error(`Unexpected fetch URL in leave live test: ${url}`)
      }),
    )
  })

  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('loads the live leave workspace instead of showing the old blocked-state error', async () => {
    renderWithProviders(<LeaveEmployeeWorkspace />, {
      accessState: {
        mode: 'live',
        token: 'live-token',
      },
      initialEntries: ['/leave/requests'],
    })

    expect(await screen.findByRole('heading', { name: /leave balances/i })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /request leave/i })).toBeInTheDocument()
    expect(await screen.findByText(/family travel and pre-booked time off\./i)).toBeInTheDocument()
    expect(screen.queryByText(/leave APIs are not published yet/i)).not.toBeInTheDocument()
  })
})

function jsonResponse(body: unknown, status = 200) {
  return new Response(JSON.stringify(body), {
    status,
    headers: {
      'Content-Type': 'application/json',
    },
  })
}
