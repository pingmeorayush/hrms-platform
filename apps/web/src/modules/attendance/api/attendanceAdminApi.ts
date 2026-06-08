import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import { fetchEmployeeDirectory } from '../../employees/api/employeesApi'
import { fetchOrganizationWorkspace } from '../../organization/api/organizationApi'
import type { AttendanceAdminWorkspaceData, AttendancePolicy, AttendancePolicyUpdatePayload, Holiday, HolidayCalendar, HolidayCalendarPayload, HolidayPayload, Shift, ShiftAssignment, ShiftAssignmentPayload, ShiftRoster, ShiftRosterPayload, ShiftRosterUpdatePayload, ShiftPayload } from '../types'

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

export async function fetchAttendanceAdminWorkspace(
  apiBaseUrl: string,
  token: string,
): Promise<AttendanceAdminWorkspaceData> {
  const headers = buildApiHeaders(token)
  const employeeFilters = {
    search: '',
    employmentStatus: '',
    departmentId: '',
    designationId: '',
    managerId: '',
    page: 1,
    perPage: 200,
  } as const

  const [policyResponse, calendarsResponse, shiftsResponse, assignmentsResponse, rostersResponse, organizationData, employeesData] =
    await Promise.all([
      fetch(`${apiBaseUrl}/attendance/policy`, { headers }),
      fetch(`${apiBaseUrl}/attendance/holiday-calendars`, { headers }),
      fetch(`${apiBaseUrl}/attendance/shifts`, { headers }),
      fetch(`${apiBaseUrl}/attendance/shift-assignments`, { headers }),
      fetch(`${apiBaseUrl}/attendance/rosters`, { headers }),
      fetchOrganizationWorkspace(apiBaseUrl, token),
      fetchEmployeeDirectory(apiBaseUrl, token, employeeFilters),
    ])

  const [policy, holidayCalendars, shifts, assignments, rosters] = await Promise.all([
    readApiJson<AttendancePolicy>(policyResponse),
    readApiJson<HolidayCalendar[]>(calendarsResponse),
    readApiJson<Shift[]>(shiftsResponse),
    readApiJson<ShiftAssignment[]>(assignmentsResponse),
    readApiJson<ShiftRoster[]>(rostersResponse),
  ])

  return {
    policy,
    holidayCalendars,
    shifts,
    assignments,
    rosters,
    employees: employeesData.items.map((employee) => ({
      id: employee.id,
      employee_code: employee.employee_code,
      full_name: employee.full_name,
      email: employee.email,
    })),
    departments: organizationData.departments,
    locations: organizationData.locations,
  }
}

export async function updateAttendancePolicy(
  apiBaseUrl: string,
  token: string,
  payload: AttendancePolicyUpdatePayload,
) {
  return requestJson<AttendancePolicy>(`${apiBaseUrl}/attendance/policy`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}

export async function createHolidayCalendar(
  apiBaseUrl: string,
  token: string,
  payload: HolidayCalendarPayload,
) {
  return requestJson<HolidayCalendar>(`${apiBaseUrl}/attendance/holiday-calendars`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export async function updateHolidayCalendar(
  apiBaseUrl: string,
  token: string,
  holidayCalendarId: number,
  payload: HolidayCalendarPayload,
) {
  return requestJson<HolidayCalendar>(`${apiBaseUrl}/attendance/holiday-calendars/${holidayCalendarId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}

export async function createHoliday(
  apiBaseUrl: string,
  token: string,
  holidayCalendarId: number,
  payload: HolidayPayload,
) {
  return requestJson<Holiday>(
    `${apiBaseUrl}/attendance/holiday-calendars/${holidayCalendarId}/holidays`,
    token,
    {
      method: 'POST',
      body: JSON.stringify(payload),
    },
  )
}

export async function updateHoliday(
  apiBaseUrl: string,
  token: string,
  holidayCalendarId: number,
  holidayId: number,
  payload: HolidayPayload,
) {
  return requestJson<Holiday>(
    `${apiBaseUrl}/attendance/holiday-calendars/${holidayCalendarId}/holidays/${holidayId}`,
    token,
    {
      method: 'PATCH',
      body: JSON.stringify(payload),
    },
  )
}

export async function createShift(
  apiBaseUrl: string,
  token: string,
  payload: ShiftPayload,
) {
  return requestJson<Shift>(`${apiBaseUrl}/attendance/shifts`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export async function updateShift(
  apiBaseUrl: string,
  token: string,
  shiftId: number,
  payload: ShiftPayload,
) {
  return requestJson<Shift>(`${apiBaseUrl}/attendance/shifts/${shiftId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}

export async function createShiftAssignment(
  apiBaseUrl: string,
  token: string,
  payload: ShiftAssignmentPayload,
) {
  return requestJson<ShiftAssignment>(`${apiBaseUrl}/attendance/shift-assignments`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export async function updateShiftAssignment(
  apiBaseUrl: string,
  token: string,
  shiftAssignmentId: number,
  payload: ShiftAssignmentPayload,
) {
  return requestJson<ShiftAssignment>(
    `${apiBaseUrl}/attendance/shift-assignments/${shiftAssignmentId}`,
    token,
    {
      method: 'PATCH',
      body: JSON.stringify(payload),
    },
  )
}

export async function createShiftRoster(
  apiBaseUrl: string,
  token: string,
  payload: ShiftRosterPayload,
) {
  const result = await requestJson<ShiftRoster[]>(`${apiBaseUrl}/attendance/rosters`, token, {
    method: 'POST',
    body: JSON.stringify({
      entries: [
        {
          employee_id: payload.employee_id,
          shift_id: payload.shift_id,
          work_date: payload.work_date,
          notes: payload.notes ?? null,
          status: payload.status ?? 'scheduled',
        },
      ],
    }),
  })

  return result[0]
}

export async function updateShiftRoster(
  apiBaseUrl: string,
  token: string,
  shiftRosterId: number,
  payload: ShiftRosterUpdatePayload,
) {
  return requestJson<ShiftRoster>(`${apiBaseUrl}/attendance/rosters/${shiftRosterId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}
