import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import { fetchEmployeeDirectory } from '../../employees/api/employeesApi'
import type {
  AssetAssignmentFormValues,
  AssetCategoryFormValues,
  AssetFormValues,
  AssetIssueFormValues,
  AssetReturnFormValues,
  DocumentCategoryFormValues,
  OperationsDocumentCategoryRecord,
  OperationsDocumentRecord,
  OperationsAssetCategoryRecord,
  OperationsAssetRecord,
  OperationsLifecycleTaskCollection,
  OperationsLifecycleType,
  OperationsLifecycleStatusRecord,
  OperationsWorkspaceData,
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

export async function fetchOperationsWorkspace(apiBaseUrl: string, token: string): Promise<OperationsWorkspaceData> {
  const [documentCategories, documents, assetCategories, assets, onboarding, offboarding, employees] = await Promise.all([
    requestJson<OperationsDocumentCategoryRecord[]>(`${apiBaseUrl}/documents/categories`, token),
    requestJson<OperationsDocumentRecord[]>(`${apiBaseUrl}/documents`, token),
    requestJson<OperationsAssetCategoryRecord[]>(`${apiBaseUrl}/assets/categories`, token),
    requestJson<OperationsAssetRecord[]>(`${apiBaseUrl}/assets`, token),
    requestJson<OperationsLifecycleStatusRecord[]>(
      `${apiBaseUrl}/employees/lifecycle-task-status?lifecycle_type=onboarding`,
      token,
    ),
    requestJson<OperationsLifecycleStatusRecord[]>(
      `${apiBaseUrl}/employees/lifecycle-task-status?lifecycle_type=offboarding`,
      token,
    ),
    fetchEmployeeDirectory(apiBaseUrl, token, {
      search: '',
      employmentStatus: '',
      departmentId: '',
      designationId: '',
      managerId: '',
      page: 1,
      perPage: 100,
    }),
  ])

  return {
    documentCategories,
    documents,
    assetCategories,
    assets,
    employees: employees.items,
    lifecycle: {
      onboarding,
      offboarding,
    },
  }
}

export function fetchEmployeeLifecycleTasks(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  lifecycleType: OperationsLifecycleType,
) {
  return requestJson<OperationsLifecycleTaskCollection>(
    `${apiBaseUrl}/employees/${employeeId}/lifecycle-tasks?lifecycle_type=${lifecycleType}`,
    token,
  )
}

export function createDocumentCategory(
  apiBaseUrl: string,
  token: string,
  payload: DocumentCategoryFormValues,
) {
  return requestJson<OperationsDocumentCategoryRecord>(`${apiBaseUrl}/documents/categories`, token, {
    method: 'POST',
    body: JSON.stringify(serializeDocumentCategoryForm(payload)),
  })
}

export function updateDocumentCategory(
  apiBaseUrl: string,
  token: string,
  categoryId: number,
  payload: DocumentCategoryFormValues,
) {
  return requestJson<OperationsDocumentCategoryRecord>(`${apiBaseUrl}/documents/categories/${categoryId}`, token, {
    method: 'PATCH',
    body: JSON.stringify(serializeDocumentCategoryForm(payload)),
  })
}

export function createAssetCategory(
  apiBaseUrl: string,
  token: string,
  payload: AssetCategoryFormValues,
) {
  return requestJson<OperationsAssetCategoryRecord>(`${apiBaseUrl}/assets/categories`, token, {
    method: 'POST',
    body: JSON.stringify({
      code: payload.code.trim(),
      name: payload.name.trim(),
      status: payload.status,
      notes: payload.notes.trim() || null,
    }),
  })
}

export function createAsset(
  apiBaseUrl: string,
  token: string,
  payload: AssetFormValues,
) {
  return requestJson<OperationsAssetRecord>(`${apiBaseUrl}/assets`, token, {
    method: 'POST',
    body: JSON.stringify({
      asset_category_id: Number(payload.asset_category_id),
      asset_tag: payload.asset_tag.trim(),
      name: payload.name.trim(),
      asset_type: payload.asset_type,
      serial_number: payload.serial_number.trim() || null,
      manufacturer: payload.manufacturer.trim() || null,
      model_name: payload.model_name.trim() || null,
      purchase_date: payload.purchase_date || null,
      status: payload.status,
      notes: payload.notes.trim() || null,
    }),
  })
}

export function assignAsset(
  apiBaseUrl: string,
  token: string,
  assetId: number,
  payload: AssetAssignmentFormValues,
) {
  return requestJson<OperationsAssetRecord>(`${apiBaseUrl}/assets/${assetId}/assign`, token, {
    method: 'POST',
    body: JSON.stringify({
      employee_id: Number(payload.employee_id),
      assigned_at: payload.assigned_at || null,
      expected_return_date: payload.expected_return_date || null,
      handover_condition: payload.handover_condition.trim() || null,
      assignment_notes: payload.assignment_notes.trim() || null,
    }),
  })
}

export function issueAsset(
  apiBaseUrl: string,
  token: string,
  assetId: number,
  payload: AssetIssueFormValues,
) {
  return requestJson<OperationsAssetRecord>(`${apiBaseUrl}/assets/${assetId}/issue`, token, {
    method: 'POST',
    body: JSON.stringify({
      issued_at: payload.issued_at || null,
      issue_notes: payload.issue_notes.trim() || null,
    }),
  })
}

export function returnAsset(
  apiBaseUrl: string,
  token: string,
  assetId: number,
  payload: AssetReturnFormValues,
) {
  return requestJson<OperationsAssetRecord>(`${apiBaseUrl}/assets/${assetId}/return`, token, {
    method: 'POST',
    body: JSON.stringify({
      returned_at: payload.returned_at || null,
      return_condition: payload.return_condition.trim() || null,
      return_notes: payload.return_notes.trim() || null,
    }),
  })
}

export function updateLifecycleTaskStatus(
  apiBaseUrl: string,
  token: string,
  employeeId: number,
  taskId: number,
  lifecycleType: OperationsLifecycleType,
  status: string,
  notes: string | null = null,
) {
  return requestJson(`${apiBaseUrl}/employees/${employeeId}/lifecycle-tasks/${taskId}`, token, {
    method: 'PATCH',
    body: JSON.stringify({
      lifecycle_type: lifecycleType,
      status,
      notes,
    }),
  })
}

function serializeDocumentCategoryForm(payload: DocumentCategoryFormValues) {
  return {
    code: payload.code.trim(),
    name: payload.name.trim(),
    repository_scope: payload.repository_scope,
    default_visibility_scope: payload.default_visibility_scope,
    retention_days: payload.retention_days.trim() ? Number(payload.retention_days) : null,
    allowed_role_names: payload.allowed_role_names
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean),
    status: payload.status,
    notes: payload.notes.trim() || null,
  }
}
