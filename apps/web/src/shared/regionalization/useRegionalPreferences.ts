import { useMutation, useQueryClient } from '@tanstack/react-query'
import { useAppSelector } from '../../app/store/hooks'
import { useAccessSnapshot } from '../../modules/access/hooks/useAccessSnapshot'
import type { AccessSnapshot } from '../../modules/access/types'
import { buildLocalizationConfigurationFromSnapshot } from './defaults'
import {
  getDemoRegionalPreferenceOverrides,
  setDemoRegionalPreferenceOverrides,
} from './demo-overrides'
import { useRegionalization } from './context'
import { updateLocalizationPreferences } from './api'
import { setCurrentRegionalSettings } from './state'
import type { LocalizationConfiguration, RegionalPreferenceOverrides } from './types'

export function useRegionalPreferences() {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const { configuration } = useRegionalization()

  const mutation = useMutation({
    mutationFn: async (payload: RegionalPreferenceOverrides) => {
      if (source === 'demo') {
        const userId = snapshot?.user.id ?? null
        if (!userId) {
          return configuration
        }

        setDemoRegionalPreferenceOverrides(userId, payload)

        return buildLocalizationConfigurationFromSnapshot(snapshot, payload)
      }

      return updateLocalizationPreferences(access.apiBaseUrl, access.token, payload)
    },
    onSuccess: (nextConfiguration) => {
      setCurrentRegionalSettings(nextConfiguration.effective_settings)

      if (source === 'demo') {
        return
      }

      queryClient.setQueryData<LocalizationConfiguration>(
        ['localization-configuration', access.apiBaseUrl, access.token],
        nextConfiguration,
      )

      queryClient.setQueryData<AccessSnapshot>(
        ['access-snapshot', access.apiBaseUrl, access.token],
        (current) =>
          current
            ? {
                ...current,
                user: {
                  ...current.user,
                  regional_settings: nextConfiguration.effective_settings,
                },
              }
            : current,
      )
    },
  })

  return {
    source,
    configuration,
    isSaving: mutation.isPending,
    savePreferences: mutation.mutateAsync,
    demoOverrides: getDemoRegionalPreferenceOverrides(snapshot?.user.id),
  }
}
