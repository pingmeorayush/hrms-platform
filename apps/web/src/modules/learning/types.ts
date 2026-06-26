import type { EmployeeRecord, EmployeeReference } from '../employees/types'
import type { OrganizationMasterRecord } from '../organization/types'

export type LearningItemStatus = 'draft' | 'active' | 'archived'
export type LearningDeliveryMode =
  | 'self_paced'
  | 'instructor_led'
  | 'virtual_session'
  | 'blended'
  | 'document_acknowledgement'
export type LearningAudienceType = 'employee' | 'department' | 'designation' | 'all_active'
export type LearningAssignmentStatus = 'active' | 'archived'
export type LearningTargetStatus = 'assigned' | 'completed'
export type LearningDueState = 'completed' | 'no_due_date' | 'overdue' | 'due_today' | 'upcoming'
export type LearningRenewalPosture = 'not_configured' | 'pending_initial_completion' | 'overdue' | 'due_today' | 'current'

export interface LearningActorSummary {
  id: number | null
  name: string | null
}

export interface LearningItemRecord {
  id: number
  code: string
  title: string
  description: string | null
  category: string
  delivery_mode: LearningDeliveryMode
  duration_minutes: number | null
  requires_completion_evidence: boolean
  renewal_frequency_months: number | null
  default_due_days: number | null
  metadata: Record<string, unknown> | null
  status: LearningItemStatus
  created_by?: LearningActorSummary | null
  updated_by?: LearningActorSummary | null
  created_at: string | null
  updated_at: string | null
}

export interface LearningAudienceRules {
  employee_ids?: number[]
  department_ids?: number[]
  designation_ids?: number[]
}

export interface LearningCompletionRules {
  requires_completion_evidence: boolean
  renewal_frequency_months: number | null
  default_due_days: number | null
}

export interface LearningAssignmentTargetSummary {
  total_count: number
  completed_count: number
  overdue_count: number
  renewal_overdue_count: number
}

export interface LearningAssignmentTargetRecord {
  id: number
  assignment: {
    id: number | null
    assignment_code: string | null
    status: string | null
    audience_type: LearningAudienceType | null
  } | null
  item: LearningItemRecord | null
  employee: EmployeeReference | null
  status: LearningTargetStatus
  completion_progress_percent: number
  due_on: string | null
  due_state: LearningDueState
  renewal_due_on: string | null
  renewal_posture: LearningRenewalPosture
  requires_completion_evidence: boolean
  evidence_present: boolean
  completion_notes: string | null
  completion_evidence: Record<string, unknown> | null
  completed_at: string | null
  completed_by?: LearningActorSummary | null
  assigned_on: string | null
  created_at: string | null
  updated_at: string | null
}

export interface LearningAssignmentRecord {
  id: number
  assignment_code: string
  item: LearningItemRecord | null
  audience_type: LearningAudienceType
  audience_rules: LearningAudienceRules
  assigned_on: string | null
  due_on: string | null
  completion_rules: LearningCompletionRules
  notes: string | null
  status: LearningAssignmentStatus
  target_count: number
  completion_count: number
  assigned_by?: LearningActorSummary | null
  target_summary: LearningAssignmentTargetSummary | null
  targets?: LearningAssignmentTargetRecord[]
  created_at: string | null
  updated_at: string | null
}

export interface LearningWorkspaceMeta {
  can_view_learning: boolean
  can_manage_catalog: boolean
  can_assign_learning: boolean
  can_complete_learning: boolean
  linked_employee_id: number | null
}

export interface LearningWorkspaceData {
  items: LearningItemRecord[]
  assignments: LearningAssignmentRecord[]
  targets: LearningAssignmentTargetRecord[]
  myAssignments: LearningAssignmentTargetRecord[]
  employees: EmployeeRecord[]
  departments: OrganizationMasterRecord[]
  designations: OrganizationMasterRecord[]
  meta: LearningWorkspaceMeta
}

export interface CreateLearningItemInput {
  code: string
  title: string
  description?: string | null
  category: string
  delivery_mode: LearningDeliveryMode
  duration_minutes?: number | null
  requires_completion_evidence: boolean
  renewal_frequency_months?: number | null
  default_due_days?: number | null
  metadata?: Record<string, unknown> | null
  status: LearningItemStatus
}

export type UpdateLearningItemInput = Partial<CreateLearningItemInput>

export interface CreateLearningAssignmentInput {
  learning_item_id: number
  audience_type: LearningAudienceType
  audience_rules: LearningAudienceRules
  assigned_on?: string | null
  due_on?: string | null
  requires_completion_evidence?: boolean | null
  renewal_frequency_months?: number | null
  default_due_days?: number | null
  notes?: string | null
}

export interface CompleteLearningTargetInput {
  completion_notes?: string | null
  completion_evidence?: {
    type: string
    reference: string
    notes?: string | null
  } | null
}
