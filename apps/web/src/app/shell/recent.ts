import { useCallback, useEffect, useMemo, useState } from 'react'
import type { AppNavItem } from './navigation'

export const SHELL_RECENT_STORAGE_KEY = 'phoenixhrms.shell.recent'
const SHELL_RECENT_EVENT = 'phoenixhrms.shell.recent.changed'
const MAX_RECENT_ITEMS = 8

export type ShellRecentIcon = AppNavItem['icon']

export type ShellRecentEntry = {
  path: string
  label: string
  icon: ShellRecentIcon
  visitedAt: number
}

export type ShellRecentDraft = Omit<ShellRecentEntry, 'visitedAt'>
export type ShellRecentModule = 'foundation' | 'organization' | 'employees' | 'attendance' | 'leave' | 'access'
export type ShellRecentActivityTone = 'neutral' | 'info' | 'success' | 'warning'
export type ShellRecentActivityItem = {
  id: string
  path: string
  title: string
  detail: string
  meta: string
  tone: ShellRecentActivityTone
}

function isShellRecentEntry(value: unknown): value is ShellRecentEntry {
  if (!value || typeof value !== 'object') {
    return false
  }

  const candidate = value as Partial<ShellRecentEntry>
  return (
    typeof candidate.path === 'string' &&
    typeof candidate.label === 'string' &&
    typeof candidate.icon === 'string' &&
    typeof candidate.visitedAt === 'number'
  )
}

function sortRecent(items: ShellRecentEntry[]) {
  return [...items].sort((left, right) => right.visitedAt - left.visitedAt).slice(0, MAX_RECENT_ITEMS)
}

function dispatchRecentChanged() {
  if (typeof window === 'undefined') {
    return
  }

  window.dispatchEvent(new CustomEvent(SHELL_RECENT_EVENT))
}

export function readShellRecent(): ShellRecentEntry[] | null {
  if (typeof window === 'undefined') {
    return null
  }

  const raw = window.localStorage.getItem(SHELL_RECENT_STORAGE_KEY)
  if (!raw) {
    return null
  }

  try {
    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) {
      window.localStorage.removeItem(SHELL_RECENT_STORAGE_KEY)
      return null
    }

    return sortRecent(parsed.filter(isShellRecentEntry))
  } catch {
    window.localStorage.removeItem(SHELL_RECENT_STORAGE_KEY)
    return null
  }
}

export function writeShellRecent(items: ShellRecentEntry[]) {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.setItem(SHELL_RECENT_STORAGE_KEY, JSON.stringify(sortRecent(items)))
  dispatchRecentChanged()
}

export function touchShellRecent(entry: ShellRecentDraft) {
  const current = readShellRecent() ?? []
  const next: ShellRecentEntry[] = [
    { ...entry, visitedAt: Date.now() },
    ...current.filter((item) => item.path !== entry.path),
  ]
  writeShellRecent(next)
  return sortRecent(next)
}

export function removeShellRecent(path: string) {
  const current = readShellRecent() ?? []
  writeShellRecent(current.filter((item) => item.path !== path))
}

export function clearShellRecent() {
  if (typeof window === 'undefined') {
    return
  }

  window.localStorage.removeItem(SHELL_RECENT_STORAGE_KEY)
  dispatchRecentChanged()
}

export function getModuleRecentActivity(
  module: ShellRecentModule,
  recentItems: ShellRecentEntry[],
  options?: { limit?: number; now?: number },
): ShellRecentActivityItem[] {
  const limit = options?.limit ?? 6
  const now = options?.now ?? Date.now()

  return recentItems
    .filter((item) => matchesRecentModule(module, item.path))
    .slice(0, limit)
    .map((item) => ({
      id: `${module}:${item.path}`,
      path: item.path,
      title: item.label,
      detail: describeRecentPath(item.path),
      meta: formatRecentVisitedAt(item.visitedAt, now),
      tone: inferRecentTone(item),
    }))
}

export function useShellRecent() {
  const [recentItems, setRecentItems] = useState<ShellRecentEntry[]>([])

  useEffect(() => {
    setRecentItems(readShellRecent() ?? [])
  }, [])

  useEffect(() => {
    if (typeof window === 'undefined') {
      return
    }

    const syncRecent = () => {
      setRecentItems(readShellRecent() ?? [])
    }

    window.addEventListener(SHELL_RECENT_EVENT, syncRecent)
    window.addEventListener('storage', syncRecent)

    return () => {
      window.removeEventListener(SHELL_RECENT_EVENT, syncRecent)
      window.removeEventListener('storage', syncRecent)
    }
  }, [])

  const recentPaths = useMemo(() => new Set(recentItems.map((item) => item.path)), [recentItems])

  const touchRecent = useCallback((entry: ShellRecentDraft) => {
    return touchShellRecent(entry)
  }, [])

  const removeRecent = useCallback((path: string) => {
    removeShellRecent(path)
  }, [])

  const clearRecent = useCallback(() => {
    clearShellRecent()
  }, [])

  return {
    recentItems,
    recentPaths,
    touchRecent,
    removeRecent,
    clearRecent,
  }
}

function matchesRecentModule(module: ShellRecentModule, path: string) {
  switch (module) {
    case 'foundation':
      return path.startsWith('/foundation')
    case 'organization':
      return path.startsWith('/admin/organization')
    case 'employees':
      return path.startsWith('/employees')
    case 'attendance':
      return path.startsWith('/attendance')
    case 'leave':
      return path.startsWith('/leave')
    case 'access':
      return path.startsWith('/access')
    default:
      return false
  }
}

function describeRecentPath(path: string) {
  if (path.startsWith('/attendance/operational-review#correction-')) {
    return 'Operational review · Correction record'
  }

  if (path.startsWith('/attendance/operational-review#exception-')) {
    return 'Operational review · Exception record'
  }

  if (path === '/attendance/operational-review#exceptions') {
    return 'Operational review · Exception queue'
  }

  if (path === '/attendance/operational-review#decisions' || path === '/attendance/operational-review') {
    return 'Operational review · Decision queue'
  }

  if (path === '/attendance/overview') {
    return 'Attendance operations center'
  }

  if (path.startsWith('/attendance/admin-setup/assignments')) {
    return 'Admin setup · Assignments'
  }

  if (path.startsWith('/attendance/admin-setup/shifts')) {
    return 'Admin setup · Shifts'
  }

  if (path.startsWith('/attendance/admin-setup/rosters')) {
    return 'Admin setup · Rosters'
  }

  if (path.startsWith('/attendance/admin-setup/policy')) {
    return 'Admin setup · Policy'
  }

  if (path.startsWith('/attendance/admin-setup/calendars')) {
    return 'Admin setup · Holiday calendars'
  }

  if (path.startsWith('/attendance/my-attendance/history')) {
    return 'My attendance · History'
  }

  if (path.startsWith('/attendance/my-attendance/corrections')) {
    return 'My attendance · Correction requests'
  }

  if (path.startsWith('/attendance/my-attendance/capture')) {
    return 'My attendance · Check in / out'
  }

  if (path.startsWith('/leave/approvals#request-')) {
    return 'Approvals · Request review'
  }

  if (path === '/leave/approvals') {
    return 'Approvals queue'
  }

  if (path === '/leave/overview') {
    return 'Leave operations center'
  }

  if (path.startsWith('/leave/requests')) {
    return 'Requests workspace'
  }

  if (path.startsWith('/leave/policy-admin')) {
    return 'Policy admin'
  }

  if (path === '/employees/overview') {
    return 'Employees operations center'
  }

  if (path.startsWith('/employees/directory')) {
    return 'Directory workspace'
  }

  if (path.startsWith('/employees/lifecycle-watch')) {
    return 'Lifecycle watch'
  }

  if (path.startsWith('/employees/onboarding')) {
    return 'Onboarding queue'
  }

  if (path.startsWith('/employees/documents')) {
    return 'Document registry'
  }

  if (path.startsWith('/employees/audit')) {
    return 'Audit trail'
  }

  const employeeDetailMatch = path.match(/^\/employees\/\d+\/([^#/?]+)/)
  if (employeeDetailMatch) {
    return `Employee workspace · ${formatPathSegment(employeeDetailMatch[1])}`
  }

  if (path === '/admin/organization' || path === '/admin/organization/overview') {
    return 'Organization operations center'
  }

  if (path.startsWith('/admin/organization/company-profile')) {
    return 'Company profile'
  }

  if (path.startsWith('/admin/organization/structure')) {
    return 'Structure registry'
  }

  if (path.startsWith('/admin/organization/locations')) {
    return 'Location registry'
  }

  if (path.startsWith('/admin/organization/cost-centers')) {
    return 'Cost center registry'
  }

  if (path === '/access#actions') {
    return 'Access contract · Actions'
  }

  if (path === '/access#diagnostics') {
    return 'Access contract · Diagnostics'
  }

  if (path === '/access#routes' || path === '/access') {
    return 'Access contract · Routes'
  }

  if (path.startsWith('/foundation')) {
    return 'Foundation command center'
  }

  return 'Workspace'
}

function inferRecentTone(item: ShellRecentEntry): ShellRecentActivityTone {
  const haystack = `${item.label} ${item.path}`.toLowerCase()

  if (
    haystack.includes('pending') ||
    haystack.includes('review') ||
    haystack.includes('approval') ||
    haystack.includes('exception') ||
    haystack.includes('expiring') ||
    haystack.includes('rejected') ||
    haystack.includes('changes requested')
  ) {
    return 'warning'
  }

  if (haystack.includes('approved') || haystack.includes('healthy') || haystack.includes('enabled')) {
    return 'success'
  }

  if (haystack.includes('audit') || haystack.includes('diagnostic') || haystack.includes('policy')) {
    return 'info'
  }

  return 'neutral'
}

function formatRecentVisitedAt(visitedAt: number, now: number) {
  const elapsedMs = Math.max(now - visitedAt, 0)
  const elapsedMinutes = Math.floor(elapsedMs / 60000)

  if (elapsedMinutes < 1) {
    return 'Just now'
  }

  if (elapsedMinutes < 60) {
    return `${elapsedMinutes} min${elapsedMinutes === 1 ? '' : 's'} ago`
  }

  const elapsedHours = Math.floor(elapsedMinutes / 60)
  if (elapsedHours < 24) {
    return `${elapsedHours} hour${elapsedHours === 1 ? '' : 's'} ago`
  }

  const elapsedDays = Math.floor(elapsedHours / 24)
  return `${elapsedDays} day${elapsedDays === 1 ? '' : 's'} ago`
}

function formatPathSegment(segment: string) {
  return segment
    .split('-')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
}
