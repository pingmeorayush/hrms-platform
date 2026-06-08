import type { AccessSnapshot, AccessUser, VisibilityContract } from '../types'
import { readApiJson } from '../../../shared/api/http'

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
