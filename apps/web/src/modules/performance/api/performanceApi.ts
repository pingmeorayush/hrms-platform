import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import type { PaginatedEmployees } from '../../employees/types'
import type {
  CalibratePerformanceReviewInput,
  CreatePerformanceCompetencyInput,
  CreatePerformanceGoalInput,
  CreatePerformanceReviewCycleInput,
  CreatePerformanceReviewInput,
  FinalizePerformanceReviewInput,
  PaginatedPerformanceCollection,
  PerformanceCompetencyRecord,
  PerformanceGoalRecord,
  PerformanceReviewCycleRecord,
  PerformanceReviewRecord,
  PerformanceWorkspaceData,
  SubmitPerformanceReviewInput,
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

export async function fetchPerformanceWorkspace(apiBaseUrl: string, token: string): Promise<PerformanceWorkspaceData> {
  const goalsUrl = buildQuery(`${apiBaseUrl}/performance/goals`, { per_page: 100 })
  const competenciesUrl = buildQuery(`${apiBaseUrl}/performance/competencies`, { per_page: 100 })
  const cyclesUrl = buildQuery(`${apiBaseUrl}/performance/review-cycles`, { per_page: 100 })
  const reviewsUrl = buildQuery(`${apiBaseUrl}/performance/reviews`, { per_page: 100 })
  const employeesUrl = buildQuery(`${apiBaseUrl}/employees`, { per_page: 100 })

  const [goals, competencies, reviewCycles, reviews, employees] = await Promise.all([
    requestJson<PaginatedPerformanceCollection<PerformanceGoalRecord>>(goalsUrl, token),
    requestJson<PaginatedPerformanceCollection<PerformanceCompetencyRecord>>(competenciesUrl, token),
    requestJson<PaginatedPerformanceCollection<PerformanceReviewCycleRecord>>(cyclesUrl, token),
    requestJson<PaginatedPerformanceCollection<PerformanceReviewRecord>>(reviewsUrl, token),
    requestJson<PaginatedEmployees>(employeesUrl, token),
  ])

  return {
    goals: goals.items,
    competencies: competencies.items,
    reviewCycles: reviewCycles.items,
    reviews: reviews.items,
    employees: employees.items,
    meta: {
      can_manage: false,
      can_review: false,
      can_calibrate: false,
      linked_employee_id: null,
    },
  }
}

export function createPerformanceGoal(apiBaseUrl: string, token: string, payload: CreatePerformanceGoalInput) {
  return requestJson<PerformanceGoalRecord>(`${apiBaseUrl}/performance/goals`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function createPerformanceCompetency(
  apiBaseUrl: string,
  token: string,
  payload: CreatePerformanceCompetencyInput,
) {
  return requestJson<PerformanceCompetencyRecord>(`${apiBaseUrl}/performance/competencies`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function createPerformanceReviewCycle(
  apiBaseUrl: string,
  token: string,
  payload: CreatePerformanceReviewCycleInput,
) {
  return requestJson<PerformanceReviewCycleRecord>(`${apiBaseUrl}/performance/review-cycles`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function createPerformanceReview(
  apiBaseUrl: string,
  token: string,
  payload: CreatePerformanceReviewInput,
) {
  return requestJson<PerformanceReviewRecord>(`${apiBaseUrl}/performance/reviews`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function submitPerformanceReview(
  apiBaseUrl: string,
  token: string,
  reviewId: number,
  payload: SubmitPerformanceReviewInput,
) {
  return requestJson<PerformanceReviewRecord>(`${apiBaseUrl}/performance/reviews/${reviewId}/submit`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function calibratePerformanceReview(
  apiBaseUrl: string,
  token: string,
  reviewId: number,
  payload: CalibratePerformanceReviewInput,
) {
  return requestJson<PerformanceReviewRecord>(`${apiBaseUrl}/performance/reviews/${reviewId}/calibrate`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function finalizePerformanceReview(
  apiBaseUrl: string,
  token: string,
  reviewId: number,
  payload: FinalizePerformanceReviewInput,
) {
  return requestJson<PerformanceReviewRecord>(`${apiBaseUrl}/performance/reviews/${reviewId}/finalize`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function publishPerformanceReview(apiBaseUrl: string, token: string, reviewId: number) {
  return requestJson<PerformanceReviewRecord>(`${apiBaseUrl}/performance/reviews/${reviewId}/publish`, token, {
    method: 'POST',
    body: JSON.stringify({}),
  })
}

export function reopenPerformanceReview(apiBaseUrl: string, token: string, reviewId: number, reason: string) {
  return requestJson<PerformanceReviewRecord>(`${apiBaseUrl}/performance/reviews/${reviewId}/reopen`, token, {
    method: 'POST',
    body: JSON.stringify({ reason }),
  })
}
