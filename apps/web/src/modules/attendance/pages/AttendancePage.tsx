import { Navigate, Outlet } from 'react-router-dom'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { getDefaultAttendanceSectionPath } from '../navigation'

export function AttendancePage() {
  return (
    <div className="workspace-stack workspace-stack--tight">
      <Outlet />
    </div>
  )
}

export function AttendanceIndexRedirect() {
  const { snapshot } = useAccessSnapshot()
  const permissions = snapshot?.user.permissions ?? []

  return <Navigate replace to={getDefaultAttendanceSectionPath(permissions)} />
}
