import { Link } from 'react-router-dom'
import { useState } from 'react'
import { formatRegionalDateTime, formatRegionalRelativeTimestamp } from '../../../shared/regionalization/formatters'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import { Textarea } from '../../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspacePillRow,
  WorkspaceSurface,
  WorkspaceToolbar,
  WorkspaceToolbarRow,
} from '../../../shared/ui/workspace'
import { useAssistantWorkspace } from '../hooks/useAssistantWorkspace'
import type {
  AssistantAuditEvent,
  AssistantCitation,
  AssistantInteraction,
  AssistantRecommendation,
} from '../types'

const nativeSelectClassName =
  'ui-select min-h-10 w-full rounded-md border border-input bg-card px-3 py-2 text-sm text-foreground shadow-none outline-none transition-colors focus-visible:ring-4 focus-visible:ring-ring/40 disabled:cursor-not-allowed disabled:opacity-60'

export function AssistantPage() {
  const workspace = useAssistantWorkspace()
  const [question, setQuestion] = useState('')
  const [selectedUseCase, setSelectedUseCase] = useState('')
  const [selectedSubjectId, setSelectedSubjectId] = useState('')
  const [recommendationScenario, setRecommendationScenario] = useState('')
  const [recommendationContext, setRecommendationContext] = useState('')
  const [feedbackNotes, setFeedbackNotes] = useState<Record<number, string>>({})
  const [decisionNotes, setDecisionNotes] = useState<Record<number, string>>({})

  const data = workspace.data

  if (workspace.isLoading) {
    return <p className="workspace-muted">Loading AI assistant workspace...</p>
  }

  if (workspace.error) {
    return <p className="workspace-error">{workspace.error.message}</p>
  }

  if (!data) {
    return (
      <WorkspacePage>
        <WorkspaceSurface>
          <WorkspaceContent>
            <WorkspaceEmptyState
              title="No assistant workspace is available yet"
              copy="Sign in with AI permissions, then finish workspace provisioning to review governed answers, citations, and recommendation posture."
            />
          </WorkspaceContent>
        </WorkspaceSurface>
      </WorkspacePage>
    )
  }

  const defaultSubjectId = String((data.linked_employee ?? data.subject_options[0])?.id ?? '')
  const activeSubjectId =
    (selectedSubjectId &&
      data.subject_options.some((option) => String(option.id) === selectedSubjectId) &&
      selectedSubjectId) ||
    defaultSubjectId
  const defaultRecommendationScenario = data.capabilities.approved_recommendation_scenarios[0]?.key ?? ''
  const activeRecommendationScenario =
    (recommendationScenario &&
      data.capabilities.approved_recommendation_scenarios.some((item) => item.key === recommendationScenario) &&
      recommendationScenario) ||
    defaultRecommendationScenario
  const selectedSubject =
    data.subject_options.find((option) => String(option.id) === activeSubjectId) ?? data.linked_employee ?? null
  const latestInteraction = data.recent_interactions[0] ?? null
  const latestRecommendation = data.recent_recommendations[0] ?? null
  const pendingRecommendation =
    data.recent_recommendations.find((item) => item.status === 'pending_review') ?? latestRecommendation ?? null
  const answerQuality = data.review_analytics.answer_quality
  const recommendationQueue = data.review_analytics.recommendation_queue
  const auditActivity = data.review_analytics.audit_activity

  async function handleQuestionSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()

    if (!question.trim()) {
      return
    }

    try {
      await workspace.askQuestion({
        question: question.trim(),
        use_case: selectedUseCase || null,
        subject_employee_id: selectedSubject ? selectedSubject.id : null,
        conversation_id: latestInteraction?.conversation_id ?? null,
      })
      setQuestion('')
    } catch {
      return
    }
  }

  async function handleRecommendationSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()

    if (!activeRecommendationScenario) {
      return
    }

    try {
      await workspace.generateRecommendation({
        scenario: activeRecommendationScenario,
        subject_employee_id: selectedSubject ? selectedSubject.id : null,
        context_note: recommendationContext.trim() || null,
        conversation_id: latestInteraction?.conversation_id ?? null,
      })
      setRecommendationContext('')
    } catch {
      return
    }
  }

  async function handleFeedback(interaction: AssistantInteraction, sentiment: 'positive' | 'neutral' | 'negative') {
    try {
      await workspace.recordInteractionFeedback(interaction.id, {
        sentiment,
        rating: sentiment === 'positive' ? 5 : sentiment === 'neutral' ? 3 : 2,
        notes: feedbackNotes[interaction.id] ?? '',
      })
    } catch {
      return
    }
  }

  async function handleDecision(
    recommendation: AssistantRecommendation,
    decision: 'accepted' | 'rejected',
  ) {
    try {
      await workspace.recordRecommendationDecision(recommendation.id, {
        decision,
        decision_notes: decisionNotes[recommendation.id] ?? '',
      })
    } catch {
      return
    }
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="AI Assistant"
          title="Governed AI Assistant"
          description="Ask read-focused HR questions, inspect source citations, and review human-gated recommendations without letting the assistant mutate critical records."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo copilot' : 'Live copilot'}</Badge>}
          context={[
            data.persona.label,
            data.permissions.can_generate_recommendations ? 'Recommendations enabled' : 'Answer-only session',
            `${data.summary.interaction_count} logged interaction(s)`,
            `${auditActivity.event_count} audit event(s)`,
          ]}
          actions={
            <div className="space-y-3">
              <div className="rounded-2xl border border-line/80 bg-white/80 p-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]">
                <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#6a7787]">Disclosure</p>
                <p className="mt-2 text-sm leading-6 text-slate-600">{data.disclosure}</p>
              </div>
              {data.linked_employee ? (
                <div className="rounded-2xl border border-line/80 bg-white/76 p-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.72)]">
                  <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#6a7787]">Default subject</p>
                  <p className="mt-2 text-sm font-semibold text-foreground">{data.linked_employee.full_name}</p>
                  <p className="text-xs text-muted-foreground">{data.linked_employee.employee_code}</p>
                </div>
              ) : null}
            </div>
          }
        />

        <WorkspaceContent className="space-y-4">
          <div className="organization-metric-grid">
            <MetricCard
              label="Citation coverage"
              value={`${answerQuality.citation_coverage_percent}%`}
              caption={
                answerQuality.answered_count
                  ? `${answerQuality.cited_answer_count} of ${answerQuality.answered_count} answered interaction(s) expose a source trail.`
                  : 'No answered interactions have been generated yet.'
              }
            />
            <MetricCard
              label="Feedback logged"
              value={String(data.summary.feedback_recorded_count)}
              caption={
                answerQuality.average_feedback_rating !== null
                  ? `Average rating ${answerQuality.average_feedback_rating.toFixed(2)} with ${answerQuality.positive_feedback_count} positive signal(s).`
                  : 'Quality feedback appears here once a reviewer records sentiment or a rating.'
              }
            />
            <MetricCard
              label="Pending review"
              value={String(data.summary.pending_recommendation_count)}
              caption={
                recommendationQueue.stale_pending_review_count > 0
                  ? `${recommendationQueue.stale_pending_review_count} recommendation(s) have been waiting more than one day for review.`
                  : 'Recommendations stay queued until a human explicitly accepts or rejects them.'
              }
            />
            <MetricCard
              label="Audit events"
              value={String(auditActivity.event_count)}
              caption="Every answer, feedback signal, and recommendation decision remains reviewable in the audit trail."
            />
          </div>

          {workspace.lastActionMessage ? (
            <StatusBanner tone="success" title="Workspace updated" message={workspace.lastActionMessage} />
          ) : null}

          {workspace.actionError ? (
            <StatusBanner tone="warning" title="Action blocked" message={workspace.actionError} />
          ) : null}

          <WorkspaceSurface>
            <WorkspaceHeader compact>
              <div className="space-y-1">
                <h2 className="text-lg font-semibold text-foreground">Review analytics</h2>
                <p className="text-sm text-muted-foreground">
                  Quality signals, queue posture, and audit activity stay visible so the assistant remains governed after the first answer.
                </p>
              </div>
            </WorkspaceHeader>
            <WorkspaceContent className="grid gap-3 lg:grid-cols-3">
              <AnalyticsCard
                title="Answer quality"
                items={[
                  `Answered interactions: ${answerQuality.answered_count}`,
                  `Guardrailed responses: ${answerQuality.guardrailed_interaction_count}`,
                  answerQuality.average_feedback_rating !== null
                    ? `Average feedback rating: ${answerQuality.average_feedback_rating.toFixed(2)}`
                    : 'Average feedback rating: not recorded yet',
                  `Negative feedback signals: ${answerQuality.negative_feedback_count}`,
                ]}
              />
              <AnalyticsCard
                title="Recommendation queue"
                items={[
                  `Accepted recommendations: ${recommendationQueue.accepted_count}`,
                  `Rejected recommendations: ${recommendationQueue.rejected_count}`,
                  recommendationQueue.oldest_pending_created_at
                    ? `Oldest pending item: ${formatRegionalDateTime(recommendationQueue.oldest_pending_created_at)}`
                    : 'Oldest pending item: no open review items',
                  recommendationQueue.latest_decision_at
                    ? `Latest decision: ${formatRegionalDateTime(recommendationQueue.latest_decision_at)}`
                    : 'Latest decision: not recorded yet',
                ]}
              />
              <AnalyticsCard
                title="Audit activity"
                items={[
                  `Workspace views: ${auditActivity.workspace_view_count}`,
                  `Answers generated: ${auditActivity.interaction_generated_count}`,
                  `Feedback events: ${auditActivity.feedback_event_count}`,
                  auditActivity.last_event_at
                    ? `Latest event: ${formatRegionalDateTime(auditActivity.last_event_at)}`
                    : 'Latest event: none recorded yet',
                ]}
              />
            </WorkspaceContent>
          </WorkspaceSurface>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)]">
            <div className="space-y-4">
              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Question composer</h2>
                    <p className="text-sm text-muted-foreground">
                      Ask one governed question at a time, optionally narrowing the use case or employee subject before the answer is generated.
                    </p>
                  </div>
                  <Badge variant="subtle">{data.permissions.can_chat ? 'Chat enabled' : 'Read only'}</Badge>
                </WorkspaceHeader>
                <WorkspaceContent>
                  <form className="space-y-3.5" onSubmit={handleQuestionSubmit}>
                    <WorkspaceToolbar>
                      <WorkspaceToolbarRow>
                        <div className="flex flex-1 flex-wrap items-end gap-2.5">
                          <WorkspaceField label="Use case" compact>
                            <select
                              aria-label="Use case"
                              className={nativeSelectClassName}
                              value={selectedUseCase}
                              onChange={(event) => setSelectedUseCase(event.target.value)}
                            >
                              <option value="">Auto-detect from question</option>
                              {data.capabilities.supported_use_cases.map((item) => (
                                <option key={item.key} value={item.key}>
                                  {item.label}
                                </option>
                              ))}
                            </select>
                          </WorkspaceField>
                          <WorkspaceField label="Subject employee" compact>
                            <select
                              aria-label="Subject employee"
                              className={nativeSelectClassName}
                              value={activeSubjectId}
                              onChange={(event) => setSelectedSubjectId(event.target.value)}
                            >
                              {!data.subject_options.length ? <option value="">No governed employee context</option> : null}
                              {data.subject_options.map((option) => (
                                <option key={option.id} value={String(option.id)}>
                                  {option.full_name} · {option.employee_code}
                                </option>
                              ))}
                            </select>
                          </WorkspaceField>
                        </div>
                      </WorkspaceToolbarRow>
                    </WorkspaceToolbar>

                    <WorkspaceField label="Ask a governed HR question">
                      <Textarea
                        aria-label="Ask a governed HR question"
                        value={question}
                        onChange={(event) => setQuestion(event.target.value)}
                        placeholder="Example: Show the latest payslip for this employee and cite the source."
                        className="min-h-32"
                      />
                    </WorkspaceField>

                    <div className="flex flex-wrap items-center justify-between gap-3">
                      <WorkspacePillRow>
                        {data.capabilities.supported_use_cases.slice(0, 3).map((item) => (
                          <button
                            key={item.key}
                            type="button"
                            className="rounded-full border border-line/80 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-line-strong hover:text-slate-900"
                            onClick={() => {
                              setSelectedUseCase(item.key)
                              setQuestion(item.examples[0] ?? question)
                            }}
                          >
                            {item.label}
                          </button>
                        ))}
                      </WorkspacePillRow>
                      <Button type="submit" disabled={workspace.pendingQuestion || !data.permissions.can_chat || !question.trim()}>
                        {workspace.pendingQuestion ? 'Generating...' : 'Ask assistant'}
                      </Button>
                    </div>
                  </form>
                </WorkspaceContent>
              </WorkspaceSurface>

              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Latest answer</h2>
                    <p className="text-sm text-muted-foreground">
                      Inspect the answer body, citation trail, and any guardrail language before acting on it.
                    </p>
                  </div>
                  {latestInteraction ? <Badge variant={statusBadgeVariant(latestInteraction.status)}>{latestInteraction.status}</Badge> : null}
                </WorkspaceHeader>
                <WorkspaceContent>
                  {latestInteraction ? (
                    <div className="space-y-4">
                      <div className="rounded-[1.15rem] border border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.94)_0%,rgba(246,249,255,0.98)_100%)] p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.78)]">
                        <div className="flex flex-wrap items-start justify-between gap-3">
                          <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#6a7787]">Question</p>
                            <p className="mt-2 text-sm font-medium text-foreground">{latestInteraction.question}</p>
                          </div>
                          <div className="text-right">
                            <p className="text-xs text-muted-foreground">{formatRegionalRelativeTimestamp(latestInteraction.responded_at)}</p>
                            <p className="mt-1 text-xs text-muted-foreground">
                              Confidence {formatConfidence(latestInteraction.confidence_score)}
                            </p>
                          </div>
                        </div>
                        <p className="mt-4 text-sm leading-7 text-slate-700">{latestInteraction.answer}</p>
                      </div>

                      {latestInteraction.guardrails.length ? (
                        <GuardrailPanel guardrails={latestInteraction.guardrails} />
                      ) : null}

                      <CitationPanel citations={latestInteraction.citations} emptyMessage="This answer did not need an explicit source citation." />

                      <div className="rounded-2xl border border-line/80 bg-white/84 p-4">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                          <div>
                            <h3 className="text-sm font-semibold text-foreground">Feedback</h3>
                            <p className="text-xs text-muted-foreground">Capture quality signals so later reviews can spot weak or misleading answers.</p>
                          </div>
                          {latestInteraction.feedback.sentiment ? (
                            <Badge variant="subtle">Recorded: {latestInteraction.feedback.sentiment}</Badge>
                          ) : null}
                        </div>
                        <div className="mt-3 grid gap-3 md:grid-cols-[minmax(0,1fr)_auto]">
                          <Input
                            aria-label={`Feedback notes for interaction ${latestInteraction.id}`}
                            value={feedbackNotes[latestInteraction.id] ?? latestInteraction.feedback.notes ?? ''}
                            onChange={(event) =>
                              setFeedbackNotes((current) => ({
                                ...current,
                                [latestInteraction.id]: event.target.value,
                              }))
                            }
                            placeholder="Optional note for answer review"
                          />
                          <div className="flex flex-wrap items-center gap-2">
                            <Button
                              size="sm"
                              variant="secondary"
                              disabled={workspace.pendingFeedbackId === latestInteraction.id}
                              onClick={() => void handleFeedback(latestInteraction, 'positive')}
                            >
                              Helpful
                            </Button>
                            <Button
                              size="sm"
                              variant="secondary"
                              disabled={workspace.pendingFeedbackId === latestInteraction.id}
                              onClick={() => void handleFeedback(latestInteraction, 'neutral')}
                            >
                              Neutral
                            </Button>
                            <Button
                              size="sm"
                              variant="secondary"
                              disabled={workspace.pendingFeedbackId === latestInteraction.id}
                              onClick={() => void handleFeedback(latestInteraction, 'negative')}
                            >
                              Needs work
                            </Button>
                          </div>
                        </div>
                      </div>
                    </div>
                  ) : (
                    <WorkspaceEmptyState
                      title="No answer yet"
                      copy="Submit a governed question to inspect the answer, citation stack, and any guardrail messaging here."
                    />
                  )}
                </WorkspaceContent>
              </WorkspaceSurface>
            </div>

            <div className="space-y-4">
              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Recommendation studio</h2>
                    <p className="text-sm text-muted-foreground">
                      Generate approved next-step suggestions only for the governed scenarios that remain human-reviewed in v1.
                    </p>
                  </div>
                  <Badge variant={data.permissions.can_generate_recommendations ? 'info' : 'warning'}>
                    {data.permissions.can_generate_recommendations ? 'Review-only actions' : 'Recommendations blocked'}
                  </Badge>
                </WorkspaceHeader>
                <WorkspaceContent>
                  {data.permissions.can_generate_recommendations ? (
                    <form className="space-y-3.5" onSubmit={handleRecommendationSubmit}>
                      <WorkspaceField label="Recommendation scenario">
                        <select
                          aria-label="Recommendation scenario"
                          className={nativeSelectClassName}
                          value={activeRecommendationScenario}
                          onChange={(event) => setRecommendationScenario(event.target.value)}
                        >
                          {data.capabilities.approved_recommendation_scenarios.map((item) => (
                            <option key={item.key} value={item.key}>
                              {item.label}
                            </option>
                          ))}
                        </select>
                      </WorkspaceField>
                      <WorkspaceField label="Operator context note">
                        <Textarea
                          aria-label="Operator context note"
                          value={recommendationContext}
                          onChange={(event) => setRecommendationContext(event.target.value)}
                          placeholder="Optional context that should shape the review recommendation"
                          className="min-h-24"
                        />
                      </WorkspaceField>
                      <Button
                        type="submit"
                        disabled={workspace.pendingRecommendation || !activeRecommendationScenario}
                      >
                        {workspace.pendingRecommendation ? 'Preparing...' : 'Generate recommendation'}
                      </Button>
                    </form>
                  ) : (
                    <WorkspaceEmptyState
                      title="Recommendation privileges are not active"
                      copy="This session can still review cited answers, but only users with the governed recommendation permission can open the review queue."
                    />
                  )}
                </WorkspaceContent>
              </WorkspaceSurface>

              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Recommendation inbox</h2>
                    <p className="text-sm text-muted-foreground">
                      Accepting a recommendation records the human decision, not an automatic workflow mutation.
                    </p>
                  </div>
                  <div className="flex flex-wrap items-center gap-2">
                    {pendingRecommendation ? (
                      <Badge variant={statusBadgeVariant(pendingRecommendation.status)}>{pendingRecommendation.status}</Badge>
                    ) : null}
                    {recommendationQueue.stale_pending_review_count > 0 ? (
                      <Badge variant="warning">{recommendationQueue.stale_pending_review_count} stale</Badge>
                    ) : null}
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent>
                  {pendingRecommendation ? (
                    <RecommendationCard
                      recommendation={pendingRecommendation}
                      decisionNote={decisionNotes[pendingRecommendation.id] ?? pendingRecommendation.decision_notes ?? ''}
                      onDecisionNoteChange={(value) =>
                        setDecisionNotes((current) => ({
                          ...current,
                          [pendingRecommendation.id]: value,
                        }))
                      }
                      onAccept={() => void handleDecision(pendingRecommendation, 'accepted')}
                      onReject={() => void handleDecision(pendingRecommendation, 'rejected')}
                      isPending={workspace.pendingDecisionId === pendingRecommendation.id}
                    />
                  ) : (
                    <WorkspaceEmptyState
                      title="No recommendations yet"
                      copy="Generate a review-only recommendation to inspect its rationale, supported citations, and human decision controls."
                    />
                  )}
                </WorkspaceContent>
              </WorkspaceSurface>

              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div className="space-y-1">
                    <h2 className="text-lg font-semibold text-foreground">Governance posture</h2>
                    <p className="text-sm text-muted-foreground">
                      These baseline rules shape every answer and recommendation regardless of what a user asks.
                    </p>
                  </div>
                </WorkspaceHeader>
                <WorkspaceContent className="space-y-3">
                  {data.capabilities.guardrails.map((item) => (
                    <div key={item} className="rounded-2xl border border-line/75 bg-white/84 px-4 py-3 text-sm leading-6 text-slate-600">
                      {item}
                    </div>
                  ))}
                </WorkspaceContent>
              </WorkspaceSurface>
            </div>
          </div>

          <div className="grid gap-4 xl:grid-cols-2">
            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Recent interactions</h2>
                  <p className="text-sm text-muted-foreground">Every response remains reviewable with timestamps, confidence, and citation posture.</p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent className="space-y-3">
                {data.recent_interactions.length ? (
                  data.recent_interactions.map((interaction) => (
                    <article key={interaction.id} className="rounded-2xl border border-line/75 bg-white/84 p-4">
                      <div className="flex flex-wrap items-start justify-between gap-3">
                        <div>
                          <p className="text-sm font-semibold text-foreground">{interaction.question}</p>
                          <p className="mt-1 text-xs text-muted-foreground">
                            {interaction.use_case} · {formatRegionalDateTime(interaction.responded_at)}
                          </p>
                        </div>
                        <Badge variant={statusBadgeVariant(interaction.status)}>{interaction.status}</Badge>
                      </div>
                      <p className="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{interaction.answer}</p>
                    </article>
                  ))
                ) : (
                  <WorkspaceEmptyState
                    title="No interactions recorded"
                    copy="The recent answer timeline will appear here after the first governed question is submitted."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>

            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="space-y-1">
                  <h2 className="text-lg font-semibold text-foreground">Recent recommendations</h2>
                  <p className="text-sm text-muted-foreground">
                    Keep an eye on which suggestions are still pending, already accepted, or explicitly rejected.
                  </p>
                </div>
              </WorkspaceHeader>
              <WorkspaceContent className="space-y-3">
                {data.recent_recommendations.length ? (
                  data.recent_recommendations.map((recommendation) => (
                    <article key={recommendation.id} className="rounded-2xl border border-line/75 bg-white/84 p-4">
                      <div className="flex flex-wrap items-start justify-between gap-3">
                        <div>
                          <p className="text-sm font-semibold text-foreground">{recommendation.title}</p>
                          <p className="mt-1 text-xs text-muted-foreground">
                            {recommendation.employee?.full_name ?? 'No employee context'} · {recommendation.scenario}
                          </p>
                        </div>
                        <Badge variant={statusBadgeVariant(recommendation.status)}>{recommendation.status}</Badge>
                      </div>
                      <p className="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{recommendation.summary}</p>
                    </article>
                  ))
                ) : (
                  <WorkspaceEmptyState
                    title="No recommendations recorded"
                    copy="Approved scenarios will appear here after the first human-gated recommendation is generated."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>

          <WorkspaceSurface>
            <WorkspaceHeader compact>
              <div className="space-y-1">
                <h2 className="text-lg font-semibold text-foreground">Audit trail</h2>
                <p className="text-sm text-muted-foreground">
                  This timeline keeps assistant answers, feedback capture, and recommendation decisions visible for later review.
                </p>
              </div>
            </WorkspaceHeader>
            <WorkspaceContent>
              {data.audit_timeline.length ? (
                <div className="space-y-3">
                  {data.audit_timeline.map((event) => (
                    <AuditEventCard key={event.id} event={event} />
                  ))}
                </div>
              ) : (
                <WorkspaceEmptyState
                  title="No audit events recorded yet"
                  copy="The audit trail will populate after the workspace is viewed and the first assistant action is logged."
                />
              )}
            </WorkspaceContent>
          </WorkspaceSurface>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function MetricCard({ label, value, caption }: { label: string; value: string; caption: string }) {
  return (
    <div className="rounded-[1.2rem] border border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.94)_0%,rgba(245,248,252,0.98)_100%)] p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.82)]">
      <p className="text-xs font-semibold uppercase tracking-[0.24em] text-[#6a7787]">{label}</p>
      <p className="mt-3 text-[1.7rem] font-semibold tracking-[-0.04em] text-slate-900">{value}</p>
      <p className="mt-2 text-sm leading-6 text-slate-600">{caption}</p>
    </div>
  )
}

function StatusBanner({
  tone,
  title,
  message,
}: {
  tone: 'success' | 'warning'
  title: string
  message: string
}) {
  return (
    <div
      className={`rounded-2xl border px-4 py-3 ${
        tone === 'success'
          ? 'border-emerald-200 bg-emerald-50/90 text-emerald-900'
          : 'border-amber-200 bg-amber-50/90 text-amber-900'
      }`}
    >
      <p className="text-sm font-semibold">{title}</p>
      <p className="mt-1 text-sm leading-6">{message}</p>
    </div>
  )
}

function AnalyticsCard({ title, items }: { title: string; items: string[] }) {
  return (
    <div className="rounded-2xl border border-line/80 bg-white/84 p-4">
      <h3 className="text-sm font-semibold text-foreground">{title}</h3>
      <div className="mt-3 space-y-2">
        {items.map((item) => (
          <div key={item} className="rounded-xl border border-line/75 bg-panel-soft/70 px-3 py-2 text-sm leading-6 text-slate-600">
            {item}
          </div>
        ))}
      </div>
    </div>
  )
}

function CitationPanel({
  citations,
  emptyMessage,
}: {
  citations: AssistantCitation[]
  emptyMessage: string
}) {
  return (
    <div className="rounded-2xl border border-line/80 bg-white/84 p-4">
      <div className="flex items-center justify-between gap-3">
        <div>
          <h3 className="text-sm font-semibold text-foreground">Citations</h3>
          <p className="text-xs text-muted-foreground">Review the source trail before trusting or sharing the answer.</p>
        </div>
        <Badge variant="subtle">{citations.length} source(s)</Badge>
      </div>
      <div className="mt-3 space-y-3">
        {citations.length ? (
          citations.map((citation) => (
            <article key={`${citation.type}-${citation.reference}`} className="rounded-xl border border-line/75 bg-panel-soft/70 p-3">
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p className="text-sm font-semibold text-foreground">{citation.label}</p>
                  <p className="text-xs text-muted-foreground">{citation.reference}</p>
                  <div className="mt-2 flex flex-wrap gap-2">
                    <Badge variant="subtle">Rank {citation.rank}</Badge>
                    <Badge variant="subtle">{formatEvidenceStrength(citation.evidence_strength)}</Badge>
                    {citation.freshness_label ? (
                      <Badge variant="subtle">{formatCitationFreshness(citation.freshness_label)}</Badge>
                    ) : null}
                    {citation.relevance_score !== null ? (
                      <Badge variant="subtle">Relevance {citation.relevance_score.toFixed(2)}</Badge>
                    ) : null}
                  </div>
                </div>
                <Button asChild size="xs" variant="secondary">
                  <Link to={citation.route}>Open route</Link>
                </Button>
              </div>
              <p className="mt-2 text-sm leading-6 text-slate-600">{citation.excerpt}</p>
            </article>
          ))
        ) : (
          <p className="text-sm text-muted-foreground">{emptyMessage}</p>
        )}
      </div>
    </div>
  )
}

function AuditEventCard({ event }: { event: AssistantAuditEvent }) {
  return (
    <article className="rounded-2xl border border-line/75 bg-white/84 p-4">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p className="text-sm font-semibold text-foreground">{event.label}</p>
          <p className="mt-1 text-xs text-muted-foreground">
            {event.event_type} {event.entity_id ? `· #${event.entity_id}` : ''}
          </p>
        </div>
        <Badge variant="subtle">{formatRegionalDateTime(event.created_at)}</Badge>
      </div>
      <p className="mt-3 text-sm leading-6 text-slate-600">{event.summary}</p>
    </article>
  )
}

function GuardrailPanel({ guardrails }: { guardrails: AssistantInteraction['guardrails'] }) {
  return (
    <div className="rounded-2xl border border-amber-200 bg-amber-50/85 p-4">
      <div className="flex items-center justify-between gap-3">
        <div>
          <h3 className="text-sm font-semibold text-amber-950">Guardrail response</h3>
          <p className="text-xs text-amber-900/75">The assistant refused to act beyond the approved Sprint 10 baseline.</p>
        </div>
        <Badge variant="warning">Read only</Badge>
      </div>
      <div className="mt-3 space-y-2">
        {guardrails.map((guardrail) => (
          <div key={guardrail.code} className="rounded-xl border border-amber-200/80 bg-white/70 p-3">
            <p className="text-sm font-semibold text-amber-950">{guardrail.code}</p>
            <p className="mt-1 text-sm leading-6 text-amber-900/85">{guardrail.message}</p>
            {guardrail.action_path ? (
              <Button asChild size="xs" variant="secondary" className="mt-3">
                <Link to={guardrail.action_path}>Open governed route</Link>
              </Button>
            ) : null}
          </div>
        ))}
      </div>
    </div>
  )
}

function RecommendationCard({
  recommendation,
  decisionNote,
  onDecisionNoteChange,
  onAccept,
  onReject,
  isPending,
}: {
  recommendation: AssistantRecommendation
  decisionNote: string
  onDecisionNoteChange: (value: string) => void
  onAccept: () => void
  onReject: () => void
  isPending: boolean
}) {
  return (
    <div className="space-y-4">
      <div className="rounded-[1.15rem] border border-line/80 bg-[linear-gradient(180deg,rgba(255,255,255,0.94)_0%,rgba(246,249,255,0.98)_100%)] p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.78)]">
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#6a7787]">Latest review item</p>
            <p className="mt-2 text-sm font-semibold text-foreground">{recommendation.title}</p>
            <p className="mt-1 text-xs text-muted-foreground">
              {recommendation.employee?.full_name ?? 'No employee context'} · Confidence {formatConfidence(recommendation.confidence_score)}
            </p>
          </div>
          <Badge variant={statusBadgeVariant(recommendation.status)}>{recommendation.status}</Badge>
        </div>
        <p className="mt-4 text-sm leading-7 text-slate-700">{recommendation.summary}</p>
      </div>

      <div className="rounded-2xl border border-line/80 bg-white/84 p-4">
        <h3 className="text-sm font-semibold text-foreground">Rationale</h3>
        <div className="mt-3 space-y-2">
          {recommendation.rationale.map((item) => (
            <div key={item} className="rounded-xl border border-line/75 bg-panel-soft/70 px-3 py-2 text-sm leading-6 text-slate-600">
              {item}
            </div>
          ))}
        </div>
      </div>

      <CitationPanel
        citations={recommendation.supporting_citations}
        emptyMessage="This recommendation does not currently expose additional supporting citations."
      />

      <div className="rounded-2xl border border-line/80 bg-white/84 p-4">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h3 className="text-sm font-semibold text-foreground">Human decision</h3>
            <p className="text-xs text-muted-foreground">Record a clear acceptance or rejection note before the team moves to the governed route.</p>
          </div>
          {recommendation.decision ? <Badge variant="subtle">Decision: {recommendation.decision}</Badge> : null}
        </div>
        <Textarea
          aria-label={`Decision notes for recommendation ${recommendation.id}`}
          value={decisionNote}
          onChange={(event) => onDecisionNoteChange(event.target.value)}
          placeholder="Optional reviewer note for the final audit record"
          className="mt-3 min-h-24"
        />
        <div className="mt-3 flex flex-wrap items-center gap-2">
          <Button disabled={isPending} onClick={onAccept}>
            Accept recommendation
          </Button>
          <Button variant="secondary" disabled={isPending} onClick={onReject}>
            Reject recommendation
          </Button>
          {recommendation.suggested_actions[0] ? (
            <Button asChild variant="ghost">
              <Link to={recommendation.suggested_actions[0].path}>
                {recommendation.suggested_actions[0].label}
              </Link>
            </Button>
          ) : null}
        </div>
      </div>
    </div>
  )
}

function statusBadgeVariant(status: string) {
  if (status === 'accepted' || status === 'answered') {
    return 'success'
  }

  if (status === 'guardrailed' || status === 'rejected') {
    return 'warning'
  }

  if (status === 'pending_review') {
    return 'info'
  }

  return 'subtle'
}

function formatConfidence(value: number | null) {
  if (typeof value !== 'number') {
    return '—'
  }

  return `${Math.round(value * 100)}%`
}

function formatEvidenceStrength(value: string) {
  return value === 'primary' ? 'Primary evidence' : 'Supporting evidence'
}

function formatCitationFreshness(value: string) {
  return value
    .split('_')
    .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
    .join(' ')
}
