import type { EmployeeReference } from '../employees/types'
import type { LocationRecord, OrganizationMasterRecord } from '../organization/types'

export type AttendanceRecordStatus = 'active' | 'inactive'
export type AttendanceAssignmentType = 'employee' | 'department' | 'location'
export type HolidayType = 'national' | 'regional' | 'company' | 'optional'
export type ShiftRosterStatus = 'scheduled' | 'cancelled'
export type AttendanceCaptureState = 'not_captured' | 'checked_in' | 'checked_out'
export type AttendanceCaptureChannel = 'web' | 'api' | null
export type AttendancePrimaryStatus =
  | 'present'
  | 'half_day'
  | 'absent'
  | 'holiday'
  | 'weekend'
  | 'incomplete'
  | null
export type AttendanceCorrectionStatus =
  | 'pending'
  | 'approved'
  | 'rejected'
  | 'changes_requested'
export type AttendanceWorkflowStatus = 'running' | 'waiting' | 'completed' | 'rejected'
export type AttendanceExceptionType =
  | 'absent'
  | 'half_day'
  | 'incomplete'
  | 'late'
  | 'pending_correction'
export type AttendanceCorrectionDecisionAction = 'approve' | 'reject' | 'request_changes'

export interface WeekendRule {
  non_working_days: number[]
}

export interface AttendancePolicy {
  id: number
  name: string
  working_hours_minutes: number
  grace_minutes: number
  late_after_minutes: number
  half_day_minutes: number
  overtime_eligible: boolean
  overtime_after_minutes: number | null
  weekend_rule: WeekendRule
  work_from_home_allowed: boolean
  enforce_geofence: boolean
  allowed_radius_meters: number | null
  status: AttendanceRecordStatus
  created_at: string | null
  updated_at: string | null
}

export interface AttendancePolicyUpdatePayload {
  name: string
  working_hours_minutes: number
  grace_minutes: number
  late_after_minutes: number
  half_day_minutes: number
  overtime_eligible: boolean
  overtime_after_minutes?: number | null
  weekend_rule: WeekendRule
  work_from_home_allowed: boolean
  enforce_geofence: boolean
  allowed_radius_meters?: number | null
  status: AttendanceRecordStatus
}

export interface Holiday {
  id: number
  holiday_calendar_id: number
  name: string
  holiday_date: string
  type: HolidayType
  is_optional: boolean
  description: string | null
  created_at: string | null
  updated_at: string | null
}

export interface HolidayPayload {
  name: string
  holiday_date: string
  type: HolidayType
  is_optional: boolean
  description?: string | null
}

export interface HolidayCalendar {
  id: number
  code: string
  name: string
  description: string | null
  location: LocationRecord | null
  department: OrganizationMasterRecord | null
  is_default: boolean
  status: AttendanceRecordStatus
  holidays: Holiday[]
  created_at: string | null
  updated_at: string | null
}

export interface HolidayCalendarPayload {
  code: string
  name: string
  description?: string | null
  location_id?: number | null
  department_id?: number | null
  is_default: boolean
  status: AttendanceRecordStatus
}

export interface Shift {
  id: number
  code: string
  name: string
  description: string | null
  start_time: string
  end_time: string
  break_duration_minutes: number
  grace_minutes: number
  working_hours_minutes: number
  is_overnight: boolean
  status: AttendanceRecordStatus
  created_at: string | null
  updated_at: string | null
}

export interface ShiftPayload {
  code: string
  name: string
  description?: string | null
  start_time: string
  end_time: string
  break_duration_minutes: number
  grace_minutes: number
  working_hours_minutes: number
  status: AttendanceRecordStatus
}

export interface ShiftAssignment {
  id: number
  assignment_type: AttendanceAssignmentType
  shift: Shift
  employee: EmployeeReference | null
  department: OrganizationMasterRecord | null
  location: LocationRecord | null
  effective_from: string
  effective_to: string | null
  notes: string | null
  status: AttendanceRecordStatus
  created_at: string | null
  updated_at: string | null
}

export interface ShiftAssignmentPayload {
  shift_id: number
  assignment_type: AttendanceAssignmentType
  employee_id?: number | null
  department_id?: number | null
  location_id?: number | null
  effective_from: string
  effective_to?: string | null
  notes?: string | null
  status: AttendanceRecordStatus
}

export interface ShiftRoster {
  id: number
  employee: EmployeeReference
  shift: Shift
  work_date: string
  notes: string | null
  status: ShiftRosterStatus
  created_at: string | null
  updated_at: string | null
}

export interface ShiftRosterPayload {
  employee_id: number
  shift_id: number
  work_date: string
  notes?: string | null
  status?: ShiftRosterStatus
}

export interface ShiftRosterUpdatePayload {
  shift_id: number
  work_date: string
  notes?: string | null
  status: ShiftRosterStatus
}

export interface AttendanceAdminWorkspaceData {
  policy: AttendancePolicy
  holidayCalendars: HolidayCalendar[]
  shifts: Shift[]
  assignments: ShiftAssignment[]
  rosters: ShiftRoster[]
  employees: EmployeeReference[]
  departments: OrganizationMasterRecord[]
  locations: LocationRecord[]
}

export interface AttendanceDeviceMetadata {
  device_id: string | null
  device_name: string | null
  platform: string | null
  browser: string | null
  app_version: string | null
}

export interface AttendanceGeolocationMetadata {
  latitude: number
  longitude: number
  accuracy_meters: number | null
}

export interface AttendanceCaptureMetadata {
  device: AttendanceDeviceMetadata | null
  geolocation: AttendanceGeolocationMetadata | null
}

export interface AttendanceCaptureSnapshot {
  at: string | null
  channel: AttendanceCaptureChannel
  ip_address: string | null
  user_agent: string | null
  metadata: AttendanceCaptureMetadata
}

export interface AttendanceCalculation {
  primary_status: AttendancePrimaryStatus
  scheduled_start_at: string | null
  scheduled_end_at: string | null
  scheduled_work_minutes: number | null
  break_duration_minutes: number
  is_late: boolean
  late_minutes: number
  is_half_day: boolean
  overtime_minutes: number
  is_weekend: boolean
  is_holiday: boolean
  holiday_name: string | null
  is_early_departure: boolean
  early_departure_minutes: number
  calculated_at: string | null
  metadata: Record<string, unknown>
}

export interface AttendanceRecord {
  id: number
  attendance_date: string
  employee: EmployeeReference
  shift: Shift | null
  shift_roster_id: number | null
  state: AttendanceCaptureState
  worked_minutes: number | null
  calculation: AttendanceCalculation
  check_in: AttendanceCaptureSnapshot
  check_out: AttendanceCaptureSnapshot
  created_at: string | null
  updated_at: string | null
}

export interface AttendanceHistoryFilters {
  dateFrom: string
  dateTo: string
  primaryStatus: '' | Exclude<AttendancePrimaryStatus, null>
  state: '' | AttendanceCaptureState
  perPage: number
}

export interface AttendancePaginationMeta {
  page: number
  per_page: number
  total: number
  last_page: number
}

export interface PaginatedAttendanceRecords {
  items: AttendanceRecord[]
  meta: AttendancePaginationMeta
}

export interface AttendanceUserReference {
  id: number
  name: string
  email: string | null
}

export interface AttendanceCorrectionValueSnapshot {
  attendance_date: string | null
  check_in_at: string | null
  check_out_at: string | null
  check_in_channel: string | null
  check_out_channel: string | null
  worked_minutes: number | null
  primary_status: AttendancePrimaryStatus
  shift_id: number | null
  shift_roster_id: number | null
}

export interface AttendanceWorkflowApprovalTask {
  id: number
  stage_key: string
  stage_name: string
  sequence: number
  status: 'open' | 'completed' | 'closed'
  available_actions: string[]
  decision: string | null
  decision_comment: string | null
  due_at: string | null
  acted_at: string | null
  assigned_to_role: string | null
  assignee: AttendanceUserReference | null
  actor: AttendanceUserReference | null
}

export interface AttendanceCorrectionWorkflow {
  id: number
  status: AttendanceWorkflowStatus
  current_stage_sequence: number | null
  approval_history: AttendanceWorkflowApprovalTask[]
  current_task: AttendanceWorkflowApprovalTask | null
}

export interface AttendanceCorrection {
  id: number
  status: AttendanceCorrectionStatus
  reason: string
  attendance_record_id: number
  employee: EmployeeReference
  requested_by: AttendanceUserReference | null
  latest_action_by: AttendanceUserReference | null
  original_values: AttendanceCorrectionValueSnapshot
  corrected_values: AttendanceCorrectionValueSnapshot
  applied_values: AttendanceCorrectionValueSnapshot | null
  decision_comment: string | null
  workflow: AttendanceCorrectionWorkflow | null
  approved_at: string | null
  rejected_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface AttendanceCorrectionSummary {
  id: number
  status: AttendanceCorrectionStatus
  reason: string
  attendance_record_id: number
  requested_by: AttendanceUserReference | null
  created_at: string | null
  updated_at: string | null
}

export interface PaginatedAttendanceCorrections {
  items: AttendanceCorrection[]
  meta: AttendancePaginationMeta
}

export interface AttendanceOperationalRecord extends AttendanceRecord {
  exception_types: AttendanceExceptionType[]
  has_pending_correction: boolean
  pending_corrections: AttendanceCorrectionSummary[]
}

export interface AttendanceOperationalReviewSummary {
  total_records: number
  present_count: number
  absent_count: number
  half_day_count: number
  incomplete_count: number
  holiday_count: number
  weekend_count: number
  late_count: number
  pending_correction_count: number
  checked_in_count: number
  checked_out_count: number
}

export interface AttendancePendingExceptionSummary {
  exception_record_count: number
  late_record_count: number
  absent_record_count: number
  half_day_record_count: number
  incomplete_record_count: number
  pending_correction_record_count: number
  pending_correction_request_count: number
}

export interface AttendanceOperationalReviewData {
  window_date: string
  summary: AttendanceOperationalReviewSummary
  items: AttendanceOperationalRecord[]
}

export interface AttendancePendingExceptionsData {
  window_date: string
  summary: AttendancePendingExceptionSummary
  attendance_items: AttendanceOperationalRecord[]
  correction_items: AttendanceCorrection[]
}

export interface AttendanceCapturePayload {
  channel?: Exclude<AttendanceCaptureChannel, null>
  captured_at?: string
  device?: AttendanceDeviceMetadata | null
  geolocation?: AttendanceGeolocationMetadata | null
}

export type AttendanceCheckInPayload = AttendanceCapturePayload
export type AttendanceCheckOutPayload = AttendanceCapturePayload

export interface AttendanceCorrectionCreatePayload {
  attendance_record_id: number
  reason: string
  corrected: {
    check_in_at?: string | null
    check_out_at?: string | null
  }
}

export interface AttendanceCorrectionDecisionPayload {
  action: AttendanceCorrectionDecisionAction
  comment?: string | null
}

export interface AttendanceCorrectionsFilters {
  employeeId?: number | null
  attendanceRecordId?: number | null
  status?: AttendanceCorrectionStatus | ''
  perPage?: number
}

export interface AttendanceEmployeeWorkspaceData {
  currentEmployee: EmployeeReference | null
  policy: AttendancePolicy | null
  todayRecord: AttendanceRecord | null
  history: PaginatedAttendanceRecords
  corrections: PaginatedAttendanceCorrections
}

export interface AttendanceReviewWorkspaceData {
  scope: 'team' | 'tenant'
  windowDate: string
  operationalReview: AttendanceOperationalReviewData
  pendingExceptions: AttendancePendingExceptionsData
  corrections: PaginatedAttendanceCorrections
}
