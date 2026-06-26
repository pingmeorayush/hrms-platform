import { useCallback, useMemo, useState } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { ApiRequestError } from '../../../shared/api/http'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  cancelRecruitmentInterview,
  createRecruitmentHandoff,
  createRecruitmentOffer,
  downloadRecruitmentResume,
  fetchRecruitmentCandidateDetail,
  fetchRecruitmentWorkspace,
  scheduleRecruitmentInterview,
  submitRecruitmentInterviewFeedback,
  transitionRecruitmentCandidateStage,
  updateRecruitmentOffer,
  updateRecruitmentRequisition,
} from '../api/recruitmentApi'
import { buildDemoRecruitmentWorkspace } from '../data/demoRecruitmentWorkspace'
import type {
  CreateRecruitmentHandoffInput,
  CreateRecruitmentOfferInput,
  RecruitmentCandidateRecord,
  RecruitmentCandidateStatus,
  RecruitmentEmployeeReference,
  RecruitmentHireHandoffRecord,
  RecruitmentInterviewRecord,
  RecruitmentOfferRecord,
  RecruitmentOfferAction,
  RecruitmentWorkspaceData,
  ScheduleRecruitmentInterviewInput,
  SubmitRecruitmentInterviewFeedbackInput,
} from '../types'

const workspaceQueryScope = 'recruitment-workspace'
const candidateDetailQueryScope = 'recruitment-candidate-detail'

function delay(ms: number) {
  return new Promise((resolve) => {
    window.setTimeout(resolve, ms)
  })
}

function friendlyActionError(error: unknown) {
  if (error instanceof ApiRequestError) {
    const firstFieldError = Object.values(error.fieldErrors)[0]?.[0]
    return firstFieldError ?? error.message
  }

  if (error instanceof Error) {
    return error.message
  }

  return 'The recruitment action could not be completed right now.'
}

export function useRecruitmentWorkspace() {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = snapshot?.user.permissions ?? []
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const demoStateKey = `${snapshot?.user.id ?? 'anonymous'}`
  const [demoStates, setDemoStates] = useState<Record<string, RecruitmentWorkspaceData>>({})
  const [pendingActionLabel, setPendingActionLabel] = useState<string | null>(null)
  const [lastActionMessage, setLastActionMessage] = useState<string | null>(null)
  const [actionError, setActionError] = useState<string | null>(null)

  const demoData = demoStates[demoStateKey] ?? buildDemoRecruitmentWorkspace(snapshot)

  const liveQuery = useQuery({
    queryKey: [workspaceQueryScope, access.apiBaseUrl, access.token],
    queryFn: () => fetchRecruitmentWorkspace(access.apiBaseUrl, access.token),
    enabled: liveEnabled,
  })

  const data = source === 'demo' ? demoData : liveQuery.data ?? null

  const canViewRecruitment = permissions.some((permission) =>
    ['recruitment.view', 'recruitment.manage', 'recruitment.approve'].includes(permission),
  )
  const canManageRecruitment = permissions.includes('recruitment.manage')
  const canApproveRecruitment = permissions.includes('recruitment.approve') || canManageRecruitment
  const canConvertHandoffs = permissions.includes('employee.manage')
  const canSubmitInterviewFeedback = permissions.includes('recruitment.interview') || canManageRecruitment

  const refreshLiveQueries = useCallback(async () => {
    await Promise.all([
      queryClient.invalidateQueries({ queryKey: [workspaceQueryScope, access.apiBaseUrl, access.token] }),
      queryClient.invalidateQueries({ queryKey: [candidateDetailQueryScope, access.apiBaseUrl, access.token] }),
    ])
  }, [access.apiBaseUrl, access.token, queryClient])

  const mutateDemoState = useCallback(
    async (mutator: (current: RecruitmentWorkspaceData) => RecruitmentWorkspaceData) => {
      await delay(160)
      setDemoStates((current) => {
        const base = current[demoStateKey] ?? buildDemoRecruitmentWorkspace(snapshot)
        return {
          ...current,
          [demoStateKey]: mutator(base),
        }
      })
    },
    [demoStateKey, snapshot],
  )

  const runAction = useCallback(
    async (label: string, message: string, action: () => Promise<void>) => {
      setPendingActionLabel(label)
      setActionError(null)
      setLastActionMessage(null)

      try {
        await action()
        setLastActionMessage(message)
      } catch (error) {
        setActionError(friendlyActionError(error))
        throw error
      } finally {
        setPendingActionLabel(null)
      }
    },
    [],
  )

  const updateRequisitionStatus = useCallback(
    async (requisitionId: number, action: string, comment?: string) => {
      await runAction('Updating requisition', 'Requisition posture updated.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => applyDemoRequisitionAction(current, requisitionId, action, comment))
          return
        }

        await updateRecruitmentRequisition(access.apiBaseUrl, access.token, requisitionId, {
          action,
          comment,
        })
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const moveCandidateStage = useCallback(
    async (candidateId: number, toStage: string, comment?: string) => {
      await runAction('Moving candidate', 'Candidate stage updated.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => applyDemoCandidateStageAction(current, candidateId, toStage, comment))
          return
        }

        await transitionRecruitmentCandidateStage(access.apiBaseUrl, access.token, candidateId, {
          to_stage: toStage,
          comment,
        })
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const createInterview = useCallback(
    async (payload: ScheduleRecruitmentInterviewInput) => {
      await runAction('Scheduling interview', 'Interview scheduled successfully.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => applyDemoInterviewCreate(current, payload))
          return
        }

        await scheduleRecruitmentInterview(access.apiBaseUrl, access.token, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const cancelInterviewAction = useCallback(
    async (interviewId: number, comment: string) => {
      await runAction('Cancelling interview', 'Interview cancelled.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => applyDemoInterviewCancel(current, interviewId, comment))
          return
        }

        await cancelRecruitmentInterview(access.apiBaseUrl, access.token, interviewId, comment)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const submitInterviewFeedbackAction = useCallback(
    async (interviewId: number, payload: SubmitRecruitmentInterviewFeedbackInput) => {
      await runAction('Submitting feedback', 'Interview scorecard saved.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => applyDemoInterviewFeedback(current, interviewId, payload, snapshot?.user.id ?? 0))
          return
        }

        await submitRecruitmentInterviewFeedback(access.apiBaseUrl, access.token, interviewId, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, snapshot?.user.id, source],
  )

  const createOfferAction = useCallback(
    async (payload: CreateRecruitmentOfferInput) => {
      await runAction('Creating offer', 'Offer created successfully.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => applyDemoOfferCreate(current, payload, snapshot?.user.id ?? 0))
          return
        }

        await createRecruitmentOffer(access.apiBaseUrl, access.token, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, snapshot?.user.id, source],
  )

  const updateOfferStatus = useCallback(
    async (offerId: number, action: RecruitmentOfferAction, comment?: string) => {
      await runAction('Updating offer', 'Offer posture updated.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => applyDemoOfferAction(current, offerId, action, comment))
          return
        }

        await updateRecruitmentOffer(access.apiBaseUrl, access.token, offerId, { action, comment })
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, source],
  )

  const createHandoffAction = useCallback(
    async (offerId: number, payload: CreateRecruitmentHandoffInput) => {
      await runAction('Creating handoff', 'Hire handoff created successfully.', async () => {
        if (source === 'demo') {
          await mutateDemoState((current) => applyDemoHandoffCreate(current, offerId, payload, snapshot?.user.id ?? 0))
          return
        }

        await createRecruitmentHandoff(access.apiBaseUrl, access.token, offerId, payload)
        await refreshLiveQueries()
      })
    },
    [access.apiBaseUrl, access.token, mutateDemoState, refreshLiveQueries, runAction, snapshot?.user.id, source],
  )

  const downloadResumeAction = useCallback(
    async (candidateId: number, resumeId: number, fileName: string, downloadPath: string) => {
      setActionError(null)
      setLastActionMessage(null)

      if (source === 'demo') {
        const fileContents = [
          `Demo resume artifact`,
          `Candidate ID: ${candidateId}`,
          `Resume ID: ${resumeId}`,
          `File: ${fileName}`,
          '',
          'This is a demo-mode artifact generated by the recruitment workspace.',
        ].join('\n')
        const blob = new Blob([fileContents], { type: 'text/plain;charset=utf-8' })
        const objectUrl = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = objectUrl
        link.download = fileName.replace(/\.pdf$/i, '.txt')
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(objectUrl)
        setLastActionMessage('Demo resume artifact generated.')
        return
      }

      try {
        await downloadRecruitmentResume(access.apiBaseUrl, access.token, downloadPath, fileName)
        setLastActionMessage('Resume download started.')
      } catch (error) {
        setActionError(friendlyActionError(error))
        throw error
      }
    },
    [access.apiBaseUrl, access.token, source],
  )

  const clearFeedback = useCallback(() => {
    setActionError(null)
    setLastActionMessage(null)
  }, [])

  return {
    source,
    snapshot,
    data,
    isLoading: source === 'live' ? liveQuery.isLoading : false,
    error: source === 'live' ? (liveQuery.error as Error | null) ?? null : null,
    pendingActionLabel,
    lastActionMessage,
    actionError,
    clearFeedback,
    canViewRecruitment,
    canManageRecruitment,
    canApproveRecruitment,
    canConvertHandoffs,
    canSubmitInterviewFeedback,
    actions: {
      updateRequisitionStatus,
      moveCandidateStage,
      createInterview,
      cancelInterview: cancelInterviewAction,
      submitInterviewFeedback: submitInterviewFeedbackAction,
      createOffer: createOfferAction,
      updateOfferStatus,
      createHandoff: createHandoffAction,
      downloadResume: downloadResumeAction,
    },
  }
}

export function useRecruitmentCandidateDetail(candidateId: number | null) {
  const access = useAppSelector((state) => state.access)
  const { snapshot, source } = useAccessSnapshot()
  const demoWorkspace = useMemo(() => buildDemoRecruitmentWorkspace(snapshot), [snapshot])
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0 && candidateId !== null

  const detailQuery = useQuery({
    queryKey: [candidateDetailQueryScope, access.apiBaseUrl, access.token, candidateId],
    queryFn: () => fetchRecruitmentCandidateDetail(access.apiBaseUrl, access.token, candidateId as number),
    enabled: liveEnabled,
  })

  const demoCandidate = candidateId === null ? null : demoWorkspace.candidates.find((candidate) => candidate.id === candidateId) ?? null

  return {
    source,
    candidate: source === 'demo' ? demoCandidate : detailQuery.data ?? null,
    isLoading: source === 'live' ? detailQuery.isLoading : false,
    error: source === 'live' ? (detailQuery.error as Error | null) ?? null : null,
  }
}

function applyDemoRequisitionAction(
  current: RecruitmentWorkspaceData,
  requisitionId: number,
  action: string,
  comment?: string,
) {
  const requisitions = current.requisitions.map((requisition) => {
    if (requisition.id !== requisitionId) {
      return requisition
    }

    const next = { ...requisition }

    if (action === 'submit') {
      next.status = 'submitted'
      next.submitted_at = new Date().toISOString()
      next.can_submit = false
      next.can_edit_details = false
    }

    if (action === 'approve') {
      next.status = 'approved'
      next.approved_at = new Date().toISOString()
      next.can_put_on_hold = true
    }

    if (action === 'reject') {
      next.status = 'rejected'
      next.notes = comment ?? next.notes
      next.can_edit_details = true
      next.can_submit = true
    }

    if (action === 'request_changes') {
      next.status = 'changes_requested'
      next.notes = comment ?? next.notes
      next.can_edit_details = true
      next.can_submit = true
    }

    if (action === 'put_on_hold') {
      next.status_before_hold = requisition.status
      next.status = 'on_hold'
      next.on_hold_at = new Date().toISOString()
      next.can_resume = true
      next.can_put_on_hold = false
      next.can_edit_details = true
    }

    if (action === 'resume') {
      next.status = requisition.status_before_hold ?? 'approved'
      next.status_before_hold = null
      next.can_resume = false
      next.can_put_on_hold = true
      next.on_hold_at = null
    }

    if (action === 'close') {
      next.status = 'closed'
      next.closed_at = new Date().toISOString()
      next.closed_reason = comment ?? 'Closed from recruiter workspace.'
      next.can_close = false
    }

    next.updated_at = new Date().toISOString()
    return next
  })

  return { ...current, requisitions }
}

function applyDemoCandidateStageAction(
  current: RecruitmentWorkspaceData,
  candidateId: number,
  toStage: string,
  comment?: string,
) {
  const transitionedAt = new Date().toISOString()
  const resultingStatus =
    toStage === 'hired'
      ? ('hired' as RecruitmentCandidateStatus)
      : toStage === 'rejected'
        ? ('rejected' as RecruitmentCandidateStatus)
        : toStage === 'withdrawn'
          ? ('withdrawn' as RecruitmentCandidateStatus)
          : ('active' as RecruitmentCandidateStatus)

  const candidates = current.candidates.map((candidate) => {
    if (candidate.id !== candidateId) {
      return candidate
    }

    const transition = {
      id: Date.now(),
      from_stage: candidate.current_stage,
      to_stage: toStage as RecruitmentCandidateRecord['current_stage'],
      resulting_status: resultingStatus,
      comment: comment ?? null,
      transitioned_by: candidate.recruiter,
      transitioned_at: transitionedAt,
    }

    return {
      ...candidate,
      current_stage: toStage as RecruitmentCandidateRecord['current_stage'],
      status: resultingStatus,
      stage_entered_at: transitionedAt,
      updated_at: transitionedAt,
      stage_history: [...(candidate.stage_history ?? []), transition],
    }
  })

  return {
    ...current,
    candidates,
  }
}

function applyDemoInterviewCreate(current: RecruitmentWorkspaceData, payload: ScheduleRecruitmentInterviewInput) {
  const candidate = current.candidates.find((record) => record.id === payload.candidate_id) ?? null
  const requisition = current.requisitions.find((record) => record.id === payload.job_requisition_id) ?? null
  const interviewer = current.directory.interviewers.find((record) => record.id === payload.interviewer_user_id) ?? null
  const nextId = Math.max(700, ...current.interviews.map((record) => record.id)) + 1
  const interview: RecruitmentInterviewRecord = {
    id: nextId,
    interview_code: `INT-${nextId}`,
    round_number: payload.round_number,
    interview_type: payload.interview_type,
    status: 'scheduled',
    timezone: payload.timezone,
    scheduled_start_at: payload.scheduled_start_at,
    scheduled_end_at: payload.scheduled_end_at,
    meeting_mode: payload.meeting_mode,
    meeting_location: payload.meeting_location ?? null,
    meeting_link: payload.meeting_link ?? null,
    agenda: payload.agenda ?? null,
    cancellation_reason: null,
    candidate,
    requisition: requisition
      ? { id: requisition.id, requisition_code: requisition.requisition_code, title: requisition.title, status: requisition.status }
      : null,
    interviewer,
    feedback: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  }

  return {
    ...applyDemoCandidateStageAction(current, payload.candidate_id, 'interview', 'Interview scheduled from workspace.'),
    interviews: [...current.interviews, interview].sort((left, right) =>
      `${right.scheduled_start_at ?? ''}`.localeCompare(`${left.scheduled_start_at ?? ''}`),
    ),
  }
}

function applyDemoInterviewCancel(current: RecruitmentWorkspaceData, interviewId: number, comment: string) {
  return {
    ...current,
    interviews: current.interviews.map((interview) =>
      interview.id === interviewId
        ? {
            ...interview,
            status: 'cancelled' as const,
            cancellation_reason: comment,
            updated_at: new Date().toISOString(),
          }
        : interview,
    ),
  }
}

function applyDemoInterviewFeedback(
  current: RecruitmentWorkspaceData,
  interviewId: number,
  payload: SubmitRecruitmentInterviewFeedbackInput,
  actorUserId: number,
) {
  const interviewer =
    current.directory.interviewers.find((record) => record.id === actorUserId) ??
    current.interviews.find((record) => record.id === interviewId)?.interviewer ??
    null

  return {
    ...current,
    interviews: current.interviews.map((interview) =>
      interview.id === interviewId
        ? {
            ...interview,
            status: 'completed' as const,
            feedback: {
              id: Date.now(),
              interviewer,
              created_at: new Date().toISOString(),
              rating: payload.rating,
              recommendation: payload.recommendation,
              comments: payload.comments,
              strengths: payload.strengths ?? null,
              concerns: payload.concerns ?? null,
            },
            updated_at: new Date().toISOString(),
          }
        : interview,
    ),
  }
}

function applyDemoOfferCreate(
  current: RecruitmentWorkspaceData,
  payload: CreateRecruitmentOfferInput,
  actorUserId: number,
) {
  const candidate = current.candidates.find((record) => record.id === payload.candidate_id) ?? null
  const requisition = current.requisitions.find((record) => record.id === payload.job_requisition_id) ?? null
  const recruiter =
    current.directory.recruiters.find((record) => record.id === (payload.recruiter_user_id ?? actorUserId)) ??
    candidate?.recruiter ??
    null
  const nextId = Math.max(500, ...current.offers.map((record) => record.id)) + 1
  const createdAt = new Date().toISOString()
  const offer: RecruitmentOfferRecord = {
    id: nextId,
    offer_code: `OFF-${nextId}`,
    status: 'draft',
    employment_type: payload.employment_type,
    currency: payload.currency,
    annual_ctc_amount: payload.annual_ctc_amount,
    joining_bonus_amount: payload.joining_bonus_amount ?? null,
    proposed_start_date: payload.proposed_start_date ?? null,
    expires_on: payload.expires_on,
    notes: payload.notes ?? null,
    candidate_message: payload.candidate_message ?? null,
    candidate,
    requisition: requisition
      ? { id: requisition.id, requisition_code: requisition.requisition_code, title: requisition.title, status: requisition.status }
      : null,
    recruiter,
    requested_by: recruiter,
    workflow_instance_id: null,
    workflow: null,
    hire_handoff: null,
    decision_history: [],
    submitted_at: null,
    approved_at: null,
    sent_at: null,
    accepted_at: null,
    declined_at: null,
    expired_at: null,
    created_at: createdAt,
    updated_at: createdAt,
  }

  return {
    ...applyDemoCandidateStageAction(current, payload.candidate_id, 'offer', 'Offer drafted from workspace.'),
    offers: [offer, ...current.offers],
  }
}

function applyDemoOfferAction(
  current: RecruitmentWorkspaceData,
  offerId: number,
  action: RecruitmentOfferAction,
  comment?: string,
) {
  const actedAt = new Date().toISOString()
  const offers = current.offers.map((offer) => {
    if (offer.id !== offerId) {
      return offer
    }

    const next: RecruitmentOfferRecord = { ...offer }
    const previousStatus = offer.status

    switch (action) {
      case 'submit':
        next.status = 'submitted'
        next.submitted_at = actedAt
        break
      case 'approve':
        next.status = 'approved'
        next.approved_at = actedAt
        break
      case 'reject':
        next.status = 'rejected'
        break
      case 'request_changes':
        next.status = 'changes_requested'
        break
      case 'mark_sent':
        next.status = 'sent'
        next.sent_at = actedAt
        break
      case 'record_acceptance':
        next.status = 'accepted'
        next.accepted_at = actedAt
        break
      case 'record_decline':
        next.status = 'declined'
        next.declined_at = actedAt
        break
      case 'mark_expired':
        next.status = 'expired'
        next.expired_at = actedAt
        break
      default:
        break
    }

    next.updated_at = actedAt
    next.decision_history = [
      ...(offer.decision_history ?? []),
      {
        id: Date.now(),
        from_status: previousStatus,
        to_status: next.status,
        decision_type: action,
        comment: comment ?? null,
        actor: offer.recruiter,
        acted_at: actedAt,
      },
    ]

    return next
  })

  const targetOffer = offers.find((offer) => offer.id === offerId)
  if (!targetOffer?.candidate?.id) {
    return { ...current, offers }
  }

  const nextStage =
    action === 'record_acceptance'
      ? 'hired'
      : action === 'mark_sent' || action === 'approve' || action === 'submit'
        ? 'offer'
        : null

  return nextStage
    ? {
        ...applyDemoCandidateStageAction(current, targetOffer.candidate.id, nextStage, comment),
        offers,
      }
    : {
        ...current,
        offers,
      }
}

function applyDemoHandoffCreate(
  current: RecruitmentWorkspaceData,
  offerId: number,
  payload: CreateRecruitmentHandoffInput,
  actorUserId: number,
) {
  const offer = current.offers.find((record) => record.id === offerId)

  if (!offer || !offer.candidate || !offer.requisition) {
    return current
  }

  const handoffId = Math.max(600, ...current.handoffs.map((record) => record.id)) + 1
  const employeeId = 1300 + handoffId
  const createdAt = new Date().toISOString()
  const employee: RecruitmentEmployeeReference = {
    id: employeeId,
    employee_code: `EMP-${employeeId}`,
    full_name: offer.candidate.full_name,
    email: offer.candidate.email,
  }
  const recruiter =
    current.directory.recruiters.find((record) => record.id === actorUserId) ??
    offer.recruiter ??
    null
  const handoff: RecruitmentHireHandoffRecord = {
    id: handoffId,
    status: payload.trigger_onboarding === false ? 'onboarding_skipped' : 'onboarding_queued',
    offer: {
      id: offer.id,
      offer_code: offer.offer_code,
      status: offer.status,
      employment_type: offer.employment_type,
      proposed_start_date: offer.proposed_start_date,
      expires_on: offer.expires_on,
    },
    candidate: {
      ...offer.candidate,
      current_stage: 'hired',
      status: 'hired',
      stage_entered_at: createdAt,
      updated_at: createdAt,
      stage_history: [
        ...(offer.candidate.stage_history ?? []),
        {
          id: Date.now(),
          from_stage: offer.candidate.current_stage,
          to_stage: 'hired',
          resulting_status: 'hired',
          comment: 'Accepted offer converted into employee handoff.',
          transitioned_by: recruiter,
          transitioned_at: createdAt,
        },
      ],
    },
    requisition: offer.requisition,
    employee,
    recruiter,
    converted_by: recruiter,
    source_resume: offer.candidate.latest_resume
      ? {
          id: offer.candidate.latest_resume.id,
          version_number: offer.candidate.latest_resume.version_number,
          original_file_name: offer.candidate.latest_resume.original_file_name,
        }
      : null,
    offer_snapshot: { offer_code: offer.offer_code, annual_ctc_amount: offer.annual_ctc_amount },
    candidate_snapshot: { full_name: offer.candidate.full_name, email: offer.candidate.email },
    requisition_snapshot: { requisition_code: offer.requisition.requisition_code, title: offer.requisition.title },
    document_references: offer.candidate.latest_resume
      ? [{ type: 'resume', id: offer.candidate.latest_resume.id, name: offer.candidate.latest_resume.original_file_name }]
      : [],
    onboarding_template_ids: payload.trigger_onboarding === false ? [] : [9001, 9002],
    onboarding_task_ids: payload.trigger_onboarding === false ? [] : [Date.now(), Date.now() + 1],
    notes: payload.notes ?? null,
    converted_at: createdAt,
    onboarding_triggered_at: payload.trigger_onboarding === false ? null : createdAt,
    created_at: createdAt,
    updated_at: createdAt,
  }

  return {
    ...applyDemoCandidateStageAction(current, offer.candidate.id, 'hired', 'Accepted offer converted to hire handoff.'),
    offers: current.offers.map((record) =>
      record.id === offerId
        ? {
            ...record,
            status: 'accepted' as const,
            hire_handoff: {
              id: handoff.id,
              status: handoff.status,
              employee,
              converted_at: handoff.converted_at,
              onboarding_triggered_at: handoff.onboarding_triggered_at,
            },
            updated_at: createdAt,
          }
        : record,
    ),
    handoffs: [handoff, ...current.handoffs],
  }
}
