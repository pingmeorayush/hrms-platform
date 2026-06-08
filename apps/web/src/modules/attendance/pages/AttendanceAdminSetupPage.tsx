import { Navigate, useLocation } from 'react-router-dom'
import { AttendanceAdminWorkspace } from '../components/AttendanceAdminWorkspace'

export function AttendanceAdminSetupPage() {
  const location = useLocation()

  if (location.pathname === '/attendance/admin-setup' || location.pathname === '/attendance/admin-setup/') {
    return <Navigate replace to="/attendance/admin-setup/policy" />
  }

  return <AttendanceAdminWorkspace />
}
