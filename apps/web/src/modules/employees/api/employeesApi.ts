import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import { fetchOrganizationWorkspace } from '../../organization/api/organizationApi'
import type { OrganizationWorkspaceData } from '../../organization/types'
import type { EmployeeDirectoryFilters, EmployeeRecord, PaginatedEmployees } from '../types'

export async function fetchEmployeeDirectory(
  apiBaseUrl: string,
  token: string,
  filters: EmployeeDirectoryFilters,
): Promise<PaginatedEmployees> {
  const params = new URLSearchParams()

  if (filters.search.trim()) {
    params.set('search', filters.search.trim())
  }

  if (filters.employmentStatus) {
    params.set('employment_status', filters.employmentStatus)
  }

  if (filters.departmentId) {
    params.set('department_id', filters.departmentId)
  }

  if (filters.designationId) {
    params.set('designation_id', filters.designationId)
  }

  if (filters.managerId) {
    params.set('manager_id', filters.managerId)
  }

  params.set('per_page', String(filters.perPage))
  params.set('page', String(filters.page))

  const response = await fetch(`${apiBaseUrl}/employees?${params.toString()}`, {
    headers: buildApiHeaders(token),
  })

  return readApiJson<PaginatedEmployees>(response)
}

export async function fetchEmployeeDetail(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
): Promise<EmployeeRecord> {
  const response = await fetch(`${apiBaseUrl}/employees/${employeeId}`, {
    headers: buildApiHeaders(token),
  })

  return readApiJson<EmployeeRecord>(response)
}

export async function fetchEmployeeDirectoryFilters(
  apiBaseUrl: string,
  token: string,
): Promise<Pick<OrganizationWorkspaceData, 'departments' | 'designations'>> {
  const data = await fetchOrganizationWorkspace(apiBaseUrl, token)

  return {
    departments: data.departments,
    designations: data.designations,
  }
}
