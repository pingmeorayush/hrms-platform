import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import type {
  CompanyProfile,
  CompanyProfileFormValues,
  LocationFormValues,
  LocationRecord,
  OrganizationCollection,
  OrganizationMasterFormValues,
  OrganizationMasterRecord,
  OrganizationWorkspaceData,
} from '../types'

const pathByCollection: Record<OrganizationCollection, string> = {
  departments: 'departments',
  designations: 'designations',
  locations: 'locations',
  costCenters: 'cost-centers',
}

async function request<T>(url: string, token: string, init?: RequestInit) {
  const response = await fetch(url, {
    ...init,
    headers: {
      ...buildApiHeaders(token, init?.body ? 'application/json' : 'application/json'),
      ...(init?.headers ?? {}),
    },
  })

  return readApiJson<T>(response)
}

export async function fetchOrganizationWorkspace(
  apiBaseUrl: string,
  token: string,
): Promise<OrganizationWorkspaceData> {
  const headers = buildApiHeaders(token)

  const [companyProfileResponse, departmentsResponse, designationsResponse, locationsResponse, costCentersResponse] =
    await Promise.all([
      fetch(`${apiBaseUrl}/organization/company-profile`, { headers }),
      fetch(`${apiBaseUrl}/organization/departments`, { headers }),
      fetch(`${apiBaseUrl}/organization/designations`, { headers }),
      fetch(`${apiBaseUrl}/organization/locations`, { headers }),
      fetch(`${apiBaseUrl}/organization/cost-centers`, { headers }),
    ])

  const [companyProfile, departments, designations, locations, costCenters] = await Promise.all([
    readApiJson<CompanyProfile>(companyProfileResponse),
    readApiJson<OrganizationMasterRecord[]>(departmentsResponse),
    readApiJson<OrganizationMasterRecord[]>(designationsResponse),
    readApiJson<LocationRecord[]>(locationsResponse),
    readApiJson<OrganizationMasterRecord[]>(costCentersResponse),
  ])

  return {
    companyProfile,
    departments,
    designations,
    locations,
    costCenters,
  }
}

export async function updateCompanyProfile(
  apiBaseUrl: string,
  token: string,
  values: CompanyProfileFormValues,
) {
  return request<CompanyProfile>(`${apiBaseUrl}/organization/company-profile`, token, {
    method: 'PATCH',
    body: JSON.stringify({
      name: values.name.trim(),
      subscription_plan: values.subscription_plan.trim() || null,
      timezone: values.timezone.trim(),
      currency: values.currency.trim().toUpperCase(),
      country_code: values.country_code.trim().toUpperCase(),
      locale: values.locale.trim(),
      language: values.language.trim().toLowerCase(),
      time_format: values.time_format,
      expansion_country_codes: values.expansion_country_codes,
    }),
  })
}

export async function createOrganizationMaster(
  apiBaseUrl: string,
  token: string,
  collection: Exclude<OrganizationCollection, 'locations'>,
  values: OrganizationMasterFormValues,
) {
  return request<OrganizationMasterRecord>(`${apiBaseUrl}/organization/${pathByCollection[collection]}`, token, {
    method: 'POST',
    body: JSON.stringify({
      code: values.code.trim(),
      name: values.name.trim(),
      description: values.description.trim() || null,
      status: values.status,
    }),
  })
}

export async function updateOrganizationMaster(
  apiBaseUrl: string,
  token: string,
  collection: Exclude<OrganizationCollection, 'locations'>,
  id: number,
  values: OrganizationMasterFormValues,
) {
  return request<OrganizationMasterRecord>(
    `${apiBaseUrl}/organization/${pathByCollection[collection]}/${id}`,
    token,
    {
      method: 'PATCH',
      body: JSON.stringify({
        code: values.code.trim(),
        name: values.name.trim(),
        description: values.description.trim() || null,
        status: values.status,
      }),
    },
  )
}

export async function createLocation(
  apiBaseUrl: string,
  token: string,
  values: LocationFormValues,
) {
  return request<LocationRecord>(`${apiBaseUrl}/organization/locations`, token, {
    method: 'POST',
    body: JSON.stringify(normalizeLocationValues(values)),
  })
}

export async function updateLocation(
  apiBaseUrl: string,
  token: string,
  id: number,
  values: LocationFormValues,
) {
  return request<LocationRecord>(`${apiBaseUrl}/organization/locations/${id}`, token, {
    method: 'PATCH',
    body: JSON.stringify(normalizeLocationValues(values)),
  })
}

function normalizeLocationValues(values: LocationFormValues) {
  return {
    code: values.code.trim(),
    name: values.name.trim(),
    timezone: values.timezone.trim(),
    currency: values.currency.trim().toUpperCase(),
    address_line_1: values.address_line_1.trim() || null,
    address_line_2: values.address_line_2.trim() || null,
    city: values.city.trim() || null,
    state: values.state.trim() || null,
    country: values.country.trim() || null,
    postal_code: values.postal_code.trim() || null,
    status: values.status,
  }
}
