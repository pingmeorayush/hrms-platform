import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import type { ReportingDashboardKey, ReportingDashboardRecord } from '../types'

async function requestJson<T>(url: string, token: string) {
  const response = await fetch(url, {
    headers: buildApiHeaders(token),
  })

  return readApiJson<T>(response)
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
