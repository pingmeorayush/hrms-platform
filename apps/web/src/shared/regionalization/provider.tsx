import { useEffect, useMemo, useSyncExternalStore, type PropsWithChildren } from 'react'
import { useQuery } from '@tanstack/react-query'
import { useAppSelector } from '../../app/store/hooks'
import { useAccessSnapshot } from '../../modules/access/hooks/useAccessSnapshot'
import { fetchLocalizationConfiguration } from './api'
import {
  getDemoRegionalPreferenceOverrides,
  subscribeDemoRegionalPreferenceOverrides,
} from './demo-overrides'
import {
  buildLocalizationConfigurationFromSnapshot,
} from './defaults'
import { RegionalizationContext } from './context'
import { setCurrentRegionalSettings } from './state'

export function RegionalizationProvider({ children }: PropsWithChildren) {
  const access = useAppSelector((state) => state.access)
  const { snapshot, source } = useAccessSnapshot()
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const demoOverrides = useSyncExternalStore(
    subscribeDemoRegionalPreferenceOverrides,
    () => getDemoRegionalPreferenceOverrides(snapshot?.user.id),
    () => null,
  )

  const liveQuery = useQuery({
    queryKey: ['localization-configuration', access.apiBaseUrl, access.token],
    queryFn: () => fetchLocalizationConfiguration(access.apiBaseUrl, access.token),
    enabled: liveEnabled,
    staleTime: 60_000,
  })

  const configuration = useMemo(() => {
    if (source === 'live' && liveQuery.data) {
      return liveQuery.data
    }

    return buildLocalizationConfigurationFromSnapshot(snapshot, source === 'demo' ? demoOverrides : null)
  }, [demoOverrides, liveQuery.data, snapshot, source])
  const effectiveSettings = configuration.effective_settings

  useEffect(() => {
    setCurrentRegionalSettings(effectiveSettings)
  }, [effectiveSettings])

  return (
    <RegionalizationContext.Provider
      value={{
        configuration,
        isLoading: source === 'live' ? liveQuery.isLoading && !liveQuery.data : false,
      }}
    >
      {children}
    </RegionalizationContext.Provider>
  )
}
