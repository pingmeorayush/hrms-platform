import { useSyncExternalStore } from 'react'

export function subscribeToBrowserStore(eventName: string, onStoreChange: () => void) {
  if (typeof window === 'undefined') {
    return () => {}
  }

  window.addEventListener(eventName, onStoreChange)
  window.addEventListener('storage', onStoreChange)

  return () => {
    window.removeEventListener(eventName, onStoreChange)
    window.removeEventListener('storage', onStoreChange)
  }
}

export function useBrowserStoreSnapshot(eventName: string, readSnapshot: () => string | null) {
  return useSyncExternalStore(
    (onStoreChange) => subscribeToBrowserStore(eventName, onStoreChange),
    readSnapshot,
    readSnapshot,
  )
}
