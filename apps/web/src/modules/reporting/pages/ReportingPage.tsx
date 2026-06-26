import { Navigate, Outlet } from 'react-router-dom'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { useReportingWorkspace } from '../hooks/useReportingWorkspace'
import { getDefaultReportingSectionPath } from '../navigation'

export function ReportingPage() {
  const workspace = useReportingWorkspace()

  return (
    <div className="workspace-stack workspace-stack--tight">
      <Outlet context={workspace} />
    </div>
  )
}

export function ReportingIndexRedirect() {
  const { snapshot } = useAccessSnapshot()

  return <Navigate replace to={getDefaultReportingSectionPath(snapshot?.user.permissions ?? [])} />
}
