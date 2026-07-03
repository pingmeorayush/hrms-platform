export type OrganizationStatus = 'active' | 'inactive'
export type OrganizationCollection = 'departments' | 'designations' | 'locations' | 'costCenters'

export interface CompanyProfile {
  id: number
  uuid: string
  name: string
  slug: string
  status: string
  subscription_plan: string | null
  timezone: string
  currency: string
  country_code: string
  locale: string
  language: string
  time_format: '12h' | '24h'
  expansion_country_codes: string[]
  created_at: string | null
  updated_at: string | null
}

export interface OrganizationMasterRecord {
  id: number
  code: string
  name: string
  description: string | null
  status: OrganizationStatus
  created_at: string | null
  updated_at: string | null
}

export type DepartmentResource = OrganizationMasterRecord
export type DesignationResource = OrganizationMasterRecord
export type CostCenterResource = OrganizationMasterRecord

export interface LocationRecord {
  id: number
  code: string
  name: string
  timezone: string
  currency: string
  address_line_1: string | null
  address_line_2: string | null
  city: string | null
  state: string | null
  country: string | null
  postal_code: string | null
  status: OrganizationStatus
  created_at: string | null
  updated_at: string | null
}

export type LocationResource = LocationRecord

export interface OrganizationWorkspaceData {
  companyProfile: CompanyProfile
  departments: OrganizationMasterRecord[]
  designations: OrganizationMasterRecord[]
  locations: LocationRecord[]
  costCenters: OrganizationMasterRecord[]
}

export interface CompanyProfileFormValues {
  name: string
  subscription_plan: string
  timezone: string
  currency: string
  country_code: string
  locale: string
  language: string
  time_format: '12h' | '24h'
  expansion_country_codes: string[]
}

export interface OrganizationMasterFormValues {
  code: string
  name: string
  description: string
  status: OrganizationStatus
}

export interface LocationFormValues {
  code: string
  name: string
  timezone: string
  currency: string
  address_line_1: string
  address_line_2: string
  city: string
  state: string
  country: string
  postal_code: string
  status: OrganizationStatus
}
