import type { AccessSnapshot } from '../../access/types'
import type {
  RecruitmentCandidateRecord,
  RecruitmentCandidateStatus,
  RecruitmentCandidateStageTransitionRecord,
  RecruitmentEmployeeReference,
  RecruitmentHireHandoffRecord,
  RecruitmentInterviewRecord,
  RecruitmentJobRequisitionRecord,
  RecruitmentOfferRecord,
  RecruitmentUserReference,
  RecruitmentWorkspaceData,
} from '../types'

function createUser(id: number, name: string, email: string): RecruitmentUserReference {
  return { id, name, email }
}

function createEmployee(id: number, code: string, fullName: string, email: string): RecruitmentEmployeeReference {
  return { id, employee_code: code, full_name: fullName, email }
}

const recruiterPrimary = createUser(6, 'Sonia Menon', 'sonia.menon@phoenixhrms.test')
const recruiterSecondary = createUser(2, 'Nisha Rao', 'nisha.rao@phoenixhrms.test')
const interviewerTech = createUser(7, 'Rahul Khanna', 'rahul.khanna@phoenixhrms.test')
const interviewerHr = createUser(8, 'Mira Shah', 'mira.shah@phoenixhrms.test')
const hiringManager = createEmployee(2101, 'PAY-2101', 'Aman Verma', 'aman.verma@phoenixhrms.test')
const hiringManager2 = createEmployee(2104, 'PAY-2104', 'Leena Iyer', 'leena.iyer@phoenixhrms.test')

function createStageHistory(
  actor: RecruitmentUserReference,
  items: Array<{
    id: number
    from_stage: RecruitmentCandidateRecord['current_stage'] | null
    to_stage: RecruitmentCandidateRecord['current_stage']
    resulting_status: RecruitmentCandidateStatus
    comment: string | null
    transitioned_at: string
  }>,
): RecruitmentCandidateStageTransitionRecord[] {
  return items.map((item) => ({
    ...item,
    transitioned_by: actor,
  }))
}

const requisitions: RecruitmentJobRequisitionRecord[] = [
  {
    id: 301,
    requisition_code: 'REQ-ENG-301',
    title: 'Senior Frontend Engineer',
    employment_type: 'full_time',
    hiring_type: 'new_position',
    priority: 'high',
    openings_count: 2,
    min_experience_years: 5,
    target_start_date: '2026-07-15',
    headcount_reference: 'FY26-GROWTH-12',
    department: { id: 11, code: 'ENG', name: 'Engineering' },
    designation: { id: 21, code: 'SFE', name: 'Senior Frontend Engineer' },
    location: { id: 31, code: 'BLR', name: 'Bengaluru' },
    cost_center: { id: 41, code: 'CC-ENG', name: 'Engineering Delivery' },
    recruiter: recruiterPrimary,
    hiring_manager: hiringManager,
    requested_by: recruiterSecondary,
    workflow_instance_id: 9301,
    workflow: {
      id: 9301,
      reference_type: 'job_requisition',
      reference_id: 301,
      status: 'approved',
      current_stage_sequence: 2,
      payload: {},
      completed_at: '2026-06-04T11:00:00Z',
      rejected_at: null,
      definition: { id: 1, key: 'recruitment-requisition-approval', name: 'Requisition Approval' },
      starter: recruiterPrimary,
      tasks: [
        {
          id: 9801,
          stage_key: 'hiring_manager_review',
          stage_name: 'Hiring manager review',
          sequence: 1,
          status: 'approved',
          available_actions: [],
          decision: 'approve',
          decision_comment: 'Role is aligned with Q3 delivery.',
          due_at: null,
          acted_at: '2026-06-03T10:00:00Z',
          assigned_to_role: 'manager',
          assignee: { id: 3, name: 'Manager Reviewer', email: 'manager@phoenixhrms.test' },
          actor: { id: 3, name: 'Manager Reviewer', email: 'manager@phoenixhrms.test' },
        },
        {
          id: 9802,
          stage_key: 'hr_review',
          stage_name: 'HR review',
          sequence: 2,
          status: 'approved',
          available_actions: [],
          decision: 'approve',
          decision_comment: 'Approved for immediate sourcing.',
          due_at: null,
          acted_at: '2026-06-04T11:00:00Z',
          assigned_to_role: 'hr',
          assignee: recruiterSecondary,
          actor: recruiterSecondary,
        },
      ],
    },
    status: 'approved',
    status_before_hold: null,
    justification: 'Expand the product engineering pod for the new design system rollout.',
    notes: 'Focus on React and design systems candidates.',
    closed_reason: null,
    can_edit_details: false,
    can_submit: false,
    can_put_on_hold: true,
    can_resume: false,
    can_close: true,
    submitted_at: '2026-06-02T08:30:00Z',
    approved_at: '2026-06-04T11:00:00Z',
    on_hold_at: null,
    closed_at: null,
    created_at: '2026-06-01T09:00:00Z',
    updated_at: '2026-06-04T11:00:00Z',
  },
  {
    id: 302,
    requisition_code: 'REQ-DAT-302',
    title: 'Data Analyst',
    employment_type: 'full_time',
    hiring_type: 'backfill',
    priority: 'medium',
    openings_count: 1,
    min_experience_years: 3,
    target_start_date: '2026-08-01',
    headcount_reference: 'FY26-BACKFILL-07',
    department: { id: 12, code: 'ANA', name: 'Analytics' },
    designation: { id: 22, code: 'DA', name: 'Data Analyst' },
    location: { id: 32, code: 'HYD', name: 'Hyderabad' },
    cost_center: { id: 42, code: 'CC-ANA', name: 'Analytics Operations' },
    recruiter: recruiterPrimary,
    hiring_manager: hiringManager2,
    requested_by: recruiterSecondary,
    workflow_instance_id: 9302,
    workflow: {
      id: 9302,
      reference_type: 'job_requisition',
      reference_id: 302,
      status: 'in_progress',
      current_stage_sequence: 1,
      payload: {},
      completed_at: null,
      rejected_at: null,
      definition: { id: 1, key: 'recruitment-requisition-approval', name: 'Requisition Approval' },
      starter: recruiterPrimary,
      tasks: [
        {
          id: 9803,
          stage_key: 'hiring_manager_review',
          stage_name: 'Hiring manager review',
          sequence: 1,
          status: 'pending',
          available_actions: ['approve', 'reject', 'request_changes'],
          decision: null,
          decision_comment: null,
          due_at: '2026-06-12T18:30:00Z',
          acted_at: null,
          assigned_to_role: 'manager',
          assignee: { id: 9, name: 'Leena Iyer', email: 'leena.iyer@phoenixhrms.test' },
          actor: null,
        },
      ],
    },
    status: 'submitted',
    status_before_hold: null,
    justification: 'Replace resigned analyst before the planning cycle.',
    notes: 'Need SQL and stakeholder reporting depth.',
    closed_reason: null,
    can_edit_details: false,
    can_submit: false,
    can_put_on_hold: false,
    can_resume: false,
    can_close: true,
    submitted_at: '2026-06-09T08:00:00Z',
    approved_at: null,
    on_hold_at: null,
    closed_at: null,
    created_at: '2026-06-08T10:00:00Z',
    updated_at: '2026-06-09T08:00:00Z',
  },
  {
    id: 303,
    requisition_code: 'REQ-HRB-303',
    title: 'HR Business Partner',
    employment_type: 'full_time',
    hiring_type: 'replacement',
    priority: 'critical',
    openings_count: 1,
    min_experience_years: 7,
    target_start_date: '2026-07-05',
    headcount_reference: 'FY26-CRITICAL-03',
    department: { id: 13, code: 'HR', name: 'People Operations' },
    designation: { id: 23, code: 'HRBP', name: 'HR Business Partner' },
    location: { id: 31, code: 'BLR', name: 'Bengaluru' },
    cost_center: { id: 43, code: 'CC-HR', name: 'People Experience' },
    recruiter: recruiterSecondary,
    hiring_manager: hiringManager,
    requested_by: recruiterSecondary,
    workflow_instance_id: null,
    workflow: null,
    status: 'on_hold',
    status_before_hold: 'approved',
    justification: 'Backfill people partner coverage for the product group.',
    notes: 'Budget confirmation pending for L7 band.',
    closed_reason: null,
    can_edit_details: true,
    can_submit: false,
    can_put_on_hold: false,
    can_resume: true,
    can_close: true,
    submitted_at: '2026-05-28T09:00:00Z',
    approved_at: '2026-05-30T12:00:00Z',
    on_hold_at: '2026-06-07T10:30:00Z',
    closed_at: null,
    created_at: '2026-05-26T09:00:00Z',
    updated_at: '2026-06-07T10:30:00Z',
  },
]

const candidates: RecruitmentCandidateRecord[] = [
  {
    id: 401,
    candidate_code: 'CAN-401',
    first_name: 'Rhea',
    last_name: 'Kapoor',
    full_name: 'Rhea Kapoor',
    email: 'rhea.kapoor@example.com',
    phone: '+91 98765 00111',
    source: 'referral',
    current_stage: 'offer',
    status: 'active',
    stage_entered_at: '2026-06-08T09:30:00Z',
    total_experience_years: 6,
    notice_period_days: 30,
    current_company: 'Northwind Labs',
    current_title: 'Staff Frontend Engineer',
    summary: 'Design-system heavy frontend lead with React, TypeScript, and platform migration experience.',
    notes: 'Strong interviewer alignment and culture fit.',
    recruiter: recruiterPrimary,
    requisition: { id: 301, requisition_code: 'REQ-ENG-301', title: 'Senior Frontend Engineer', status: 'approved' },
    resume_count: 2,
    latest_resume: {
      id: 4501,
      version_number: 2,
      is_current: true,
      original_file_name: 'rhea-kapoor-resume-v2.pdf',
      mime_type: 'application/pdf',
      file_size_bytes: 223344,
      checksum_sha256: 'resume-rhea-v2',
      notes: 'Updated portfolio links added.',
      uploaded_by: recruiterPrimary,
      download_url: '/api/v1/recruitment/candidates/401/resumes/4501/download',
      created_at: '2026-06-07T09:00:00Z',
    },
    resumes: [
      {
        id: 4501,
        version_number: 2,
        is_current: true,
        original_file_name: 'rhea-kapoor-resume-v2.pdf',
        mime_type: 'application/pdf',
        file_size_bytes: 223344,
        checksum_sha256: 'resume-rhea-v2',
        notes: 'Updated portfolio links added.',
        uploaded_by: recruiterPrimary,
        download_url: '/api/v1/recruitment/candidates/401/resumes/4501/download',
        created_at: '2026-06-07T09:00:00Z',
      },
      {
        id: 4500,
        version_number: 1,
        is_current: false,
        original_file_name: 'rhea-kapoor-resume.pdf',
        mime_type: 'application/pdf',
        file_size_bytes: 201144,
        checksum_sha256: 'resume-rhea-v1',
        notes: 'Original referral submission.',
        uploaded_by: recruiterPrimary,
        download_url: '/api/v1/recruitment/candidates/401/resumes/4500/download',
        created_at: '2026-06-02T08:00:00Z',
      },
    ],
    stage_history: createStageHistory(recruiterPrimary, [
      { id: 4601, from_stage: null, to_stage: 'applied', resulting_status: 'active', comment: 'Referred by design lead.', transitioned_at: '2026-06-02T08:00:00Z' },
      { id: 4602, from_stage: 'applied', to_stage: 'screening', resulting_status: 'active', comment: 'Profile shortlisted for recruiter screen.', transitioned_at: '2026-06-03T09:00:00Z' },
      { id: 4603, from_stage: 'screening', to_stage: 'shortlisted', resulting_status: 'active', comment: 'Screen cleared.', transitioned_at: '2026-06-04T10:30:00Z' },
      { id: 4604, from_stage: 'shortlisted', to_stage: 'interview', resulting_status: 'active', comment: 'Technical and manager rounds scheduled.', transitioned_at: '2026-06-05T12:00:00Z' },
      { id: 4605, from_stage: 'interview', to_stage: 'offer', resulting_status: 'active', comment: 'Move to offer after debrief.', transitioned_at: '2026-06-08T09:30:00Z' },
    ]),
    created_at: '2026-06-02T08:00:00Z',
    updated_at: '2026-06-08T09:30:00Z',
  },
  {
    id: 402,
    candidate_code: 'CAN-402',
    first_name: 'Dev',
    last_name: 'Mishra',
    full_name: 'Dev Mishra',
    email: 'dev.mishra@example.com',
    phone: '+91 98765 00222',
    source: 'manual',
    current_stage: 'interview',
    status: 'active',
    stage_entered_at: '2026-06-09T06:30:00Z',
    total_experience_years: 4,
    notice_period_days: 45,
    current_company: 'SignalStack',
    current_title: 'Frontend Engineer',
    summary: 'Hands-on frontend engineer with strong testing posture and microfrontend exposure.',
    notes: 'Need one more technical panel round.',
    recruiter: recruiterPrimary,
    requisition: { id: 301, requisition_code: 'REQ-ENG-301', title: 'Senior Frontend Engineer', status: 'approved' },
    resume_count: 1,
    latest_resume: {
      id: 4502,
      version_number: 1,
      is_current: true,
      original_file_name: 'dev-mishra-resume.pdf',
      mime_type: 'application/pdf',
      file_size_bytes: 181003,
      checksum_sha256: 'resume-dev-v1',
      notes: null,
      uploaded_by: recruiterPrimary,
      download_url: '/api/v1/recruitment/candidates/402/resumes/4502/download',
      created_at: '2026-06-04T08:30:00Z',
    },
    resumes: [
      {
        id: 4502,
        version_number: 1,
        is_current: true,
        original_file_name: 'dev-mishra-resume.pdf',
        mime_type: 'application/pdf',
        file_size_bytes: 181003,
        checksum_sha256: 'resume-dev-v1',
        notes: null,
        uploaded_by: recruiterPrimary,
        download_url: '/api/v1/recruitment/candidates/402/resumes/4502/download',
        created_at: '2026-06-04T08:30:00Z',
      },
    ],
    stage_history: createStageHistory(recruiterPrimary, [
      { id: 4606, from_stage: null, to_stage: 'applied', resulting_status: 'active', comment: 'Direct application.', transitioned_at: '2026-06-04T08:30:00Z' },
      { id: 4607, from_stage: 'applied', to_stage: 'screening', resulting_status: 'active', comment: 'Resume review complete.', transitioned_at: '2026-06-05T08:30:00Z' },
      { id: 4608, from_stage: 'screening', to_stage: 'shortlisted', resulting_status: 'active', comment: 'Phone screen positive.', transitioned_at: '2026-06-07T07:00:00Z' },
      { id: 4609, from_stage: 'shortlisted', to_stage: 'interview', resulting_status: 'active', comment: 'Panel rounds started.', transitioned_at: '2026-06-09T06:30:00Z' },
    ]),
    created_at: '2026-06-04T08:30:00Z',
    updated_at: '2026-06-09T06:30:00Z',
  },
  {
    id: 403,
    candidate_code: 'CAN-403',
    first_name: 'Sara',
    last_name: 'Thomas',
    full_name: 'Sara Thomas',
    email: 'sara.thomas@example.com',
    phone: '+91 98765 00333',
    source: 'agency',
    current_stage: 'screening',
    status: 'active',
    stage_entered_at: '2026-06-10T05:00:00Z',
    total_experience_years: 3,
    notice_period_days: 60,
    current_company: 'Kite Data',
    current_title: 'Business Analyst',
    summary: 'Analytics candidate with SQL and Looker delivery depth.',
    notes: 'Pending recruiter screening notes.',
    recruiter: recruiterPrimary,
    requisition: { id: 302, requisition_code: 'REQ-DAT-302', title: 'Data Analyst', status: 'submitted' },
    resume_count: 1,
    latest_resume: {
      id: 4503,
      version_number: 1,
      is_current: true,
      original_file_name: 'sara-thomas-resume.pdf',
      mime_type: 'application/pdf',
      file_size_bytes: 176882,
      checksum_sha256: 'resume-sara-v1',
      notes: null,
      uploaded_by: recruiterPrimary,
      download_url: '/api/v1/recruitment/candidates/403/resumes/4503/download',
      created_at: '2026-06-10T05:00:00Z',
    },
    resumes: [],
    stage_history: createStageHistory(recruiterPrimary, [
      { id: 4610, from_stage: null, to_stage: 'applied', resulting_status: 'active', comment: 'Agency profile received.', transitioned_at: '2026-06-09T09:00:00Z' },
      { id: 4611, from_stage: 'applied', to_stage: 'screening', resulting_status: 'active', comment: 'Queue for recruiter review.', transitioned_at: '2026-06-10T05:00:00Z' },
    ]),
    created_at: '2026-06-09T09:00:00Z',
    updated_at: '2026-06-10T05:00:00Z',
  },
  {
    id: 404,
    candidate_code: 'CAN-404',
    first_name: 'Aakash',
    last_name: 'Sen',
    full_name: 'Aakash Sen',
    email: 'aakash.sen@example.com',
    phone: '+91 98765 00444',
    source: 'manual',
    current_stage: 'hired',
    status: 'hired',
    stage_entered_at: '2026-06-09T12:00:00Z',
    total_experience_years: 7,
    notice_period_days: 0,
    current_company: 'LatticeWorks',
    current_title: 'People Partner',
    summary: 'Senior HRBP profile with org design and employee relations experience.',
    notes: 'Accepted offer and onboarding queued.',
    recruiter: recruiterSecondary,
    requisition: { id: 303, requisition_code: 'REQ-HRB-303', title: 'HR Business Partner', status: 'on_hold' },
    resume_count: 1,
    latest_resume: {
      id: 4504,
      version_number: 1,
      is_current: true,
      original_file_name: 'aakash-sen-resume.pdf',
      mime_type: 'application/pdf',
      file_size_bytes: 198221,
      checksum_sha256: 'resume-aakash-v1',
      notes: null,
      uploaded_by: recruiterSecondary,
      download_url: '/api/v1/recruitment/candidates/404/resumes/4504/download',
      created_at: '2026-05-28T09:30:00Z',
    },
    resumes: [],
    stage_history: createStageHistory(recruiterSecondary, [
      { id: 4612, from_stage: null, to_stage: 'applied', resulting_status: 'active', comment: 'Sourced via recruiter network.', transitioned_at: '2026-05-28T09:30:00Z' },
      { id: 4613, from_stage: 'applied', to_stage: 'screening', resulting_status: 'active', comment: 'Initial screening completed.', transitioned_at: '2026-05-29T10:00:00Z' },
      { id: 4614, from_stage: 'screening', to_stage: 'shortlisted', resulting_status: 'active', comment: 'Progress to manager discussion.', transitioned_at: '2026-05-30T09:00:00Z' },
      { id: 4615, from_stage: 'shortlisted', to_stage: 'interview', resulting_status: 'active', comment: 'Panel complete.', transitioned_at: '2026-05-31T12:30:00Z' },
      { id: 4616, from_stage: 'interview', to_stage: 'offer', resulting_status: 'active', comment: 'Offer initiated.', transitioned_at: '2026-06-03T08:00:00Z' },
      { id: 4617, from_stage: 'offer', to_stage: 'hired', resulting_status: 'hired', comment: 'Accepted offer and handoff created.', transitioned_at: '2026-06-09T12:00:00Z' },
    ]),
    created_at: '2026-05-28T09:30:00Z',
    updated_at: '2026-06-09T12:00:00Z',
  },
  {
    id: 405,
    candidate_code: 'CAN-405',
    first_name: 'Priya',
    last_name: 'Joshi',
    full_name: 'Priya Joshi',
    email: 'priya.joshi@example.com',
    phone: '+91 98765 00555',
    source: 'social',
    current_stage: 'rejected',
    status: 'rejected',
    stage_entered_at: '2026-06-06T14:30:00Z',
    total_experience_years: 2,
    notice_period_days: 30,
    current_company: 'LightFrame',
    current_title: 'Frontend Developer',
    summary: 'Promising developer but not yet at senior hiring bar.',
    notes: 'Keep warm for mid-level pool.',
    recruiter: recruiterPrimary,
    requisition: { id: 301, requisition_code: 'REQ-ENG-301', title: 'Senior Frontend Engineer', status: 'approved' },
    resume_count: 1,
    latest_resume: {
      id: 4505,
      version_number: 1,
      is_current: true,
      original_file_name: 'priya-joshi-resume.pdf',
      mime_type: 'application/pdf',
      file_size_bytes: 165901,
      checksum_sha256: 'resume-priya-v1',
      notes: null,
      uploaded_by: recruiterPrimary,
      download_url: '/api/v1/recruitment/candidates/405/resumes/4505/download',
      created_at: '2026-06-02T09:30:00Z',
    },
    resumes: [],
    stage_history: createStageHistory(recruiterPrimary, [
      { id: 4618, from_stage: null, to_stage: 'applied', resulting_status: 'active', comment: 'Inbound social application.', transitioned_at: '2026-06-02T09:30:00Z' },
      { id: 4619, from_stage: 'applied', to_stage: 'screening', resulting_status: 'active', comment: 'Resume reviewed.', transitioned_at: '2026-06-03T11:00:00Z' },
      { id: 4620, from_stage: 'screening', to_stage: 'rejected', resulting_status: 'rejected', comment: 'Not enough senior ownership examples.', transitioned_at: '2026-06-06T14:30:00Z' },
    ]),
    created_at: '2026-06-02T09:30:00Z',
    updated_at: '2026-06-06T14:30:00Z',
  },
]

const offers: RecruitmentOfferRecord[] = [
  {
    id: 501,
    offer_code: 'OFF-501',
    status: 'accepted',
    employment_type: 'full_time',
    currency: 'INR',
    annual_ctc_amount: 2800000,
    joining_bonus_amount: 150000,
    proposed_start_date: '2026-07-15',
    expires_on: '2026-06-20',
    notes: 'Aligns with band and remote flexibility request.',
    candidate_message: 'We are excited to move forward.',
    candidate: candidates[0],
    requisition: candidates[0].requisition,
    recruiter: recruiterPrimary,
    requested_by: recruiterPrimary,
    workflow_instance_id: 9501,
    workflow: {
      id: 9501,
      reference_type: 'offer',
      reference_id: 501,
      status: 'completed',
      current_stage_sequence: 2,
      payload: {},
      completed_at: '2026-06-09T09:00:00Z',
      rejected_at: null,
      definition: { id: 2, key: 'recruitment-offer-approval', name: 'Offer Approval' },
      starter: recruiterPrimary,
      tasks: [],
    },
    hire_handoff: null,
    decision_history: [
      { id: 9701, from_status: 'draft', to_status: 'submitted', decision_type: 'submit', comment: 'Ready for approval.', actor: recruiterPrimary, acted_at: '2026-06-08T10:00:00Z' },
      { id: 9702, from_status: 'submitted', to_status: 'approved', decision_type: 'approve', comment: 'Approved.', actor: { id: 3, name: 'Manager Reviewer', email: 'manager@phoenixhrms.test' }, acted_at: '2026-06-08T14:00:00Z' },
      { id: 9703, from_status: 'approved', to_status: 'sent', decision_type: 'mark_sent', comment: 'Sent to candidate.', actor: recruiterPrimary, acted_at: '2026-06-08T15:00:00Z' },
      { id: 9704, from_status: 'sent', to_status: 'accepted', decision_type: 'record_acceptance', comment: 'Candidate accepted verbally.', actor: recruiterPrimary, acted_at: '2026-06-09T09:00:00Z' },
    ],
    submitted_at: '2026-06-08T10:00:00Z',
    approved_at: '2026-06-08T14:00:00Z',
    sent_at: '2026-06-08T15:00:00Z',
    accepted_at: '2026-06-09T09:00:00Z',
    declined_at: null,
    expired_at: null,
    created_at: '2026-06-07T12:00:00Z',
    updated_at: '2026-06-09T09:00:00Z',
  },
  {
    id: 502,
    offer_code: 'OFF-502',
    status: 'submitted',
    employment_type: 'full_time',
    currency: 'INR',
    annual_ctc_amount: 2400000,
    joining_bonus_amount: null,
    proposed_start_date: '2026-07-28',
    expires_on: '2026-06-18',
    notes: 'Final compensation approval pending.',
    candidate_message: 'Awaiting final internal approval.',
    candidate: candidates[1],
    requisition: candidates[1].requisition,
    recruiter: recruiterPrimary,
    requested_by: recruiterPrimary,
    workflow_instance_id: 9502,
    workflow: {
      id: 9502,
      reference_type: 'offer',
      reference_id: 502,
      status: 'in_progress',
      current_stage_sequence: 1,
      payload: {},
      completed_at: null,
      rejected_at: null,
      definition: { id: 2, key: 'recruitment-offer-approval', name: 'Offer Approval' },
      starter: recruiterPrimary,
      tasks: [
        {
          id: 9804,
          stage_key: 'hiring_manager_review',
          stage_name: 'Hiring manager review',
          sequence: 1,
          status: 'pending',
          available_actions: ['approve', 'reject', 'request_changes'],
          decision: null,
          decision_comment: null,
          due_at: '2026-06-12T18:30:00Z',
          acted_at: null,
          assigned_to_role: 'manager',
          assignee: { id: 3, name: 'Manager Reviewer', email: 'manager@phoenixhrms.test' },
          actor: null,
        },
      ],
    },
    hire_handoff: null,
    decision_history: [
      { id: 9705, from_status: 'draft', to_status: 'submitted', decision_type: 'submit', comment: 'Send for approval.', actor: recruiterPrimary, acted_at: '2026-06-10T05:30:00Z' },
    ],
    submitted_at: '2026-06-10T05:30:00Z',
    approved_at: null,
    sent_at: null,
    accepted_at: null,
    declined_at: null,
    expired_at: null,
    created_at: '2026-06-10T04:30:00Z',
    updated_at: '2026-06-10T05:30:00Z',
  },
  {
    id: 503,
    offer_code: 'OFF-503',
    status: 'accepted',
    employment_type: 'full_time',
    currency: 'INR',
    annual_ctc_amount: 3100000,
    joining_bonus_amount: 200000,
    proposed_start_date: '2026-06-24',
    expires_on: '2026-06-10',
    notes: 'Executive sign-off completed.',
    candidate_message: 'Welcome to Phoenix.',
    candidate: candidates[3],
    requisition: candidates[3].requisition,
    recruiter: recruiterSecondary,
    requested_by: recruiterSecondary,
    workflow_instance_id: 9503,
    workflow: {
      id: 9503,
      reference_type: 'offer',
      reference_id: 503,
      status: 'completed',
      current_stage_sequence: 2,
      payload: {},
      completed_at: '2026-06-05T16:00:00Z',
      rejected_at: null,
      definition: { id: 2, key: 'recruitment-offer-approval', name: 'Offer Approval' },
      starter: recruiterSecondary,
      tasks: [],
    },
    hire_handoff: {
      id: 601,
      status: 'onboarding_queued',
      employee: createEmployee(1206, 'EMP-1206', 'Aakash Sen', 'aakash.sen@example.com'),
      converted_at: '2026-06-09T12:30:00Z',
      onboarding_triggered_at: '2026-06-09T12:32:00Z',
    },
    decision_history: [
      { id: 9706, from_status: 'draft', to_status: 'submitted', decision_type: 'submit', comment: null, actor: recruiterSecondary, acted_at: '2026-06-02T09:00:00Z' },
      { id: 9707, from_status: 'submitted', to_status: 'approved', decision_type: 'approve', comment: null, actor: { id: 3, name: 'Manager Reviewer', email: 'manager@phoenixhrms.test' }, acted_at: '2026-06-03T14:00:00Z' },
      { id: 9708, from_status: 'approved', to_status: 'sent', decision_type: 'mark_sent', comment: null, actor: recruiterSecondary, acted_at: '2026-06-03T15:00:00Z' },
      { id: 9709, from_status: 'sent', to_status: 'accepted', decision_type: 'record_acceptance', comment: 'Candidate signed on DocuSign.', actor: recruiterSecondary, acted_at: '2026-06-05T11:00:00Z' },
    ],
    submitted_at: '2026-06-02T09:00:00Z',
    approved_at: '2026-06-03T14:00:00Z',
    sent_at: '2026-06-03T15:00:00Z',
    accepted_at: '2026-06-05T11:00:00Z',
    declined_at: null,
    expired_at: null,
    created_at: '2026-06-02T08:00:00Z',
    updated_at: '2026-06-09T12:32:00Z',
  },
]

const interviews: RecruitmentInterviewRecord[] = [
  {
    id: 701,
    interview_code: 'INT-701',
    round_number: 1,
    interview_type: 'technical',
    status: 'completed',
    timezone: 'Asia/Kolkata',
    scheduled_start_at: '2026-06-06T09:30:00Z',
    scheduled_end_at: '2026-06-06T10:30:00Z',
    meeting_mode: 'virtual',
    meeting_location: null,
    meeting_link: 'https://meet.example.com/int-701',
    agenda: 'React architecture and system design',
    cancellation_reason: null,
    candidate: candidates[0],
    requisition: candidates[0].requisition,
    interviewer: interviewerTech,
    feedback: {
      id: 801,
      rating: 5,
      recommendation: 'strong_hire',
      comments: 'Excellent architecture depth and communication.',
      strengths: 'Design systems, mentoring, system ownership',
      concerns: 'Compensation at upper bound.',
      interviewer: interviewerTech,
      created_at: '2026-06-06T11:00:00Z',
    },
    created_at: '2026-06-05T12:00:00Z',
    updated_at: '2026-06-06T11:00:00Z',
  },
  {
    id: 702,
    interview_code: 'INT-702',
    round_number: 2,
    interview_type: 'managerial',
    status: 'scheduled',
    timezone: 'Asia/Kolkata',
    scheduled_start_at: '2026-06-11T07:30:00Z',
    scheduled_end_at: '2026-06-11T08:15:00Z',
    meeting_mode: 'virtual',
    meeting_location: null,
    meeting_link: 'https://meet.example.com/int-702',
    agenda: 'Manager panel and roadmap alignment',
    cancellation_reason: null,
    candidate: candidates[1],
    requisition: candidates[1].requisition,
    interviewer: interviewerTech,
    feedback: null,
    created_at: '2026-06-10T06:00:00Z',
    updated_at: '2026-06-10T06:00:00Z',
  },
  {
    id: 703,
    interview_code: 'INT-703',
    round_number: 1,
    interview_type: 'screening',
    status: 'scheduled',
    timezone: 'Asia/Kolkata',
    scheduled_start_at: '2026-06-12T09:00:00Z',
    scheduled_end_at: '2026-06-12T09:30:00Z',
    meeting_mode: 'phone',
    meeting_location: null,
    meeting_link: null,
    agenda: 'Recruiter screening call',
    cancellation_reason: null,
    candidate: candidates[2],
    requisition: candidates[2].requisition,
    interviewer: interviewerHr,
    feedback: null,
    created_at: '2026-06-10T05:30:00Z',
    updated_at: '2026-06-10T05:30:00Z',
  },
]

const handoffs: RecruitmentHireHandoffRecord[] = [
  {
    id: 601,
    status: 'onboarding_queued',
    offer: {
      id: 503,
      offer_code: 'OFF-503',
      status: 'accepted',
      employment_type: 'full_time',
      proposed_start_date: '2026-06-24',
      expires_on: '2026-06-10',
    },
    candidate: candidates[3],
    requisition: candidates[3].requisition,
    employee: createEmployee(1206, 'EMP-1206', 'Aakash Sen', 'aakash.sen@example.com'),
    recruiter: recruiterSecondary,
    converted_by: recruiterSecondary,
    source_resume: {
      id: 4504,
      version_number: 1,
      original_file_name: 'aakash-sen-resume.pdf',
    },
    offer_snapshot: { offer_code: 'OFF-503', annual_ctc_amount: 3100000, proposed_start_date: '2026-06-24' },
    candidate_snapshot: { full_name: 'Aakash Sen', email: 'aakash.sen@example.com' },
    requisition_snapshot: { requisition_code: 'REQ-HRB-303', title: 'HR Business Partner' },
    document_references: [{ type: 'resume', id: 4504, name: 'aakash-sen-resume.pdf' }],
    onboarding_template_ids: [9001, 9002],
    onboarding_task_ids: [9101, 9102],
    notes: 'Queued IT and HR onboarding tasks.',
    converted_at: '2026-06-09T12:30:00Z',
    onboarding_triggered_at: '2026-06-09T12:32:00Z',
    created_at: '2026-06-09T12:30:00Z',
    updated_at: '2026-06-09T12:32:00Z',
  },
]

function cloneRecord<T>(value: T): T {
  return JSON.parse(JSON.stringify(value)) as T
}

function scopeForSnapshot(snapshot: AccessSnapshot | null, data: RecruitmentWorkspaceData): RecruitmentWorkspaceData {
  if (!snapshot) {
    return cloneRecord(data)
  }

  const permissions = snapshot.user.permissions
  const userId = snapshot.user.id
  const employeeId = snapshot.user.employee?.id ?? null

  if (permissions.includes('recruitment.manage')) {
    return cloneRecord(data)
  }

  const visibleRequisitionIds = new Set<number>()

  data.requisitions.forEach((requisition) => {
    if (permissions.includes('recruitment.approve') && requisition.hiring_manager?.id === employeeId) {
      visibleRequisitionIds.add(requisition.id)
      return
    }

    if (requisition.recruiter?.id === userId) {
      visibleRequisitionIds.add(requisition.id)
    }
  })

  const visibleCandidates = data.candidates.filter((candidate) => {
    const requisitionId = candidate.requisition?.id
    return candidate.recruiter?.id === userId || (requisitionId ? visibleRequisitionIds.has(requisitionId) : false)
  })

  const visibleCandidateIds = new Set(visibleCandidates.map((candidate) => candidate.id))

  const visibleOffers = data.offers.filter((offer) => {
    const requisitionId = offer.requisition?.id
    const candidateId = offer.candidate?.id
    return (
      offer.recruiter?.id === userId ||
      (candidateId ? visibleCandidateIds.has(candidateId) : false) ||
      (requisitionId ? visibleRequisitionIds.has(requisitionId) : false)
    )
  })

  const visibleInterviews = data.interviews.filter((interview) => {
    const requisitionId = interview.requisition?.id
    const candidateId = interview.candidate?.id
    return (
      interview.interviewer?.id === userId ||
      (candidateId ? visibleCandidateIds.has(candidateId) : false) ||
      (requisitionId ? visibleRequisitionIds.has(requisitionId) : false)
    )
  })

  const visibleHandoffs = data.handoffs.filter((handoff) => {
    const requisitionId = handoff.requisition?.id
    const candidateId = handoff.candidate?.id
    return (
      handoff.recruiter?.id === userId ||
      (candidateId ? visibleCandidateIds.has(candidateId) : false) ||
      (requisitionId ? visibleRequisitionIds.has(requisitionId) : false)
    )
  })

  const visibleRequisitions = data.requisitions.filter((requisition) => visibleRequisitionIds.has(requisition.id))
  const recruiterMap = new Map<number, RecruitmentUserReference>()
  const interviewerMap = new Map<number, RecruitmentUserReference>()
  const managerMap = new Map<number, RecruitmentEmployeeReference>()

  visibleRequisitions.forEach((requisition) => {
    if (requisition.recruiter) {
      recruiterMap.set(requisition.recruiter.id, requisition.recruiter)
    }

    if (requisition.hiring_manager) {
      managerMap.set(requisition.hiring_manager.id, requisition.hiring_manager)
    }
  })

  visibleCandidates.forEach((candidate) => {
    if (candidate.recruiter) {
      recruiterMap.set(candidate.recruiter.id, candidate.recruiter)
    }
  })

  visibleInterviews.forEach((interview) => {
    if (interview.interviewer) {
      interviewerMap.set(interview.interviewer.id, interview.interviewer)
    }
  })

  visibleOffers.forEach((offer) => {
    if (offer.recruiter) {
      recruiterMap.set(offer.recruiter.id, offer.recruiter)
    }
  })

  return cloneRecord({
    requisitions: visibleRequisitions,
    candidates: visibleCandidates,
    interviews: visibleInterviews,
    offers: visibleOffers,
    handoffs: visibleHandoffs,
    directory: {
      recruiters: [...recruiterMap.values()],
      interviewers: [...interviewerMap.values()],
      hiring_managers: [...managerMap.values()],
    },
  })
}

export function buildDemoRecruitmentWorkspace(snapshot: AccessSnapshot | null): RecruitmentWorkspaceData {
  return scopeForSnapshot(snapshot, {
    requisitions,
    candidates,
    interviews,
    offers,
    handoffs,
    directory: {
      recruiters: [recruiterPrimary, recruiterSecondary],
      interviewers: [interviewerTech, interviewerHr],
      hiring_managers: [hiringManager, hiringManager2],
    },
  })
}
