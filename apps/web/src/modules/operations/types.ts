import type { EmployeeRecord } from '../employees/types'

export type OperationsLifecycleType = 'onboarding' | 'offboarding'

export interface OperationsDocumentCategoryRecord {
  id: number
  code: string
  name: string
  repository_scope: string
  default_visibility_scope: string
  retention_days: number | null
  allowed_role_names: string[]
  status: string
  notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsDocumentRecord {
  id: number
  document_category_id: number | null
  document_category: {
    id: number
    code: string
    name: string
    default_visibility_scope: string
    retention_days: number | null
    allowed_role_names: string[]
    status: string
  } | null
  title: string
  repository_scope: string
  linked_entity_type: string | null
  linked_entity_id: number | null
  visibility_scope: string
  original_file_name: string
  mime_type: string
  file_size_bytes: number
  checksum_sha256: string | null
  retention_until: string | null
  metadata: Record<string, unknown>
  notes: string | null
  download_url: string
  created_at: string | null
  updated_at: string | null
}

export interface OperationsAssetCategoryRecord {
  id: number
  code: string
  name: string
  status: string
  notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsAssetAssignmentRecord {
  id: number
  asset_id: number
  employee_id: number
  employee: {
    id: number
    employee_code: string
    full_name: string
    email: string | null
  } | null
  status: string
  assigned_at: string | null
  issued_at: string | null
  expected_return_date: string | null
  returned_at: string | null
  handover_condition: string | null
  return_condition: string | null
  assignment_notes: string | null
  issue_notes: string | null
  return_notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsAssetRecord {
  id: number
  asset_category_id: number
  asset_category: {
    id: number
    code: string
    name: string
    status: string
  } | null
  asset_tag: string
  name: string
  asset_type: string
  serial_number: string | null
  manufacturer: string | null
  model_name: string | null
  purchase_date: string | null
  status: string
  notes: string | null
  current_assignment: OperationsAssetAssignmentRecord | null
  assignment_history: OperationsAssetAssignmentRecord[]
  created_at: string | null
  updated_at: string | null
}

export interface OperationsLifecycleStatusRecord {
  employee: {
    id: number
    employee_code: string
    full_name: string
    email: string | null
    date_of_joining: string | null
    department: string | null
    designation: string | null
  }
  lifecycle_type: OperationsLifecycleType
  summary: {
    total_count: number
    closed_count: number
    incomplete_count: number
    progress_percentage: number
    is_complete: boolean
  }
}

export interface OperationsLifecycleTaskRecord {
  id: number
  employee_id: number
  lifecycle_type: OperationsLifecycleType
  template_id: number | null
  title: string
  category: string
  task_type: string | null
  assignee_type: string
  assigned_to_user_id: number | null
  assigned_to_user_name: string | null
  requires_approval: boolean
  approval_workflow_key: string | null
  workflow_instance_id: number | null
  status: string
  sort_order: number
  due_date: string | null
  due_state: string
  completed_at: string | null
  completed_by_user_id: number | null
  latest_action_by_user_id: number | null
  approved_at: string | null
  notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsLifecycleTaskCollection {
  items: OperationsLifecycleTaskRecord[]
  summary: {
    total_count: number
    completed_count: number
    skipped_count: number
    pending_count: number
    in_progress_count: number
    awaiting_approval_count: number
    changes_requested_count: number
    rejected_count: number
    incomplete_count: number
    progress_percentage: number
    is_complete: boolean
  }
  lifecycle_type: OperationsLifecycleType
}

export interface OperationsWorkspaceData {
  documentCategories: OperationsDocumentCategoryRecord[]
  documents: OperationsDocumentRecord[]
  assetCategories: OperationsAssetCategoryRecord[]
  assets: OperationsAssetRecord[]
  employees: EmployeeRecord[]
  lifecycle: {
    onboarding: OperationsLifecycleStatusRecord[]
    offboarding: OperationsLifecycleStatusRecord[]
  }
  lifecycleTaskDetails?: Partial<
    Record<
      number,
      {
        onboarding?: OperationsLifecycleTaskCollection
        offboarding?: OperationsLifecycleTaskCollection
      }
    >
  >
}

export interface DocumentCategoryFormValues {
  code: string
  name: string
  repository_scope: string
  default_visibility_scope: string
  retention_days: string
  allowed_role_names: string
  status: string
  notes: string
}

export interface AssetCategoryFormValues {
  code: string
  name: string
  status: string
  notes: string
}

export interface AssetFormValues {
  asset_category_id: string
  asset_tag: string
  name: string
  asset_type: string
  serial_number: string
  manufacturer: string
  model_name: string
  purchase_date: string
  status: string
  notes: string
}

export interface AssetAssignmentFormValues {
  employee_id: string
  assigned_at: string
  expected_return_date: string
  handover_condition: string
  assignment_notes: string
}

export interface AssetIssueFormValues {
  issued_at: string
  issue_notes: string
}

export interface AssetReturnFormValues {
  returned_at: string
  return_condition: string
  return_notes: string
}
