import { Navigate, Outlet } from 'react-router-dom'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { usePerformanceWorkspace } from '../hooks/usePerformanceWorkspace'
import { getDefaultPerformanceSectionPath } from '../navigation'

export function PerformancePage() {
  const workspace = usePerformanceWorkspace()

  return (
    <div className="workspace-stack workspace-stack--tight">
      <Outlet context={workspace} />
    </div>
  )
}

export function PerformanceIndexRedirect() {
  const { snapshot } = useAccessSnapshot()

  return <Navigate replace to={getDefaultPerformanceSectionPath(snapshot?.user.permissions ?? [])} />
}
