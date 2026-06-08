import { Navigate, Outlet } from 'react-router-dom'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { useLeaveWorkspace } from '../hooks/useLeaveWorkspace'
import { getDefaultLeaveSectionPath } from '../navigation'

export function LeavePage() {
  const workspace = useLeaveWorkspace()

  return (
    <div className="workspace-stack workspace-stack--tight">
      <Outlet context={workspace} />
    </div>
  )
}

export function LeaveIndexRedirect() {
  const { snapshot } = useAccessSnapshot()

  return <Navigate replace to={getDefaultLeaveSectionPath(snapshot?.user.permissions ?? [])} />
}
