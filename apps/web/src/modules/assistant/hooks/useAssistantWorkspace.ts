import { useMemo, useState } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { useAppSelector } from '../../../app/store/hooks'
import { ApiRequestError } from '../../../shared/api/http'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  createAssistantChat,
  createAssistantRecommendation,
  fetchAssistantWorkspace,
  submitAssistantInteractionFeedback,
  submitAssistantRecommendationDecision,
} from '../api/assistantApi'
import {
  buildDemoAssistantWorkspace,
  createDemoAssistantChat,
  createDemoAssistantRecommendation,
  recordDemoAssistantInteractionFeedback,
  recordDemoAssistantRecommendationDecision,
} from '../data/demoAssistantWorkspace'
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

const queryScope = 'assistant-workspace'

function cloneWorkspaceData(data: AssistantWorkspaceData | null) {
  return data ? structuredClone(data) : null
}

export function useAssistantWorkspace() {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const demoStateKey = String(snapshot?.user.id ?? 'anonymous')
  const [demoStates, setDemoStates] = useState<Record<string, AssistantWorkspaceData | null>>({})
  const [lastActionMessage, setLastActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)
  const [pendingQuestion, setPendingQuestion] = useState(false)
  const [pendingRecommendation, setPendingRecommendation] = useState(false)
  const [pendingDecisionId, setPendingDecisionId] = useState<number | null>(null)
  const [pendingFeedbackId, setPendingFeedbackId] = useState<number | null>(null)

  const demoData = demoStates[demoStateKey] ?? buildDemoAssistantWorkspace(snapshot ?? null)
  const queryKey = useMemo(() => [queryScope, access.apiBaseUrl, access.token] as const, [access.apiBaseUrl, access.token])

  const liveQuery = useQuery({
    queryKey,
    queryFn: () => fetchAssistantWorkspace(access.apiBaseUrl, access.token),
    enabled: liveEnabled,
  })

  const data = source === 'demo' ? demoData : liveQuery.data ?? null

  async function askQuestion(payload: AssistantChatInput) {
    setActionError(null)
    setLastActionMessage(null)
    setPendingQuestion(true)

    try {
      if (!data) {
        throw new Error('The assistant workspace is not available yet.')
      }

      let result: AssistantChatResponse

      if (source === 'demo') {
        const nextState = createDemoAssistantChat(cloneWorkspaceData(data) ?? data, payload)
        setDemoStates((current) => ({
          ...current,
          [demoStateKey]: nextState.workspace,
        }))
        result = nextState.result
      } else {
        result = await createAssistantChat(access.apiBaseUrl, access.token, payload)
        queryClient.setQueryData<AssistantWorkspaceData | undefined>(queryKey, (current) =>
          current ? appendInteraction(current, result.interaction) : current,
        )
        void queryClient.invalidateQueries({ queryKey })
      }

      setLastActionMessage('Assistant response generated with governed citations.')

      return result
    } catch (error) {
      const message = resolveActionError(error, 'The assistant could not answer that request.')
      setActionError(message)
      throw error
    } finally {
      setPendingQuestion(false)
    }
  }

  async function generateRecommendation(payload: AssistantRecommendationInput) {
    setActionError(null)
    setLastActionMessage(null)
    setPendingRecommendation(true)

    try {
      if (!data) {
        throw new Error('The assistant workspace is not available yet.')
      }

      let recommendation: AssistantRecommendation

      if (source === 'demo') {
        const nextState = createDemoAssistantRecommendation(cloneWorkspaceData(data) ?? data, payload)
        setDemoStates((current) => ({
          ...current,
          [demoStateKey]: nextState.workspace,
        }))
        recommendation = nextState.recommendation
      } else {
        recommendation = await createAssistantRecommendation(access.apiBaseUrl, access.token, payload)
        queryClient.setQueryData<AssistantWorkspaceData | undefined>(queryKey, (current) =>
          current ? appendRecommendation(current, recommendation) : current,
        )
        void queryClient.invalidateQueries({ queryKey })
      }

      setLastActionMessage('Review-only recommendation prepared successfully.')

      return recommendation
    } catch (error) {
      const message = resolveActionError(error, 'The assistant could not generate that recommendation.')
      setActionError(message)
      throw error
    } finally {
      setPendingRecommendation(false)
    }
  }

  async function recordRecommendationDecision(
    recommendationId: number,
    payload: AssistantRecommendationDecisionInput,
  ) {
    setActionError(null)
    setLastActionMessage(null)
    setPendingDecisionId(recommendationId)

    try {
      if (!data) {
        throw new Error('The assistant workspace is not available yet.')
      }

      let recommendation: AssistantRecommendation

      if (source === 'demo') {
        const nextState = recordDemoAssistantRecommendationDecision(
          cloneWorkspaceData(data) ?? data,
          recommendationId,
          payload,
          snapshot?.user.name ?? 'Operator',
        )

        setDemoStates((current) => ({
          ...current,
          [demoStateKey]: nextState,
        }))

        recommendation = nextState.recent_recommendations.find((item) => item.id === recommendationId) as AssistantRecommendation
      } else {
        recommendation = await submitAssistantRecommendationDecision(
          access.apiBaseUrl,
          access.token,
          recommendationId,
          payload,
        )

        queryClient.setQueryData<AssistantWorkspaceData | undefined>(queryKey, (current) =>
          current ? replaceRecommendation(current, recommendation) : current,
        )
        void queryClient.invalidateQueries({ queryKey })
      }

      setLastActionMessage(`Recommendation ${payload.decision} and captured for audit review.`)

      return recommendation
    } catch (error) {
      const message = resolveActionError(error, 'The recommendation decision could not be recorded.')
      setActionError(message)
      throw error
    } finally {
      setPendingDecisionId(null)
    }
  }

  async function recordInteractionFeedback(
    interactionId: number,
    payload: AssistantInteractionFeedbackInput,
  ) {
    setActionError(null)
    setLastActionMessage(null)
    setPendingFeedbackId(interactionId)

    try {
      if (!data) {
        throw new Error('The assistant workspace is not available yet.')
      }

      let interaction: AssistantInteraction

      if (source === 'demo') {
        const nextState = recordDemoAssistantInteractionFeedback(cloneWorkspaceData(data) ?? data, interactionId, payload)
        setDemoStates((current) => ({
          ...current,
          [demoStateKey]: nextState,
        }))
        interaction = nextState.recent_interactions.find((item) => item.id === interactionId) as AssistantInteraction
      } else {
        interaction = await submitAssistantInteractionFeedback(
          access.apiBaseUrl,
          access.token,
          interactionId,
          payload,
        )

        queryClient.setQueryData<AssistantWorkspaceData | undefined>(queryKey, (current) =>
          current ? replaceInteraction(current, interaction) : current,
        )
        void queryClient.invalidateQueries({ queryKey })
      }

      setLastActionMessage('Feedback recorded for assistant quality review.')

      return interaction
    } catch (error) {
      const message = resolveActionError(error, 'Feedback could not be recorded.')
      setActionError(message)
      throw error
    } finally {
      setPendingFeedbackId(null)
    }
  }

  return {
    source,
    data,
    isLoading: source === 'live' ? liveQuery.isLoading : false,
    error: source === 'live' ? (liveQuery.error as Error | null) ?? null : null,
    lastActionMessage,
    actionError,
    pendingQuestion,
    pendingRecommendation,
    pendingDecisionId,
    pendingFeedbackId,
    askQuestion,
    generateRecommendation,
    recordRecommendationDecision,
    recordInteractionFeedback,
  }
}

function appendInteraction(workspace: AssistantWorkspaceData, interaction: AssistantInteraction): AssistantWorkspaceData {
  const nextInteractions = [interaction, ...workspace.recent_interactions.filter((item) => item.id !== interaction.id)].slice(0, 8)

  return {
    ...workspace,
    summary: {
      ...workspace.summary,
      interaction_count: workspace.summary.interaction_count + 1,
      answered_interaction_count:
        workspace.summary.answered_interaction_count + (interaction.status === 'answered' ? 1 : 0),
      guardrailed_interaction_count:
        workspace.summary.guardrailed_interaction_count + (interaction.status === 'guardrailed' ? 1 : 0),
      last_interaction_at: interaction.responded_at,
    },
    recent_interactions: nextInteractions,
  }
}

function appendRecommendation(
  workspace: AssistantWorkspaceData,
  recommendation: AssistantRecommendation,
): AssistantWorkspaceData {
  const nextRecommendations = [
    recommendation,
    ...workspace.recent_recommendations.filter((item) => item.id !== recommendation.id),
  ].slice(0, 8)

  return {
    ...workspace,
    summary: {
      ...workspace.summary,
      recommendation_count: workspace.summary.recommendation_count + 1,
      pending_recommendation_count:
        workspace.summary.pending_recommendation_count + (recommendation.status === 'pending_review' ? 1 : 0),
      accepted_recommendation_count:
        workspace.summary.accepted_recommendation_count + (recommendation.status === 'accepted' ? 1 : 0),
      rejected_recommendation_count:
        workspace.summary.rejected_recommendation_count + (recommendation.status === 'rejected' ? 1 : 0),
    },
    recent_recommendations: nextRecommendations,
  }
}

function replaceRecommendation(
  workspace: AssistantWorkspaceData,
  recommendation: AssistantRecommendation,
): AssistantWorkspaceData {
  const previousRecommendation = workspace.recent_recommendations.find((item) => item.id === recommendation.id) ?? null
  const nextRecommendations = workspace.recent_recommendations.map((item) =>
    item.id === recommendation.id ? recommendation : item,
  )

  return {
    ...workspace,
    summary: {
      ...workspace.summary,
      pending_recommendation_count:
        workspace.summary.pending_recommendation_count -
        (previousRecommendation?.status === 'pending_review' ? 1 : 0) +
        (recommendation.status === 'pending_review' ? 1 : 0),
      accepted_recommendation_count:
        workspace.summary.accepted_recommendation_count -
        (previousRecommendation?.status === 'accepted' ? 1 : 0) +
        (recommendation.status === 'accepted' ? 1 : 0),
      rejected_recommendation_count:
        workspace.summary.rejected_recommendation_count -
        (previousRecommendation?.status === 'rejected' ? 1 : 0) +
        (recommendation.status === 'rejected' ? 1 : 0),
    },
    recent_recommendations: nextRecommendations,
  }
}

function replaceInteraction(workspace: AssistantWorkspaceData, interaction: AssistantInteraction): AssistantWorkspaceData {
  const previousInteraction = workspace.recent_interactions.find((item) => item.id === interaction.id) ?? null
  const previousFeedbackRecorded =
    previousInteraction?.feedback.sentiment !== null ||
    previousInteraction?.feedback.rating !== null ||
    previousInteraction?.feedback.notes !== null
  const nextFeedbackRecorded =
    interaction.feedback.sentiment !== null ||
    interaction.feedback.rating !== null ||
    interaction.feedback.notes !== null

  return {
    ...workspace,
    summary: {
      ...workspace.summary,
      feedback_recorded_count:
        workspace.summary.feedback_recorded_count -
        (previousFeedbackRecorded ? 1 : 0) +
        (nextFeedbackRecorded ? 1 : 0),
    },
    recent_interactions: workspace.recent_interactions.map((item) =>
      item.id === interaction.id ? interaction : item,
    ),
  }
}

function resolveActionError(error: unknown, fallbackMessage: string) {
  if (error instanceof ApiRequestError) {
    return error.message
  }

  if (error instanceof Error) {
    return error.message
  }

  return fallbackMessage
}
