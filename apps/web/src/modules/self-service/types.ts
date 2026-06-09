import type {
  EmployeeAddressRecord,
  EmployeeContactRecord,
  EmployeeEmergencyContactRecord,
  EmployeeRecord,
} from '../employees/types'

export type SelfServiceDocumentSourceType =
  | 'policy_acknowledgement'
  | 'employee_document'
  | 'repository_document'

export interface SelfServiceDocumentCategory {
  id: number
  code: string
  name: string
}

export interface SelfServiceDocumentRecord {
  id: string
  source_type: SelfServiceDocumentSourceType
  source_id: number
  title: string
  subtitle: string
  status: 'assigned' | 'acknowledged' | 'available'
  document_type: string | null
  file_name: string | null
  mime_type: string | null
  file_size_bytes: number | null
  due_date: string | null
  expiry_date: string | null
  visibility_scope: string | null
  download_url: string | null
  acknowledge_url: string | null
  action_required: boolean
  notes: string | null
  category: SelfServiceDocumentCategory | null
  repository_scope: string | null
  created_at: string | null
  updated_at: string | null
}

export interface SelfServiceDocumentSummary {
  total_count: number
  pending_acknowledgement_count: number
  acknowledged_count: number
  downloadable_count: number
  hidden_sensitive_count: number
}

export interface SelfServiceAssetCategory {
  id: number
  code: string
  name: string
  status: string
}

export interface SelfServiceAssetAssignment {
  id: number
  status: 'assigned' | 'issued' | 'returned'
  assigned_at: string | null
  issued_at: string | null
  expected_return_date: string | null
  returned_at: string | null
  handover_condition: string | null
  return_condition: string | null
  assignment_notes: string | null
  issue_notes: string | null
  return_notes: string | null
  due_state: 'overdue' | 'due_today' | 'upcoming' | 'no_due_date'
}

export interface SelfServiceAssetRecord {
  id: number
  asset_tag: string
  name: string
  asset_type: string
  status: 'assigned' | 'issued' | 'returned' | 'available' | 'maintenance' | 'retired'
  serial_number: string | null
  manufacturer: string | null
  model_name: string | null
  purchase_date: string | null
  notes: string | null
  category: SelfServiceAssetCategory | null
  assignment: SelfServiceAssetAssignment | null
  created_at: string | null
  updated_at: string | null
}

export interface SelfServiceAssetSummary {
  active_count: number
  assigned_count: number
  issued_count: number
  overdue_count: number
}

export interface SelfServiceSensitivePanel {
  visible: boolean
  message: string | null
}

export interface SelfServiceWorkspaceData {
  employee: EmployeeRecord
  profile: {
    contacts: EmployeeContactRecord[]
    addresses: EmployeeAddressRecord[]
    emergency_contacts: EmployeeEmergencyContactRecord[]
    sensitive_panels: {
      bank_accounts: SelfServiceSensitivePanel
    }
  }
  documents: {
    summary: SelfServiceDocumentSummary
    items: SelfServiceDocumentRecord[]
  }
  assets: {
    summary: SelfServiceAssetSummary
    items: SelfServiceAssetRecord[]
  }
}
