import type { EffectiveRegionalSettings } from './types'
import { fallbackRegionalSettings } from './defaults'

let currentRegionalSettings: EffectiveRegionalSettings = fallbackRegionalSettings

export function getCurrentRegionalSettings() {
  return currentRegionalSettings
}

export function setCurrentRegionalSettings(settings: EffectiveRegionalSettings) {
  currentRegionalSettings = settings
}
