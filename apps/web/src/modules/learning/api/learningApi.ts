import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import type { PaginatedEmployees } from '../../employees/types'
import type { OrganizationMasterRecord } from '../../organization/types'
import type {
  CompleteLearningTargetInput,
  CreateLearningAssignmentInput,
  CreateLearningItemInput,
  LearningAssignmentRecord,
  LearningAssignmentTargetRecord,
  LearningItemRecord,
  LearningWorkspaceData,
  UpdateLearningItemInput,
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

function uniqueItems(items: Array<LearningItemRecord | null | undefined>) {
  const unique = new Map<number, LearningItemRecord>()

  items.forEach((item) => {
    if (item) {
      unique.set(item.id, item)
    }
  })

  return [...unique.values()]
}

function buildWorkspaceMeta(options: FetchLearningWorkspaceOptions) {
  return {
    can_view_learning: options.canViewLearning,
    can_manage_catalog: options.canManageCatalog,
    can_assign_learning: options.canAssignLearning,
    can_complete_learning: options.canCompleteLearning,
    linked_employee_id: options.linkedEmployeeId,
  }
}

export interface FetchLearningWorkspaceOptions {
  canViewLearning: boolean
  canManageCatalog: boolean
  canAssignLearning: boolean
  canCompleteLearning: boolean
  linkedEmployeeId: number | null
}

export async function fetchLearningWorkspace(
  apiBaseUrl: string,
  token: string,
  options: FetchLearningWorkspaceOptions,
): Promise<LearningWorkspaceData> {
  const canAdmin = options.canManageCatalog || options.canAssignLearning
  const shouldFetchAssignments = options.canViewLearning || canAdmin
  const shouldFetchTargets = options.canViewLearning || canAdmin
  const shouldFetchMyAssignments =
    options.linkedEmployeeId !== null && (options.canCompleteLearning || options.canViewLearning || canAdmin)

  const [
    itemsResponse,
    assignmentsResponse,
    targetsResponse,
    myAssignmentsResponse,
    employeesResponse,
    departmentsResponse,
    designationsResponse,
  ] = await Promise.all([
    canAdmin
      ? requestJson<{ items: LearningItemRecord[] }>(buildQuery(`${apiBaseUrl}/learning/items`, { per_page: 100 }), token)
      : Promise.resolve(null),
    shouldFetchAssignments
      ? requestJson<{ items: LearningAssignmentRecord[] }>(
          buildQuery(`${apiBaseUrl}/learning/assignments`, { per_page: 100 }),
          token,
        )
      : Promise.resolve(null),
    shouldFetchTargets
      ? requestJson<{ items: LearningAssignmentTargetRecord[] }>(
          buildQuery(`${apiBaseUrl}/learning/targets`, { per_page: 100 }),
          token,
        )
      : Promise.resolve(null),
    shouldFetchMyAssignments
      ? requestJson<{ items: LearningAssignmentTargetRecord[] }>(
          buildQuery(`${apiBaseUrl}/learning/my-assignments`, { per_page: 100 }),
          token,
        )
      : Promise.resolve(null),
    canAdmin
      ? requestJson<PaginatedEmployees>(buildQuery(`${apiBaseUrl}/employees`, { per_page: 100 }), token)
      : Promise.resolve(null),
    canAdmin
      ? requestJson<OrganizationMasterRecord[]>(`${apiBaseUrl}/organization/departments`, token)
      : Promise.resolve(null),
    canAdmin
      ? requestJson<OrganizationMasterRecord[]>(`${apiBaseUrl}/organization/designations`, token)
      : Promise.resolve(null),
  ])

  const assignments = assignmentsResponse?.items ?? []
  const targets = targetsResponse?.items ?? []
  const myAssignments = myAssignmentsResponse?.items ?? []
  const items = itemsResponse?.items ?? uniqueItems([
    ...assignments.map((assignment) => assignment.item),
    ...targets.map((target) => target.item),
    ...myAssignments.map((target) => target.item),
  ])

  return {
    items,
    assignments,
    targets,
    myAssignments,
    employees: employeesResponse?.items ?? [],
    departments: departmentsResponse ?? [],
    designations: designationsResponse ?? [],
    meta: buildWorkspaceMeta(options),
  }
}

export function createLearningItem(apiBaseUrl: string, token: string, payload: CreateLearningItemInput) {
  return requestJson<LearningItemRecord>(`${apiBaseUrl}/learning/items`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function updateLearningItem(
  apiBaseUrl: string,
  token: string,
  learningItemId: number,
  payload: UpdateLearningItemInput,
) {
  return requestJson<LearningItemRecord>(`${apiBaseUrl}/learning/items/${learningItemId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}

export function createLearningAssignment(
  apiBaseUrl: string,
  token: string,
  payload: CreateLearningAssignmentInput,
) {
  return requestJson<LearningAssignmentRecord>(`${apiBaseUrl}/learning/assignments`, token, {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}

export function completeLearningTarget(
  apiBaseUrl: string,
  token: string,
  targetId: number,
  payload: CompleteLearningTargetInput,
) {
  return requestJson<LearningAssignmentTargetRecord>(`${apiBaseUrl}/learning/targets/${targetId}/complete`, token, {
    method: 'PATCH',
    body: JSON.stringify(payload),
  })
}
