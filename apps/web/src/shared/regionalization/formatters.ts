import { getCurrentRegionalSettings } from './state'

function resolveDate(value: Date | string | null | undefined) {
  if (!value) {
    return null
  }

  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? null : value
  }

  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    const [year, month, day] = value.split('-').map(Number)
    return new Date(Date.UTC(year, month - 1, day, 12, 0, 0))
  }

  const parsed = new Date(value)
  return Number.isNaN(parsed.getTime()) ? null : parsed
}

function isDateOnly(value: Date | string | null | undefined) {
  return typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)
}

export function formatRegionalDate(value: Date | string | null | undefined, fallback = 'Pending') {
  const date = resolveDate(value)
  const settings = getCurrentRegionalSettings()

  if (!date) {
    return fallback
  }

  return new Intl.DateTimeFormat(settings.locale, {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    timeZone: isDateOnly(value) ? 'UTC' : settings.timezone,
  }).format(date)
}

export function formatRegionalDateTime(value: Date | string | null | undefined, fallback = 'Pending') {
  const date = resolveDate(value)
  const settings = getCurrentRegionalSettings()

  if (!date) {
    return fallback
  }

  return new Intl.DateTimeFormat(settings.locale, {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: settings.time_format === '12h',
    timeZone: settings.timezone,
  }).format(date)
}

export function formatRegionalTime(value: Date | string | null | undefined, fallback = 'Pending') {
  const date = resolveDate(value)
  const settings = getCurrentRegionalSettings()

  if (!date) {
    return fallback
  }

  return new Intl.DateTimeFormat(settings.locale, {
    hour: 'numeric',
    minute: '2-digit',
    hour12: settings.time_format === '12h',
    timeZone: settings.timezone,
  }).format(date)
}

export function formatRegionalCurrency(
  value: number | string | null | undefined,
  currency = getCurrentRegionalSettings().currency,
  options: Intl.NumberFormatOptions = {},
  fallback = 'Pending',
) {
  const amount = typeof value === 'string' ? Number(value) : value
  const settings = getCurrentRegionalSettings()

  if (typeof amount !== 'number' || Number.isNaN(amount)) {
    return fallback
  }

  return new Intl.NumberFormat(settings.locale, {
    style: 'currency',
    currency,
    maximumFractionDigits: 2,
    ...options,
  }).format(amount)
}

export function formatRegionalNumber(
  value: number | string | null | undefined,
  options: Intl.NumberFormatOptions = {},
  fallback = '—',
) {
  const amount = typeof value === 'string' ? Number(value) : value
  const settings = getCurrentRegionalSettings()

  if (typeof amount !== 'number' || Number.isNaN(amount)) {
    return fallback
  }

  return new Intl.NumberFormat(settings.locale, options).format(amount)
}

export function formatRegionalRelativeTimestamp(value: string | null | undefined, fallback = 'Pending') {
  if (!value) {
    return fallback
  }

  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) {
    return fallback
  }

  const diffMs = Date.now() - parsed.getTime()
  const diffMinutes = Math.round(diffMs / 60_000)

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
