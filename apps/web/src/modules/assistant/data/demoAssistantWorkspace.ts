import type { AccessSnapshot } from '../../access/types'
import type {
  AssistantAuditEvent,
  AssistantChatInput,
  AssistantChatResponse,
  AssistantConversation,
  AssistantEmployeeSummary,
  AssistantInteraction,
  AssistantInteractionFeedbackInput,
  AssistantRecommendation,
  AssistantRecommendationDecisionInput,
  AssistantRecommendationInput,
  AssistantWorkspaceData,
} from '../types'

type DemoEmployeeRecord = AssistantEmployeeSummary & {
  manager_id: number | null
  employment_status: 'active' | 'probation' | 'notice_period'
}

const demoEmployees: DemoEmployeeRecord[] = [
  {
    id: 2101,
    employee_code: 'PAY-2101',
    full_name: 'Aman Verma',
    email: 'aman.verma@phoenixhrms.test',
    manager_id: null,
    employment_status: 'active',
  },
  {
    id: 2102,
    employee_code: 'PAY-2102',
    full_name: 'Nisha Rao',
    email: 'nisha.rao@phoenixhrms.test',
    manager_id: null,
    employment_status: 'active',
  },
  {
    id: 2103,
    employee_code: 'PAY-2103',
    full_name: 'Kabir Malik',
    email: 'kabir.malik@phoenixhrms.test',
    manager_id: 2101,
    employment_status: 'notice_period',
  },
  {
    id: 2106,
    employee_code: 'PAY-2106',
    full_name: 'Sonia Menon',
    email: 'sonia.menon@phoenixhrms.test',
    manager_id: 2101,
    employment_status: 'active',
  },
]

const staticGuardrails = [
  'Critical approvals, payroll changes, employee status changes, and compensation changes are not executed by the assistant in v1.',
  'Responses stay limited to the current tenant and the requesting session permission scope.',
  'Recommendations remain review-only until a human explicitly accepts or rejects them.',
  'Citations expose source context so operators can verify why the assistant responded the way it did.',
]

const staticUseCases = [
  {
    key: 'leave_balance',
    label: 'Leave balance and booking posture',
    description:
      'Summarizes available leave, booked leave, and the most constrained leave pool for an accessible employee.',
    examples: ['How many leave days do I have left?', 'Show my annual leave balance.'],
  },
  {
    key: 'attendance_summary',
    label: 'Attendance and time posture',
    description:
      'Explains today or recent attendance, late arrivals, and missing checkout patterns from governed attendance records.',
    examples: ['Did I check in today?', 'Summarize my recent attendance posture.'],
  },
  {
    key: 'payslip_summary',
    label: 'Payslip and payroll release summary',
    description: 'Surfaces the latest finalized payslip references and net-pay posture without modifying payroll.',
    examples: ['Show my last payslip.', 'What was my latest net salary?'],
  },
  {
    key: 'policy_document',
    label: 'Policy and document acknowledgement summary',
    description:
      'Lists pending policy acknowledgements and searchable policy documents available in the current session scope.',
    examples: ['Which policies still need my acknowledgement?', 'Find remote work policy documents.'],
  },
  {
    key: 'learning_summary',
    label: 'Learning assignment posture',
    description: 'Summarizes assigned, overdue, and completed learning work for an accessible employee.',
    examples: ['What training is still due?', 'Summarize my learning assignments.'],
  },
]

const staticRecommendationScenarios = [
  {
    key: 'learning_next_best_action',
    label: 'Learning next-best action',
    description: 'Suggests the next governed learning step and never auto-assigns or completes training.',
    human_review_required: true,
  },
  {
    key: 'policy_acknowledgement_follow_up',
    label: 'Policy acknowledgement follow-up',
    description: 'Highlights pending policy acknowledgements and records a human decision before any manual follow-up.',
    human_review_required: true,
  },
  {
    key: 'attendance_follow_up',
    label: 'Attendance follow-up',
    description: 'Suggests a human review path for lateness or missing checkout patterns without editing attendance.',
    human_review_required: true,
  },
]

export function buildDemoAssistantWorkspace(snapshot: AccessSnapshot | null): AssistantWorkspaceData | null {
  if (!snapshot) {
    return null
  }

  const linkedEmployee = resolveLinkedEmployee(snapshot)
  const subjectOptions = buildSubjectOptions(snapshot, linkedEmployee)
  const recentInteractions = buildSeedInteractions(snapshot, linkedEmployee)
  const recentRecommendations = buildSeedRecommendations(snapshot, linkedEmployee)
  const auditTimeline = buildSeedAuditTimeline(recentInteractions, recentRecommendations)

  return {
    disclosure:
      'Responses are AI-generated from governed tenant data and must not be used to auto-execute critical HR actions.',
    persona: resolvePersona(snapshot, linkedEmployee),
    linked_employee: linkedEmployee,
    subject_options: subjectOptions,
    capabilities: {
      supported_use_cases: staticUseCases,
      approved_recommendation_scenarios: staticRecommendationScenarios,
      guardrails: staticGuardrails,
    },
    permissions: {
      can_chat: hasAnyPermission(snapshot.user.permissions, ['ai.view', 'ai.recommend']),
      can_generate_recommendations: snapshot.user.permissions.includes('ai.recommend'),
    },
    summary: buildWorkspaceSummary(recentInteractions, recentRecommendations),
    review_analytics: buildReviewAnalytics(recentInteractions, recentRecommendations, auditTimeline),
    audit_timeline: auditTimeline,
    recent_interactions: recentInteractions,
    recent_recommendations: recentRecommendations,
  }
}

export function createDemoAssistantChat(
  workspace: AssistantWorkspaceData,
  payload: AssistantChatInput,
): { workspace: AssistantWorkspaceData; result: AssistantChatResponse } {
  const conversationId =
    payload.conversation_id ??
    workspace.recent_interactions[0]?.conversation_id ??
    workspace.recent_recommendations[0]?.conversation_id ??
    9401
  const conversation = buildConversation(workspace, conversationId, payload.question)
  const subject = resolveSubjectEmployee(workspace, payload.subject_employee_id ?? null)
  const interaction = buildDemoInteraction(
    workspace,
    workspace.summary.interaction_count + 501,
    conversation.id,
    payload.question,
    payload.use_case ?? null,
    subject,
  )

  return {
    workspace: withDerivedReviewState({
      ...workspace,
      audit_timeline: [
        buildAuditEvent(
          10000 + interaction.id,
          'ai.interaction.generated',
          'Answer generated',
          `Generated a ${interaction.status} response for the ${interaction.use_case} use case.`,
          interaction.responded_at,
          {
            interaction_id: interaction.id,
            use_case: interaction.use_case,
            status: interaction.status,
          },
        ),
        ...workspace.audit_timeline,
      ],
      recent_interactions: [interaction, ...workspace.recent_interactions].slice(0, 8),
    }),
    result: {
      conversation,
      interaction,
    },
  }
}

export function createDemoAssistantRecommendation(
  workspace: AssistantWorkspaceData,
  payload: AssistantRecommendationInput,
): { workspace: AssistantWorkspaceData; recommendation: AssistantRecommendation } {
  const subject = resolveSubjectEmployee(workspace, payload.subject_employee_id ?? null)
  const recommendation = buildDemoRecommendation(
    workspace,
    workspace.summary.recommendation_count + 801,
    payload,
    subject,
  )

  return {
    workspace: withDerivedReviewState({
      ...workspace,
      audit_timeline: [
        buildAuditEvent(
          20000 + recommendation.id,
          'ai.recommendation.generated',
          'Recommendation prepared',
          `Prepared a ${recommendation.scenario} recommendation for human review.`,
          recommendation.created_at,
          {
            recommendation_id: recommendation.id,
            scenario: recommendation.scenario,
          },
        ),
        ...workspace.audit_timeline,
      ],
      recent_recommendations: [recommendation, ...workspace.recent_recommendations].slice(0, 8),
    }),
    recommendation,
  }
}

export function recordDemoAssistantRecommendationDecision(
  workspace: AssistantWorkspaceData,
  recommendationId: number,
  payload: AssistantRecommendationDecisionInput,
  actorName: string,
): AssistantWorkspaceData {
  const nextRecommendations = workspace.recent_recommendations.map((recommendation) => {
    if (recommendation.id !== recommendationId) {
      return recommendation
    }

    return {
      ...recommendation,
      status: payload.decision,
      decision: payload.decision,
      decision_notes: normalizeOptionalText(payload.decision_notes),
      decided_at: '2026-07-02T10:30:00+05:30',
      decided_by: {
        id: recommendation.employee?.id ?? 0,
        name: actorName,
      },
      updated_at: '2026-07-02T10:30:00+05:30',
    }
  })

  return withDerivedReviewState({
    ...workspace,
    audit_timeline: [
      buildAuditEvent(
        30000 + recommendationId,
        'ai.recommendation.decision_recorded',
        'Recommendation decision recorded',
        `Recorded a ${payload.decision} decision for the ${nextRecommendations.find((item) => item.id === recommendationId)?.scenario ?? 'governed'} recommendation.`,
        '2026-07-02T10:30:00+05:30',
        {
          recommendation_id: recommendationId,
          scenario: nextRecommendations.find((item) => item.id === recommendationId)?.scenario ?? 'governed',
          decision: payload.decision,
        },
      ),
      ...workspace.audit_timeline,
    ],
    recent_recommendations: nextRecommendations,
  })
}

export function recordDemoAssistantInteractionFeedback(
  workspace: AssistantWorkspaceData,
  interactionId: number,
  payload: AssistantInteractionFeedbackInput,
): AssistantWorkspaceData {
  return withDerivedReviewState({
    ...workspace,
    audit_timeline: [
      buildAuditEvent(
        40000 + interactionId,
        'ai.interaction.feedback_recorded',
        'Feedback recorded',
        `Recorded ${payload.sentiment} feedback${payload.rating ? ` with rating ${payload.rating}` : ''}.`,
        '2026-07-02T10:32:00+05:30',
        {
          interaction_id: interactionId,
          sentiment: payload.sentiment,
          rating: payload.rating ?? null,
        },
      ),
      ...workspace.audit_timeline,
    ],
    recent_interactions: workspace.recent_interactions.map((interaction) => {
      if (interaction.id !== interactionId) {
        return interaction
      }

      return {
        ...interaction,
        feedback: {
          rating: payload.rating ?? null,
          sentiment: payload.sentiment,
          notes: normalizeOptionalText(payload.notes),
        },
        updated_at: '2026-07-02T10:32:00+05:30',
      }
    }),
  })
}

function resolveLinkedEmployee(snapshot: AccessSnapshot) {
  const snapshotEmployee = snapshot.user.employee
  const linkedEmployeeId = snapshotEmployee?.id ?? null

  if (linkedEmployeeId === null) {
    return null
  }

  if (!snapshotEmployee) {
    return null
  }

  return demoEmployees.find((employee) => employee.id === linkedEmployeeId) ?? {
    id: snapshotEmployee.id,
    employee_code: snapshotEmployee.employee_code,
    full_name: snapshotEmployee.full_name,
    email: snapshotEmployee.email ?? '',
  }
}

function buildSubjectOptions(snapshot: AccessSnapshot, linkedEmployee: AssistantEmployeeSummary | null) {
  const permissions = snapshot.user.permissions

  if (hasAnyPermission(permissions, ['employee.manage', 'payroll.view', 'learning.assign'])) {
    return demoEmployees.filter((employee) => employee.employment_status !== 'probation').map(toEmployeeSummary)
  }

  if (!linkedEmployee) {
    return []
  }

  const includeDirectReports = snapshot.user.roles.includes('manager') || hasAnyPermission(permissions, ['leave.approve', 'attendance.approve'])

  return demoEmployees
    .filter((employee) => employee.id === linkedEmployee.id || (includeDirectReports && employee.manager_id === linkedEmployee.id))
    .map(toEmployeeSummary)
}

function buildSeedInteractions(snapshot: AccessSnapshot, linkedEmployee: AssistantEmployeeSummary | null): AssistantInteraction[] {
  const seedSubject = linkedEmployee ?? demoEmployees[0]
  const seedWorkspace = {
    linked_employee: linkedEmployee,
  }

  const first = buildDemoInteraction(
    seedWorkspace,
    401,
    9401,
    'How many leave days do I have left?',
    'leave_balance',
    seedSubject,
    '2026-07-02T09:18:00+05:30',
  )

  const second = buildDemoInteraction(
    seedWorkspace,
    400,
    9401,
    'Approve this leave request.',
    null,
    seedSubject,
    '2026-07-02T09:03:00+05:30',
  )

  const interactions = [first, second]

  if (hasAnyPermission(snapshot.user.permissions, ['employee.manage', 'payslip.view'])) {
    interactions.push(
      buildDemoInteraction(
        seedWorkspace,
        399,
        9399,
        'Show the latest payslip summary.',
        'payslip_summary',
        seedSubject,
        '2026-07-01T17:42:00+05:30',
      ),
    )
  }

  return interactions
}

function buildSeedRecommendations(snapshot: AccessSnapshot, linkedEmployee: AssistantEmployeeSummary | null): AssistantRecommendation[] {
  if (!snapshot.user.permissions.includes('ai.recommend')) {
    return []
  }

  const subject = linkedEmployee ?? demoEmployees[0]
  const seedWorkspace = {
    recent_interactions: [] as AssistantInteraction[],
  }

  return [
    buildDemoRecommendation(
      seedWorkspace,
      801,
      {
        scenario: snapshot.user.roles.includes('manager') ? 'attendance_follow_up' : 'learning_next_best_action',
        subject_employee_id: subject.id,
        context_note: 'Seeded demo recommendation',
      },
      subject,
      'pending_review',
      '2026-07-02T08:55:00+05:30',
    ),
  ]
}

function withDerivedReviewState(workspace: AssistantWorkspaceData): AssistantWorkspaceData {
  const auditTimeline = sortAuditTimeline(workspace.audit_timeline)

  return {
    ...workspace,
    summary: buildWorkspaceSummary(workspace.recent_interactions, workspace.recent_recommendations),
    review_analytics: buildReviewAnalytics(workspace.recent_interactions, workspace.recent_recommendations, auditTimeline),
    audit_timeline: auditTimeline,
  }
}

function buildWorkspaceSummary(
  interactions: AssistantInteraction[],
  recommendations: AssistantRecommendation[],
): AssistantWorkspaceData['summary'] {
  return {
    interaction_count: interactions.length,
    recommendation_count: recommendations.length,
    pending_recommendation_count: recommendations.filter((item) => item.status === 'pending_review').length,
    answered_interaction_count: interactions.filter((item) => item.status === 'answered').length,
    guardrailed_interaction_count: interactions.filter((item) => item.status === 'guardrailed').length,
    feedback_recorded_count: interactions.filter(
      (item) => item.feedback.sentiment !== null || item.feedback.rating !== null || item.feedback.notes !== null,
    ).length,
    accepted_recommendation_count: recommendations.filter((item) => item.status === 'accepted').length,
    rejected_recommendation_count: recommendations.filter((item) => item.status === 'rejected').length,
    last_interaction_at: interactions[0]?.responded_at ?? null,
  }
}

function buildReviewAnalytics(
  interactions: AssistantInteraction[],
  recommendations: AssistantRecommendation[],
  auditTimeline: AssistantAuditEvent[],
): AssistantWorkspaceData['review_analytics'] {
  const answeredInteractions = interactions.filter((item) => item.status === 'answered')
  const citedAnswers = answeredInteractions.filter((item) => item.citations.length > 0)
  const feedbackRatings = interactions
    .map((item) => item.feedback.rating)
    .filter((rating): rating is number => rating !== null)
  const pendingRecommendations = recommendations
    .filter((item) => item.status === 'pending_review')
    .sort((left, right) => (left.created_at ?? '').localeCompare(right.created_at ?? ''))
  const decidedRecommendations = recommendations
    .filter((item) => item.decided_at)
    .sort((left, right) => (right.decided_at ?? '').localeCompare(left.decided_at ?? ''))

  return {
    answer_quality: {
      answered_count: answeredInteractions.length,
      cited_answer_count: citedAnswers.length,
      citation_coverage_percent:
        answeredInteractions.length === 0 ? 0 : Math.round((citedAnswers.length / answeredInteractions.length) * 1000) / 10,
      feedback_recorded_count: interactions.filter(
        (item) => item.feedback.sentiment !== null || item.feedback.rating !== null || item.feedback.notes !== null,
      ).length,
      average_feedback_rating:
        feedbackRatings.length > 0
          ? Math.round((feedbackRatings.reduce((sum, value) => sum + value, 0) / feedbackRatings.length) * 100) / 100
          : null,
      positive_feedback_count: interactions.filter((item) => item.feedback.sentiment === 'positive').length,
      negative_feedback_count: interactions.filter((item) => item.feedback.sentiment === 'negative').length,
      guardrailed_interaction_count: interactions.filter((item) => item.status === 'guardrailed').length,
    },
    recommendation_queue: {
      pending_review_count: pendingRecommendations.length,
      accepted_count: recommendations.filter((item) => item.status === 'accepted').length,
      rejected_count: recommendations.filter((item) => item.status === 'rejected').length,
      stale_pending_review_count: pendingRecommendations.filter((item) => (item.created_at ?? '') <= '2026-07-01T10:00:00+05:30').length,
      oldest_pending_created_at: pendingRecommendations[0]?.created_at ?? null,
      latest_decision_at: decidedRecommendations[0]?.decided_at ?? null,
    },
    audit_activity: {
      event_count: auditTimeline.length,
      workspace_view_count: auditTimeline.filter((event) => event.event_type === 'ai.assistant.workspace.viewed').length,
      interaction_generated_count: auditTimeline.filter((event) => event.event_type === 'ai.interaction.generated').length,
      feedback_event_count: auditTimeline.filter((event) => event.event_type === 'ai.interaction.feedback_recorded').length,
      recommendation_generated_count: auditTimeline.filter((event) => event.event_type === 'ai.recommendation.generated').length,
      recommendation_decision_count: auditTimeline.filter((event) => event.event_type === 'ai.recommendation.decision_recorded').length,
      last_event_at: auditTimeline[0]?.created_at ?? null,
    },
  }
}

function buildSeedAuditTimeline(
  interactions: AssistantInteraction[],
  recommendations: AssistantRecommendation[],
): AssistantAuditEvent[] {
  return sortAuditTimeline([
    buildAuditEvent(
      9001,
      'ai.assistant.workspace.viewed',
      'Workspace viewed',
      `Loaded ${interactions.length} interaction(s) and ${recommendations.length} recommendation(s) into the governed assistant workspace.`,
      '2026-07-02T09:20:00+05:30',
      {
        interaction_count: interactions.length,
        recommendation_count: recommendations.length,
      },
    ),
    ...interactions.slice(0, 3).map((interaction) =>
      buildAuditEvent(
        9100 + interaction.id,
        'ai.interaction.generated',
        'Answer generated',
        `Generated a ${interaction.status} response for the ${interaction.use_case} use case.`,
        interaction.responded_at,
        {
          interaction_id: interaction.id,
          use_case: interaction.use_case,
          status: interaction.status,
        },
      ),
    ),
    ...recommendations.slice(0, 3).map((recommendation) =>
      buildAuditEvent(
        9200 + recommendation.id,
        'ai.recommendation.generated',
        'Recommendation prepared',
        `Prepared a ${recommendation.scenario} recommendation for human review.`,
        recommendation.created_at,
        {
          recommendation_id: recommendation.id,
          scenario: recommendation.scenario,
        },
      ),
    ),
  ])
}

function buildAuditEvent(
  id: number,
  eventType: string,
  label: string,
  summary: string,
  createdAt: string | null,
  metadata: Record<string, unknown>,
): AssistantAuditEvent {
  return {
    id,
    event_type: eventType,
    label,
    summary,
    entity_type: eventType.includes('recommendation') ? 'ai_recommendation' : eventType.includes('interaction') ? 'ai_interaction' : 'ai_workspace',
    entity_id: metadata.recommendation_id ? String(metadata.recommendation_id) : metadata.interaction_id ? String(metadata.interaction_id) : null,
    created_at: createdAt,
    metadata,
  }
}

function sortAuditTimeline(events: AssistantAuditEvent[]) {
  return [...events]
    .sort((left, right) => (right.created_at ?? '').localeCompare(left.created_at ?? ''))
    .slice(0, 8)
}

function buildConversation(
  workspace: AssistantWorkspaceData,
  conversationId: number,
  question: string,
): AssistantConversation {
  return {
    id: conversationId,
    title: question.slice(0, 120) || 'New AI conversation',
    persona: workspace.persona.key,
    status: 'active',
    metadata: {
      created_from: 'demo',
    },
    last_interacted_at: '2026-07-02T10:20:00+05:30',
    created_at: '2026-07-02T08:45:00+05:30',
    updated_at: '2026-07-02T10:20:00+05:30',
  }
}

function buildDemoInteraction(
  workspace: Pick<AssistantWorkspaceData, 'linked_employee'>,
  interactionId: number,
  conversationId: number,
  question: string,
  explicitUseCase: string | null,
  subject: AssistantEmployeeSummary | null,
  respondedAt = '2026-07-02T10:20:00+05:30',
): AssistantInteraction {
  if (isMutationOrApprovalRequest(question)) {
    return {
      id: interactionId,
      conversation_id: conversationId,
      interaction_type: 'guardrail',
      use_case: 'guardrail',
      question,
      answer:
        'This assistant can explain governed data and propose review-only recommendations, but it does not approve, mutate, or auto-execute critical HR actions in v1. Use the linked operational workspace for the controlled action path.',
      status: 'guardrailed',
      confidence_score: 0.99,
      citations: [],
      guardrails: [
        {
          code: 'approval_required',
          message: 'Human approval is required for critical actions, and no backend mutation was executed.',
        },
        {
          code: 'read_only_v1',
          message: 'Supported v1 experiences are limited to read-focused questions and review-only recommendations.',
        },
      ],
      metadata: {
        question,
        supported_use_cases: staticUseCases.map((item) => item.key),
      },
      feedback: {
        rating: null,
        sentiment: null,
        notes: null,
      },
      responded_at: respondedAt,
      created_at: respondedAt,
      updated_at: respondedAt,
    }
  }

  const useCase = resolveUseCase(question, explicitUseCase)
  const demoSubject = subject ?? workspace.linked_employee

  if (!demoSubject && useCase !== 'policy_document') {
    return {
      id: interactionId,
      conversation_id: conversationId,
      interaction_type: 'guardrail',
      use_case: 'missing_subject',
      question,
      answer:
        'A linked or explicitly selected employee profile is required for this question, and the current session does not resolve one yet.',
      status: 'guardrailed',
      confidence_score: 0.95,
      citations: [],
      guardrails: [
        {
          code: 'employee_context_required',
          message: 'Choose an employee context or use a linked self-service profile before retrying this query.',
          action_path: '/self-service/profile',
        },
      ],
      metadata: {
        required_route: '/self-service/profile',
      },
      feedback: {
        rating: null,
        sentiment: null,
        notes: null,
      },
      responded_at: respondedAt,
      created_at: respondedAt,
      updated_at: respondedAt,
    }
  }

  return buildUseCaseInteraction(interactionId, conversationId, question, useCase, demoSubject, respondedAt)
}

function buildUseCaseInteraction(
  interactionId: number,
  conversationId: number,
  question: string,
  useCase: string,
  subject: AssistantEmployeeSummary | null,
  respondedAt: string,
): AssistantInteraction {
  if (subject === null && useCase === 'policy_document') {
    return buildPolicyInteraction(interactionId, conversationId, question, null, respondedAt)
  }

  switch (useCase) {
    case 'leave_balance':
      return buildLeaveInteraction(interactionId, conversationId, question, subject as AssistantEmployeeSummary, respondedAt)
    case 'attendance_summary':
      return buildAttendanceInteraction(interactionId, conversationId, question, subject as AssistantEmployeeSummary, respondedAt)
    case 'payslip_summary':
      return buildPayslipInteraction(interactionId, conversationId, question, subject as AssistantEmployeeSummary, respondedAt)
    case 'policy_document':
      return buildPolicyInteraction(interactionId, conversationId, question, subject, respondedAt)
    case 'learning_summary':
      return buildLearningInteraction(interactionId, conversationId, question, subject as AssistantEmployeeSummary, respondedAt)
    default:
      return {
        id: interactionId,
        conversation_id: conversationId,
        interaction_type: 'guardrail',
        use_case: 'unsupported',
        question,
        answer:
          'This v1 assistant currently supports governed leave, attendance, payslip, policy, and learning posture questions only. Rephrase the request into one of those areas or use the targeted workspace for the action you need.',
        status: 'guardrailed',
        confidence_score: 0.82,
        citations: [],
        guardrails: [
          {
            code: 'use_case_not_supported',
            message: 'The requested use case is outside the approved Sprint 10 assistant baseline.',
          },
        ],
        metadata: {
          question,
          supported_use_cases: staticUseCases.map((item) => item.key),
        },
        feedback: {
          rating: null,
          sentiment: null,
          notes: null,
        },
        responded_at: respondedAt,
        created_at: respondedAt,
        updated_at: respondedAt,
      }
  }
}

function buildLeaveInteraction(
  interactionId: number,
  conversationId: number,
  question: string,
  subject: AssistantEmployeeSummary,
  respondedAt: string,
): AssistantInteraction {
  const signal = demoSignalsForEmployee(subject.id)

  return {
    id: interactionId,
    conversation_id: conversationId,
    interaction_type: 'answer',
    use_case: 'leave_balance',
    question,
    answer: `${subject.full_name} currently has ${signal.leave.available} available leave day(s) across 2 governed balance record(s). The largest available pool is Annual Leave with ${signal.leave.primary_pool} day(s) available, while ${signal.leave.booked} day(s) are already booked and ${signal.leave.used} day(s) have been used in the active period.`,
    status: 'answered',
    confidence_score: 0.92,
    citations: rankDemoCitations([
      {
        type: 'leave_balance',
        label: 'Annual Leave balance',
        reference: `Leave balance #LB-${subject.employee_code}`,
        excerpt: `Available ${signal.leave.primary_pool} day(s), booked ${signal.leave.booked} day(s), used ${signal.leave.used} day(s).`,
        entity_type: 'leave_balance',
        entity_id: subject.id,
        route: '/leave/requests',
      },
    ]),
    guardrails: [],
    metadata: {
      subject_employee_id: subject.id,
      balance_count: 2,
    },
    feedback: {
      rating: null,
      sentiment: null,
      notes: null,
    },
    responded_at: respondedAt,
    created_at: respondedAt,
    updated_at: respondedAt,
  }
}

function buildAttendanceInteraction(
  interactionId: number,
  conversationId: number,
  question: string,
  subject: AssistantEmployeeSummary,
  respondedAt: string,
): AssistantInteraction {
  const signal = demoSignalsForEmployee(subject.id)
  const isTodayQuestion = question.toLowerCase().includes('today')

  return {
    id: interactionId,
    conversation_id: conversationId,
    interaction_type: 'answer',
    use_case: 'attendance_summary',
    question,
    answer: isTodayQuestion
      ? `${subject.full_name} has a present attendance record for 2026-07-02. Check-in is 2026-07-02 09:06:00, check-out is ${signal.attendance.open_checkout ? 'still open' : '2026-07-02 18:11:00'}, and worked minutes currently total ${signal.attendance.worked_minutes_today}.`
      : `In the last 14 days, ${subject.full_name} has ${signal.attendance.record_count} attendance record(s), ${signal.attendance.late_count} late arrival(s), ${signal.attendance.open_checkout_count} open checkout record(s), and ${signal.attendance.overtime_minutes} total overtime minute(s) captured in governed attendance data.`,
    status: 'answered',
    confidence_score: 0.9,
    citations: rankDemoCitations([
      {
        type: 'attendance_record',
        label: isTodayQuestion ? 'Today attendance' : 'Recent attendance window',
        reference: `Attendance record #AT-${subject.employee_code}`,
        excerpt: `Status present, worked ${signal.attendance.worked_minutes_today} minute(s), overtime ${signal.attendance.overtime_minutes} minute(s).`,
        entity_type: 'attendance_record',
        entity_id: subject.id,
        route: '/attendance/my-attendance/history',
      },
    ]),
    guardrails: [],
    metadata: {
      subject_employee_id: subject.id,
      window: isTodayQuestion ? 'today' : '14_days',
    },
    feedback: {
      rating: null,
      sentiment: null,
      notes: null,
    },
    responded_at: respondedAt,
    created_at: respondedAt,
    updated_at: respondedAt,
  }
}

function buildPayslipInteraction(
  interactionId: number,
  conversationId: number,
  question: string,
  subject: AssistantEmployeeSummary,
  respondedAt: string,
): AssistantInteraction {
  const signal = demoSignalsForEmployee(subject.id)

  return {
    id: interactionId,
    conversation_id: conversationId,
    interaction_type: 'answer',
    use_case: 'payslip_summary',
    question,
    answer: `The latest finalized payslip visible for ${subject.full_name} is ${signal.payslip.slip_number} dated 2026-06-30, with net salary ${signal.payslip.currency} ${signal.payslip.net_salary.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}. 2 recent finalized payslip reference(s) are available in this session.`,
    status: 'answered',
    confidence_score: 0.93,
    citations: rankDemoCitations([
      {
        type: 'payslip',
        label: signal.payslip.slip_number,
        reference: `Payslip #PS-${subject.employee_code}`,
        excerpt: `Period 2026-06-01 to 2026-06-30, net salary ${signal.payslip.currency} ${signal.payslip.net_salary.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}.`,
        entity_type: 'payslip',
        entity_id: subject.id,
        route: '/payroll/my-pay',
      },
    ]),
    guardrails: [],
    metadata: {
      subject_employee_id: subject.id,
      payslip_count: 2,
    },
    feedback: {
      rating: null,
      sentiment: null,
      notes: null,
    },
    responded_at: respondedAt,
    created_at: respondedAt,
    updated_at: respondedAt,
  }
}

function buildPolicyInteraction(
  interactionId: number,
  conversationId: number,
  question: string,
  subject: AssistantEmployeeSummary | null,
  respondedAt: string,
): AssistantInteraction {
  const pendingCount = subject ? demoSignalsForEmployee(subject.id).policy.pending_acknowledgements : 0

  return {
    id: interactionId,
    conversation_id: conversationId,
    interaction_type: 'answer',
    use_case: 'policy_document',
    question,
    answer: subject
      ? `2 policy document(s) match the current request. The most recent visible match is Remote work policy FY26. ${subject.full_name} also has ${pendingCount} pending policy acknowledgement(s).`
      : '2 policy document(s) match the current request. The most recent visible match is Remote work policy FY26.',
    status: 'answered',
    confidence_score: 0.86,
    citations: rankDemoCitations([
      {
        type: 'policy_document',
        label: 'Remote work policy FY26',
        reference: 'Document #POL-REMOTE-2026',
        excerpt: 'Updated remote work expectations and travel approval guardrails.',
        entity_type: 'document',
        entity_id: 1201,
        route: '/self-service/documents',
      },
      ...(subject
        ? [
            {
              type: 'policy_acknowledgement',
              label: 'Remote work policy FY26 acknowledgement',
              reference: `Policy acknowledgement #PA-${subject.employee_code}`,
              excerpt: `Status assigned, due 2026-07-05.`,
              entity_type: 'policy_acknowledgement',
              entity_id: subject.id,
              route: '/self-service/documents',
            },
          ]
        : []),
    ]),
    guardrails: [],
    metadata: {
      policy_document_count: 2,
      acknowledgement_count: subject ? pendingCount : 0,
      subject_employee_id: subject?.id ?? null,
    },
    feedback: {
      rating: null,
      sentiment: null,
      notes: null,
    },
    responded_at: respondedAt,
    created_at: respondedAt,
    updated_at: respondedAt,
  }
}

function buildLearningInteraction(
  interactionId: number,
  conversationId: number,
  question: string,
  subject: AssistantEmployeeSummary,
  respondedAt: string,
): AssistantInteraction {
  const signal = demoSignalsForEmployee(subject.id)

  return {
    id: interactionId,
    conversation_id: conversationId,
    interaction_type: 'answer',
    use_case: 'learning_summary',
    question,
    answer: `${subject.full_name} has ${signal.learning.total} governed learning assignment target(s): ${signal.learning.assigned} assigned, ${signal.learning.in_progress} in progress, ${signal.learning.completed} completed, and ${signal.learning.overdue} overdue.`,
    status: 'answered',
    confidence_score: 0.9,
    citations: rankDemoCitations([
      {
        type: 'learning_assignment_target',
        label: signal.learning.priority_title,
        reference: `Learning target #LRN-${subject.employee_code}`,
        excerpt: `Status ${signal.learning.overdue ? 'assigned' : 'in_progress'}, due 2026-07-08, progress ${signal.learning.progress_percent}%.`,
        entity_type: 'learning_assignment_target',
        entity_id: subject.id,
        route: '/learning/my-learning',
      },
    ]),
    guardrails: [],
    metadata: {
      subject_employee_id: subject.id,
      target_count: signal.learning.total,
    },
    feedback: {
      rating: null,
      sentiment: null,
      notes: null,
    },
    responded_at: respondedAt,
    created_at: respondedAt,
    updated_at: respondedAt,
  }
}

function buildDemoRecommendation(
  workspace: Pick<AssistantWorkspaceData, 'recent_interactions'>,
  recommendationId: number,
  payload: AssistantRecommendationInput,
  subject: AssistantEmployeeSummary | null,
  status: AssistantRecommendation['status'] = 'pending_review',
  createdAt = '2026-07-02T10:24:00+05:30',
): AssistantRecommendation {
  const scenario = payload.scenario
  const normalizedContext = normalizeOptionalText(payload.context_note)

  if (scenario === 'attendance_follow_up') {
    const signal = demoSignalsForEmployee(subject?.id ?? 2101)

    return {
      id: recommendationId,
      conversation_id: payload.conversation_id ?? workspace.recent_interactions[0]?.conversation_id ?? null,
      scenario,
      title: `Review attendance follow-up for ${subject?.full_name ?? 'the selected employee'}`,
      summary:
        `${signal.attendance.late_count + signal.attendance.open_checkout_count} attendance record(s) need manual review for lateness or missing checkout posture before anyone makes a correction.`,
      rationale: [
        `${signal.attendance.record_count} recent attendance record(s) were reviewed.`,
        `${signal.attendance.late_count + signal.attendance.open_checkout_count} record(s) matched manual-review criteria.`,
        ...(normalizedContext ? [`Operator context note: ${normalizedContext}`] : []),
      ],
      confidence_score: 0.9,
      suggested_actions: [
        {
          type: 'open_route',
          label: 'Open attendance review workspace',
          path: '/attendance/operational-review',
          requires_confirmation: true,
          notes: 'No correction request or attendance change is submitted automatically. Review the governed workflow manually.',
        },
      ],
      supporting_citations: rankDemoCitations([
        {
          type: 'attendance_record',
          label: 'Recent flagged attendance posture',
          reference: `Attendance record #AT-${subject?.employee_code ?? 'selected'}`,
          excerpt: `Late ${signal.attendance.late_count} time(s), open checkout ${signal.attendance.open_checkout_count} time(s).`,
          entity_type: 'attendance_record',
          entity_id: subject?.id ?? 2101,
          route: '/attendance/operational-review',
        },
      ]),
      status,
      human_review_required: true,
      decision: status === 'accepted' || status === 'rejected' ? status : null,
      decision_notes: null,
      decided_at: null,
      metadata: {
        subject_employee_id: subject?.id ?? null,
        context_note: normalizedContext,
      },
      created_at: createdAt,
      updated_at: createdAt,
      employee: subject,
      decided_by: null,
    }
  }

  if (scenario === 'policy_acknowledgement_follow_up') {
    const signal = demoSignalsForEmployee(subject?.id ?? 2101)

    return {
      id: recommendationId,
      conversation_id: payload.conversation_id ?? workspace.recent_interactions[0]?.conversation_id ?? null,
      scenario,
      title: `Review policy acknowledgement follow-up for ${subject?.full_name ?? 'the selected employee'}`,
      summary:
        `${signal.policy.pending_acknowledgements} policy acknowledgement(s) remain open. Review the due items and record any manual follow-up outside the assistant.`,
      rationale: [
        `${signal.policy.pending_acknowledgements + 1} acknowledgement record(s) were reviewed in the current session scope.`,
        `${signal.policy.pending_acknowledgements} item(s) remain assigned and unacknowledged.`,
        ...(normalizedContext ? [`Operator context note: ${normalizedContext}`] : []),
      ],
      confidence_score: 0.88,
      suggested_actions: [
        {
          type: 'open_route',
          label: 'Open policy document workspace',
          path: '/self-service/documents',
          requires_confirmation: true,
          notes: 'No acknowledgement is auto-submitted by the assistant. Use the governed workspace to review or acknowledge manually.',
        },
      ],
      supporting_citations: rankDemoCitations([
        {
          type: 'policy_acknowledgement',
          label: 'Remote work policy FY26 acknowledgement',
          reference: `Policy acknowledgement #PA-${subject?.employee_code ?? 'selected'}`,
          excerpt: `Status assigned, due 2026-07-05.`,
          entity_type: 'policy_acknowledgement',
          entity_id: subject?.id ?? 2101,
          route: '/self-service/documents',
        },
      ]),
      status,
      human_review_required: true,
      decision: status === 'accepted' || status === 'rejected' ? status : null,
      decision_notes: null,
      decided_at: null,
      metadata: {
        subject_employee_id: subject?.id ?? null,
        context_note: normalizedContext,
      },
      created_at: createdAt,
      updated_at: createdAt,
      employee: subject,
      decided_by: null,
    }
  }

  const signal = demoSignalsForEmployee(subject?.id ?? 2101)

  return {
    id: recommendationId,
    conversation_id: payload.conversation_id ?? workspace.recent_interactions[0]?.conversation_id ?? null,
    scenario,
    title: `Review the next learning step for ${subject?.full_name ?? 'the selected employee'}`,
    summary:
      signal.learning.overdue > 0
        ? `Prioritize ${signal.learning.priority_title} before opening new learning work.`
        : 'No blocked learning targets were found, so the next step is to review the active learning workspace and confirm whether a new course assignment is actually needed.',
    rationale: [
      `${signal.learning.total} governed learning target(s) are currently visible for review.`,
      signal.learning.overdue > 0
        ? `${signal.learning.overdue} target(s) are overdue and should be reviewed before adding more work.`
        : 'No overdue targets were detected in the visible assignment set.',
      ...(normalizedContext ? [`Operator context note: ${normalizedContext}`] : []),
    ],
    confidence_score: signal.learning.overdue > 0 ? 0.91 : 0.73,
    suggested_actions: [
      {
        type: 'open_route',
        label: 'Open governed learning workspace',
        path: '/learning/my-learning',
        requires_confirmation: true,
        notes: 'No training is auto-assigned by this recommendation. Review and assign manually if still appropriate.',
      },
    ],
    supporting_citations: rankDemoCitations([
      {
        type: 'learning_assignment_target',
        label: signal.learning.priority_title,
        reference: `Learning target #LRN-${subject?.employee_code ?? 'selected'}`,
        excerpt: `Status assigned, due 2026-07-08, progress ${signal.learning.progress_percent}%.`,
        entity_type: 'learning_assignment_target',
        entity_id: subject?.id ?? 2101,
        route: '/learning/my-learning',
      },
    ]),
    status,
    human_review_required: true,
    decision: status === 'accepted' || status === 'rejected' ? status : null,
    decision_notes: null,
    decided_at: null,
    metadata: {
      subject_employee_id: subject?.id ?? null,
      context_note: normalizedContext,
    },
    created_at: createdAt,
    updated_at: createdAt,
    employee: subject,
    decided_by: null,
  }
}

function resolvePersona(snapshot: AccessSnapshot, linkedEmployee: AssistantEmployeeSummary | null) {
  const roles = snapshot.user.roles

  if (roles.includes('recruiter')) {
    return { key: 'recruiter_copilot', label: 'Recruiter Copilot' }
  }

  if (roles.includes('hr.admin') || roles.includes('tenant.admin')) {
    return { key: 'hr_copilot', label: 'HR Copilot' }
  }

  if (roles.includes('manager')) {
    return { key: 'manager_copilot', label: 'Manager Copilot' }
  }

  if (roles.includes('learning.admin')) {
    return { key: 'learning_copilot', label: 'Learning Copilot' }
  }

  if (roles.includes('platform.super_admin')) {
    return { key: 'platform_copilot', label: 'Platform Copilot' }
  }

  return linkedEmployee
    ? { key: 'employee_copilot', label: 'Employee Copilot' }
    : { key: 'assistant', label: 'Phoenix Assistant' }
}

function resolveSubjectEmployee(workspace: AssistantWorkspaceData, subjectEmployeeId: number | null) {
  if (subjectEmployeeId === null) {
    return workspace.linked_employee ?? workspace.subject_options[0] ?? null
  }

  return workspace.subject_options.find((option) => option.id === subjectEmployeeId) ?? null
}

function resolveUseCase(question: string, explicitUseCase: string | null) {
  if (explicitUseCase && staticUseCases.some((item) => item.key === explicitUseCase)) {
    return explicitUseCase
  }

  const normalized = question.toLowerCase()
  const containsLateWord = /\blate\b/.test(normalized)

  if (normalized.includes('leave') || normalized.includes('balance') || normalized.includes('day off')) {
    return 'leave_balance'
  }

  if (
    normalized.includes('attendance') ||
    normalized.includes('check in') ||
    normalized.includes('check-in') ||
    containsLateWord ||
    normalized.includes('overtime') ||
    normalized.includes('worked hours')
  ) {
    return 'attendance_summary'
  }

  if (
    normalized.includes('payslip') ||
    normalized.includes('salary slip') ||
    normalized.includes('net pay') ||
    normalized.includes('payroll')
  ) {
    return 'payslip_summary'
  }

  if (
    normalized.includes('policy') ||
    normalized.includes('document') ||
    normalized.includes('handbook') ||
    normalized.includes('acknowledge')
  ) {
    return 'policy_document'
  }

  if (
    normalized.includes('learning') ||
    normalized.includes('training') ||
    normalized.includes('course') ||
    normalized.includes('certification')
  ) {
    return 'learning_summary'
  }

  return 'unsupported'
}

function isMutationOrApprovalRequest(question: string) {
  const normalized = question.toLowerCase()

  return [
    'approve',
    'reject',
    'terminate',
    'promote',
    'transfer',
    'increase salary',
    'change compensation',
    'update payroll',
    'process payroll',
    'apply leave',
    'submit leave',
    'execute',
    'delete',
  ].some((keyword) => normalized.includes(keyword))
}

function demoSignalsForEmployee(employeeId: number) {
  switch (employeeId) {
    case 2102:
      return {
        leave: { available: 16, primary_pool: 10, booked: 1, used: 3 },
        attendance: { record_count: 11, late_count: 0, open_checkout_count: 0, overtime_minutes: 95, open_checkout: false, worked_minutes_today: 521 },
        payslip: { slip_number: 'PSL-6204-PAY-2102', currency: 'INR', net_salary: 225000 },
        policy: { pending_acknowledgements: 1 },
        learning: { total: 3, assigned: 1, in_progress: 1, completed: 1, overdue: 0, progress_percent: 65, priority_title: 'Leadership Essentials' },
      }
    case 2103:
      return {
        leave: { available: 6.5, primary_pool: 4, booked: 2, used: 6.5 },
        attendance: { record_count: 9, late_count: 2, open_checkout_count: 1, overtime_minutes: 40, open_checkout: true, worked_minutes_today: 386 },
        payslip: { slip_number: 'PSL-6204-PAY-2103', currency: 'INR', net_salary: 118500 },
        policy: { pending_acknowledgements: 2 },
        learning: { total: 4, assigned: 2, in_progress: 1, completed: 1, overdue: 1, progress_percent: 28, priority_title: 'Security Awareness 2026' },
      }
    case 2106:
      return {
        leave: { available: 9, primary_pool: 5, booked: 1.5, used: 5 },
        attendance: { record_count: 10, late_count: 1, open_checkout_count: 0, overtime_minutes: 70, open_checkout: false, worked_minutes_today: 492 },
        payslip: { slip_number: 'PSL-6204-PAY-2106', currency: 'INR', net_salary: 146800 },
        policy: { pending_acknowledgements: 1 },
        learning: { total: 5, assigned: 1, in_progress: 2, completed: 2, overdue: 1, progress_percent: 44, priority_title: 'Interview Calibration Workshop' },
      }
    default:
      return {
        leave: { available: 14.5, primary_pool: 8.5, booked: 1, used: 4 },
        attendance: { record_count: 12, late_count: 1, open_checkout_count: 0, overtime_minutes: 135, open_checkout: false, worked_minutes_today: 508 },
        payslip: { slip_number: 'PSL-6204-PAY-2101', currency: 'INR', net_salary: 182500 },
        policy: { pending_acknowledgements: 1 },
        learning: { total: 3, assigned: 1, in_progress: 1, completed: 1, overdue: 0, progress_percent: 72, priority_title: 'Manager Excellence Sprint' },
      }
  }
}

function hasAnyPermission(permissions: string[], requiredPermissions: string[]) {
  return requiredPermissions.some((permission) => permissions.includes(permission))
}

function toEmployeeSummary(employee: DemoEmployeeRecord): AssistantEmployeeSummary {
  return {
    id: employee.id,
    employee_code: employee.employee_code,
    full_name: employee.full_name,
    email: employee.email,
  }
}

function normalizeOptionalText(value: string | null | undefined) {
  const normalized = value?.trim() ?? ''
  return normalized.length ? normalized : null
}

function rankDemoCitations(
  citations: Array<{
    type: string
    label: string
    reference: string
    excerpt: string
    entity_type: string
    entity_id: number
    route: string
  }>,
) {
  return citations.map((citation, index) => {
    const rank = index + 1
    const baseScore =
      citation.type === 'payslip'
        ? 0.97
        : citation.type === 'leave_balance'
          ? 0.95
          : citation.type === 'attendance_record'
            ? 0.94
            : citation.type === 'learning_assignment_target'
              ? 0.9
              : citation.type === 'policy_document'
                ? 0.88
                : 0.84

    return {
      ...citation,
      rank,
      relevance_score: Math.max(0.55, Math.round((baseScore - ((rank - 1) * 0.04)) * 100) / 100),
      evidence_strength: rank === 1 ? 'primary' : 'supporting',
      freshness_label:
        citation.type === 'payslip'
          ? rank === 1
            ? 'current_pay_cycle'
            : 'recent_pay_cycle'
          : citation.type === 'attendance_record'
            ? rank === 1
              ? 'current_attendance_window'
              : 'recent_attendance_window'
            : citation.type === 'leave_balance'
              ? 'active_balance_window'
              : citation.type === 'policy_document'
                ? 'current_policy_reference'
                : citation.type === 'policy_acknowledgement'
                  ? 'pending_acknowledgement'
                  : 'active_learning_target',
      captured_at: null,
    }
  })
}
