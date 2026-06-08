import { beforeEach, describe, expect, it } from 'vitest'
import {
  clearShellRecent,
  getModuleRecentActivity,
  readShellRecent,
  removeShellRecent,
  SHELL_RECENT_STORAGE_KEY,
  touchShellRecent,
} from './recent'

describe('shell recent state', () => {
  beforeEach(() => {
    window.localStorage.removeItem(SHELL_RECENT_STORAGE_KEY)
  })

  it('stores hash-aware recent destinations and keeps the newest visit first', () => {
    touchShellRecent({
      path: '/access#routes',
      label: 'Access · Routes',
      icon: 'access',
    })
    touchShellRecent({
      path: '/access#actions',
      label: 'Access · Actions',
      icon: 'access',
    })
    touchShellRecent({
      path: '/access#routes',
      label: 'Access · Routes',
      icon: 'access',
    })

    const recent = readShellRecent()

    expect(recent?.map((item) => item.path)).toEqual(['/access#routes', '/access#actions'])
  })

  it('supports removing one recent destination and clearing the list', () => {
    touchShellRecent({
      path: '/employees/directory',
      label: 'Employee directory',
      icon: 'employees',
    })
    touchShellRecent({
      path: '/leave/approvals',
      label: 'Leave approvals',
      icon: 'leave',
    })

    removeShellRecent('/employees/directory')
    expect(readShellRecent()?.map((item) => item.path)).toEqual(['/leave/approvals'])

    clearShellRecent()
    expect(readShellRecent()).toBeNull()
  })

  it('maps recent destinations into module activity items with useful path context', () => {
    const activity = getModuleRecentActivity(
      'attendance',
      [
        {
          path: '/attendance/operational-review#correction-9301',
          label: 'Kabir Malik · Attendance correction · Pending',
          icon: 'attendance',
          visitedAt: 1_000,
        },
        {
          path: '/employees/directory',
          label: 'Employee directory',
          icon: 'employees',
          visitedAt: 900,
        },
      ],
      { now: 61_000 },
    )

    expect(activity).toHaveLength(1)
    expect(activity[0]).toMatchObject({
      title: 'Kabir Malik · Attendance correction · Pending',
      detail: 'Operational review · Correction record',
      meta: '1 min ago',
      tone: 'warning',
    })
  })
})
