import { useOutletContext } from 'react-router-dom'
import { usePerformanceWorkspace } from '../hooks/usePerformanceWorkspace'

export function usePerformanceRouteWorkspace() {
  return useOutletContext<ReturnType<typeof usePerformanceWorkspace>>()
}
