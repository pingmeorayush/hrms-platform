import { useCallback, useEffect, useMemo, useState } from 'react'
import type { AppNavItem } from './navigation'

export const SHELL_FAVORITES_STORAGE_KEY = 'phoenixhrms.shell.favorites'
const SHELL_FAVORITES_EVENT = 'phoenixhrms.shell.favorites.changed'
const MAX_FAVORITES = 12
const EMPTY_SHELL_FAVORITES: ShellFavoriteDraft[] = []

export type ShellFavoriteIcon = AppNavItem['icon']

export type ShellFavorite = {
  path: string
  label: string
  icon: ShellFavoriteIcon
  description: string
  meta?: string
  pinnedAt: number
}

export type ShellFavoriteDraft = Omit<ShellFavorite, 'pinnedAt'>

function isShellFavorite(value: unknown): value is ShellFavorite {
  if (!value || typeof value !== 'object') {
    return false
  }

  const candidate = value as Partial<ShellFavorite>
  return (
    typeof candidate.path === 'string' &&
    typeof candidate.label === 'string' &&
    typeof candidate.icon === 'string' &&
    typeof candidate.description === 'string' &&
    typeof candidate.pinnedAt === 'number'
  )
}

function sortFavorites(items: ShellFavorite[]) {
  return [...items].sort((left, right) => right.pinnedAt - left.pinnedAt).slice(0, MAX_FAVORITES)
}

function dispatchFavoritesChanged() {
  if (typeof window === 'undefined') {
    return
  }

  window.dispatchEvent(new CustomEvent(SHELL_FAVORITES_EVENT))
}

export function readShellFavorites(): ShellFavorite[] | null {
  if (typeof window === 'undefined') {
    return null
  }

  const raw = window.localStorage.getItem(SHELL_FAVORITES_STORAGE_KEY)
  if (!raw) {
    return null
  }

  try {
    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) {
      window.localStorage.removeItem(SHELL_FAVORITES_STORAGE_KEY)
      return null
    }

    return sortFavorites(parsed.filter(isShellFavorite))
  } catch {
    window.localStorage.removeItem(SHELL_FAVORITES_STORAGE_KEY)
    return null
  }
}

export function writeShellFavorites(items: ShellFavorite[]) {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.setItem(SHELL_FAVORITES_STORAGE_KEY, JSON.stringify(sortFavorites(items)))
  dispatchFavoritesChanged()
}

export function toggleShellFavorite(favorite: ShellFavoriteDraft) {
  const current = readShellFavorites() ?? []
  const exists = current.some((item) => item.path === favorite.path)

  if (exists) {
    writeShellFavorites(current.filter((item) => item.path !== favorite.path))
    return false
  }

  writeShellFavorites([{ ...favorite, pinnedAt: Date.now() }, ...current.filter((item) => item.path !== favorite.path)])
  return true
}

export function useShellFavorites(defaultFavorites: ShellFavoriteDraft[] = EMPTY_SHELL_FAVORITES) {
  const [favorites, setFavorites] = useState<ShellFavorite[]>([])

  useEffect(() => {
    if (typeof window === 'undefined') {
      return
    }

    const stored = readShellFavorites()
    if (stored === null) {
      if (defaultFavorites.length) {
        writeShellFavorites(
          defaultFavorites.map((favorite, index) => ({
            ...favorite,
            pinnedAt: Date.now() - index,
          })),
        )
        return
      }

      setFavorites([])
      return
    }

    setFavorites(stored)
  }, [defaultFavorites])

  useEffect(() => {
    if (typeof window === 'undefined') {
      return
    }

    const syncFavorites = () => {
      setFavorites(readShellFavorites() ?? [])
    }

    window.addEventListener(SHELL_FAVORITES_EVENT, syncFavorites)
    window.addEventListener('storage', syncFavorites)

    return () => {
      window.removeEventListener(SHELL_FAVORITES_EVENT, syncFavorites)
      window.removeEventListener('storage', syncFavorites)
    }
  }, [])

  const favoritePaths = useMemo(() => new Set(favorites.map((item) => item.path)), [favorites])

  const isFavorite = useCallback(
    (path: string) => favoritePaths.has(path),
    [favoritePaths],
  )

  const toggleFavorite = useCallback((favorite: ShellFavoriteDraft) => {
    return toggleShellFavorite(favorite)
  }, [])

  return {
    favorites,
    favoritePaths,
    isFavorite,
    toggleFavorite,
  }
}
