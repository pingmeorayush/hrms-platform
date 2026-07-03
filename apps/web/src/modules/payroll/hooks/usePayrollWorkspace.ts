import { startTransition, useEffect, useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import {
  formatRegionalCurrency,
  formatRegionalDate,
} from '../../../shared/regionalization/formatters'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import type { AccessSnapshot } from '../../access/types'
import {
  approvePayrollRun,
  calculatePayrollRun,
  closePayrollPeriod,
  downloadPayrollPayslip,
  fetchPayrollWorkspace,
  fetchEmployeeCompensationDetail,
  generatePayrollPayslips,
  lockPayrollRun,
  openPayrollPeriod,
  preparePayrollPeriod,
  reopenPayrollRun,
} from '../api/payrollApi'
import {
  buildDemoPayrollCompensationDetail,
  buildDemoPayrollWorkspace,
  resolveDemoPayrollEmployeeId,
} from '../data/demoPayrollWorkspace'
import type {
  EmployeeCompensationDetail,
  PayrollCalculationSummary,
  PayrollItemRecord,
  PayrollPeriodRecord,
  PayrollRunRecord,
  PayrollWorkspaceData,
  PayslipRecord,
} from '../types'

const queryScope = 'payroll-workspace'

type DemoActionName =
  | 'open-period'
  | 'prepare-period'
  | 'calculate-run'
  | 'approve-run'
  | 'lock-run'
  | 'reopen-run'
  | 'close-period'
  | 'generate-payslips'

function delay(ms: number) {
  return new Promise((resolve) => {
    window.setTimeout(resolve, ms)
  })
}

export function usePayrollWorkspace() {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = snapshot?.user.permissions ?? []
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}`
  const [demoStates, setDemoStates] = useState<Record<string, PayrollWorkspaceData>>({})
  const [selectedPeriodId, setSelectedPeriodId] = useState<number | null>(null)
  const [selectedRunId, setSelectedRunId] = useState<number | null>(null)
  const [pendingActionLabel, setPendingActionLabel] = useState<string | null>(null)
  const [lastActionMessage, setLastActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)

  const demoData = demoStates[demoStateKey] ?? buildDemoPayrollWorkspace()

  const baseQueryKey = useMemo(() => [queryScope, access.apiBaseUrl, access.token] as const, [access.apiBaseUrl, access.token])
  const liveQuery = useQuery({
    queryKey: [...baseQueryKey, selectedRunId ?? 'none'],
    queryFn: () => fetchPayrollWorkspace(access.apiBaseUrl, access.token, { selectedRunId }),
    enabled: liveEnabled,
  })

  const data = source === 'demo' ? demoData : liveQuery.data ?? null

  useEffect(() => {
    if (!data) {
      return
    }

    if (selectedRunId && data.runs.some((run) => run.id === selectedRunId)) {
      return
    }

    const defaultRun = [...data.runs].sort((left, right) => right.start_date.localeCompare(left.start_date))[0] ?? null
    startTransition(() => {
      setSelectedRunId(defaultRun?.id ?? null)
    })
  }, [data, selectedRunId])

  useEffect(() => {
    if (!data) {
      return
    }

    if (selectedRunId) {
      const linkedPeriod = data.periods.find((period) => period.latest_run?.id === selectedRunId)
      if (linkedPeriod && linkedPeriod.id !== selectedPeriodId) {
        startTransition(() => {
          setSelectedPeriodId(linkedPeriod.id)
        })
      }
      return
    }

    if (selectedPeriodId && data.periods.some((period) => period.id === selectedPeriodId)) {
      return
    }

    const defaultPeriod = [...data.periods].sort((left, right) => right.start_date.localeCompare(left.start_date))[0] ?? null
    startTransition(() => {
      setSelectedPeriodId(defaultPeriod?.id ?? null)
    })
  }, [data, selectedPeriodId, selectedRunId])

  const selectedRun = useMemo(
    () => data?.runs.find((run) => run.id === selectedRunId) ?? null,
    [data?.runs, selectedRunId],
  )
  const selectedPeriod = useMemo(() => {
    if (!data) {
      return null
    }

    if (selectedPeriodId) {
      return data.periods.find((period) => period.id === selectedPeriodId) ?? null
    }

    if (selectedRun) {
      return data.periods.find((period) => period.id === selectedRun.payroll_period_id) ?? null
    }

    return null
  }, [data, selectedPeriodId, selectedRun])

  const payslipsForSelectedRun = useMemo(
    () => data?.payslips.filter((record) => record.payroll_run_id === selectedRun?.id) ?? [],
    [data?.payslips, selectedRun?.id],
  )

  const canViewPayroll = hasAnyPermission(permissions, ['payroll.view', 'payroll.process', 'payroll.approve', 'payroll.lock', 'payroll.reopen'])
  const canProcessPayroll = hasAnyPermission(permissions, ['payroll.process'])
  const canApprovePayroll = hasAnyPermission(permissions, ['payroll.approve'])
  const canLockPayroll = hasAnyPermission(permissions, ['payroll.lock'])
  const canReopenPayroll = hasAnyPermission(permissions, ['payroll.reopen'])
  const canViewPayrollAmounts = hasAnyPermission(permissions, ['payroll.view', 'payroll.export'])
  const canViewPayslips = hasAnyPermission(permissions, ['payslip.view', 'payroll.view'])
  const canViewCompensation = hasAnyPermission(permissions, ['compensation.view'])
  const canAccessPayrollSelfService = hasAnyPermission(permissions, ['payslip.view', 'compensation.view'])

  const currentEmployee = useMemo(
    () => resolveCurrentPayrollEmployee(snapshot ?? null, data, source),
    [data, snapshot, source],
  )
  const currentEmployeePayslips = useMemo(
    () =>
      currentEmployee
        ? (data?.payslips ?? [])
            .filter((record) => record.employee_id === currentEmployee.id)
            .sort((left, right) =>
              `${right.payroll_date ?? ''}${right.generated_at ?? ''}`.localeCompare(
                `${left.payroll_date ?? ''}${left.generated_at ?? ''}`,
              ),
            )
        : [],
    [currentEmployee, data?.payslips],
  )
  const latestCurrentEmployeePayslip = currentEmployeePayslips[0] ?? null
  const compensationUnlocked =
    canViewCompensation &&
    latestCurrentEmployeePayslip?.status === 'generated'

  const compensationQuery = useQuery({
    queryKey: [
      ...baseQueryKey,
      'compensation-detail',
      currentEmployee?.id ?? 'none',
    ],
    queryFn: () =>
      fetchEmployeeCompensationDetail(
        access.apiBaseUrl,
        access.token,
        currentEmployee?.id as number,
      ),
    enabled: liveEnabled && canViewCompensation && currentEmployee !== null,
  })
  const currentEmployeeCompensation: EmployeeCompensationDetail | null = useMemo(() => {
    if (!currentEmployee) {
      return null
    }

    if (source === 'demo') {
      return buildDemoPayrollCompensationDetail(currentEmployee.id)
    }

    return compensationQuery.data ?? null
  }, [compensationQuery.data, currentEmployee, source])

  const mutateAction = useMutation({
    mutationFn: async (payload: {
      action: DemoActionName
      periodId?: number | null
      runId?: number | null
      reason?: string
      comment?: string
    }) => {
      setActionError(null)
      setLastActionMessage(null)
      setPendingActionLabel(actionLabel(payload.action))

      if (source === 'demo') {
        await delay(180)
        const nextData = applyDemoAction(demoStates[demoStateKey] ?? buildDemoPayrollWorkspace(), payload)
        setDemoStates((current) => ({
          ...current,
          [demoStateKey]: nextData,
        }))
        return { message: actionSuccessMessage(payload.action), data: nextData }
      }

      const token = access.token
      const apiBaseUrl = access.apiBaseUrl

      if (payload.action === 'open-period' && payload.periodId) {
        await openPayrollPeriod(apiBaseUrl, token, payload.periodId)
      }

      if (payload.action === 'prepare-period' && payload.periodId) {
        await preparePayrollPeriod(apiBaseUrl, token, payload.periodId)
      }

      if (payload.action === 'close-period' && payload.periodId) {
        await closePayrollPeriod(apiBaseUrl, token, payload.periodId)
      }

      if (payload.action === 'calculate-run' && payload.runId) {
        await calculatePayrollRun(apiBaseUrl, token, payload.runId)
      }

      if (payload.action === 'approve-run' && payload.runId) {
        await approvePayrollRun(apiBaseUrl, token, payload.runId, payload.comment)
      }

      if (payload.action === 'lock-run' && payload.runId) {
        await lockPayrollRun(apiBaseUrl, token, payload.runId)
      }

      if (payload.action === 'reopen-run' && payload.runId && payload.reason) {
        await reopenPayrollRun(apiBaseUrl, token, payload.runId, payload.reason)
      }

      if (payload.action === 'generate-payslips' && payload.runId) {
        await generatePayrollPayslips(apiBaseUrl, token, payload.runId)
      }

      return { message: actionSuccessMessage(payload.action), data: null }
    },
    onSuccess: async (result, variables) => {
      setLastActionMessage(result.message)

      if (source === 'live') {
        await queryClient.invalidateQueries({ queryKey: baseQueryKey })
      }

      if (variables.periodId) {
        setSelectedPeriodId(variables.periodId)
      }
      if (variables.runId) {
        setSelectedRunId(variables.runId)
      }
    },
    onError: (error) => {
      const message = error instanceof ApiRequestError
        ? error.message
        : error instanceof Error
          ? error.message
          : 'Payroll action failed.'
      setActionError(message)
    },
    onSettled: () => {
      setPendingActionLabel(null)
    },
  })

  return {
    source,
    snapshot,
    data,
    selectedPeriodId,
    selectedRunId,
    selectedPeriod,
    selectedRun,
    payslipsForSelectedRun,
    canViewPayroll,
    canProcessPayroll,
    canApprovePayroll,
    canLockPayroll,
    canReopenPayroll,
    canViewPayrollAmounts,
    canViewPayslips,
    isLoading: source === 'live' ? liveQuery.isLoading : false,
    error: source === 'live' ? ((liveQuery.error as Error | null) ?? null) : null,
    isSaving: mutateAction.isPending,
    pendingActionLabel,
    lastActionMessage,
    actionError,
    currentEmployee,
    currentEmployeePayslips,
    latestCurrentEmployeePayslip,
    currentEmployeeCompensation,
    canAccessPayrollSelfService,
    canViewCompensation,
    compensationUnlocked,
    selectPeriod(periodId: number) {
      setSelectedPeriodId(periodId)
      const nextRun = data?.periods.find((period) => period.id === periodId)?.latest_run ?? null
      setSelectedRunId(nextRun?.id ?? null)
    },
    selectRun(runId: number) {
      setSelectedRunId(runId)
      const nextPeriod = data?.periods.find((period) => period.latest_run?.id === runId) ?? null
      if (nextPeriod) {
        setSelectedPeriodId(nextPeriod.id)
      }
    },
    clearActionMessage() {
      setLastActionMessage(null)
      setActionError(null)
    },
    async downloadPayslip(payslip: PayslipRecord) {
      if (source === 'demo') {
        downloadDemoPayslip(payslip)
        return
      }

      await downloadPayrollPayslip(
        access.apiBaseUrl,
        access.token,
        payslip.id,
        payslip.file_name,
      )
    },
    async runAction(action: DemoActionName, options: { periodId?: number | null; runId?: number | null; reason?: string; comment?: string } = {}) {
      await mutateAction.mutateAsync({
        action,
        periodId: options.periodId ?? null,
        runId: options.runId ?? null,
        reason: options.reason,
        comment: options.comment,
      })
    },
  }
}

function applyDemoAction(
  currentData: PayrollWorkspaceData,
  payload: {
    action: DemoActionName
    periodId?: number | null
    runId?: number | null
    reason?: string
    comment?: string
  },
): PayrollWorkspaceData {
  const periods = currentData.periods.map((period) => ({ ...period }))
  const runs = currentData.runs.map((run) => ({ ...run, items: run.items.map((item) => ({ ...item, employee: item.employee ? { ...item.employee } : null, validation_errors: [...item.validation_errors] })) }))
  const payslips = currentData.payslips.map((record) => ({ ...record, employee: record.employee ? { ...record.employee } : null }))

  const periodIndex = periods.findIndex((period) => period.id === payload.periodId)
  const runIndex = runs.findIndex((run) => run.id === payload.runId)

  if (payload.action === 'open-period' && periodIndex >= 0) {
    periods[periodIndex] = {
      ...periods[periodIndex],
      status: 'open',
      opened_at: nowIso(),
      updated_at: nowIso(),
    }
  }

  if (payload.action === 'prepare-period' && periodIndex >= 0) {
    const period = periods[periodIndex]
    const nextRun = createDemoPreparedRun(period)
    runs.unshift(nextRun)
    periods[periodIndex] = {
      ...period,
      status: 'prepared',
      prepared_at: nowIso(),
      latest_run: nextRun,
      updated_at: nowIso(),
    }
  }

  if (payload.action === 'calculate-run' && runIndex >= 0) {
    const run = runs[runIndex]
    const nextItems = createCalculatedItems(run)
    const hasErrors = nextItems.some((item) => item.status === 'error')
    const nextRun: PayrollRunRecord = {
      ...run,
      status: hasErrors ? 'failed' : 'calculated',
      items: nextItems,
      calculated_at: nowIso(),
      updated_at: nowIso(),
      calculation_summary: calculateSummaryFromItems(nextItems),
    }
    runs[runIndex] = nextRun
    syncPeriodLatestRun(periods, nextRun)
  }

  if (payload.action === 'approve-run' && runIndex >= 0) {
    const nextRun = {
      ...runs[runIndex],
      status: 'approved' as const,
      approved_at: nowIso(),
      updated_at: nowIso(),
    }
    runs[runIndex] = nextRun
    syncPeriodLatestRun(periods, nextRun)
  }

  if (payload.action === 'lock-run' && runIndex >= 0) {
    const nextRun = {
      ...runs[runIndex],
      status: 'locked' as const,
      locked_at: nowIso(),
      updated_at: nowIso(),
    }
    runs[runIndex] = nextRun
    syncPeriodLatestRun(periods, nextRun)
  }

  if (payload.action === 'reopen-run' && runIndex >= 0) {
    const reopenedRun = {
      ...runs[runIndex],
      status: 'ready' as const,
      reopened_at: nowIso(),
      calculated_at: null,
      approved_at: null,
      locked_at: null,
      updated_at: nowIso(),
      items: [],
      calculation_summary: {},
    }
    runs[runIndex] = reopenedRun
    syncPeriodLatestRun(periods, reopenedRun)
    return {
      periods,
      runs,
      payslips: payslips.filter((record) => record.payroll_run_id !== reopenedRun.id),
    }
  }

  if (payload.action === 'generate-payslips' && runIndex >= 0) {
    const run = runs[runIndex]
    const generatedPayslips = createPayslipsFromRun(run)
    return {
      periods,
      runs,
      payslips: [
        ...payslips.filter((record) => record.payroll_run_id !== run.id),
        ...generatedPayslips,
      ],
    }
  }

  if (payload.action === 'close-period' && periodIndex >= 0) {
    periods[periodIndex] = {
      ...periods[periodIndex],
      status: 'closed',
      closed_at: nowIso(),
      updated_at: nowIso(),
    }
  }

  return {
    periods,
    runs,
    payslips,
  }
}

function createDemoPreparedRun(period: PayrollPeriodRecord): PayrollRunRecord {
  return {
    id: period.id + 1000,
    payroll_period_id: period.id,
    name: `${period.name} Preparation Run`,
    frequency: period.frequency,
    start_date: period.start_date,
    end_date: period.end_date,
    status: 'ready',
    prerequisite_summary: {
      ready_for_calculation: true,
      blocking_count: 0,
      warning_count: 0,
      passed_count: 4,
    },
    prerequisite_snapshot: {
      checks: [
        {
          code: 'attendance_finalization',
          title: 'Attendance finalization',
          status: 'passed',
          detail: 'Attendance coverage is complete for the payroll period.',
        },
        {
          code: 'compensation_coverage',
          title: 'Compensation coverage',
          status: 'passed',
          detail: 'Compensation assignments are complete for all active employees.',
        },
      ],
      summary: {
        ready_for_calculation: true,
        blocking_count: 0,
        warning_count: 0,
        passed_count: 4,
      },
    },
    input_summary: {
      employee_count: 19,
      input_count: 96,
      manual_adjustment_count: 0,
      attendance_record_count: 61,
      approved_leave_request_count: 5,
      total_worked_minutes: 17320,
      total_overtime_minutes: 45,
      total_lop_days: 0,
      total_paid_leave_days: 2,
      total_unpaid_leave_days: 0,
      total_manual_adjustment_amount: 0,
    },
    calculation_summary: {},
    items: [],
    prepared_at: nowIso(),
    inputs_generated_at: nowIso(),
    calculated_at: null,
    approved_at: null,
    locked_at: null,
    reopened_at: null,
    closed_at: null,
    created_at: nowIso(),
    updated_at: nowIso(),
  }
}

function createCalculatedItems(run: PayrollRunRecord): PayrollItemRecord[] {
  if (run.status === 'failed') {
    return run.items
  }

  return [
    {
      id: run.id * 10 + 1,
      employee_id: 2101,
      employee: {
        id: 2101,
        employee_code: 'PAY-2101',
        full_name: 'Aman Verma',
        email: 'aman.verma@phoenixhrms.test',
      },
      employee_compensation_id: 9101,
      status: 'calculated',
      employment_days: '31.00',
      unpaid_days: '0.00',
      lop_days: '0.00',
      overtime_minutes: 45,
      overtime_earnings: currency(351.56),
      gross_salary: currency(62000),
      total_earnings: currency(62351.56),
      total_deductions: currency(3720),
      net_salary: currency(58631.56),
      employer_cost: currency(64331.56),
      validation_errors: [],
      created_at: nowIso(),
      updated_at: nowIso(),
    },
    {
      id: run.id * 10 + 2,
      employee_id: 2103,
      employee: {
        id: 2103,
        employee_code: 'PAY-2103',
        full_name: 'Kabir Malik',
        email: 'kabir.malik@phoenixhrms.test',
      },
      employee_compensation_id: 9103,
      status: 'calculated',
      employment_days: '31.00',
      unpaid_days: '0.50',
      lop_days: '0.50',
      overtime_minutes: 0,
      overtime_earnings: currency(0),
      gross_salary: currency(51000),
      total_earnings: currency(51000),
      total_deductions: currency(3187.5),
      net_salary: currency(47812.5),
      employer_cost: currency(52740),
      validation_errors: [],
      created_at: nowIso(),
      updated_at: nowIso(),
    },
  ]
}

function currency(value: number) {
  return value.toFixed(2)
}

function createPayslipsFromRun(run: PayrollRunRecord): PayslipRecord[] {
  return run.items
    .filter((item) => item.status === 'calculated')
    .map((item) => ({
      id: Number(`${run.id}${item.id}`),
      payroll_run_id: run.id,
      payroll_period_id: run.payroll_period_id,
      payroll_item_id: item.id,
      employee_id: item.employee_id,
      employee: item.employee,
      slip_number: `PSL-${run.id}-${item.employee?.employee_code ?? item.employee_id}`,
      status: 'generated',
      currency: 'INR',
      start_date: run.start_date,
      end_date: run.end_date,
      payroll_date: run.end_date,
      file_name: `${(item.employee?.employee_code ?? `emp-${item.employee_id}`).toLowerCase()}-${run.start_date.replaceAll('-', '')}-${run.end_date.replaceAll('-', '')}-payslip.html`,
      gross_salary: item.gross_salary,
      total_earnings: item.total_earnings,
      total_deductions: item.total_deductions,
      net_salary: item.net_salary,
      employer_cost: item.employer_cost,
      generated_at: nowIso(),
      created_at: nowIso(),
      updated_at: nowIso(),
    }))
}

function calculateSummaryFromItems(items: PayrollItemRecord[]): Partial<PayrollCalculationSummary> {
  const summary = items.reduce(
    (accumulator, item) => {
      accumulator.employee_count += 1
      accumulator.item_count += 1
      accumulator.error_count += item.status === 'error' ? 1 : 0
      accumulator.gross_salary_total += Number(item.gross_salary)
      accumulator.total_earnings += Number(item.total_earnings)
      accumulator.total_deductions += Number(item.total_deductions)
      accumulator.net_salary_total += Number(item.net_salary)
      accumulator.employer_cost_total += Number(item.employer_cost)
      accumulator.total_lop_days += Number(item.lop_days)
      accumulator.total_unpaid_days += Number(item.unpaid_days)
      accumulator.total_overtime_earnings += Number(item.overtime_earnings)

      return accumulator
    },
    {
      employee_count: 0,
      item_count: 0,
      error_count: 0,
      gross_salary_total: 0,
      total_earnings: 0,
      total_deductions: 0,
      net_salary_total: 0,
      employer_cost_total: 0,
      total_lop_days: 0,
      total_unpaid_days: 0,
      total_overtime_earnings: 0,
    },
  )

  return Object.fromEntries(
    Object.entries(summary).map(([key, value]) => [
      key,
      ['employee_count', 'item_count', 'error_count'].includes(key) ? value : Number(value.toFixed(2)),
    ]),
  ) as Partial<PayrollCalculationSummary>
}

function syncPeriodLatestRun(periods: PayrollPeriodRecord[], run: PayrollRunRecord) {
  const periodIndex = periods.findIndex((period) => period.id === run.payroll_period_id)

  if (periodIndex >= 0) {
    periods[periodIndex] = {
      ...periods[periodIndex],
      latest_run: run,
      updated_at: nowIso(),
    }
  }
}

function actionLabel(action: DemoActionName) {
  return {
    'open-period': 'Opening payroll period',
    'prepare-period': 'Preparing payroll period',
    'calculate-run': 'Calculating payroll run',
    'approve-run': 'Approving payroll run',
    'lock-run': 'Locking payroll run',
    'reopen-run': 'Reopening payroll run',
    'close-period': 'Closing payroll period',
    'generate-payslips': 'Generating payslips',
  }[action]
}

function actionSuccessMessage(action: DemoActionName) {
  return {
    'open-period': 'Payroll period opened successfully.',
    'prepare-period': 'Payroll period prepared successfully.',
    'calculate-run': 'Payroll run calculated successfully.',
    'approve-run': 'Payroll run approved successfully.',
    'lock-run': 'Payroll run locked successfully.',
    'reopen-run': 'Payroll run reopened successfully.',
    'close-period': 'Payroll period closed successfully.',
    'generate-payslips': 'Payslips generated successfully.',
  }[action]
}

function hasAnyPermission(permissions: string[], requiredPermissions: string[]) {
  return requiredPermissions.some((permission) => permissions.includes(permission))
}

function resolveCurrentPayrollEmployee(
  snapshot: AccessSnapshot | null,
  data: PayrollWorkspaceData | null,
  source: 'demo' | 'live',
) {
  if (!snapshot || !data) {
    return null
  }

  const allEmployees = new Map<number, NonNullable<PayslipRecord['employee']>>()

  for (const payslip of data.payslips) {
    if (payslip.employee) {
      allEmployees.set(payslip.employee.id, payslip.employee)
    }
  }

  const employees = Array.from(allEmployees.values())
  const linkedEmployee = snapshot.user.employee

  if (linkedEmployee) {
    const resolvedEmployee = allEmployees.get(linkedEmployee.id)

    if (resolvedEmployee) {
      return resolvedEmployee
    }

    return {
      id: linkedEmployee.id,
      employee_code: linkedEmployee.employee_code,
      full_name: linkedEmployee.full_name,
      email: linkedEmployee.email ?? snapshot.user.email,
    }
  }

  if (source === 'live') {
    return null
  }

  const matchedByEmail = employees.find(
    (employee) => employee.email.toLowerCase() === snapshot.user.email.toLowerCase(),
  )

  if (matchedByEmail) {
    return matchedByEmail
  }

  const matchedByName = employees.find(
    (employee) => employee.full_name.toLowerCase() === snapshot.user.name.toLowerCase(),
  )

  if (matchedByName) {
    return matchedByName
  }

  const mappedEmployeeId = resolveDemoPayrollEmployeeId(snapshot.user.id)

  if (mappedEmployeeId) {
    const mappedEmployee = employees.find((employee) => employee.id === mappedEmployeeId)
    if (mappedEmployee) {
      return mappedEmployee
    }

    const mappedCompensation = buildDemoPayrollCompensationDetail(mappedEmployeeId)
    if (mappedCompensation) {
      return mappedCompensation.employee
    }
  }

  return null
}

function downloadDemoPayslip(payslip: PayslipRecord) {
  if (typeof window === 'undefined' || typeof document === 'undefined') {
    return
  }

  const html = renderDemoPayslipHtml(payslip)
  const blob = new Blob([html], { type: 'text/html' })
  const objectUrl = window.URL.createObjectURL(blob)
  const anchor = document.createElement('a')
  anchor.href = objectUrl
  anchor.download = payslip.file_name
  document.body.append(anchor)
  anchor.click()
  anchor.remove()
  window.URL.revokeObjectURL(objectUrl)
}

function renderDemoPayslipHtml(payslip: PayslipRecord) {
  const companyName = payslip.company_snapshot?.name ?? 'Phoenix People Ops'
  const employeeName = payslip.employee?.full_name ?? 'Employee'
  const employeeCode = payslip.employee?.employee_code ?? '—'
  const employeeEmail = payslip.employee?.email ?? '—'
  const earningsRows = (payslip.earnings_breakdown ?? [])
    .map(
      (line) => `
        <tr>
          <td>${escapeHtml(line.name ?? line.code ?? 'Component')}</td>
          <td class="amount">${formatMoney(line.base_amount ?? line.prorated_amount ?? 0, payslip.currency)}</td>
          <td class="amount">${formatMoney(line.prorated_amount ?? 0, payslip.currency)}</td>
        </tr>`,
    )
    .join('')
  const deductionRows = (payslip.deductions_breakdown ?? [])
    .map(
      (line) => `
        <tr>
          <td>${escapeHtml(line.name ?? line.code ?? 'Component')}</td>
          <td class="amount">${formatMoney(line.base_amount ?? line.prorated_amount ?? 0, payslip.currency)}</td>
          <td class="amount">${formatMoney(line.prorated_amount ?? 0, payslip.currency)}</td>
        </tr>`,
    )
    .join('')
  const employerContributionRows = (payslip.employer_contribution_breakdown ?? [])
    .map(
      (line) => `
        <tr>
          <td>${escapeHtml(line.name ?? line.code ?? 'Component')}</td>
          <td class="amount">${formatMoney(line.prorated_amount ?? 0, payslip.currency)}</td>
        </tr>`,
    )
    .join('')

  return `<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>${escapeHtml(payslip.slip_number)}</title>
    <style>
      :root {
        color-scheme: light;
        --ink: #172033;
        --muted: #5f6b85;
        --line: #d8dfeb;
        --soft: #eef3f9;
        --card: #ffffff;
        --accent: #0f4c81;
      }
      * { box-sizing: border-box; }
      body {
        margin: 0;
        padding: 24px;
        background: #f3f6fb;
        color: var(--ink);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      }
      .document {
        max-width: 960px;
        margin: 0 auto;
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 24px;
        overflow: hidden;
      }
      .hero {
        padding: 28px 32px 24px;
        background: linear-gradient(135deg, #0f4c81 0%, #0d355a 100%);
        color: #fff;
      }
      .hero-top, .tables, .grid-two, .meta-grid { display: grid; gap: 18px; }
      .hero-top { grid-template-columns: minmax(0, 1fr) 240px; align-items: start; }
      .grid-two, .tables { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .pill {
        display: inline-flex;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255,255,255,0.18);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
      }
      .hero h1 { margin: 10px 0 4px; font-size: 31px; line-height: 1.1; }
      .hero p { margin: 0; color: rgba(255,255,255,0.82); }
      .net-card, .section, .summary {
        border: 1px solid var(--line);
        border-radius: 20px;
        background: #fff;
      }
      .net-card {
        padding: 18px 20px;
        background: rgba(255,255,255,0.12);
        border-color: rgba(255,255,255,0.18);
      }
      .net-card .label, .detail .label, .summary .label { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
      .net-card .label { color: rgba(255,255,255,0.72); }
      .net-card .amount { display: block; margin-top: 8px; font-size: 30px; font-weight: 800; }
      .body { padding: 28px 32px 32px; }
      .section { padding: 20px; }
      .section h2, .summary h2 { margin: 0 0 16px; font-size: 16px; }
      .meta-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .detail .label, .summary .label { color: var(--muted); display:block; }
      .detail .value { display:block; margin-top:4px; font-size:14px; font-weight:600; }
      table { width:100%; border-collapse:collapse; }
      thead th {
        padding: 11px 12px;
        background: var(--soft);
        color: var(--muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        text-align: left;
      }
      td { padding: 12px; border-bottom: 1px solid var(--line); font-size: 14px; }
      tbody tr:last-child td { border-bottom: none; }
      .amount { text-align: right; white-space: nowrap; }
      .summary { padding: 20px; }
      .summary-row {
        display:flex; justify-content:space-between; gap:16px;
        padding:10px 0; border-bottom:1px solid var(--line);
      }
      .summary-row:last-of-type { border-bottom:none; }
      .summary-highlight {
        margin-top:18px; padding:18px 20px; border-radius:18px;
        background:#eef5fd; border:1px solid #cde0f4;
      }
      .summary-highlight .amount { display:block; margin-top:8px; font-size:32px; font-weight:800; color:#0d355a; }
      .footer {
        margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--line);
        color: var(--muted); font-size: 12px;
      }
      @media (max-width: 860px) {
        body { padding: 12px; }
        .hero, .body { padding: 22px; }
        .hero-top, .grid-two, .tables, .meta-grid { grid-template-columns: 1fr; }
      }
    </style>
  </head>
  <body>
    <main class="document">
      <section class="hero">
        <div class="hero-top">
          <div>
            <span class="pill">Salary Slip</span>
            <h1>${escapeHtml(companyName)}</h1>
            <p>Payroll statement for ${escapeHtml(employeeName)} covering ${formatDisplayDate(payslip.start_date)} to ${formatDisplayDate(payslip.end_date)}.</p>
          </div>
          <div class="net-card">
            <span class="label">Net Pay</span>
            <span class="amount">${formatMoney(payslip.net_salary, payslip.currency)}</span>
          </div>
        </div>
      </section>
      <section class="body">
        <div class="grid-two">
          <section class="section">
            <h2>Employee Details</h2>
            <div class="meta-grid">
              <div class="detail"><span class="label">Employee Name</span><span class="value">${escapeHtml(employeeName)}</span></div>
              <div class="detail"><span class="label">Employee Code</span><span class="value">${escapeHtml(employeeCode)}</span></div>
              <div class="detail"><span class="label">Email</span><span class="value">${escapeHtml(employeeEmail)}</span></div>
              <div class="detail"><span class="label">Slip Number</span><span class="value">${escapeHtml(payslip.slip_number)}</span></div>
            </div>
          </section>
          <section class="section">
            <h2>Payroll Details</h2>
            <div class="meta-grid">
              <div class="detail"><span class="label">Period Range</span><span class="value">${formatDisplayDate(payslip.start_date)} to ${formatDisplayDate(payslip.end_date)}</span></div>
              <div class="detail"><span class="label">Payroll Date</span><span class="value">${formatDisplayDate(payslip.payroll_date)}</span></div>
              <div class="detail"><span class="label">Generated On</span><span class="value">${formatDisplayDate(payslip.generated_at)}</span></div>
              <div class="detail"><span class="label">Currency</span><span class="value">${escapeHtml(payslip.currency)}</span></div>
            </div>
          </section>
        </div>
        <div class="tables">
          <section class="section">
            <h2>Earnings Breakdown</h2>
            <table>
              <thead><tr><th>Component</th><th class="amount">Base</th><th class="amount">Payable</th></tr></thead>
              <tbody>${earningsRows || '<tr><td colspan="3">No earning components were captured for this payslip.</td></tr>'}</tbody>
            </table>
          </section>
          <section class="section">
            <h2>Deductions Breakdown</h2>
            <table>
              <thead><tr><th>Component</th><th class="amount">Base</th><th class="amount">Applied</th></tr></thead>
              <tbody>${deductionRows || '<tr><td colspan="3">No deduction components were captured for this payslip.</td></tr>'}</tbody>
            </table>
          </section>
        </div>
        <div class="grid-two" style="margin-top: 18px;">
          <section class="section">
            <h2>Employer Contributions</h2>
            <table>
              <thead><tr><th>Component</th><th class="amount">Amount</th></tr></thead>
              <tbody>${employerContributionRows || '<tr><td colspan="2">No employer contribution entries were recorded for this payslip.</td></tr>'}</tbody>
            </table>
          </section>
          <section class="summary">
            <h2>Payroll Summary</h2>
            <div class="summary-row"><span class="label">Gross Salary</span><strong>${formatMoney(payslip.gross_salary, payslip.currency)}</strong></div>
            <div class="summary-row"><span class="label">Total Earnings</span><strong>${formatMoney(payslip.total_earnings, payslip.currency)}</strong></div>
            <div class="summary-row"><span class="label">Total Deductions</span><strong>${formatMoney(payslip.total_deductions, payslip.currency)}</strong></div>
            <div class="summary-row"><span class="label">Employer Cost</span><strong>${formatMoney(payslip.employer_cost, payslip.currency)}</strong></div>
            <div class="summary-highlight">
              <span class="label">Net Pay Released</span>
              <span class="amount">${formatMoney(payslip.net_salary, payslip.currency)}</span>
            </div>
          </section>
        </div>
        <div class="footer">
          This is a system-generated salary slip intended for controlled employee payroll release.
        </div>
      </section>
    </main>
  </body>
</html>`
}

function formatMoney(value: string | number | null | undefined, currency: string) {
  return formatRegionalCurrency(value, currency || 'INR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

function formatDisplayDate(value: string | null | undefined) {
  return formatRegionalDate(value, '—')
}

function escapeHtml(value: string) {
  return value
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;')
}

function nowIso() {
  return '2026-06-08T14:20:00+05:30'
}
