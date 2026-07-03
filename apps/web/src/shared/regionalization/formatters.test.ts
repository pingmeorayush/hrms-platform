import { beforeEach, describe, expect, it } from 'vitest'
import { fallbackRegionalSettings } from './defaults'
import {
  formatRegionalCurrency,
  formatRegionalDate,
  formatRegionalTime,
} from './formatters'
import { setCurrentRegionalSettings } from './state'

describe('regional formatters', () => {
  beforeEach(() => {
    setCurrentRegionalSettings(fallbackRegionalSettings)
  })

  it('keeps date-only values stable across timezones', () => {
    setCurrentRegionalSettings({
      ...fallbackRegionalSettings,
      country_code: 'US',
      locale: 'en-US',
      timezone: 'America/Los_Angeles',
      currency: 'USD',
      time_format: '12h',
      week_start: 'sunday',
    })

    expect(formatRegionalDate('2026-06-30')).toMatch(/Jun .*30.*2026/)
  })

  it('uses the active regional settings for time and currency output', () => {
    setCurrentRegionalSettings({
      ...fallbackRegionalSettings,
      country_code: 'US',
      locale: 'en-US',
      timezone: 'America/New_York',
      currency: 'USD',
      time_format: '12h',
      week_start: 'sunday',
    })

    expect(formatRegionalTime('2026-06-30T19:15:00Z')).toMatch(/PM/i)
    expect(formatRegionalCurrency(1234.5)).toContain('$')
  })
})
