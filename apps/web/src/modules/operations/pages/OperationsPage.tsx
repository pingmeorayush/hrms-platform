import { Navigate, Outlet } from 'react-router-dom'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { useOperationsWorkspace } from '../hooks/useOperationsWorkspace'
import { getDefaultOperationsSectionPath } from '../navigation'

export function OperationsPage() {
  const workspace = useOperationsWorkspace()

  return (
    <div className="workspace-stack workspace-stack--tight">
      <Outlet context={workspace} />
    </div>
  )
}

export function OperationsIndexRedirect() {
  const { snapshot } = useAccessSnapshot()

  return <Navigate replace to={getDefaultOperationsSectionPath(snapshot?.user.permissions ?? [])} />
}
