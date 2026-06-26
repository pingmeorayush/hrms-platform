import type { BadgeVariant } from '../../shared/ui/badge'
import type {
  PerformanceGoalStatus,
  PerformanceReviewActorRole,
  PerformanceReviewCycleStatus,
  PerformanceReviewStatus,
} from './types'

export function formatPerformanceDate(value: string | null | undefined) {
  if (!value) {
    return 'Pending'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

export function formatPerformanceRelativeTimestamp(value: string | null | undefined) {
  if (!value) {
    return 'Pending'
  }

  const diffMs = Date.now() - new Date(value).getTime()
  const diffMinutes = Math.round(diffMs / 60000)

  if (Math.abs(diffMinutes) < 1) {
    return 'Just now'
  }

  if (Math.abs(diffMinutes) < 60) {
    return `${diffMinutes}m ago`
  }

  const diffHours = Math.round(diffMinutes / 60)
  if (Math.abs(diffHours) < 24) {
    return `${diffHours}h ago`
  }

  const diffDays = Math.round(diffHours / 24)
  return `${diffDays}d ago`
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
