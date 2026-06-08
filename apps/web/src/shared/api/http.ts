export interface ApiEnvelope<T> {
  data?: T
  message?: string
  errors?: Record<string, string[]>
}

export class ApiRequestError extends Error {
  status: number
  fieldErrors: Record<string, string[]>

  constructor(message: string, status: number, fieldErrors: Record<string, string[]> = {}) {
    super(message)
    this.name = 'ApiRequestError'
    this.status = status
    this.fieldErrors = fieldErrors
  }
}

export async function readApiJson<T>(response: Response): Promise<T> {
  const payload = (await response.json()) as ApiEnvelope<T>

  if (!response.ok || payload.data === undefined) {
    throw new ApiRequestError(
      payload.message ?? 'The API request failed.',
      response.status,
      payload.errors ?? {},
    )
  }

  return payload.data
}

export function buildApiHeaders(token: string, contentType = 'application/json') {
  return {
    Accept: 'application/json',
    Authorization: `Bearer ${token}`,
    'Content-Type': contentType,
  }
}
