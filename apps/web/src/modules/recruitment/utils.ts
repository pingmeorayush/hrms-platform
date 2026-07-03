import type {
  RecruitmentCandidateStage,
  RecruitmentCandidateStatus,
  RecruitmentHandoffStatus,
  RecruitmentInterviewStatus,
  RecruitmentMeetingMode,
  RecruitmentOfferStatus,
  RecruitmentRequisitionStatus,
} from './types'
import type { BadgeVariant } from '../../shared/ui/badge'
import {
  formatRegionalCurrency,
  formatRegionalDate,
  formatRegionalDateTime,
} from '../../shared/regionalization/formatters'

export const recruitmentCandidateStages: RecruitmentCandidateStage[] = [
  'applied',
  'screening',
  'shortlisted',
  'interview',
  'offer',
  'hired',
  'rejected',
  'withdrawn',
]

export function formatRecruitmentLabel(value: string | null | undefined) {
  if (!value) {
    return 'Not set'
  }

  return value
    .replace(/[_-]/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase())
}

export function formatRecruitmentDate(value: string | null | undefined) {
  return formatRegionalDate(value, 'Pending')
}

export function formatRecruitmentDateTime(value: string | null | undefined) {
  return formatRegionalDateTime(value, 'Pending')
}

export function formatRecruitmentCurrency(amount: number | null | undefined, currency = 'INR') {
  return formatRegionalCurrency(amount, currency, { maximumFractionDigits: 0 }, 'Pending')
}

export function requisitionStatusBadgeVariant(status: RecruitmentRequisitionStatus): BadgeVariant {
  switch (status) {
    case 'approved':
      return 'success'
    case 'submitted':
    case 'changes_requested':
      return 'warning'
    case 'rejected':
      return 'danger'
    case 'on_hold':
      return 'info'
    case 'closed':
      return 'neutral'
    case 'draft':
    default:
      return 'subtle'
  }
}

export function candidateStageBadgeVariant(stage: RecruitmentCandidateStage): BadgeVariant {
  switch (stage) {
    case 'hired':
      return 'success'
    case 'rejected':
    case 'withdrawn':
      return 'danger'
    case 'offer':
      return 'warning'
    case 'interview':
      return 'info'
    case 'screening':
    case 'shortlisted':
      return 'neutral'
    case 'applied':
    default:
      return 'subtle'
  }
}

export function candidateStatusBadgeVariant(status: RecruitmentCandidateStatus): BadgeVariant {
  switch (status) {
    case 'hired':
      return 'success'
    case 'rejected':
    case 'withdrawn':
      return 'danger'
    case 'active':
    default:
      return 'neutral'
  }
}

export function interviewStatusBadgeVariant(status: RecruitmentInterviewStatus): BadgeVariant {
  switch (status) {
    case 'completed':
      return 'success'
    case 'cancelled':
      return 'danger'
    case 'scheduled':
    default:
      return 'info'
  }
}

export function offerStatusBadgeVariant(status: RecruitmentOfferStatus): BadgeVariant {
  switch (status) {
    case 'accepted':
    case 'approved':
      return 'success'
    case 'declined':
    case 'rejected':
    case 'expired':
      return 'danger'
    case 'submitted':
    case 'changes_requested':
    case 'sent':
      return 'warning'
    case 'draft':
    default:
      return 'neutral'
  }
}

export function handoffStatusBadgeVariant(status: RecruitmentHandoffStatus): BadgeVariant {
  switch (status) {
    case 'onboarding_queued':
      return 'info'
    case 'employee_created':
      return 'success'
    case 'onboarding_skipped':
    default:
      return 'warning'
  }
}

export function meetingModeLabel(mode: RecruitmentMeetingMode) {
  switch (mode) {
    case 'onsite':
      return 'Onsite'
    case 'phone':
      return 'Phone'
    case 'virtual':
    default:
      return 'Virtual'
  }
}

export function nextCandidateStage(stage: RecruitmentCandidateStage): RecruitmentCandidateStage | null {
  switch (stage) {
    case 'applied':
      return 'screening'
    case 'screening':
      return 'shortlisted'
    case 'shortlisted':
      return 'interview'
    case 'interview':
      return 'offer'
    case 'offer':
      return 'hired'
    default:
      return null
  }
}
