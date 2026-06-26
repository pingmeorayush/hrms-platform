import { useCallback, useMemo } from 'react'
import { useBrowserStoreSnapshot } from './browserStore'

export type CommandCenterModule = 'attendance' | 'leave' | 'employees' | 'organization' | 'access' | 'foundation'
export type CommandCenterTone = 'neutral' | 'info' | 'success' | 'warning' | 'danger'

export type CommandCenterActivityEvent = {
  id: string
  module: CommandCenterModule
  title: string
  detail: string
  meta: string
  tone: CommandCenterTone
  path: string
  createdAt: number
}

export type CommandCenterAlertOverride = {
  id: string
  module: CommandCenterModule
  title: string
  detail: string
  meta: string
  tone: CommandCenterTone
  path?: string
  updatedAt: number
}

type CommandCenterEventStore = {
  activities: CommandCenterActivityEvent[]
  alerts: CommandCenterAlertOverride[]
}
const EMPTY_COMMAND_CENTER_EVENT_STORE: CommandCenterEventStore = {
  activities: [],
  alerts: [],
}

export const COMMAND_CENTER_EVENTS_STORAGE_KEY = 'phoenixhrms.command-center.events'
const COMMAND_CENTER_EVENTS_EVENT = 'phoenixhrms.command-center.events.changed'
const MAX_ACTIVITY_EVENTS = 18
const MAX_ALERT_OVERRIDES = 18
const ACTIVITY_EVENT_TTL_MS = 1000 * 60 * 60 * 24
const ALERT_OVERRIDE_TTL_MS = 1000 * 60 * 60 * 6

function isObjectLike(value: unknown): value is Record<string, unknown> {
  return Boolean(value) && typeof value === 'object'
}

function isCommandCenterActivityEvent(value: unknown): value is CommandCenterActivityEvent {
  if (!isObjectLike(value)) {
    return false
  }

  return (
    typeof value.id === 'string' &&
    typeof value.module === 'string' &&
    typeof value.title === 'string' &&
    typeof value.detail === 'string' &&
    typeof value.meta === 'string' &&
    typeof value.tone === 'string' &&
    typeof value.path === 'string' &&
    typeof value.createdAt === 'number'
  )
}

function isCommandCenterAlertOverride(value: unknown): value is CommandCenterAlertOverride {
  if (!isObjectLike(value)) {
    return false
  }

  return (
    typeof value.id === 'string' &&
    typeof value.module === 'string' &&
    typeof value.title === 'string' &&
    typeof value.detail === 'string' &&
    typeof value.meta === 'string' &&
    typeof value.tone === 'string' &&
    (typeof value.path === 'undefined' || typeof value.path === 'string') &&
    typeof value.updatedAt === 'number'
  )
}

function dispatchCommandCenterEventsChanged() {
  if (typeof window === 'undefined') {
    return
  }

  window.dispatchEvent(new CustomEvent(COMMAND_CENTER_EVENTS_EVENT))
}

function pruneStore(store: CommandCenterEventStore, now = Date.now()): CommandCenterEventStore {
  return {
    activities: [...store.activities]
      .filter((event) => now - event.createdAt <= ACTIVITY_EVENT_TTL_MS)
      .sort((left, right) => right.createdAt - left.createdAt)
      .slice(0, MAX_ACTIVITY_EVENTS),
    alerts: [...store.alerts]
      .filter((alert) => now - alert.updatedAt <= ALERT_OVERRIDE_TTL_MS)
      .sort((left, right) => right.updatedAt - left.updatedAt)
      .slice(0, MAX_ALERT_OVERRIDES),
  }
}

function readCommandCenterEventsRaw() {
  if (typeof window === 'undefined') {
    return null
  }

  return window.localStorage.getItem(COMMAND_CENTER_EVENTS_STORAGE_KEY)
}

function parseCommandCenterEvents(raw: string | null): CommandCenterEventStore | null {
  if (!raw) {
    return null
  }

  try {
    const parsed = JSON.parse(raw)
    if (!isObjectLike(parsed)) {
      return null
    }

    return pruneStore({
      activities: Array.isArray(parsed.activities)
        ? parsed.activities.filter(isCommandCenterActivityEvent)
        : [],
      alerts: Array.isArray(parsed.alerts)
        ? parsed.alerts.filter(isCommandCenterAlertOverride)
        : [],
    })
  } catch {
    return null
  }
}

export function readCommandCenterEvents(): CommandCenterEventStore {
  const raw = readCommandCenterEventsRaw()
  const parsed = parseCommandCenterEvents(raw)

  if (typeof window !== 'undefined') {
    if (raw && parsed === null) {
      window.localStorage.removeItem(COMMAND_CENTER_EVENTS_STORAGE_KEY)
    } else if (raw && parsed) {
      const normalized = JSON.stringify(parsed)
      if (normalized !== raw) {
        window.localStorage.setItem(COMMAND_CENTER_EVENTS_STORAGE_KEY, normalized)
      }
    }
  }

  return parsed ?? EMPTY_COMMAND_CENTER_EVENT_STORE
}

export function writeCommandCenterEvents(store: CommandCenterEventStore) {
  if (typeof window === 'undefined') {
    return
  }

  const next = pruneStore(store)
  window.localStorage.setItem(COMMAND_CENTER_EVENTS_STORAGE_KEY, JSON.stringify(next))
  dispatchCommandCenterEventsChanged()
}

export function pushCommandCenterActivityEvent(
  event: Omit<CommandCenterActivityEvent, 'createdAt' | 'id'> & { id?: string },
) {
  const current = readCommandCenterEvents()
  const createdAt = Date.now()
  const nextEvent: CommandCenterActivityEvent = {
    ...event,
    id: event.id ?? `${event.module}:${event.path}:${createdAt}`,
    createdAt,
  }

  writeCommandCenterEvents({
    ...current,
    activities: [
      nextEvent,
      ...current.activities.filter((entry) => entry.id !== nextEvent.id),
    ],
  })

  return nextEvent
}

export function setCommandCenterAlertOverride(
  alert: Omit<CommandCenterAlertOverride, 'updatedAt'>,
) {
  const current = readCommandCenterEvents()
  const nextAlert: CommandCenterAlertOverride = {
    ...alert,
    updatedAt: Date.now(),
  }

  writeCommandCenterEvents({
    ...current,
    alerts: [
      nextAlert,
      ...current.alerts.filter((entry) => !(entry.module === nextAlert.module && entry.id === nextAlert.id)),
    ],
  })

  return nextAlert
}

export function clearCommandCenterEvents() {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.removeItem(COMMAND_CENTER_EVENTS_STORAGE_KEY)
  dispatchCommandCenterEventsChanged()
}

export function applyCommandCenterAlertOverrides<
  T extends {
    id: string
    title: string
    detail: string
    meta: string
    tone: CommandCenterTone
    path?: string
  },
>(items: T[], overrides: CommandCenterAlertOverride[]) {
  if (!overrides.length) {
    return items
  }

  const overrideMap = new Map(overrides.map((override) => [override.id, override]))

  return items.map((item) => {
    const override = overrideMap.get(item.id)
    if (!override) {
      return item
    }

    return {
      ...item,
      title: override.title,
      detail: override.detail,
      meta: override.meta,
      tone: override.tone,
      path: override.path,
    }
  })
}

export function useCommandCenterEvents(module: CommandCenterModule) {
  const storeRaw = useBrowserStoreSnapshot(COMMAND_CENTER_EVENTS_EVENT, readCommandCenterEventsRaw)
  const store = useMemo(
    () => parseCommandCenterEvents(storeRaw) ?? EMPTY_COMMAND_CENTER_EVENT_STORE,
    [storeRaw],
  )

  const activityEvents = useMemo(
    () => store.activities.filter((event) => event.module === module),
    [module, store.activities],
  )
  const alertOverrides = useMemo(
    () => store.alerts.filter((alert) => alert.module === module),
    [module, store.alerts],
  )

  const pushActivity = useCallback(
    (event: Omit<CommandCenterActivityEvent, 'createdAt' | 'id' | 'module'> & { id?: string }) =>
      pushCommandCenterActivityEvent({ ...event, module }),
    [module],
  )
  const setAlert = useCallback(
    (alert: Omit<CommandCenterAlertOverride, 'updatedAt' | 'module'>) =>
      setCommandCenterAlertOverride({ ...alert, module }),
    [module],
  )

  return {
    activityEvents,
    alertOverrides,
    pushActivity,
    setAlert,
  }
}
