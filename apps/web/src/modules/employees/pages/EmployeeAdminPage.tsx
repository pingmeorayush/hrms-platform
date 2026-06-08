import { Navigate, Outlet } from 'react-router-dom'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { getDefaultEmployeeSectionPath } from '../navigation'

export function EmployeeAdminPage() {
  return (
    <div className="workspace-stack workspace-stack--tight">
      <Outlet />
    </div>
  )
}

export function EmployeeIndexRedirect() {
  const { snapshot } = useAccessSnapshot()

  return <Navigate replace to={getDefaultEmployeeSectionPath(snapshot?.user.permissions ?? [])} />
}
