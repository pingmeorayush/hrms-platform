import type { EmployeeReference } from '../employees/types'
import type { LocationRecord, OrganizationMasterRecord, OrganizationStatus } from '../organization/types'

export type LeaveTypeCategory = 'earned' | 'casual' | 'sick' | 'optional' | 'unpaid'
export type LeaveAccrualFrequency = 'monthly' | 'quarterly' | 'annual' | 'none'
export type LeaveRequestStatus =
  | 'approved'
  | 'pending'
  | 'rejected'
  | 'cancelled'
  | 'changes_requested'
export type LeaveReviewDecisionAction = 'approve' | 'reject' | 'request_changes'

export interface LeaveTypeRecord {
  id: number
  code: string
  name: string
  category: LeaveTypeCategory
  description: string | null
  is_paid: boolean
  requires_approval: boolean
  allows_half_day: boolean
  color_token: string
  status: OrganizationStatus
  created_at: string | null
  updated_at: string | null
}

export interface LeavePolicyRecord {
  id: number
  leave_type_id: number
  leave_type: LeaveTypeRecord
  annual_allowance_days: number
  opening_balance_days: number
  accrual_frequency: LeaveAccrualFrequency
  carry_forward_limit_days: number
  encashment_limit_days: number
  max_consecutive_days: number
  min_notice_days: number
  requires_documentation_after_days: number | null
  applicable_department: OrganizationMasterRecord | null
  applicable_location: LocationRecord | null
  status: OrganizationStatus
  created_at: string | null
  updated_at: string | null
}

export interface LeaveCalendarEntry {
  id: number
  employee: EmployeeReference
  department: OrganizationMasterRecord
  location: LocationRecord | null
  leave_type: LeaveTypeRecord
  start_date: string
  end_date: string
  total_days: number
  status: LeaveRequestStatus
  reason: string
  created_at: string | null
  updated_at: string | null
}

export interface LeaveBalanceRecord {
  id: number
  employee_id: number
  employee: EmployeeReference
  leave_type: LeaveTypeRecord
  available_days: number
  booked_days: number
  used_days: number
  accrued_days: number
  carry_forward_days: number
  updated_at: string | null
}

export interface LeaveRequestRecord {
  id: number
  employee: EmployeeReference
  department: OrganizationMasterRecord
  location: LocationRecord | null
  leave_type: LeaveTypeRecord
  start_date: string
  end_date: string
  total_days: number
  status: LeaveRequestStatus
  reason: string
  approver_comment: string | null
  can_cancel: boolean
  created_at: string | null
  updated_at: string | null
}

export interface LeaveAdminWorkspaceData {
  leaveTypes: LeaveTypeRecord[]
  policies: LeavePolicyRecord[]
  calendarEntries: LeaveCalendarEntry[]
  balances: LeaveBalanceRecord[]
  requests: LeaveRequestRecord[]
  departments: OrganizationMasterRecord[]
  locations: LocationRecord[]
  employees: EmployeeReference[]
}

export interface LeaveTypeFormValues {
  code: string
  name: string
  category: LeaveTypeCategory
  description: string
  is_paid: boolean
  requires_approval: boolean
  allows_half_day: boolean
  color_token: string
  status: OrganizationStatus
}

export interface LeavePolicyFormValues {
  leave_type_id: string
  annual_allowance_days: string
  opening_balance_days: string
  accrual_frequency: LeaveAccrualFrequency
  carry_forward_limit_days: string
  encashment_limit_days: string
  max_consecutive_days: string
  min_notice_days: string
  requires_documentation_after_days: string
  applicable_department_id: string
  applicable_location_id: string
  status: OrganizationStatus
}

export interface LeaveCalendarFilters {
  status: '' | LeaveRequestStatus
  departmentId: string
  locationId: string
}

export interface LeaveRequestFormValues {
  leave_type_id: string
  start_date: string
  end_date: string
  reason: string
}
