import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import type {
  AssistantChatInput,
  AssistantChatResponse,
  AssistantInteraction,
  AssistantInteractionFeedbackInput,
  AssistantRecommendation,
  AssistantRecommendationDecisionInput,
  AssistantRecommendationInput,
  AssistantWorkspaceData,
} from '../types'

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

export function fetchAssistantWorkspace(apiBaseUrl: string, token: string) {
  return requestJson<AssistantWorkspaceData>(`${apiBaseUrl}/ai/workspace`, token)
}

export function createAssistantChat(apiBaseUrl: string, token: string, payload: AssistantChatInput) {
  return requestJson<AssistantChatResponse>(`${apiBaseUrl}/ai/chat`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function createAssistantRecommendation(
  apiBaseUrl: string,
  token: string,
  payload: AssistantRecommendationInput,
) {
  return requestJson<AssistantRecommendation>(`${apiBaseUrl}/ai/recommendations`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function submitAssistantRecommendationDecision(
  apiBaseUrl: string,
  token: string,
  recommendationId: number,
  payload: AssistantRecommendationDecisionInput,
) {
  return requestJson<AssistantRecommendation>(
    `${apiBaseUrl}/ai/recommendations/${recommendationId}/decisions`,
    token,
    {
      method: 'POST',
      body: JSON.stringify(payload),
    },
  )
}

export function submitAssistantInteractionFeedback(
  apiBaseUrl: string,
  token: string,
  interactionId: number,
  payload: AssistantInteractionFeedbackInput,
) {
  return requestJson<AssistantInteraction>(
    `${apiBaseUrl}/ai/interactions/${interactionId}/feedback`,
    token,
    {
      method: 'POST',
      body: JSON.stringify(payload),
    },
  )
}
