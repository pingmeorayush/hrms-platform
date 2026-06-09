import type { EmployeeReference } from '../../employees/types'
import type {
  EmployeeCompensationDetail,
  EmployeeCompensationRecord,
  PayrollCalculationSummary,
  PayrollCheck,
  PayrollItemRecord,
  PayrollPeriodRecord,
  PayrollRunRecord,
  PayrollWorkspaceData,
  PayslipRecord,
} from '../types'

const amanVerma: EmployeeReference = {
  id: 2101,
  employee_code: 'PAY-2101',
  full_name: 'Aman Verma',
  email: 'aman.verma@phoenixhrms.test',
}

const nishaRao: EmployeeReference = {
  id: 2102,
  employee_code: 'PAY-2102',
  full_name: 'Nisha Rao',
  email: 'nisha.rao@phoenixhrms.test',
}

const kabirMalik: EmployeeReference = {
  id: 2103,
  employee_code: 'PAY-2103',
  full_name: 'Kabir Malik',
  email: 'kabir.malik@phoenixhrms.test',
}

const payrollEmployeeIdByUserId: Record<number, number> = {
  1: amanVerma.id,
  2: nishaRao.id,
  3: amanVerma.id,
  4: kabirMalik.id,
}

function todayIso() {
  return '2026-06-08T10:30:00+05:30'
}

function currency(value: number) {
  return value.toFixed(2)
}

function compensationRecord(
  id: number,
  employee: EmployeeReference,
  values: Partial<EmployeeCompensationRecord>,
): EmployeeCompensationRecord {
  return {
    id,
    employee_id: employee.id,
    employee,
    salary_structure_id: 0,
    salary_structure: {
      id: 0,
      code: '',
      name: null,
      version: 1,
      currency: 'INR',
      pay_frequency: 'monthly',
    },
    previous_revision_id: null,
    revision_reason: 'Annual review',
    effective_from: '2026-04-01',
    revision_date: '2026-04-01',
    annual_ctc_amount: currency(0),
    basic_salary_amount: currency(0),
    gross_salary_amount: currency(0),
    net_salary_amount: currency(0),
    notes: null,
    component_snapshot: [],
    created_at: '2026-04-01T09:00:00+05:30',
    updated_at: '2026-04-01T09:00:00+05:30',
    ...values,
  }
}

function makeCalculationSummary(values: Partial<PayrollCalculationSummary>): Partial<PayrollCalculationSummary> {
  return {
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
    ...values,
  }
}

function makeBlockedChecks(): PayrollCheck[] {
  return [
    {
      code: 'attendance_finalization',
      title: 'Attendance finalization',
      status: 'blocked',
      detail: 'Four employee attendance records are still incomplete for the period.',
    },
    {
      code: 'pending_leave',
      title: 'Pending leave requests',
      status: 'blocked',
      detail: 'Two pending leave requests overlap the payroll period and could change LOP.',
    },
    {
      code: 'manual_adjustments',
      title: 'Manual adjustments',
      status: 'warning',
      detail: 'Three draft manual adjustments are still waiting for payroll review.',
    },
    {
      code: 'compensation_coverage',
      title: 'Compensation coverage',
      status: 'passed',
      detail: 'All active employees have an effective compensation assignment for this period.',
    },
  ]
}

function makeFailedItems(): PayrollItemRecord[] {
  return [
    {
      id: 7301,
      employee_id: amanVerma.id,
      employee: amanVerma,
      employee_compensation_id: 9101,
      status: 'calculated',
      employment_days: '31.00',
      unpaid_days: '0.00',
      lop_days: '0.00',
      overtime_minutes: 90,
      overtime_earnings: currency(703.12),
      gross_salary: currency(62000),
      total_earnings: currency(62703.12),
      total_deductions: currency(3720),
      net_salary: currency(58983.12),
      employer_cost: currency(64683.12),
      validation_errors: [],
      created_at: todayIso(),
      updated_at: todayIso(),
    },
    {
      id: 7302,
      employee_id: nishaRao.id,
      employee: nishaRao,
      employee_compensation_id: 9102,
      status: 'error',
      employment_days: '31.00',
      unpaid_days: '4.00',
      lop_days: '1.00',
      overtime_minutes: 0,
      overtime_earnings: currency(0),
      gross_salary: currency(54000),
      total_earnings: currency(54000),
      total_deductions: currency(56100),
      net_salary: currency(-2100),
      employer_cost: currency(55800),
      validation_errors: ['Net salary cannot be negative. Review reimbursement reversal and unpaid days.'],
      created_at: todayIso(),
      updated_at: todayIso(),
    },
  ]
}

function makeLockedItems(): PayrollItemRecord[] {
  return [
    {
      id: 7401,
      employee_id: amanVerma.id,
      employee: amanVerma,
      employee_compensation_id: 9101,
      status: 'calculated',
      employment_days: '31.00',
      unpaid_days: '0.00',
      lop_days: '0.00',
      overtime_minutes: 30,
      overtime_earnings: currency(234.38),
      gross_salary: currency(62000),
      total_earnings: currency(62234.38),
      total_deductions: currency(3720),
      net_salary: currency(58514.38),
      employer_cost: currency(64214.38),
      validation_errors: [],
      created_at: '2026-06-03T09:30:00+05:30',
      updated_at: '2026-06-03T09:30:00+05:30',
    },
    {
      id: 7402,
      employee_id: kabirMalik.id,
      employee: kabirMalik,
      employee_compensation_id: 9103,
      status: 'calculated',
      employment_days: '31.00',
      unpaid_days: '0.50',
      lop_days: '0.50',
      overtime_minutes: 0,
      overtime_earnings: currency(0),
      gross_salary: currency(51000),
      total_earnings: currency(51000),
      total_deductions: currency(3187.50),
      net_salary: currency(47812.50),
      employer_cost: currency(52740),
      validation_errors: [],
      created_at: '2026-06-03T09:30:00+05:30',
      updated_at: '2026-06-03T09:30:00+05:30',
    },
  ]
}

function createBlockedRun(): PayrollRunRecord {
  return {
    id: 6201,
    payroll_period_id: 8202,
    name: 'June 2026 Preparation Run',
    frequency: 'monthly',
    start_date: '2026-06-01',
    end_date: '2026-06-30',
    status: 'blocked',
    prerequisite_summary: {
      ready_for_calculation: false,
      blocking_count: 2,
      warning_count: 1,
      passed_count: 1,
    },
    prerequisite_snapshot: {
      checks: makeBlockedChecks(),
      summary: {
        ready_for_calculation: false,
        blocking_count: 2,
        warning_count: 1,
        passed_count: 1,
      },
    },
    input_summary: {
      employee_count: 31,
      input_count: 138,
      manual_adjustment_count: 3,
      attendance_record_count: 84,
      approved_leave_request_count: 12,
      total_worked_minutes: 25740,
      total_overtime_minutes: 390,
      total_lop_days: 3.5,
      total_paid_leave_days: 9,
      total_unpaid_leave_days: 1.5,
      total_manual_adjustment_amount: 18450,
    },
    calculation_summary: {},
    items: [],
    prepared_at: '2026-06-02T11:00:00+05:30',
    inputs_generated_at: '2026-06-02T11:00:00+05:30',
    calculated_at: null,
    approved_at: null,
    locked_at: null,
    reopened_at: null,
    closed_at: null,
    created_at: '2026-06-02T11:00:00+05:30',
    updated_at: '2026-06-02T11:00:00+05:30',
  }
}

function createFailedRun(): PayrollRunRecord {
  return {
    id: 6202,
    payroll_period_id: 8203,
    name: 'July 2026 Preparation Run',
    frequency: 'monthly',
    start_date: '2026-07-01',
    end_date: '2026-07-31',
    status: 'failed',
    prerequisite_summary: {
      ready_for_calculation: true,
      blocking_count: 0,
      warning_count: 1,
      passed_count: 3,
    },
    prerequisite_snapshot: {
      checks: [
        {
          code: 'attendance_finalization',
          title: 'Attendance finalization',
          status: 'passed',
          detail: 'Attendance for the full payroll window is finalized.',
        },
        {
          code: 'compensation_coverage',
          title: 'Compensation coverage',
          status: 'passed',
          detail: 'All employees have payroll-ready compensation assignments.',
        },
        {
          code: 'adjustment_review',
          title: 'Adjustment review',
          status: 'warning',
          detail: 'One reimbursement reversal created an exception on recalculation.',
        },
      ],
      summary: {
        ready_for_calculation: true,
        blocking_count: 0,
        warning_count: 1,
        passed_count: 3,
      },
    },
    input_summary: {
      employee_count: 24,
      input_count: 112,
      manual_adjustment_count: 2,
      attendance_record_count: 76,
      approved_leave_request_count: 7,
      total_worked_minutes: 21980,
      total_overtime_minutes: 120,
      total_lop_days: 1,
      total_paid_leave_days: 4,
      total_unpaid_leave_days: 0.5,
      total_manual_adjustment_amount: -1200,
    },
    calculation_summary: makeCalculationSummary({
      employee_count: 2,
      item_count: 2,
      error_count: 1,
      gross_salary_total: 116000,
      total_earnings: 116703.12,
      total_deductions: 59820,
      net_salary_total: 56883.12,
      employer_cost_total: 120483.12,
      total_lop_days: 1,
      total_unpaid_days: 4,
      total_overtime_earnings: 703.12,
    }),
    items: makeFailedItems(),
    prepared_at: '2026-07-31T18:00:00+05:30',
    inputs_generated_at: '2026-07-31T18:00:00+05:30',
    calculated_at: '2026-08-01T09:10:00+05:30',
    approved_at: null,
    locked_at: null,
    reopened_at: null,
    closed_at: null,
    created_at: '2026-07-31T18:00:00+05:30',
    updated_at: '2026-08-01T09:10:00+05:30',
  }
}

function createReadyRun(): PayrollRunRecord {
  return {
    id: 6203,
    payroll_period_id: 8204,
    name: 'August 2026 Preparation Run',
    frequency: 'monthly',
    start_date: '2026-08-01',
    end_date: '2026-08-31',
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
        {
          code: 'leave_finalization',
          title: 'Leave finalization',
          status: 'passed',
          detail: 'No pending leave requests overlap the period.',
        },
        {
          code: 'manual_adjustments',
          title: 'Manual adjustments',
          status: 'passed',
          detail: 'Manual adjustment review is complete for the current run.',
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
      employee_count: 26,
      input_count: 126,
      manual_adjustment_count: 1,
      attendance_record_count: 82,
      approved_leave_request_count: 6,
      total_worked_minutes: 23480,
      total_overtime_minutes: 180,
      total_lop_days: 0.5,
      total_paid_leave_days: 6,
      total_unpaid_leave_days: 0,
      total_manual_adjustment_amount: 4500,
    },
    calculation_summary: {},
    items: [],
    prepared_at: '2026-08-31T18:15:00+05:30',
    inputs_generated_at: '2026-08-31T18:15:00+05:30',
    calculated_at: null,
    approved_at: null,
    locked_at: null,
    reopened_at: null,
    closed_at: null,
    created_at: '2026-08-31T18:15:00+05:30',
    updated_at: '2026-08-31T18:15:00+05:30',
  }
}

function createLockedRun(): PayrollRunRecord {
  return {
    id: 6204,
    payroll_period_id: 8201,
    name: 'May 2026 Preparation Run',
    frequency: 'monthly',
    start_date: '2026-05-01',
    end_date: '2026-05-31',
    status: 'locked',
    prerequisite_summary: {
      ready_for_calculation: true,
      blocking_count: 0,
      warning_count: 0,
      passed_count: 4,
    },
    prerequisite_snapshot: {
      checks: [],
      summary: {
        ready_for_calculation: true,
        blocking_count: 0,
        warning_count: 0,
        passed_count: 4,
      },
    },
    input_summary: {
      employee_count: 18,
      input_count: 92,
      manual_adjustment_count: 0,
      attendance_record_count: 61,
      approved_leave_request_count: 4,
      total_worked_minutes: 16820,
      total_overtime_minutes: 30,
      total_lop_days: 0.5,
      total_paid_leave_days: 3,
      total_unpaid_leave_days: 0,
      total_manual_adjustment_amount: 0,
    },
    calculation_summary: makeCalculationSummary({
      employee_count: 2,
      item_count: 2,
      error_count: 0,
      gross_salary_total: 113000,
      total_earnings: 113234.38,
      total_deductions: 6907.5,
      net_salary_total: 106326.88,
      employer_cost_total: 116954.38,
      total_lop_days: 0.5,
      total_unpaid_days: 0.5,
      total_overtime_earnings: 234.38,
    }),
    items: makeLockedItems(),
    prepared_at: '2026-05-31T18:15:00+05:30',
    inputs_generated_at: '2026-05-31T18:15:00+05:30',
    calculated_at: '2026-06-01T09:00:00+05:30',
    approved_at: '2026-06-01T09:35:00+05:30',
    locked_at: '2026-06-01T10:00:00+05:30',
    reopened_at: null,
    closed_at: '2026-06-01T10:20:00+05:30',
    created_at: '2026-05-31T18:15:00+05:30',
    updated_at: '2026-06-01T10:20:00+05:30',
  }
}

function createPeriods(runsByPeriodId: Map<number, PayrollRunRecord>): PayrollPeriodRecord[] {
  const periods: PayrollPeriodRecord[] = [
    {
      id: 8201,
      payroll_calendar_id: 5101,
      name: 'May 2026 Payroll',
      frequency: 'monthly',
      start_date: '2026-05-01',
      end_date: '2026-05-31',
      payroll_date: '2026-05-31',
      status: 'closed',
      opened_at: '2026-05-01T09:00:00+05:30',
      prepared_at: '2026-05-31T18:15:00+05:30',
      closed_at: '2026-06-01T10:20:00+05:30',
      latest_run: runsByPeriodId.get(8201) ?? null,
      created_at: '2026-04-28T11:00:00+05:30',
      updated_at: '2026-06-01T10:20:00+05:30',
    },
    {
      id: 8202,
      payroll_calendar_id: 5101,
      name: 'June 2026 Payroll',
      frequency: 'monthly',
      start_date: '2026-06-01',
      end_date: '2026-06-30',
      payroll_date: '2026-06-30',
      status: 'prepared',
      opened_at: '2026-06-01T09:00:00+05:30',
      prepared_at: '2026-06-30T18:15:00+05:30',
      closed_at: null,
      latest_run: runsByPeriodId.get(8202) ?? null,
      created_at: '2026-05-28T11:00:00+05:30',
      updated_at: '2026-06-30T18:15:00+05:30',
    },
    {
      id: 8203,
      payroll_calendar_id: 5101,
      name: 'July 2026 Payroll',
      frequency: 'monthly',
      start_date: '2026-07-01',
      end_date: '2026-07-31',
      payroll_date: '2026-07-31',
      status: 'prepared',
      opened_at: '2026-07-01T09:00:00+05:30',
      prepared_at: '2026-07-31T18:15:00+05:30',
      closed_at: null,
      latest_run: runsByPeriodId.get(8203) ?? null,
      created_at: '2026-06-28T11:00:00+05:30',
      updated_at: '2026-08-01T09:10:00+05:30',
    },
    {
      id: 8204,
      payroll_calendar_id: 5101,
      name: 'August 2026 Payroll',
      frequency: 'monthly',
      start_date: '2026-08-01',
      end_date: '2026-08-31',
      payroll_date: '2026-08-31',
      status: 'prepared',
      opened_at: '2026-08-01T09:00:00+05:30',
      prepared_at: '2026-08-31T18:15:00+05:30',
      closed_at: null,
      latest_run: runsByPeriodId.get(8204) ?? null,
      created_at: '2026-07-28T11:00:00+05:30',
      updated_at: '2026-08-31T18:15:00+05:30',
    },
    {
      id: 8205,
      payroll_calendar_id: 5101,
      name: 'September 2026 Payroll',
      frequency: 'monthly',
      start_date: '2026-09-01',
      end_date: '2026-09-30',
      payroll_date: '2026-09-30',
      status: 'draft',
      opened_at: null,
      prepared_at: null,
      closed_at: null,
      latest_run: null,
      created_at: '2026-08-28T11:00:00+05:30',
      updated_at: '2026-08-28T11:00:00+05:30',
    },
  ]

  return periods
}

function createPayslips(): PayslipRecord[] {
  return [
    {
      id: 91001,
      payroll_run_id: 6204,
      payroll_period_id: 8201,
      payroll_item_id: 7401,
      employee_id: amanVerma.id,
      employee: amanVerma,
      slip_number: 'PSL-6204-PAY-2101',
      status: 'generated',
      currency: 'INR',
      start_date: '2026-05-01',
      end_date: '2026-05-31',
      payroll_date: '2026-05-31',
      file_name: 'pay-2101-20260501-20260531-payslip.html',
      gross_salary: currency(62000),
      total_earnings: currency(62234.38),
      total_deductions: currency(3720),
      net_salary: currency(58514.38),
      employer_cost: currency(64214.38),
      earnings_breakdown: [
        { code: 'BASIC', name: 'Basic salary', base_amount: currency(20666.67), prorated_amount: currency(20666.67) },
        { code: 'HRA', name: 'House rent allowance', base_amount: currency(8266.67), prorated_amount: currency(8266.67) },
        { code: 'SPECIAL', name: 'Special allowance', base_amount: currency(33066.66), prorated_amount: currency(33066.66) },
        { code: 'OT', name: 'Overtime earnings', base_amount: currency(234.38), prorated_amount: currency(234.38) },
      ],
      deductions_breakdown: [
        { code: 'PF', name: 'Provident fund', base_amount: currency(1800), prorated_amount: currency(1800) },
        { code: 'PT', name: 'Professional tax', base_amount: currency(200), prorated_amount: currency(200) },
        { code: 'TDS', name: 'Income tax withholding', base_amount: currency(1720), prorated_amount: currency(1720) },
      ],
      employer_contribution_breakdown: [
        { code: 'EPF_ER', name: 'Employer PF contribution', prorated_amount: currency(1800) },
        { code: 'INS', name: 'Insurance contribution', prorated_amount: currency(180) },
      ],
      company_snapshot: {
        name: 'Phoenix People Ops',
        currency: 'INR',
        timezone: 'Asia/Kolkata',
      },
      rendered_format: 'html',
      generated_at: '2026-06-01T10:12:00+05:30',
      created_at: '2026-06-01T10:12:00+05:30',
      updated_at: '2026-06-01T10:12:00+05:30',
    },
    {
      id: 91002,
      payroll_run_id: 6204,
      payroll_period_id: 8201,
      payroll_item_id: 7402,
      employee_id: kabirMalik.id,
      employee: kabirMalik,
      slip_number: 'PSL-6204-PAY-2103',
      status: 'generated',
      currency: 'INR',
      start_date: '2026-05-01',
      end_date: '2026-05-31',
      payroll_date: '2026-05-31',
      file_name: 'pay-2103-20260501-20260531-payslip.html',
      gross_salary: currency(51000),
      total_earnings: currency(51000),
      total_deductions: currency(3187.50),
      net_salary: currency(47812.50),
      employer_cost: currency(52740),
      earnings_breakdown: [
        { code: 'BASIC', name: 'Basic salary', base_amount: currency(17000), prorated_amount: currency(17000) },
        { code: 'HRA', name: 'House rent allowance', base_amount: currency(6800), prorated_amount: currency(6800) },
        { code: 'SPECIAL', name: 'Special allowance', base_amount: currency(27200), prorated_amount: currency(27200) },
      ],
      deductions_breakdown: [
        { code: 'PF', name: 'Provident fund', base_amount: currency(1530), prorated_amount: currency(1530) },
        { code: 'PT', name: 'Professional tax', base_amount: currency(200), prorated_amount: currency(200) },
        { code: 'TDS', name: 'Income tax withholding', base_amount: currency(1457.5), prorated_amount: currency(1457.5) },
      ],
      employer_contribution_breakdown: [
        { code: 'EPF_ER', name: 'Employer PF contribution', prorated_amount: currency(1530) },
        { code: 'INS', name: 'Insurance contribution', prorated_amount: currency(210) },
      ],
      company_snapshot: {
        name: 'Phoenix People Ops',
        currency: 'INR',
        timezone: 'Asia/Kolkata',
      },
      rendered_format: 'html',
      generated_at: '2026-06-01T10:12:00+05:30',
      created_at: '2026-06-01T10:12:00+05:30',
      updated_at: '2026-06-01T10:12:00+05:30',
    },
  ]
}

function buildCompensationHistory(): Record<number, EmployeeCompensationRecord[]> {
  return {
    [amanVerma.id]: [
      compensationRecord(9302, amanVerma, {
        salary_structure_id: 8102,
        salary_structure: {
          id: 8102,
          code: 'ENG-L6-2026',
          name: 'Engineering L6',
          version: 2,
          currency: 'INR',
          pay_frequency: 'monthly',
        },
        previous_revision_id: 9301,
        revision_reason: 'Merit increase after annual cycle',
        effective_from: '2026-04-01',
        revision_date: '2026-04-01',
        annual_ctc_amount: currency(744000),
        basic_salary_amount: currency(248000),
        gross_salary_amount: currency(62000),
        net_salary_amount: currency(58514.38),
        notes: 'Includes current retention allowance.',
        component_snapshot: [
          {
            salary_component_id: 201,
            code: 'BASIC',
            name: 'Basic salary',
            category: 'earning',
            display_order: 1,
            resolved_formula_inputs: {
              calculation_type: 'flat_amount',
              flat_amount: currency(20666.67),
              percentage_value: null,
              percentage_basis_component_codes: [],
              expression_formula: null,
            },
          },
          {
            salary_component_id: 202,
            code: 'HRA',
            name: 'House rent allowance',
            category: 'earning',
            display_order: 2,
            resolved_formula_inputs: {
              calculation_type: 'percentage',
              flat_amount: null,
              percentage_value: 40,
              percentage_basis_component_codes: ['BASIC'],
              expression_formula: null,
            },
          },
          {
            salary_component_id: 206,
            code: 'SPECIAL',
            name: 'Special allowance',
            category: 'earning',
            display_order: 3,
            resolved_formula_inputs: {
              calculation_type: 'flat_amount',
              flat_amount: currency(33066.67),
              percentage_value: null,
              percentage_basis_component_codes: [],
              expression_formula: null,
            },
          },
        ],
      }),
      compensationRecord(9301, amanVerma, {
        salary_structure_id: 8101,
        salary_structure: {
          id: 8101,
          code: 'ENG-L6-2025',
          name: 'Engineering L6',
          version: 1,
          currency: 'INR',
          pay_frequency: 'monthly',
        },
        revision_reason: 'Base structure rollout',
        effective_from: '2025-08-01',
        revision_date: '2025-08-01',
        annual_ctc_amount: currency(708000),
        basic_salary_amount: currency(236000),
        gross_salary_amount: currency(59000),
        net_salary_amount: currency(55760),
      }),
    ],
    [nishaRao.id]: [
      compensationRecord(9303, nishaRao, {
        salary_structure_id: 8201,
        salary_structure: {
          id: 8201,
          code: 'OPS-L5-2026',
          name: 'Operations L5',
          version: 1,
          currency: 'INR',
          pay_frequency: 'monthly',
        },
        revision_reason: 'Quarterly structure alignment',
        effective_from: '2026-02-01',
        revision_date: '2026-02-01',
        annual_ctc_amount: currency(648000),
        basic_salary_amount: currency(216000),
        gross_salary_amount: currency(54000),
        net_salary_amount: currency(50650),
        notes: 'Draft payout remains gated until finalized payroll is available.',
      }),
    ],
    [kabirMalik.id]: [
      compensationRecord(9304, kabirMalik, {
        salary_structure_id: 8302,
        salary_structure: {
          id: 8302,
          code: 'QA-L4-2026',
          name: 'QA Analyst L4',
          version: 2,
          currency: 'INR',
          pay_frequency: 'monthly',
        },
        previous_revision_id: 9305,
        revision_reason: 'Promotion to senior analyst band',
        effective_from: '2026-03-01',
        revision_date: '2026-03-01',
        annual_ctc_amount: currency(612000),
        basic_salary_amount: currency(204000),
        gross_salary_amount: currency(51000),
        net_salary_amount: currency(47812.50),
        component_snapshot: [
          {
            salary_component_id: 201,
            code: 'BASIC',
            name: 'Basic salary',
            category: 'earning',
            display_order: 1,
            resolved_formula_inputs: {
              calculation_type: 'flat_amount',
              flat_amount: currency(17000),
              percentage_value: null,
              percentage_basis_component_codes: [],
              expression_formula: null,
            },
          },
          {
            salary_component_id: 204,
            code: 'FLEX',
            name: 'Flexible allowance',
            category: 'earning',
            display_order: 2,
            resolved_formula_inputs: {
              calculation_type: 'flat_amount',
              flat_amount: currency(25000),
              percentage_value: null,
              percentage_basis_component_codes: [],
              expression_formula: null,
            },
          },
          {
            salary_component_id: 205,
            code: 'STAT-DEDUCT',
            name: 'Statutory deductions',
            category: 'deduction',
            display_order: 3,
            resolved_formula_inputs: {
              calculation_type: 'expression',
              flat_amount: null,
              percentage_value: null,
              percentage_basis_component_codes: [],
              expression_formula: 'basic * 0.12 + professional_tax',
            },
          },
        ],
      }),
      compensationRecord(9305, kabirMalik, {
        salary_structure_id: 8301,
        salary_structure: {
          id: 8301,
          code: 'QA-L3-2025',
          name: 'QA Analyst L3',
          version: 1,
          currency: 'INR',
          pay_frequency: 'monthly',
        },
        revision_reason: 'Initial salary assignment',
        effective_from: '2025-11-15',
        revision_date: '2025-11-15',
        annual_ctc_amount: currency(564000),
        basic_salary_amount: currency(188000),
        gross_salary_amount: currency(47000),
        net_salary_amount: currency(44150),
      }),
    ],
  }
}

export function resolveDemoPayrollEmployeeId(userId: number | null | undefined) {
  if (!userId) {
    return null
  }

  return payrollEmployeeIdByUserId[userId] ?? null
}

export function buildDemoPayrollCompensationDetail(employeeId: number): EmployeeCompensationDetail | null {
  const history = buildCompensationHistory()[employeeId] ?? []
  const employee = history[0]?.employee ?? [amanVerma, nishaRao, kabirMalik].find((record) => record.id === employeeId) ?? null

  if (!employee) {
    return null
  }

  return {
    employee: {
      ...employee,
      employment_status: employee.id === nishaRao.id ? 'probation' : 'active',
    },
    current_assignment: history[0] ?? null,
    history,
  }
}

export function buildDemoPayrollWorkspace(): PayrollWorkspaceData {
  const runs = [createBlockedRun(), createFailedRun(), createReadyRun(), createLockedRun()]
  const runsByPeriodId = new Map(runs.map((run) => [run.payroll_period_id, run]))

  return {
    periods: createPeriods(runsByPeriodId),
    runs,
    payslips: createPayslips(),
  }
}
