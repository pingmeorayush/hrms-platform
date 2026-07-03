import type { BadgeVariant } from '../../shared/ui/badge'
import {
  formatRegionalCurrency,
  formatRegionalDate,
  formatRegionalRelativeTimestamp,
} from '../../shared/regionalization/formatters'
import type { PayrollPeriodStatus, PayrollRunStatus } from './types'

export function formatCurrency(value: number | string, currency = 'INR') {
  return formatRegionalCurrency(value, currency, { maximumFractionDigits: 2 })
}

export function formatDate(value: string | null | undefined) {
  return formatRegionalDate(value, 'Pending')
}

export function formatRelativeTimestamp(value: string | null | undefined) {
  return formatRegionalRelativeTimestamp(value, 'Pending')
}

export function formatRunStatus(status: PayrollRunStatus) {
  return status
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase())
}

export function formatPeriodStatus(status: PayrollPeriodStatus) {
  return status
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase())
}

export function runStatusBadgeVariant(status: PayrollRunStatus): BadgeVariant {
  switch (status) {
    case 'ready':
      return 'info'
    case 'blocked':
      return 'warning'
    case 'failed':
      return 'danger'
    case 'approved':
      return 'success'
    case 'locked':
      return 'success'
    case 'calculated':
    default:
      return 'neutral'
  }
}

export function periodStatusBadgeVariant(status: PayrollPeriodStatus): BadgeVariant {
  switch (status) {
    case 'draft':
      return 'neutral'
    case 'open':
      return 'info'
    case 'prepared':
      return 'warning'
    case 'closed':
      return 'success'
    default:
      return 'neutral'
  }
}
