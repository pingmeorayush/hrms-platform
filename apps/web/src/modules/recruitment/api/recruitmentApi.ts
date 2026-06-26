import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import type {
  CreateRecruitmentHandoffInput,
  CreateRecruitmentOfferInput,
  RecruitmentCandidateRecord,
  RecruitmentHireHandoffRecord,
  RecruitmentInterviewRecord,
  RecruitmentJobRequisitionRecord,
  RecruitmentOfferRecord,
  RecruitmentWorkspaceData,
  ScheduleRecruitmentInterviewInput,
  SubmitRecruitmentInterviewFeedbackInput,
  UpdateRecruitmentOfferInput,
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

function buildQuery(url: string, filters: Record<string, string | number | null | undefined>) {
  const searchParams = new URLSearchParams()

  Object.entries(filters).forEach(([key, value]) => {
    if (value !== null && value !== undefined && `${value}`.length > 0) {
      searchParams.set(key, String(value))
    }
  })

  return `${url}?${searchParams.toString()}`
}

function resolveApiUrl(apiBaseUrl: string, path: string) {
  return new URL(path, apiBaseUrl).toString()
}

export async function fetchRecruitmentWorkspace(apiBaseUrl: string, token: string): Promise<RecruitmentWorkspaceData> {
  const requisitionsUrl = buildQuery(`${apiBaseUrl}/recruitment/requisitions`, { per_page: 100 })
  const candidatesUrl = buildQuery(`${apiBaseUrl}/recruitment/candidates`, { per_page: 100 })
  const interviewsUrl = buildQuery(`${apiBaseUrl}/recruitment/interviews`, { per_page: 100 })
  const offersUrl = buildQuery(`${apiBaseUrl}/recruitment/offers`, { per_page: 100 })
  const handoffsUrl = buildQuery(`${apiBaseUrl}/recruitment/handoffs`, { per_page: 100 })

  const [requisitions, candidates, interviews, offers, handoffs] = await Promise.all([
    requestJson<{ items: RecruitmentJobRequisitionRecord[] }>(requisitionsUrl, token),
    requestJson<{ items: RecruitmentCandidateRecord[] }>(candidatesUrl, token),
    requestJson<{ items: RecruitmentInterviewRecord[] }>(interviewsUrl, token),
    requestJson<{ items: RecruitmentOfferRecord[] }>(offersUrl, token),
    requestJson<{ items: RecruitmentHireHandoffRecord[] }>(handoffsUrl, token),
  ])

  const recruiterMap = new Map<number, RecruitmentWorkspaceData['directory']['recruiters'][number]>()
  const interviewerMap = new Map<number, RecruitmentWorkspaceData['directory']['interviewers'][number]>()
  const hiringManagerMap = new Map<number, RecruitmentWorkspaceData['directory']['hiring_managers'][number]>()

  requisitions.items.forEach((requisition) => {
    if (requisition.recruiter) {
      recruiterMap.set(requisition.recruiter.id, requisition.recruiter)
    }

    if (requisition.hiring_manager) {
      hiringManagerMap.set(requisition.hiring_manager.id, requisition.hiring_manager)
    }
  })

  candidates.items.forEach((candidate) => {
    if (candidate.recruiter) {
      recruiterMap.set(candidate.recruiter.id, candidate.recruiter)
    }
  })

  interviews.items.forEach((interview) => {
    if (interview.interviewer) {
      interviewerMap.set(interview.interviewer.id, interview.interviewer)
    }
  })

  offers.items.forEach((offer) => {
    if (offer.recruiter) {
      recruiterMap.set(offer.recruiter.id, offer.recruiter)
    }
  })

  return {
    requisitions: requisitions.items,
    candidates: candidates.items,
    interviews: interviews.items,
    offers: offers.items,
    handoffs: handoffs.items,
    directory: {
      recruiters: [...recruiterMap.values()].sort((left, right) => left.name.localeCompare(right.name)),
      interviewers: [...interviewerMap.values()].sort((left, right) => left.name.localeCompare(right.name)),
      hiring_managers: [...hiringManagerMap.values()].sort((left, right) => left.full_name.localeCompare(right.full_name)),
    },
  }
}

export function fetchRecruitmentCandidateDetail(apiBaseUrl: string, token: string, candidateId: number) {
  return requestJson<RecruitmentCandidateRecord>(`${apiBaseUrl}/recruitment/candidates/${candidateId}`, token)
}

export function updateRecruitmentRequisition(
  apiBaseUrl: string,
  token: string,
  requisitionId: number,
  payload: { action: string; comment?: string | null },
) {
  return requestJson<RecruitmentJobRequisitionRecord>(`${apiBaseUrl}/recruitment/requisitions/${requisitionId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}

export function transitionRecruitmentCandidateStage(
  apiBaseUrl: string,
  token: string,
  candidateId: number,
  payload: { to_stage: string; comment?: string | null },
) {
  return requestJson<RecruitmentCandidateRecord>(
    `${apiBaseUrl}/recruitment/candidates/${candidateId}/stage-transitions`,
    token,
    {
      method: 'POST',
      body: JSON.stringify(payload),
    },
  )
}

export function scheduleRecruitmentInterview(
  apiBaseUrl: string,
  token: string,
  payload: ScheduleRecruitmentInterviewInput,
) {
  return requestJson<RecruitmentInterviewRecord>(`${apiBaseUrl}/recruitment/interviews`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function cancelRecruitmentInterview(
  apiBaseUrl: string,
  token: string,
  interviewId: number,
  comment: string,
) {
  return requestJson<RecruitmentInterviewRecord>(`${apiBaseUrl}/recruitment/interviews/${interviewId}`, token, {
    method: 'PATCH',
    body: JSON.stringify({ action: 'cancel', comment }),
  })
}

export function submitRecruitmentInterviewFeedback(
  apiBaseUrl: string,
  token: string,
  interviewId: number,
  payload: SubmitRecruitmentInterviewFeedbackInput,
) {
  return requestJson<RecruitmentInterviewRecord>(
    `${apiBaseUrl}/recruitment/interviews/${interviewId}/feedback`,
    token,
    {
      method: 'POST',
      body: JSON.stringify(payload),
    },
  )
}

export function createRecruitmentOffer(
  apiBaseUrl: string,
  token: string,
  payload: CreateRecruitmentOfferInput,
) {
  return requestJson<RecruitmentOfferRecord>(`${apiBaseUrl}/recruitment/offers`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function updateRecruitmentOffer(
  apiBaseUrl: string,
  token: string,
  offerId: number,
  payload: UpdateRecruitmentOfferInput,
) {
  return requestJson<RecruitmentOfferRecord>(`${apiBaseUrl}/recruitment/offers/${offerId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}

export function createRecruitmentHandoff(
  apiBaseUrl: string,
  token: string,
  offerId: number,
  payload: CreateRecruitmentHandoffInput,
) {
  return requestJson<RecruitmentHireHandoffRecord>(`${apiBaseUrl}/recruitment/offers/${offerId}/handoff`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export async function downloadRecruitmentResume(
  apiBaseUrl: string,
  token: string,
  downloadPath: string,
  fallbackFileName: string,
) {
  const response = await fetch(resolveApiUrl(apiBaseUrl, downloadPath), {
    headers: {
      Accept: 'application/pdf,application/octet-stream',
      Authorization: `Bearer ${token}`,
    },
  })

  if (!response.ok) {
    throw new Error('Unable to download the selected resume right now.')
  }

  const blob = await response.blob()
  const objectUrl = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = objectUrl
  link.download = fallbackFileName
  document.body.appendChild(link)
  link.click()
  link.remove()
  window.URL.revokeObjectURL(objectUrl)
}
