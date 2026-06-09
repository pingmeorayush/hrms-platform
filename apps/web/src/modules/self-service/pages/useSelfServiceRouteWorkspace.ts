import { useOutletContext } from 'react-router-dom'
import { useSelfServiceWorkspace } from '../hooks/useSelfServiceWorkspace'

export function useSelfServiceRouteWorkspace() {
  return useOutletContext<ReturnType<typeof useSelfServiceWorkspace>>()
}
