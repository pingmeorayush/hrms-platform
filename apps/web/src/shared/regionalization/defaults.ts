import type { AccessSnapshot, AccessUser, TenantInfo } from '../../modules/access/types'
import type {
  EffectiveRegionalSettings,
  LocalizationConfiguration,
  RegionalCountryOption,
  RegionalOption,
  RegionalPreferenceOverrides,
  RegionalTenantDefaults,
  RegionalTimeFormat,
} from './types'

const supportedCountries: RegionalCountryOption[] = [
  {
    code: 'IN',
    label: 'India',
    locale: 'en-IN',
    language: 'en',
    timezone: 'Asia/Kolkata',
    currency: 'INR',
    time_format: '24h',
    week_start: 'monday',
    launch_default: true,
    expansion_placeholder: true,
  },
  {
    code: 'US',
    label: 'United States',
    locale: 'en-US',
    language: 'en',
    timezone: 'America/New_York',
    currency: 'USD',
    time_format: '12h',
    week_start: 'sunday',
    launch_default: false,
    expansion_placeholder: true,
  },
  {
    code: 'GB',
    label: 'United Kingdom',
    locale: 'en-GB',
    language: 'en',
    timezone: 'Europe/London',
    currency: 'GBP',
    time_format: '24h',
    week_start: 'monday',
    launch_default: false,
    expansion_placeholder: true,
  },
  {
    code: 'DE',
    label: 'Germany',
    locale: 'de-DE',
    language: 'de',
    timezone: 'Europe/Berlin',
    currency: 'EUR',
    time_format: '24h',
    week_start: 'monday',
    launch_default: false,
    expansion_placeholder: true,
  },
  {
    code: 'AE',
    label: 'United Arab Emirates',
    locale: 'en-AE',
    language: 'en',
    timezone: 'Asia/Dubai',
    currency: 'AED',
    time_format: '12h',
    week_start: 'monday',
    launch_default: false,
    expansion_placeholder: true,
  },
  {
    code: 'SG',
    label: 'Singapore',
    locale: 'en-SG',
    language: 'en',
    timezone: 'Asia/Singapore',
    currency: 'SGD',
    time_format: '24h',
    week_start: 'monday',
    launch_default: false,
    expansion_placeholder: true,
  },
]

function makeOptionList<T extends string>(values: T[], labels: Record<T, string>): RegionalOption[] {
  return values.map((code) => ({ code, label: labels[code] ?? code }))
}

export const fallbackSupportedOptions = {
  countries: supportedCountries,
  languages: makeOptionList(['en', 'de'], {
    en: 'English',
    de: 'German',
  }),
  locales: supportedCountries
    .map((country) => ({
      code: country.locale,
      label: `${country.locale} (${country.label})`,
    }))
    .filter((value, index, array) => array.findIndex((entry) => entry.code === value.code) === index),
  currencies: supportedCountries
    .map((country) => ({
      code: country.currency,
      label: `${country.currency} (${country.label})`,
    }))
    .filter((value, index, array) => array.findIndex((entry) => entry.code === value.code) === index),
  time_formats: [
    { code: '12h' as const, label: '12-hour' },
    { code: '24h' as const, label: '24-hour' },
  ],
}

const fallbackCountry = supportedCountries.find((country) => country.code === 'IN') ?? supportedCountries[0]

export const fallbackTenantDefaults: RegionalTenantDefaults = {
  country_code: fallbackCountry.code,
  locale: fallbackCountry.locale,
  language: fallbackCountry.language,
  timezone: fallbackCountry.timezone,
  currency: fallbackCountry.currency,
  time_format: fallbackCountry.time_format,
  week_start: fallbackCountry.week_start,
  expansion_country_codes: ['US', 'DE'],
}

export const fallbackRegionalSettings: EffectiveRegionalSettings = {
  ...fallbackTenantDefaults,
  source: {
    locale: 'tenant',
    language: 'tenant',
    timezone: 'tenant',
    currency: 'tenant',
    time_format: 'tenant',
  },
}

export const fallbackLocalizationConfiguration: LocalizationConfiguration = {
  effective_settings: fallbackRegionalSettings,
  tenant_defaults: fallbackTenantDefaults,
  supported: fallbackSupportedOptions,
}

function normalizeRegionalPreferenceOverrides(
  overrides: RegionalPreferenceOverrides | null | undefined,
): RegionalPreferenceOverrides | null {
  if (!overrides) {
    return null
  }

  const normalizedEntries = Object.entries(overrides)
    .filter(([, value]) => value !== undefined)
    .map(([key, value]) => {
      if (typeof value === 'string') {
        const trimmed = value.trim()
        return [key, trimmed === '' ? null : trimmed]
      }

      return [key, value]
    })

  if (!normalizedEntries.length) {
    return null
  }

  return Object.fromEntries(normalizedEntries) as RegionalPreferenceOverrides
}

function presetForCountry(countryCode: string | null | undefined) {
  return supportedCountries.find((country) => country.code === countryCode) ?? fallbackCountry
}

function normalizeCountryCodes(value: string[] | null | undefined) {
  return (value ?? [])
    .map((item) => item.toUpperCase())
    .filter((item, index, array) => Boolean(item) && array.indexOf(item) === index)
}

export function resolveTenantRegionalDefaults(tenant: Partial<TenantInfo> | null | undefined): RegionalTenantDefaults {
  const preset = presetForCountry(tenant?.country_code)
  const expansionCountryCodes = normalizeCountryCodes(tenant?.expansion_country_codes)

  return {
    country_code: tenant?.country_code ?? preset.code,
    locale: tenant?.locale ?? preset.locale,
    language: tenant?.language ?? preset.language,
    timezone: tenant?.timezone ?? preset.timezone,
    currency: tenant?.currency ?? preset.currency,
    time_format: (tenant?.time_format as RegionalTimeFormat | null | undefined) ?? preset.time_format,
    week_start: preset.week_start,
    expansion_country_codes: expansionCountryCodes.length
      ? expansionCountryCodes
      : fallbackTenantDefaults.expansion_country_codes,
  }
}

function normalizeEffectiveRegionalSettings(
  user: Pick<AccessUser, 'regional_settings' | 'tenant'> | null | undefined,
): EffectiveRegionalSettings {
  const tenantDefaults = resolveTenantRegionalDefaults(user?.tenant)
  const effective = user?.regional_settings

  if (!effective) {
    return {
      ...tenantDefaults,
      source: {
        locale: 'tenant',
        language: 'tenant',
        timezone: 'tenant',
        currency: 'tenant',
        time_format: 'tenant',
      },
    }
  }

  return {
    country_code: effective.country_code ?? tenantDefaults.country_code,
    locale: effective.locale ?? tenantDefaults.locale,
    language: effective.language ?? tenantDefaults.language,
    timezone: effective.timezone ?? tenantDefaults.timezone,
    currency: effective.currency ?? tenantDefaults.currency,
    time_format: (effective.time_format as RegionalTimeFormat | null | undefined) ?? tenantDefaults.time_format,
    week_start: effective.week_start ?? tenantDefaults.week_start,
    expansion_country_codes: normalizeCountryCodes(effective.expansion_country_codes),
    source: {
      locale: effective.source?.locale ?? 'tenant',
      language: effective.source?.language ?? 'tenant',
      timezone: effective.source?.timezone ?? 'tenant',
      currency: effective.source?.currency ?? 'tenant',
      time_format: effective.source?.time_format ?? 'tenant',
    },
  }
}

export function buildLocalizationConfigurationFromSnapshot(
  snapshot: AccessSnapshot | null | undefined,
  overrides?: RegionalPreferenceOverrides | null,
): LocalizationConfiguration {
  const tenantDefaults = resolveTenantRegionalDefaults(snapshot?.user.tenant)
  const normalizedOverrides = normalizeRegionalPreferenceOverrides(overrides)

  if (normalizedOverrides) {
    return {
      effective_settings: {
        country_code: tenantDefaults.country_code,
        locale: normalizedOverrides.locale ?? tenantDefaults.locale,
        language: normalizedOverrides.language ?? tenantDefaults.language,
        timezone: normalizedOverrides.timezone ?? tenantDefaults.timezone,
        currency: normalizedOverrides.currency ?? tenantDefaults.currency,
        time_format:
          (normalizedOverrides.time_format as RegionalTimeFormat | null | undefined) ?? tenantDefaults.time_format,
        week_start: tenantDefaults.week_start,
        expansion_country_codes: tenantDefaults.expansion_country_codes,
        source: {
          locale: normalizedOverrides.locale ? 'user' : 'tenant',
          language: normalizedOverrides.language ? 'user' : 'tenant',
          timezone: normalizedOverrides.timezone ? 'user' : 'tenant',
          currency: normalizedOverrides.currency ? 'user' : 'tenant',
          time_format: normalizedOverrides.time_format ? 'user' : 'tenant',
        },
      },
      tenant_defaults: tenantDefaults,
      supported: fallbackSupportedOptions,
    }
  }

  return {
    effective_settings: normalizeEffectiveRegionalSettings(snapshot?.user),
    tenant_defaults: tenantDefaults,
    supported: fallbackSupportedOptions,
  }
}
