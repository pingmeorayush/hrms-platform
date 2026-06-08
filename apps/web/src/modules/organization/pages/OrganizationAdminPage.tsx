import { Navigate, Outlet } from 'react-router-dom'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { useOrganizationWorkspace } from '../hooks/useOrganizationWorkspace'
import { getDefaultOrganizationSectionPath } from '../navigation'

export function OrganizationAdminPage() {
  const workspace = useOrganizationWorkspace()

  return (
    <div className="workspace-stack workspace-stack--tight">
      <Outlet context={workspace} />
    </div>
  )
}

export function OrganizationIndexRedirect() {
  const { snapshot } = useAccessSnapshot()

  return <Navigate replace to={getDefaultOrganizationSectionPath(snapshot?.user.permissions ?? [])} />
}
