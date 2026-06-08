import { useOutletContext } from 'react-router-dom'
import { useLeaveWorkspace } from '../hooks/useLeaveWorkspace'

export function useLeaveRouteWorkspace() {
  return useOutletContext<ReturnType<typeof useLeaveWorkspace>>()
}
