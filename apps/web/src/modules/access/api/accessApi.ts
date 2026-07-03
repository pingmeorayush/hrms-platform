import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import type {
  AccessAdminPermission,
  AccessAdminRole,
  AccessAdminUser,
  AccessSnapshot,
  AccessUser,
  VisibilityContract,
} from '../types'

export interface LoginPayload {
  email: string
  password: string
  device_name?: string
}

export interface MfaPayload {
  email: string
  code: string
  device_name?: string
}

export interface ForgotPasswordPayload {
  email: string
}

export interface ResetPasswordPayload {
  email: string
  token: string
  password: string
  password_confirmation: string
}

export interface AccessTokenResponse {
  access_token: string
  token_type: string
  expires_at: string
}

export interface MfaChallengeResponse {
  mfa_required: true
  mfa_method: string
}

export type LoginResponse = AccessTokenResponse | MfaChallengeResponse

export interface AccessUserFilters {
  search?: string
  status?: 'all' | 'active' | 'inactive'
}

export interface CreateAccessUserPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  roles: string[]
  is_active?: boolean
  requires_mfa?: boolean
}

export interface UpdateAccessUserPayload {
  name?: string
  email?: string
  password?: string
  password_confirmation?: string
  roles?: string[]
  is_active?: boolean
  requires_mfa?: boolean
}

export interface CreateAccessRolePayload {
  name: string
  permissions: string[]
}

export interface UpdateAccessRolePayload {
  permissions: string[]
}

async function requestPublicJson<T>(url: string, init?: RequestInit) {
  const response = await fetch(url, {
    ...init,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(init?.headers ?? {}),
    },
  })

  return readApiJson<T>(response)
}

async function requestAuthedJson<T>(url: string, token: string, init?: RequestInit) {
  const response = await fetch(url, {
    ...init,
    headers: {
      ...buildApiHeaders(token),
      ...(init?.headers ?? {}),
    },
  })

  return readApiJson<T>(response)
}

export async function fetchAccessSnapshot(apiBaseUrl: string, token: string): Promise<AccessSnapshot> {
  const headers = {
    Accept: 'application/json',
    Authorization: `Bearer ${token}`,
  }

  const [meResponse, visibilityResponse] = await Promise.all([
    fetch(`${apiBaseUrl}/auth/me`, { headers }),
    fetch(`${apiBaseUrl}/ui/visibility`, { headers }),
  ])

  const user = await readApiJson<AccessUser>(meResponse)
  const visibility = await readApiJson<VisibilityContract>(visibilityResponse)

  return {
    user,
    visibility,
  }
}

export function login(apiBaseUrl: string, payload: LoginPayload) {
  return requestPublicJson<LoginResponse>(`${apiBaseUrl}/auth/login`, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function verifyMfa(apiBaseUrl: string, payload: MfaPayload) {
  return requestPublicJson<AccessTokenResponse>(`${apiBaseUrl}/auth/verify-mfa`, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function forgotPassword(apiBaseUrl: string, payload: ForgotPasswordPayload) {
  return requestPublicJson<null>(`${apiBaseUrl}/auth/forgot-password`, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function resetPassword(apiBaseUrl: string, payload: ResetPasswordPayload) {
  return requestPublicJson<null>(`${apiBaseUrl}/auth/reset-password`, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function logout(apiBaseUrl: string, token: string) {
  return requestAuthedJson<null>(`${apiBaseUrl}/auth/logout`, token, {
    method: 'POST',
  })
}

export function fetchAccessRoles(apiBaseUrl: string, token: string) {
  return requestAuthedJson<AccessAdminRole[]>(`${apiBaseUrl}/admin/roles`, token)
}

export function fetchAccessPermissions(apiBaseUrl: string, token: string) {
  return requestAuthedJson<AccessAdminPermission[]>(`${apiBaseUrl}/admin/permissions`, token)
}

export function createAccessRole(apiBaseUrl: string, token: string, payload: CreateAccessRolePayload) {
  return requestAuthedJson<AccessAdminRole>(`${apiBaseUrl}/admin/roles`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function updateAccessRole(
  apiBaseUrl: string,
  token: string,
  roleId: number,
  payload: UpdateAccessRolePayload,
) {
  return requestAuthedJson<AccessAdminRole>(`${apiBaseUrl}/admin/roles/${roleId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}

export function fetchAccessUsers(apiBaseUrl: string, token: string, filters: AccessUserFilters = {}) {
  const searchParams = new URLSearchParams()

  if (filters.search?.trim()) {
    searchParams.set('search', filters.search.trim())
  }

  if (filters.status && filters.status !== 'all') {
    searchParams.set('status', filters.status)
  }

  const suffix = searchParams.toString()

  return requestAuthedJson<AccessAdminUser[]>(
    `${apiBaseUrl}/admin/users${suffix ? `?${suffix}` : ''}`,
    token,
  )
}

export function createAccessUser(apiBaseUrl: string, token: string, payload: CreateAccessUserPayload) {
  return requestAuthedJson<AccessAdminUser>(`${apiBaseUrl}/admin/users`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function updateAccessUser(
  apiBaseUrl: string,
  token: string,
  userId: number,
  payload: UpdateAccessUserPayload,
) {
  return requestAuthedJson<AccessAdminUser>(`${apiBaseUrl}/admin/users/${userId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}
