import { useOutletContext } from 'react-router-dom'
import { usePayrollWorkspace } from '../hooks/usePayrollWorkspace'

export function usePayrollRouteWorkspace() {
  return useOutletContext<ReturnType<typeof usePayrollWorkspace>>()
}
