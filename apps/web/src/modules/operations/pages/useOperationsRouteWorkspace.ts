import { useOutletContext } from 'react-router-dom'
import { useOperationsWorkspace } from '../hooks/useOperationsWorkspace'

export function useOperationsRouteWorkspace() {
  return useOutletContext<ReturnType<typeof useOperationsWorkspace>>()
}
