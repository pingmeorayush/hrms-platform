import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import type {
  ReportingDashboardKey,
  ReportingDashboardRecord,
  ReportingDatasetRecord,
  ReportingExportRecord,
  ReportingExportRequestInput,
  ReportingExplorerQueryInput,
  ReportingQueryResult,
  ReportingSavedViewInput,
  ReportingSavedViewRecord,
  ReportingSubscriptionInput,
  ReportingSubscriptionRecord,
  ReportingSubscriptionUpdateInput,
} from '../types'

interface PaginatedPayload<T> {
  items: T[]
  meta: {
    page: number
    per_page: number
    total: number
    last_page: number
  }
}

async function requestJson<T>(url: string, token: string, init?: RequestInit) {
  const response = await fetch(url, {
    ...init,
    headers: {
      ...buildApiHeaders(token),
      ...(init?.headers ?? {}),
    },
  })

  return readApiJson<T>(response)
}

async function postJson<T>(url: string, token: string, body?: unknown) {
  return requestJson<T>(url, token, {
    method: 'POST',
    body: JSON.stringify(body ?? {}),
  })
}

function appendRecordToParams(
  params: URLSearchParams,
  prefix: string,
  record: Record<string, unknown> | undefined,
) {
  if (!record) {
    return
  }

  for (const [key, value] of Object.entries(record)) {
    if (value === undefined || value === null || value === '') {
      continue
    }

    const paramKey = `${prefix}[${key}]`

    if (Array.isArray(value)) {
      value.forEach((entry) => {
        params.append(`${paramKey}[]`, String(entry))
      })
      continue
    }

    params.append(paramKey, String(value))
  }
}

async function fetchAllPages<T>(
  apiBaseUrl: string,
  token: string,
  path: string,
  params: URLSearchParams = new URLSearchParams(),
) {
  const firstPageParams = new URLSearchParams(params)
  firstPageParams.set('per_page', firstPageParams.get('per_page') ?? '100')
  firstPageParams.set('page', '1')

  const firstPage = await requestJson<PaginatedPayload<T>>(
    `${apiBaseUrl}${path}?${firstPageParams.toString()}`,
    token,
  )

  if (firstPage.meta.last_page <= 1) {
    return firstPage.items
  }

  const remainingPages = await Promise.all(
    Array.from({ length: firstPage.meta.last_page - 1 }, (_, index) => {
      const pageParams = new URLSearchParams(firstPageParams)
      pageParams.set('page', String(index + 2))

      return requestJson<PaginatedPayload<T>>(
        `${apiBaseUrl}${path}?${pageParams.toString()}`,
        token,
      )
    }),
  )

  return [firstPage, ...remainingPages].flatMap((page) => page.items)
}

export function fetchReportingDashboard(
  apiBaseUrl: string,
  token: string,
  dashboardKey: ReportingDashboardKey,
  options: { forceRefresh?: boolean } = {},
) {
  const params = new URLSearchParams()

  if (options.forceRefresh) {
    params.set('force_refresh', '1')
  }

  const suffix = params.size ? `?${params.toString()}` : ''

  return requestJson<ReportingDashboardRecord>(
    `${apiBaseUrl}/reporting/dashboards/${dashboardKey}${suffix}`,
    token,
  )
}

export function fetchReportingDatasets(apiBaseUrl: string, token: string) {
  return fetchAllPages<ReportingDatasetRecord>(apiBaseUrl, token, '/reporting/datasets')
}

export function queryReportingDataset(
  apiBaseUrl: string,
  token: string,
  input: ReportingExplorerQueryInput,
) {
  const params = new URLSearchParams()

  appendRecordToParams(params, 'filters', input.filters)
  appendRecordToParams(params, 'filter_operators', input.filterOperators)

  if (input.sortBy) {
    params.set('sort_by', input.sortBy)
  }

  if (input.sortDirection) {
    params.set('sort_direction', input.sortDirection)
  }

  if (input.drilldownPath) {
    params.set('drilldown_path', input.drilldownPath)
  }

  if (input.page) {
    params.set('page', String(input.page))
  }

  if (input.perPage) {
    params.set('per_page', String(input.perPage))
  }

  return requestJson<ReportingQueryResult>(
    `${apiBaseUrl}/reporting/reports/${input.datasetKey}?${params.toString()}`,
    token,
  )
}

export function fetchSavedReportViews(apiBaseUrl: string, token: string) {
  const params = new URLSearchParams()
  params.set('include_shared', '1')

  return fetchAllPages<ReportingSavedViewRecord>(apiBaseUrl, token, '/reporting/saved-views', params)
}

export function createSavedReportView(
  apiBaseUrl: string,
  token: string,
  payload: ReportingSavedViewInput,
) {
  return postJson<ReportingSavedViewRecord>(`${apiBaseUrl}/reporting/saved-views`, token, payload)
}

export function updateSavedReportView(
  apiBaseUrl: string,
  token: string,
  savedReportViewId: number,
  payload: Partial<ReportingSavedViewInput> & { status?: string },
) {
  return requestJson<ReportingSavedViewRecord>(
    `${apiBaseUrl}/reporting/saved-views/${savedReportViewId}`,
    token,
    {
      method: 'PATCH',
      body: JSON.stringify(payload),
    },
  )
}

export function archiveSavedReportView(
  apiBaseUrl: string,
  token: string,
  savedReportViewId: number,
) {
  return requestJson<ReportingSavedViewRecord>(
    `${apiBaseUrl}/reporting/saved-views/${savedReportViewId}`,
    token,
    {
      method: 'DELETE',
    },
  )
}

export function fetchReportingExports(apiBaseUrl: string, token: string) {
  return fetchAllPages<ReportingExportRecord>(apiBaseUrl, token, '/reporting/exports')
}

export function requestReportingExport(
  apiBaseUrl: string,
  token: string,
  payload: ReportingExportRequestInput,
) {
  return postJson<ReportingExportRecord>(`${apiBaseUrl}/reporting/exports`, token, payload)
}

export function processReportingExport(
  apiBaseUrl: string,
  token: string,
  reportExportId: number,
) {
  return postJson<ReportingExportRecord>(`${apiBaseUrl}/reporting/exports/${reportExportId}/process`, token)
}

export async function downloadReportingExport(
  apiBaseUrl: string,
  token: string,
  reportExportId: number,
  fileName: string,
) {
  const response = await fetch(`${apiBaseUrl}/reporting/exports/${reportExportId}/download`, {
    headers: {
      Accept: 'application/octet-stream',
      Authorization: `Bearer ${token}`,
    },
  })

  if (!response.ok) {
    let message = 'The report export download failed.'

    try {
      const payload = (await response.json()) as {
        message?: string
      }
      message = payload.message ?? message
    } catch {
      // Keep the default message for non-JSON download failures.
    }

    throw new Error(message)
  }

  const blob = await response.blob()
  const objectUrl = window.URL.createObjectURL(blob)
  const anchor = document.createElement('a')
  anchor.href = objectUrl
  anchor.download = fileName
  document.body.append(anchor)
  anchor.click()
  anchor.remove()
  window.URL.revokeObjectURL(objectUrl)
}

export function fetchReportingSubscriptions(apiBaseUrl: string, token: string) {
  return fetchAllPages<ReportingSubscriptionRecord>(apiBaseUrl, token, '/reporting/subscriptions')
}

export function createReportingSubscription(
  apiBaseUrl: string,
  token: string,
  payload: ReportingSubscriptionInput,
) {
  return postJson<ReportingSubscriptionRecord>(`${apiBaseUrl}/reporting/subscriptions`, token, payload)
}

export function updateReportingSubscription(
  apiBaseUrl: string,
  token: string,
  reportSubscriptionId: number,
  payload: ReportingSubscriptionUpdateInput,
) {
  return requestJson<ReportingSubscriptionRecord>(
    `${apiBaseUrl}/reporting/subscriptions/${reportSubscriptionId}`,
    token,
    {
      method: 'PATCH',
      body: JSON.stringify(payload),
    },
  )
}

export function revokeReportingSubscription(
  apiBaseUrl: string,
  token: string,
  reportSubscriptionId: number,
) {
  return requestJson<ReportingSubscriptionRecord>(
    `${apiBaseUrl}/reporting/subscriptions/${reportSubscriptionId}`,
    token,
    {
      method: 'DELETE',
    },
  )
}

export function deliverReportingSubscription(
  apiBaseUrl: string,
  token: string,
  reportSubscriptionId: number,
) {
  return postJson<ReportingSubscriptionRecord>(
    `${apiBaseUrl}/reporting/subscriptions/${reportSubscriptionId}/deliver`,
    token,
  )
}
