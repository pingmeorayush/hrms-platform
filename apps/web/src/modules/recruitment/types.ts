export type RecruitmentRequisitionStatus =
  | 'draft'
  | 'submitted'
  | 'approved'
  | 'on_hold'
  | 'closed'
  | 'rejected'
  | 'changes_requested'

export type RecruitmentCandidateStage =
  | 'applied'
  | 'screening'
  | 'shortlisted'
  | 'interview'
  | 'offer'
  | 'hired'
  | 'rejected'
  | 'withdrawn'

export type RecruitmentCandidateStatus = 'active' | 'hired' | 'rejected' | 'withdrawn'

export type RecruitmentInterviewStatus = 'scheduled' | 'completed' | 'cancelled'
export type RecruitmentInterviewType = 'screening' | 'technical' | 'managerial' | 'hr' | 'culture'
export type RecruitmentMeetingMode = 'virtual' | 'onsite' | 'phone'

export type RecruitmentOfferStatus =
  | 'draft'
  | 'submitted'
  | 'approved'
  | 'rejected'
  | 'changes_requested'
  | 'sent'
  | 'accepted'
  | 'declined'
  | 'expired'

export type RecruitmentOfferAction =
  | 'submit'
  | 'approve'
  | 'reject'
  | 'request_changes'
  | 'mark_sent'
  | 'record_acceptance'
  | 'record_decline'
  | 'mark_expired'

export type RecruitmentHandoffStatus = 'employee_created' | 'onboarding_queued' | 'onboarding_skipped'

export interface RecruitmentUserReference {
  id: number
  name: string
  email: string | null
}

export interface RecruitmentEmployeeReference {
  id: number
  employee_code: string
  full_name: string
  email: string | null
}

export interface RecruitmentDepartmentReference {
  id: number
  code: string
  name: string
}

export interface RecruitmentDesignationReference {
  id: number
  code: string
  name: string
}

export interface RecruitmentLocationReference {
  id: number
  code: string
  name: string
}

export interface RecruitmentCostCenterReference {
  id: number
  code: string
  name: string
}

export interface RecruitmentWorkflowTaskRecord {
  id: number
  stage_key: string
  stage_name: string
  sequence: number
  status: string
  available_actions: string[]
  decision: string | null
  decision_comment: string | null
  due_at: string | null
  acted_at: string | null
  assigned_to_role: string | null
  assignee: RecruitmentUserReference | null
  actor: RecruitmentUserReference | null
}

export interface RecruitmentWorkflowRecord {
  id: number
  reference_type: string
  reference_id: number
  status: string
  current_stage_sequence: number | null
  payload: Record<string, unknown>
  completed_at: string | null
  rejected_at: string | null
  definition?: {
    id: number
    key: string
    name: string
  } | null
  starter?: RecruitmentUserReference | null
  tasks?: RecruitmentWorkflowTaskRecord[]
}

export interface RecruitmentJobRequisitionReference {
  id: number
  requisition_code: string
  title: string
  status: RecruitmentRequisitionStatus
}

export interface RecruitmentJobRequisitionRecord extends RecruitmentJobRequisitionReference {
  employment_type: 'full_time' | 'contract' | 'intern' | 'consultant' | 'temporary'
  hiring_type: 'new_position' | 'backfill' | 'replacement'
  priority: 'low' | 'medium' | 'high' | 'critical'
  openings_count: number
  min_experience_years: number | null
  target_start_date: string | null
  headcount_reference: string | null
  department: RecruitmentDepartmentReference | null
  designation: RecruitmentDesignationReference | null
  location: RecruitmentLocationReference | null
  cost_center: RecruitmentCostCenterReference | null
  recruiter: RecruitmentUserReference | null
  hiring_manager: RecruitmentEmployeeReference | null
  requested_by: RecruitmentUserReference | null
  workflow_instance_id: number | null
  workflow?: RecruitmentWorkflowRecord | null
  status_before_hold: RecruitmentRequisitionStatus | null
  justification: string
  notes: string | null
  closed_reason: string | null
  can_edit_details: boolean
  can_submit: boolean
  can_put_on_hold: boolean
  can_resume: boolean
  can_close: boolean
  submitted_at: string | null
  approved_at: string | null
  on_hold_at: string | null
  closed_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface RecruitmentCandidateResumeRecord {
  id: number
  version_number: number
  is_current: boolean
  original_file_name: string
  mime_type: string
  file_size_bytes: number
  checksum_sha256: string
  notes: string | null
  uploaded_by: RecruitmentUserReference | null
  download_url: string
  created_at: string | null
}

export interface RecruitmentCandidateStageTransitionRecord {
  id: number
  from_stage: RecruitmentCandidateStage | null
  to_stage: RecruitmentCandidateStage
  resulting_status: RecruitmentCandidateStatus
  comment: string | null
  transitioned_by: RecruitmentUserReference | null
  transitioned_at: string | null
}

export interface RecruitmentCandidateRecord {
  id: number
  candidate_code: string
  first_name: string
  last_name: string | null
  full_name: string
  email: string
  phone: string | null
  source: 'manual' | 'career_portal' | 'referral' | 'agency' | 'campus' | 'social'
  current_stage: RecruitmentCandidateStage
  status: RecruitmentCandidateStatus
  stage_entered_at: string | null
  total_experience_years: number | null
  notice_period_days: number | null
  current_company: string | null
  current_title: string | null
  summary: string | null
  notes: string | null
  recruiter: RecruitmentUserReference | null
  requisition: RecruitmentJobRequisitionReference | null
  resume_count?: number
  latest_resume?: RecruitmentCandidateResumeRecord | null
  resumes?: RecruitmentCandidateResumeRecord[]
  stage_history?: RecruitmentCandidateStageTransitionRecord[]
  created_at: string | null
  updated_at: string | null
}

export interface RecruitmentInterviewFeedbackRecord {
  id: number
  rating: number
  recommendation: 'strong_hire' | 'hire' | 'hold' | 'no_hire'
  comments: string
  strengths: string | null
  concerns: string | null
  interviewer: RecruitmentUserReference | null
  created_at: string | null
}

export interface RecruitmentInterviewRecord {
  id: number
  interview_code: string
  round_number: number
  interview_type: RecruitmentInterviewType
  status: RecruitmentInterviewStatus
  timezone: string
  scheduled_start_at: string | null
  scheduled_end_at: string | null
  meeting_mode: RecruitmentMeetingMode
  meeting_location: string | null
  meeting_link: string | null
  agenda: string | null
  cancellation_reason: string | null
  candidate: RecruitmentCandidateRecord | null
  requisition: RecruitmentJobRequisitionReference | null
  interviewer: RecruitmentUserReference | null
  feedback: RecruitmentInterviewFeedbackRecord | null
  created_at: string | null
  updated_at: string | null
}

export interface RecruitmentOfferDecisionRecord {
  id: number
  from_status: RecruitmentOfferStatus | null
  to_status: RecruitmentOfferStatus
  decision_type: string
  comment: string | null
  actor: RecruitmentUserReference | null
  acted_at: string | null
}

export interface RecruitmentHireHandoffSummary {
  id: number
  status: RecruitmentHandoffStatus
  employee: RecruitmentEmployeeReference | null
  converted_at: string | null
  onboarding_triggered_at: string | null
}

export interface RecruitmentOfferRecord {
  id: number
  offer_code: string
  status: RecruitmentOfferStatus
  employment_type: 'full_time' | 'contract' | 'intern' | 'consultant' | 'temporary'
  currency: string
  annual_ctc_amount: number
  joining_bonus_amount: number | null
  proposed_start_date: string | null
  expires_on: string | null
  notes: string | null
  candidate_message: string | null
  candidate: RecruitmentCandidateRecord | null
  requisition: RecruitmentJobRequisitionReference | null
  recruiter: RecruitmentUserReference | null
  requested_by: RecruitmentUserReference | null
  workflow_instance_id: number | null
  workflow?: RecruitmentWorkflowRecord | null
  hire_handoff?: RecruitmentHireHandoffSummary | null
  decision_history?: RecruitmentOfferDecisionRecord[]
  submitted_at: string | null
  approved_at: string | null
  sent_at: string | null
  accepted_at: string | null
  declined_at: string | null
  expired_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface RecruitmentHireHandoffRecord {
  id: number
  status: RecruitmentHandoffStatus
  offer: {
    id: number
    offer_code: string
    status: RecruitmentOfferStatus
    employment_type: string
    proposed_start_date: string | null
    expires_on: string | null
  } | null
  candidate: RecruitmentCandidateRecord | null
  requisition: RecruitmentJobRequisitionReference | null
  employee: RecruitmentEmployeeReference | null
  recruiter: RecruitmentUserReference | null
  converted_by: RecruitmentUserReference | null
  source_resume: {
    id: number
    version_number: number
    original_file_name: string
  } | null
  offer_snapshot: Record<string, unknown>
  candidate_snapshot: Record<string, unknown>
  requisition_snapshot: Record<string, unknown>
  document_references: Array<Record<string, unknown>>
  onboarding_template_ids: number[]
  onboarding_task_ids: number[]
  notes: string | null
  converted_at: string | null
  onboarding_triggered_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface RecruitmentDirectoryRecord {
  recruiters: RecruitmentUserReference[]
  interviewers: RecruitmentUserReference[]
  hiring_managers: RecruitmentEmployeeReference[]
}

export interface RecruitmentWorkspaceData {
  requisitions: RecruitmentJobRequisitionRecord[]
  candidates: RecruitmentCandidateRecord[]
  interviews: RecruitmentInterviewRecord[]
  offers: RecruitmentOfferRecord[]
  handoffs: RecruitmentHireHandoffRecord[]
  directory: RecruitmentDirectoryRecord
}

export interface RecruitmentRequisitionFilters {
  status: '' | RecruitmentRequisitionStatus
  priority: '' | RecruitmentJobRequisitionRecord['priority']
  q: string
}

export interface RecruitmentCandidateFilters {
  stage: '' | RecruitmentCandidateStage
  status: '' | RecruitmentCandidateStatus
  requisitionId: string
  q: string
}

export interface ScheduleRecruitmentInterviewInput {
  job_requisition_id: number
  candidate_id: number
  interviewer_user_id: number
  round_number: number
  interview_type: RecruitmentInterviewType
  timezone: string
  scheduled_start_at: string
  scheduled_end_at: string
  meeting_mode: RecruitmentMeetingMode
  meeting_location?: string | null
  meeting_link?: string | null
  agenda?: string | null
}

export interface SubmitRecruitmentInterviewFeedbackInput {
  rating: number
  recommendation: 'strong_hire' | 'hire' | 'hold' | 'no_hire'
  comments: string
  strengths?: string | null
  concerns?: string | null
}

export interface CreateRecruitmentOfferInput {
  job_requisition_id: number
  candidate_id: number
  recruiter_user_id?: number | null
  employment_type: RecruitmentOfferRecord['employment_type']
  currency: string
  annual_ctc_amount: number
  joining_bonus_amount?: number | null
  proposed_start_date?: string | null
  expires_on: string
  notes?: string | null
  candidate_message?: string | null
}

export interface UpdateRecruitmentOfferInput {
  action?: RecruitmentOfferAction
  comment?: string | null
  recruiter_user_id?: number | null
  employment_type?: RecruitmentOfferRecord['employment_type']
  currency?: string
  annual_ctc_amount?: number
  joining_bonus_amount?: number | null
  proposed_start_date?: string | null
  expires_on?: string
  notes?: string | null
  candidate_message?: string | null
}

export interface CreateRecruitmentHandoffInput {
  trigger_onboarding?: boolean
  notes?: string | null
}
