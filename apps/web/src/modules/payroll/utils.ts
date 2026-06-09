import type { BadgeVariant } from '../../shared/ui/badge'
import type { PayrollPeriodStatus, PayrollRunStatus } from './types'

const currencyFormatter = new Intl.NumberFormat('en-IN', {
  style: 'currency',
  currency: 'INR',
  maximumFractionDigits: 2,
})

export function formatCurrency(value: number | string, currency = 'INR') {
  const amount = typeof value === 'string' ? Number(value) : value

  if (currency === 'INR') {
    return currencyFormatter.format(amount)
  }

  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
    maximumFractionDigits: 2,
  }).format(amount)
}

export function formatDate(value: string | null | undefined) {
  if (!value) {
    return 'Pending'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

export function formatRelativeTimestamp(value: string | null | undefined) {
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
