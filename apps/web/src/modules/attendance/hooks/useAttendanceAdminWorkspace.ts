import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  createHoliday,
  createHolidayCalendar,
  createShift,
  createShiftAssignment,
  createShiftRoster,
  fetchAttendanceAdminWorkspace,
  updateAttendancePolicy,
  updateHoliday,
  updateHolidayCalendar,
  updateShift,
  updateShiftAssignment,
  updateShiftRoster,
} from '../api/attendanceAdminApi'
import { buildDemoAttendanceWorkspace } from '../data/demoAttendanceAdmin'
import type {
  AttendanceAdminWorkspaceData,
  AttendancePolicy,
  AttendancePolicyUpdatePayload,
  Holiday,
  HolidayCalendar,
  HolidayCalendarPayload,
  HolidayPayload,
  Shift,
  ShiftAssignment,
  ShiftAssignmentPayload,
  ShiftRoster,
  ShiftRosterPayload,
  ShiftRosterStatus,
  ShiftRosterUpdatePayload,
  ShiftPayload,
} from '../types'

const queryScope = 'attendance-admin-workspace'

export function useAttendanceAdminWorkspace(options?: { enabled?: boolean }) {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = snapshot?.user.permissions ?? []
  const workspaceEnabled = options?.enabled ?? true
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}`
  const [demoState, setDemoState] = useState<{
    key: string
    data: AttendanceAdminWorkspaceData
  }>(() => ({
    key: demoStateKey,
    data: buildDemoAttendanceWorkspace(snapshot),
  }))

  if (demoState.key !== demoStateKey) {
    setDemoState({
      key: demoStateKey,
      data: buildDemoAttendanceWorkspace(snapshot),
    })
  }

  const queryKey = useMemo(
    () => [queryScope, access.apiBaseUrl, access.token] as const,
    [access.apiBaseUrl, access.token],
  )

  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0

  const liveQuery = useQuery({
    queryKey,
    queryFn: () => fetchAttendanceAdminWorkspace(access.apiBaseUrl, access.token),
    enabled: liveEnabled && workspaceEnabled,
  })

  const invalidateWorkspace = async () => {
    await queryClient.invalidateQueries({ queryKey })
  }

  const updatePolicyMutation = useMutation({
    mutationFn: (payload: AttendancePolicyUpdatePayload) =>
      updateAttendancePolicy(access.apiBaseUrl, access.token, payload),
    onSuccess: invalidateWorkspace,
  })

  const holidayCalendarMutation = useMutation({
    mutationFn: ({
      holidayCalendarId,
      payload,
    }: {
      holidayCalendarId?: number
      payload: HolidayCalendarPayload
    }) =>
      holidayCalendarId
        ? updateHolidayCalendar(access.apiBaseUrl, access.token, holidayCalendarId, payload)
        : createHolidayCalendar(access.apiBaseUrl, access.token, payload),
    onSuccess: invalidateWorkspace,
  })

  const holidayMutation = useMutation({
    mutationFn: ({
      holidayCalendarId,
      holidayId,
      payload,
    }: {
      holidayCalendarId: number
      holidayId?: number
      payload: HolidayPayload
    }) =>
      holidayId
        ? updateHoliday(access.apiBaseUrl, access.token, holidayCalendarId, holidayId, payload)
        : createHoliday(access.apiBaseUrl, access.token, holidayCalendarId, payload),
    onSuccess: invalidateWorkspace,
  })

  const shiftMutation = useMutation({
    mutationFn: ({ shiftId, payload }: { shiftId?: number; payload: ShiftPayload }) =>
      shiftId
        ? updateShift(access.apiBaseUrl, access.token, shiftId, payload)
        : createShift(access.apiBaseUrl, access.token, payload),
    onSuccess: invalidateWorkspace,
  })

  const assignmentMutation = useMutation({
    mutationFn: ({
      shiftAssignmentId,
      payload,
    }: {
      shiftAssignmentId?: number
      payload: ShiftAssignmentPayload
    }) =>
      shiftAssignmentId
        ? updateShiftAssignment(access.apiBaseUrl, access.token, shiftAssignmentId, payload)
        : createShiftAssignment(access.apiBaseUrl, access.token, payload),
    onSuccess: invalidateWorkspace,
  })

  const rosterMutation = useMutation({
    mutationFn: ({
      shiftRosterId,
      payload,
    }: {
      shiftRosterId?: number
      payload: ShiftRosterPayload & { status: ShiftRosterStatus }
    }) =>
      shiftRosterId
        ? updateShiftRoster(access.apiBaseUrl, access.token, shiftRosterId, {
            shift_id: payload.shift_id,
            work_date: payload.work_date,
            notes: payload.notes ?? null,
            status: payload.status,
          } satisfies ShiftRosterUpdatePayload)
        : createShiftRoster(access.apiBaseUrl, access.token, payload),
    onSuccess: invalidateWorkspace,
  })

  const data = !workspaceEnabled ? null : source === 'demo' ? demoState.data : liveQuery.data ?? null
  const canEditPolicy = snapshot ? permissions.includes('attendance.edit') : access.mode === 'demo'
  const canManageShift = snapshot ? permissions.includes('attendance.manage_shift') : access.mode === 'demo'
  const canManageRoster = snapshot ? permissions.includes('attendance.manage_roster') : access.mode === 'demo'
  const canManageAny = canEditPolicy || canManageShift || canManageRoster

  return {
    source,
    snapshot,
    data,
    canEditPolicy,
    canManageShift,
    canManageRoster,
    canManageAny,
    isLoading: workspaceEnabled && source === 'live' ? liveQuery.isLoading : false,
    error: workspaceEnabled && source === 'live' ? ((liveQuery.error as Error | null) ?? null) : null,
    isSaving:
      updatePolicyMutation.isPending ||
      holidayCalendarMutation.isPending ||
      holidayMutation.isPending ||
      shiftMutation.isPending ||
      assignmentMutation.isPending ||
      rosterMutation.isPending,
    async savePolicy(payload: AttendancePolicyUpdatePayload) {
      if (!data) {
        throw new Error('Attendance policy is unavailable.')
      }

      if (source === 'demo') {
        const nextPolicy: AttendancePolicy = {
          ...data.policy,
          ...payload,
          overtime_after_minutes: payload.overtime_eligible ? payload.overtime_after_minutes ?? null : null,
          allowed_radius_meters: payload.enforce_geofence ? payload.allowed_radius_meters ?? null : null,
          updated_at: new Date().toISOString(),
        }

        setDemoState((current) => ({
          ...current,
          data: {
            ...current.data,
            policy: nextPolicy,
          },
        }))

        return nextPolicy
      }

      return updatePolicyMutation.mutateAsync(payload)
    },
    async saveHolidayCalendar(holidayCalendarId: number | undefined, payload: HolidayCalendarPayload) {
      if (!data) {
        throw new Error('Holiday calendars are unavailable.')
      }

      if (source === 'demo') {
        ensureUniqueHolidayCalendarCode(data.holidayCalendars, payload.code, holidayCalendarId)

        const record = buildHolidayCalendarRecord(data, holidayCalendarId, payload)

        setDemoState((current) => ({
          ...current,
          data: {
            ...current.data,
            holidayCalendars: upsertRecord(current.data.holidayCalendars, record, sortByName),
          },
        }))

        return record
      }

      return holidayCalendarMutation.mutateAsync({ holidayCalendarId, payload })
    },
    async saveHoliday(
      holidayCalendarId: number,
      holidayId: number | undefined,
      payload: HolidayPayload,
    ) {
      if (!data) {
        throw new Error('Holiday calendars are unavailable.')
      }

      if (source === 'demo') {
        const calendar = data.holidayCalendars.find((record) => record.id === holidayCalendarId)

        if (!calendar) {
          throw new Error('The selected holiday calendar could not be found.')
        }

        ensureUniqueHolidayDate(calendar.holidays, payload.holiday_date, holidayId)

        const holiday = buildHolidayRecord(calendar, holidayId, payload)

        setDemoState((current) => ({
          ...current,
          data: {
            ...current.data,
            holidayCalendars: current.data.holidayCalendars.map((record) =>
              record.id === holidayCalendarId
                ? {
                    ...record,
                    holidays: upsertRecord(record.holidays, holiday, sortHolidays),
                    updated_at: new Date().toISOString(),
                  }
                : record,
            ),
          },
        }))

        return holiday
      }

      return holidayMutation.mutateAsync({ holidayCalendarId, holidayId, payload })
    },
    async saveShift(shiftId: number | undefined, payload: ShiftPayload) {
      if (!data) {
        throw new Error('Shift records are unavailable.')
      }

      if (source === 'demo') {
        ensureUniqueShiftCode(data.shifts, payload.code, shiftId)

        const shift = buildShiftRecord(data.shifts, shiftId, payload)

        setDemoState((current) => ({
          ...current,
          data: {
            ...current.data,
            shifts: upsertRecord(current.data.shifts, shift, sortByName),
          },
        }))

        return shift
      }

      return shiftMutation.mutateAsync({ shiftId, payload })
    },
    async saveAssignment(shiftAssignmentId: number | undefined, payload: ShiftAssignmentPayload) {
      if (!data) {
        throw new Error('Shift assignments are unavailable.')
      }

      if (source === 'demo') {
        ensureAssignmentScopeConflict(data.assignments, payload, shiftAssignmentId)

        const assignment = buildShiftAssignmentRecord(data, shiftAssignmentId, payload)

        setDemoState((current) => ({
          ...current,
          data: {
            ...current.data,
            assignments: upsertRecord(current.data.assignments, assignment, sortAssignments),
          },
        }))

        return assignment
      }

      return assignmentMutation.mutateAsync({ shiftAssignmentId, payload })
    },
    async saveRoster(shiftRosterId: number | undefined, payload: ShiftRosterPayload & { status: ShiftRosterStatus }) {
      if (!data) {
        throw new Error('Roster entries are unavailable.')
      }

      if (source === 'demo') {
        ensureRosterConflict(data.rosters, payload, shiftRosterId)

        const roster = buildShiftRosterRecord(data, shiftRosterId, payload)

        setDemoState((current) => ({
          ...current,
          data: {
            ...current.data,
            rosters: upsertRecord(current.data.rosters, roster, sortRosters),
          },
        }))

        return roster
      }

      return rosterMutation.mutateAsync({ shiftRosterId, payload })
    },
  }
}

function ensureUniqueHolidayCalendarCode(
  calendars: HolidayCalendar[],
  code: string,
  holidayCalendarId: number | undefined,
) {
  const duplicate = calendars.find(
    (record) => record.id !== holidayCalendarId && record.code.toLowerCase() === code.trim().toLowerCase(),
  )

  if (duplicate) {
    throw new ApiRequestError('Holiday calendar code must be unique.', 422, {
      code: ['This holiday calendar code is already in use.'],
    })
  }
}

function ensureUniqueHolidayDate(holidays: Holiday[], holidayDate: string, holidayId: number | undefined) {
  const duplicate = holidays.find(
    (record) => record.id !== holidayId && record.holiday_date === holidayDate,
  )

  if (duplicate) {
    throw new ApiRequestError('Holiday date conflicts with an existing holiday.', 422, {
      holiday_date: ['A holiday already exists for this date in the selected calendar.'],
    })
  }
}

function ensureUniqueShiftCode(shifts: Shift[], code: string, shiftId: number | undefined) {
  const duplicate = shifts.find(
    (record) => record.id !== shiftId && record.code.toLowerCase() === code.trim().toLowerCase(),
  )

  if (duplicate) {
    throw new ApiRequestError('Shift code must be unique.', 422, {
      code: ['This shift code is already in use.'],
    })
  }
}

function ensureAssignmentScopeConflict(
  assignments: ShiftAssignment[],
  payload: ShiftAssignmentPayload,
  shiftAssignmentId: number | undefined,
) {
  const nextTargetId = getAssignmentTargetId(payload)
  const conflict = assignments.find((record) => {
    if (record.id === shiftAssignmentId || record.assignment_type !== payload.assignment_type) {
      return false
    }

    if (getAssignmentTargetId(record) !== nextTargetId) {
      return false
    }

    return dateRangesOverlap(
      record.effective_from,
      record.effective_to,
      payload.effective_from,
      payload.effective_to ?? null,
    )
  })

  if (conflict) {
    throw new ApiRequestError('Shift assignment overlaps an existing assignment for the same scope.', 422, {
      effective_from: ['The selected date range overlaps an existing assignment.'],
    })
  }
}

function ensureRosterConflict(
  rosters: ShiftRoster[],
  payload: ShiftRosterPayload & { status: ShiftRosterStatus },
  shiftRosterId: number | undefined,
) {
  const conflict = rosters.find(
    (record) =>
      record.id !== shiftRosterId &&
      record.employee.id === payload.employee_id &&
      record.work_date === payload.work_date &&
      record.status === 'scheduled' &&
      payload.status === 'scheduled',
  )

  if (conflict) {
    throw new ApiRequestError('Roster entry conflicts with an existing scheduled shift.', 422, {
      work_date: ['This employee already has a scheduled roster entry on the selected date.'],
    })
  }
}

function buildHolidayCalendarRecord(
  data: AttendanceAdminWorkspaceData,
  holidayCalendarId: number | undefined,
  payload: HolidayCalendarPayload,
): HolidayCalendar {
  const existing = holidayCalendarId
    ? data.holidayCalendars.find((record) => record.id === holidayCalendarId) ?? null
    : null
  const timestamp = new Date().toISOString()

  return {
    id: existing?.id ?? nextId(data.holidayCalendars),
    code: payload.code.trim(),
    name: payload.name.trim(),
    description: normalizeNullableString(payload.description),
    location:
      payload.location_id === undefined
        ? (existing?.location ?? null)
        : data.locations.find((record) => record.id === payload.location_id) ?? null,
    department:
      payload.department_id === undefined
        ? (existing?.department ?? null)
        : data.departments.find((record) => record.id === payload.department_id) ?? null,
    is_default: payload.is_default,
    status: payload.status,
    holidays: existing?.holidays ?? [],
    created_at: existing?.created_at ?? timestamp,
    updated_at: timestamp,
  }
}

function buildHolidayRecord(
  calendar: HolidayCalendar,
  holidayId: number | undefined,
  payload: HolidayPayload,
): Holiday {
  const existing = holidayId ? calendar.holidays.find((record) => record.id === holidayId) ?? null : null
  const timestamp = new Date().toISOString()

  return {
    id: existing?.id ?? nextId(calendar.holidays),
    holiday_calendar_id: calendar.id,
    name: payload.name.trim(),
    holiday_date: payload.holiday_date,
    type: payload.type,
    is_optional: payload.is_optional,
    description: normalizeNullableString(payload.description),
    created_at: existing?.created_at ?? timestamp,
    updated_at: timestamp,
  }
}

function buildShiftRecord(
  shifts: Shift[],
  shiftId: number | undefined,
  payload: ShiftPayload,
): Shift {
  const existing = shiftId ? shifts.find((record) => record.id === shiftId) ?? null : null
  const timestamp = new Date().toISOString()

  return {
    id: existing?.id ?? nextId(shifts),
    code: payload.code.trim(),
    name: payload.name.trim(),
    description: normalizeNullableString(payload.description),
    start_time: payload.start_time,
    end_time: payload.end_time,
    break_duration_minutes: payload.break_duration_minutes,
    grace_minutes: payload.grace_minutes,
    working_hours_minutes: payload.working_hours_minutes,
    is_overnight: payload.end_time <= payload.start_time,
    status: payload.status,
    created_at: existing?.created_at ?? timestamp,
    updated_at: timestamp,
  }
}

function buildShiftAssignmentRecord(
  data: AttendanceAdminWorkspaceData,
  shiftAssignmentId: number | undefined,
  payload: ShiftAssignmentPayload,
): ShiftAssignment {
  const existing = shiftAssignmentId
    ? data.assignments.find((record) => record.id === shiftAssignmentId) ?? null
    : null
  const shift = data.shifts.find((record) => record.id === payload.shift_id)

  if (!shift) {
    throw new Error('The selected shift could not be found.')
  }

  const timestamp = new Date().toISOString()

  return {
    id: existing?.id ?? nextId(data.assignments),
    assignment_type: payload.assignment_type,
    shift,
    employee:
      payload.assignment_type === 'employee'
        ? data.employees.find((record) => record.id === payload.employee_id) ?? null
        : null,
    department:
      payload.assignment_type === 'department'
        ? data.departments.find((record) => record.id === payload.department_id) ?? null
        : null,
    location:
      payload.assignment_type === 'location'
        ? data.locations.find((record) => record.id === payload.location_id) ?? null
        : null,
    effective_from: payload.effective_from,
    effective_to: payload.effective_to ?? null,
    notes: normalizeNullableString(payload.notes),
    status: payload.status,
    created_at: existing?.created_at ?? timestamp,
    updated_at: timestamp,
  }
}

function buildShiftRosterRecord(
  data: AttendanceAdminWorkspaceData,
  shiftRosterId: number | undefined,
  payload: ShiftRosterPayload & { status: ShiftRosterStatus },
): ShiftRoster {
  const existing = shiftRosterId ? data.rosters.find((record) => record.id === shiftRosterId) ?? null : null
  const shift = data.shifts.find((record) => record.id === payload.shift_id)
  const employee =
    existing?.employee ??
    data.employees.find((record) => record.id === payload.employee_id) ??
    null

  if (!shift || !employee) {
    throw new Error('The selected roster references are incomplete.')
  }

  const timestamp = new Date().toISOString()

  return {
    id: existing?.id ?? nextId(data.rosters),
    employee,
    shift,
    work_date: payload.work_date,
    notes: normalizeNullableString(payload.notes),
    status: payload.status,
    created_at: existing?.created_at ?? timestamp,
    updated_at: timestamp,
  }
}

function getAssignmentTargetId(
  assignment:
    | ShiftAssignment
    | ShiftAssignmentPayload,
) {
  switch (assignment.assignment_type) {
    case 'employee':
      return 'employee' in assignment ? assignment.employee?.id ?? null : assignment.employee_id ?? null
    case 'department':
      return 'department' in assignment ? assignment.department?.id ?? null : assignment.department_id ?? null
    case 'location':
      return 'location' in assignment ? assignment.location?.id ?? null : assignment.location_id ?? null
    default:
      return null
  }
}

function dateRangesOverlap(
  leftStart: string,
  leftEnd: string | null,
  rightStart: string,
  rightEnd: string | null,
) {
  const leftStartTime = new Date(leftStart).getTime()
  const leftEndTime = new Date(leftEnd ?? '9999-12-31').getTime()
  const rightStartTime = new Date(rightStart).getTime()
  const rightEndTime = new Date(rightEnd ?? '9999-12-31').getTime()

  return leftStartTime <= rightEndTime && rightStartTime <= leftEndTime
}

function upsertRecord<T extends { id: number }>(
  items: T[],
  nextItem: T,
  compare: (left: T, right: T) => number,
) {
  const nextItems = items.some((record) => record.id === nextItem.id)
    ? items.map((record) => (record.id === nextItem.id ? nextItem : record))
    : [...items, nextItem]

  return [...nextItems].sort(compare)
}

function sortByName<T extends { name: string }>(left: T, right: T) {
  return left.name.localeCompare(right.name)
}

function sortHolidays(left: Holiday, right: Holiday) {
  return left.holiday_date.localeCompare(right.holiday_date)
}

function sortAssignments(left: ShiftAssignment, right: ShiftAssignment) {
  return left.effective_from.localeCompare(right.effective_from) || left.id - right.id
}

function sortRosters(left: ShiftRoster, right: ShiftRoster) {
  return left.work_date.localeCompare(right.work_date) || left.id - right.id
}

function nextId<T extends { id: number }>(items: T[]) {
  return items.reduce((max, record) => Math.max(max, record.id), 0) + 1
}

function normalizeNullableString(value: string | null | undefined) {
  const trimmed = value?.trim()
  return trimmed ? trimmed : null
}
