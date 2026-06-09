import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import type { SelfServiceWorkspaceData } from '../types'

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

function resolveApiUrl(apiBaseUrl: string, path: string) {
  return new URL(path, apiBaseUrl).toString()
}

export function fetchSelfServiceWorkspace(apiBaseUrl: string, token: string) {
  return requestJson<SelfServiceWorkspaceData>(`${apiBaseUrl}/self-service/workspace`, token)
}

export async function acknowledgeSelfServicePolicy(
  apiBaseUrl: string,
  token: string,
  policyAcknowledgementId: number,
  acknowledgementNotes: string | null = null,
) {
  return requestJson(`${apiBaseUrl}/policy-acknowledgements/${policyAcknowledgementId}/acknowledge`, token, {
    method: 'PATCH',
    body: JSON.stringify({
      acknowledgement_notes: acknowledgementNotes,
    }),
  })
}

export async function downloadSelfServiceDocument(
  apiBaseUrl: string,
  token: string,
  downloadPath: string,
  fileName: string,
) {
  const response = await fetch(resolveApiUrl(apiBaseUrl, downloadPath), {
    headers: {
      Accept: 'application/octet-stream',
      Authorization: `Bearer ${token}`,
    },
  })

  if (!response.ok) {
    let message = 'The document download failed.'

    try {
      const payload = (await response.json()) as { message?: string }
      message = payload.message ?? message
    } catch {
      // Keep the fallback message for binary endpoints that do not return JSON.
    }

    throw new Error(message)
  }

  const blob = await response.blob()
  const objectUrl = window.URL.createObjectURL(blob)
  const anchor = document.createElement('a')
  anchor.href = objectUrl
  anchor.download = fileName
  document.body.append(anchor)
  anchor.click()
  anchor.remove()
  window.URL.revokeObjectURL(objectUrl)
}
