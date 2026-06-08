import { ApiRequestError, buildApiHeaders, readApiJson } from '../../../shared/api/http'
import type {
  EmployeeAddressRecord,
  EmployeeBankAccountCollection,
  EmployeeContactRecord,
  EmployeeDocumentRecord,
  EmployeeEmergencyContactRecord,
  EmployeeOnboardingData,
  EmployeeOnboardingTaskRecord,
  EmployeeRecord,
  PaginatedAuditLogEntries,
} from '../types'

export interface EmployeeProfileUpdatePayload {
  first_name?: string
  middle_name?: string | null
  last_name?: string
  email?: string
  phone?: string | null
  date_of_birth?: string | null
  gender?: string | null
  marital_status?: string | null
  employment_type?: string
  user_id?: number | null
}

export interface EmployeeTransferPayload {
  effective_date: string
  department_id?: number
  manager_id?: number | null
  location_id?: number | null
  cost_center_id?: number | null
  notes?: string | null
}

export interface EmployeePromotionPayload {
  effective_date: string
  designation_id: number
  department_id?: number
  manager_id?: number | null
  location_id?: number | null
  cost_center_id?: number | null
  notes?: string | null
}

export interface EmployeeTerminationPayload {
  termination_date: string
  reason: string
  notes?: string | null
}

export interface EmployeeContactPayload {
  type: EmployeeContactRecord['type']
  label?: string | null
  value: string
  is_primary?: boolean
  status?: 'active' | 'inactive'
  notes?: string | null
}

export interface EmployeeAddressPayload {
  type: EmployeeAddressRecord['type']
  address_line_1: string
  address_line_2?: string | null
  city: string
  state?: string | null
  country: string
  postal_code: string
  notes?: string | null
}

export interface EmployeeEmergencyContactPayload {
  name: string
  relationship: string
  phone_number: string
  email?: string | null
  address?: string | null
  priority?: number | null
  notes?: string | null
}

export interface EmployeeOnboardingTaskPayload {
  title?: string
  category?: EmployeeOnboardingTaskRecord['category']
  task_type?: EmployeeOnboardingTaskRecord['task_type']
  assignee_type?: EmployeeOnboardingTaskRecord['assignee_type']
  status?: EmployeeOnboardingTaskRecord['status']
  sort_order?: number
  due_date?: string | null
  notes?: string | null
}

export interface EmployeeDocumentUploadPayload {
  document_type: string
  expiry_date?: string | null
  notes?: string | null
  file: File
}

async function requestJson<T>(url: string, token: string, init?: RequestInit) {
  const response = await fetch(url, {
    ...init,
    headers: {
      ...buildApiHeaders(token),
      ...(init?.headers ?? {}),
    },
  })

  return readApiJson<T>(response)
}

async function requestMultipart<T>(url: string, token: string, formData: FormData) {
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: formData,
  })

  return readApiJson<T>(response)
}

export function fetchEmployeeContacts(apiBaseUrl: string, token: string, employeeId: number) {
  return requestJson<EmployeeContactRecord[]>(
    `${apiBaseUrl}/employees/${employeeId}/contacts`,
    token,
  )
}

export function createEmployeeContact(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  payload: EmployeeContactPayload,
) {
  return requestJson<EmployeeContactRecord>(`${apiBaseUrl}/employees/${employeeId}/contacts`, token, {
    method: 'POST',
    body: JSON.stringify(normalizeContactPayload(payload)),
  })
}

export function updateEmployeeContact(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  contactId: number,
  payload: Partial<EmployeeContactPayload>,
) {
  return requestJson<EmployeeContactRecord>(
    `${apiBaseUrl}/employees/${employeeId}/contacts/${contactId}`,
    token,
    {
      method: 'PATCH',
      body: JSON.stringify(normalizeContactPayload(payload)),
    },
  )
}

export function fetchEmployeeAddresses(apiBaseUrl: string, token: string, employeeId: number) {
  return requestJson<EmployeeAddressRecord[]>(
    `${apiBaseUrl}/employees/${employeeId}/addresses`,
    token,
  )
}

export function createEmployeeAddress(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  payload: EmployeeAddressPayload,
) {
  return requestJson<EmployeeAddressRecord>(`${apiBaseUrl}/employees/${employeeId}/addresses`, token, {
    method: 'POST',
    body: JSON.stringify(normalizeAddressPayload(payload)),
  })
}

export function updateEmployeeAddress(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  addressId: number,
  payload: Partial<EmployeeAddressPayload>,
) {
  return requestJson<EmployeeAddressRecord>(
    `${apiBaseUrl}/employees/${employeeId}/addresses/${addressId}`,
    token,
    {
      method: 'PATCH',
      body: JSON.stringify(normalizeAddressPayload(payload)),
    },
  )
}

export function fetchEmployeeEmergencyContacts(apiBaseUrl: string, token: string, employeeId: number) {
  return requestJson<EmployeeEmergencyContactRecord[]>(
    `${apiBaseUrl}/employees/${employeeId}/emergency-contacts`,
    token,
  )
}

export function createEmployeeEmergencyContact(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  payload: EmployeeEmergencyContactPayload,
) {
  return requestJson<EmployeeEmergencyContactRecord>(
    `${apiBaseUrl}/employees/${employeeId}/emergency-contacts`,
    token,
    {
      method: 'POST',
      body: JSON.stringify(normalizeEmergencyContactPayload(payload)),
    },
  )
}

export function updateEmployeeEmergencyContact(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  emergencyContactId: number,
  payload: Partial<EmployeeEmergencyContactPayload>,
) {
  return requestJson<EmployeeEmergencyContactRecord>(
    `${apiBaseUrl}/employees/${employeeId}/emergency-contacts/${emergencyContactId}`,
    token,
    {
      method: 'PATCH',
      body: JSON.stringify(normalizeEmergencyContactPayload(payload)),
    },
  )
}

export function updateEmployeeProfile(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  payload: EmployeeProfileUpdatePayload,
) {
  return requestJson<EmployeeRecord>(`${apiBaseUrl}/employees/${employeeId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(normalizeEmployeePayload(payload)),
  })
}

export function recordEmployeeTransfer(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  payload: EmployeeTransferPayload,
) {
  return requestJson<EmployeeRecord>(`${apiBaseUrl}/employees/${employeeId}/transfer`, token, {
    method: 'POST',
    body: JSON.stringify(normalizeTransferPayload(payload)),
  })
}

export function recordEmployeePromotion(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  payload: EmployeePromotionPayload,
) {
  return requestJson<EmployeeRecord>(`${apiBaseUrl}/employees/${employeeId}/promote`, token, {
    method: 'POST',
    body: JSON.stringify(normalizePromotionPayload(payload)),
  })
}

export function recordEmployeeTermination(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  payload: EmployeeTerminationPayload,
) {
  return requestJson<EmployeeRecord>(`${apiBaseUrl}/employees/${employeeId}/terminate`, token, {
    method: 'POST',
    body: JSON.stringify({
      termination_date: payload.termination_date,
      reason: payload.reason.trim(),
      notes: normalizeNullableString(payload.notes),
    }),
  })
}

export function fetchEmployeeOnboarding(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
) {
  return requestJson<EmployeeOnboardingData>(
    `${apiBaseUrl}/employees/${employeeId}/onboarding-tasks`,
    token,
  )
}

export function createEmployeeOnboardingTask(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  payload: EmployeeOnboardingTaskPayload,
) {
  return requestJson<EmployeeOnboardingTaskRecord>(
    `${apiBaseUrl}/employees/${employeeId}/onboarding-tasks`,
    token,
    {
      method: 'POST',
      body: JSON.stringify(normalizeOnboardingPayload(payload)),
    },
  )
}

export function updateEmployeeOnboardingTask(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  taskId: number,
  payload: EmployeeOnboardingTaskPayload,
) {
  return requestJson<EmployeeOnboardingTaskRecord>(
    `${apiBaseUrl}/employees/${employeeId}/onboarding-tasks/${taskId}`,
    token,
    {
      method: 'PATCH',
      body: JSON.stringify(normalizeOnboardingPayload(payload)),
    },
  )
}

export function fetchEmployeeDocuments(apiBaseUrl: string, token: string, employeeId: number) {
  return requestJson<EmployeeDocumentRecord[]>(
    `${apiBaseUrl}/employees/${employeeId}/documents`,
    token,
  )
}

export function uploadEmployeeDocument(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  payload: EmployeeDocumentUploadPayload,
) {
  const formData = new FormData()
  formData.set('document_type', payload.document_type.trim())
  formData.set('file', payload.file)

  const expiryDate = normalizeNullableString(payload.expiry_date)
  const notes = normalizeNullableString(payload.notes)

  if (expiryDate) {
    formData.set('expiry_date', expiryDate)
  }

  if (notes) {
    formData.set('notes', notes)
  }

  return requestMultipart<EmployeeDocumentRecord>(
    `${apiBaseUrl}/employees/${employeeId}/documents`,
    token,
    formData,
  )
}

export async function downloadEmployeeDocument(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  documentId: number,
  fileName: string,
) {
  const response = await fetch(
    `${apiBaseUrl}/employees/${employeeId}/documents/${documentId}/download`,
    {
      headers: {
        Accept: 'application/octet-stream',
        Authorization: `Bearer ${token}`,
      },
    },
  )

  if (!response.ok) {
    let message = 'The document download failed.'
    let fieldErrors: Record<string, string[]> = {}

    try {
      const payload = (await response.json()) as {
        message?: string
        errors?: Record<string, string[]>
      }

      message = payload.message ?? message
      fieldErrors = payload.errors ?? {}
    } catch {
      // Ignore non-JSON error bodies.
    }

    throw new ApiRequestError(message, response.status, fieldErrors)
  }

  const blob = await response.blob()
  const objectUrl = window.URL.createObjectURL(blob)
  const anchor = document.createElement('a')
  anchor.href = objectUrl
  anchor.download = fileName
  anchor.click()
  window.URL.revokeObjectURL(objectUrl)
}

export function fetchEmployeeBankAccounts(apiBaseUrl: string, token: string, employeeId: number) {
  return requestJson<EmployeeBankAccountCollection>(
    `${apiBaseUrl}/employees/${employeeId}/bank-accounts`,
    token,
  )
}

export function fetchEmployeeAuditHistory(apiBaseUrl: string, token: string, employeeId: number) {
  return requestJson<PaginatedAuditLogEntries>(
    `${apiBaseUrl}/employees/${employeeId}/audit-history?per_page=10`,
    token,
  )
}

function normalizeEmployeePayload(payload: EmployeeProfileUpdatePayload) {
  return {
    ...payload,
    first_name: payload.first_name?.trim(),
    middle_name: normalizeNullableString(payload.middle_name),
    last_name: payload.last_name?.trim(),
    email: payload.email?.trim(),
    phone: normalizeNullableString(payload.phone),
    date_of_birth: normalizeNullableString(payload.date_of_birth),
    gender: normalizeNullableString(payload.gender),
    marital_status: normalizeNullableString(payload.marital_status),
    employment_type: payload.employment_type?.trim(),
    user_id: payload.user_id ?? null,
  }
}

function normalizeTransferPayload(payload: EmployeeTransferPayload) {
  return {
    effective_date: payload.effective_date,
    department_id: payload.department_id,
    manager_id: payload.manager_id ?? null,
    location_id: payload.location_id ?? null,
    cost_center_id: payload.cost_center_id ?? null,
    notes: normalizeNullableString(payload.notes),
  }
}

function normalizePromotionPayload(payload: EmployeePromotionPayload) {
  return {
    effective_date: payload.effective_date,
    designation_id: payload.designation_id,
    department_id: payload.department_id,
    manager_id: payload.manager_id ?? null,
    location_id: payload.location_id ?? null,
    cost_center_id: payload.cost_center_id ?? null,
    notes: normalizeNullableString(payload.notes),
  }
}

function normalizeContactPayload(payload: Partial<EmployeeContactPayload>) {
  return {
    ...payload,
    label: normalizeNullableString(payload.label),
    value: payload.value?.trim(),
    notes: normalizeNullableString(payload.notes),
  }
}

function normalizeAddressPayload(payload: Partial<EmployeeAddressPayload>) {
  return {
    ...payload,
    address_line_1: payload.address_line_1?.trim(),
    address_line_2: normalizeNullableString(payload.address_line_2),
    city: payload.city?.trim(),
    state: normalizeNullableString(payload.state),
    country: payload.country?.trim(),
    postal_code: payload.postal_code?.trim(),
    notes: normalizeNullableString(payload.notes),
  }
}

function normalizeEmergencyContactPayload(payload: Partial<EmployeeEmergencyContactPayload>) {
  return {
    ...payload,
    name: payload.name?.trim(),
    relationship: payload.relationship?.trim(),
    phone_number: payload.phone_number?.trim(),
    email: normalizeNullableString(payload.email),
    address: normalizeNullableString(payload.address),
    priority: payload.priority ?? null,
    notes: normalizeNullableString(payload.notes),
  }
}

function normalizeOnboardingPayload(payload: EmployeeOnboardingTaskPayload) {
  return {
    ...payload,
    title: payload.title?.trim(),
    due_date: normalizeNullableString(payload.due_date),
    notes: normalizeNullableString(payload.notes),
  }
}

function normalizeNullableString(value: string | null | undefined) {
  const nextValue = value?.trim()
  return nextValue ? nextValue : null
}
