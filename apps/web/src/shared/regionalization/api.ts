import { buildApiHeaders, readApiJson } from '../api/http'
import type { LocalizationConfiguration, RegionalPreferenceOverrides } from './types'

export async function fetchLocalizationConfiguration(
  apiBaseUrl: string,
  token: string,
): Promise<LocalizationConfiguration> {
  const response = await fetch(`${apiBaseUrl}/localization`, {
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
  })

  return readApiJson<LocalizationConfiguration>(response)
}

export async function updateLocalizationPreferences(
  apiBaseUrl: string,
  token: string,
  payload: RegionalPreferenceOverrides,
): Promise<LocalizationConfiguration> {
  const response = await fetch(`${apiBaseUrl}/localization/preferences`, {
    method: 'PATCH',
    headers: buildApiHeaders(token),
    body: JSON.stringify(payload),
  })

  return readApiJson<LocalizationConfiguration>(response)
}
