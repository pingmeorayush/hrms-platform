import { useOutletContext } from 'react-router-dom'
import { useRecruitmentWorkspace } from '../hooks/useRecruitmentWorkspace'

export function useRecruitmentRouteWorkspace() {
  return useOutletContext<ReturnType<typeof useRecruitmentWorkspace>>()
}
