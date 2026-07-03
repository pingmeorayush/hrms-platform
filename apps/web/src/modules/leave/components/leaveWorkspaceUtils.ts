import type { LeaveRequestRecord } from '../types'
import { formatRegionalDate } from '../../../shared/regionalization/formatters'

export function formatDate(value: string | null) {
  return formatRegionalDate(value, 'Not available')
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
