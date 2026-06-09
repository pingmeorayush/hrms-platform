import { useMemo, useState } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  createPayrollAdjustment,
  fetchEmployeeCompensations,
  fetchPayrollAdjustments,
  updatePayrollAdjustment,
} from '../api/payrollApi'
import type {
  PayrollAdjustmentRecord,
  PayrollEmployeeOption,
  PayrollRunRecord,
} from '../types'

const demoEmployees: PayrollEmployeeOption[] = [
  {
    id: 2101,
    employee_code: 'PAY-2101',
    full_name: 'Aman Verma',
    email: 'aman.verma@phoenixhrms.test',
    employment_status: 'active',
  },
  {
    id: 2102,
    employee_code: 'PAY-2102',
    full_name: 'Nisha Rao',
    email: 'nisha.rao@phoenixhrms.test',
    employment_status: 'probation',
  },
  {
    id: 2103,
    employee_code: 'PAY-2103',
    full_name: 'Kabir Malik',
    email: 'kabir.malik@phoenixhrms.test',
    employment_status: 'active',
  },
]

const adjustmentsQueryScope = 'payroll-run-adjustments'

export function usePayrollRunAdjustments(selectedRun: PayrollRunRecord | null) {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = snapshot?.user.permissions ?? []
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0 && selectedRun !== null
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}`
  const [demoStates, setDemoStates] = useState<Record<string, Record<number, PayrollAdjustmentRecord[]>>>({})
  const [isSaving, setIsSaving] = useState(false)
  const [pendingActionLabel, setPendingActionLabel] = useState<string | null>(null)
  const [lastActionMessage, setLastActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)

  const adjustmentsQuery = useQuery({
    queryKey: [adjustmentsQueryScope, access.apiBaseUrl, access.token, selectedRun?.id ?? 'none'],
    queryFn: () => fetchPayrollAdjustments(access.apiBaseUrl, access.token, selectedRun?.id as number),
    enabled: liveEnabled,
  })

  const compensationQuery = useQuery({
    queryKey: [adjustmentsQueryScope, access.apiBaseUrl, access.token, 'employees'],
    queryFn: () => fetchEmployeeCompensations(access.apiBaseUrl, access.token, { currentOnly: true }),
    enabled: access.mode === 'live' && access.token.trim().length > 0,
  })

  const demoAdjustments = useMemo(() => {
    if (!selectedRun) {
      return []
    }

    return demoStates[demoStateKey]?.[selectedRun.id] ?? buildDemoAdjustments(selectedRun.id)
  }, [demoStateKey, demoStates, selectedRun])

  const employeeOptions = useMemo(() => {
    if (source === 'demo') {
      return demoEmployees
    }

    const currentAssignments = compensationQuery.data ?? []
    const seen = new Set<number>()

    return currentAssignments
      .filter((assignment) => assignment.employee !== null)
      .map((assignment) => assignment.employee)
      .filter((employee): employee is NonNullable<typeof employee> => employee !== null)
      .filter((employee) => {
        if (seen.has(employee.id)) {
          return false
        }

        seen.add(employee.id)
        return true
      })
      .map((employee) => ({
        id: employee.id,
        employee_code: employee.employee_code,
        full_name: employee.full_name,
        email: employee.email,
        employment_status: 'active',
      }))
  }, [compensationQuery.data, source])

  async function saveAdjustment(
    adjustmentId: number | null,
    payload: Record<string, unknown>,
  ) {
    if (!selectedRun) {
      return null
    }

    setIsSaving(true)
    setPendingActionLabel(adjustmentId ? 'Updating payroll adjustment' : 'Creating payroll adjustment')
    setLastActionMessage(null)
    setActionError(null)

    try {
      if (source === 'demo') {
        setDemoStates((current) => {
          const currentRunAdjustments = current[demoStateKey]?.[selectedRun.id] ?? buildDemoAdjustments(selectedRun.id)
          const nextAdjustment = buildDemoAdjustmentRecord(selectedRun.id, adjustmentId, payload, currentRunAdjustments)

          return {
            ...current,
            [demoStateKey]: {
              ...(current[demoStateKey] ?? {}),
              [selectedRun.id]: upsertAdjustments(currentRunAdjustments, nextAdjustment),
            },
          }
        })

        setLastActionMessage(adjustmentId ? 'Payroll adjustment updated successfully.' : 'Payroll adjustment created successfully.')
        return null
      }

      const result = adjustmentId
        ? await updatePayrollAdjustment(access.apiBaseUrl, access.token, selectedRun.id, adjustmentId, payload)
        : await createPayrollAdjustment(access.apiBaseUrl, access.token, selectedRun.id, payload)

      await queryClient.invalidateQueries({
        queryKey: [adjustmentsQueryScope, access.apiBaseUrl, access.token, selectedRun.id],
      })
      setLastActionMessage(adjustmentId ? 'Payroll adjustment updated successfully.' : 'Payroll adjustment created successfully.')

      return result
    } catch (error) {
      const message = error instanceof ApiRequestError
        ? error.message
        : error instanceof Error
          ? error.message
          : 'Payroll adjustment action failed.'

      setActionError(message)
      throw error
    } finally {
      setIsSaving(false)
      setPendingActionLabel(null)
    }
  }

  return {
    adjustments: source === 'demo' ? demoAdjustments : adjustmentsQuery.data ?? [],
    employeeOptions,
    isLoading: source === 'live' ? adjustmentsQuery.isLoading : false,
    error: source === 'live' ? ((adjustmentsQuery.error as Error | null) ?? null) : null,
    isSaving,
    pendingActionLabel,
    lastActionMessage,
    actionError,
    canManageAdjustments: permissions.includes('payroll.process'),
    async saveAdjustment(adjustmentId: number | null, payload: Record<string, unknown>) {
      return saveAdjustment(adjustmentId, payload)
    },
    clearFeedback() {
      setLastActionMessage(null)
      setActionError(null)
    },
  }
}

function buildDemoAdjustments(runId: number) {
  switch (runId) {
    case 6201:
      return [
        {
          id: 9001,
          payroll_run_id: runId,
          employee_id: 2101,
          employee: {
            id: 2101,
            employee_code: 'PAY-2101',
            full_name: 'Aman Verma',
            email: 'aman.verma@phoenixhrms.test',
          },
          adjustment_code: 'JUN_BONUS',
          name: 'Quarter close bonus',
          category: 'bonus',
          amount: '7500.00',
          effective_date: '2026-06-27',
          status: 'active',
          notes: 'Approved by finance for the June close.',
          created_at: '2026-06-02T11:30:00+05:30',
          updated_at: '2026-06-02T11:30:00+05:30',
        },
      ]
    case 6203:
      return [
        {
          id: 9002,
          payroll_run_id: runId,
          employee_id: 2103,
          employee: {
            id: 2103,
            employee_code: 'PAY-2103',
            full_name: 'Kabir Malik',
            email: 'kabir.malik@phoenixhrms.test',
          },
          adjustment_code: 'AUG_REIMB',
          name: 'Travel reimbursement',
          category: 'reimbursement',
          amount: '2400.00',
          effective_date: '2026-08-21',
          status: 'active',
          notes: 'Customer travel reimbursement cleared for August payroll.',
          created_at: '2026-06-08T09:15:00+05:30',
          updated_at: '2026-06-08T09:15:00+05:30',
        },
      ]
    default:
      return []
  }
}

function buildDemoAdjustmentRecord(
  runId: number,
  adjustmentId: number | null,
  payload: Record<string, unknown>,
  currentAdjustments: PayrollAdjustmentRecord[],
): PayrollAdjustmentRecord {
  const existing = adjustmentId
    ? currentAdjustments.find((adjustment) => adjustment.id === adjustmentId) ?? null
    : null
  const employeeId = Number(payload.employee_id)
  const employee = demoEmployees.find((item) => item.id === employeeId) ?? null

  return {
    id: adjustmentId ?? nextAdjustmentId(currentAdjustments),
    payroll_run_id: runId,
    employee_id: employeeId,
    employee: employee
      ? {
          id: employee.id,
          employee_code: employee.employee_code,
          full_name: employee.full_name,
          email: employee.email,
        }
      : existing?.employee ?? null,
    adjustment_code: String(payload.adjustment_code ?? '').trim().toUpperCase(),
    name: String(payload.name ?? '').trim(),
    category: payload.category as PayrollAdjustmentRecord['category'],
    amount: Number(payload.amount ?? 0).toFixed(2),
    effective_date: String(payload.effective_date ?? ''),
    status: (payload.status as PayrollAdjustmentRecord['status']) ?? 'active',
    notes: normalizeNullableText(payload.notes),
    created_at: existing?.created_at ?? nowIso(),
    updated_at: nowIso(),
  }
}

function upsertAdjustments(
  adjustments: PayrollAdjustmentRecord[],
  nextAdjustment: PayrollAdjustmentRecord,
) {
  return [...adjustments.filter((adjustment) => adjustment.id !== nextAdjustment.id), nextAdjustment].sort((left, right) =>
    `${right.effective_date}-${right.updated_at ?? ''}`.localeCompare(`${left.effective_date}-${left.updated_at ?? ''}`),
  )
}

function nextAdjustmentId(adjustments: PayrollAdjustmentRecord[]) {
  return Math.max(0, ...adjustments.map((adjustment) => adjustment.id)) + 1
}

function normalizeNullableText(value: unknown) {
  const text = String(value ?? '').trim()
  return text ? text : null
}

function nowIso() {
  return '2026-06-09T18:05:00+05:30'
}
