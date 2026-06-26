import { Navigate, Outlet } from 'react-router-dom'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { useLearningWorkspace } from '../hooks/useLearningWorkspace'
import { getDefaultLearningSectionPath } from '../navigation'

export function LearningPage() {
  const workspace = useLearningWorkspace()

  return (
    <div className="workspace-stack workspace-stack--tight">
      <Outlet context={workspace} />
    </div>
  )
}

export function LearningIndexRedirect() {
  const { snapshot } = useAccessSnapshot()

  return <Navigate replace to={getDefaultLearningSectionPath(snapshot?.user.permissions ?? [])} />
}
