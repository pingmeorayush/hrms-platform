import type { EmployeeRecord, EmployeeReference, PaginationMeta } from '../employees/types'

export type PerformanceGoalStatus = 'draft' | 'active' | 'archived'
export type PerformanceReviewCycleStatus = 'draft' | 'scheduled' | 'active' | 'archived'
export type PerformanceReviewStatus =
  | 'draft'
  | 'self_assessment'
  | 'manager_review'
  | 'calibration'
  | 'finalized'
  | 'published'
  | 'reopened'

export type PerformanceReviewActorRole = 'self' | 'manager' | 'reviewer' | 'hr' | null

export interface PerformanceScaleLabel {
  value: number
  label: string
}

export interface PerformanceScaleDefinition {
  min_rating: number
  max_rating: number
  labels: PerformanceScaleLabel[]
}

export interface PerformanceCompetencyRecord {
  id: number
  code: string
  name: string
  category: string
  description: string | null
  scale_definition: PerformanceScaleDefinition
  status: 'active' | 'inactive' | 'archived'
  created_at: string | null
  updated_at: string | null
}

export interface PerformanceReviewTemplateSection {
  key: string
  label: string
  weight_percent: number
  required: boolean
}

export interface PerformanceReviewTemplate {
  sections: PerformanceReviewTemplateSection[]
  rating_scale: {
    min: number
    max: number
  }
}

export interface PerformanceReviewParticipantRules {
  population: {
    employment_statuses: string[]
    employment_types: string[]
    department_ids: number[]
    designation_ids: number[]
  }
  reviewers: {
    self_review_required: boolean
    manager_review_required: boolean
    peer_reviewer_slots: number
    allow_hr_reviewer: boolean
  }
}

export interface PerformanceCompetencyVisibility {
  enabled: boolean
  visible_to_employee: boolean
  visible_to_manager: boolean
  visible_to_hr: boolean
  required_competency_ids: number[]
}

export interface PerformanceReviewCycleRecord {
  id: number
  code: string
  name: string
  cycle_type: 'annual' | 'half_yearly' | 'quarterly' | 'probation' | 'project'
  starts_on: string
  ends_on: string
  self_review_due_on: string | null
  manager_review_due_on: string | null
  calibration_starts_on: string | null
  calibration_ends_on: string | null
  publish_on: string | null
  participant_rules: PerformanceReviewParticipantRules
  review_template: PerformanceReviewTemplate
  competency_visibility: PerformanceCompetencyVisibility
  status: PerformanceReviewCycleStatus
  goal_count?: number
  can_edit_configuration: boolean
  created_at: string | null
  updated_at: string | null
}

export interface PerformanceGoalRecord {
  id: number
  goal_code: string
  goal_type: 'library'
  title: string
  description: string | null
  review_cycle?: {
    id: number
    code: string
    name: string
    status: PerformanceReviewCycleStatus
  } | null
  owner_employee: EmployeeReference | null
  department: {
    id: number
    code: string
    name: string
  } | null
  due_on: string
  weight_percent: number
  success_metric: {
    measure_type: string | null
    target_value: string | number | null
    unit: string | null
    notes: string | null
  } | null
  status: PerformanceGoalStatus
  can_edit_configuration: boolean
  created_at: string | null
  updated_at: string | null
}

export interface PerformanceReviewGoalSnapshot {
  id: number
  goal_code: string
  title: string
  description: string | null
  due_on: string | null
  weight_percent: number
  success_metric: {
    measure_type: string | null
    target_value: string | number | null
    unit: string | null
    notes: string | null
  } | null
  status: PerformanceGoalStatus
}

export interface PerformanceReviewCompetencySnapshot {
  id: number
  code: string
  name: string
  category: string
  scale_definition: PerformanceScaleDefinition
}

export interface PerformanceReviewVisibilityRules {
  employee_can_view_manager_assessment_before_publish: boolean
  employee_can_view_peer_feedback_after_publish: boolean
  peer_feedback_anonymous_to_employee: boolean
  manager_can_view_peer_feedback: boolean
  reviewer_can_view_other_reviewer_feedback: boolean
}

export interface PerformanceSubmissionSection {
  key: string
  rating: number
  comment: string | null
}

export interface PerformanceSubmissionCompetency {
  competency_id: number
  rating: number
  comment: string | null
}

export interface PerformanceReviewSubmissionRecord {
  id: number
  role_type: 'self' | 'manager' | 'reviewer'
  submitted_by: {
    id: number | null
    name: string | null
    employee_id: number | null
  } | null
  is_anonymous_to_current_user: boolean
  overall_rating: number
  summary: string
  confidential_notes: string | null
  section_payload: PerformanceSubmissionSection[]
  competency_payload: PerformanceSubmissionCompetency[]
  submitted_at: string | null
}

export interface PerformanceCalibrationPayload {
  overall_rating: number
  summary: string
  confidential_notes: string | null
  section_adjustments: Array<{
    key: string
    calibrated_rating: number
    note: string | null
  }>
  competency_adjustments: Array<{
    competency_id: number
    calibrated_rating: number
    note: string | null
  }>
}

export interface PerformanceFinalPayload {
  final_rating: number
  summary: string
  employee_visible_summary: string
  recommendation: string | null
  finalized_by_user_id: number
  finalized_by_name: string
}

export interface PerformanceReviewRecord {
  id: number
  review_cycle: {
    id: number
    code: string
    name: string
    status: PerformanceReviewCycleStatus
    self_review_due_on: string | null
    manager_review_due_on: string | null
  } | null
  employee: EmployeeReference | null
  manager_employee: EmployeeReference | null
  reviewer_user_ids: number[]
  goal_snapshot: PerformanceReviewGoalSnapshot[]
  competency_snapshot: PerformanceReviewCompetencySnapshot[]
  visibility_rules: PerformanceReviewVisibilityRules
  status: PerformanceReviewStatus
  actor_role: PerformanceReviewActorRole
  submissions: PerformanceReviewSubmissionRecord[]
  calibration_payload?: PerformanceCalibrationPayload | null
  final_payload?: PerformanceFinalPayload | null
  launched_at: string | null
  self_submitted_at: string | null
  manager_submitted_at: string | null
  calibration_completed_at: string | null
  finalized_at: string | null
  published_at: string | null
  reopened_at: string | null
  reopen_count: number
  reopened_reason: string | null
  created_at: string | null
  updated_at: string | null
}

export interface PerformanceWorkspaceData {
  goals: PerformanceGoalRecord[]
  competencies: PerformanceCompetencyRecord[]
  reviewCycles: PerformanceReviewCycleRecord[]
  reviews: PerformanceReviewRecord[]
  employees: EmployeeRecord[]
  meta: {
    can_manage: boolean
    can_review: boolean
    can_calibrate: boolean
    linked_employee_id: number | null
  }
}

export interface PaginatedPerformanceCollection<T> {
  items: T[]
  meta: PaginationMeta
}

export interface CreatePerformanceGoalInput {
  goal_code: string
  goal_type: 'library'
  title: string
  description?: string | null
  performance_review_cycle_id?: number | null
  owner_employee_id: number
  department_id?: number | null
  due_on: string
  weight_percent: number
  success_metric?: PerformanceGoalRecord['success_metric'] | null
  status: PerformanceGoalStatus
}

export interface CreatePerformanceCompetencyInput {
  code: string
  name: string
  category: string
  description?: string | null
  scale_definition: PerformanceScaleDefinition
  status: PerformanceCompetencyRecord['status']
}

export interface CreatePerformanceReviewCycleInput {
  code: string
  name: string
  cycle_type: PerformanceReviewCycleRecord['cycle_type']
  starts_on: string
  ends_on: string
  self_review_due_on?: string | null
  manager_review_due_on?: string | null
  calibration_starts_on?: string | null
  calibration_ends_on?: string | null
  publish_on?: string | null
  participant_rules: PerformanceReviewParticipantRules
  review_template: PerformanceReviewTemplate
  competency_visibility: PerformanceCompetencyVisibility
  status: PerformanceReviewCycleStatus
}

export interface CreatePerformanceReviewInput {
  performance_review_cycle_id: number
  employee_id: number
  reviewer_user_ids?: number[]
  visibility_rules?: Partial<PerformanceReviewVisibilityRules>
  launch_immediately?: boolean
}

export interface SubmitPerformanceReviewInput {
  sections: PerformanceSubmissionSection[]
  competencies?: PerformanceSubmissionCompetency[]
  overall_rating: number
  summary: string
  confidential_notes?: string | null
}

export interface CalibratePerformanceReviewInput {
  overall_rating: number
  summary: string
  confidential_notes?: string | null
  section_adjustments?: PerformanceCalibrationPayload['section_adjustments']
  competency_adjustments?: PerformanceCalibrationPayload['competency_adjustments']
}

export interface FinalizePerformanceReviewInput {
  final_rating: number
  summary: string
  employee_visible_summary: string
  recommendation?: string | null
}
