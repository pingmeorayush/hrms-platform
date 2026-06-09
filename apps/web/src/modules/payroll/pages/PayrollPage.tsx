import { Navigate, Outlet } from 'react-router-dom'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { usePayrollWorkspace } from '../hooks/usePayrollWorkspace'
import { getDefaultPayrollSectionPath } from '../navigation'

export function PayrollPage() {
  const workspace = usePayrollWorkspace()

  return (
    <div className="workspace-stack workspace-stack--tight">
      <Outlet context={workspace} />
    </div>
  )
}

export function PayrollIndexRedirect() {
  const { snapshot } = useAccessSnapshot()

  return <Navigate replace to={getDefaultPayrollSectionPath(snapshot?.user.permissions ?? [])} />
}
