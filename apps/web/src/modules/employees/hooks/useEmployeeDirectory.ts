import { useMemo } from 'react'
import { useQuery } from '@tanstack/react-query'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { buildDemoOrganizationWorkspace } from '../../organization/data/demoOrganizationWorkspace'
import { fetchEmployeeDetail, fetchEmployeeDirectory, fetchEmployeeDirectoryFilters } from '../api/employeesApi'
import { buildDemoEmployees } from '../data/demoEmployees'
import type { EmployeeDirectoryFilters, EmployeeRecord, EmployeeStatus, PaginatedEmployees } from '../types'

export function useEmployeeDirectory(filters: EmployeeDirectoryFilters) {
  const access = useAppSelector((state) => state.access)
  const { snapshot, source } = useAccessSnapshot()
  const demoEmployees = useMemo(() => buildDemoEmployees(snapshot), [snapshot])
  const demoFilterData = useMemo(() => buildDemoOrganizationWorkspace(snapshot), [snapshot])
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0

  const directoryQuery = useQuery({
    queryKey: ['employee-directory', access.apiBaseUrl, access.token, filters],
    queryFn: () => fetchEmployeeDirectory(access.apiBaseUrl, access.token, filters),
    enabled: liveEnabled,
  })

  const filterQuery = useQuery({
    queryKey: ['employee-directory-filters', access.apiBaseUrl, access.token],
    queryFn: () => fetchEmployeeDirectoryFilters(access.apiBaseUrl, access.token),
    enabled: liveEnabled,
    staleTime: 300_000,
  })

  const demoDirectory = useMemo(() => filterDemoEmployees(demoEmployees, filters), [demoEmployees, filters])
  const directory = source === 'demo' ? demoDirectory : directoryQuery.data ?? null

  const managers = useMemo(() => {
    const sourceEmployees = source === 'demo' ? demoEmployees : directory?.items ?? []
    const uniqueManagers = new Map<number, NonNullable<EmployeeRecord['manager']>>()

    sourceEmployees.forEach((employee) => {
      if (employee.manager) {
        uniqueManagers.set(employee.manager.id, employee.manager)
      }
    })

    return [...uniqueManagers.values()].sort((left, right) => left.full_name.localeCompare(right.full_name))
  }, [demoEmployees, directory?.items, source])

  const departments = source === 'demo' ? demoFilterData.departments : filterQuery.data?.departments ?? []
  const designations = source === 'demo' ? demoFilterData.designations : filterQuery.data?.designations ?? []

  return {
    source,
    snapshot,
    directory,
    departments,
    designations,
    managers,
    isLoading: source === 'live' ? directoryQuery.isLoading || filterQuery.isLoading : false,
    error:
      source === 'live'
        ? ((directoryQuery.error as Error | null) ?? (filterQuery.error as Error | null) ?? null)
        : null,
    canManage: snapshot ? snapshot.user.permissions.includes('employee.manage') : access.mode === 'demo',
  }
}

export function useEmployeeDetail(employeeId: number | null) {
  const access = useAppSelector((state) => state.access)
  const { snapshot, source } = useAccessSnapshot()
  const demoEmployees = useMemo(() => buildDemoEmployees(snapshot), [snapshot])
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0 && employeeId !== null

  const detailQuery = useQuery({
    queryKey: ['employee-detail', access.apiBaseUrl, access.token, employeeId],
    queryFn: () => fetchEmployeeDetail(access.apiBaseUrl, access.token, employeeId as number),
    enabled: liveEnabled,
  })

  const demoEmployee = employeeId === null ? null : demoEmployees.find((employee) => employee.id === employeeId) ?? null

  return {
    source,
    employee: source === 'demo' ? demoEmployee : detailQuery.data ?? null,
    isLoading: source === 'live' ? detailQuery.isLoading : false,
    error: source === 'live' ? (detailQuery.error as Error | null) ?? null : null,
  }
}

function filterDemoEmployees(employees: EmployeeRecord[], filters: EmployeeDirectoryFilters): PaginatedEmployees {
  const query = filters.search.trim().toLowerCase()

  const filteredEmployees = employees.filter((employee) => {
    const matchesSearch =
      query.length === 0 ||
      [
        employee.employee_code,
        employee.email,
        employee.first_name,
        employee.last_name,
        employee.full_name,
      ]
        .join(' ')
        .toLowerCase()
        .includes(query)

    const matchesStatus = !filters.employmentStatus || employee.employment_status === filters.employmentStatus
    const matchesDepartment = !filters.departmentId || String(employee.department.id) === filters.departmentId
    const matchesDesignation = !filters.designationId || String(employee.designation.id) === filters.designationId
    const matchesManager = !filters.managerId || String(employee.manager?.id ?? '') === filters.managerId

    return matchesSearch && matchesStatus && matchesDepartment && matchesDesignation && matchesManager
  })

  const sortedEmployees = [...filteredEmployees].sort((left, right) =>
    left.employee_code.localeCompare(right.employee_code),
  )
  const start = (filters.page - 1) * filters.perPage
  const items = sortedEmployees.slice(start, start + filters.perPage)

  return {
    items,
    meta: {
      page: filters.page,
      per_page: filters.perPage,
      total: sortedEmployees.length,
      last_page: Math.max(1, Math.ceil(sortedEmployees.length / filters.perPage)),
    },
  }
}

export const employeeStatusOptions: Array<{ value: '' | EmployeeStatus; label: string }> = [
  { value: '', label: 'All statuses' },
  { value: 'active', label: 'Active' },
  { value: 'probation', label: 'Probation' },
  { value: 'notice_period', label: 'Notice period' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'terminated', label: 'Terminated' },
]
