import { useOutletContext } from 'react-router-dom'
import { useReportingWorkspace } from '../hooks/useReportingWorkspace'

export function useReportingRouteWorkspace() {
  return useOutletContext<ReturnType<typeof useReportingWorkspace>>()
}
