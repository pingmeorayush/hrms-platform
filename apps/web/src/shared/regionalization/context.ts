import { createContext, useContext } from 'react'
import { fallbackLocalizationConfiguration } from './defaults'
import type { LocalizationConfiguration } from './types'

export interface RegionalizationContextValue {
  configuration: LocalizationConfiguration
  isLoading: boolean
}

export const RegionalizationContext = createContext<RegionalizationContextValue>({
  configuration: fallbackLocalizationConfiguration,
  isLoading: false,
})

export function useRegionalization() {
  return useContext(RegionalizationContext)
}
