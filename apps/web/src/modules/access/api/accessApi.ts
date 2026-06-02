import type { AccessSnapshot, AccessUser, VisibilityContract } from '../types'

async function readJson<T>(response: Response): Promise<T> {
  const payload = (await response.json()) as { data?: T; message?: string }

  if (!response.ok || payload.data === undefined) {
    throw new Error(payload.message ?? 'The API request failed.')
  }

  return payload.data
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

  const user = await readJson<AccessUser>(meResponse)
  const visibility = await readJson<VisibilityContract>(visibilityResponse)

  return {
    user,
    visibility,
  }
}
