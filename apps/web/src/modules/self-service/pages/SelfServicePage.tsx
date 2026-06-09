import { Navigate, Outlet } from 'react-router-dom'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { useSelfServiceWorkspace } from '../hooks/useSelfServiceWorkspace'
import { getDefaultSelfServiceSectionPath } from '../navigation'

export function SelfServicePage() {
  const workspace = useSelfServiceWorkspace()

  return (
    <div className="workspace-stack workspace-stack--tight">
      <Outlet context={workspace} />
    </div>
  )
}

export function SelfServiceIndexRedirect() {
  const { snapshot } = useAccessSnapshot()

  return <Navigate replace to={getDefaultSelfServiceSectionPath(snapshot?.user.permissions ?? [])} />
}
