import type { LearningAudienceType, LearningDeliveryMode, LearningDueState, LearningRenewalPosture } from './types'

function replaceSeparators(value: string) {
  return value.replace(/_/g, ' ')
}

export function formatLearningLabel(value: string | null | undefined) {
  if (!value) {
    return 'Not set'
  }

  return replaceSeparators(value)
    .split(' ')
    .filter(Boolean)
    .map((part) => `${part[0]?.toUpperCase() ?? ''}${part.slice(1)}`)
    .join(' ')
}

export function formatLearningDate(value: string | null | undefined) {
  if (!value) {
    return 'Not scheduled'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(`${value}T00:00:00`))
}

export function learningDueStateVariant(state: LearningDueState) {
  switch (state) {
    case 'overdue':
      return 'danger' as const
    case 'due_today':
      return 'warning' as const
    case 'completed':
      return 'success' as const
    case 'upcoming':
      return 'info' as const
    default:
      return 'neutral' as const
  }
}

export function learningRenewalVariant(state: LearningRenewalPosture) {
  switch (state) {
    case 'overdue':
      return 'danger' as const
    case 'due_today':
      return 'warning' as const
    case 'current':
      return 'success' as const
    case 'pending_initial_completion':
      return 'info' as const
    default:
      return 'neutral' as const
  }
}

export function learningDeliveryModeLabel(value: LearningDeliveryMode) {
  return formatLearningLabel(value)
}

export function learningAudienceLabel(value: LearningAudienceType) {
  return value === 'all_active' ? 'All active employees' : formatLearningLabel(value)
}
