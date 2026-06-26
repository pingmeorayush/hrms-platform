import { useOutletContext } from 'react-router-dom'
import { useLearningWorkspace } from '../hooks/useLearningWorkspace'

export function useLearningRouteWorkspace() {
  return useOutletContext<ReturnType<typeof useLearningWorkspace>>()
}
