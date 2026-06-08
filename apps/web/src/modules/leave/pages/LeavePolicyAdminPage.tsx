import { LeaveAdminWorkspaceView } from '../components/LeaveAdminWorkspace'
import { useLeaveRouteWorkspace } from './useLeaveRouteWorkspace'

export function LeavePolicyAdminPage() {
  const workspace = useLeaveRouteWorkspace()

  return <LeaveAdminWorkspaceView workspace={workspace} />
}
