import { Navigate, Outlet } from 'react-router-dom'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { useRecruitmentWorkspace } from '../hooks/useRecruitmentWorkspace'
import { getDefaultRecruitmentSectionPath } from '../navigation'

export function RecruitmentPage() {
  const workspace = useRecruitmentWorkspace()

  return (
    <div className="workspace-stack workspace-stack--tight">
      <Outlet context={workspace} />
    </div>
  )
}

export function RecruitmentIndexRedirect() {
  const { snapshot } = useAccessSnapshot()

  return <Navigate replace to={getDefaultRecruitmentSectionPath(snapshot?.user.permissions ?? [])} />
}
