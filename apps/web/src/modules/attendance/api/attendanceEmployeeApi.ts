import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import type {
  AttendanceCheckInPayload,
  AttendanceCheckOutPayload,
  AttendanceCorrection,
  AttendanceCorrectionDecisionPayload,
  AttendanceCorrectionCreatePayload,
  AttendanceCorrectionsFilters,
  AttendanceHistoryFilters,
  AttendanceOperationalReviewData,
  AttendancePendingExceptionsData,
  AttendancePolicy,
  AttendanceRecord,
  PaginatedAttendanceCorrections,
  PaginatedAttendanceRecords,
} from '../types'

async function requestJson<T>(url: string, token: string, init?: RequestInit) {
  const response = await fetch(url, {
    ...init,
    headers: {
      ...buildApiHeaders(token),
      ...(init?.headers ?? {}),
    },
  })

  return readApiJson<T>(response)
}

export async function fetchAttendanceHistory(
  apiBaseUrl: string,
  token: string,
  filters: AttendanceHistoryFilters,
) {
  const searchParams = new URLSearchParams()

  if (filters.dateFrom) {
    searchParams.set('date_from', filters.dateFrom)
  }

  if (filters.dateTo) {
    searchParams.set('date_to', filters.dateTo)
  }

  if (filters.primaryStatus) {
    searchParams.set('primary_status', filters.primaryStatus)
  }

  if (filters.state) {
    searchParams.set('state', filters.state)
  }

  searchParams.set('per_page', String(filters.perPage))

  return requestJson<PaginatedAttendanceRecords>(
    `${apiBaseUrl}/attendance?${searchParams.toString()}`,
    token,
  )
}

export async function fetchTodayAttendance(
  apiBaseUrl: string,
  token: string,
  today: string,
) {
  const history = await fetchAttendanceHistory(apiBaseUrl, token, {
    dateFrom: today,
    dateTo: today,
    primaryStatus: '',
    state: '',
    perPage: 1,
  })

  return history.items[0] ?? null
}

export async function fetchAttendanceCorrections(
  apiBaseUrl: string,
  token: string,
  filters: AttendanceCorrectionsFilters = {},
) {
  const searchParams = new URLSearchParams()

  if (filters.employeeId) {
    searchParams.set('employee_id', String(filters.employeeId))
  }

  if (filters.attendanceRecordId) {
    searchParams.set('attendance_record_id', String(filters.attendanceRecordId))
  }

  if (filters.status) {
    searchParams.set('status', filters.status)
  }

  searchParams.set('per_page', String(filters.perPage ?? 20))

  return requestJson<PaginatedAttendanceCorrections>(
    `${apiBaseUrl}/attendance/corrections?${searchParams.toString()}`,
    token,
  )
}

export async function fetchAttendancePolicy(
  apiBaseUrl: string,
  token: string,
) {
  return requestJson<AttendancePolicy>(`${apiBaseUrl}/attendance/policy`, token)
}

export async function createAttendanceCheckIn(
  apiBaseUrl: string,
  token: string,
  payload: AttendanceCheckInPayload,
) {
  return requestJson<AttendanceRecord>(`${apiBaseUrl}/attendance/check-in`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export async function createAttendanceCheckOut(
  apiBaseUrl: string,
  token: string,
  payload: AttendanceCheckOutPayload,
) {
  return requestJson<AttendanceRecord>(`${apiBaseUrl}/attendance/check-out`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export async function createAttendanceCorrection(
  apiBaseUrl: string,
  token: string,
  payload: AttendanceCorrectionCreatePayload,
) {
  return requestJson<AttendanceCorrection>(`${apiBaseUrl}/attendance/corrections`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export async function fetchAttendanceOperationalReview(
  apiBaseUrl: string,
  token: string,
  date: string,
  employeeId?: number | null,
) {
  const searchParams = new URLSearchParams()
  searchParams.set('date', date)

  if (employeeId) {
    searchParams.set('employee_id', String(employeeId))
  }

  return requestJson<AttendanceOperationalReviewData>(
    `${apiBaseUrl}/attendance/operational-review?${searchParams.toString()}`,
    token,
  )
}

export async function fetchAttendancePendingExceptions(
  apiBaseUrl: string,
  token: string,
  date: string,
  employeeId?: number | null,
) {
  const searchParams = new URLSearchParams()
  searchParams.set('date', date)

  if (employeeId) {
    searchParams.set('employee_id', String(employeeId))
  }

  return requestJson<AttendancePendingExceptionsData>(
    `${apiBaseUrl}/attendance/pending-exceptions?${searchParams.toString()}`,
    token,
  )
}

export async function updateAttendanceCorrectionDecision(
  apiBaseUrl: string,
  token: string,
  correctionId: number,
  payload: AttendanceCorrectionDecisionPayload,
) {
  return requestJson<AttendanceCorrection>(
    `${apiBaseUrl}/attendance/corrections/${correctionId}`,
    token,
    {
      method: 'PATCH',
      body: JSON.stringify(payload),
    },
  )
}
