import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { renderWithProviders } from '../../../shared/testing/renderWithProviders'
import { ReportingExplorerPage } from './ReportingExplorerPage'
import { ReportingExportsPage } from './ReportingExportsPage'
import { ReportingSubscriptionsPage } from './ReportingSubscriptionsPage'
import type {
  ReportingDatasetRecord,
  ReportingExportRecord,
  ReportingQueryResult,
  ReportingSavedViewRecord,
  ReportingSubscriptionRecord,
  ReportingWorkspaceData,
} from '../types'

let mockWorkspace: {
  data: ReportingWorkspaceData | null
  isLoading: boolean
  error: Error | null
  source: 'demo' | 'live'
  canViewReporting: boolean
  accessibleDashboardKeys: string[]
  pendingActionLabel: string | null
  lastActionMessage: string | null
  actionError: string | null
  actions: {
    queryDataset: ReturnType<typeof vi.fn>
    createSavedView: ReturnType<typeof vi.fn>
    archiveSavedView: ReturnType<typeof vi.fn>
    requestExport: ReturnType<typeof vi.fn>
    processExport: ReturnType<typeof vi.fn>
    downloadExport: ReturnType<typeof vi.fn>
    createSubscription: ReturnType<typeof vi.fn>
    updateSubscription: ReturnType<typeof vi.fn>
    revokeSubscription: ReturnType<typeof vi.fn>
    deliverSubscription: ReturnType<typeof vi.fn>
  }
} = {} as never

vi.mock('./useReportingRouteWorkspace', () => ({
  useReportingRouteWorkspace: () => mockWorkspace,
}))

const dataset: ReportingDatasetRecord = {
  id: 4101,
  key: 'workforce_headcount_snapshot',
  name: 'Workforce headcount snapshot',
  domain: 'workforce',
  description: 'Governed workforce roster reporting dataset.',
  source_references: [{ table: 'employees' }],
  grain: 'employee',
  approved_fields: [
    {
      key: 'employee_code',
      label: 'Employee code',
      type: 'string',
      description: null,
      sensitive: false,
      masking_strategy: 'none',
    },
    {
      key: 'employee_name',
      label: 'Employee name',
      type: 'string',
      description: null,
      sensitive: false,
      masking_strategy: 'none',
    },
    {
      key: 'employee_email',
      label: 'Employee email',
      type: 'string',
      description: null,
      sensitive: true,
      masking_strategy: 'partial',
    },
  ],
  approved_filters: [
    {
      key: 'department_name',
      label: 'Department',
      type: 'string',
      required: false,
      operators: ['eq', 'contains'],
    },
  ],
  drilldown_paths: [
    {
      key: 'employee_profile',
      label: 'Employee profile',
      target_dataset_key: 'workforce_headcount_snapshot',
      description: 'Open employee detail.',
      allowed_filter_keys: ['department_name'],
    },
  ],
  masking_posture: { employee_email: 'masked' },
  freshness_expectation_minutes: 60,
  governance: {
    version: 2,
    certification_status: 'certified',
    review_notes: null,
    reviewed_by: null,
    reviewed_at: new Date().toISOString(),
    certified_by: null,
    certified_at: new Date().toISOString(),
  },
  created_at: new Date().toISOString(),
  updated_at: new Date().toISOString(),
}

const savedView: ReportingSavedViewRecord = {
  id: 5101,
  view_uuid: 'saved-view-5101',
  name: 'Weekly workforce lens',
  description: 'Default workforce filter posture.',
  status: 'active',
  share: {
    scope: 'roles',
    shared_role_names: ['manager'],
  },
  dataset: {
    id: dataset.id,
    key: dataset.key,
    name: dataset.name,
    domain: dataset.domain,
  },
  owner: {
    id: 101,
    name: 'Aarav Nanda',
    email: 'aarav@example.com',
  },
  query: {
    filters: {
      department_name: 'Engineering',
    },
    filter_operators: {
      department_name: 'eq',
    },
    sort_by: 'employee_name',
    sort_direction: 'asc',
    drilldown_path: null,
  },
  presentation_preferences: {
    visible_columns: ['employee_code', 'employee_name'],
  },
  validation: {
    status: 'valid',
    reason: null,
  },
  created_at: new Date().toISOString(),
  updated_at: new Date().toISOString(),
}

function createQueryResult(overrides: Partial<ReportingQueryResult> = {}): ReportingQueryResult {
  return {
    dataset,
    items: [],
    meta: {
      page: 1,
      per_page: 25,
      total: 0,
      last_page: 1,
      sort_by: 'employee_name',
      sort_direction: 'asc',
      drilldown_path: null,
    },
    filters: {
      available: dataset.approved_filters,
      applied: {},
    },
    freshness: {
      generated_at: new Date().toISOString(),
      expectation_minutes: 60,
    },
    visibility: {
      masked_field_keys: ['employee_email'],
      hidden_field_keys: [],
      drilldown_keys: ['employee_profile'],
    },
    ...overrides,
  }
}

function createExport(overrides: Partial<ReportingExportRecord>): ReportingExportRecord {
  return {
    id: 6101,
    export_uuid: 'report-export-6101',
    status: 'queued',
    format: 'csv',
    execution_mode: 'async',
    delivery_target: 'requestor_download',
    dataset: {
      id: dataset.id,
      key: dataset.key,
      name: dataset.name,
      domain: dataset.domain,
    },
    requested_by: {
      id: 101,
      name: 'Aarav Nanda',
      email: 'aarav@example.com',
    },
    query: {
      filters: {},
      filter_operators: {},
      sort_by: 'employee_name',
      sort_direction: 'asc',
      drilldown_path: null,
    },
    counts: {
      estimated_row_count: 22,
      exported_row_count: null,
    },
    visibility: {
      masked_field_keys: ['employee_email'],
      hidden_field_keys: [],
      drilldown_keys: [],
    },
    freshness: {
      generated_at: new Date().toISOString(),
      expectation_minutes: 60,
    },
    file: {
      name: null,
      size_bytes: null,
      checksum_sha256: null,
      download_available: false,
      download_url: null,
    },
    retention: {
      expires_at: null,
      is_expired: false,
    },
    requested_at: new Date().toISOString(),
    started_at: null,
    completed_at: null,
    failed_at: null,
    notified_at: null,
    last_error: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
    ...overrides,
  }
}

function createSubscription(overrides: Partial<ReportingSubscriptionRecord>): ReportingSubscriptionRecord {
  return {
    id: 7101,
    subscription_uuid: 'report-subscription-7101',
    name: 'Monday workforce brief',
    description: 'Weekly workforce delivery.',
    status: 'blocked',
    owner: {
      id: 101,
      name: 'Aarav Nanda',
      email: 'aarav@example.com',
    },
    source: {
      dataset: {
        id: dataset.id,
        key: dataset.key,
        name: dataset.name,
        domain: dataset.domain,
      },
      saved_view: {
        id: savedView.id,
        view_uuid: savedView.view_uuid,
        name: savedView.name,
        status: savedView.status,
      },
    },
    delivery: {
      channel: 'in_app_notification',
      target: 'owner_only',
      export_format: 'csv',
    },
    schedule: {
      frequency: 'weekly',
      timezone: 'Asia/Kolkata',
      config: {
        time_of_day: '09:00',
        weekday: 1,
      },
      next_delivery_at: null,
    },
    query: {
      filters: savedView.query.filters,
      filter_operators: savedView.query.filter_operators,
      sort_by: savedView.query.sort_by,
      sort_direction: savedView.query.sort_direction,
      drilldown_path: savedView.query.drilldown_path,
    },
    validation: {
      status: 'blocked',
      reason: 'The source saved view was archived.',
    },
    last_delivery: {
      status: 'blocked',
      error: 'saved_view_archived',
      delivered_at: null,
      report_export_id: null,
    },
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
    ...overrides,
  }
}

function createWorkspaceData(): ReportingWorkspaceData {
  return {
    dashboards: {},
    failures: [],
    activity: [],
    datasets: [dataset],
    savedViews: [savedView],
    exports: [],
    subscriptions: [],
  }
}

beforeEach(() => {
  mockWorkspace = {
    data: createWorkspaceData(),
    isLoading: false,
    error: null,
    source: 'demo',
    canViewReporting: true,
    accessibleDashboardKeys: [],
    pendingActionLabel: null,
    lastActionMessage: null,
    actionError: null,
    actions: {
      queryDataset: vi.fn().mockResolvedValue(createQueryResult()),
      createSavedView: vi.fn().mockResolvedValue(undefined),
      archiveSavedView: vi.fn().mockResolvedValue(undefined),
      requestExport: vi.fn().mockResolvedValue(undefined),
      processExport: vi.fn().mockResolvedValue(undefined),
      downloadExport: vi.fn().mockResolvedValue(undefined),
      createSubscription: vi.fn().mockResolvedValue(undefined),
      updateSubscription: vi.fn().mockResolvedValue(undefined),
      revokeSubscription: vi.fn().mockResolvedValue(undefined),
      deliverSubscription: vi.fn().mockResolvedValue(undefined),
    },
  }
})

describe('Reporting operations pages', () => {
  it('shows the explorer empty state and supports saving views, archiving views, and requesting exports', async () => {
    const user = userEvent.setup()

    renderWithProviders(<ReportingExplorerPage />, {
      accessState: { demoPersona: 'tenantAdmin' },
    })

    expect(await screen.findByRole('heading', { name: /governed report explorer/i })).toBeInTheDocument()
    expect(await screen.findByRole('heading', { name: /no rows match the current filters/i })).toBeInTheDocument()

    await user.type(screen.getByLabelText(/view name/i), 'Monthly workforce check')
    await user.click(screen.getByRole('button', { name: /save current view/i }))

    expect(mockWorkspace.actions.createSavedView).toHaveBeenCalledWith(
      expect.objectContaining({
        dataset_key: dataset.key,
        name: 'Monthly workforce check',
      }),
    )

    await user.click(screen.getByRole('button', { name: /^archive$/i }))
    expect(mockWorkspace.actions.archiveSavedView).toHaveBeenCalledWith(savedView.id)

    await user.click(screen.getByRole('button', { name: /export csv/i }))
    expect(mockWorkspace.actions.requestExport).toHaveBeenCalledWith(
      expect.objectContaining({
        dataset_key: dataset.key,
        format: 'csv',
      }),
    )
  })

  it('surfaces queued, completed, failed, and expired export states and allows queue actions', async () => {
    const user = userEvent.setup()

    mockWorkspace.data = {
      ...createWorkspaceData(),
      exports: [
        createExport({
          id: 6101,
          export_uuid: 'export-queued',
          status: 'queued',
          file: {
            name: null,
            size_bytes: null,
            checksum_sha256: null,
            download_available: false,
            download_url: null,
          },
        }),
        createExport({
          id: 6102,
          export_uuid: 'export-completed',
          status: 'completed',
          execution_mode: 'sync',
          counts: {
            estimated_row_count: 22,
            exported_row_count: 22,
          },
          file: {
            name: 'headcount-weekly.csv',
            size_bytes: 2048,
            checksum_sha256: 'checksum-6102',
            download_available: true,
            download_url: '#download',
          },
          retention: {
            expires_at: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString(),
            is_expired: false,
          },
          completed_at: new Date().toISOString(),
        }),
        createExport({
          id: 6103,
          export_uuid: 'export-failed',
          status: 'failed',
          last_error: 'governance_blocked_due_to_masking',
          failed_at: new Date().toISOString(),
        }),
        createExport({
          id: 6104,
          export_uuid: 'export-expired',
          status: 'expired',
          file: {
            name: 'headcount-old.csv',
            size_bytes: 1024,
            checksum_sha256: 'checksum-6104',
            download_available: false,
            download_url: null,
          },
          retention: {
            expires_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
            is_expired: true,
          },
        }),
      ],
    }

    renderWithProviders(<ReportingExportsPage />, {
      accessState: { demoPersona: 'tenantAdmin' },
    })

    expect(await screen.findByRole('heading', { name: /export queue and delivery posture/i })).toBeInTheDocument()
    expect(screen.getAllByText(/^queued$/i).length).toBeGreaterThan(0)
    expect(screen.getAllByText(/^completed$/i).length).toBeGreaterThan(0)
    expect(screen.getAllByText(/^expired$/i).length).toBeGreaterThan(0)

    await user.click(screen.getByRole('button', { name: /process export/i }))
    expect(mockWorkspace.actions.processExport).toHaveBeenCalledWith(6101)

    await user.click(screen.getByText('headcount-weekly.csv'))
    await user.click(screen.getByRole('button', { name: /download/i }))
    expect(mockWorkspace.actions.downloadExport).toHaveBeenCalledWith(
      expect.objectContaining({
        id: 6102,
      }),
    )
  })

  it('shows blocked subscription posture and supports delivery, resume, revoke, and creation actions', async () => {
    const user = userEvent.setup()

    mockWorkspace.data = {
      ...createWorkspaceData(),
      subscriptions: [
        createSubscription({ id: 7101, status: 'blocked' }),
        createSubscription({
          id: 7102,
          name: 'People ops daily digest',
          status: 'active',
          validation: {
            status: 'valid',
            reason: null,
          },
          last_delivery: {
            status: 'delivered',
            error: null,
            delivered_at: new Date().toISOString(),
            report_export_id: 6102,
          },
          schedule: {
            frequency: 'daily',
            timezone: 'Asia/Kolkata',
            config: {
              time_of_day: '10:00',
            },
            next_delivery_at: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString(),
          },
          source: {
            dataset: {
              id: dataset.id,
              key: dataset.key,
              name: dataset.name,
              domain: dataset.domain,
            },
            saved_view: null,
          },
        }),
      ],
    }

    renderWithProviders(<ReportingSubscriptionsPage />, {
      accessState: { demoPersona: 'tenantAdmin' },
    })

    expect(await screen.findByRole('heading', { name: /subscription center/i })).toBeInTheDocument()
    expect(screen.getByText(/the source saved view was archived/i)).toBeInTheDocument()

    await user.click(screen.getByRole('button', { name: /resume/i }))
    expect(mockWorkspace.actions.updateSubscription).toHaveBeenCalledWith(7101, {
      status: 'active',
    })

    await user.click(screen.getByRole('button', { name: /deliver now/i }))
    expect(mockWorkspace.actions.deliverSubscription).toHaveBeenCalledWith(7101)

    await user.click(screen.getByRole('button', { name: /^revoke$/i }))
    expect(mockWorkspace.actions.revokeSubscription).toHaveBeenCalledWith(7101)

    await user.click(screen.getByLabelText(/saved view/i))
    await user.click(
      screen.getByRole('option', {
        name: /weekly workforce lens · workforce headcount snapshot/i,
      }),
    )
    await user.type(screen.getByLabelText(/^name$/i), 'Friday workforce drop')
    await user.click(screen.getByRole('button', { name: /create subscription/i }))

    expect(mockWorkspace.actions.createSubscription).toHaveBeenCalledWith(
      expect.objectContaining({
        saved_report_view_id: savedView.id,
        name: 'Friday workforce drop',
      }),
    )
  })
})
