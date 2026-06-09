import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import { fetchEmployeeDirectory } from '../../employees/api/employeesApi'
import type {
  EmployeeCompensationDetail,
  EmployeeCompensationRecord,
  PaginatedPayslips,
  PaginatedPayrollPeriods,
  PaginatedPayrollRuns,
  PayrollAdjustmentRecord,
  PayrollCalendarRecord,
  PayrollEmployeeOption,
  PayrollPeriodRecord,
  PayrollRunRecord,
  PayrollSetupWorkspaceData,
  PayrollWorkspaceData,
  PayslipRecord,
  SalaryComponentRecord,
  SalaryStructureRecord,
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

async function postAction<T>(url: string, token: string, body?: Record<string, unknown>) {
  return requestJson<T>(url, token, {
    method: 'POST',
    body: JSON.stringify(body ?? {}),
  })
}

export async function fetchPayrollWorkspace(
  apiBaseUrl: string,
  token: string,
  options: {
    selectedRunId?: number | null
  } = {},
): Promise<PayrollWorkspaceData> {
  const [periods, runs, payslips, selectedRun] = await Promise.all([
    fetchAllPayrollPeriods(apiBaseUrl, token),
    fetchAllPayrollRuns(apiBaseUrl, token),
    fetchAllPayslips(apiBaseUrl, token),
    options.selectedRunId
      ? requestJson<PayrollRunRecord>(`${apiBaseUrl}/payroll/runs/${options.selectedRunId}`, token)
      : Promise.resolve<PayrollRunRecord | null>(null),
  ])

  const mappedRuns = selectedRun
    ? runs.map((run) => (run.id === selectedRun.id ? selectedRun : run))
    : runs

  const mappedPeriods = periods.map((period) => ({
    ...period,
    latest_run:
      period.latest_run && selectedRun && period.latest_run.id === selectedRun.id
        ? selectedRun
        : period.latest_run,
  }))

  return {
    periods: mappedPeriods,
    runs: mappedRuns,
    payslips,
  }
}

export async function fetchPayrollSetupWorkspace(
  apiBaseUrl: string,
  token: string,
): Promise<PayrollSetupWorkspaceData> {
  const [calendars, periods, salaryComponents, salaryStructures, compensations, employeeDirectoryResult] = await Promise.all([
    fetchPayrollCalendars(apiBaseUrl, token),
    fetchAllPayrollPeriods(apiBaseUrl, token),
    fetchSalaryComponents(apiBaseUrl, token),
    fetchSalaryStructures(apiBaseUrl, token),
    fetchEmployeeCompensations(apiBaseUrl, token),
    fetchEmployeeDirectory(apiBaseUrl, token, {
      search: '',
      employmentStatus: '',
      departmentId: '',
      designationId: '',
      managerId: '',
      page: 1,
      perPage: 100,
    }).catch(() => null),
  ])

  const employees = employeeDirectoryResult?.items.map((employee): PayrollEmployeeOption => ({
    id: employee.id,
    employee_code: employee.employee_code,
    full_name: employee.full_name,
    email: employee.email,
    employment_status: employee.employment_status,
  })) ?? []

  return {
    calendars,
    periods,
    salaryComponents,
    salaryStructures,
    compensations,
    employees,
  }
}

export function fetchPayrollCalendars(apiBaseUrl: string, token: string) {
  return requestJson<PayrollCalendarRecord[]>(`${apiBaseUrl}/payroll/calendars`, token)
}

export function createPayrollCalendar(
  apiBaseUrl: string,
  token: string,
  payload: Record<string, unknown>,
) {
  return postAction<PayrollCalendarRecord>(`${apiBaseUrl}/payroll/calendars`, token, payload)
}

export function updatePayrollCalendar(
  apiBaseUrl: string,
  token: string,
  payrollCalendarId: number,
  payload: Record<string, unknown>,
) {
  return requestJson<PayrollCalendarRecord>(`${apiBaseUrl}/payroll/calendars/${payrollCalendarId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}

export function createPayrollPeriod(
  apiBaseUrl: string,
  token: string,
  payload: Record<string, unknown>,
) {
  return postAction<PayrollPeriodRecord>(`${apiBaseUrl}/payroll/periods`, token, payload)
}

export function fetchSalaryComponents(apiBaseUrl: string, token: string) {
  return requestJson<SalaryComponentRecord[]>(`${apiBaseUrl}/payroll/salary-components`, token)
}

export function createSalaryComponent(
  apiBaseUrl: string,
  token: string,
  payload: Record<string, unknown>,
) {
  return postAction<SalaryComponentRecord>(`${apiBaseUrl}/payroll/salary-components`, token, payload)
}

export function updateSalaryComponent(
  apiBaseUrl: string,
  token: string,
  salaryComponentId: number,
  payload: Record<string, unknown>,
) {
  return requestJson<SalaryComponentRecord>(`${apiBaseUrl}/payroll/salary-components/${salaryComponentId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}

export function fetchSalaryStructures(apiBaseUrl: string, token: string) {
  return requestJson<SalaryStructureRecord[]>(`${apiBaseUrl}/payroll/salary-structures`, token)
}

export function createSalaryStructure(
  apiBaseUrl: string,
  token: string,
  payload: Record<string, unknown>,
) {
  return postAction<SalaryStructureRecord>(`${apiBaseUrl}/payroll/salary-structures`, token, payload)
}

export function versionSalaryStructure(
  apiBaseUrl: string,
  token: string,
  salaryStructureId: number,
  payload: Record<string, unknown>,
) {
  return requestJson<SalaryStructureRecord>(`${apiBaseUrl}/payroll/salary-structures/${salaryStructureId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}

export function fetchEmployeeCompensations(
  apiBaseUrl: string,
  token: string,
  options: { currentOnly?: boolean; employeeId?: number | null } = {},
) {
  const params = new URLSearchParams()

  if (options.currentOnly !== undefined) {
    params.set('current_only', options.currentOnly ? '1' : '0')
  }

  if (options.employeeId) {
    params.set('employee_id', String(options.employeeId))
  }

  const suffix = params.size ? `?${params.toString()}` : ''

  return requestJson<EmployeeCompensationRecord[]>(`${apiBaseUrl}/payroll/compensations${suffix}`, token)
}

export function assignEmployeeCompensation(
  apiBaseUrl: string,
  token: string,
  payload: Record<string, unknown>,
) {
  return postAction<EmployeeCompensationRecord>(`${apiBaseUrl}/payroll/compensations`, token, payload)
}

export function fetchPayrollAdjustments(
  apiBaseUrl: string,
  token: string,
  payrollRunId: number,
) {
  return requestJson<PayrollAdjustmentRecord[]>(`${apiBaseUrl}/payroll/runs/${payrollRunId}/adjustments`, token)
}

export function createPayrollAdjustment(
  apiBaseUrl: string,
  token: string,
  payrollRunId: number,
  payload: Record<string, unknown>,
) {
  return postAction<PayrollAdjustmentRecord>(`${apiBaseUrl}/payroll/runs/${payrollRunId}/adjustments`, token, payload)
}

export function updatePayrollAdjustment(
  apiBaseUrl: string,
  token: string,
  payrollRunId: number,
  payrollAdjustmentId: number,
  payload: Record<string, unknown>,
) {
  return requestJson<PayrollAdjustmentRecord>(
    `${apiBaseUrl}/payroll/runs/${payrollRunId}/adjustments/${payrollAdjustmentId}`,
    token,
    {
      method: 'PATCH',
      body: JSON.stringify(payload),
    },
  )
}

export function openPayrollPeriod(apiBaseUrl: string, token: string, payrollPeriodId: number) {
  return postAction<PayrollPeriodRecord>(`${apiBaseUrl}/payroll/periods/${payrollPeriodId}/open`, token)
}

export function preparePayrollPeriod(apiBaseUrl: string, token: string, payrollPeriodId: number) {
  return postAction<{
    period: PayrollPeriodRecord
    run: PayrollRunRecord
  }>(`${apiBaseUrl}/payroll/periods/${payrollPeriodId}/prepare`, token)
}

export function closePayrollPeriod(apiBaseUrl: string, token: string, payrollPeriodId: number) {
  return postAction<PayrollPeriodRecord>(`${apiBaseUrl}/payroll/periods/${payrollPeriodId}/close`, token)
}

export function calculatePayrollRun(apiBaseUrl: string, token: string, payrollRunId: number) {
  return postAction<PayrollRunRecord>(`${apiBaseUrl}/payroll/runs/${payrollRunId}/calculate`, token)
}

export function approvePayrollRun(
  apiBaseUrl: string,
  token: string,
  payrollRunId: number,
  comment?: string | null,
) {
  return postAction<PayrollRunRecord>(`${apiBaseUrl}/payroll/runs/${payrollRunId}/approve`, token, {
    comment: comment?.trim() || null,
  })
}

export function lockPayrollRun(apiBaseUrl: string, token: string, payrollRunId: number) {
  return postAction<PayrollRunRecord>(`${apiBaseUrl}/payroll/runs/${payrollRunId}/lock`, token)
}

export function reopenPayrollRun(
  apiBaseUrl: string,
  token: string,
  payrollRunId: number,
  reason: string,
) {
  return postAction<PayrollRunRecord>(`${apiBaseUrl}/payroll/runs/${payrollRunId}/reopen`, token, {
    reason,
  })
}

export function generatePayrollPayslips(apiBaseUrl: string, token: string, payrollRunId: number) {
  return postAction<{
    generated_count: number
    items: PayslipRecord[]
  }>(`${apiBaseUrl}/payroll/runs/${payrollRunId}/generate-payslips`, token)
}

export function fetchEmployeeCompensationDetail(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
) {
  return requestJson<EmployeeCompensationDetail>(
    `${apiBaseUrl}/payroll/compensations/${employeeId}`,
    token,
  )
}

export async function downloadPayrollPayslip(
  apiBaseUrl: string,
  token: string,
  payslipId: number,
  fileName: string,
) {
  const response = await fetch(
    `${apiBaseUrl}/payroll/payslips/${payslipId}/download`,
    {
      headers: {
        Accept: 'application/octet-stream',
        Authorization: `Bearer ${token}`,
      },
    },
  )

  if (!response.ok) {
    let message = 'The payslip download failed.'
    let fieldErrors: Record<string, string[]> = {}

    try {
      const payload = (await response.json()) as {
        message?: string
        errors?: Record<string, string[]>
      }

      message = payload.message ?? message
      fieldErrors = payload.errors ?? {}
    } catch {
      // Keep the default message when a binary endpoint returns a non-JSON error body.
    }

    throw new Error(
      Object.keys(fieldErrors).length
        ? `${message} ${Object.values(fieldErrors).flat().join(' ')}`
        : message,
    )
  }

  const blob = await response.blob()
  const objectUrl = window.URL.createObjectURL(blob)
  const anchor = document.createElement('a')
  anchor.href = objectUrl
  anchor.download = fileName
  document.body.append(anchor)
  anchor.click()
  anchor.remove()
  window.URL.revokeObjectURL(objectUrl)
}

async function fetchAllPayrollPeriods(apiBaseUrl: string, token: string): Promise<PayrollPeriodRecord[]> {
  const firstPage = await requestJson<PaginatedPayrollPeriods>(
    `${apiBaseUrl}/payroll/periods?per_page=100&page=1`,
    token,
  )

  if (firstPage.meta.last_page <= 1) {
    return firstPage.items
  }

  const remainingPages = await Promise.all(
    Array.from({ length: firstPage.meta.last_page - 1 }, (_, index) =>
      requestJson<PaginatedPayrollPeriods>(
        `${apiBaseUrl}/payroll/periods?per_page=100&page=${index + 2}`,
        token,
      ),
    ),
  )

  return [firstPage, ...remainingPages].flatMap((page) => page.items)
}

async function fetchAllPayrollRuns(apiBaseUrl: string, token: string): Promise<PayrollRunRecord[]> {
  const firstPage = await requestJson<PaginatedPayrollRuns>(
    `${apiBaseUrl}/payroll/runs?per_page=100&page=1`,
    token,
  )

  if (firstPage.meta.last_page <= 1) {
    return firstPage.items
  }

  const remainingPages = await Promise.all(
    Array.from({ length: firstPage.meta.last_page - 1 }, (_, index) =>
      requestJson<PaginatedPayrollRuns>(
        `${apiBaseUrl}/payroll/runs?per_page=100&page=${index + 2}`,
        token,
      ),
    ),
  )

  return [firstPage, ...remainingPages].flatMap((page) => page.items)
}

async function fetchAllPayslips(apiBaseUrl: string, token: string): Promise<PayslipRecord[]> {
  const firstPage = await requestJson<PaginatedPayslips>(
    `${apiBaseUrl}/payroll/payslips?per_page=100&page=1`,
    token,
  )

  if (firstPage.meta.last_page <= 1) {
    return firstPage.items
  }

  const remainingPages = await Promise.all(
    Array.from({ length: firstPage.meta.last_page - 1 }, (_, index) =>
      requestJson<PaginatedPayslips>(
        `${apiBaseUrl}/payroll/payslips?per_page=100&page=${index + 2}`,
        token,
      ),
    ),
  )

  return [firstPage, ...remainingPages].flatMap((page) => page.items)
}
