import type { AccessSnapshot } from '../../access/types'
import { buildDemoEmployees } from '../../employees/data/demoEmployees'
import type { EmployeeRecord, EmployeeReference } from '../../employees/types'
import { buildDemoAttendanceWorkspace } from './demoAttendanceAdmin'
import type {
  AttendanceCalculation,
  AttendanceCaptureMetadata,
  AttendanceCaptureSnapshot,
  AttendanceCorrection,
  AttendanceCorrectionDecisionAction,
  AttendanceCorrectionValueSnapshot,
  AttendanceOperationalRecord,
  AttendancePendingExceptionsData,
  AttendanceReviewWorkspaceData,
  AttendanceUserReference,
  AttendanceWorkflowApprovalTask,
  AttendanceWorkflowStatus,
  Shift,
} from '../types'

export interface AttendanceReviewDemoState {
  scope: 'team' | 'tenant'
  employees: EmployeeRecord[]
  records: AttendanceOperationalRecord[]
  corrections: AttendanceCorrection[]
}

const defaultDeviceMetadata = {
  device: {
    device_id: 'ops-console-01',
    device_name: 'Phoenix Attendance Console',
    platform: 'Desktop',
    browser: 'Chromium',
    app_version: 'wave-6',
  },
  geolocation: {
    latitude: 12.9716,
    longitude: 77.5946,
    accuracy_meters: 18,
  },
} satisfies AttendanceCaptureMetadata

export function buildDemoAttendanceReviewState(
  snapshot: AccessSnapshot | null,
): AttendanceReviewDemoState {
  const employees = buildDemoEmployees(snapshot)
  const attendanceAdmin = buildDemoAttendanceWorkspace(snapshot)
  const shifts = attendanceAdmin.shifts
  const scope = isTenantScope(snapshot) ? 'tenant' : 'team'
  const directReportManagerId = resolveManagerEmployeeId(snapshot)
  const visibleEmployees = employees.filter((employee) =>
    scope === 'tenant' ? true : employee.manager?.id === directReportManagerId,
  )
  const employeeById = new Map(employees.map((employee) => [employee.id, employee]))
  const records = buildOperationalRecords(visibleEmployees, shifts)
  const corrections = buildCorrections(visibleEmployees, employeeById, records, snapshot)

  return {
    scope,
    employees: visibleEmployees,
    records,
    corrections,
  }
}

export function deriveDemoAttendanceReviewWorkspace(
  date: string,
  state: AttendanceReviewDemoState,
): AttendanceReviewWorkspaceData {
  const windowRecords = state.records
    .filter((record) => record.attendance_date === date)
    .map((record) => hydrateOperationalRecord(record, state.corrections))
  const exceptionRecords = windowRecords.filter((record) => record.exception_types.length > 0)
  const correctionItems = state.corrections
    .filter(
      (correction) =>
        correction.status === 'pending' &&
        correction.original_values.attendance_date === date,
    )
    .sort(sortCorrections)

  return {
    scope: state.scope,
    windowDate: date,
    operationalReview: {
      window_date: date,
      summary: summarizeOperationalRecords(windowRecords),
      items: windowRecords,
    },
    pendingExceptions: {
      window_date: date,
      summary: summarizePendingExceptions(exceptionRecords, correctionItems),
      attendance_items: exceptionRecords,
      correction_items: correctionItems,
    },
    corrections: {
      items: [...state.corrections].sort(sortCorrections),
      meta: {
        page: 1,
        per_page: 50,
        total: state.corrections.length,
        last_page: 1,
      },
    },
  }
}

export function applyDemoCorrectionDecision(
  state: AttendanceReviewDemoState,
  correctionId: number,
  action: AttendanceCorrectionDecisionAction,
  actor: AttendanceUserReference | null,
  comment: string | null,
): AttendanceReviewDemoState {
  const decisionTimestamp = '2026-06-03T16:20:00+05:30'
  const nextCorrections = state.corrections.map((correction): AttendanceCorrection => {
    if (correction.id !== correctionId) {
      return correction
    }

    const nextStatus: AttendanceCorrection['status'] =
      action === 'approve'
        ? 'approved'
        : action === 'reject'
          ? 'rejected'
          : 'changes_requested'
    const approvalHistory = [
      ...(correction.workflow?.approval_history ?? []),
      buildWorkflowTask({
        id: correction.id + 7000,
        status: 'completed',
        decision: action,
        decisionComment: comment,
        actedAt: decisionTimestamp,
        actor,
      }),
    ]

    return {
      ...correction,
      status: nextStatus,
      latest_action_by: actor,
      decision_comment: comment,
      approved_at: action === 'approve' ? decisionTimestamp : correction.approved_at,
      rejected_at: action === 'reject' ? decisionTimestamp : correction.rejected_at,
      applied_values:
        action === 'approve'
          ? {
              ...correction.corrected_values,
            }
          : null,
      updated_at: decisionTimestamp,
      workflow: correction.workflow
        ? {
            ...correction.workflow,
            status: mapDecisionToWorkflowStatus(action),
            current_task: null,
            approval_history: approvalHistory,
          }
        : null,
    }
  })

  const nextRecords = state.records.map((record): AttendanceOperationalRecord => {
    const updatedCorrection = nextCorrections.find((correction) => correction.attendance_record_id === record.id)

    if (!updatedCorrection || updatedCorrection.id !== correctionId || updatedCorrection.status !== 'approved') {
      return record
    }

    return applyApprovedCorrection(record, updatedCorrection)
  })

  return {
    ...state,
    records: nextRecords,
    corrections: nextCorrections,
  }
}

function buildOperationalRecords(
  employees: EmployeeRecord[],
  shifts: Shift[],
): AttendanceOperationalRecord[] {
  const generalShift = shifts.find((shift) => shift.code === 'GEN-IND') ?? shifts[0] ?? null
  const opsShift = shifts.find((shift) => shift.code === 'EARLY-OPS') ?? generalShift

  return [
    buildOperationalRecord({
      id: 6102,
      employee: employees.find((employee) => employee.id === 1002) ?? employees[0] ?? null,
      attendanceDate: '2026-06-03',
      shift: generalShift,
      state: 'checked_out',
      workedMinutes: 474,
      checkInAt: '2026-06-03T09:44:00+05:30',
      checkOutAt: '2026-06-03T18:38:00+05:30',
      calculation: buildCalculation({
        date: '2026-06-03',
        shift: generalShift,
        primaryStatus: 'present',
        lateMinutes: 14,
      }),
      exceptionTypes: ['late'],
    }),
    buildOperationalRecord({
      id: 6103,
      employee: employees.find((employee) => employee.id === 1003) ?? employees[0] ?? null,
      attendanceDate: '2026-06-03',
      shift: generalShift,
      state: 'checked_out',
      workedMinutes: 251,
      checkInAt: '2026-06-03T09:32:00+05:30',
      checkOutAt: '2026-06-03T14:43:00+05:30',
      calculation: buildCalculation({
        date: '2026-06-03',
        shift: generalShift,
        primaryStatus: 'half_day',
        isHalfDay: true,
        earlyDepartureMinutes: 167,
      }),
      exceptionTypes: ['half_day'],
    }),
    buildOperationalRecord({
      id: 6104,
      employee: employees.find((employee) => employee.id === 1004) ?? employees[0] ?? null,
      attendanceDate: '2026-06-03',
      shift: generalShift,
      state: 'checked_out',
      workedMinutes: 486,
      checkInAt: '2026-06-03T09:28:00+05:30',
      checkOutAt: '2026-06-03T18:44:00+05:30',
      calculation: buildCalculation({
        date: '2026-06-03',
        shift: generalShift,
        primaryStatus: 'present',
      }),
      exceptionTypes: [],
    }),
    buildOperationalRecord({
      id: 6105,
      employee: employees.find((employee) => employee.id === 1005) ?? employees[0] ?? null,
      attendanceDate: '2026-06-03',
      shift: opsShift,
      state: 'checked_in',
      workedMinutes: null,
      checkInAt: '2026-06-03T08:04:00+05:30',
      checkOutAt: null,
      calculation: buildCalculation({
        date: '2026-06-03',
        shift: opsShift,
        primaryStatus: 'incomplete',
      }),
      exceptionTypes: ['incomplete'],
    }),
    buildOperationalRecord({
      id: 6002,
      employee: employees.find((employee) => employee.id === 1002) ?? employees[0] ?? null,
      attendanceDate: '2026-06-02',
      shift: generalShift,
      state: 'checked_out',
      workedMinutes: 481,
      checkInAt: '2026-06-02T09:31:00+05:30',
      checkOutAt: '2026-06-02T18:42:00+05:30',
      calculation: buildCalculation({
        date: '2026-06-02',
        shift: generalShift,
        primaryStatus: 'present',
      }),
      exceptionTypes: [],
    }),
    buildOperationalRecord({
      id: 6003,
      employee: employees.find((employee) => employee.id === 1003) ?? employees[0] ?? null,
      attendanceDate: '2026-06-02',
      shift: generalShift,
      state: 'not_captured',
      workedMinutes: null,
      checkInAt: null,
      checkOutAt: null,
      calculation: buildCalculation({
        date: '2026-06-02',
        shift: generalShift,
        primaryStatus: 'absent',
      }),
      exceptionTypes: ['absent'],
    }),
  ].filter((record): record is AttendanceOperationalRecord => Boolean(record))
}

function buildCorrections(
  employees: EmployeeRecord[],
  employeeById: Map<number, EmployeeRecord>,
  records: AttendanceOperationalRecord[],
  snapshot: AccessSnapshot | null,
) {
  const requester = buildUserReference(snapshot)
  const meera = employeeById.get(1004)
  const managerActor = {
    id: 3,
    name: 'Manager Reviewer',
    email: 'manager@phoenixhrms.test',
  } satisfies AttendanceUserReference
  const hrActor = meera
    ? {
        id: 1004,
        name: meera.full_name,
        email: meera.email,
      }
    : requester

  const recordById = new Map(records.map((record) => [record.id, record]))
  const pendingRecord = recordById.get(6103)
  const tenantPendingRecord = recordById.get(6105)
  const approvedRecord = recordById.get(6003)
  const rejectedRecord = recordById.get(6002)

  return [
    pendingRecord
      ? createCorrection({
          id: 9301,
          status: 'pending',
          reason: 'Missed the evening project closeout after stepping into a vendor walkthrough.',
          record: pendingRecord,
          requestedBy: requester,
          latestActionBy: null,
          correctedValues: {
            ...buildCorrectionValues(pendingRecord),
            check_out_at: '2026-06-03T18:05:00+05:30',
            worked_minutes: 463,
            primary_status: 'present',
          },
          appliedValues: null,
          decisionComment: null,
          workflow: {
            id: 10401,
            status: 'waiting',
            current_stage_sequence: 1,
            approval_history: [],
            current_task: buildWorkflowTask({
              id: 11401,
              status: 'open',
              decision: null,
              decisionComment: null,
              actedAt: null,
              actor: null,
            }),
          },
          approvedAt: null,
          rejectedAt: null,
          createdAt: '2026-06-03T15:20:00+05:30',
          updatedAt: '2026-06-03T15:20:00+05:30',
        })
      : null,
    tenantPendingRecord && employees.some((employee) => employee.id === 1005)
      ? createCorrection({
          id: 9302,
          status: 'pending',
          reason: 'Checkout failed during the early-ops handover and needs a corrected sign-out.',
          record: tenantPendingRecord,
          requestedBy: requester,
          latestActionBy: null,
          correctedValues: {
            ...buildCorrectionValues(tenantPendingRecord),
            check_out_at: '2026-06-03T16:08:00+05:30',
            worked_minutes: 439,
            primary_status: 'present',
          },
          appliedValues: null,
          decisionComment: null,
          workflow: {
            id: 10402,
            status: 'waiting',
            current_stage_sequence: 1,
            approval_history: [],
            current_task: buildWorkflowTask({
              id: 11402,
              status: 'open',
              decision: null,
              decisionComment: null,
              actedAt: null,
              actor: null,
            }),
          },
          approvedAt: null,
          rejectedAt: null,
          createdAt: '2026-06-03T15:36:00+05:30',
          updatedAt: '2026-06-03T15:36:00+05:30',
        })
      : null,
    approvedRecord
      ? createCorrection({
          id: 9300,
          status: 'approved',
          reason: 'Kiosk outage prevented the mid-day catch-up from being captured in real time.',
          record: approvedRecord,
          requestedBy: requester,
          latestActionBy: hrActor,
          correctedValues: {
            ...buildCorrectionValues(approvedRecord),
            check_in_at: '2026-06-02T13:04:00+05:30',
            check_out_at: '2026-06-02T18:06:00+05:30',
            worked_minutes: 242,
            primary_status: 'half_day',
          },
          appliedValues: {
            ...buildCorrectionValues(approvedRecord),
            check_in_at: '2026-06-02T13:04:00+05:30',
            check_out_at: '2026-06-02T18:06:00+05:30',
            worked_minutes: 242,
            primary_status: 'half_day',
          },
          decisionComment: 'Approved after validating the kiosk incident and recalculating the day.',
          workflow: {
            id: 10400,
            status: 'completed',
            current_stage_sequence: 2,
            approval_history: [
              buildWorkflowTask({
                id: 11400,
                status: 'completed',
                decision: 'approve',
                decisionComment: 'Approved after validating the kiosk incident and recalculating the day.',
                actedAt: '2026-06-02T19:10:00+05:30',
                actor: managerActor,
              }),
            ],
            current_task: null,
          },
          approvedAt: '2026-06-02T19:10:00+05:30',
          rejectedAt: null,
          createdAt: '2026-06-02T17:42:00+05:30',
          updatedAt: '2026-06-02T19:10:00+05:30',
        })
      : null,
    rejectedRecord
      ? createCorrection({
          id: 9299,
          status: 'rejected',
          reason: 'Requested to backdate an arrival that was not supported by any supporting note.',
          record: rejectedRecord,
          requestedBy: requester,
          latestActionBy: managerActor,
          correctedValues: {
            ...buildCorrectionValues(rejectedRecord),
            check_in_at: '2026-06-02T08:45:00+05:30',
          },
          appliedValues: null,
          decisionComment: 'Rejected because the proposed arrival time could not be validated.',
          workflow: {
            id: 10399,
            status: 'rejected',
            current_stage_sequence: 1,
            approval_history: [
              buildWorkflowTask({
                id: 11399,
                status: 'completed',
                decision: 'reject',
                decisionComment: 'Rejected because the proposed arrival time could not be validated.',
                actedAt: '2026-06-02T12:08:00+05:30',
                actor: managerActor,
              }),
            ],
            current_task: null,
          },
          approvedAt: null,
          rejectedAt: '2026-06-02T12:08:00+05:30',
          createdAt: '2026-06-02T10:24:00+05:30',
          updatedAt: '2026-06-02T12:08:00+05:30',
        })
      : null,
    pendingRecord
      ? createCorrection({
          id: 9298,
          status: 'changes_requested',
          reason: 'Requested a corrected checkout without adding the supporting customer handoff note.',
          record: pendingRecord,
          requestedBy: requester,
          latestActionBy: managerActor,
          correctedValues: {
            ...buildCorrectionValues(pendingRecord),
            check_out_at: '2026-05-30T18:00:00+05:30',
          },
          appliedValues: null,
          decisionComment: 'Please add the customer handoff context before this can be approved.',
          workflow: {
            id: 10398,
            status: 'waiting',
            current_stage_sequence: 1,
            approval_history: [
              buildWorkflowTask({
                id: 11398,
                status: 'completed',
                decision: 'request_changes',
                decisionComment: 'Please add the customer handoff context before this can be approved.',
                actedAt: '2026-05-30T18:40:00+05:30',
                actor: managerActor,
              }),
            ],
            current_task: null,
          },
          approvedAt: null,
          rejectedAt: null,
          createdAt: '2026-05-30T17:20:00+05:30',
          updatedAt: '2026-05-30T18:40:00+05:30',
        })
      : null,
  ].filter((correction): correction is AttendanceCorrection => Boolean(correction))
}

function buildOperationalRecord({
  id,
  employee,
  attendanceDate,
  shift,
  state,
  workedMinutes,
  checkInAt,
  checkOutAt,
  calculation,
  exceptionTypes,
}: {
  id: number
  employee: EmployeeRecord | null
  attendanceDate: string
  shift: Shift | null
  state: AttendanceOperationalRecord['state']
  workedMinutes: number | null
  checkInAt: string | null
  checkOutAt: string | null
  calculation: AttendanceCalculation
  exceptionTypes: AttendanceOperationalRecord['exception_types']
}): AttendanceOperationalRecord | null {
  if (!employee) {
    return null
  }

  const employeeReference = toEmployeeReference(employee)

  return {
    id,
    attendance_date: attendanceDate,
    employee: employeeReference,
    shift,
    shift_roster_id: null,
    state,
    worked_minutes: workedMinutes,
    calculation,
    check_in: buildCaptureSnapshot(checkInAt),
    check_out: buildCaptureSnapshot(checkOutAt),
    created_at: `${attendanceDate}T09:00:00+05:30`,
    updated_at: checkOutAt ?? checkInAt ?? `${attendanceDate}T09:00:00+05:30`,
    exception_types: exceptionTypes,
    has_pending_correction: false,
    pending_corrections: [],
  }
}

function hydrateOperationalRecord(
  record: AttendanceOperationalRecord,
  corrections: AttendanceCorrection[],
) {
  const pendingCorrections = corrections
    .filter(
      (correction) =>
        correction.attendance_record_id === record.id && correction.status === 'pending',
    )
    .map((correction) => ({
      id: correction.id,
      status: correction.status,
      reason: correction.reason,
      attendance_record_id: correction.attendance_record_id,
      requested_by: correction.requested_by,
      created_at: correction.created_at,
      updated_at: correction.updated_at,
    }))
  const exceptionTypes = [...record.exception_types]

  if (pendingCorrections.length) {
    exceptionTypes.push('pending_correction')
  }

  return {
    ...record,
    exception_types: Array.from(new Set(exceptionTypes)),
    has_pending_correction: pendingCorrections.length > 0,
    pending_corrections: pendingCorrections,
  }
}

function summarizeOperationalRecords(records: AttendanceOperationalRecord[]) {
  return {
    total_records: records.length,
    present_count: records.filter((record) => record.calculation.primary_status === 'present').length,
    absent_count: records.filter((record) => record.calculation.primary_status === 'absent').length,
    half_day_count: records.filter((record) => record.calculation.primary_status === 'half_day').length,
    incomplete_count: records.filter((record) => record.calculation.primary_status === 'incomplete').length,
    holiday_count: records.filter((record) => record.calculation.primary_status === 'holiday').length,
    weekend_count: records.filter((record) => record.calculation.primary_status === 'weekend').length,
    late_count: records.filter((record) => record.calculation.is_late).length,
    pending_correction_count: records.filter((record) => record.has_pending_correction).length,
    checked_in_count: records.filter((record) => record.state === 'checked_in').length,
    checked_out_count: records.filter((record) => record.state === 'checked_out').length,
  }
}

function summarizePendingExceptions(
  records: AttendanceOperationalRecord[],
  corrections: AttendanceCorrection[],
): AttendancePendingExceptionsData['summary'] {
  return {
    exception_record_count: records.length,
    late_record_count: records.filter((record) => record.exception_types.includes('late')).length,
    absent_record_count: records.filter((record) => record.exception_types.includes('absent')).length,
    half_day_record_count: records.filter((record) => record.exception_types.includes('half_day')).length,
    incomplete_record_count: records.filter((record) => record.exception_types.includes('incomplete')).length,
    pending_correction_record_count: records.filter((record) => record.has_pending_correction).length,
    pending_correction_request_count: corrections.length,
  }
}

function applyApprovedCorrection(
  record: AttendanceOperationalRecord,
  correction: AttendanceCorrection,
): AttendanceOperationalRecord {
  const nextCheckInAt = correction.applied_values?.check_in_at ?? record.check_in.at
  const nextCheckOutAt = correction.applied_values?.check_out_at ?? record.check_out.at
  const nextStatus = correction.applied_values?.primary_status ?? record.calculation.primary_status
  const nextWorkedMinutes = correction.applied_values?.worked_minutes ?? record.worked_minutes
  const nextExceptionTypes = record.exception_types.filter((type) => type !== 'pending_correction')

  if (nextStatus === 'half_day' && !nextExceptionTypes.includes('half_day')) {
    nextExceptionTypes.push('half_day')
  }

  if (nextStatus === 'present') {
    return {
      ...record,
      state: nextCheckOutAt ? 'checked_out' : 'checked_in',
      worked_minutes: nextWorkedMinutes,
      check_in: nextCheckInAt ? buildCaptureSnapshot(nextCheckInAt) : record.check_in,
      check_out: nextCheckOutAt ? buildCaptureSnapshot(nextCheckOutAt) : record.check_out,
      calculation: {
        ...record.calculation,
        primary_status: nextStatus,
        is_half_day: false,
        early_departure_minutes: 0,
        is_early_departure: false,
      },
      exception_types: nextExceptionTypes.filter((type) => type !== 'half_day' && type !== 'incomplete'),
      has_pending_correction: false,
      pending_corrections: [],
      updated_at: correction.updated_at,
    }
  }

  return {
    ...record,
    worked_minutes: nextWorkedMinutes,
    check_in: nextCheckInAt ? buildCaptureSnapshot(nextCheckInAt) : record.check_in,
    check_out: nextCheckOutAt ? buildCaptureSnapshot(nextCheckOutAt) : record.check_out,
    calculation: {
      ...record.calculation,
      primary_status: nextStatus,
      is_half_day: nextStatus === 'half_day',
    },
    exception_types: Array.from(new Set(nextExceptionTypes)),
    has_pending_correction: false,
    pending_corrections: [],
    updated_at: correction.updated_at,
  }
}

function createCorrection({
  id,
  status,
  reason,
  record,
  requestedBy,
  latestActionBy,
  correctedValues,
  appliedValues,
  decisionComment,
  workflow,
  approvedAt,
  rejectedAt,
  createdAt,
  updatedAt,
}: {
  id: number
  status: AttendanceCorrection['status']
  reason: string
  record: AttendanceOperationalRecord
  requestedBy: AttendanceUserReference | null
  latestActionBy: AttendanceUserReference | null
  correctedValues: AttendanceCorrectionValueSnapshot
  appliedValues: AttendanceCorrectionValueSnapshot | null
  decisionComment: string | null
  workflow: AttendanceCorrection['workflow']
  approvedAt: string | null
  rejectedAt: string | null
  createdAt: string
  updatedAt: string
}): AttendanceCorrection {
  return {
    id,
    status,
    reason,
    attendance_record_id: record.id,
    employee: record.employee,
    requested_by: requestedBy,
    latest_action_by: latestActionBy,
    original_values: buildCorrectionValues(record),
    corrected_values: correctedValues,
    applied_values: appliedValues,
    decision_comment: decisionComment,
    workflow,
    approved_at: approvedAt,
    rejected_at: rejectedAt,
    created_at: createdAt,
    updated_at: updatedAt,
  }
}

function buildCorrectionValues(record: AttendanceOperationalRecord): AttendanceCorrectionValueSnapshot {
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

function buildCalculation({
  date,
  shift,
  primaryStatus,
  lateMinutes = 0,
  isHalfDay = false,
  earlyDepartureMinutes = 0,
}: {
  date: string
  shift: Shift | null
  primaryStatus: AttendanceCalculation['primary_status']
  lateMinutes?: number
  isHalfDay?: boolean
  earlyDepartureMinutes?: number
}): AttendanceCalculation {
  const window = buildScheduledWindow(date, shift)

  return {
    primary_status: primaryStatus,
    scheduled_start_at: window.start,
    scheduled_end_at: window.end,
    scheduled_work_minutes: shift?.working_hours_minutes ?? null,
    break_duration_minutes: shift?.break_duration_minutes ?? 0,
    is_late: lateMinutes > 0,
    late_minutes: lateMinutes,
    is_half_day: isHalfDay,
    overtime_minutes: 0,
    is_weekend: false,
    is_holiday: false,
    holiday_name: null,
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

  return {
    start: `${date}T${shift.start_time}:00+05:30`,
    end: `${date}T${shift.end_time}:00+05:30`,
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
    user_agent:
      'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_5) AppleWebKit/537.36 Chrome/126.0.0.0 Safari/537.36',
    metadata: defaultDeviceMetadata,
  }
}

function buildWorkflowTask({
  id,
  status,
  decision,
  decisionComment,
  actedAt,
  actor,
}: {
  id: number
  status: AttendanceWorkflowApprovalTask['status']
  decision: string | null
  decisionComment: string | null
  actedAt: string | null
  actor: AttendanceUserReference | null
}): AttendanceWorkflowApprovalTask {
  return {
    id,
    stage_key: 'manager_review',
    stage_name: 'Manager review',
    sequence: 1,
    status,
    available_actions: ['approve', 'reject', 'request_changes'],
    decision,
    decision_comment: decisionComment,
    due_at: '2026-06-03T18:30:00+05:30',
    acted_at: actedAt,
    assigned_to_role: 'manager',
    assignee: actor,
    actor,
  }
}

function mapDecisionToWorkflowStatus(
  action: AttendanceCorrectionDecisionAction,
): AttendanceWorkflowStatus {
  switch (action) {
    case 'approve':
      return 'completed'
    case 'reject':
      return 'rejected'
    case 'request_changes':
    default:
      return 'waiting'
  }
}

function isTenantScope(snapshot: AccessSnapshot | null) {
  const permissions = snapshot?.user.permissions ?? []

  return (
    permissions.includes('attendance.edit') ||
    permissions.includes('attendance.manage_shift') ||
    permissions.includes('attendance.manage_roster')
  )
}

function resolveManagerEmployeeId(snapshot: AccessSnapshot | null) {
  const mappedEmployeeIdByUserId: Record<number, number> = {
    3: 1001,
  }

  return snapshot ? mappedEmployeeIdByUserId[snapshot.user.id] ?? null : null
}

function buildUserReference(snapshot: AccessSnapshot | null): AttendanceUserReference | null {
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

function sortCorrections(left: AttendanceCorrection, right: AttendanceCorrection) {
  const leftAt = left.updated_at ?? left.created_at ?? ''
  const rightAt = right.updated_at ?? right.created_at ?? ''

  return rightAt.localeCompare(leftAt)
}
