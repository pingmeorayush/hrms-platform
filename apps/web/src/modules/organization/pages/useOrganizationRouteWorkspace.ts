import { useOutletContext } from 'react-router-dom'
import { useOrganizationWorkspace } from '../hooks/useOrganizationWorkspace'

export function useOrganizationRouteWorkspace() {
  return useOutletContext<ReturnType<typeof useOrganizationWorkspace>>()
}
