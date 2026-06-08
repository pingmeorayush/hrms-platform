import type { LeaveRequestRecord } from '../types'

export function formatDate(value: string | null) {
  if (!value) {
    return 'Not available'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

export function formatRequestStatus(status: LeaveRequestRecord['status']) {
  return status
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
}

export function requestBadgeVariant(status: LeaveRequestRecord['status']) {
  switch (status) {
    case 'approved':
      return 'success'
    case 'pending':
      return 'warning'
    case 'changes_requested':
      return 'info'
    case 'rejected':
      return 'danger'
    case 'cancelled':
    default:
      return 'subtle'
  }
}
