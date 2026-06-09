import type { EmployeeReference, PaginationMeta } from '../employees/types'

export type PayrollPeriodStatus = 'draft' | 'open' | 'prepared' | 'closed'
export type PayrollRunStatus = 'ready' | 'blocked' | 'calculated' | 'failed' | 'approved' | 'locked'
export type PayrollCheckStatus = 'passed' | 'warning' | 'blocked'

export interface PayrollCalendarRecord {
  id: number
  name: string
  frequency: 'monthly' | 'weekly' | 'biweekly' | 'semi_monthly' | 'custom'
  timezone: string
  payroll_day: number | null
  payroll_weekday: number | null
  is_default: boolean
  status: 'active' | 'inactive'
  created_at: string | null
  updated_at: string | null
}

export interface PayrollCheck {
  code: string
  title: string
  status: PayrollCheckStatus
  detail: string
}

export interface PayrollPrerequisiteSummary {
  ready_for_calculation: boolean
  blocking_count: number
  warning_count: number
  passed_count: number
}

export interface PayrollPrerequisiteSnapshot {
  checks: PayrollCheck[]
  summary: PayrollPrerequisiteSummary
}

export interface PayrollInputSummary {
  employee_count: number
  input_count: number
  manual_adjustment_count: number
  attendance_record_count: number
  approved_leave_request_count: number
  total_worked_minutes: number
  total_overtime_minutes: number
  total_lop_days: number
  total_paid_leave_days: number
  total_unpaid_leave_days: number
  total_manual_adjustment_amount: number
}

export interface PayrollCalculationSummary {
  employee_count: number
  item_count: number
  error_count: number
  gross_salary_total: number
  total_earnings: number
  total_deductions: number
  net_salary_total: number
  employer_cost_total: number
  total_lop_days: number
  total_unpaid_days: number
  total_overtime_earnings: number
}

export interface PayrollItemRecord {
  id: number
  employee_id: number
  employee: EmployeeReference | null
  employee_compensation_id: number | null
  status: 'calculated' | 'error'
  employment_days: string
  unpaid_days: string
  lop_days: string
  overtime_minutes: number
  overtime_earnings: string
  gross_salary: string
  total_earnings: string
  total_deductions: string
  net_salary: string
  employer_cost: string
  validation_errors: string[]
  created_at: string | null
  updated_at: string | null
}

export interface PayrollRunRecord {
  id: number
  payroll_period_id: number
  name: string
  frequency: string
  start_date: string
  end_date: string
  status: PayrollRunStatus
  prerequisite_summary: PayrollPrerequisiteSummary
  prerequisite_snapshot: PayrollPrerequisiteSnapshot
  input_summary: Partial<PayrollInputSummary>
  calculation_summary: Partial<PayrollCalculationSummary>
  items: PayrollItemRecord[]
  prepared_at: string | null
  inputs_generated_at: string | null
  calculated_at: string | null
  approved_at: string | null
  locked_at: string | null
  reopened_at: string | null
  closed_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface PayrollPeriodRecord {
  id: number
  payroll_calendar_id: number
  payroll_calendar?: PayrollCalendarRecord | null
  name: string
  frequency: string
  start_date: string
  end_date: string
  payroll_date: string
  status: PayrollPeriodStatus
  opened_at: string | null
  prepared_at: string | null
  closed_at: string | null
  latest_run: PayrollRunRecord | null
  created_at: string | null
  updated_at: string | null
}

export interface SalaryComponentFormulaInputs {
  flat_amount: string | null
  percentage_value: string | null
  percentage_basis_component_codes: string[]
  expression_formula: string | null
}

export interface SalaryComponentRecord {
  id: number
  code: string
  name: string
  category: 'earning' | 'deduction' | 'employer_contribution'
  calculation_type: 'fixed' | 'percentage' | 'expression'
  default_formula_inputs: SalaryComponentFormulaInputs
  is_taxable: boolean
  is_proratable: boolean
  display_order: number
  status: 'active' | 'inactive'
  created_at: string | null
  updated_at: string | null
}

export interface SalaryStructureResolvedFormulaInputs {
  calculation_type: string | null
  flat_amount: string | null
  percentage_value: string | null
  percentage_basis_component_codes: string[]
  expression_formula: string | null
}

export interface SalaryStructureComponentRecord {
  id: number
  salary_component_id: number
  salary_component: SalaryComponentRecord | null
  display_order: number
  resolved_formula_inputs: SalaryStructureResolvedFormulaInputs
}

export interface SalaryStructureRecord {
  id: number
  previous_version_id: number | null
  code: string
  name: string | null
  currency: string
  country_code: string
  pay_frequency: 'monthly' | 'weekly' | 'biweekly' | 'semi_monthly' | 'custom'
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
  status: 'draft' | 'active' | 'inactive' | 'superseded'
  notes: string | null
  components: SalaryStructureComponentRecord[]
  created_at: string | null
  updated_at: string | null
}

export interface PayrollEmployeeOption {
  id: number
  employee_code: string
  full_name: string
  email: string
  employment_status: string
}

export interface PayslipRecord {
  id: number
  payroll_run_id: number
  payroll_period_id: number
  payroll_item_id: number | null
  employee_id: number
  employee: EmployeeReference | null
  slip_number: string
  status: 'generated'
  currency: string
  start_date: string
  end_date: string
  payroll_date: string
  file_name: string
  gross_salary: string
  total_earnings: string
  total_deductions: string
  net_salary: string
  employer_cost: string
  earnings_breakdown?: Array<{
    code?: string | null
    name?: string | null
    base_amount?: string | number | null
    prorated_amount?: string | number | null
  }>
  deductions_breakdown?: Array<{
    code?: string | null
    name?: string | null
    base_amount?: string | number | null
    prorated_amount?: string | number | null
  }>
  employer_contribution_breakdown?: Array<{
    code?: string | null
    name?: string | null
    prorated_amount?: string | number | null
  }>
  company_snapshot?: {
    name?: string | null
    currency?: string | null
    timezone?: string | null
  }
  rendered_format?: string | null
  generated_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface CompensationComponentSnapshot {
  salary_component_id: number | null
  code: string | null
  name: string | null
  category: string | null
  display_order: number
  resolved_formula_inputs: {
    calculation_type: string | null
    flat_amount: string | number | null
    percentage_value: string | number | null
    percentage_basis_component_codes: string[]
    expression_formula: string | null
  }
}

export interface EmployeeCompensationRecord {
  id: number
  employee_id: number
  employee: EmployeeReference | null
  salary_structure_id: number
  salary_structure: {
    id: number
    code: string
    name: string | null
    version: number
    currency: string
    pay_frequency: string
  }
  previous_revision_id: number | null
  revision_reason: string
  effective_from: string
  revision_date: string
  annual_ctc_amount: string
  basic_salary_amount: string
  gross_salary_amount: string
  net_salary_amount: string
  notes: string | null
  component_snapshot: CompensationComponentSnapshot[]
  created_at: string | null
  updated_at: string | null
}

export interface EmployeeCompensationDetail {
  employee: EmployeeReference & {
    employment_status: string
  }
  current_assignment: EmployeeCompensationRecord | null
  history: EmployeeCompensationRecord[]
}

export interface PayrollAdjustmentRecord {
  id: number
  payroll_run_id: number
  employee_id: number
  employee: EmployeeReference | null
  adjustment_code: string
  name: string
  category: 'earning' | 'deduction' | 'reimbursement' | 'bonus' | 'custom'
  amount: string
  effective_date: string
  status: 'active' | 'cancelled'
  notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface PayrollWorkspaceData {
  periods: PayrollPeriodRecord[]
  runs: PayrollRunRecord[]
  payslips: PayslipRecord[]
}

export interface PayrollSetupWorkspaceData {
  calendars: PayrollCalendarRecord[]
  periods: PayrollPeriodRecord[]
  salaryComponents: SalaryComponentRecord[]
  salaryStructures: SalaryStructureRecord[]
  compensations: EmployeeCompensationRecord[]
  employees: PayrollEmployeeOption[]
}

export interface PaginatedPayrollPeriods {
  items: PayrollPeriodRecord[]
  meta: PaginationMeta
}

export interface PaginatedPayrollRuns {
  items: PayrollRunRecord[]
  meta: PaginationMeta
}

export interface PaginatedPayslips {
  items: PayslipRecord[]
  meta: PaginationMeta
}

export interface PaginatedPayrollAdjustments {
  items: PayrollAdjustmentRecord[]
  meta: PaginationMeta
}
