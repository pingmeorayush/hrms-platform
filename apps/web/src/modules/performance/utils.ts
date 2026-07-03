import type { BadgeVariant } from '../../shared/ui/badge'
import {
  formatRegionalDate,
  formatRegionalRelativeTimestamp,
} from '../../shared/regionalization/formatters'
import type {
  PerformanceGoalStatus,
  PerformanceReviewActorRole,
  PerformanceReviewCycleStatus,
  PerformanceReviewStatus,
} from './types'

export function formatPerformanceDate(value: string | null | undefined) {
  return formatRegionalDate(value, 'Pending')
}

export function formatPerformanceRelativeTimestamp(value: string | null | undefined) {
  return formatRegionalRelativeTimestamp(value, 'Pending')
}

export function formatPerformanceLabel(value: string) {
  return value
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase())
}

export function reviewStatusBadgeVariant(status: PerformanceReviewStatus): BadgeVariant {
  switch (status) {
    case 'self_assessment':
      return 'info'
    case 'manager_review':
      return 'warning'
    case 'calibration':
      return 'warning'
    case 'finalized':
      return 'neutral'
    case 'published':
      return 'success'
    case 'reopened':
      return 'danger'
    case 'draft':
    default:
      return 'neutral'
  }
}

export function cycleStatusBadgeVariant(status: PerformanceReviewCycleStatus): BadgeVariant {
  switch (status) {
    case 'active':
      return 'success'
    case 'scheduled':
      return 'info'
    case 'archived':
      return 'neutral'
    case 'draft':
    default:
      return 'warning'
  }
}

export function goalStatusBadgeVariant(status: PerformanceGoalStatus): BadgeVariant {
  switch (status) {
    case 'active':
      return 'success'
    case 'archived':
      return 'neutral'
    case 'draft':
    default:
      return 'warning'
  }
}

export function formatActorRole(role: PerformanceReviewActorRole) {
  if (!role) {
    return 'Viewer'
  }

  if (role === 'hr') {
    return 'HR / Calibration'
  }

  return formatPerformanceLabel(role)
}
