export type DemoPersona = 'platformAdmin' | 'tenantAdmin' | 'recruiter' | 'itOperator' | 'manager' | 'employee'

export interface TenantInfo {
  company_id: number
  company_name: string
  subscription_plan: string | null
  timezone: string | null
  currency: string | null
  country_code: string | null
  locale: string | null
  language: string | null
  time_format: '12h' | '24h' | null
  expansion_country_codes: string[]
}

export interface UserRegionalSettings {
  country_code: string
  locale: string
  language: string
  timezone: string
  currency: string
  time_format: '12h' | '24h'
  week_start: 'monday' | 'sunday'
  expansion_country_codes: string[]
  source: {
    locale: 'user' | 'tenant'
    language: 'user' | 'tenant'
    timezone: 'user' | 'tenant'
    currency: 'user' | 'tenant'
    time_format: 'user' | 'tenant'
  }
}

export interface LinkedEmployeeSummary {
  id: number
  employee_code: string
  full_name: string
  email: string | null
}

export interface AccessUser {
  id: number
  name: string
  initials: string
  email: string
  employee: LinkedEmployeeSummary | null
  roles: string[]
  permissions: string[]
  tenant: TenantInfo
  regional_settings: UserRegionalSettings
}

export interface VisibilityItem {
  id: string
  label: string
  href: string | null
  description: string | null
  required_permissions: string[]
  match: 'all' | 'any'
  visible: boolean
}

export type VisibilityAction = VisibilityItem

export interface VisibilityActionGroup {
  id: string
  title: string
  description: string
  actions: VisibilityAction[]
  visible_count: number
  hidden_count: number
}

export interface VisibilityContract {
  navigation: VisibilityItem[]
  action_groups: VisibilityActionGroup[]
  meta: {
    visible_navigation_count: number
    hidden_navigation_count: number
    backend_enforcement_note: string
  }
}

export interface AccessSnapshot {
  user: AccessUser
  visibility: VisibilityContract
}

export interface AccessAdminRole {
  id: number
  name: string
  guard_name: string
  permissions: string[]
}

export interface AccessAdminPermission {
  id: number
  name: string
  guard_name: string
}

export interface AccessAdminUser {
  id: number
  name: string
  initials: string
  email: string
  is_active: boolean
  requires_mfa: boolean
  mfa_method: string | null
  last_login_at: string | null
  locked_until: string | null
  created_at: string | null
  updated_at: string | null
  roles: string[]
  employee: LinkedEmployeeSummary | null
}
