import { useQuery } from '@tanstack/react-query'
import { fetchAccessSnapshot } from '../api/accessApi'
import { getDemoSnapshot } from '../data/demoSnapshots'
import { useAppSelector } from '../../../app/store/hooks'

export function useAccessSnapshot() {
  const access = useAppSelector((state) => state.access)
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0

  const liveQuery = useQuery({
    queryKey: ['access-snapshot', access.apiBaseUrl, access.token],
    queryFn: () => fetchAccessSnapshot(access.apiBaseUrl, access.token),
    enabled: liveEnabled,
  })

  if (access.mode === 'demo') {
    return {
      snapshot: getDemoSnapshot(access.demoPersona),
      isLoading: false,
      error: null as Error | null,
      source: 'demo' as const,
    }
  }

  if (!access.token.trim()) {
    return {
      snapshot: null,
      isLoading: false,
      error: null as Error | null,
      source: 'live' as const,
    }
  }

  return {
    snapshot: liveQuery.data ?? null,
    isLoading: liveQuery.isLoading,
    error: (liveQuery.error as Error | null) ?? null,
    source: 'live' as const,
  }
}
