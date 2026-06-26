import { useCallback, useMemo, useState } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  archiveSavedReportView,
  createReportingSubscription,
  createSavedReportView,
  deliverReportingSubscription,
  downloadReportingExport,
  fetchReportingDashboard,
  fetchReportingDatasets,
  fetchReportingExports,
  fetchReportingSubscriptions,
  fetchSavedReportViews,
  processReportingExport,
  queryReportingDataset,
  requestReportingExport,
  revokeReportingSubscription,
  updateReportingSubscription,
} from '../api/reportingApi'
import { getAccessibleReportingDashboardKeys } from '../config'
import { buildDemoReportingWorkspaceState, type ReportingDemoWorkspaceState } from '../data/demoReportingWorkspace'
import type {
  ReportingDatasetRecord,
  ReportingDashboardFailure,
  ReportingExportRecord,
  ReportingExportRequestInput,
  ReportingExplorerQueryInput,
  ReportingQueryResult,
  ReportingSavedViewInput,
  ReportingSavedViewRecord,
  ReportingSubscriptionInput,
  ReportingSubscriptionRecord,
  ReportingSubscriptionUpdateInput,
  ReportingWorkspaceData,
} from '../types'

const dashboardQueryScope = 'reporting-dashboards'
const operationsQueryScope = 'reporting-operations'
const emptyPermissions: string[] = []

function delay(ms: number) {
  return new Promise((resolve) => {
    window.setTimeout(resolve, ms)
  })
}

function friendlyWorkspaceError(error: unknown) {
  if (error instanceof ApiRequestError) {
    const firstFieldError = Object.values(error.fieldErrors)[0]?.[0]
    return new Error(firstFieldError ?? error.message)
  }

  if (error instanceof Error) {
    return error
  }

  return new Error('The reporting workspace could not be loaded.')
}

function friendlyActionError(error: unknown) {
  if (error instanceof ApiRequestError) {
    const firstFieldError = Object.values(error.fieldErrors)[0]?.[0]
    return firstFieldError ?? error.message
  }

  if (error instanceof Error) {
    return error.message
  }

  return 'The reporting action could not be completed right now.'
}

function isNumericValue(value: unknown) {
  return typeof value === 'number' || (typeof value === 'string' && value.trim() !== '' && !Number.isNaN(Number(value)))
}

function toComparableValue(value: unknown) {
  if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}/.test(value)) {
    return new Date(value).getTime()
  }

  if (isNumericValue(value)) {
    return Number(value)
  }

  return String(value ?? '').toLowerCase()
}

function compareDemoValues(left: unknown, operator: string, right: unknown) {
  const normalizedOperator = operator || 'eq'

  if (normalizedOperator === 'contains') {
    return String(left ?? '').toLowerCase().includes(String(right ?? '').toLowerCase())
  }

  if (normalizedOperator === 'starts_with') {
    return String(left ?? '').toLowerCase().startsWith(String(right ?? '').toLowerCase())
  }

  if (normalizedOperator === 'ends_with') {
    return String(left ?? '').toLowerCase().endsWith(String(right ?? '').toLowerCase())
  }

  if (normalizedOperator === 'in' || normalizedOperator === 'not_in') {
    const values = Array.isArray(right) ? right.map((value) => String(value).toLowerCase()) : [String(right).toLowerCase()]
    const contains = values.includes(String(left ?? '').toLowerCase())
    return normalizedOperator === 'in' ? contains : !contains
  }

  if (normalizedOperator === 'gt' || normalizedOperator === 'gte' || normalizedOperator === 'lt' || normalizedOperator === 'lte') {
    const leftComparable = toComparableValue(left)
    const rightComparable = toComparableValue(right)

    if (normalizedOperator === 'gt') {
      return leftComparable > rightComparable
    }

    if (normalizedOperator === 'gte') {
      return leftComparable >= rightComparable
    }

    if (normalizedOperator === 'lt') {
      return leftComparable < rightComparable
    }

    return leftComparable <= rightComparable
  }

  if (normalizedOperator === 'neq') {
    return String(left ?? '').toLowerCase() !== String(right ?? '').toLowerCase()
  }

  return String(left ?? '').toLowerCase() === String(right ?? '').toLowerCase()
}

function sortDemoRows(rows: ReportingQueryResult['items'], sortBy: string, sortDirection: 'asc' | 'desc') {
  return [...rows].sort((left, right) => {
    const leftValue = left[sortBy]
    const rightValue = right[sortBy]
    const leftComparable = toComparableValue(leftValue)
    const rightComparable = toComparableValue(rightValue)

    if (leftComparable < rightComparable) {
      return sortDirection === 'asc' ? -1 : 1
    }

    if (leftComparable > rightComparable) {
      return sortDirection === 'asc' ? 1 : -1
    }

    return 0
  })
}

function canViewDatasetDomain(permissions: string[], domain: string) {
  const canReport = permissions.some((permission) =>
    ['reporting.view', 'reporting.manage', 'reporting.certify'].includes(permission),
  )

  if (!canReport) {
    return false
  }

  switch (domain) {
    case 'workforce':
      return permissions.some((permission) =>
        ['employee.view', 'employee.manage', 'organization.view', 'organization.manage'].includes(
          permission,
        ),
      )
    case 'attendance':
      return permissions.some((permission) =>
        ['attendance.analytics.view', 'attendance.view', 'attendance.edit', 'attendance.approve'].includes(
          permission,
        ),
      )
    case 'leave':
      return permissions.some((permission) =>
        ['leave.view', 'leave.approve', 'leave.manage_balance', 'leave.manage_policy'].includes(
          permission,
        ),
      )
    case 'payroll':
      return permissions.some((permission) =>
        ['payroll.view', 'compensation.view', 'payroll.process', 'payroll.approve'].includes(permission),
      )
    case 'recruitment':
      return permissions.some((permission) =>
        ['recruitment.view', 'recruitment.manage', 'recruitment.approve', 'recruitment.interview'].includes(
          permission,
        ),
      )
    case 'performance':
      return permissions.some((permission) =>
        ['performance.view', 'performance.review', 'performance.manage', 'performance.calibrate'].includes(
          permission,
        ),
      )
    case 'learning':
      return permissions.some((permission) =>
        ['learning.view', 'learning.complete', 'learning.assign', 'learning.manage'].includes(permission),
      )
    case 'operations':
      return permissions.some((permission) =>
        ['document.view', 'asset.view', 'employee.view', 'employee.manage'].includes(permission),
      )
    case 'cross_domain':
      return permissions.some((permission) => ['reporting.manage', 'reporting.certify'].includes(permission))
    default:
      return false
  }
}

function canSeeSensitiveWorkforceFields(permissions: string[]) {
  return permissions.some((permission) =>
    ['reporting.manage', 'reporting.certify', 'employee.manage', 'organization.manage'].includes(permission),
  )
}

function buildLiveActivity(data: Pick<ReportingWorkspaceData, 'datasets' | 'exports' | 'subscriptions'>) {
  const items = []

  if (data.exports.some((record) => record.status === 'queued' || record.status === 'processing')) {
    items.push({
      id: 'live-queued-export',
      title: 'Queued export delivery needs follow-up',
      detail: 'At least one governed export is still processing before download becomes available.',
      meta: 'Exports · async lifecycle',
      tone: 'warning' as const,
      path: '/reporting/exports',
    })
  }

  if (data.subscriptions.some((record) => record.status === 'blocked')) {
    items.push({
      id: 'live-blocked-subscription',
      title: 'Subscription delivery is blocked',
      detail: 'A recurring report is paused until certification or access posture is restored.',
      meta: 'Subscriptions · blocked',
      tone: 'danger' as const,
      path: '/reporting/subscriptions',
    })
  }

  if (data.datasets.some((dataset) => dataset.governance.certification_status !== 'certified')) {
    items.push({
      id: 'live-uncertified-dataset',
      title: 'A dataset still needs certification',
      detail: 'Some governed report datasets are not yet certified for broad explorer use.',
      meta: 'Catalog · governance review',
      tone: 'info' as const,
      path: '/reporting/explorer',
    })
  }

  return items
}

function buildDemoVisibility(permissions: string[], dataset: ReportingDatasetRecord) {
  const maskedFieldKeys =
    dataset.key === 'workforce_headcount_snapshot' && !canSeeSensitiveWorkforceFields(permissions)
      ? ['employee_email']
      : []
  const hiddenFieldKeys =
    dataset.key === 'payroll_run_register' && !permissions.some((permission) =>
      ['reporting.manage', 'reporting.certify', 'payroll.view', 'compensation.view', 'payroll.process', 'payroll.approve'].includes(permission),
    )
      ? ['net_payroll']
      : []

  return {
    masked_field_keys: maskedFieldKeys,
    hidden_field_keys: hiddenFieldKeys,
    drilldown_keys: dataset.drilldown_paths.map((path) => path.key),
  }
}

function queryDemoDataset(
  state: ReportingDemoWorkspaceState,
  permissions: string[],
  input: ReportingExplorerQueryInput,
): ReportingQueryResult {
  const dataset = state.workspace.datasets.find((record) => record.key === input.datasetKey)

  if (!dataset || !canViewDatasetDomain(permissions, input.datasetKey === 'workforce_headcount_snapshot' ? 'workforce' : dataset.domain)) {
    throw new Error('This governed report is not available in the current reporting scope.')
  }

  const availableRows = state.rowsByDatasetKey[input.datasetKey] ?? []
  const requestedFilters = input.filters ?? {}
  const requestedOperators = input.filterOperators ?? {}

  const filteredRows = availableRows.filter((row) =>
    dataset.approved_filters.every((filterDefinition) => {
      const requestedValue = requestedFilters[filterDefinition.key]

      if (requestedValue === undefined || requestedValue === null || requestedValue === '') {
        return !filterDefinition.required
      }

      return compareDemoValues(
        row[filterDefinition.key],
        requestedOperators[filterDefinition.key] ?? 'eq',
        requestedValue,
      )
    }),
  )

  const sortBy = input.sortBy ?? dataset.approved_fields[0]?.key ?? 'id'
  const sortDirection = input.sortDirection ?? 'asc'
  const sortedRows = sortDemoRows(filteredRows, sortBy, sortDirection)
  const page = input.page ?? 1
  const perPage = input.perPage ?? 25
  const startIndex = (page - 1) * perPage
  const pagedRows = sortedRows.slice(startIndex, startIndex + perPage)
  const drilldownPath = input.drilldownPath ?? null
  const visibility = buildDemoVisibility(permissions, dataset)

  return {
    dataset,
    items: pagedRows.map((row) => ({
      ...row,
      drilldowns: drilldownPath
        ? row.drilldowns.filter((entry) => entry.key === drilldownPath)
        : row.drilldowns,
    })),
    meta: {
      page,
      per_page: perPage,
      total: sortedRows.length,
      last_page: Math.max(1, Math.ceil(sortedRows.length / perPage)),
      sort_by: sortBy,
      sort_direction: sortDirection,
      drilldown_path: drilldownPath,
    },
    filters: {
      available: dataset.approved_filters,
      applied: Object.fromEntries(
        Object.entries(requestedFilters)
          .filter(([, value]) => value !== '' && value !== null && value !== undefined)
          .map(([key, value]) => [
            key,
            {
              operator: requestedOperators[key] ?? 'eq',
              value,
            },
          ]),
      ),
    },
    freshness: {
      generated_at: new Date().toISOString(),
      expectation_minutes: dataset.freshness_expectation_minutes,
    },
    visibility: {
      ...visibility,
      drilldown_keys: drilldownPath
        ? dataset.drilldown_paths.filter((path) => path.key === drilldownPath).map((path) => path.key)
        : dataset.drilldown_paths.map((path) => path.key),
    },
  }
}

function createDemoExportDownload(record: ReportingExportRecord) {
  const body =
    record.format === 'json'
      ? JSON.stringify(
          {
            export_uuid: record.export_uuid,
            dataset: record.dataset,
            filters: record.query.filters,
            exported_row_count: record.counts.exported_row_count,
            visibility: record.visibility,
          },
          null,
          2,
        )
      : [
          'Export UUID,Dataset,Rows',
          `${record.export_uuid},${record.dataset?.name ?? 'Dataset'},${record.counts.exported_row_count ?? 0}`,
        ].join('\n')

  const blob = new Blob([body], {
    type: record.format === 'json' ? 'application/json;charset=utf-8' : 'text/csv;charset=utf-8',
  })
  const objectUrl = window.URL.createObjectURL(blob)
  const anchor = document.createElement('a')
  anchor.href = objectUrl
  anchor.download = record.file.name ?? `report-export.${record.format}`
  document.body.append(anchor)
  anchor.click()
  anchor.remove()
  window.URL.revokeObjectURL(objectUrl)
}

export function useReportingWorkspace() {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = useMemo(() => snapshot?.user.permissions ?? emptyPermissions, [snapshot?.user.permissions])
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const accessibleDashboardKeys = useMemo(
    () => getAccessibleReportingDashboardKeys(permissions),
    [permissions],
  )
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}`
  const [demoStates, setDemoStates] = useState<Record<string, ReportingDemoWorkspaceState>>({})
  const [pendingActionLabel, setPendingActionLabel] = useState<string | null>(null)
  const [lastActionMessage, setLastActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)

  const demoState = useMemo(
    () => demoStates[demoStateKey] ?? buildDemoReportingWorkspaceState(snapshot),
    [demoStateKey, demoStates, snapshot],
  )

  const liveDashboardsQuery = useQuery({
    queryKey: [dashboardQueryScope, access.apiBaseUrl, access.token, accessibleDashboardKeys.join(',')],
    queryFn: async (): Promise<{
      dashboards: ReportingWorkspaceData['dashboards']
      failures: ReportingDashboardFailure[]
    }> => {
      const settled = await Promise.allSettled(
        accessibleDashboardKeys.map((dashboardKey) =>
          fetchReportingDashboard(access.apiBaseUrl, access.token, dashboardKey),
        ),
      )

      const dashboards: ReportingWorkspaceData['dashboards'] = {}
      const failures: ReportingDashboardFailure[] = []

      settled.forEach((result, index) => {
        const dashboardKey = accessibleDashboardKeys[index]

        if (result.status === 'fulfilled') {
          dashboards[dashboardKey] = result.value
          return
        }

        const reason = result.reason
        failures.push({
          key: dashboardKey,
          message:
            reason instanceof ApiRequestError
              ? reason.message
              : reason instanceof Error
                ? reason.message
                : 'The governed dashboard could not be loaded.',
        })
      })

      return {
        dashboards,
        failures,
      }
    },
    enabled: liveEnabled && accessibleDashboardKeys.length > 0,
  })

  const liveOperationsQuery = useQuery({
    queryKey: [operationsQueryScope, access.apiBaseUrl, access.token],
    queryFn: async (): Promise<Pick<ReportingWorkspaceData, 'datasets' | 'savedViews' | 'exports' | 'subscriptions' | 'activity'>> => {
      const [datasets, savedViews, exports, subscriptions] = await Promise.all([
        fetchReportingDatasets(access.apiBaseUrl, access.token),
        fetchSavedReportViews(access.apiBaseUrl, access.token),
        fetchReportingExports(access.apiBaseUrl, access.token),
        fetchReportingSubscriptions(access.apiBaseUrl, access.token),
      ])

      return {
        datasets,
        savedViews,
        exports,
        subscriptions,
        activity: buildLiveActivity({
          datasets,
          exports,
          subscriptions,
        }),
      }
    },
    enabled: liveEnabled,
  })

  const liveData = useMemo(() => {
    if (!liveDashboardsQuery.data && !liveOperationsQuery.data) {
      return null
    }

    return {
      dashboards: liveDashboardsQuery.data?.dashboards ?? {},
      failures: liveDashboardsQuery.data?.failures ?? [],
      activity: liveOperationsQuery.data?.activity ?? [],
      datasets: liveOperationsQuery.data?.datasets ?? [],
      savedViews: liveOperationsQuery.data?.savedViews ?? [],
      exports: liveOperationsQuery.data?.exports ?? [],
      subscriptions: liveOperationsQuery.data?.subscriptions ?? [],
    } satisfies ReportingWorkspaceData
  }, [liveDashboardsQuery.data, liveOperationsQuery.data])

  const data = source === 'demo' ? demoState.workspace : liveData

  const refreshLiveQueries = useCallback(async () => {
    await Promise.all([
      queryClient.invalidateQueries({ queryKey: [dashboardQueryScope, access.apiBaseUrl, access.token] }),
      queryClient.invalidateQueries({ queryKey: [operationsQueryScope, access.apiBaseUrl, access.token] }),
    ])
  }, [access.apiBaseUrl, access.token, queryClient])

  const mutateDemoState = useCallback(
    async (mutator: (current: ReportingDemoWorkspaceState) => ReportingDemoWorkspaceState) => {
      await delay(150)
      setDemoStates((current) => {
        const base = current[demoStateKey] ?? buildDemoReportingWorkspaceState(snapshot)
        return {
          ...current,
          [demoStateKey]: mutator(base),
        }
      })
    },
    [demoStateKey, snapshot],
  )

  const runAction = useCallback(
    async (label: string, message: string, action: () => Promise<void>) => {
      setPendingActionLabel(label)
      setActionError(null)
      setLastActionMessage(null)

      try {
        await action()
        setLastActionMessage(message)
      } catch (error) {
        setActionError(friendlyActionError(error))
        throw error
      } finally {
        setPendingActionLabel(null)
      }
    },
    [],
  )

  const queryDataset = useCallback(
    async (input: ReportingExplorerQueryInput) => {
      if (source === 'demo') {
        return queryDemoDataset(demoState, permissions, input)
      }

      return queryReportingDataset(access.apiBaseUrl, access.token, input)
    },
    [access.apiBaseUrl, access.token, demoState, permissions, source],
  )

  const createSavedViewAction = useCallback(
    async (payload: ReportingSavedViewInput) => {
      await runAction('Saving view', 'Saved report view created.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => {
            const nextId = Math.max(9200, ...current.workspace.savedViews.map((view) => view.id)) + 1
            const dataset = current.workspace.datasets.find((record) => record.key === payload.dataset_key) ?? null
            const nextView: ReportingSavedViewRecord = {
              id: nextId,
              view_uuid: `demo-view-${nextId}`,
              name: payload.name,
              description: payload.description ?? null,
              status: 'active',
              share: {
                scope: payload.share_scope ?? 'private',
                shared_role_names: payload.shared_role_names ?? [],
              },
              dataset: dataset
                ? {
                    id: dataset.id,
                    key: dataset.key,
                    name: dataset.name,
                    domain: dataset.domain,
                  }
                : null,
              owner: snapshot?.user
                ? {
                    id: snapshot.user.id,
                    name: snapshot.user.name,
                    email: snapshot.user.email,
                  }
                : null,
              query: {
                filters: payload.filters ?? {},
                filter_operators: payload.filter_operators ?? {},
                sort_by: payload.sort_by ?? null,
                sort_direction: payload.sort_direction ?? null,
                drilldown_path: payload.drilldown_path ?? null,
              },
              presentation_preferences: payload.presentation_preferences ?? {},
              validation: { status: 'valid', reason: null },
              created_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            }

            return {
              ...current,
              workspace: {
                ...current.workspace,
                savedViews: [nextView, ...current.workspace.savedViews],
              },
            }
          })
          return
        }

        await createSavedReportView(access.apiBaseUrl, access.token, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, snapshot, source],
  )

  const archiveSavedViewAction = useCallback(
    async (savedReportViewId: number) => {
      await runAction('Archiving view', 'Saved report view archived.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => ({
            ...current,
            workspace: {
              ...current.workspace,
              savedViews: current.workspace.savedViews.filter((view) => view.id !== savedReportViewId),
              subscriptions: current.workspace.subscriptions.map((subscription) =>
                subscription.source.saved_view?.id === savedReportViewId
                  ? {
                      ...subscription,
                      status: 'blocked',
                      validation: {
                        status: 'blocked',
                        reason: 'The source saved view was archived.',
                      },
                      last_delivery: {
                        ...subscription.last_delivery,
                        status: 'blocked',
                        error: 'saved_view_archived',
                      },
                    }
                  : subscription,
              ),
            },
          }))
          return
        }

        await archiveSavedReportView(access.apiBaseUrl, access.token, savedReportViewId)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const requestExportAction = useCallback(
    async (payload: ReportingExportRequestInput) => {
      await runAction('Requesting export', 'Report export requested.', async () => {
        if (source === 'demo') {
          const preview = queryDemoDataset(demoState, permissions, {
            datasetKey: payload.dataset_key,
            filters: payload.filters,
            filterOperators: payload.filter_operators,
            sortBy: payload.sort_by ?? null,
            sortDirection: payload.sort_direction ?? null,
            drilldownPath: payload.drilldown_path ?? null,
            page: 1,
            perPage: 100,
          })

          await mutateDemoState((current) => {
            const nextId = Math.max(9300, ...current.workspace.exports.map((record) => record.id)) + 1
            const dataset = current.workspace.datasets.find((record) => record.key === payload.dataset_key) ?? null
            const shouldQueue =
              payload.execution_mode === 'async' ||
              ((payload.execution_mode ?? 'auto') === 'auto' && preview.meta.total > 2)
            const now = new Date().toISOString()

            const nextExport: ReportingExportRecord = {
              id: nextId,
              export_uuid: `demo-export-${nextId}`,
              status: shouldQueue ? 'queued' : 'completed',
              format: payload.format ?? 'csv',
              execution_mode: shouldQueue ? 'async' : 'sync',
              delivery_target: payload.delivery_target ?? 'requestor_download',
              dataset: dataset
                ? {
                    id: dataset.id,
                    key: dataset.key,
                    name: dataset.name,
                    domain: dataset.domain,
                  }
                : null,
              requested_by: snapshot?.user
                ? {
                    id: snapshot.user.id,
                    name: snapshot.user.name,
                    email: snapshot.user.email,
                  }
                : null,
              query: {
                filters: payload.filters ?? {},
                filter_operators: payload.filter_operators ?? {},
                sort_by: payload.sort_by ?? null,
                sort_direction: payload.sort_direction ?? null,
                drilldown_path: payload.drilldown_path ?? null,
              },
              counts: {
                estimated_row_count: preview.meta.total,
                exported_row_count: shouldQueue ? null : preview.meta.total,
              },
              visibility: preview.visibility,
              freshness: preview.freshness,
              file: {
                name: shouldQueue ? null : `${dataset?.key ?? 'report'}.${payload.format ?? 'csv'}`,
                size_bytes: shouldQueue ? null : 2048,
                checksum_sha256: shouldQueue ? null : `demo-checksum-${nextId}`,
                download_available: !shouldQueue,
                download_url: shouldQueue ? null : '#demo-reporting-export-new',
              },
              retention: {
                expires_at: shouldQueue ? null : new Date(Date.now() + 48 * 60 * 60 * 1000).toISOString(),
                is_expired: false,
              },
              requested_at: now,
              started_at: shouldQueue ? null : now,
              completed_at: shouldQueue ? null : now,
              failed_at: null,
              notified_at: shouldQueue ? null : now,
              last_error: null,
              created_at: now,
              updated_at: now,
            }

            return {
              ...current,
              workspace: {
                ...current.workspace,
                exports: [nextExport, ...current.workspace.exports],
              },
            }
          })
          return
        }

        await requestReportingExport(access.apiBaseUrl, access.token, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, demoState, mutateDemoState, permissions, refreshLiveQueries, runAction, snapshot, source],
  )

  const processExportAction = useCallback(
    async (reportExportId: number) => {
      await runAction('Processing export', 'Report export processed.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => ({
            ...current,
            workspace: {
              ...current.workspace,
              exports: current.workspace.exports.map((record) =>
                record.id === reportExportId
                  ? {
                      ...record,
                      status: 'completed',
                      counts: {
                        ...record.counts,
                        exported_row_count: record.counts.estimated_row_count,
                      },
                      file: {
                        ...record.file,
                        name: record.file.name ?? `${record.dataset?.key ?? 'report'}.${record.format}`,
                        size_bytes: record.file.size_bytes ?? 2048,
                        checksum_sha256: record.file.checksum_sha256 ?? `demo-checksum-${record.id}`,
                        download_available: true,
                        download_url: '#demo-reporting-export-processed',
                      },
                      retention: {
                        expires_at: new Date(Date.now() + 48 * 60 * 60 * 1000).toISOString(),
                        is_expired: false,
                      },
                      started_at: record.started_at ?? new Date().toISOString(),
                      completed_at: new Date().toISOString(),
                      notified_at: new Date().toISOString(),
                      updated_at: new Date().toISOString(),
                    }
                  : record,
              ),
            },
          }))
          return
        }

        await processReportingExport(access.apiBaseUrl, access.token, reportExportId)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const downloadExportAction = useCallback(
    async (record: ReportingExportRecord) => {
      setActionError(null)
      setLastActionMessage(null)

      if (source === 'demo') {
        createDemoExportDownload(record)
        setLastActionMessage('Report export download started.')
        return
      }

      await downloadReportingExport(
        access.apiBaseUrl,
        access.token,
        record.id,
        record.file.name ?? `report-export.${record.format}`,
      )
      setLastActionMessage('Report export download started.')
    },
    [access.apiBaseUrl, access.token, source],
  )

  const createSubscriptionAction = useCallback(
    async (payload: ReportingSubscriptionInput) => {
      await runAction('Saving subscription', 'Report subscription created.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => {
            const nextId = Math.max(9400, ...current.workspace.subscriptions.map((record) => record.id)) + 1
            const datasetKey =
              payload.dataset_key ??
              current.workspace.savedViews.find((view) => view.id === payload.saved_report_view_id)?.dataset?.key ??
              null
            const dataset = current.workspace.datasets.find((record) => record.key === datasetKey) ?? null
            const savedView = current.workspace.savedViews.find((view) => view.id === payload.saved_report_view_id) ?? null
            const now = new Date().toISOString()
            const validationBlocked = savedView?.validation.status === 'blocked'

            const nextSubscription: ReportingSubscriptionRecord = {
              id: nextId,
              subscription_uuid: `demo-subscription-${nextId}`,
              name: payload.name,
              description: payload.description ?? null,
              status: validationBlocked ? 'blocked' : 'active',
              owner: snapshot?.user
                ? {
                    id: snapshot.user.id,
                    name: snapshot.user.name,
                    email: snapshot.user.email,
                  }
                : null,
              source: {
                dataset: dataset
                  ? {
                      id: dataset.id,
                      key: dataset.key,
                      name: dataset.name,
                      domain: dataset.domain,
                    }
                  : null,
                saved_view: savedView
                  ? {
                      id: savedView.id,
                      view_uuid: savedView.view_uuid,
                      name: savedView.name,
                      status: savedView.status,
                    }
                  : null,
              },
              delivery: {
                channel: payload.delivery_channel ?? 'in_app_notification',
                target: payload.delivery_target ?? 'owner_only',
                export_format: payload.export_format ?? 'csv',
              },
              schedule: {
                frequency: payload.frequency,
                timezone: payload.timezone,
                config: payload.schedule_config,
                next_delivery_at: validationBlocked ? null : new Date(Date.now() + 3 * 24 * 60 * 60 * 1000).toISOString(),
              },
              query: {
                filters: payload.filters ?? savedView?.query.filters ?? {},
                filter_operators: payload.filter_operators ?? savedView?.query.filter_operators ?? {},
                sort_by: payload.sort_by ?? savedView?.query.sort_by ?? null,
                sort_direction: payload.sort_direction ?? savedView?.query.sort_direction ?? null,
                drilldown_path: payload.drilldown_path ?? savedView?.query.drilldown_path ?? null,
              },
              validation: validationBlocked
                ? { status: 'blocked', reason: savedView?.validation.reason ?? 'The selected saved view is blocked.' }
                : { status: 'valid', reason: null },
              last_delivery: {
                status: null,
                error: null,
                delivered_at: null,
                report_export_id: null,
              },
              created_at: now,
              updated_at: now,
            }

            return {
              ...current,
              workspace: {
                ...current.workspace,
                subscriptions: [nextSubscription, ...current.workspace.subscriptions],
              },
            }
          })
          return
        }

        await createReportingSubscription(access.apiBaseUrl, access.token, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, snapshot, source],
  )

  const updateSubscriptionAction = useCallback(
    async (reportSubscriptionId: number, payload: ReportingSubscriptionUpdateInput) => {
      await runAction('Updating subscription', 'Report subscription updated.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => ({
            ...current,
            workspace: {
              ...current.workspace,
              subscriptions: current.workspace.subscriptions.map((subscription) =>
                subscription.id === reportSubscriptionId
                  ? {
                      ...subscription,
                      status: payload.status ?? subscription.status,
                      schedule: {
                        ...subscription.schedule,
                        frequency: payload.frequency ?? subscription.schedule.frequency,
                        timezone: payload.timezone ?? subscription.schedule.timezone,
                        config: payload.schedule_config ?? subscription.schedule.config,
                        next_delivery_at:
                          (payload.status ?? subscription.status) === 'active'
                            ? new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString()
                            : null,
                      },
                      delivery: {
                        ...subscription.delivery,
                        export_format: payload.export_format ?? subscription.delivery.export_format,
                      },
                      updated_at: new Date().toISOString(),
                    }
                  : subscription,
              ),
            },
          }))
          return
        }

        await updateReportingSubscription(access.apiBaseUrl, access.token, reportSubscriptionId, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const revokeSubscriptionAction = useCallback(
    async (reportSubscriptionId: number) => {
      await runAction('Revoking subscription', 'Report subscription revoked.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => ({
            ...current,
            workspace: {
              ...current.workspace,
              subscriptions: current.workspace.subscriptions.map((subscription) =>
                subscription.id === reportSubscriptionId
                  ? {
                      ...subscription,
                      status: 'revoked',
                      schedule: {
                        ...subscription.schedule,
                        next_delivery_at: null,
                      },
                      updated_at: new Date().toISOString(),
                    }
                  : subscription,
              ),
            },
          }))
          return
        }

        await revokeReportingSubscription(access.apiBaseUrl, access.token, reportSubscriptionId)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const deliverSubscriptionAction = useCallback(
    async (reportSubscriptionId: number) => {
      await runAction('Delivering subscription', 'Report subscription delivery refreshed.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => {
            const subscription = current.workspace.subscriptions.find((record) => record.id === reportSubscriptionId)

            if (!subscription) {
              return current
            }

            if (subscription.validation.status === 'blocked' || subscription.status === 'blocked') {
              return {
                ...current,
                workspace: {
                  ...current.workspace,
                  subscriptions: current.workspace.subscriptions.map((record) =>
                    record.id === reportSubscriptionId
                      ? {
                          ...record,
                          last_delivery: {
                            status: 'blocked',
                            error: record.validation.reason ?? 'subscription_blocked',
                            delivered_at: null,
                            report_export_id: null,
                          },
                          updated_at: new Date().toISOString(),
                        }
                      : record,
                  ),
                },
              }
            }

            const nextExportId = Math.max(9300, ...current.workspace.exports.map((record) => record.id)) + 1
            const now = new Date().toISOString()
            const nextExport: ReportingExportRecord = {
              id: nextExportId,
              export_uuid: `demo-export-${nextExportId}`,
              status: 'completed',
              format: subscription.delivery.export_format,
              execution_mode: 'sync',
              delivery_target: 'requestor_download',
              dataset: subscription.source.dataset,
              requested_by: subscription.owner ?? null,
              query: subscription.query,
              counts: { estimated_row_count: 2, exported_row_count: 2 },
              visibility: {
                masked_field_keys:
                  subscription.source.dataset?.key === 'workforce_headcount_snapshot' && !canSeeSensitiveWorkforceFields(permissions)
                    ? ['employee_email']
                    : [],
                hidden_field_keys: [],
                drilldown_keys: subscription.query.drilldown_path ? [subscription.query.drilldown_path] : [],
              },
              freshness: { generated_at: now, expectation_minutes: 60 },
              file: {
                name: `${subscription.name.toLowerCase().replace(/\s+/g, '-')}.${subscription.delivery.export_format}`,
                size_bytes: 2048,
                checksum_sha256: `demo-checksum-${nextExportId}`,
                download_available: true,
                download_url: '#demo-reporting-export-delivery',
              },
              retention: { expires_at: new Date(Date.now() + 48 * 60 * 60 * 1000).toISOString(), is_expired: false },
              requested_at: now,
              started_at: now,
              completed_at: now,
              failed_at: null,
              notified_at: now,
              last_error: null,
              created_at: now,
              updated_at: now,
            }

            return {
              ...current,
              workspace: {
                ...current.workspace,
                exports: [nextExport, ...current.workspace.exports],
                subscriptions: current.workspace.subscriptions.map((record) =>
                  record.id === reportSubscriptionId
                    ? {
                        ...record,
                        last_delivery: {
                          status: 'completed',
                          error: null,
                          delivered_at: now,
                          report_export_id: nextExportId,
                        },
                        updated_at: now,
                      }
                    : record,
                ),
              },
            }
          })
          return
        }

        await deliverReportingSubscription(access.apiBaseUrl, access.token, reportSubscriptionId)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, permissions, refreshLiveQueries, runAction, source],
  )

  return {
    data,
    isLoading:
      source === 'demo'
        ? false
        : liveDashboardsQuery.isLoading || liveOperationsQuery.isLoading,
    error:
      liveDashboardsQuery.error || liveOperationsQuery.error
        ? friendlyWorkspaceError(liveDashboardsQuery.error ?? liveOperationsQuery.error)
        : null,
    source,
    pendingActionLabel,
    lastActionMessage,
    actionError,
    canViewReporting: permissions.some((permission) =>
      ['reporting.view', 'reporting.manage', 'reporting.certify', 'reporting.export'].includes(permission),
    ),
    accessibleDashboardKeys,
    actions: {
      queryDataset,
      createSavedView: createSavedViewAction,
      archiveSavedView: archiveSavedViewAction,
      requestExport: requestExportAction,
      processExport: processExportAction,
      downloadExport: downloadExportAction,
      createSubscription: createSubscriptionAction,
      updateSubscription: updateSubscriptionAction,
      revokeSubscription: revokeSubscriptionAction,
      deliverSubscription: deliverSubscriptionAction,
    },
  }
}
