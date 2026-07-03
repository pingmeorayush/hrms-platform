import { useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import type { Dispatch, SetStateAction } from 'react'
import { ArrowRight, Download, PlaneTakeoff, UserRoundCheck } from 'lucide-react'
import { formatRegionalNumber } from '../../../shared/regionalization/formatters'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import { Textarea } from '../../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSurface,
} from '../../../shared/ui/workspace'
import { SelectField } from '../../../shared/ui/select-field'
import { useRecruitmentCandidateDetail } from '../hooks/useRecruitmentWorkspace'
import { useRecruitmentRouteWorkspace } from './useRecruitmentRouteWorkspace'
import type {
  CreateRecruitmentOfferInput,
  RecruitmentCandidateStage,
  RecruitmentInterviewRecord,
  RecruitmentOfferAction,
} from '../types'
import {
  candidateStageBadgeVariant,
  candidateStatusBadgeVariant,
  formatRecruitmentCurrency,
  formatRecruitmentDate,
  formatRecruitmentDateTime,
  formatRecruitmentLabel,
  handoffStatusBadgeVariant,
  interviewStatusBadgeVariant,
  meetingModeLabel,
  nextCandidateStage,
  offerStatusBadgeVariant,
  recruitmentCandidateStages,
} from '../utils'

const stageOptions: Array<[string, string]> = recruitmentCandidateStages.map((stage) => [stage, formatRecruitmentLabel(stage)])
const interviewTypeOptions: Array<[string, string]> = [
  ['screening', 'Screening'],
  ['technical', 'Technical'],
  ['managerial', 'Managerial'],
  ['hr', 'HR'],
  ['culture', 'Culture'],
]
const meetingModeOptions: Array<[string, string]> = [
  ['virtual', 'Virtual'],
  ['onsite', 'Onsite'],
  ['phone', 'Phone'],
]
const recommendationOptions: Array<[string, string]> = [
  ['strong_hire', 'Strong hire'],
  ['hire', 'Hire'],
  ['hold', 'Hold'],
  ['no_hire', 'No hire'],
]
const offerActionLabels: Record<RecruitmentOfferAction, string> = {
  submit: 'Submit for approval',
  approve: 'Approve',
  reject: 'Reject',
  request_changes: 'Request changes',
  mark_sent: 'Mark sent',
  record_acceptance: 'Record acceptance',
  record_decline: 'Record decline',
  mark_expired: 'Mark expired',
}

export function RecruitmentCandidateDetailPage() {
  const params = useParams()
  const candidateId = Number(params.candidateId)
  const workspace = useRecruitmentRouteWorkspace()
  const detail = useRecruitmentCandidateDetail(Number.isNaN(candidateId) ? null : candidateId)

  const candidate = detail.candidate
  const candidateInterviews = useMemo(
    () => (workspace.data?.interviews ?? []).filter((interview) => interview.candidate?.id === candidate?.id),
    [candidate?.id, workspace.data?.interviews],
  )
  const candidateOffers = useMemo(
    () =>
      (workspace.data?.offers ?? [])
        .filter((offer) => offer.candidate?.id === candidate?.id)
        .sort((left, right) => `${right.created_at ?? ''}`.localeCompare(`${left.created_at ?? ''}`)),
    [candidate?.id, workspace.data?.offers],
  )
  const candidateHandoff = useMemo(
    () => (workspace.data?.handoffs ?? []).find((handoff) => handoff.candidate?.id === candidate?.id) ?? null,
    [candidate?.id, workspace.data?.handoffs],
  )

  const [stageTarget, setStageTarget] = useState<RecruitmentCandidateStage>('screening')
  const [stageComment, setStageComment] = useState('')
  const [offerComment, setOfferComment] = useState('')
  const [feedbackInterviewId, setFeedbackInterviewId] = useState<number | null>(null)
  const [feedbackForm, setFeedbackForm] = useState({
    rating: '4',
    recommendation: 'hire',
    comments: '',
    strengths: '',
    concerns: '',
  })
  const [interviewForm, setInterviewForm] = useState(() => ({
    interviewer_user_id: '',
    round_number: '1',
    interview_type: 'technical',
    scheduled_start_at: '',
    scheduled_end_at: '',
    meeting_mode: 'virtual',
    meeting_link: 'https://meet.example.com/recruitment-demo',
    meeting_location: '',
    agenda: '',
  }))
  const [offerForm, setOfferForm] = useState<CreateRecruitmentOfferInput>({
    job_requisition_id: candidate?.requisition?.id ?? 0,
    candidate_id: candidate?.id ?? 0,
    employment_type: 'full_time',
    currency: 'INR',
    annual_ctc_amount: 0,
    joining_bonus_amount: 0,
    proposed_start_date: '',
    expires_on: '',
    notes: '',
    candidate_message: '',
  })
  const [handoffNotes, setHandoffNotes] = useState('')
  const [triggerOnboarding, setTriggerOnboarding] = useState(true)

  const activeOffer = candidateOffers[0] ?? null
  const interviewerOptions = workspace.data?.directory.interviewers ?? []
  const nextStage = candidate ? nextCandidateStage(candidate.current_stage) : null

  async function handleStageMove() {
    if (!candidate) {
      return
    }

    await workspace.actions.moveCandidateStage(candidate.id, stageTarget, stageComment)
    setStageComment('')
  }

  async function handleScheduleInterview() {
    if (!candidate?.requisition || !interviewForm.interviewer_user_id) {
      return
    }

    await workspace.actions.createInterview({
      job_requisition_id: candidate.requisition.id,
      candidate_id: candidate.id,
      interviewer_user_id: Number(interviewForm.interviewer_user_id),
      round_number: Number(interviewForm.round_number),
      interview_type: interviewForm.interview_type as 'screening' | 'technical' | 'managerial' | 'hr' | 'culture',
      timezone: workspace.snapshot?.user.tenant.timezone ?? 'Asia/Kolkata',
      scheduled_start_at: new Date(interviewForm.scheduled_start_at).toISOString(),
      scheduled_end_at: new Date(interviewForm.scheduled_end_at).toISOString(),
      meeting_mode: interviewForm.meeting_mode as 'virtual' | 'onsite' | 'phone',
      meeting_link: interviewForm.meeting_link || null,
      meeting_location: interviewForm.meeting_location || null,
      agenda: interviewForm.agenda || null,
    })
  }

  async function handleSubmitFeedback(interviewId: number) {
    await workspace.actions.submitInterviewFeedback(interviewId, {
      rating: Number(feedbackForm.rating),
      recommendation: feedbackForm.recommendation as 'strong_hire' | 'hire' | 'hold' | 'no_hire',
      comments: feedbackForm.comments,
      strengths: feedbackForm.strengths || null,
      concerns: feedbackForm.concerns || null,
    })
    setFeedbackInterviewId(null)
    setFeedbackForm({
      rating: '4',
      recommendation: 'hire',
      comments: '',
      strengths: '',
      concerns: '',
    })
  }

  async function handleCreateOffer() {
    if (!candidate?.requisition) {
      return
    }

    await workspace.actions.createOffer({
      ...offerForm,
      job_requisition_id: candidate.requisition.id,
      candidate_id: candidate.id,
      recruiter_user_id: candidate.recruiter?.id ?? workspace.snapshot?.user.id ?? null,
      proposed_start_date: offerForm.proposed_start_date || null,
      joining_bonus_amount: offerForm.joining_bonus_amount || null,
      notes: offerForm.notes || null,
      candidate_message: offerForm.candidate_message || null,
    })
  }

  async function handleOfferAction(action: RecruitmentOfferAction) {
    if (!activeOffer) {
      return
    }

    await workspace.actions.updateOfferStatus(activeOffer.id, action, offerComment)
    setOfferComment('')
  }

  async function handleCreateHandoff() {
    if (!activeOffer) {
      return
    }

    await workspace.actions.createHandoff(activeOffer.id, {
      trigger_onboarding: triggerOnboarding,
      notes: handoffNotes || null,
    })
    setHandoffNotes('')
  }

  if (detail.isLoading) {
    return <WorkspaceEmptyState title="Loading candidate detail" copy="Resolving resumes, stage history, interviews, offers, and handoff posture." />
  }

  if (detail.error) {
    return <WorkspaceEmptyState title="Candidate detail unavailable" copy={detail.error.message || 'Unable to load candidate detail.'} />
  }

  if (!candidate) {
    return (
      <WorkspaceEmptyState
        title="Candidate not found in this scope"
        copy="This session either cannot access the requested candidate or the candidate does not exist in the current recruitment scope."
        actions={
          <Button asChild>
            <Link to="/recruitment/candidates">Return to candidate board</Link>
          </Button>
        }
      />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader>
          <div className="space-y-1.5">
            <div className="flex flex-wrap items-center gap-2">
              <Badge variant={candidateStageBadgeVariant(candidate.current_stage)}>
                {formatRecruitmentLabel(candidate.current_stage)}
              </Badge>
              <Badge variant={candidateStatusBadgeVariant(candidate.status)}>
                {formatRecruitmentLabel(candidate.status)}
              </Badge>
              <Badge variant="neutral">{candidate.candidate_code}</Badge>
            </div>
            <h1 className="text-xl font-semibold text-foreground">{candidate.full_name}</h1>
            <p className="text-sm text-muted-foreground">
              {candidate.current_title ?? 'Role pending'} · {candidate.current_company ?? 'Company pending'} ·{' '}
              {candidate.requisition?.requisition_code ?? 'REQ pending'}
            </p>
          </div>
          <WorkspaceHeaderActions>
            <Button asChild variant="secondary" size="sm">
              <Link to="/recruitment/candidates">Back to pipeline</Link>
            </Button>
            {candidate.latest_resume ? (
              <Button
                size="sm"
                onClick={() =>
                  workspace.actions.downloadResume(
                    candidate.id,
                    candidate.latest_resume?.id ?? 0,
                    candidate.latest_resume?.original_file_name ?? 'resume.pdf',
                    candidate.latest_resume?.download_url ?? '',
                  )
                }
              >
                <Download className="h-4 w-4" />
                Download current resume
              </Button>
            ) : null}
          </WorkspaceHeaderActions>
        </WorkspaceHeader>
        <WorkspaceContent className="space-y-4">
          {workspace.pendingActionLabel ? (
            <div className="rounded-xl border border-line bg-panel-soft px-3 py-2 text-sm text-foreground">
              {workspace.pendingActionLabel}…
            </div>
          ) : null}
          {workspace.lastActionMessage ? (
            <div className="rounded-xl border border-[color-mix(in_srgb,var(--success)_22%,white)] bg-[color-mix(in_srgb,var(--success)_10%,white)] px-3 py-2 text-sm text-success">
              {workspace.lastActionMessage}
            </div>
          ) : null}
          {workspace.actionError ? (
            <div className="rounded-xl border border-[color-mix(in_srgb,var(--danger)_22%,white)] bg-[color-mix(in_srgb,var(--danger)_10%,white)] px-3 py-2 text-sm text-destructive">
              {workspace.actionError}
            </div>
          ) : null}

          <div className="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
            <section className="rounded-[1.1rem] border border-line/80 bg-white/94 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
              <div className="flex items-center justify-between gap-2">
                <h2 className="text-base font-semibold text-foreground">Candidate summary</h2>
                <Badge variant="info">{candidate.source}</Badge>
              </div>
              <dl className="mt-4 grid gap-3 text-sm md:grid-cols-2">
                <InfoRow label="Email" value={candidate.email} />
                <InfoRow label="Phone" value={candidate.phone ?? 'Not captured'} />
                <InfoRow label="Recruiter" value={candidate.recruiter?.name ?? 'Unassigned'} />
                <InfoRow label="Notice period" value={candidate.notice_period_days ? `${candidate.notice_period_days} days` : 'Not captured'} />
                <InfoRow label="Experience" value={candidate.total_experience_years ? `${candidate.total_experience_years} years` : 'Not captured'} />
                <InfoRow label="Stage entered" value={formatRecruitmentDate(candidate.stage_entered_at)} />
              </dl>

              {candidate.summary ? (
                <div className="mt-4 rounded-xl border border-line/80 bg-panel-soft/60 px-3 py-3">
                  <div className="text-xs font-semibold uppercase tracking-[0.14em] text-text-subtle">Summary</div>
                  <p className="mt-1 text-sm text-foreground">{candidate.summary}</p>
                </div>
              ) : null}

              {workspace.canManageRecruitment ? (
                <div className="mt-4 rounded-xl border border-line/80 bg-panel-soft/60 p-3">
                  <h3 className="text-sm font-semibold text-foreground">Move stage</h3>
                  <div className="mt-3 grid gap-3 md:grid-cols-2">
                    <SelectField
                      label="Next stage"
                      value={stageTarget}
                      options={stageOptions}
                      onChange={(value) => setStageTarget(value as RecruitmentCandidateStage)}
                      compact
                    />
                    <WorkspaceField label="Comment" compact>
                      <Input
                        value={stageComment}
                        onChange={(event) => setStageComment(event.target.value)}
                        placeholder={nextStage ? `For example: move to ${formatRecruitmentLabel(nextStage)}` : 'Explain the transition'}
                      />
                    </WorkspaceField>
                  </div>
                  <div className="mt-3 flex flex-wrap gap-2">
                    <Button size="sm" onClick={handleStageMove}>
                      Update stage
                    </Button>
                    {nextStage ? (
                      <Button
                        size="sm"
                        variant="secondary"
                        onClick={() =>
                          workspace.actions.moveCandidateStage(
                            candidate.id,
                            nextStage,
                            `Quick advanced to ${formatRecruitmentLabel(nextStage)} from candidate detail.`,
                          )
                        }
                      >
                        Quick move to {formatRecruitmentLabel(nextStage)}
                      </Button>
                    ) : null}
                  </div>
                </div>
              ) : null}
            </section>

            <section className="rounded-[1.1rem] border border-line/80 bg-white/94 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
              <div className="flex items-center justify-between gap-2">
                <h2 className="text-base font-semibold text-foreground">Resume versions</h2>
                <Badge variant="neutral">{candidate.resumes?.length ?? 0} files</Badge>
              </div>
              <div className="mt-3 space-y-2.5">
                {(candidate.resumes ?? []).length ? (
                  (candidate.resumes ?? []).map((resume) => (
                    <div key={resume.id} className="rounded-xl border border-line/80 bg-panel-soft/60 px-3 py-3">
                      <div className="flex items-center justify-between gap-2">
                        <div>
                          <div className="font-semibold text-foreground">{resume.original_file_name}</div>
                          <div className="text-xs text-muted-foreground">
                            v{resume.version_number} · {formatRegionalNumber(resume.file_size_bytes)} bytes ·{' '}
                            {formatRecruitmentDate(resume.created_at)}
                          </div>
                        </div>
                        <div className="flex items-center gap-2">
                          {resume.is_current ? <Badge variant="success">Current</Badge> : null}
                          <Button
                            size="xs"
                            variant="secondary"
                            onClick={() => workspace.actions.downloadResume(candidate.id, resume.id, resume.original_file_name, resume.download_url)}
                          >
                            <Download className="h-4 w-4" />
                            Download
                          </Button>
                        </div>
                      </div>
                      {resume.notes ? <p className="mt-2 text-sm text-muted-foreground">{resume.notes}</p> : null}
                    </div>
                  ))
                ) : (
                  <p className="text-sm text-muted-foreground">No resume versions are attached to this candidate yet.</p>
                )}
              </div>
            </section>
          </div>

          <section className="rounded-[1.1rem] border border-line/80 bg-white/94 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
            <div className="flex items-center justify-between gap-2">
              <h2 className="text-base font-semibold text-foreground">Stage timeline</h2>
              <Badge variant="neutral">{candidate.stage_history?.length ?? 0} events</Badge>
            </div>
            <div className="mt-3 space-y-3">
              {(candidate.stage_history ?? []).length ? (
                (candidate.stage_history ?? []).map((event) => (
                  <div key={event.id} className="rounded-xl border border-line/80 bg-panel-soft/60 px-3 py-3">
                    <div className="flex flex-wrap items-center gap-2">
                      <Badge variant="subtle">{event.from_stage ? formatRecruitmentLabel(event.from_stage) : 'Start'}</Badge>
                      <ArrowRight className="h-4 w-4 text-text-subtle" />
                      <Badge variant={candidateStageBadgeVariant(event.to_stage)}>{formatRecruitmentLabel(event.to_stage)}</Badge>
                      <Badge variant={candidateStatusBadgeVariant(event.resulting_status)}>
                        {formatRecruitmentLabel(event.resulting_status)}
                      </Badge>
                    </div>
                    <div className="mt-2 text-sm text-muted-foreground">
                      {event.comment ?? 'No comment captured'} · {formatRecruitmentDateTime(event.transitioned_at)}
                    </div>
                  </div>
                ))
              ) : (
                <p className="text-sm text-muted-foreground">No stage history is currently available for this candidate.</p>
              )}
            </div>
          </section>

          <div className="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
            <section className="rounded-[1.1rem] border border-line/80 bg-white/94 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
              <div className="flex items-center justify-between gap-2">
                <h2 className="text-base font-semibold text-foreground">Interview coordination</h2>
                <Badge variant="info">{candidateInterviews.length} rounds</Badge>
              </div>

              {workspace.canManageRecruitment ? (
                <div className="mt-4 rounded-xl border border-line/80 bg-panel-soft/60 p-3">
                  <h3 className="text-sm font-semibold text-foreground">Schedule next interview</h3>
                  <div className="mt-3 grid gap-3 md:grid-cols-2">
                    <SelectField
                      label="Interviewer"
                      value={interviewForm.interviewer_user_id}
                      options={[
                        ['', interviewerOptions.length ? 'Select interviewer' : 'No interviewer options'],
                        ...interviewerOptions.map((option) => [String(option.id), option.name] as [string, string]),
                      ]}
                      compact
                      onChange={(value) => setInterviewForm((current) => ({ ...current, interviewer_user_id: value }))}
                    />
                    <WorkspaceField label="Round" compact>
                      <Input
                        type="number"
                        min={1}
                        value={interviewForm.round_number}
                        onChange={(event) => setInterviewForm((current) => ({ ...current, round_number: event.target.value }))}
                      />
                    </WorkspaceField>
                    <SelectField
                      label="Interview type"
                      value={interviewForm.interview_type}
                      options={interviewTypeOptions}
                      compact
                      onChange={(value) => setInterviewForm((current) => ({ ...current, interview_type: value }))}
                    />
                    <SelectField
                      label="Meeting mode"
                      value={interviewForm.meeting_mode}
                      options={meetingModeOptions}
                      compact
                      onChange={(value) => setInterviewForm((current) => ({ ...current, meeting_mode: value }))}
                    />
                    <WorkspaceField label="Start" compact>
                      <Input
                        type="datetime-local"
                        value={interviewForm.scheduled_start_at}
                        onChange={(event) => setInterviewForm((current) => ({ ...current, scheduled_start_at: event.target.value }))}
                      />
                    </WorkspaceField>
                    <WorkspaceField label="End" compact>
                      <Input
                        type="datetime-local"
                        value={interviewForm.scheduled_end_at}
                        onChange={(event) => setInterviewForm((current) => ({ ...current, scheduled_end_at: event.target.value }))}
                      />
                    </WorkspaceField>
                    <WorkspaceField label="Meeting link" compact>
                      <Input
                        value={interviewForm.meeting_link}
                        onChange={(event) => setInterviewForm((current) => ({ ...current, meeting_link: event.target.value }))}
                        placeholder="https://meet.example.com/session"
                      />
                    </WorkspaceField>
                    <WorkspaceField label="Location" compact>
                      <Input
                        value={interviewForm.meeting_location}
                        onChange={(event) => setInterviewForm((current) => ({ ...current, meeting_location: event.target.value }))}
                        placeholder="Optional for onsite rounds"
                      />
                    </WorkspaceField>
                  </div>
                  <WorkspaceField label="Agenda" className="mt-3">
                    <Textarea
                      value={interviewForm.agenda}
                      onChange={(event) => setInterviewForm((current) => ({ ...current, agenda: event.target.value }))}
                      className="min-h-24"
                      placeholder="Capture the panel focus, decision goals, or candidate prep notes."
                    />
                  </WorkspaceField>
                  <div className="mt-3">
                    <Button size="sm" onClick={handleScheduleInterview} disabled={!interviewForm.interviewer_user_id}>
                      Schedule interview
                    </Button>
                  </div>
                </div>
              ) : null}

              <div className="mt-4 space-y-3">
                {candidateInterviews.length ? (
                  candidateInterviews.map((interview) => (
                    <InterviewCard
                      key={interview.id}
                      interview={interview}
                      canManage={workspace.canManageRecruitment}
                      canSubmitFeedback={workspace.canSubmitInterviewFeedback}
                      feedbackInterviewId={feedbackInterviewId}
                      feedbackForm={feedbackForm}
                      setFeedbackForm={setFeedbackForm}
                      setFeedbackInterviewId={setFeedbackInterviewId}
                      onCancel={(comment) => workspace.actions.cancelInterview(interview.id, comment)}
                      onSubmitFeedback={() => handleSubmitFeedback(interview.id)}
                    />
                  ))
                ) : (
                  <p className="text-sm text-muted-foreground">No interview rounds are currently attached to this candidate.</p>
                )}
              </div>
            </section>

            <section className="space-y-4">
              <div className="rounded-[1.1rem] border border-line/80 bg-white/94 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <div className="flex items-center justify-between gap-2">
                  <h2 className="text-base font-semibold text-foreground">Offer posture</h2>
                  <Badge variant="warning">{candidateOffers.length} record(s)</Badge>
                </div>

                {!candidateOffers.length && workspace.canManageRecruitment ? (
                  <div className="mt-4 rounded-xl border border-line/80 bg-panel-soft/60 p-3">
                    <h3 className="text-sm font-semibold text-foreground">Draft an offer</h3>
                    <div className="mt-3 grid gap-3 md:grid-cols-2">
                      <WorkspaceField label="Annual CTC" compact>
                        <Input
                          type="number"
                          min={0}
                          value={offerForm.annual_ctc_amount}
                          onChange={(event) =>
                            setOfferForm((current) => ({ ...current, annual_ctc_amount: Number(event.target.value) }))
                          }
                        />
                      </WorkspaceField>
                      <WorkspaceField label="Joining bonus" compact>
                        <Input
                          type="number"
                          min={0}
                          value={offerForm.joining_bonus_amount ?? 0}
                          onChange={(event) =>
                            setOfferForm((current) => ({ ...current, joining_bonus_amount: Number(event.target.value) }))
                          }
                        />
                      </WorkspaceField>
                      <WorkspaceField label="Proposed start date" compact>
                        <Input
                          type="date"
                          value={offerForm.proposed_start_date ?? ''}
                          onChange={(event) => setOfferForm((current) => ({ ...current, proposed_start_date: event.target.value }))}
                        />
                      </WorkspaceField>
                      <WorkspaceField label="Expires on" compact>
                        <Input
                          type="date"
                          value={offerForm.expires_on}
                          onChange={(event) => setOfferForm((current) => ({ ...current, expires_on: event.target.value }))}
                        />
                      </WorkspaceField>
                    </div>
                    <WorkspaceField label="Candidate message" className="mt-3">
                      <Textarea
                        className="min-h-24"
                        value={offerForm.candidate_message ?? ''}
                        onChange={(event) => setOfferForm((current) => ({ ...current, candidate_message: event.target.value }))}
                      />
                    </WorkspaceField>
                    <div className="mt-3">
                      <Button size="sm" onClick={handleCreateOffer} disabled={!offerForm.expires_on || !offerForm.annual_ctc_amount}>
                        Create draft offer
                      </Button>
                    </div>
                  </div>
                ) : null}

                {activeOffer ? (
                  <div className="mt-4 rounded-xl border border-line/80 bg-panel-soft/60 p-3">
                    <div className="flex flex-wrap items-center gap-2">
                      <Badge variant={offerStatusBadgeVariant(activeOffer.status)}>
                        {formatRecruitmentLabel(activeOffer.status)}
                      </Badge>
                      <Badge variant="neutral">{activeOffer.offer_code}</Badge>
                    </div>
                    <div className="mt-3 space-y-1 text-sm">
                      <div className="font-semibold text-foreground">
                        {formatRecruitmentCurrency(activeOffer.annual_ctc_amount, activeOffer.currency)}
                      </div>
                      <div className="text-muted-foreground">
                        Starts {formatRecruitmentDate(activeOffer.proposed_start_date)} · expires {formatRecruitmentDate(activeOffer.expires_on)}
                      </div>
                    </div>

                    <WorkspaceField label="Offer action comment" className="mt-3">
                      <Textarea
                        className="min-h-24"
                        value={offerComment}
                        onChange={(event) => setOfferComment(event.target.value)}
                        placeholder="Explain approval context, candidate response, or expiry notes."
                      />
                    </WorkspaceField>

                    <div className="mt-3 flex flex-wrap gap-2">
                      {workspace.canManageRecruitment &&
                      ['draft', 'rejected', 'changes_requested'].includes(activeOffer.status) ? (
                        <Button size="sm" onClick={() => handleOfferAction('submit')}>
                          {offerActionLabels.submit}
                        </Button>
                      ) : null}
                      {workspace.canApproveRecruitment && activeOffer.status === 'submitted' ? (
                        <>
                          <Button size="sm" onClick={() => handleOfferAction('approve')}>
                            {offerActionLabels.approve}
                          </Button>
                          <Button size="sm" variant="secondary" onClick={() => handleOfferAction('request_changes')}>
                            {offerActionLabels.request_changes}
                          </Button>
                          <Button size="sm" variant="danger" onClick={() => handleOfferAction('reject')}>
                            {offerActionLabels.reject}
                          </Button>
                        </>
                      ) : null}
                      {workspace.canManageRecruitment && activeOffer.status === 'approved' ? (
                        <Button size="sm" onClick={() => handleOfferAction('mark_sent')}>
                          {offerActionLabels.mark_sent}
                        </Button>
                      ) : null}
                      {workspace.canManageRecruitment && activeOffer.status === 'sent' ? (
                        <>
                          <Button size="sm" onClick={() => handleOfferAction('record_acceptance')}>
                            {offerActionLabels.record_acceptance}
                          </Button>
                          <Button size="sm" variant="secondary" onClick={() => handleOfferAction('record_decline')}>
                            {offerActionLabels.record_decline}
                          </Button>
                          <Button size="sm" variant="ghost" onClick={() => handleOfferAction('mark_expired')}>
                            {offerActionLabels.mark_expired}
                          </Button>
                        </>
                      ) : null}
                    </div>

                    {(activeOffer.decision_history ?? []).length ? (
                      <div className="mt-4 space-y-2">
                        <div className="text-xs font-semibold uppercase tracking-[0.14em] text-text-subtle">
                          Decision history
                        </div>
                        {(activeOffer.decision_history ?? []).map((decision) => (
                          <div key={decision.id} className="rounded-xl border border-line/80 bg-white/80 px-3 py-2 text-sm">
                            <div className="font-medium text-foreground">
                              {formatRecruitmentLabel(decision.decision_type)} · {formatRecruitmentLabel(decision.to_status)}
                            </div>
                            <div className="text-muted-foreground">
                              {decision.actor?.name ?? 'System'} · {formatRecruitmentDateTime(decision.acted_at)}
                            </div>
                          </div>
                        ))}
                      </div>
                    ) : null}
                  </div>
                ) : (
                  <p className="mt-3 text-sm text-muted-foreground">No offer has been created for this candidate yet.</p>
                )}
              </div>

              <div className="rounded-[1.1rem] border border-line/80 bg-white/94 p-4 shadow-[0_14px_28px_rgba(15,23,42,0.05)]">
                <div className="flex items-center justify-between gap-2">
                  <h2 className="text-base font-semibold text-foreground">Hire handoff</h2>
                  {candidateHandoff ? (
                    <Badge variant={handoffStatusBadgeVariant(candidateHandoff.status)}>
                      {formatRecruitmentLabel(candidateHandoff.status)}
                    </Badge>
                  ) : (
                    <Badge variant="subtle">Pending</Badge>
                  )}
                </div>

                {candidateHandoff ? (
                  <div className="mt-3 space-y-3">
                    <div className="rounded-xl border border-line/80 bg-panel-soft/60 px-3 py-3">
                      <div className="font-semibold text-foreground">
                        {candidateHandoff.employee?.full_name ?? 'Employee pending'} · {candidateHandoff.employee?.employee_code ?? 'EMP pending'}
                      </div>
                      <div className="mt-1 text-sm text-muted-foreground">
                        Converted {formatRecruitmentDateTime(candidateHandoff.converted_at)} · onboarding{' '}
                        {candidateHandoff.onboarding_template_ids.length ? `queued with ${candidateHandoff.onboarding_template_ids.length} templates` : 'not queued'}
                      </div>
                      {candidateHandoff.employee ? (
                        <div className="mt-3">
                          <Button asChild size="sm" variant="secondary">
                            <Link to={`/employees/${candidateHandoff.employee.id}`}>Open employee workspace</Link>
                          </Button>
                        </div>
                      ) : null}
                    </div>
                  </div>
                ) : activeOffer?.status === 'accepted' && workspace.canConvertHandoffs ? (
                  <div className="mt-3 rounded-xl border border-line/80 bg-panel-soft/60 p-3">
                    <div className="flex items-center gap-2">
                      <PlaneTakeoff className="h-4 w-4 text-primary" />
                      <p className="text-sm text-foreground">
                        This offer is accepted and ready for conversion into a hire handoff.
                      </p>
                    </div>
                    <WorkspaceField label="Handoff notes" className="mt-3">
                      <Textarea
                        className="min-h-24"
                        value={handoffNotes}
                        onChange={(event) => setHandoffNotes(event.target.value)}
                        placeholder="Capture onboarding context, provisioning notes, or conversion details."
                      />
                    </WorkspaceField>
                    <label className="mt-3 flex items-center gap-2 text-sm text-foreground">
                      <input
                        type="checkbox"
                        checked={triggerOnboarding}
                        onChange={(event) => setTriggerOnboarding(event.target.checked)}
                      />
                      Trigger onboarding templates from the handoff event
                    </label>
                    <div className="mt-3">
                      <Button size="sm" onClick={handleCreateHandoff}>
                        <UserRoundCheck className="h-4 w-4" />
                        Create hire handoff
                      </Button>
                    </div>
                  </div>
                ) : (
                  <p className="mt-3 text-sm text-muted-foreground">
                    Handoff becomes available once an offer is accepted and the session can create employees.
                  </p>
                )}
              </div>
            </section>
          </div>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function InfoRow({ label, value }: { label: string; value: string }) {
  return (
    <div>
      <dt className="text-xs font-semibold uppercase tracking-[0.14em] text-text-subtle">{label}</dt>
      <dd className="mt-1 text-sm text-foreground">{value}</dd>
    </div>
  )
}

function InterviewCard({
  interview,
  canManage,
  canSubmitFeedback,
  feedbackInterviewId,
  feedbackForm,
  setFeedbackForm,
  setFeedbackInterviewId,
  onCancel,
  onSubmitFeedback,
}: {
  interview: RecruitmentInterviewRecord
  canManage: boolean
  canSubmitFeedback: boolean
  feedbackInterviewId: number | null
  feedbackForm: {
    rating: string
    recommendation: string
    comments: string
    strengths: string
    concerns: string
  }
  setFeedbackForm: Dispatch<
    SetStateAction<{
      rating: string
      recommendation: string
      comments: string
      strengths: string
      concerns: string
    }>
  >
  setFeedbackInterviewId: Dispatch<SetStateAction<number | null>>
  onCancel: (comment: string) => Promise<void>
  onSubmitFeedback: () => Promise<void>
}) {
  const [cancelComment, setCancelComment] = useState('')
  const showFeedbackForm = feedbackInterviewId === interview.id

  return (
    <div className="rounded-xl border border-line/80 bg-panel-soft/60 px-3 py-3">
      <div className="flex flex-wrap items-center gap-2">
        <Badge variant={interviewStatusBadgeVariant(interview.status)}>{formatRecruitmentLabel(interview.status)}</Badge>
        <Badge variant="neutral">{interview.interview_code}</Badge>
        <Badge variant="subtle">{formatRecruitmentLabel(interview.interview_type)}</Badge>
      </div>
      <div className="mt-2 text-sm text-foreground">
        {formatRecruitmentDateTime(interview.scheduled_start_at)} · {meetingModeLabel(interview.meeting_mode)}
      </div>
      <div className="mt-1 text-sm text-muted-foreground">
        {interview.interviewer?.name ?? 'Interviewer pending'} · {interview.agenda ?? 'Agenda pending'}
      </div>

      {interview.feedback ? (
        <div className="mt-3 rounded-xl border border-line/80 bg-white/80 px-3 py-3">
          <div className="font-semibold text-foreground">
            Rating {interview.feedback.rating}/5 · {formatRecruitmentLabel(interview.feedback.recommendation)}
          </div>
          <p className="mt-1 text-sm text-muted-foreground">{interview.feedback.comments}</p>
        </div>
      ) : null}

      {interview.status === 'scheduled' && canManage ? (
        <div className="mt-3 space-y-3">
          <WorkspaceField label="Cancellation note">
            <Input
              value={cancelComment}
              onChange={(event) => setCancelComment(event.target.value)}
              placeholder="Explain why the interview is being cancelled."
            />
          </WorkspaceField>
          <div className="flex flex-wrap gap-2">
            <Button size="xs" variant="ghost" onClick={() => onCancel(cancelComment)} disabled={!cancelComment.trim()}>
              Cancel interview
            </Button>
            {!interview.feedback && canSubmitFeedback ? (
              <Button size="xs" variant="secondary" onClick={() => setFeedbackInterviewId(showFeedbackForm ? null : interview.id)}>
                {showFeedbackForm ? 'Hide scorecard' : 'Submit scorecard'}
              </Button>
            ) : null}
          </div>
        </div>
      ) : null}

      {showFeedbackForm ? (
        <div className="mt-3 rounded-xl border border-line/80 bg-white/80 p-3">
          <div className="grid gap-3 md:grid-cols-2">
            <WorkspaceField label="Rating" compact>
              <Input
                type="number"
                min={1}
                max={5}
                value={feedbackForm.rating}
                onChange={(event) => setFeedbackForm((current) => ({ ...current, rating: event.target.value }))}
              />
            </WorkspaceField>
            <SelectField
              label="Recommendation"
              value={feedbackForm.recommendation}
              options={recommendationOptions}
              compact
              onChange={(value) => setFeedbackForm((current) => ({ ...current, recommendation: value }))}
            />
          </div>
          <WorkspaceField label="Comments" className="mt-3">
            <Textarea
              className="min-h-24"
              value={feedbackForm.comments}
              onChange={(event) => setFeedbackForm((current) => ({ ...current, comments: event.target.value }))}
            />
          </WorkspaceField>
          <div className="mt-3 grid gap-3 md:grid-cols-2">
            <WorkspaceField label="Strengths" compact>
              <Textarea
                className="min-h-20"
                value={feedbackForm.strengths}
                onChange={(event) => setFeedbackForm((current) => ({ ...current, strengths: event.target.value }))}
              />
            </WorkspaceField>
            <WorkspaceField label="Concerns" compact>
              <Textarea
                className="min-h-20"
                value={feedbackForm.concerns}
                onChange={(event) => setFeedbackForm((current) => ({ ...current, concerns: event.target.value }))}
              />
            </WorkspaceField>
          </div>
          <div className="mt-3 flex gap-2">
            <Button size="xs" onClick={onSubmitFeedback} disabled={!feedbackForm.comments.trim()}>
              Save scorecard
            </Button>
            <Button size="xs" variant="secondary" onClick={() => setFeedbackInterviewId(null)}>
              Cancel
            </Button>
          </div>
        </div>
      ) : null}
    </div>
  )
}
