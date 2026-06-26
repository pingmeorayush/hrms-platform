import { useMemo } from 'react'
import { useQuery } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { fetchReportingDashboard } from '../api/reportingApi'
import { getAccessibleReportingDashboardKeys } from '../config'
import { buildDemoReportingWorkspace } from '../data/demoReportingWorkspace'
import type { ReportingDashboardFailure, ReportingWorkspaceData } from '../types'

const workspaceQueryScope = 'reporting-workspace'
const emptyPermissions: string[] = []

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

export function useReportingWorkspace() {
  const access = useAppSelector((state) => state.access)
  const { snapshot, source } = useAccessSnapshot()
  const permissions = useMemo(() => snapshot?.user.permissions ?? emptyPermissions, [snapshot?.user.permissions])
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const accessibleDashboardKeys = useMemo(
    () => getAccessibleReportingDashboardKeys(permissions),
    [permissions],
  )
  const demoData = useMemo(() => buildDemoReportingWorkspace(snapshot), [snapshot])

  const liveQuery = useQuery({
    queryKey: [workspaceQueryScope, access.apiBaseUrl, access.token, accessibleDashboardKeys.join(',')],
    queryFn: async (): Promise<ReportingWorkspaceData> => {
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
        activity: [],
      }
    },
    enabled: liveEnabled && accessibleDashboardKeys.length > 0,
  })

  return {
    data: source === 'demo' ? demoData : liveQuery.data ?? null,
    isLoading: source === 'demo' ? false : liveQuery.isLoading,
    error: liveQuery.error ? friendlyWorkspaceError(liveQuery.error) : null,
    source,
    canViewReporting: permissions.some((permission) =>
      ['reporting.view', 'reporting.manage', 'reporting.certify', 'reporting.export'].includes(permission),
    ),
    accessibleDashboardKeys,
  }
}
