import { buildApiHeaders, readApiJson } from '../../../shared/api/http'
import { fetchEmployeeDirectory } from '../../employees/api/employeesApi'
import type {
  AssetAssignmentFormValues,
  AssetCategoryFormValues,
  AssetFormValues,
  AssetIssueFormValues,
  AssetReturnFormValues,
  DocumentCategoryFormValues,
  IntegrationConnectionFormValues,
  IntegrationDispatchFormValues,
  IntegrationSubscriptionFormValues,
  OperationsDocumentCategoryRecord,
  OperationsDocumentRecord,
  OperationsAssetCategoryRecord,
  OperationsAssetRecord,
  OperationsIntegrationConnectionRecord,
  OperationsIntegrationEventRecord,
  OperationsIntegrationSubscriptionRecord,
  OperationsIntegrationSyncJobRecord,
  OperationsIntegrationSystemRecord,
  OperationsLifecycleTaskCollection,
  OperationsLifecycleType,
  OperationsLifecycleStatusRecord,
  OperationsObservabilityOverviewRecord,
  OperationsPagedMeta,
  OperationsResilienceOverviewRecord,
  OperationsReleaseEnvironmentRecord,
  OperationsReleaseGateRecord,
  OperationsReleaseReadinessDecisionRecord,
  OperationsReleaseReadinessOverviewRecord,
  OperationsReleasePolicyRecord,
  OperationsReleaseSummaryRecord,
  ReleaseReadinessDecisionFormValues,
  ResilienceValidationRunFormValues,
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

interface OperationsCollectionResponse<T> {
  items: T[]
}

interface OperationsSyncJobResponse {
  items: OperationsIntegrationSyncJobRecord[]
  meta: OperationsPagedMeta
}

interface OperationsIntegrationCatalogResponse {
  systems: OperationsIntegrationSystemRecord[]
  events: OperationsIntegrationEventRecord[]
}

interface OperationsReleaseQualityResponse {
  summary: OperationsReleaseSummaryRecord
  policy: OperationsReleasePolicyRecord
  gates: OperationsReleaseGateRecord[]
  environments: OperationsReleaseEnvironmentRecord[]
}

type OperationsReleaseReadinessResponse = OperationsReleaseReadinessOverviewRecord
type OperationsObservabilityOverviewResponse = OperationsObservabilityOverviewRecord
type OperationsResilienceReadinessResponse = OperationsResilienceOverviewRecord

export async function fetchOperationsWorkspace(
  apiBaseUrl: string,
  token: string,
  grantedPermissions: string[],
): Promise<OperationsWorkspaceData> {
  const canViewDocuments = hasAnyPermission(grantedPermissions, ['document.view', 'document.manage'])
  const canViewAssets = hasAnyPermission(grantedPermissions, ['asset.view', 'asset.manage'])
  const canViewEmployees = hasAnyPermission(grantedPermissions, ['employee.view', 'employee.manage'])
  const canManageLifecycle = grantedPermissions.includes('employee.manage')
  const canViewIntegrations = hasAnyPermission(grantedPermissions, ['integration.view', 'integration.manage'])
  const canViewResilience = hasAnyPermission(grantedPermissions, ['resilience.view', 'resilience.manage'])
  const canViewObservability = hasAnyPermission(grantedPermissions, ['observability.view', 'observability.manage'])
  const canViewRelease = hasAnyPermission(grantedPermissions, ['release.view', 'release.manage'])

  const [
    documentCategories,
    documents,
    assetCategories,
    assets,
    onboarding,
    offboarding,
    employees,
    integrationCatalog,
    integrationConnections,
    integrationSubscriptions,
    integrationSyncJobs,
    resilienceReadiness,
    observabilityOverview,
    releaseQuality,
    releaseReadiness,
  ] = await Promise.all([
    canViewDocuments
      ? requestJson<OperationsDocumentCategoryRecord[]>(`${apiBaseUrl}/documents/categories`, token)
      : Promise.resolve([]),
    canViewDocuments
      ? requestJson<OperationsDocumentRecord[]>(`${apiBaseUrl}/documents`, token)
      : Promise.resolve([]),
    canViewAssets
      ? requestJson<OperationsAssetCategoryRecord[]>(`${apiBaseUrl}/assets/categories`, token)
      : Promise.resolve([]),
    canViewAssets
      ? requestJson<OperationsAssetRecord[]>(`${apiBaseUrl}/assets`, token)
      : Promise.resolve([]),
    canManageLifecycle
      ? requestJson<OperationsLifecycleStatusRecord[]>(
          `${apiBaseUrl}/employees/lifecycle-task-status?lifecycle_type=onboarding`,
          token,
        )
      : Promise.resolve([]),
    canManageLifecycle
      ? requestJson<OperationsLifecycleStatusRecord[]>(
          `${apiBaseUrl}/employees/lifecycle-task-status?lifecycle_type=offboarding`,
          token,
        )
      : Promise.resolve([]),
    canViewEmployees
      ? fetchEmployeeDirectory(apiBaseUrl, token, {
          search: '',
          employmentStatus: '',
          departmentId: '',
          designationId: '',
          managerId: '',
          page: 1,
          perPage: 100,
        })
      : Promise.resolve({ items: [] }),
    canViewIntegrations
      ? requestJson<OperationsIntegrationCatalogResponse>(`${apiBaseUrl}/integrations/catalog`, token)
      : Promise.resolve({ systems: [], events: [] }),
    canViewIntegrations
      ? requestJson<OperationsCollectionResponse<OperationsIntegrationConnectionRecord>>(
          `${apiBaseUrl}/integrations/connections`,
          token,
        )
      : Promise.resolve({ items: [] }),
    canViewIntegrations
      ? requestJson<OperationsCollectionResponse<OperationsIntegrationSubscriptionRecord>>(
          `${apiBaseUrl}/integrations/webhook-subscriptions`,
          token,
        )
      : Promise.resolve({ items: [] }),
    canViewIntegrations
      ? requestJson<OperationsSyncJobResponse>(`${apiBaseUrl}/integrations/sync-jobs?per_page=25`, token)
      : Promise.resolve({
          items: [],
          meta: {
            page: 1,
            per_page: 25,
            total: 0,
            last_page: 1,
          },
        }),
    canViewResilience
      ? requestJson<OperationsResilienceReadinessResponse>(`${apiBaseUrl}/resilience/readiness`, token)
      : Promise.resolve({
          summary: {
            total_scenario_count: 0,
            ready_scenario_count: 0,
            attention_scenario_count: 0,
            failed_scenario_count: 0,
            overdue_scenario_count: 0,
            validation_run_count: 0,
            latest_validation_at: null,
          },
          policy: {
            primary_region: 'primary',
            secondary_region: 'secondary',
            backup_cadence: '',
            restore_validation_cadence: '',
            dr_drill_cadence: '',
            retention_policy: '',
            encryption_posture: '',
            coverage_scope: '',
            default_rpo_minutes: 60,
            default_rto_minutes: 240,
            artifact_refs: [],
          },
          scenarios: [],
          runbook: [],
          validation_runs: [],
        }),
    canViewObservability
      ? requestJson<OperationsObservabilityOverviewResponse>(`${apiBaseUrl}/observability/overview`, token)
      : Promise.resolve({
          summary: {
            service_count: 0,
            healthy_service_count: 0,
            degraded_service_count: 0,
            critical_service_count: 0,
            active_alert_count: 0,
            routed_alert_count: 0,
            monitored_workflow_count: 0,
            monitored_integration_count: 0,
            release_critical_coverage_count: 0,
          },
          telemetry: {
            health_endpoint: '/up',
            default_log_channel: 'stack',
            slack_alert_channel: null,
            dashboard_refresh_minutes: 5,
            required_release_workflows: [],
          },
          services: [],
          signals: [],
          alerts: [],
          alert_routes: [],
          coverage: {
            workflows: [],
            integrations: [],
            release_critical: [],
          },
        }),
    canViewRelease
      ? requestJson<OperationsReleaseQualityResponse>(`${apiBaseUrl}/release/quality-gates`, token)
      : Promise.resolve({
          summary: {
            total_gate_count: 0,
            blocking_gate_count: 0,
            passing_gate_count: 0,
            pending_gate_count: 0,
            warning_gate_count: 0,
            blocked_environment_count: 0,
            protected_environment_count: 0,
          },
          policy: {
            protected_branch: 'main',
            promotion_rule: '',
            required_workflow_names: [],
            reviewer_roles: [],
            artifact_paths: [],
          },
          gates: [],
          environments: [],
        }),
    canViewRelease
      ? requestJson<OperationsReleaseReadinessResponse>(`${apiBaseUrl}/release/readiness`, token)
      : Promise.resolve({
          summary: {
            total_area_count: 0,
            ready_area_count: 0,
            attention_area_count: 0,
            blocked_area_count: 0,
            blocker_count: 0,
            runbook_count: 0,
            decision_count: 0,
            latest_decision_at: null,
          },
          policy: {
            review_cadence: '',
            decision_owner_roles: [],
            target_environments: ['production'],
            artifact_refs: [],
          },
          recommendation: {
            status: 'pending_review',
            summary: '',
          },
          areas: [],
          blockers: [],
          runbooks: [],
          latest_decision: null,
          decision_history: [],
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
    integrations: {
      systems: integrationCatalog.systems,
      events: integrationCatalog.events,
      connections: integrationConnections.items,
      subscriptions: integrationSubscriptions.items,
      syncJobs: integrationSyncJobs.items,
      syncJobMeta: integrationSyncJobs.meta,
    },
    release: releaseQuality,
    releaseReadiness,
    resilience: resilienceReadiness,
    observability: observabilityOverview,
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

export function createIntegrationConnection(
  apiBaseUrl: string,
  token: string,
  payload: IntegrationConnectionFormValues,
) {
  return requestJson<OperationsIntegrationConnectionRecord>(`${apiBaseUrl}/integrations/connections`, token, {
    method: 'POST',
    body: JSON.stringify({
      system_key: payload.system_key,
      name: payload.name.trim(),
      direction: payload.direction,
      status: payload.status,
      auth_mode: payload.auth_mode,
      endpoint_url: payload.endpoint_url.trim() || null,
      description: payload.description.trim() || null,
      scopes: payload.scopes
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean),
    }),
  })
}

export function createIntegrationSubscription(
  apiBaseUrl: string,
  token: string,
  payload: IntegrationSubscriptionFormValues,
) {
  return requestJson<OperationsIntegrationSubscriptionRecord>(
    `${apiBaseUrl}/integrations/webhook-subscriptions`,
    token,
    {
      method: 'POST',
      body: JSON.stringify({
        integration_connection_id: Number(payload.integration_connection_id),
        event_key: payload.event_key,
        direction: payload.direction,
        status: payload.status,
        endpoint_url: payload.endpoint_url.trim() || null,
        secret: payload.secret.trim(),
        custom_headers: parseJsonRecord(payload.custom_headers),
        filter_rules: parseJsonRecord(payload.filter_rules),
      }),
    },
  )
}

export function dispatchIntegrationEvent(
  apiBaseUrl: string,
  token: string,
  payload: IntegrationDispatchFormValues,
) {
  return requestJson<OperationsCollectionResponse<OperationsIntegrationSyncJobRecord>>(
    `${apiBaseUrl}/integrations/events/dispatch`,
    token,
    {
      method: 'POST',
      body: JSON.stringify({
        event_key: payload.event_key,
        entity_type: payload.entity_type.trim() || null,
        entity_id: payload.entity_id.trim() || null,
        payload: parseJsonRecord(payload.payload),
      }),
    },
  )
}

export function createResilienceValidationRun(
  apiBaseUrl: string,
  token: string,
  payload: ResilienceValidationRunFormValues,
) {
  return requestJson(`${apiBaseUrl}/resilience/validation-runs`, token, {
    method: 'POST',
    body: JSON.stringify(serializeResilienceValidationRun(payload)),
  })
}

export function createReleaseReadinessDecision(
  apiBaseUrl: string,
  token: string,
  payload: ReleaseReadinessDecisionFormValues,
) {
  return requestJson<OperationsReleaseReadinessDecisionRecord>(`${apiBaseUrl}/release/readiness/decisions`, token, {
    method: 'POST',
    body: JSON.stringify(serializeReleaseReadinessDecision(payload)),
  })
}

export function retryIntegrationSyncJob(
  apiBaseUrl: string,
  token: string,
  jobId: number,
) {
  return requestJson<OperationsIntegrationSyncJobRecord>(
    `${apiBaseUrl}/integrations/sync-jobs/${jobId}/retry`,
    token,
    {
      method: 'POST',
    },
  )
}

export function processIntegrationSyncJob(
  apiBaseUrl: string,
  token: string,
  jobId: number,
) {
  return requestJson<OperationsIntegrationSyncJobRecord>(
    `${apiBaseUrl}/integrations/sync-jobs/${jobId}/process`,
    token,
    {
      method: 'POST',
    },
  )
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

function parseJsonRecord(value: string) {
  if (!value.trim()) {
    return {}
  }

  const parsed = JSON.parse(value) as unknown

  if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
    throw new Error('JSON fields must contain an object.')
  }

  return parsed as Record<string, unknown>
}

function serializeResilienceValidationRun(payload: ResilienceValidationRunFormValues) {
  return {
    scenario_key: payload.scenario_key,
    status: payload.status,
    recovery_point_actual_minutes: payload.recovery_point_actual_minutes.trim()
      ? Number(payload.recovery_point_actual_minutes)
      : null,
    recovery_time_actual_minutes: payload.recovery_time_actual_minutes.trim()
      ? Number(payload.recovery_time_actual_minutes)
      : null,
    evidence_refs: payload.evidence_refs
      .split(/[\n,]/)
      .map((item) => item.trim())
      .filter(Boolean),
    notes: payload.notes.trim() || null,
  }
}

function serializeReleaseReadinessDecision(payload: ReleaseReadinessDecisionFormValues) {
  return {
    release_window_label: payload.release_window_label.trim(),
    target_environment: payload.target_environment,
    decision_status: payload.decision_status,
    summary: payload.summary.trim(),
    blockers: payload.blockers
      .split('\n')
      .map((line) => line.trim())
      .filter(Boolean)
      .map((line) => {
        const [areaKey = '', ownerRole = '', title = '', notes = ''] = line.split('|').map((item) => item.trim())

        return {
          area_key: areaKey || null,
          owner_role: ownerRole || 'tenant.admin',
          title: title || line,
          notes: notes || null,
          status: 'open',
        }
      }),
    artifact_refs: payload.artifact_refs
      .split(/[\n,]/)
      .map((item) => item.trim())
      .filter(Boolean),
    decision_notes: payload.decision_notes.trim() || null,
  }
}

function hasAnyPermission(grantedPermissions: string[], requiredPermissions: string[]) {
  return requiredPermissions.some((permission) => grantedPermissions.includes(permission))
}
