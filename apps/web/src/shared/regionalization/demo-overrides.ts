import type { RegionalPreferenceOverrides } from './types'

const demoRegionalPreferenceOverrides = new Map<number, RegionalPreferenceOverrides>()
const listeners = new Set<() => void>()

function emitChange() {
  listeners.forEach((listener) => listener())
}

export function getDemoRegionalPreferenceOverrides(userId: number | null | undefined) {
  if (!userId) {
    return null
  }

  return demoRegionalPreferenceOverrides.get(userId) ?? null
}

export function setDemoRegionalPreferenceOverrides(userId: number, overrides: RegionalPreferenceOverrides) {
  const hasAnyOverride = Object.values(overrides).some((value) => value !== null && value !== undefined)

  if (!hasAnyOverride) {
    demoRegionalPreferenceOverrides.delete(userId)
  } else {
    demoRegionalPreferenceOverrides.set(userId, overrides)
  }

  emitChange()
}

export function subscribeDemoRegionalPreferenceOverrides(listener: () => void) {
  listeners.add(listener)

  return () => {
    listeners.delete(listener)
  }
}
