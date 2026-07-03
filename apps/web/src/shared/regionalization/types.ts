export type RegionalWeekStart = 'monday' | 'sunday'
export type RegionalTimeFormat = '12h' | '24h'

export interface RegionalSourceMap {
  locale: 'user' | 'tenant'
  language: 'user' | 'tenant'
  timezone: 'user' | 'tenant'
  currency: 'user' | 'tenant'
  time_format: 'user' | 'tenant'
}

export interface RegionalTenantDefaults {
  country_code: string
  locale: string
  language: string
  timezone: string
  currency: string
  time_format: RegionalTimeFormat
  week_start: RegionalWeekStart
  expansion_country_codes: string[]
}

export interface EffectiveRegionalSettings extends RegionalTenantDefaults {
  source: RegionalSourceMap
}

export interface RegionalCountryOption {
  code: string
  label: string
  locale: string
  language: string
  timezone: string
  currency: string
  time_format: RegionalTimeFormat
  week_start: RegionalWeekStart
  launch_default: boolean
  expansion_placeholder: boolean
}

export interface RegionalOption {
  code: string
  label: string
}

export interface RegionalSupportedOptions {
  countries: RegionalCountryOption[]
  languages: RegionalOption[]
  locales: RegionalOption[]
  currencies: RegionalOption[]
  time_formats: Array<{
    code: RegionalTimeFormat
    label: string
  }>
}

export interface LocalizationConfiguration {
  effective_settings: EffectiveRegionalSettings
  tenant_defaults: RegionalTenantDefaults
  supported: RegionalSupportedOptions
}

export interface RegionalPreferenceOverrides {
  locale?: string | null
  language?: string | null
  timezone?: string | null
  currency?: string | null
  time_format?: RegionalTimeFormat | null
}
