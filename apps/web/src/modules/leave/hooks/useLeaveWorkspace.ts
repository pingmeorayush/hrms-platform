import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import type { AccessSnapshot } from '../../access/types'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { buildDemoEmployees } from '../../employees/data/demoEmployees'
import type { EmployeeRecord } from '../../employees/types'
import {
  createLeavePolicy,
  createLeaveRequest,
  createLeaveType,
  fetchLeaveWorkspace,
  updateLeavePolicy,
  updateLeaveRequestDecision,
  updateLeaveType,
} from '../api/leaveApi'
import { buildDemoLeaveWorkspace } from '../data/demoLeaveWorkspace'
import type {
  LeaveAdminWorkspaceData,
  LeavePolicyFormValues,
  LeavePolicyRecord,
  LeaveReviewDecisionAction,
  LeaveRequestFormValues,
  LeaveRequestRecord,
  LeaveTypeFormValues,
  LeaveTypeRecord,
} from '../types'

const queryScope = 'leave-workspace'

export function useLeaveWorkspace() {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = snapshot?.user.permissions ?? []
  const shouldLoadOrganizationContext = hasAnyPermission(permissions, [
    'organization.view',
    'organization.manage',
    'leave.manage_policy',
    'leave.manage_balance',
    'leave.manage_accrual',
    'employee.manage',
  ])
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}`
  const [demoStates, setDemoStates] = useState<Record<string, LeaveAdminWorkspaceData>>({})
  const demoData = demoStates[demoStateKey] ?? buildDemoLeaveWorkspace(snapshot)
  const employeeDirectory = source === 'demo' ? buildDemoEmployees(snapshot) : []

  const queryKey = useMemo(
    () => [queryScope, access.apiBaseUrl, access.token, shouldLoadOrganizationContext] as const,
    [access.apiBaseUrl, access.token, shouldLoadOrganizationContext],
  )
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0

  const liveQuery = useQuery({
    queryKey,
    queryFn: () =>
      fetchLeaveWorkspace(access.apiBaseUrl, access.token, {
        includeOrganizationContext: shouldLoadOrganizationContext,
      }),
    enabled: liveEnabled,
  })

  const invalidateWorkspace = async () => {
    await queryClient.invalidateQueries({ queryKey })
  }

  const leaveTypeMutation = useMutation({
    mutationFn: ({
      leaveTypeId,
      values,
    }: {
      leaveTypeId?: number
      values: LeaveTypeFormValues
    }) =>
      leaveTypeId
        ? updateLeaveType(access.apiBaseUrl, access.token, leaveTypeId, values)
        : createLeaveType(access.apiBaseUrl, access.token, values),
    onSuccess: invalidateWorkspace,
  })

  const leavePolicyMutation = useMutation({
    mutationFn: ({
      leavePolicyId,
      values,
    }: {
      leavePolicyId?: number
      values: LeavePolicyFormValues
    }) =>
      leavePolicyId
        ? updateLeavePolicy(access.apiBaseUrl, access.token, leavePolicyId, values)
        : createLeavePolicy(access.apiBaseUrl, access.token, values),
    onSuccess: invalidateWorkspace,
  })

  const leaveRequestMutation = useMutation({
    mutationFn: (values: LeaveRequestFormValues) =>
      createLeaveRequest(access.apiBaseUrl, access.token, values),
    onSuccess: invalidateWorkspace,
  })

  const leaveDecisionMutation = useMutation({
    mutationFn: ({
      requestId,
      action,
      comment,
    }: {
      requestId: number
      action: LeaveReviewDecisionAction | 'cancel'
      comment?: string | null
    }) => updateLeaveRequestDecision(access.apiBaseUrl, access.token, requestId, action, comment),
    onSuccess: invalidateWorkspace,
  })

  const data = source === 'demo' ? demoData : liveQuery.data ?? null
  const canManagePolicy = snapshot
    ? hasAnyPermission(permissions, [
        'leave.manage_policy',
        'leave.manage_balance',
        'leave.manage_accrual',
        'employee.manage',
      ])
    : access.mode === 'demo'
  const canApproveLeave = snapshot
    ? hasAnyPermission(permissions, ['leave.approve', 'employee.manage'])
    : access.mode === 'demo'
  const canViewSelfService = snapshot
    ? hasAnyPermission(permissions, [
        'leave.view',
        'leave.request',
        'leave.approve',
        'leave.manage_policy',
        'leave.manage_balance',
        'employee.manage',
      ])
    : access.mode === 'demo'
  const canRequestLeave = snapshot
    ? hasAnyPermission(permissions, ['leave.request', 'employee.manage'])
    : access.mode === 'demo'
  const currentEmployee = data ? resolveCurrentEmployee(snapshot, data) : null
  const reviewScope =
    snapshot &&
    permissions.includes('leave.approve') &&
    !hasAnyPermission(permissions, ['employee.manage', 'leave.manage_policy', 'leave.manage_balance'])
      ? 'team'
      : 'tenant'
  const currentEmployeeBalances = currentEmployee
    ? data?.balances.filter((record) => record.employee_id === currentEmployee.id) ?? []
    : []
  const currentEmployeeRequests = currentEmployee
    ? [...(data?.requests.filter((record) => record.employee.id === currentEmployee.id) ?? [])].sort(
        (left, right) =>
          (right.updated_at ?? right.created_at ?? '').localeCompare(
            left.updated_at ?? left.created_at ?? '',
          ),
      )
    : []
  const reviewEmployees = resolveReviewEmployees(reviewScope, currentEmployee, employeeDirectory, data)
  const reviewEmployeeIds = new Set(reviewEmployees.map((employee) => employee.id))
  const scopedReviewRequests = data
    ? [
        ...data.requests.filter(
          (record) => reviewScope === 'tenant' || reviewEmployeeIds.has(record.employee.id),
        ),
      ].sort(sortLeaveRequestsForReview)
    : []
  const pendingReviewRequests = scopedReviewRequests.filter((record) => record.status === 'pending')
  const reviewCalendarEntries = scopedReviewRequests.filter(
    (record) => record.status === 'pending' || record.status === 'approved',
  )

  return {
    source,
    snapshot,
    data,
    currentEmployee,
    currentEmployeeBalances,
    currentEmployeeRequests,
    canManagePolicy,
    canApproveLeave,
    canViewSelfService,
    canRequestLeave,
    reviewScope,
    reviewEmployees,
    scopedReviewRequests,
    pendingReviewRequests,
    reviewCalendarEntries,
    isLoading: source === 'live' ? liveQuery.isLoading : false,
    error: source === 'live' ? ((liveQuery.error as Error | null) ?? null) : null,
    isSaving:
      leaveTypeMutation.isPending ||
      leavePolicyMutation.isPending ||
      leaveRequestMutation.isPending ||
      leaveDecisionMutation.isPending,
    async saveLeaveType(leaveTypeId: number | undefined, values: LeaveTypeFormValues) {
      if (!data) {
        throw new Error('Leave setup data is unavailable.')
      }

      if (source === 'demo') {
        ensureUniqueLeaveTypeCode(data.leaveTypes, values.code, leaveTypeId)

        const nextRecord: LeaveTypeRecord = {
          id: leaveTypeId ?? nextNumericId(data.leaveTypes),
          code: values.code.trim().toUpperCase(),
          name: values.name.trim(),
          category: values.category,
          description: values.description.trim() || null,
          is_paid: values.is_paid,
          requires_approval: values.requires_approval,
          allows_half_day: values.allows_half_day,
          color_token: values.color_token.trim() || '#0972d3',
          status: values.status,
          created_at:
            data.leaveTypes.find((record) => record.id === leaveTypeId)?.created_at ??
            new Date().toISOString(),
          updated_at: new Date().toISOString(),
        }

        setDemoStates((current) => {
          const currentData = current[demoStateKey] ?? buildDemoLeaveWorkspace(snapshot)

          return {
            ...current,
            [demoStateKey]: {
              ...currentData,
              leaveTypes: upsertRecord(currentData.leaveTypes, nextRecord),
              policies: currentData.policies.map((policy) =>
                policy.leave_type_id === nextRecord.id
                  ? {
                      ...policy,
                      leave_type: nextRecord,
                      updated_at: nextRecord.updated_at,
                    }
                  : policy,
              ),
              balances: currentData.balances.map((balance) =>
                balance.leave_type.id === nextRecord.id
                  ? {
                      ...balance,
                      leave_type: nextRecord,
                      updated_at: nextRecord.updated_at,
                    }
                  : balance,
              ),
              requests: currentData.requests.map((request) =>
                request.leave_type.id === nextRecord.id
                  ? {
                      ...request,
                      leave_type: nextRecord,
                      updated_at: nextRecord.updated_at,
                    }
                  : request,
              ),
              calendarEntries: currentData.calendarEntries.map((entry) =>
                entry.leave_type.id === nextRecord.id
                  ? {
                      ...entry,
                      leave_type: nextRecord,
                      updated_at: nextRecord.updated_at,
                    }
                  : entry,
              ),
            },
          }
        })

        return nextRecord
      }

      return leaveTypeMutation.mutateAsync({ leaveTypeId, values })
    },
    async saveLeavePolicy(policyId: number | undefined, values: LeavePolicyFormValues) {
      if (!data) {
        throw new Error('Leave setup data is unavailable.')
      }

      if (source === 'demo') {
        const leaveTypeId = Number(values.leave_type_id)
        const leaveType = data.leaveTypes.find((record) => record.id === leaveTypeId)

        if (!leaveType) {
          throw new ApiRequestError('Select a valid leave type.', 422, {
            leave_type_id: ['Select a valid leave type.'],
          })
        }

        const nextRecord: LeavePolicyRecord = {
          id: policyId ?? nextNumericId(data.policies),
          leave_type_id: leaveType.id,
          leave_type: leaveType,
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
          applicable_department:
            data.departments.find(
              (record) => record.id === Number(values.applicable_department_id),
            ) ?? null,
          applicable_location:
            data.locations.find((record) => record.id === Number(values.applicable_location_id)) ??
            null,
          status: values.status,
          created_at:
            data.policies.find((record) => record.id === policyId)?.created_at ??
            new Date().toISOString(),
          updated_at: new Date().toISOString(),
        }

        setDemoStates((current) => {
          const currentData = current[demoStateKey] ?? buildDemoLeaveWorkspace(snapshot)

          return {
            ...current,
            [demoStateKey]: {
              ...currentData,
              policies: upsertRecord(currentData.policies, nextRecord),
            },
          }
        })

        return nextRecord
      }

      return leavePolicyMutation.mutateAsync({ leavePolicyId: policyId, values })
    },
    async submitLeaveRequest(values: LeaveRequestFormValues) {
      if (!data || !currentEmployee) {
        throw new Error('Leave self-service is unavailable for this session.')
      }

      if (source === 'demo') {
        const leaveType = data.leaveTypes.find((record) => record.id === Number(values.leave_type_id))

        if (!leaveType) {
          throw new ApiRequestError('Select a valid leave type.', 422, {
            leave_type_id: ['Select a valid leave type.'],
          })
        }

        if (!values.start_date || !values.end_date) {
          throw new ApiRequestError('Start date and end date are required.', 422, {
            start_date: ['Start date is required.'],
            end_date: ['End date is required.'],
          })
        }

        if (values.start_date > values.end_date) {
          throw new ApiRequestError('Start date cannot be after end date.', 422, {
            start_date: ['Start date cannot be after end date.'],
          })
        }

        const totalDays = calculateInclusiveDays(values.start_date, values.end_date)
        const balance = currentEmployeeBalances.find(
          (record) => record.leave_type.id === leaveType.id,
        )

        if (balance && totalDays > balance.available_days) {
          throw new ApiRequestError('Requested leave exceeds the available balance.', 422, {
            start_date: ['Requested leave exceeds the available balance.'],
          })
        }

        const overlappingRequest = currentEmployeeRequests.find(
          (record) =>
            record.status !== 'cancelled' &&
            record.status !== 'rejected' &&
            record.status !== 'changes_requested' &&
            datesOverlap(values.start_date, values.end_date, record.start_date, record.end_date),
        )

        if (overlappingRequest) {
          throw new ApiRequestError('Leave dates overlap with an existing request.', 422, {
            start_date: ['Leave dates overlap with an existing request.'],
          })
        }

        const now = new Date().toISOString()
        const nextRequest: LeaveRequestRecord = {
          id: nextNumericId(data.requests),
          employee: currentEmployee,
          department:
            data.requests.find((record) => record.employee.id === currentEmployee.id)?.department ??
            data.departments[0],
          location:
            data.requests.find((record) => record.employee.id === currentEmployee.id)?.location ??
            null,
          leave_type: leaveType,
          start_date: values.start_date,
          end_date: values.end_date,
          total_days: totalDays,
          status: 'pending',
          reason: values.reason.trim(),
          approver_comment: null,
          can_cancel: true,
          created_at: now,
          updated_at: now,
        }

        setDemoStates((current) => {
          const currentData = current[demoStateKey] ?? buildDemoLeaveWorkspace(snapshot)
          const nextRequests = upsertRecord(currentData.requests, nextRequest)
          const nextBalances = currentData.balances.map((record) =>
            record.employee_id === currentEmployee.id && record.leave_type.id === leaveType.id
              ? {
                  ...record,
                  available_days: Math.max(0, record.available_days - totalDays),
                  booked_days: record.booked_days + totalDays,
                  updated_at: now,
                }
              : record,
          )

          return {
            ...current,
            [demoStateKey]: {
              ...currentData,
              requests: nextRequests,
              balances: nextBalances,
              calendarEntries: nextRequests.map(toCalendarEntry),
            },
          }
        })

        return nextRequest
      }

      return leaveRequestMutation.mutateAsync(values)
    },
    async decideLeaveRequest(
      requestId: number,
      {
        action,
        comment,
      }: {
        action: LeaveReviewDecisionAction
        comment?: string | null
      },
    ) {
      if (!data || !canApproveLeave) {
        throw new Error('Leave approval review is unavailable for this session.')
      }

      if (source === 'demo') {
        const request = data.requests.find((record) => record.id === requestId)

        if (
          !request ||
          request.status !== 'pending' ||
          (reviewScope === 'team' && !reviewEmployeeIds.has(request.employee.id))
        ) {
          throw new ApiRequestError('The selected leave request could not be reviewed.', 404)
        }

        const now = new Date().toISOString()
        const trimmedComment = comment?.trim() ?? ''
        const nextStatus =
          action === 'approve'
            ? 'approved'
            : action === 'reject'
              ? 'rejected'
              : 'changes_requested'
        const nextComment =
          trimmedComment ||
          (action === 'approve'
            ? 'Approved from the manager review queue.'
            : action === 'reject'
              ? 'Rejected from the manager review queue.'
              : 'Changes requested before approval.')
        const nextRequest: LeaveRequestRecord = {
          ...request,
          status: nextStatus,
          approver_comment: nextComment,
          can_cancel: false,
          updated_at: now,
        }

        setDemoStates((current) => {
          const currentData = current[demoStateKey] ?? buildDemoLeaveWorkspace(snapshot)
          const nextRequests = upsertRecord(currentData.requests, nextRequest)
          const shouldReleaseBalance = action === 'reject' || action === 'request_changes'
          const nextBalances = shouldReleaseBalance
            ? currentData.balances.map((record) =>
                record.employee_id === request.employee.id &&
                record.leave_type.id === request.leave_type.id
                  ? {
                      ...record,
                      available_days: record.available_days + request.total_days,
                      booked_days: Math.max(0, record.booked_days - request.total_days),
                      updated_at: now,
                    }
                  : record,
              )
            : currentData.balances

          return {
            ...current,
            [demoStateKey]: {
              ...currentData,
              requests: nextRequests,
              balances: nextBalances,
              calendarEntries: nextRequests.map(toCalendarEntry),
            },
          }
        })

        return nextRequest
      }

      return leaveDecisionMutation.mutateAsync({
        requestId,
        action,
        comment,
      })
    },
    async cancelLeaveRequest(requestId: number) {
      if (!data || !currentEmployee) {
        throw new Error('Leave self-service is unavailable for this session.')
      }

      if (source === 'demo') {
        const request = data.requests.find((record) => record.id === requestId)

        if (!request || request.employee.id !== currentEmployee.id) {
          throw new ApiRequestError('The selected leave request could not be found.', 404)
        }

        if (!request.can_cancel || !['pending', 'approved'].includes(request.status)) {
          throw new ApiRequestError(
            'Only pending or approved leave requests can be cancelled in this session.',
            422,
          )
        }

        const now = new Date().toISOString()
        const nextRequest: LeaveRequestRecord = {
          ...request,
          status: 'cancelled',
          can_cancel: false,
          approver_comment:
            request.status === 'approved'
              ? 'Cancelled by the employee after approval.'
              : 'Cancelled by the employee before approval.',
          updated_at: now,
        }

        setDemoStates((current) => {
          const currentData = current[demoStateKey] ?? buildDemoLeaveWorkspace(snapshot)
          const nextRequests = upsertRecord(currentData.requests, nextRequest)
          const nextBalances = currentData.balances.map((record) =>
            record.employee_id === currentEmployee.id && record.leave_type.id === request.leave_type.id
              ? {
                  ...record,
                  available_days: record.available_days + request.total_days,
                  booked_days: Math.max(0, record.booked_days - request.total_days),
                  updated_at: now,
                }
              : record,
          )

          return {
            ...current,
            [demoStateKey]: {
              ...currentData,
              requests: nextRequests,
              balances: nextBalances,
              calendarEntries: nextRequests.map(toCalendarEntry),
            },
          }
        })

        return nextRequest
      }

      return leaveDecisionMutation.mutateAsync({
        requestId,
        action: 'cancel',
      })
    },
  }
}

function hasAnyPermission(grantedPermissions: string[], requiredPermissions: string[]) {
  return requiredPermissions.some((permission) => grantedPermissions.includes(permission))
}

function ensureUniqueLeaveTypeCode(
  records: LeaveTypeRecord[],
  code: string,
  leaveTypeId: number | undefined,
) {
  const normalizedCode = code.trim().toUpperCase()
  const duplicate = records.find(
    (record) => record.code.toUpperCase() === normalizedCode && record.id !== leaveTypeId,
  )

  if (duplicate) {
    throw new ApiRequestError('Leave type code must be unique.', 422, {
      code: ['Use a different leave type code.'],
    })
  }
}

function nextNumericId(records: Array<{ id: number }>) {
  return Math.max(0, ...records.map((record) => record.id)) + 1
}

function upsertRecord<T extends { id: number; updated_at: string | null }>(records: T[], nextRecord: T) {
  return [...records.filter((record) => record.id !== nextRecord.id), nextRecord].sort((left, right) =>
    (right.updated_at ?? '').localeCompare(left.updated_at ?? ''),
  )
}

function resolveCurrentEmployee(snapshot: AccessSnapshot | null, data: LeaveAdminWorkspaceData) {
  const matchedByEmail = data.employees.find(
    (employee) => employee.email.toLowerCase() === snapshot?.user.email.toLowerCase(),
  )

  if (matchedByEmail) {
    return matchedByEmail
  }

  const matchedByName = data.employees.find(
    (employee) => employee.full_name.toLowerCase() === snapshot?.user.name.toLowerCase(),
  )

  if (matchedByName) {
    return matchedByName
  }

  const mappedEmployeeIdByUserId: Record<number, number> = {
    1: 1004,
    2: 1004,
    3: 1001,
    4: 1005,
  }

  const mappedEmployeeId = snapshot ? mappedEmployeeIdByUserId[snapshot.user.id] : undefined

  if (mappedEmployeeId) {
    return data.employees.find((employee) => employee.id === mappedEmployeeId) ?? null
  }

  return data.employees[0] ?? null
}

function resolveReviewEmployees(
  scope: 'team' | 'tenant',
  currentEmployee: LeaveAdminWorkspaceData['employees'][number] | null,
  employeeDirectory: EmployeeRecord[],
  data: LeaveAdminWorkspaceData | null,
) {
  if (scope === 'tenant') {
    if (employeeDirectory.length) {
      return employeeDirectory
    }

    return data?.employees ?? []
  }

  if (!currentEmployee) {
    return []
  }

  if (employeeDirectory.length) {
    return employeeDirectory.filter((employee) => employee.manager?.id === currentEmployee.id)
  }

  return (data?.employees ?? []).filter((employee) => employee.id !== currentEmployee.id)
}

function sortLeaveRequestsForReview(left: LeaveRequestRecord, right: LeaveRequestRecord) {
  if (left.status === 'pending' && right.status !== 'pending') {
    return -1
  }

  if (left.status !== 'pending' && right.status === 'pending') {
    return 1
  }

  if (left.start_date !== right.start_date) {
    return left.start_date.localeCompare(right.start_date)
  }

  return (right.updated_at ?? right.created_at ?? '').localeCompare(
    left.updated_at ?? left.created_at ?? '',
  )
}

function toCalendarEntry(request: LeaveRequestRecord) {
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

function calculateInclusiveDays(startDate: string, endDate: string) {
  const start = new Date(`${startDate}T00:00:00Z`)
  const end = new Date(`${endDate}T00:00:00Z`)
  const diff = end.getTime() - start.getTime()

  return Math.floor(diff / (24 * 60 * 60 * 1000)) + 1
}

function datesOverlap(startA: string, endA: string, startB: string, endB: string) {
  return startA <= endB && startB <= endA
}
