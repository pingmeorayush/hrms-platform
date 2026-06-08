import { LeaveReviewWorkspaceView } from '../components/LeaveReviewWorkspace'
import { useLeaveRouteWorkspace } from './useLeaveRouteWorkspace'

export function LeaveApprovalsPage() {
  const workspace = useLeaveRouteWorkspace()

  return <LeaveReviewWorkspaceView workspace={workspace} />
}
