export interface AssistantEmployeeSummary {
  id: number
  employee_code: string
  full_name: string
  email: string
}

export interface AssistantPersona {
  key: string
  label: string
}

export interface AssistantUseCase {
  key: string
  label: string
  description: string
  examples: string[]
}

export interface AssistantRecommendationScenario {
  key: string
  label: string
  description: string
  human_review_required: boolean
}

export interface AssistantCitation {
  type: string
  label: string
  reference: string
  excerpt: string
  entity_type: string
  entity_id: number
  route: string
  rank: number
  relevance_score: number | null
  evidence_strength: string
  freshness_label: string | null
  captured_at: string | null
}

export interface AssistantGuardrail {
  code: string
  message: string
  action_path?: string
}

export interface AssistantConversation {
  id: number
  title: string
  persona: string
  status: string
  metadata: Record<string, unknown>
  last_interacted_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface AssistantInteraction {
  id: number
  conversation_id: number
  interaction_type: string
  use_case: string
  question: string
  answer: string
  status: string
  confidence_score: number | null
  citations: AssistantCitation[]
  guardrails: AssistantGuardrail[]
  metadata: Record<string, unknown>
  feedback: {
    rating: number | null
    sentiment: 'positive' | 'negative' | 'neutral' | null
    notes: string | null
  }
  responded_at: string | null
  created_at: string | null
  updated_at: string | null
  conversation?: AssistantConversation
}

export interface AssistantRecommendationAction {
  type: string
  label: string
  path: string
  requires_confirmation: boolean
  notes?: string
}

export interface AssistantRecommendation {
  id: number
  conversation_id: number | null
  scenario: string
  title: string
  summary: string
  rationale: string[]
  confidence_score: number | null
  suggested_actions: AssistantRecommendationAction[]
  supporting_citations: AssistantCitation[]
  status: 'pending_review' | 'accepted' | 'rejected' | string
  human_review_required: boolean
  decision: 'accepted' | 'rejected' | null
  decision_notes: string | null
  decided_at: string | null
  metadata: Record<string, unknown>
  created_at: string | null
  updated_at: string | null
  employee?: AssistantEmployeeSummary | null
  decided_by?: {
    id: number
    name: string
  } | null
}

export interface AssistantAuditEvent {
  id: number
  event_type: string
  label: string
  summary: string
  entity_type: string | null
  entity_id: string | null
  created_at: string | null
  metadata: Record<string, unknown>
}

export interface AssistantReviewAnalytics {
  answer_quality: {
    answered_count: number
    cited_answer_count: number
    citation_coverage_percent: number
    feedback_recorded_count: number
    average_feedback_rating: number | null
    positive_feedback_count: number
    negative_feedback_count: number
    guardrailed_interaction_count: number
  }
  recommendation_queue: {
    pending_review_count: number
    accepted_count: number
    rejected_count: number
    stale_pending_review_count: number
    oldest_pending_created_at: string | null
    latest_decision_at: string | null
  }
  audit_activity: {
    event_count: number
    workspace_view_count: number
    interaction_generated_count: number
    feedback_event_count: number
    recommendation_generated_count: number
    recommendation_decision_count: number
    last_event_at: string | null
  }
}

export interface AssistantWorkspaceData {
  disclosure: string
  persona: AssistantPersona
  linked_employee: AssistantEmployeeSummary | null
  subject_options: AssistantEmployeeSummary[]
  capabilities: {
    supported_use_cases: AssistantUseCase[]
    approved_recommendation_scenarios: AssistantRecommendationScenario[]
    guardrails: string[]
  }
  permissions: {
    can_chat: boolean
    can_generate_recommendations: boolean
  }
  summary: {
    interaction_count: number
    recommendation_count: number
    pending_recommendation_count: number
    answered_interaction_count: number
    guardrailed_interaction_count: number
    feedback_recorded_count: number
    accepted_recommendation_count: number
    rejected_recommendation_count: number
    last_interaction_at: string | null
  }
  review_analytics: AssistantReviewAnalytics
  audit_timeline: AssistantAuditEvent[]
  recent_interactions: AssistantInteraction[]
  recent_recommendations: AssistantRecommendation[]
}

export interface AssistantChatResponse {
  conversation: AssistantConversation
  interaction: AssistantInteraction
}

export interface AssistantChatInput {
  conversation_id?: number | null
  question: string
  use_case?: string | null
  subject_employee_id?: number | null
}

export interface AssistantRecommendationInput {
  conversation_id?: number | null
  scenario: string
  subject_employee_id?: number | null
  context_note?: string | null
}

export interface AssistantRecommendationDecisionInput {
  decision: 'accepted' | 'rejected'
  decision_notes?: string | null
}

export interface AssistantInteractionFeedbackInput {
  rating?: number | null
  sentiment: 'positive' | 'negative' | 'neutral'
  notes?: string | null
}
