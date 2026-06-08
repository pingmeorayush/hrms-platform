import { LeaveEmployeeWorkspaceView } from '../components/LeaveEmployeeWorkspace'
import { useLeaveRouteWorkspace } from './useLeaveRouteWorkspace'

export function LeaveRequestsPage() {
  const workspace = useLeaveRouteWorkspace()

  return <LeaveEmployeeWorkspaceView workspace={workspace} />
}
