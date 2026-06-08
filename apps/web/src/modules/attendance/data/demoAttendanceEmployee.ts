import type { AccessSnapshot } from '../../access/types'
import { buildDemoEmployees } from '../../employees/data/demoEmployees'
import type { EmployeeRecord, EmployeeReference } from '../../employees/types'
import { buildDemoAttendanceWorkspace } from './demoAttendanceAdmin'
import type {
  AttendanceCalculation,
  AttendanceCaptureMetadata,
  AttendanceCaptureSnapshot,
  AttendanceCorrection,
  AttendanceCorrectionValueSnapshot,
  AttendanceEmployeeWorkspaceData,
  AttendanceHistoryFilters,
  AttendanceRecord,
  AttendanceUserReference,
  PaginatedAttendanceCorrections,
  PaginatedAttendanceRecords,
  Shift,
} from '../types'

const demoToday = '2026-06-03'

const defaultDeviceMetadata = {
  device: {
    device_id: 'web-console-01',
    device_name: 'Phoenix Web Console',
    platform: 'Desktop',
    browser: 'Chromium',
    app_version: 'wave-4',
  },
  geolocation: {
    latitude: 12.9716,
    longitude: 77.5946,
    accuracy_meters: 24,
  },
} satisfies AttendanceCaptureMetadata

export function buildDemoAttendanceEmployeeWorkspace(
  snapshot: AccessSnapshot | null,
): AttendanceEmployeeWorkspaceData {
  const attendanceAdmin = buildDemoAttendanceWorkspace(snapshot)
  const employees = buildDemoEmployees(snapshot)
  const currentEmployee = resolveCurrentEmployee(snapshot, employees)

  if (!currentEmployee) {
    return {
      currentEmployee: null,
      policy: attendanceAdmin.policy,
      todayRecord: null,
      history: emptyAttendanceRecords(),
      corrections: emptyCorrections(),
    }
  }

  const historyItems = buildHistory(currentEmployee, attendanceAdmin.shifts)
  const correctionItems = buildCorrections(historyItems, snapshot)

  return {
    currentEmployee: toEmployeeReference(currentEmployee),
    policy: attendanceAdmin.policy,
    todayRecord: historyItems.find((record) => record.attendance_date === demoToday) ?? null,
    history: {
      items: historyItems,
      meta: buildPaginationMeta(historyItems.length),
    },
    corrections: {
      items: correctionItems,
      meta: buildPaginationMeta(correctionItems.length),
    },
  }
}

export function filterDemoAttendanceHistory(
  history: PaginatedAttendanceRecords,
  filters: AttendanceHistoryFilters,
) {
  const filteredItems = history.items.filter((record) => {
    if (filters.dateFrom && record.attendance_date < filters.dateFrom) {
      return false
    }

    if (filters.dateTo && record.attendance_date > filters.dateTo) {
      return false
    }

    if (filters.primaryStatus && record.calculation.primary_status !== filters.primaryStatus) {
      return false
    }

    if (filters.state && record.state !== filters.state) {
      return false
    }

    return true
  })

  return {
    items: filteredItems.slice(0, filters.perPage),
    meta: buildPaginationMeta(filteredItems.length, filters.perPage),
  } satisfies PaginatedAttendanceRecords
}

function buildHistory(
  employee: EmployeeRecord,
  shifts: Shift[],
) {
  const defaultShift = shifts.find((record) => record.code === 'GEN-IND') ?? shifts[0] ?? null
  const employeeRef = toEmployeeReference(employee)

  const items: AttendanceRecord[] = [
    buildAttendanceRecord({
      id: 3105,
      attendanceDate: demoToday,
      employee: employeeRef,
      shift: defaultShift,
      state: 'not_captured',
      workedMinutes: null,
      calculation: buildCalculation({
        date: demoToday,
        shift: defaultShift,
        primaryStatus: null,
      }),
      checkInAt: null,
      checkOutAt: null,
    }),
    buildAttendanceRecord({
      id: 3104,
      attendanceDate: '2026-06-02',
      employee: employeeRef,
      shift: defaultShift,
      state: 'checked_out',
      workedMinutes: 486,
      calculation: buildCalculation({
        date: '2026-06-02',
        shift: defaultShift,
        primaryStatus: 'present',
        lateMinutes: 6,
      }),
      checkInAt: '2026-06-02T09:36:00+05:30',
      checkOutAt: '2026-06-02T18:42:00+05:30',
    }),
    buildAttendanceRecord({
      id: 3103,
      attendanceDate: '2026-06-01',
      employee: employeeRef,
      shift: defaultShift,
      state: 'checked_out',
      workedMinutes: 248,
      calculation: buildCalculation({
        date: '2026-06-01',
        shift: defaultShift,
        primaryStatus: 'half_day',
        isHalfDay: true,
        earlyDepartureMinutes: 172,
      }),
      checkInAt: '2026-06-01T09:28:00+05:30',
      checkOutAt: '2026-06-01T14:36:00+05:30',
    }),
    buildAttendanceRecord({
      id: 3102,
      attendanceDate: '2026-05-31',
      employee: employeeRef,
      shift: null,
      state: 'not_captured',
      workedMinutes: null,
      calculation: buildCalculation({
        date: '2026-05-31',
        shift: null,
        primaryStatus: 'weekend',
        isWeekend: true,
      }),
      checkInAt: null,
      checkOutAt: null,
    }),
    buildAttendanceRecord({
      id: 3101,
      attendanceDate: '2026-05-30',
      employee: employeeRef,
      shift: defaultShift,
      state: 'checked_out',
      workedMinutes: 562,
      calculation: buildCalculation({
        date: '2026-05-30',
        shift: defaultShift,
        primaryStatus: 'present',
        overtimeMinutes: 82,
      }),
      checkInAt: '2026-05-30T09:24:00+05:30',
      checkOutAt: '2026-05-30T19:46:00+05:30',
    }),
    buildAttendanceRecord({
      id: 3100,
      attendanceDate: '2026-05-29',
      employee: employeeRef,
      shift: defaultShift,
      state: 'checked_in',
      workedMinutes: null,
      calculation: buildCalculation({
        date: '2026-05-29',
        shift: defaultShift,
        primaryStatus: 'incomplete',
      }),
      checkInAt: '2026-05-29T09:32:00+05:30',
      checkOutAt: null,
    }),
  ]

  return items
}

function buildCorrections(
  historyItems: AttendanceRecord[],
  snapshot: AccessSnapshot | null,
) {
  const requester = buildRequester(snapshot)
  const pendingRecord = historyItems.find((record) => record.id === 3100)
  const approvedRecord = historyItems.find((record) => record.id === 3103)

  const items: AttendanceCorrection[] = []

  if (pendingRecord) {
    items.push({
      id: 8201,
      status: 'pending',
      reason: 'Missed check-out while handling vendor onboarding handoff.',
      attendance_record_id: pendingRecord.id,
      employee: pendingRecord.employee,
      requested_by: requester,
      latest_action_by: null,
      original_values: buildCorrectionValues(pendingRecord),
      corrected_values: {
        ...buildCorrectionValues(pendingRecord),
        check_out_at: '2026-05-29T18:38:00+05:30',
        primary_status: 'present',
      },
      applied_values: null,
      decision_comment: null,
      workflow: {
        id: 9401,
        status: 'waiting',
        current_stage_sequence: 1,
        approval_history: [
          {
            id: 11801,
            stage_key: 'manager_review',
            stage_name: 'Manager review',
            sequence: 1,
            status: 'open',
            available_actions: ['approve', 'reject', 'request_changes'],
            decision: null,
            decision_comment: null,
            due_at: '2026-06-04T18:30:00+05:30',
            acted_at: null,
            assigned_to_role: 'manager',
            assignee: {
              id: 3,
              name: 'Manager Reviewer',
              email: 'manager@phoenixhrms.test',
            },
            actor: null,
          },
        ],
        current_task: {
          id: 11801,
          stage_key: 'manager_review',
          stage_name: 'Manager review',
          sequence: 1,
          status: 'open',
          available_actions: ['approve', 'reject', 'request_changes'],
          decision: null,
          decision_comment: null,
          due_at: '2026-06-04T18:30:00+05:30',
          acted_at: null,
          assigned_to_role: 'manager',
          assignee: {
            id: 3,
            name: 'Manager Reviewer',
            email: 'manager@phoenixhrms.test',
          },
          actor: null,
        },
      },
      approved_at: null,
      rejected_at: null,
      created_at: '2026-06-02T19:10:00+05:30',
      updated_at: '2026-06-02T19:10:00+05:30',
    })
  }

  if (approvedRecord) {
    items.push({
      id: 8200,
      status: 'approved',
      reason: 'Corrected the afternoon sign-out that was captured from a kiosk fallback.',
      attendance_record_id: approvedRecord.id,
      employee: approvedRecord.employee,
      requested_by: requester,
      latest_action_by: {
        id: 4,
        name: 'Meera Sethi',
        email: 'meera.sethi@phoenixhrms.test',
      },
      original_values: buildCorrectionValues(approvedRecord),
      corrected_values: {
        ...buildCorrectionValues(approvedRecord),
        check_out_at: '2026-06-01T15:12:00+05:30',
        worked_minutes: 284,
        primary_status: 'half_day',
      },
      applied_values: {
        ...buildCorrectionValues(approvedRecord),
        check_out_at: '2026-06-01T15:12:00+05:30',
        worked_minutes: 284,
        primary_status: 'half_day',
      },
      decision_comment: 'Approved after validating the kiosk outage note.',
      workflow: {
        id: 9400,
        status: 'completed',
        current_stage_sequence: 1,
        approval_history: [
          {
            id: 11800,
            stage_key: 'manager_review',
            stage_name: 'Manager review',
            sequence: 1,
            status: 'completed',
            available_actions: ['approve', 'reject', 'request_changes'],
            decision: 'approve',
            decision_comment: 'Approved after validating the kiosk outage note.',
            due_at: '2026-06-02T12:00:00+05:30',
            acted_at: '2026-06-02T10:42:00+05:30',
            assigned_to_role: 'manager',
            assignee: {
              id: 4,
              name: 'Meera Sethi',
              email: 'meera.sethi@phoenixhrms.test',
            },
            actor: {
              id: 4,
              name: 'Meera Sethi',
              email: 'meera.sethi@phoenixhrms.test',
            },
          },
        ],
        current_task: null,
      },
      approved_at: '2026-06-02T10:42:00+05:30',
      rejected_at: null,
      created_at: '2026-06-01T18:16:00+05:30',
      updated_at: '2026-06-02T10:42:00+05:30',
    })
  }

  return items
}

function buildAttendanceRecord({
  id,
  attendanceDate,
  employee,
  shift,
  state,
  workedMinutes,
  calculation,
  checkInAt,
  checkOutAt,
  shiftRosterId = null,
}: {
  id: number
  attendanceDate: string
  employee: EmployeeReference
  shift: Shift | null
  state: AttendanceRecord['state']
  workedMinutes: number | null
  calculation: AttendanceCalculation
  checkInAt: string | null
  checkOutAt: string | null
  shiftRosterId?: number | null
}): AttendanceRecord {
  return {
    id,
    attendance_date: attendanceDate,
    employee,
    shift,
    shift_roster_id: shiftRosterId,
    state,
    worked_minutes: workedMinutes,
    calculation,
    check_in: buildCaptureSnapshot(checkInAt),
    check_out: buildCaptureSnapshot(checkOutAt),
    created_at: checkInAt ?? `${attendanceDate}T09:00:00+05:30`,
    updated_at: checkOutAt ?? checkInAt ?? `${attendanceDate}T09:00:00+05:30`,
  }
}

function buildCalculation({
  date,
  shift,
  primaryStatus,
  lateMinutes = 0,
  isHalfDay = false,
  overtimeMinutes = 0,
  isWeekend = false,
  holidayName = null,
  earlyDepartureMinutes = 0,
}: {
  date: string
  shift: Shift | null
  primaryStatus: AttendanceCalculation['primary_status']
  lateMinutes?: number
  isHalfDay?: boolean
  overtimeMinutes?: number
  isWeekend?: boolean
  holidayName?: string | null
  earlyDepartureMinutes?: number
}): AttendanceCalculation {
  const scheduledWindow = buildScheduledWindow(date, shift)

  return {
    primary_status: primaryStatus,
    scheduled_start_at: scheduledWindow.start,
    scheduled_end_at: scheduledWindow.end,
    scheduled_work_minutes: shift?.working_hours_minutes ?? null,
    break_duration_minutes: shift?.break_duration_minutes ?? 0,
    is_late: lateMinutes > 0,
    late_minutes: lateMinutes,
    is_half_day: isHalfDay,
    overtime_minutes: overtimeMinutes,
    is_weekend: isWeekend,
    is_holiday: Boolean(holidayName),
    holiday_name: holidayName,
    is_early_departure: earlyDepartureMinutes > 0,
    early_departure_minutes: earlyDepartureMinutes,
    calculated_at: `${date}T19:10:00+05:30`,
    metadata: {},
  }
}

function buildScheduledWindow(date: string, shift: Shift | null) {
  if (!shift) {
    return {
      start: null,
      end: null,
    }
  }

  const start = `${date}T${shift.start_time}:00+05:30`
  const endDate = shift.is_overnight ? addDays(date, 1) : date

  return {
    start,
    end: `${endDate}T${shift.end_time}:00+05:30`,
  }
}

function buildCaptureSnapshot(at: string | null): AttendanceCaptureSnapshot {
  if (!at) {
    return {
      at: null,
      channel: null,
      ip_address: null,
      user_agent: null,
      metadata: {
        device: null,
        geolocation: null,
      },
    }
  }

  return {
    at,
    channel: 'web',
    ip_address: '203.110.92.14',
    user_agent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_5) AppleWebKit/537.36 Chrome/126.0.0.0 Safari/537.36',
    metadata: defaultDeviceMetadata,
  }
}

function buildCorrectionValues(record: AttendanceRecord): AttendanceCorrectionValueSnapshot {
  return {
    attendance_date: record.attendance_date,
    check_in_at: record.check_in.at,
    check_out_at: record.check_out.at,
    check_in_channel: record.check_in.channel,
    check_out_channel: record.check_out.channel,
    worked_minutes: record.worked_minutes,
    primary_status: record.calculation.primary_status,
    shift_id: record.shift?.id ?? null,
    shift_roster_id: record.shift_roster_id,
  }
}

function resolveCurrentEmployee(snapshot: AccessSnapshot | null, employees: EmployeeRecord[]) {
  const matchedByEmail = employees.find(
    (employee) => employee.email.toLowerCase() === snapshot?.user.email.toLowerCase(),
  )

  if (matchedByEmail) {
    return matchedByEmail
  }

  const mappedEmployeeIdByUserId: Record<number, number> = {
    1: 1004,
    2: 1004,
    3: 1001,
    4: 1005,
  }

  const mappedEmployeeId = snapshot ? mappedEmployeeIdByUserId[snapshot.user.id] : undefined

  if (mappedEmployeeId) {
    return employees.find((employee) => employee.id === mappedEmployeeId) ?? null
  }

  return employees[0] ?? null
}

function buildRequester(snapshot: AccessSnapshot | null): AttendanceUserReference | null {
  if (!snapshot) {
    return null
  }

  return {
    id: snapshot.user.id,
    name: snapshot.user.name,
    email: snapshot.user.email,
  }
}

function toEmployeeReference(employee: EmployeeRecord): EmployeeReference {
  return {
    id: employee.id,
    employee_code: employee.employee_code,
    full_name: employee.full_name,
    email: employee.email,
  }
}

function emptyAttendanceRecords(): PaginatedAttendanceRecords {
  return {
    items: [],
    meta: buildPaginationMeta(0),
  }
}

function emptyCorrections(): PaginatedAttendanceCorrections {
  return {
    items: [],
    meta: buildPaginationMeta(0),
  }
}

function buildPaginationMeta(total: number, perPage = 14) {
  const safePerPage = Math.max(perPage, 1)

  return {
    page: 1,
    per_page: safePerPage,
    total,
    last_page: Math.max(1, Math.ceil(total / safePerPage)),
  }
}

function addDays(date: string, days: number) {
  const [year, month, day] = date.split('-').map(Number)
  const nextDate = new Date(Date.UTC(year, month - 1, day + days))

  return `${nextDate.getUTCFullYear()}-${String(nextDate.getUTCMonth() + 1).padStart(2, '0')}-${String(
    nextDate.getUTCDate(),
  ).padStart(2, '0')}`
}
