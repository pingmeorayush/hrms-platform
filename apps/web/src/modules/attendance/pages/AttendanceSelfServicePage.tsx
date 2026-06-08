import { Navigate, useLocation } from 'react-router-dom'
import { AttendanceEmployeeWorkspace } from '../components/AttendanceEmployeeWorkspace'

export function AttendanceSelfServicePage() {
  const location = useLocation()

  if (location.pathname === '/attendance/my-attendance' || location.pathname === '/attendance/my-attendance/') {
    return <Navigate replace to="/attendance/my-attendance/history" />
  }

  return <AttendanceEmployeeWorkspace />
}
