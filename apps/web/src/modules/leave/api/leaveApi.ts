import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import { fetchOrganizationWorkspace } from '../../organization/api/organizationApi'
import type { EmployeeReference } from '../../employees/types'
import type { LocationRecord, OrganizationMasterRecord } from '../../organization/types'
import type {
  LeaveAdminWorkspaceData,
  LeavePolicyFormValues,
  LeavePolicyRecord,
  LeaveRequestFormValues,
  LeaveRequestRecord,
  LeaveReviewDecisionAction,
  LeaveTypeFormValues,
  LeaveTypeRecord,
  LeaveBalanceRecord,
  LeaveCalendarEntry,
} from '../types'

interface PaginatedLeaveRequests {
  items: LeaveRequestRecord[]
  meta: {
    page: number
    per_page: number
    total: number
    last_page: number
  }
}

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

export async function fetchLeaveWorkspace(
  apiBaseUrl: string,
  token: string,
  options: {
    includeOrganizationContext?: boolean
  } = {},
): Promise<LeaveAdminWorkspaceData> {
  const organizationContextPromise = options.includeOrganizationContext
    ? fetchOrganizationWorkspace(apiBaseUrl, token)
        .then((data) => ({
          departments: data.departments,
          locations: data.locations,
        }))
        .catch(() => ({
          departments: [] as OrganizationMasterRecord[],
          locations: [] as LocationRecord[],
        }))
    : Promise.resolve({
        departments: [] as OrganizationMasterRecord[],
        locations: [] as LocationRecord[],
      })

  const [leaveTypes, policies, balances, requests, organizationContext] = await Promise.all([
    requestJson<LeaveTypeRecord[]>(`${apiBaseUrl}/leave/types`, token),
    requestJson<LeavePolicyRecord[]>(`${apiBaseUrl}/leave/policies`, token),
    requestJson<LeaveBalanceRecord[]>(`${apiBaseUrl}/leave/balances`, token),
    fetchAllLeaveRequests(apiBaseUrl, token),
    organizationContextPromise,
  ])

  const employees = dedupeEmployees([
    ...balances.map((record) => record.employee),
    ...requests.map((record) => record.employee),
  ])

  return {
    leaveTypes,
    policies,
    balances,
    requests,
    calendarEntries: requests.map(toCalendarEntry),
    departments: organizationContext.departments,
    locations: organizationContext.locations,
    employees,
  }
}

export async function createLeaveType(
  apiBaseUrl: string,
  token: string,
  values: LeaveTypeFormValues,
) {
  return requestJson<LeaveTypeRecord>(`${apiBaseUrl}/leave/types`, token, {
    method: 'POST',
    body: JSON.stringify({
      code: values.code.trim().toUpperCase(),
      name: values.name.trim(),
      category: values.category,
      description: values.description.trim() || null,
      is_paid: values.is_paid,
      requires_approval: values.requires_approval,
      allows_half_day: values.allows_half_day,
      color_token: values.color_token.trim(),
      status: values.status,
    }),
  })
}

export async function updateLeaveType(
  apiBaseUrl: string,
  token: string,
  leaveTypeId: number,
  values: LeaveTypeFormValues,
) {
  return requestJson<LeaveTypeRecord>(`${apiBaseUrl}/leave/types/${leaveTypeId}`, token, {
    method: 'PATCH',
    body: JSON.stringify({
      code: values.code.trim().toUpperCase(),
      name: values.name.trim(),
      category: values.category,
      description: values.description.trim() || null,
      is_paid: values.is_paid,
      requires_approval: values.requires_approval,
      allows_half_day: values.allows_half_day,
      color_token: values.color_token.trim(),
      status: values.status,
    }),
  })
}

export async function createLeavePolicy(
  apiBaseUrl: string,
  token: string,
  values: LeavePolicyFormValues,
) {
  return requestJson<LeavePolicyRecord>(`${apiBaseUrl}/leave/policies`, token, {
    method: 'POST',
    body: JSON.stringify(normalizeLeavePolicyValues(values)),
  })
}

export async function updateLeavePolicy(
  apiBaseUrl: string,
  token: string,
  leavePolicyId: number,
  values: LeavePolicyFormValues,
) {
  return requestJson<LeavePolicyRecord>(`${apiBaseUrl}/leave/policies/${leavePolicyId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(normalizeLeavePolicyValues(values)),
  })
}

export async function createLeaveRequest(
  apiBaseUrl: string,
  token: string,
  values: LeaveRequestFormValues,
) {
  return requestJson<LeaveRequestRecord>(`${apiBaseUrl}/leave/requests`, token, {
    method: 'POST',
    body: JSON.stringify({
      leave_type_id: Number(values.leave_type_id),
      start_date: values.start_date,
      end_date: values.end_date,
      reason: values.reason.trim(),
    }),
  })
}

export async function updateLeaveRequestDecision(
  apiBaseUrl: string,
  token: string,
  leaveRequestId: number,
  action: LeaveReviewDecisionAction | 'cancel',
  comment?: string | null,
) {
  return requestJson<LeaveRequestRecord>(`${apiBaseUrl}/leave/requests/${leaveRequestId}`, token, {
    method: 'PATCH',
    body: JSON.stringify({
      action,
      comment: comment?.trim() || null,
    }),
  })
}

async function fetchAllLeaveRequests(
  apiBaseUrl: string,
  token: string,
): Promise<LeaveRequestRecord[]> {
  const firstPage = await requestJson<PaginatedLeaveRequests>(
    `${apiBaseUrl}/leave/requests?per_page=100&page=1`,
    token,
  )

  if (firstPage.meta.last_page <= 1) {
    return firstPage.items
  }

  const remainingPages = await Promise.all(
    Array.from({ length: firstPage.meta.last_page - 1 }, (_, index) =>
      requestJson<PaginatedLeaveRequests>(
        `${apiBaseUrl}/leave/requests?per_page=100&page=${index + 2}`,
        token,
      ),
    ),
  )

  return [firstPage, ...remainingPages].flatMap((page) => page.items)
}

function normalizeLeavePolicyValues(values: LeavePolicyFormValues) {
  return {
    leave_type_id: Number(values.leave_type_id),
    annual_allowance_days: Number(values.annual_allowance_days),
    opening_balance_days: Number(values.opening_balance_days),
    accrual_frequency: values.accrual_frequency,
    carry_forward_limit_days: Number(values.carry_forward_limit_days),
    encashment_limit_days: Number(values.encashment_limit_days),
    max_consecutive_days: Number(values.max_consecutive_days),
    min_notice_days: Number(values.min_notice_days),
    requires_documentation_after_days: values.requires_documentation_after_days
      ? Number(values.requires_documentation_after_days)
      : null,
    applicable_department_id: values.applicable_department_id
      ? Number(values.applicable_department_id)
      : null,
    applicable_location_id: values.applicable_location_id
      ? Number(values.applicable_location_id)
      : null,
    status: values.status,
  }
}

function dedupeEmployees(records: Array<EmployeeReference | undefined>) {
  const byId = new Map<number, EmployeeReference>()

  for (const record of records) {
    if (!record) {
      continue
    }

    byId.set(record.id, record)
  }

  return [...byId.values()].sort((left, right) => left.full_name.localeCompare(right.full_name))
}

function toCalendarEntry(request: LeaveRequestRecord): LeaveCalendarEntry {
  return {
    id: request.id,
    employee: request.employee,
    department: request.department,
    location: request.location,
    leave_type: request.leave_type,
    start_date: request.start_date,
    end_date: request.end_date,
    total_days: request.total_days,
    status: request.status,
    reason: request.reason,
    created_at: request.created_at,
    updated_at: request.updated_at,
  }
}
