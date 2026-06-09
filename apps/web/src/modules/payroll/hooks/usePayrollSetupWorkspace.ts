import { useState } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  assignEmployeeCompensation,
  createPayrollCalendar,
  createPayrollPeriod,
  createSalaryComponent,
  createSalaryStructure,
  fetchPayrollSetupWorkspace,
  updatePayrollCalendar,
  updateSalaryComponent,
  versionSalaryStructure,
} from '../api/payrollApi'
import type {
  EmployeeCompensationRecord,
  PayrollCalendarRecord,
  PayrollEmployeeOption,
  PayrollPeriodRecord,
  PayrollSetupWorkspaceData,
  SalaryComponentRecord,
  SalaryStructureComponentRecord,
  SalaryStructureRecord,
} from '../types'

const queryScope = 'payroll-setup-workspace'

type SetupAction =
  | 'save-calendar'
  | 'save-period'
  | 'save-component'
  | 'save-structure'
  | 'assign-compensation'

export function usePayrollSetupWorkspace() {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = snapshot?.user.permissions ?? []
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}`
  const [demoStates, setDemoStates] = useState<Record<string, PayrollSetupWorkspaceData>>({})
  const [isSaving, setIsSaving] = useState(false)
  const [pendingActionLabel, setPendingActionLabel] = useState<string | null>(null)
  const [lastActionMessage, setLastActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)

  const demoData = demoStates[demoStateKey] ?? buildDemoPayrollSetupWorkspace()
  const liveQuery = useQuery({
    queryKey: [queryScope, access.apiBaseUrl, access.token],
    queryFn: () => fetchPayrollSetupWorkspace(access.apiBaseUrl, access.token),
    enabled: liveEnabled,
  })

  const data = source === 'demo' ? demoData : liveQuery.data ?? null

  const canManagePayrollSetup = hasAnyPermission(permissions, ['payroll.process', 'salary.manage', 'compensation.manage'])
  const canManageSalaryConfiguration = hasAnyPermission(permissions, ['salary.manage'])
  const canManageCompensation = hasAnyPermission(permissions, ['compensation.manage'])
  const canProcessPayroll = hasAnyPermission(permissions, ['payroll.process'])

  async function executeAction<T>({
    action,
    live,
    demo,
  }: {
    action: {
      id: SetupAction
      label: string
      successMessage: string
    }
    live: () => Promise<T>
    demo: (currentData: PayrollSetupWorkspaceData) => PayrollSetupWorkspaceData
  }): Promise<T | null> {
    setIsSaving(true)
    setPendingActionLabel(action.label)
    setLastActionMessage(null)
    setActionError(null)

    try {
      if (source === 'demo') {
        setDemoStates((current) => ({
          ...current,
          [demoStateKey]: demo(current[demoStateKey] ?? buildDemoPayrollSetupWorkspace()),
        }))
        setLastActionMessage(action.successMessage)

        return null
      }

      const result = await live()
      await queryClient.invalidateQueries({ queryKey: [queryScope, access.apiBaseUrl, access.token] })
      setLastActionMessage(action.successMessage)

      return result
    } catch (error) {
      const message = error instanceof ApiRequestError
        ? error.message
        : error instanceof Error
          ? error.message
          : 'Payroll setup action failed.'

      setActionError(message)
      throw error
    } finally {
      setIsSaving(false)
      setPendingActionLabel(null)
    }
  }

  return {
    source,
    snapshot,
    data,
    isLoading: source === 'live' ? liveQuery.isLoading : false,
    error: source === 'live' ? ((liveQuery.error as Error | null) ?? null) : null,
    isSaving,
    pendingActionLabel,
    lastActionMessage,
    actionError,
    canManagePayrollSetup,
    canManageSalaryConfiguration,
    canManageCompensation,
    canProcessPayroll,
    clearActionMessage() {
      setLastActionMessage(null)
      setActionError(null)
    },
    async saveCalendar(calendarId: number | null, payload: Record<string, unknown>) {
      return executeAction({
        action: {
          id: 'save-calendar',
          label: calendarId ? 'Updating payroll calendar' : 'Creating payroll calendar',
          successMessage: calendarId ? 'Payroll calendar updated successfully.' : 'Payroll calendar created successfully.',
        },
        live: () =>
          calendarId
            ? updatePayrollCalendar(access.apiBaseUrl, access.token, calendarId, payload)
            : createPayrollCalendar(access.apiBaseUrl, access.token, payload),
        demo: (current) => applyDemoCalendarUpsert(current, calendarId, payload),
      })
    },
    async savePeriod(payload: Record<string, unknown>) {
      return executeAction({
        action: {
          id: 'save-period',
          label: 'Creating payroll period',
          successMessage: 'Payroll period created successfully.',
        },
        live: () => createPayrollPeriod(access.apiBaseUrl, access.token, payload),
        demo: (current) => applyDemoPeriodCreate(current, payload),
      })
    },
    async saveSalaryComponent(componentId: number | null, payload: Record<string, unknown>) {
      return executeAction({
        action: {
          id: 'save-component',
          label: componentId ? 'Updating salary component' : 'Creating salary component',
          successMessage: componentId ? 'Salary component updated successfully.' : 'Salary component created successfully.',
        },
        live: () =>
          componentId
            ? updateSalaryComponent(access.apiBaseUrl, access.token, componentId, payload)
            : createSalaryComponent(access.apiBaseUrl, access.token, payload),
        demo: (current) => applyDemoSalaryComponentUpsert(current, componentId, payload),
      })
    },
    async saveSalaryStructure(structureId: number | null, payload: Record<string, unknown>) {
      return executeAction({
        action: {
          id: 'save-structure',
          label: structureId ? 'Creating salary structure version' : 'Creating salary structure',
          successMessage: structureId ? 'Salary structure version created successfully.' : 'Salary structure created successfully.',
        },
        live: () =>
          structureId
            ? versionSalaryStructure(access.apiBaseUrl, access.token, structureId, payload)
            : createSalaryStructure(access.apiBaseUrl, access.token, payload),
        demo: (current) => applyDemoSalaryStructureUpsert(current, structureId, payload),
      })
    },
    async assignCompensation(payload: Record<string, unknown>) {
      return executeAction({
        action: {
          id: 'assign-compensation',
          label: 'Assigning employee compensation',
          successMessage: 'Employee compensation assigned successfully.',
        },
        live: () => assignEmployeeCompensation(access.apiBaseUrl, access.token, payload),
        demo: (current) => applyDemoCompensationAssignment(current, payload),
      })
    },
  }
}

function hasAnyPermission(permissions: string[], requiredPermissions: string[]) {
  return requiredPermissions.some((permission) => permissions.includes(permission))
}

function buildDemoPayrollSetupWorkspace(): PayrollSetupWorkspaceData {
  const calendars: PayrollCalendarRecord[] = [
    {
      id: 301,
      name: 'Main Monthly Payroll',
      frequency: 'monthly',
      timezone: 'Asia/Kolkata',
      payroll_day: 30,
      payroll_weekday: null,
      is_default: true,
      status: 'active',
      created_at: '2026-06-01T09:00:00+05:30',
      updated_at: '2026-06-08T14:20:00+05:30',
    },
    {
      id: 302,
      name: 'Weekly Contractor Payroll',
      frequency: 'weekly',
      timezone: 'Asia/Kolkata',
      payroll_day: null,
      payroll_weekday: 5,
      is_default: false,
      status: 'active',
      created_at: '2026-05-15T09:00:00+05:30',
      updated_at: '2026-06-07T10:00:00+05:30',
    },
  ]

  const periods: PayrollPeriodRecord[] = [
    {
      id: 401,
      payroll_calendar_id: 301,
      payroll_calendar: calendars[0],
      name: 'July 2026 Payroll',
      frequency: 'monthly',
      start_date: '2026-07-01',
      end_date: '2026-07-31',
      payroll_date: '2026-07-31',
      status: 'prepared',
      opened_at: '2026-07-01T09:00:00+05:30',
      prepared_at: '2026-07-29T11:20:00+05:30',
      closed_at: null,
      latest_run: null,
      created_at: '2026-06-20T09:00:00+05:30',
      updated_at: '2026-07-29T11:20:00+05:30',
    },
    {
      id: 402,
      payroll_calendar_id: 301,
      payroll_calendar: calendars[0],
      name: 'August 2026 Payroll',
      frequency: 'monthly',
      start_date: '2026-08-01',
      end_date: '2026-08-31',
      payroll_date: '2026-08-31',
      status: 'draft',
      opened_at: null,
      prepared_at: null,
      closed_at: null,
      latest_run: null,
      created_at: '2026-06-28T09:00:00+05:30',
      updated_at: '2026-06-28T09:00:00+05:30',
    },
  ]

  const salaryComponents: SalaryComponentRecord[] = [
    {
      id: 501,
      code: 'BASIC',
      name: 'Basic Salary',
      category: 'earning',
      calculation_type: 'fixed',
      default_formula_inputs: {
        flat_amount: '50000.00',
        percentage_value: null,
        percentage_basis_component_codes: [],
        expression_formula: null,
      },
      is_taxable: true,
      is_proratable: true,
      display_order: 1,
      status: 'active',
      created_at: '2026-06-01T09:00:00+05:30',
      updated_at: '2026-06-01T09:00:00+05:30',
    },
    {
      id: 502,
      code: 'HRA',
      name: 'House Rent Allowance',
      category: 'earning',
      calculation_type: 'percentage',
      default_formula_inputs: {
        flat_amount: null,
        percentage_value: '40.0000',
        percentage_basis_component_codes: ['BASIC'],
        expression_formula: null,
      },
      is_taxable: true,
      is_proratable: true,
      display_order: 2,
      status: 'active',
      created_at: '2026-06-01T09:00:00+05:30',
      updated_at: '2026-06-01T09:00:00+05:30',
    },
    {
      id: 503,
      code: 'PF',
      name: 'Provident Fund',
      category: 'deduction',
      calculation_type: 'expression',
      default_formula_inputs: {
        flat_amount: null,
        percentage_value: null,
        percentage_basis_component_codes: [],
        expression_formula: 'MIN(BASIC * 0.12, 1800)',
      },
      is_taxable: false,
      is_proratable: true,
      display_order: 3,
      status: 'active',
      created_at: '2026-06-01T09:00:00+05:30',
      updated_at: '2026-06-05T09:00:00+05:30',
    },
    {
      id: 504,
      code: 'BONUS',
      name: 'Performance Bonus',
      category: 'earning',
      calculation_type: 'fixed',
      default_formula_inputs: {
        flat_amount: '0.00',
        percentage_value: null,
        percentage_basis_component_codes: [],
        expression_formula: null,
      },
      is_taxable: true,
      is_proratable: false,
      display_order: 4,
      status: 'inactive',
      created_at: '2026-05-20T09:00:00+05:30',
      updated_at: '2026-06-02T09:00:00+05:30',
    },
  ]

  const salaryStructures: SalaryStructureRecord[] = [
    buildDemoSalaryStructure({
      id: 601,
      previous_version_id: 600,
      code: 'ENG-G6',
      name: 'Engineering Grade 6',
      currency: 'INR',
      country_code: 'IN',
      pay_frequency: 'monthly',
      grade: 'G6',
      band: 'B3',
      level: 'L2',
      annual_ctc_amount: '1980000.00',
      basic_salary_amount: '660000.00',
      gross_salary_amount: '165000.00',
      net_salary_amount: '129000.00',
      effective_from: '2026-08-01',
      revision_date: '2026-08-01',
      version: 2,
      status: 'active',
      notes: 'Primary engineering compensation structure.',
      componentConfigs: [
        { componentId: 501, displayOrder: 1, amount: '55000.00' },
        { componentId: 502, displayOrder: 2, percentageValue: '40.0000', basisCodes: ['BASIC'] },
        { componentId: 503, displayOrder: 3, expressionFormula: 'MIN(BASIC * 0.12, 1800)' },
      ],
      created_at: '2026-06-01T09:00:00+05:30',
      updated_at: '2026-08-01T09:00:00+05:30',
    }, salaryComponents),
    buildDemoSalaryStructure({
      id: 602,
      previous_version_id: null,
      code: 'OPS-G4',
      name: 'Operations Grade 4',
      currency: 'INR',
      country_code: 'IN',
      pay_frequency: 'monthly',
      grade: 'G4',
      band: 'B1',
      level: 'L1',
      annual_ctc_amount: '1260000.00',
      basic_salary_amount: '420000.00',
      gross_salary_amount: '105000.00',
      net_salary_amount: '83500.00',
      effective_from: '2026-07-01',
      revision_date: '2026-07-01',
      version: 1,
      status: 'draft',
      notes: 'Draft structure for shared services.',
      componentConfigs: [
        { componentId: 501, displayOrder: 1, amount: '35000.00' },
        { componentId: 502, displayOrder: 2, percentageValue: '35.0000', basisCodes: ['BASIC'] },
      ],
      created_at: '2026-06-10T09:00:00+05:30',
      updated_at: '2026-06-10T09:00:00+05:30',
    }, salaryComponents),
  ]

  const employees: PayrollEmployeeOption[] = [
    { id: 2101, employee_code: 'PAY-2101', full_name: 'Aman Verma', email: 'aman.verma@phoenixhrms.test', employment_status: 'active' },
    { id: 2102, employee_code: 'PAY-2102', full_name: 'Nisha Rao', email: 'nisha.rao@phoenixhrms.test', employment_status: 'probation' },
    { id: 2103, employee_code: 'PAY-2103', full_name: 'Kabir Malik', email: 'kabir.malik@phoenixhrms.test', employment_status: 'active' },
  ]

  const compensations: EmployeeCompensationRecord[] = [
    buildDemoCompensationRecord({
      id: 701,
      employee: employees[0],
      salaryStructure: salaryStructures[0],
      annual_ctc_amount: '1980000.00',
      basic_salary_amount: '660000.00',
      gross_salary_amount: '165000.00',
      net_salary_amount: '129000.00',
      revision_reason: 'annual_revision',
      effective_from: '2026-08-01',
      revision_date: '2026-08-01',
      previous_revision_id: 698,
      notes: 'Aligned with FY26 engineering revision.',
    }),
    buildDemoCompensationRecord({
      id: 702,
      employee: employees[1],
      salaryStructure: salaryStructures[1],
      annual_ctc_amount: '1260000.00',
      basic_salary_amount: '420000.00',
      gross_salary_amount: '105000.00',
      net_salary_amount: '83500.00',
      revision_reason: 'initial_assignment',
      effective_from: '2026-07-01',
      revision_date: '2026-07-01',
      previous_revision_id: null,
      notes: 'Probation compensation setup.',
    }),
  ]

  return {
    calendars,
    periods,
    salaryComponents,
    salaryStructures,
    compensations,
    employees,
  }
}

function buildDemoSalaryStructure(
  payload: {
    id: number
    previous_version_id: number | null
    code: string
    name: string
    currency: string
    country_code: string
    pay_frequency: SalaryStructureRecord['pay_frequency']
    grade: string | null
    band: string | null
    level: string | null
    annual_ctc_amount: string
    basic_salary_amount: string
    gross_salary_amount: string
    net_salary_amount: string
    effective_from: string
    revision_date: string
    version: number
    status: SalaryStructureRecord['status']
    notes: string | null
    componentConfigs: Array<{
      componentId: number
      displayOrder: number
      amount?: string | null
      percentageValue?: string | null
      basisCodes?: string[]
      expressionFormula?: string | null
    }>
    created_at: string
    updated_at: string
  },
  salaryComponents: SalaryComponentRecord[],
): SalaryStructureRecord {
  const components: SalaryStructureComponentRecord[] = payload.componentConfigs.map((config, index) => {
    const component = salaryComponents.find((item) => item.id === config.componentId) ?? null

    return {
      id: payload.id * 10 + index + 1,
      salary_component_id: config.componentId,
      salary_component: component,
      display_order: config.displayOrder,
      resolved_formula_inputs: {
        calculation_type: component?.calculation_type ?? null,
        flat_amount: config.amount ?? component?.default_formula_inputs.flat_amount ?? null,
        percentage_value: config.percentageValue ?? component?.default_formula_inputs.percentage_value ?? null,
        percentage_basis_component_codes: config.basisCodes ?? component?.default_formula_inputs.percentage_basis_component_codes ?? [],
        expression_formula: config.expressionFormula ?? component?.default_formula_inputs.expression_formula ?? null,
      },
    }
  })

  return {
    id: payload.id,
    previous_version_id: payload.previous_version_id,
    code: payload.code,
    name: payload.name,
    currency: payload.currency,
    country_code: payload.country_code,
    pay_frequency: payload.pay_frequency,
    grade: payload.grade,
    band: payload.band,
    level: payload.level,
    annual_ctc_amount: payload.annual_ctc_amount,
    basic_salary_amount: payload.basic_salary_amount,
    gross_salary_amount: payload.gross_salary_amount,
    net_salary_amount: payload.net_salary_amount,
    effective_from: payload.effective_from,
    revision_date: payload.revision_date,
    version: payload.version,
    status: payload.status,
    notes: payload.notes,
    components,
    created_at: payload.created_at,
    updated_at: payload.updated_at,
  }
}

function buildDemoCompensationRecord(payload: {
  id: number
  employee: PayrollEmployeeOption
  salaryStructure: SalaryStructureRecord
  previous_revision_id: number | null
  revision_reason: EmployeeCompensationRecord['revision_reason']
  effective_from: string
  revision_date: string
  annual_ctc_amount: string
  basic_salary_amount: string
  gross_salary_amount: string
  net_salary_amount: string
  notes: string | null
}): EmployeeCompensationRecord {
  return {
    id: payload.id,
    employee_id: payload.employee.id,
    employee: {
      id: payload.employee.id,
      employee_code: payload.employee.employee_code,
      full_name: payload.employee.full_name,
      email: payload.employee.email,
    },
    salary_structure_id: payload.salaryStructure.id,
    salary_structure: {
      id: payload.salaryStructure.id,
      code: payload.salaryStructure.code,
      name: payload.salaryStructure.name,
      version: payload.salaryStructure.version,
      currency: payload.salaryStructure.currency,
      pay_frequency: payload.salaryStructure.pay_frequency,
    },
    previous_revision_id: payload.previous_revision_id,
    revision_reason: payload.revision_reason,
    effective_from: payload.effective_from,
    revision_date: payload.revision_date,
    annual_ctc_amount: payload.annual_ctc_amount,
    basic_salary_amount: payload.basic_salary_amount,
    gross_salary_amount: payload.gross_salary_amount,
    net_salary_amount: payload.net_salary_amount,
    notes: payload.notes,
    component_snapshot: payload.salaryStructure.components.map((component) => ({
      salary_component_id: component.salary_component_id,
      code: component.salary_component?.code ?? null,
      name: component.salary_component?.name ?? null,
      category: component.salary_component?.category ?? null,
      display_order: component.display_order,
      resolved_formula_inputs: component.resolved_formula_inputs,
    })),
    created_at: nowIso(),
    updated_at: nowIso(),
  }
}

function applyDemoCalendarUpsert(
  currentData: PayrollSetupWorkspaceData,
  calendarId: number | null,
  payload: Record<string, unknown>,
): PayrollSetupWorkspaceData {
  const calendars = currentData.calendars.map((item) => ({ ...item }))
  const nextRecord: PayrollCalendarRecord = {
    id: calendarId ?? nextNumericId(calendars),
    name: String(payload.name ?? '').trim(),
    frequency: payload.frequency as PayrollCalendarRecord['frequency'],
    timezone: String(payload.timezone ?? 'Asia/Kolkata'),
    payroll_day: parseNullableInteger(payload.payroll_day),
    payroll_weekday: parseNullableInteger(payload.payroll_weekday),
    is_default: Boolean(payload.is_default),
    status: payload.status as PayrollCalendarRecord['status'],
    created_at: calendarId
      ? calendars.find((item) => item.id === calendarId)?.created_at ?? nowIso()
      : nowIso(),
    updated_at: nowIso(),
  }

  const nextCalendars = upsertRecord(calendars, nextRecord, 'name')
    .map((calendar) => ({ ...calendar, is_default: nextRecord.is_default ? calendar.id === nextRecord.id : calendar.is_default }))

  return {
    ...currentData,
    calendars: nextCalendars,
    periods: currentData.periods.map((period) => ({
      ...period,
      payroll_calendar:
        period.payroll_calendar_id === nextRecord.id
          ? nextRecord
          : nextCalendars.find((calendar) => calendar.id === period.payroll_calendar_id) ?? period.payroll_calendar ?? null,
    })),
  }
}

function applyDemoPeriodCreate(
  currentData: PayrollSetupWorkspaceData,
  payload: Record<string, unknown>,
): PayrollSetupWorkspaceData {
  const payrollCalendarId = Number(payload.payroll_calendar_id)
  const calendar = currentData.calendars.find((item) => item.id === payrollCalendarId) ?? null
  const period: PayrollPeriodRecord = {
    id: nextNumericId(currentData.periods),
    payroll_calendar_id: payrollCalendarId,
    payroll_calendar: calendar,
    name: String(payload.name ?? '').trim(),
    frequency: calendar?.frequency ?? 'monthly',
    start_date: String(payload.start_date ?? ''),
    end_date: String(payload.end_date ?? ''),
    payroll_date: String(payload.payroll_date ?? ''),
    status: 'draft',
    opened_at: null,
    prepared_at: null,
    closed_at: null,
    latest_run: null,
    created_at: nowIso(),
    updated_at: nowIso(),
  }

  return {
    ...currentData,
    periods: [period, ...currentData.periods].sort((left, right) => right.start_date.localeCompare(left.start_date)),
  }
}

function applyDemoSalaryComponentUpsert(
  currentData: PayrollSetupWorkspaceData,
  componentId: number | null,
  payload: Record<string, unknown>,
): PayrollSetupWorkspaceData {
  const components = currentData.salaryComponents.map((item) => ({ ...item, default_formula_inputs: { ...item.default_formula_inputs } }))
  const nextRecord: SalaryComponentRecord = {
    id: componentId ?? nextNumericId(components),
    code: String(payload.code ?? '').trim().toUpperCase(),
    name: String(payload.name ?? '').trim(),
    category: payload.category as SalaryComponentRecord['category'],
    calculation_type: payload.calculation_type as SalaryComponentRecord['calculation_type'],
    default_formula_inputs: {
      flat_amount: normalizeMoneyString(payload.flat_amount),
      percentage_value: normalizeDecimalString(payload.percentage_value, 4),
      percentage_basis_component_codes: normalizeCodeList(payload.percentage_basis_component_codes),
      expression_formula: nullableString(payload.expression_formula),
    },
    is_taxable: Boolean(payload.is_taxable),
    is_proratable: Boolean(payload.is_proratable),
    display_order: Number(payload.display_order ?? 0),
    status: payload.status as SalaryComponentRecord['status'],
    created_at: componentId
      ? components.find((item) => item.id === componentId)?.created_at ?? nowIso()
      : nowIso(),
    updated_at: nowIso(),
  }

  return {
    ...currentData,
    salaryComponents: upsertRecord(components, nextRecord, 'display_order'),
  }
}

function applyDemoSalaryStructureUpsert(
  currentData: PayrollSetupWorkspaceData,
  structureId: number | null,
  payload: Record<string, unknown>,
): PayrollSetupWorkspaceData {
  const nextId = nextNumericId(currentData.salaryStructures)
  const baseStructure = structureId
    ? currentData.salaryStructures.find((item) => item.id === structureId) ?? null
    : null
  const version = baseStructure ? baseStructure.version + 1 : 1
  const componentPayload = Array.isArray(payload.components) ? payload.components as Array<Record<string, unknown>> : []

  const nextStructure = buildDemoSalaryStructure({
    id: structureId ? nextId : nextId,
    previous_version_id: baseStructure?.id ?? null,
    code: String(payload.code ?? '').trim().toUpperCase(),
    name: String(payload.name ?? '').trim(),
    currency: String(payload.currency ?? 'INR').trim().toUpperCase(),
    country_code: String(payload.country_code ?? 'IN').trim().toUpperCase(),
    pay_frequency: payload.pay_frequency as SalaryStructureRecord['pay_frequency'],
    grade: nullableString(payload.grade),
    band: nullableString(payload.band),
    level: nullableString(payload.level),
    annual_ctc_amount: normalizeMoneyString(payload.annual_ctc_amount) ?? '0.00',
    basic_salary_amount: normalizeMoneyString(payload.basic_salary_amount) ?? '0.00',
    gross_salary_amount: normalizeMoneyString(payload.gross_salary_amount) ?? '0.00',
    net_salary_amount: normalizeMoneyString(payload.net_salary_amount) ?? '0.00',
    effective_from: String(payload.effective_from ?? ''),
    revision_date: String(payload.revision_date ?? ''),
    version,
    status: payload.status as SalaryStructureRecord['status'],
    notes: nullableString(payload.notes),
    componentConfigs: componentPayload.map((component, index) => ({
      componentId: Number(component.salary_component_id),
      displayOrder: Number(component.display_order ?? index + 1),
      amount: normalizeMoneyString(component.configured_amount),
      percentageValue: normalizeDecimalString(component.configured_percentage, 4),
      basisCodes: normalizeCodeList(component.configured_basis_component_codes),
      expressionFormula: nullableString(component.configured_expression_formula),
    })),
    created_at: nowIso(),
    updated_at: nowIso(),
  }, currentData.salaryComponents)

  return {
    ...currentData,
    salaryStructures: [
      ...currentData.salaryStructures.map((structure) =>
        baseStructure && structure.id === baseStructure.id
          ? { ...structure, status: 'superseded' as const }
          : structure,
      ),
      nextStructure,
    ].sort((left, right) => `${right.code}-${right.version}`.localeCompare(`${left.code}-${left.version}`)),
  }
}

function applyDemoCompensationAssignment(
  currentData: PayrollSetupWorkspaceData,
  payload: Record<string, unknown>,
): PayrollSetupWorkspaceData {
  const employeeId = Number(payload.employee_id)
  const employee = currentData.employees.find((item) => item.id === employeeId)

  if (!employee) {
    return currentData
  }

  const structure = currentData.salaryStructures.find((item) => item.id === Number(payload.salary_structure_id))

  if (!structure) {
    return currentData
  }

  const previousAssignment = currentData.compensations.find((item) => item.employee_id === employeeId) ?? null
  const nextAssignment = buildDemoCompensationRecord({
    id: nextNumericId(currentData.compensations),
    employee,
    salaryStructure: structure,
    previous_revision_id: previousAssignment?.id ?? null,
    revision_reason: payload.revision_reason as EmployeeCompensationRecord['revision_reason'],
    effective_from: String(payload.effective_from ?? ''),
    revision_date: String(payload.revision_date ?? ''),
    annual_ctc_amount: structure.annual_ctc_amount,
    basic_salary_amount: structure.basic_salary_amount,
    gross_salary_amount: structure.gross_salary_amount,
    net_salary_amount: structure.net_salary_amount,
    notes: nullableString(payload.notes),
  })

  return {
    ...currentData,
    compensations: [
      ...currentData.compensations.filter((item) => item.employee_id !== employeeId),
      nextAssignment,
    ].sort((left, right) => left.employee?.full_name.localeCompare(right.employee?.full_name ?? '') ?? 0),
  }
}

function upsertRecord<T extends { id: number }>(
  records: T[],
  nextRecord: T,
  sortBy: 'name' | 'display_order',
) {
  const nextRecords = [...records.filter((item) => item.id !== nextRecord.id), nextRecord]

  if (sortBy === 'display_order') {
    return nextRecords.sort((left, right) => {
      const leftOrder = Number((left as { display_order?: number }).display_order ?? 0)
      const rightOrder = Number((right as { display_order?: number }).display_order ?? 0)

      if (leftOrder !== rightOrder) {
        return leftOrder - rightOrder
      }

      return String((left as { name?: string }).name ?? '').localeCompare(String((right as { name?: string }).name ?? ''))
    })
  }

  return nextRecords.sort((left, right) =>
    String((left as { name?: string }).name ?? '').localeCompare(String((right as { name?: string }).name ?? '')),
  )
}

function nextNumericId(records: Array<{ id: number }>) {
  return Math.max(0, ...records.map((record) => record.id)) + 1
}

function nullableString(value: unknown) {
  const text = String(value ?? '').trim()
  return text ? text : null
}

function parseNullableInteger(value: unknown) {
  if (value === null || value === undefined || value === '') {
    return null
  }

  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : null
}

function normalizeCodeList(value: unknown) {
  if (Array.isArray(value)) {
    return value
      .map((item) => String(item).trim().toUpperCase())
      .filter(Boolean)
  }

  const text = String(value ?? '').trim()
  return text
    ? text.split(',').map((item) => item.trim().toUpperCase()).filter(Boolean)
    : []
}

function normalizeMoneyString(value: unknown) {
  const text = String(value ?? '').trim()

  if (!text) {
    return null
  }

  return Number(text).toFixed(2)
}

function normalizeDecimalString(value: unknown, precision: number) {
  const text = String(value ?? '').trim()

  if (!text) {
    return null
  }

  return Number(text).toFixed(precision)
}

function nowIso() {
  return '2026-06-09T16:45:00+05:30'
}
