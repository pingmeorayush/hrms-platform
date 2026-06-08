import type { AccessSnapshot } from '../../access/types'
import { buildDemoEmployees } from '../../employees/data/demoEmployees'
import { buildDemoOrganizationWorkspace } from '../../organization/data/demoOrganizationWorkspace'
import type {
  LeaveAdminWorkspaceData,
  LeaveBalanceRecord,
  LeaveCalendarEntry,
  LeavePolicyRecord,
  LeaveRequestRecord,
  LeaveTypeRecord,
} from '../types'

export function buildDemoLeaveWorkspace(snapshot: AccessSnapshot | null): LeaveAdminWorkspaceData {
  const organization = buildDemoOrganizationWorkspace(snapshot)
  const employees = buildDemoEmployees(snapshot)
  const leaveTypes = buildLeaveTypes()
  const leaveTypeByCode = new Map(leaveTypes.map((record) => [record.code, record]))
  const policies = buildPolicies(leaveTypeByCode, organization)
  const requests = buildRequests(leaveTypeByCode, employees)
  const balances = buildBalances(leaveTypeByCode, employees)
  const calendarEntries = requests.map(toCalendarEntry)

  return {
    leaveTypes,
    policies,
    calendarEntries,
    balances,
    requests,
    departments: organization.departments,
    locations: organization.locations,
    employees: employees.map((employee) => ({
      id: employee.id,
      employee_code: employee.employee_code,
      full_name: employee.full_name,
      email: employee.email,
    })),
  }
}

function buildLeaveTypes(): LeaveTypeRecord[] {
  return [
    createLeaveType({
      id: 1,
      code: 'AL',
      name: 'Annual Leave',
      category: 'earned',
      description: 'Core planned time off with monthly accrual and carry-forward.',
      is_paid: true,
      requires_approval: true,
      allows_half_day: true,
      color_token: '#0972d3',
      status: 'active',
    }),
    createLeaveType({
      id: 2,
      code: 'SL',
      name: 'Sick Leave',
      category: 'sick',
      description: 'Protected sick leave with documentation thresholds.',
      is_paid: true,
      requires_approval: true,
      allows_half_day: true,
      color_token: '#d13212',
      status: 'active',
    }),
    createLeaveType({
      id: 3,
      code: 'CL',
      name: 'Casual Leave',
      category: 'casual',
      description: 'Short-notice personal leave with capped consecutive days.',
      is_paid: true,
      requires_approval: true,
      allows_half_day: false,
      color_token: '#ec7211',
      status: 'active',
    }),
    createLeaveType({
      id: 4,
      code: 'OH',
      name: 'Optional Holiday',
      category: 'optional',
      description: 'Employee-selected optional holidays from the annual list.',
      is_paid: true,
      requires_approval: true,
      allows_half_day: false,
      color_token: '#7d6608',
      status: 'active',
    }),
    createLeaveType({
      id: 5,
      code: 'LWP',
      name: 'Leave Without Pay',
      category: 'unpaid',
      description: 'Unpaid leave used when paid balances are unavailable.',
      is_paid: false,
      requires_approval: true,
      allows_half_day: false,
      color_token: '#5f6b7a',
      status: 'active',
    }),
  ]
}

function buildPolicies(
  leaveTypeByCode: Map<string, LeaveTypeRecord>,
  organization: ReturnType<typeof buildDemoOrganizationWorkspace>,
): LeavePolicyRecord[] {
  const peopleOps = organization.departments.find((record) => record.code === 'PEO') ?? null
  const bengaluru = organization.locations.find((record) => record.code === 'BLR') ?? null

  return [
    createPolicy({
      id: 101,
      leaveType: leaveTypeByCode.get('AL'),
      annual_allowance_days: 18,
      opening_balance_days: 0,
      accrual_frequency: 'monthly',
      carry_forward_limit_days: 8,
      encashment_limit_days: 5,
      max_consecutive_days: 10,
      min_notice_days: 5,
      requires_documentation_after_days: null,
      applicable_department: null,
      applicable_location: null,
      status: 'active',
    }),
    createPolicy({
      id: 102,
      leaveType: leaveTypeByCode.get('SL'),
      annual_allowance_days: 12,
      opening_balance_days: 12,
      accrual_frequency: 'annual',
      carry_forward_limit_days: 0,
      encashment_limit_days: 0,
      max_consecutive_days: 6,
      min_notice_days: 0,
      requires_documentation_after_days: 2,
      applicable_department: null,
      applicable_location: null,
      status: 'active',
    }),
    createPolicy({
      id: 103,
      leaveType: leaveTypeByCode.get('CL'),
      annual_allowance_days: 6,
      opening_balance_days: 6,
      accrual_frequency: 'quarterly',
      carry_forward_limit_days: 0,
      encashment_limit_days: 0,
      max_consecutive_days: 3,
      min_notice_days: 1,
      requires_documentation_after_days: null,
      applicable_department: null,
      applicable_location: bengaluru,
      status: 'active',
    }),
    createPolicy({
      id: 104,
      leaveType: leaveTypeByCode.get('OH'),
      annual_allowance_days: 2,
      opening_balance_days: 2,
      accrual_frequency: 'annual',
      carry_forward_limit_days: 0,
      encashment_limit_days: 0,
      max_consecutive_days: 1,
      min_notice_days: 3,
      requires_documentation_after_days: null,
      applicable_department: peopleOps,
      applicable_location: null,
      status: 'active',
    }),
    createPolicy({
      id: 105,
      leaveType: leaveTypeByCode.get('LWP'),
      annual_allowance_days: 0,
      opening_balance_days: 0,
      accrual_frequency: 'none',
      carry_forward_limit_days: 0,
      encashment_limit_days: 0,
      max_consecutive_days: 15,
      min_notice_days: 3,
      requires_documentation_after_days: null,
      applicable_department: null,
      applicable_location: null,
      status: 'active',
    }),
  ]
}

function buildRequests(
  leaveTypeByCode: Map<string, LeaveTypeRecord>,
  employees: ReturnType<typeof buildDemoEmployees>,
): LeaveRequestRecord[] {
  return [
    createRequest({
      id: 201,
      employee: employees.find((record) => record.id === 1002),
      leaveType: leaveTypeByCode.get('AL'),
      start_date: '2026-06-17',
      end_date: '2026-06-19',
      total_days: 3,
      status: 'approved',
      reason: 'Family travel and pre-booked time off.',
      approver_comment: 'Approved after release planning confirmed coverage.',
      can_cancel: false,
    }),
    createRequest({
      id: 202,
      employee: employees.find((record) => record.id === 1003),
      leaveType: leaveTypeByCode.get('SL'),
      start_date: '2026-06-08',
      end_date: '2026-06-09',
      total_days: 2,
      status: 'pending',
      reason: 'Recovery after medical consultation.',
      approver_comment: null,
      can_cancel: true,
    }),
    createRequest({
      id: 203,
      employee: employees.find((record) => record.id === 1004),
      leaveType: leaveTypeByCode.get('CL'),
      start_date: '2026-06-24',
      end_date: '2026-06-24',
      total_days: 1,
      status: 'approved',
      reason: 'School admissions appointment.',
      approver_comment: 'Approved as planned casual leave.',
      can_cancel: false,
    }),
    createRequest({
      id: 204,
      employee: employees.find((record) => record.id === 1005),
      leaveType: leaveTypeByCode.get('OH'),
      start_date: '2026-08-28',
      end_date: '2026-08-28',
      total_days: 1,
      status: 'approved',
      reason: 'Regional festival observance.',
      approver_comment: 'Approved against the optional holiday pool.',
      can_cancel: false,
    }),
    createRequest({
      id: 205,
      employee: employees.find((record) => record.id === 1006),
      leaveType: leaveTypeByCode.get('LWP'),
      start_date: '2026-06-30',
      end_date: '2026-07-02',
      total_days: 3,
      status: 'rejected',
      reason: 'Requested after payroll lock window without exception approval.',
      approver_comment: 'Rejected because the request fell outside the approved payroll exception window.',
      can_cancel: false,
    }),
    createRequest({
      id: 206,
      employee: employees.find((record) => record.id === 1001),
      leaveType: leaveTypeByCode.get('AL'),
      start_date: '2026-07-10',
      end_date: '2026-07-11',
      total_days: 2,
      status: 'cancelled',
      reason: 'Vacation plan moved after the customer summit date changed.',
      approver_comment: 'Cancellation captured before final scheduling freeze.',
      can_cancel: false,
    }),
  ].filter((record): record is LeaveRequestRecord => Boolean(record))
}

function buildBalances(
  leaveTypeByCode: Map<string, LeaveTypeRecord>,
  employees: ReturnType<typeof buildDemoEmployees>,
): LeaveBalanceRecord[] {
  const balanceBlueprints: Array<{
    employeeId: number
    code: string
    available: number
    booked: number
    used: number
    accrued: number
    carry: number
  }> = [
    { employeeId: 1001, code: 'AL', available: 9, booked: 2, used: 5, accrued: 11, carry: 2 },
    { employeeId: 1001, code: 'SL', available: 10, booked: 0, used: 2, accrued: 12, carry: 0 },
    { employeeId: 1001, code: 'CL', available: 4, booked: 0, used: 2, accrued: 6, carry: 0 },
    { employeeId: 1002, code: 'AL', available: 8, booked: 3, used: 7, accrued: 13, carry: 2 },
    { employeeId: 1002, code: 'SL', available: 9, booked: 0, used: 3, accrued: 12, carry: 0 },
    { employeeId: 1002, code: 'CL', available: 5, booked: 0, used: 1, accrued: 6, carry: 0 },
    { employeeId: 1003, code: 'AL', available: 6, booked: 0, used: 4, accrued: 10, carry: 0 },
    { employeeId: 1003, code: 'SL', available: 10, booked: 2, used: 0, accrued: 12, carry: 0 },
    { employeeId: 1003, code: 'CL', available: 6, booked: 0, used: 0, accrued: 6, carry: 0 },
    { employeeId: 1004, code: 'AL', available: 11, booked: 0, used: 4, accrued: 13, carry: 2 },
    { employeeId: 1004, code: 'SL', available: 11, booked: 0, used: 1, accrued: 12, carry: 0 },
    { employeeId: 1004, code: 'CL', available: 5, booked: 1, used: 0, accrued: 6, carry: 0 },
    { employeeId: 1005, code: 'AL', available: 4, booked: 1, used: 5, accrued: 8, carry: 1 },
    { employeeId: 1005, code: 'SL', available: 12, booked: 0, used: 0, accrued: 12, carry: 0 },
    { employeeId: 1005, code: 'OH', available: 1, booked: 0, used: 1, accrued: 2, carry: 0 },
    { employeeId: 1006, code: 'LWP', available: 0, booked: 0, used: 3, accrued: 0, carry: 0 },
  ]

  return balanceBlueprints.reduce<LeaveBalanceRecord[]>((records, item, index) => {
      const leaveType = leaveTypeByCode.get(item.code)
      const employee = employees.find((record) => record.id === item.employeeId)

      if (!leaveType || !employee) {
        return records
      }

      records.push({
        id: 5000 + index + 1,
        employee_id: employee.id,
        employee: {
          id: employee.id,
          employee_code: employee.employee_code,
          full_name: employee.full_name,
          email: employee.email,
        },
        leave_type: leaveType,
        available_days: item.available,
        booked_days: item.booked,
        used_days: item.used,
        accrued_days: item.accrued,
        carry_forward_days: item.carry,
        updated_at: timestamp(12 + index),
      })

      return records
    }, [])
}

function createLeaveType(
  values: Omit<LeaveTypeRecord, 'created_at' | 'updated_at'> &
    Partial<Pick<LeaveTypeRecord, 'created_at' | 'updated_at'>>,
): LeaveTypeRecord {
  return {
    ...values,
    created_at: values.created_at ?? timestamp(480),
    updated_at: values.updated_at ?? timestamp(24),
  }
}

function createPolicy({
  leaveType,
  ...values
}: Omit<LeavePolicyRecord, 'created_at' | 'updated_at' | 'leave_type_id' | 'leave_type'> & {
  leaveType: LeaveTypeRecord | undefined
}): LeavePolicyRecord {
  if (!leaveType) {
    throw new Error('Missing leave type for policy seed data.')
  }

  return {
    ...values,
    leave_type_id: leaveType.id,
    leave_type: leaveType,
    created_at: timestamp(360),
    updated_at: timestamp(36),
  }
}

function createRequest({
  employee,
  leaveType,
  ...values
}: Omit<LeaveRequestRecord, 'created_at' | 'updated_at' | 'employee' | 'department' | 'location' | 'leave_type'> & {
  employee: ReturnType<typeof buildDemoEmployees>[number] | undefined
  leaveType: LeaveTypeRecord | undefined
}): LeaveRequestRecord | null {
  if (!employee || !leaveType) {
    return null
  }

  return {
    ...values,
    employee: {
      id: employee.id,
      employee_code: employee.employee_code,
      full_name: employee.full_name,
      email: employee.email,
    },
    department: employee.department,
    location: employee.location,
    leave_type: leaveType,
    created_at: timestamp(96),
    updated_at: timestamp(18),
  }
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

function timestamp(hoursAgo: number) {
  return new Date(Date.now() - hoursAgo * 60 * 60 * 1000).toISOString()
}
