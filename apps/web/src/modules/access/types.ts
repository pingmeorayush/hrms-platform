export type DemoPersona = 'platformAdmin' | 'tenantAdmin' | 'recruiter' | 'itOperator' | 'manager' | 'employee'

export interface TenantInfo {
  company_id: number
  company_name: string
  subscription_plan: string | null
  timezone: string | null
  currency: string | null
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
