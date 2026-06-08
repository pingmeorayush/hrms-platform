import type { LocationRecord, OrganizationMasterRecord } from '../organization/types'

export interface EmployeeReference {
  id: number
  employee_code: string
  full_name: string
  email: string
}

export interface EmployeeRecord {
  id: number
  employee_code: string
  first_name: string
  middle_name: string | null
  last_name: string
  full_name: string
  email: string
  phone: string | null
  date_of_birth: string | null
  gender: string | null
  marital_status: string | null
  date_of_joining: string
  employment_type: string
  employment_status: EmployeeStatus
  termination_reason: string | null
  terminated_at: string | null
  department: OrganizationMasterRecord
  designation: OrganizationMasterRecord
  manager: EmployeeReference | null
  location: LocationRecord | null
  cost_center: OrganizationMasterRecord | null
  user_id: number | null
  created_at: string | null
  updated_at: string | null
}

export type EmployeeStatus =
  | 'active'
  | 'inactive'
  | 'probation'
  | 'notice_period'
  | 'terminated'

export interface PaginationMeta {
  page: number
  per_page: number
  total: number
  last_page: number
}

export interface PaginatedEmployees {
  items: EmployeeRecord[]
  meta: PaginationMeta
}

export interface EmployeeDirectoryFilters {
  search: string
  employmentStatus: '' | EmployeeStatus
  departmentId: string
  designationId: string
  managerId: string
  page: number
  perPage: number
}

export interface EmployeeContactRecord {
  id: number
  employee_id: number
  type: 'email' | 'phone' | 'mobile' | 'whatsapp' | 'other'
  label: string | null
  value: string
  is_primary: boolean
  status: 'active' | 'inactive' | null
  notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface EmployeeAddressRecord {
  id: number
  employee_id: number
  type: 'permanent' | 'current' | 'office'
  address_line_1: string
  address_line_2: string | null
  city: string
  state: string | null
  country: string
  postal_code: string
  notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface EmployeeEmergencyContactRecord {
  id: number
  employee_id: number
  name: string
  relationship: string
  phone_number: string
  email: string | null
  address: string | null
  priority: number | null
  notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface EmployeeOnboardingTaskRecord {
  id: number
  employee_id: number
  title: string
  category: 'hr' | 'it' | 'manager' | 'department' | 'compliance' | 'training' | 'other'
  task_type:
    | 'read_policy'
    | 'submit_documents'
    | 'complete_training'
    | 'attend_session'
    | 'meet_manager'
    | 'setup_equipment'
    | 'complete_forms'
    | 'other'
    | null
  assignee_type: 'employee' | 'manager' | 'hr' | 'it_team' | 'facilities' | 'security' | 'other'
  status: 'pending' | 'in_progress' | 'completed' | 'skipped'
  sort_order: number
  due_date: string | null
  completed_at: string | null
  notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface EmployeeOnboardingTaskSummary {
  total_count: number
  completed_count: number
  skipped_count: number
  pending_count: number
  in_progress_count: number
  incomplete_count: number
  progress_percentage: number
  is_complete: boolean
}

export interface EmployeeOnboardingData {
  items: EmployeeOnboardingTaskRecord[]
  summary: EmployeeOnboardingTaskSummary
}

export interface EmployeeDocumentRecord {
  id: number
  employee_id: number
  document_type: string
  original_file_name: string
  mime_type: string
  file_size_bytes: number
  expiry_date: string | null
  notes: string | null
  download_url: string
  created_at: string | null
  updated_at: string | null
}

export interface EmployeeBankAccountRecord {
  id: number
  employee_id: number
  account_holder_name: string
  bank_name: string
  branch_name: string | null
  account_number: string
  ifsc_code: string | null
  routing_number: string | null
  iban: string | null
  swift_code: string | null
  status: string
  is_primary: boolean
  verified_at: string | null
  notes: string | null
  sensitive_access: 'full' | 'masked'
  created_at: string | null
  updated_at: string | null
}

export interface EmployeeBankAccountCollection {
  items: EmployeeBankAccountRecord[]
  meta: {
    total: number
  }
}

export interface AuditLogUser {
  id: number | null
  name: string | null
  email: string | null
}

export interface AuditLogEntry {
  id: number
  event_type: string
  entity_type: string | null
  entity_id: string | null
  ip_address: string | null
  created_at: string | null
  metadata: Record<string, unknown>
  user: AuditLogUser | null
}

export interface PaginatedAuditLogEntries {
  items: AuditLogEntry[]
  meta: PaginationMeta
}

export interface EmployeeProfileWorkspaceData {
  employee: EmployeeRecord
  contacts: EmployeeContactRecord[]
  addresses: EmployeeAddressRecord[]
  emergencyContacts: EmployeeEmergencyContactRecord[]
  onboarding: EmployeeOnboardingData
  documents: EmployeeDocumentRecord[]
  bankAccounts: EmployeeBankAccountCollection | null
  auditHistory: PaginatedAuditLogEntries
}
