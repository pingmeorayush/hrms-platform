import { useMemo, useState } from 'react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { SelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSplit,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import { usePerformanceRouteWorkspace } from './usePerformanceRouteWorkspace'
import type {
  PerformanceReviewCycleRecord,
  PerformanceReviewRecord,
  PerformanceReviewStatus,
} from '../types'
import { formatActorRole, formatPerformanceDate, formatPerformanceLabel, reviewStatusBadgeVariant } from '../utils'

type ReviewTab = 'all' | PerformanceReviewStatus

interface ReviewCreateFormState {
  performance_review_cycle_id: string
  employee_id: string
  reviewer_user_id: string
  launch_immediately: boolean
  employee_can_view_manager_assessment_before_publish: boolean
  employee_can_view_peer_feedback_after_publish: boolean
  peer_feedback_anonymous_to_employee: boolean
  manager_can_view_peer_feedback: boolean
  reviewer_can_view_other_reviewer_feedback: boolean
}

interface SubmissionDraft {
  sections: Array<{ key: string; label: string; rating: string; comment: string }>
  competencies: Array<{ competency_id: number; name: string; rating: string; comment: string }>
  overall_rating: string
  summary: string
  confidential_notes: string
}

interface CalibrationDraft {
  overall_rating: string
  summary: string
  confidential_notes: string
}

interface FinalizationDraft {
  final_rating: string
  summary: string
  employee_visible_summary: string
  recommendation: string
}

const reviewTabs: Array<{ id: ReviewTab; label: string }> = [
  { id: 'all', label: 'All reviews' },
  { id: 'self_assessment', label: 'Self assessment' },
  { id: 'manager_review', label: 'Manager review' },
  { id: 'calibration', label: 'Calibration' },
  { id: 'finalized', label: 'Finalized' },
  { id: 'published', label: 'Published' },
  { id: 'reopened', label: 'Reopened' },
]

function buildSubmissionDraft(review: PerformanceReviewRecord | null, cycle: PerformanceReviewCycleRecord | null): SubmissionDraft {
  const sections =
    cycle?.review_template.sections.map((section) => ({
      key: section.key,
      label: section.label,
      rating: '',
      comment: '',
    })) ?? []

  const competencies =
    review?.competency_snapshot.map((competency) => ({
      competency_id: competency.id,
      name: competency.name,
      rating: '',
      comment: '',
    })) ?? []

  return {
    sections,
    competencies,
    overall_rating: '',
    summary: '',
    confidential_notes: '',
  }
}

type PerformanceRouteWorkspace = ReturnType<typeof usePerformanceRouteWorkspace>

interface ReviewWorkflowPanelsProps {
  selectedReview: PerformanceReviewRecord
  selectedCycle: PerformanceReviewCycleRecord | null
  workspace: PerformanceRouteWorkspace
  actionableSubmission: boolean
}

function ReviewWorkflowPanels({
  selectedReview,
  selectedCycle,
  workspace,
  actionableSubmission,
}: ReviewWorkflowPanelsProps) {
  const [submissionDraft, setSubmissionDraft] = useState<SubmissionDraft>(() =>
    buildSubmissionDraft(selectedReview, selectedCycle),
  )
  const [calibrationDraft, setCalibrationDraft] = useState<CalibrationDraft>(() => ({
    overall_rating: selectedReview.calibration_payload?.overall_rating
      ? String(selectedReview.calibration_payload.overall_rating)
      : '',
    summary: selectedReview.calibration_payload?.summary ?? '',
    confidential_notes: selectedReview.calibration_payload?.confidential_notes ?? '',
  }))
  const [finalizationDraft, setFinalizationDraft] = useState<FinalizationDraft>(() => ({
    final_rating: selectedReview.final_payload?.final_rating
      ? String(selectedReview.final_payload.final_rating)
      : '',
    summary: selectedReview.final_payload?.summary ?? '',
    employee_visible_summary: selectedReview.final_payload?.employee_visible_summary ?? '',
    recommendation: selectedReview.final_payload?.recommendation ?? '',
  }))
  const [reopenReason, setReopenReason] = useState(selectedReview.reopened_reason ?? '')

  const handleSubmitReview = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    await workspace.submitReview(selectedReview.id, {
      sections: submissionDraft.sections.map((section) => ({
        key: section.key,
        rating: Number(section.rating || 0),
        comment: section.comment || null,
      })),
      competencies: submissionDraft.competencies
        .filter((competency) => competency.rating)
        .map((competency) => ({
          competency_id: competency.competency_id,
          rating: Number(competency.rating),
          comment: competency.comment || null,
        })),
      overall_rating: Number(submissionDraft.overall_rating || 0),
      summary: submissionDraft.summary,
      confidential_notes: submissionDraft.confidential_notes || null,
    })
  }

  const handleCalibrateReview = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    await workspace.calibrateReview(selectedReview.id, {
      overall_rating: Number(calibrationDraft.overall_rating || 0),
      summary: calibrationDraft.summary,
      confidential_notes: calibrationDraft.confidential_notes || null,
    })
  }

  const handleFinalizeReview = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    await workspace.finalizeReview(selectedReview.id, {
      final_rating: Number(finalizationDraft.final_rating || 0),
      summary: finalizationDraft.summary,
      employee_visible_summary: finalizationDraft.employee_visible_summary,
      recommendation: finalizationDraft.recommendation || null,
    })
  }

  return (
    <>
      {actionableSubmission ? (
        <form
          onSubmit={handleSubmitReview}
          className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]"
        >
          <div className="space-y-1">
            <h2 className="text-base font-semibold text-foreground">Submit review input</h2>
            <p className="text-sm text-muted-foreground">
              Capture the role-appropriate review narrative for the current cycle stage without
              breaking visibility rules.
            </p>
          </div>
          <div className="mt-4 space-y-3">
            {submissionDraft.sections.map((section, index) => (
              <div key={section.key} className="rounded-2xl border border-line/70 bg-panel/70 px-3 py-3">
                <div className="text-sm font-medium text-foreground">{section.label}</div>
                <div className="mt-2 grid gap-3 sm:grid-cols-[120px_1fr]">
                  <WorkspaceField>
                    <span>Rating</span>
                    <Input
                      type="number"
                      min="1"
                      max="5"
                      step="0.1"
                      value={section.rating}
                      onChange={(event) =>
                        setSubmissionDraft((current) => ({
                          ...current,
                          sections: current.sections.map((item, itemIndex) =>
                            itemIndex === index ? { ...item, rating: event.target.value } : item,
                          ),
                        }))
                      }
                      aria-label={`Section rating ${section.label}`}
                    />
                  </WorkspaceField>
                  <WorkspaceField>
                    <span>Comment</span>
                    <Textarea
                      value={section.comment}
                      onChange={(event) =>
                        setSubmissionDraft((current) => ({
                          ...current,
                          sections: current.sections.map((item, itemIndex) =>
                            itemIndex === index ? { ...item, comment: event.target.value } : item,
                          ),
                        }))
                      }
                      rows={2}
                      aria-label={`Section comment ${section.label}`}
                    />
                  </WorkspaceField>
                </div>
              </div>
            ))}
            {submissionDraft.competencies.map((competency, index) => (
              <div
                key={competency.competency_id}
                className="rounded-2xl border border-line/70 bg-panel/70 px-3 py-3"
              >
                <div className="text-sm font-medium text-foreground">{competency.name}</div>
                <div className="mt-2 grid gap-3 sm:grid-cols-[120px_1fr]">
                  <WorkspaceField>
                    <span>Rating</span>
                    <Input
                      type="number"
                      min="1"
                      max="5"
                      step="0.1"
                      value={competency.rating}
                      onChange={(event) =>
                        setSubmissionDraft((current) => ({
                          ...current,
                          competencies: current.competencies.map((item, itemIndex) =>
                            itemIndex === index ? { ...item, rating: event.target.value } : item,
                          ),
                        }))
                      }
                      aria-label={`Competency rating ${competency.name}`}
                    />
                  </WorkspaceField>
                  <WorkspaceField>
                    <span>Comment</span>
                    <Textarea
                      value={competency.comment}
                      onChange={(event) =>
                        setSubmissionDraft((current) => ({
                          ...current,
                          competencies: current.competencies.map((item, itemIndex) =>
                            itemIndex === index ? { ...item, comment: event.target.value } : item,
                          ),
                        }))
                      }
                      rows={2}
                      aria-label={`Competency comment ${competency.name}`}
                    />
                  </WorkspaceField>
                </div>
              </div>
            ))}
            <WorkspaceField>
              <span>Overall rating</span>
              <Input
                type="number"
                min="1"
                max="5"
                step="0.1"
                value={submissionDraft.overall_rating}
                onChange={(event) =>
                  setSubmissionDraft((current) => ({ ...current, overall_rating: event.target.value }))
                }
                aria-label="Overall rating"
              />
            </WorkspaceField>
            <WorkspaceField>
              <span>Summary</span>
              <Textarea
                value={submissionDraft.summary}
                onChange={(event) =>
                  setSubmissionDraft((current) => ({ ...current, summary: event.target.value }))
                }
                rows={3}
                aria-label="Review summary"
              />
            </WorkspaceField>
            <WorkspaceField>
              <span>Confidential notes</span>
              <Textarea
                value={submissionDraft.confidential_notes}
                onChange={(event) =>
                  setSubmissionDraft((current) => ({
                    ...current,
                    confidential_notes: event.target.value,
                  }))
                }
                rows={2}
                aria-label="Confidential notes"
              />
            </WorkspaceField>
          </div>
          <div className="mt-4 flex justify-end">
            <Button type="submit" disabled={workspace.pendingActionLabel !== null}>
              Submit review
            </Button>
          </div>
        </form>
      ) : null}

      {workspace.canCalibratePerformance && selectedReview.status === 'calibration' ? (
        <form
          onSubmit={handleCalibrateReview}
          className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]"
        >
          <div className="space-y-1">
            <h2 className="text-base font-semibold text-foreground">Calibration decision</h2>
            <p className="text-sm text-muted-foreground">
              Capture the calibrated rating and private alignment notes before the review is finalized.
            </p>
          </div>
          <div className="mt-4 grid gap-3">
            <WorkspaceField>
              <span>Overall rating</span>
              <Input
                type="number"
                min="1"
                max="5"
                step="0.1"
                value={calibrationDraft.overall_rating}
                onChange={(event) =>
                  setCalibrationDraft((current) => ({ ...current, overall_rating: event.target.value }))
                }
                aria-label="Calibration overall rating"
              />
            </WorkspaceField>
            <WorkspaceField>
              <span>Summary</span>
              <Textarea
                value={calibrationDraft.summary}
                onChange={(event) =>
                  setCalibrationDraft((current) => ({ ...current, summary: event.target.value }))
                }
                rows={3}
                aria-label="Calibration summary"
              />
            </WorkspaceField>
            <WorkspaceField>
              <span>Confidential notes</span>
              <Textarea
                value={calibrationDraft.confidential_notes}
                onChange={(event) =>
                  setCalibrationDraft((current) => ({
                    ...current,
                    confidential_notes: event.target.value,
                  }))
                }
                rows={2}
                aria-label="Calibration confidential notes"
              />
            </WorkspaceField>
          </div>
          <div className="mt-4 flex justify-end">
            <Button type="submit" disabled={workspace.pendingActionLabel !== null}>
              Save calibration
            </Button>
          </div>
        </form>
      ) : null}

      {workspace.canCalibratePerformance &&
      ['calibration', 'reopened'].includes(selectedReview.status) ? (
        <form
          onSubmit={handleFinalizeReview}
          className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]"
        >
          <div className="space-y-1">
            <h2 className="text-base font-semibold text-foreground">Finalize review</h2>
            <p className="text-sm text-muted-foreground">
              Lock the calibrated outcome before publish and preserve the employee-facing summary.
            </p>
          </div>
          <div className="mt-4 grid gap-3">
            <WorkspaceField>
              <span>Final rating</span>
              <Input
                type="number"
                min="1"
                max="5"
                step="0.1"
                value={finalizationDraft.final_rating}
                onChange={(event) =>
                  setFinalizationDraft((current) => ({ ...current, final_rating: event.target.value }))
                }
                aria-label="Final rating"
              />
            </WorkspaceField>
            <WorkspaceField>
              <span>Final summary</span>
              <Textarea
                value={finalizationDraft.summary}
                onChange={(event) =>
                  setFinalizationDraft((current) => ({ ...current, summary: event.target.value }))
                }
                rows={3}
                aria-label="Final summary"
              />
            </WorkspaceField>
            <WorkspaceField>
              <span>Employee visible summary</span>
              <Textarea
                value={finalizationDraft.employee_visible_summary}
                onChange={(event) =>
                  setFinalizationDraft((current) => ({
                    ...current,
                    employee_visible_summary: event.target.value,
                  }))
                }
                rows={3}
                aria-label="Employee visible summary"
              />
            </WorkspaceField>
            <WorkspaceField>
              <span>Recommendation</span>
              <Input
                value={finalizationDraft.recommendation}
                onChange={(event) =>
                  setFinalizationDraft((current) => ({ ...current, recommendation: event.target.value }))
                }
                placeholder="retain / promotion_watch / accelerate"
                aria-label="Final recommendation"
              />
            </WorkspaceField>
          </div>
          <div className="mt-4 flex justify-end">
            <Button type="submit" disabled={workspace.pendingActionLabel !== null}>
              Finalize review
            </Button>
          </div>
        </form>
      ) : null}

      {workspace.canManagePerformance && selectedReview.status === 'finalized' ? (
        <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
          <div className="space-y-1">
            <h2 className="text-base font-semibold text-foreground">Publish review</h2>
            <p className="text-sm text-muted-foreground">
              Release the finalized performance summary to the employee-facing surface once
              calibration is complete.
            </p>
          </div>
          <div className="mt-4 flex justify-end">
            <Button
              onClick={() => workspace.publishReview(selectedReview.id)}
              disabled={workspace.pendingActionLabel !== null}
            >
              Publish review
            </Button>
          </div>
        </div>
      ) : null}

      {workspace.canCalibratePerformance &&
      ['finalized', 'published'].includes(selectedReview.status) ? (
        <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
          <div className="space-y-1">
            <h2 className="text-base font-semibold text-foreground">Controlled reopen</h2>
            <p className="text-sm text-muted-foreground">
              Reopen the locked review only when the final posture is no longer trustworthy and the
              audit trail needs a new evidence round.
            </p>
          </div>
          <div className="mt-4 grid gap-3">
            <WorkspaceField>
              <span>Reason</span>
              <Textarea
                value={reopenReason}
                onChange={(event) => setReopenReason(event.target.value)}
                rows={3}
                aria-label="Reopen reason"
              />
            </WorkspaceField>
          </div>
          <div className="mt-4 flex justify-end">
            <Button
              variant="secondary"
              onClick={() => workspace.reopenReview(selectedReview.id, reopenReason)}
              disabled={
                workspace.pendingActionLabel !== null || reopenReason.trim().length === 0
              }
            >
              Reopen review
            </Button>
          </div>
        </div>
      ) : null}

      <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
        <div className="space-y-1">
          <h2 className="text-base font-semibold text-foreground">Visible submissions</h2>
          <p className="text-sm text-muted-foreground">
            This list respects the current actor role and hides restricted identities where the
            visibility rules require anonymity.
          </p>
        </div>
        <div className="mt-4 space-y-3">
          {selectedReview.submissions.map((submission) => (
            <div key={submission.id} className="rounded-2xl border border-line/70 bg-panel/70 px-3 py-3">
              <div className="flex items-center justify-between gap-3">
                <div>
                  <div className="text-sm font-medium text-foreground">
                    {formatPerformanceLabel(submission.role_type)}
                  </div>
                  <div className="text-xs text-muted-foreground">
                    {submission.is_anonymous_to_current_user
                      ? 'Anonymous reviewer'
                      : submission.submitted_by?.name ?? 'Anonymous reviewer'}{' '}
                    · {formatPerformanceDate(submission.submitted_at)}
                  </div>
                </div>
                {submission.is_anonymous_to_current_user ? (
                  <Badge variant="warning">Anonymous</Badge>
                ) : null}
              </div>
              <p className="mt-2 text-sm text-muted-foreground">{submission.summary}</p>
            </div>
          ))}
          {!selectedReview.submissions.length ? (
            <div className="rounded-2xl border border-dashed border-line bg-panel/50 px-4 py-4 text-sm text-muted-foreground">
              No visible submissions are available yet for this review.
            </div>
          ) : null}
        </div>
      </div>
    </>
  )
}

export function PerformanceReviewsPage() {
  const workspace = usePerformanceRouteWorkspace()
  const data = workspace.data
  const [activeTab, setActiveTab] = useState<ReviewTab>('all')
  const [searchTerm, setSearchTerm] = useState('')
  const [selectedReviewId, setSelectedReviewId] = useState<number | null>(null)
  const [createForm, setCreateForm] = useState<ReviewCreateFormState>({
    performance_review_cycle_id: '',
    employee_id: '',
    reviewer_user_id: '',
    launch_immediately: true,
    employee_can_view_manager_assessment_before_publish: false,
    employee_can_view_peer_feedback_after_publish: true,
    peer_feedback_anonymous_to_employee: true,
    manager_can_view_peer_feedback: true,
    reviewer_can_view_other_reviewer_feedback: false,
  })

  const filteredReviews = useMemo(() => {
    if (!data) {
      return []
    }

    const base =
      activeTab === 'all'
        ? data.reviews
        : data.reviews.filter((review) => review.status === activeTab)
    const query = searchTerm.trim().toLowerCase()
    if (!query) {
      return base
    }

    return base.filter((review) =>
      [
        review.employee?.full_name ?? '',
        review.employee?.employee_code ?? '',
        review.review_cycle?.name ?? '',
        review.status,
      ]
        .join(' ')
        .toLowerCase()
        .includes(query),
    )
  }, [activeTab, data, searchTerm])

  const selectedReview =
    filteredReviews.find((review) => review.id === selectedReviewId) ??
    data?.reviews.find((review) => review.id === selectedReviewId) ??
    filteredReviews[0] ??
    null

  const selectedCycle = useMemo(
    () =>
      data?.reviewCycles.find((cycle) => cycle.id === selectedReview?.review_cycle?.id) ?? null,
    [data?.reviewCycles, selectedReview?.review_cycle?.id],
  )

  const handleCreateReview = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    if (!createForm.performance_review_cycle_id || !createForm.employee_id) {
      return
    }

    await workspace.createReview({
      performance_review_cycle_id: Number(createForm.performance_review_cycle_id),
      employee_id: Number(createForm.employee_id),
      reviewer_user_ids: createForm.reviewer_user_id ? [Number(createForm.reviewer_user_id)] : [],
      visibility_rules: {
        employee_can_view_manager_assessment_before_publish:
          createForm.employee_can_view_manager_assessment_before_publish,
        employee_can_view_peer_feedback_after_publish:
          createForm.employee_can_view_peer_feedback_after_publish,
        peer_feedback_anonymous_to_employee: createForm.peer_feedback_anonymous_to_employee,
        manager_can_view_peer_feedback: createForm.manager_can_view_peer_feedback,
        reviewer_can_view_other_reviewer_feedback:
          createForm.reviewer_can_view_other_reviewer_feedback,
      },
      launch_immediately: createForm.launch_immediately,
    })
  }

  const actionableSubmission =
    selectedReview &&
    ['self', 'manager', 'reviewer'].includes(selectedReview.actor_role ?? '') &&
    ['self_assessment', 'manager_review', 'reopened'].includes(selectedReview.status)

  if (workspace.isLoading) {
    return (
      <WorkspaceEmptyState
        title="Loading reviews"
        copy="Resolving review queues, submissions, and calibration posture."
      />
    )
  }

  if (workspace.error) {
    return (
      <WorkspaceEmptyState
        title="Reviews unavailable"
        copy={workspace.error.message || 'The review workspace could not be loaded.'}
      />
    )
  }

  if (!data || !workspace.canViewPerformance) {
    return (
      <WorkspaceEmptyState
        title="Reviews unavailable"
        copy="This session does not currently resolve to performance review visibility."
      />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="space-y-1.5">
            <Badge variant="info">Review execution</Badge>
            <CardTitle>Performance review cockpit</CardTitle>
            <CardDescription>
              Complete self and manager review work, inspect visibility-restricted feedback, and
              drive calibration through final publication without losing audit posture.
            </CardDescription>
          </div>
          <WorkspaceHeaderActions>
            <Badge variant={workspace.source === 'demo' ? 'info' : 'neutral'}>
              {workspace.source === 'demo' ? 'Demo review posture' : 'Live review posture'}
            </Badge>
            {workspace.pendingActionLabel ? (
              <Badge variant="info">{workspace.pendingActionLabel}</Badge>
            ) : null}
            {workspace.lastActionMessage ? (
              <Badge variant="success">{workspace.lastActionMessage}</Badge>
            ) : null}
            {workspace.actionError ? <Badge variant="danger">{workspace.actionError}</Badge> : null}
          </WorkspaceHeaderActions>
        </WorkspaceHeader>

        <WorkspaceContent className="space-y-4">
          <WorkspaceTabs aria-label="Performance review tabs">
            {reviewTabs.map((tab) => (
              <WorkspaceTabButton
                key={tab.id}
                isActive={activeTab === tab.id}
                onClick={() => setActiveTab(tab.id)}
              >
                {tab.label}
              </WorkspaceTabButton>
            ))}
          </WorkspaceTabs>

          <WorkspaceField>
            <span>Search reviews</span>
            <Input
              value={searchTerm}
              onChange={(event) => setSearchTerm(event.target.value)}
              placeholder="Search by employee, cycle, or status"
            />
          </WorkspaceField>

          <WorkspaceSplit
            primary={
              <WorkspaceTableShell>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Employee</TableHead>
                      <TableHead>Cycle</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Actor role</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredReviews.map((review) => (
                      <TableRow
                        key={review.id}
                        onClick={() => setSelectedReviewId(review.id)}
                        className="cursor-pointer"
                      >
                        <TableCell>
                          <div className="space-y-1">
                            <div className="font-medium text-foreground">
                              {review.employee?.full_name ?? 'Review subject'}
                            </div>
                            <div className="text-xs text-muted-foreground">
                              {review.employee?.employee_code ?? 'No employee code'}
                            </div>
                          </div>
                        </TableCell>
                        <TableCell>{review.review_cycle?.name ?? 'Cycle pending'}</TableCell>
                        <TableCell>
                          <Badge variant={reviewStatusBadgeVariant(review.status)}>
                            {formatPerformanceLabel(review.status)}
                          </Badge>
                        </TableCell>
                        <TableCell>{formatActorRole(review.actor_role)}</TableCell>
                      </TableRow>
                    ))}
                    {!filteredReviews.length ? (
                      <TableRow>
                        <TableCell colSpan={4} className="text-sm text-muted-foreground">
                          No reviews match the selected state filters.
                        </TableCell>
                      </TableRow>
                    ) : null}
                  </TableBody>
                </Table>
              </WorkspaceTableShell>
            }
            secondary={
              <div className="space-y-4">
                {selectedReview ? (
                  <div className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                    <div className="flex items-center justify-between gap-3">
                      <div className="space-y-1">
                        <h2 className="text-base font-semibold text-foreground">
                          {selectedReview.employee?.full_name ?? 'Review detail'}
                        </h2>
                        <p className="text-sm text-muted-foreground">
                          {selectedReview.review_cycle?.name ?? 'Review cycle pending'} ·{' '}
                          {formatActorRole(selectedReview.actor_role)}
                        </p>
                      </div>
                      <Badge variant={reviewStatusBadgeVariant(selectedReview.status)}>
                        {formatPerformanceLabel(selectedReview.status)}
                      </Badge>
                    </div>
                    <div className="mt-4 grid gap-3 text-sm text-muted-foreground">
                      <div>
                        Self due {formatPerformanceDate(selectedReview.review_cycle?.self_review_due_on)} ·
                        Manager due {formatPerformanceDate(selectedReview.review_cycle?.manager_review_due_on)}
                      </div>
                      <div>
                        {selectedReview.goal_snapshot.length} goal snapshot item(s) ·{' '}
                        {selectedReview.competency_snapshot.length} competency signal(s)
                      </div>
                      {selectedReview.reopened_reason ? (
                        <div>Reopen reason: {selectedReview.reopened_reason}</div>
                      ) : null}
                    </div>
                  </div>
                ) : null}

                {workspace.canManagePerformance ? (
                  <form
                    onSubmit={handleCreateReview}
                    className="rounded-[1rem] border border-line/80 bg-white/90 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]"
                  >
                    <div className="space-y-1">
                      <h2 className="text-base font-semibold text-foreground">Launch review</h2>
                      <p className="text-sm text-muted-foreground">
                        Create a new runtime review from the configured cycle and employee goal baseline.
                      </p>
                    </div>
                    <div className="mt-4 grid gap-3">
                      <WorkspaceField>
                        <span>Review cycle</span>
                        <SelectField
                          label="Review cycle"
                          value={createForm.performance_review_cycle_id}
                          onChange={(value) =>
                            setCreateForm((current) => ({
                              ...current,
                              performance_review_cycle_id: value,
                            }))
                          }
                          options={[
                            { value: '', label: 'Select cycle' },
                            ...data.reviewCycles.map((cycle) => ({
                              value: String(cycle.id),
                              label: `${cycle.name} · ${formatPerformanceLabel(cycle.status)}`,
                            })),
                          ]}
                        />
                      </WorkspaceField>
                      <WorkspaceField>
                        <span>Employee</span>
                        <SelectField
                          label="Employee"
                          value={createForm.employee_id}
                          onChange={(value) =>
                            setCreateForm((current) => ({
                              ...current,
                              employee_id: value,
                            }))
                          }
                          options={[
                            { value: '', label: 'Select employee' },
                            ...data.employees.map((employee) => ({
                              value: String(employee.id),
                              label: `${employee.full_name} · ${employee.employee_code}`,
                            })),
                          ]}
                        />
                      </WorkspaceField>
                      <WorkspaceField>
                        <span>Peer reviewer user</span>
                        <SelectField
                          label="Peer reviewer user"
                          value={createForm.reviewer_user_id}
                          onChange={(value) =>
                            setCreateForm((current) => ({
                              ...current,
                              reviewer_user_id: value,
                            }))
                          }
                          options={[
                            ['', 'No peer reviewer'],
                            ['2', 'Tenant Administrator'],
                            ['3', 'Manager Reviewer'],
                            ['6', 'Recruiter Lead'],
                          ]}
                        />
                      </WorkspaceField>
                      <div className="grid gap-2 rounded-2xl border border-line/70 bg-panel/70 px-3 py-3 text-sm text-muted-foreground">
                        <label className="flex items-center gap-2">
                          <input
                            type="checkbox"
                            checked={createForm.launch_immediately}
                            onChange={(event) =>
                              setCreateForm((current) => ({
                                ...current,
                                launch_immediately: event.target.checked,
                              }))
                            }
                          />
                          Launch immediately
                        </label>
                        <label className="flex items-center gap-2">
                          <input
                            type="checkbox"
                            checked={
                              createForm.employee_can_view_manager_assessment_before_publish
                            }
                            onChange={(event) =>
                              setCreateForm((current) => ({
                                ...current,
                                employee_can_view_manager_assessment_before_publish:
                                  event.target.checked,
                              }))
                            }
                          />
                          Employee can see manager assessment before publish
                        </label>
                        <label className="flex items-center gap-2">
                          <input
                            type="checkbox"
                            checked={createForm.employee_can_view_peer_feedback_after_publish}
                            onChange={(event) =>
                              setCreateForm((current) => ({
                                ...current,
                                employee_can_view_peer_feedback_after_publish:
                                  event.target.checked,
                              }))
                            }
                          />
                          Employee can see peer feedback after publish
                        </label>
                        <label className="flex items-center gap-2">
                          <input
                            type="checkbox"
                            checked={createForm.peer_feedback_anonymous_to_employee}
                            onChange={(event) =>
                              setCreateForm((current) => ({
                                ...current,
                                peer_feedback_anonymous_to_employee: event.target.checked,
                              }))
                            }
                          />
                          Peer feedback stays anonymous to employee
                        </label>
                      </div>
                    </div>
                    <div className="mt-4 flex justify-end">
                      <Button type="submit" disabled={workspace.pendingActionLabel !== null}>
                        Create review
                      </Button>
                    </div>
                  </form>
                ) : null}

                {selectedReview ? (
                  <ReviewWorkflowPanels
                    key={`${selectedReview.id}:${selectedReview.status}:${selectedReview.updated_at}`}
                    selectedReview={selectedReview}
                    selectedCycle={selectedCycle}
                    workspace={workspace}
                    actionableSubmission={Boolean(actionableSubmission)}
                  />
                ) : null}
              </div>
            }
          />
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}
