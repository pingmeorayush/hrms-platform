import { startTransition, useEffect, useMemo, useState } from 'react'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { useAppSelector } from '../../../app/store/hooks'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  assignAsset,
  createReleaseReadinessDecision,
  createResilienceValidationRun,
  createIntegrationConnection,
  createIntegrationSubscription,
  createAsset,
  createAssetCategory,
  createDocumentCategory,
  dispatchIntegrationEvent,
  fetchEmployeeLifecycleTasks,
  fetchOperationsWorkspace,
  issueAsset,
  processIntegrationSyncJob,
  retryIntegrationSyncJob,
  returnAsset,
  updateDocumentCategory,
  updateLifecycleTaskStatus,
} from '../api/operationsApi'
import {
  buildDemoOperationsWorkspace,
  createDemoReleaseReadinessDecision,
  buildDemoResilienceOverview,
  getDemoLifecycleTasks,
} from '../data/demoOperationsWorkspace'
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
  OperationsAssetRecord,
  OperationsIntegrationSyncJobRecord,
  OperationsLifecycleTaskCollection,
  OperationsLifecycleType,
  OperationsWorkspaceData,
  ReleaseReadinessDecisionFormValues,
  ResilienceValidationRunFormValues,
} from '../types'

const queryScope = 'operations-workspace'

function cloneWorkspaceData(data: OperationsWorkspaceData) {
  return structuredClone(data)
}

export function useOperationsWorkspace() {
  const access = useAppSelector((state) => state.access)
  const queryClient = useQueryClient()
  const { snapshot, source } = useAccessSnapshot()
  const permissions = useMemo(() => snapshot?.user.permissions ?? [], [snapshot?.user.permissions])
  const liveEnabled = access.mode === 'live' && access.token.trim().length > 0
  const demoStateKey = String(snapshot?.user.id ?? 'anonymous')
  const [demoStates, setDemoStates] = useState<Record<string, OperationsWorkspaceData>>({})
  const [selectedLifecycleType, setSelectedLifecycleType] = useState<OperationsLifecycleType>('onboarding')
  const [selectedLifecycleEmployeeId, setSelectedLifecycleEmployeeId] = useState<number | null>(null)

  const demoData = demoStates[demoStateKey] ?? buildDemoOperationsWorkspace(snapshot ?? null)
  const permissionsKey = useMemo(() => [...permissions].sort().join(','), [permissions])
  const queryKey = useMemo(
    () => [queryScope, access.apiBaseUrl, access.token, permissionsKey] as const,
    [access.apiBaseUrl, access.token, permissionsKey],
  )

  const liveQuery = useQuery({
    queryKey,
    queryFn: () => fetchOperationsWorkspace(access.apiBaseUrl, access.token, permissions),
    enabled: liveEnabled,
  })

  const data = source === 'demo' ? demoData : liveQuery.data ?? null
  const selectedLifecycleStatuses = useMemo(
    () => data?.lifecycle[selectedLifecycleType] ?? [],
    [data, selectedLifecycleType],
  )

  useEffect(() => {
    if (!selectedLifecycleStatuses.length) {
      if (selectedLifecycleEmployeeId !== null) {
        startTransition(() => {
          setSelectedLifecycleEmployeeId(null)
        })
      }
      return
    }

    if (selectedLifecycleEmployeeId && selectedLifecycleStatuses.some((record) => record.employee.id === selectedLifecycleEmployeeId)) {
      return
    }

    startTransition(() => {
      setSelectedLifecycleEmployeeId(selectedLifecycleStatuses[0]?.employee.id ?? null)
    })
  }, [selectedLifecycleEmployeeId, selectedLifecycleStatuses])

  const lifecycleTaskQuery = useQuery({
    queryKey: [...queryKey, 'lifecycle-tasks', selectedLifecycleType, selectedLifecycleEmployeeId ?? 'none'],
    queryFn: () =>
      fetchEmployeeLifecycleTasks(
        access.apiBaseUrl,
        access.token,
        selectedLifecycleEmployeeId as number,
        selectedLifecycleType,
      ),
    enabled: liveEnabled && selectedLifecycleEmployeeId !== null,
  })

  const selectedLifecycleTasks =
    source === 'demo'
      ? getDemoLifecycleTasks(data, selectedLifecycleEmployeeId, selectedLifecycleType)
      : lifecycleTaskQuery.data ?? null

  const canViewDocuments = hasAnyPermission(permissions, ['document.view', 'document.manage'])
  const canManageDocuments = hasAnyPermission(permissions, ['document.manage'])
  const canViewAssets = hasAnyPermission(permissions, ['asset.view', 'asset.manage'])
  const canManageAssets = hasAnyPermission(permissions, ['asset.manage'])
  const canManageLifecycle = hasAnyPermission(permissions, ['employee.manage'])
  const canViewIntegrations = hasAnyPermission(permissions, ['integration.view', 'integration.manage'])
  const canManageIntegrations = hasAnyPermission(permissions, ['integration.manage'])
  const canViewResilience = hasAnyPermission(permissions, ['resilience.view', 'resilience.manage'])
  const canManageResilience = hasAnyPermission(permissions, ['resilience.manage'])
  const canViewObservability = hasAnyPermission(permissions, ['observability.view', 'observability.manage'])
  const canManageObservability = hasAnyPermission(permissions, ['observability.manage'])
  const canViewRelease = hasAnyPermission(permissions, ['release.view', 'release.manage'])
  const canManageRelease = hasAnyPermission(permissions, ['release.manage'])

  async function persistDemoUpdate(
    updater: (workspace: OperationsWorkspaceData) => OperationsWorkspaceData,
  ) {
    const current = cloneWorkspaceData(demoStates[demoStateKey] ?? demoData)
    const next = updater(current)

    setDemoStates((existing) => ({
      ...existing,
      [demoStateKey]: next,
    }))
  }

  async function refreshLiveWorkspace() {
    await queryClient.invalidateQueries({ queryKey })
    await queryClient.invalidateQueries({
      queryKey: [...queryKey, 'lifecycle-tasks', selectedLifecycleType, selectedLifecycleEmployeeId ?? 'none'],
    })
  }

  async function saveDocumentCategory(values: DocumentCategoryFormValues, categoryId?: number | null) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) => upsertDemoDocumentCategory(workspace, values, categoryId ?? null))
      return
    }

    if (categoryId) {
      await updateDocumentCategory(access.apiBaseUrl, access.token, categoryId, values)
    } else {
      await createDocumentCategory(access.apiBaseUrl, access.token, values)
    }

    await refreshLiveWorkspace()
  }

  async function saveAssetCategory(values: AssetCategoryFormValues) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) => createDemoAssetCategory(workspace, values))
      return
    }

    await createAssetCategory(access.apiBaseUrl, access.token, values)
    await refreshLiveWorkspace()
  }

  async function saveAsset(values: AssetFormValues) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) => createDemoAsset(workspace, values))
      return
    }

    await createAsset(access.apiBaseUrl, access.token, values)
    await refreshLiveWorkspace()
  }

  async function assignAssetToEmployee(assetId: number, values: AssetAssignmentFormValues) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) => assignDemoAsset(workspace, assetId, values))
      return
    }

    await assignAsset(access.apiBaseUrl, access.token, assetId, values)
    await refreshLiveWorkspace()
  }

  async function issueAssignedAsset(assetId: number, values: AssetIssueFormValues) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) => issueDemoAsset(workspace, assetId, values))
      return
    }

    await issueAsset(access.apiBaseUrl, access.token, assetId, values)
    await refreshLiveWorkspace()
  }

  async function returnAssignedAsset(assetId: number, values: AssetReturnFormValues) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) => returnDemoAsset(workspace, assetId, values))
      return
    }

    await returnAsset(access.apiBaseUrl, access.token, assetId, values)
    await refreshLiveWorkspace()
  }

  async function updateLifecycleTask(
    employeeId: number,
    taskId: number,
    status: string,
    notes: string | null = null,
  ) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) =>
        updateDemoLifecycleTask(workspace, employeeId, taskId, selectedLifecycleType, status, notes),
      )
      return
    }

    await updateLifecycleTaskStatus(
      access.apiBaseUrl,
      access.token,
      employeeId,
      taskId,
      selectedLifecycleType,
      status,
      notes,
    )
    await refreshLiveWorkspace()
  }

  async function saveIntegrationConnection(values: IntegrationConnectionFormValues) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) => createDemoIntegrationConnection(workspace, values))
      return
    }

    await createIntegrationConnection(access.apiBaseUrl, access.token, values)
    await refreshLiveWorkspace()
  }

  async function saveIntegrationSubscription(values: IntegrationSubscriptionFormValues) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) => createDemoIntegrationSubscription(workspace, values))
      return
    }

    await createIntegrationSubscription(access.apiBaseUrl, access.token, values)
    await refreshLiveWorkspace()
  }

  async function triggerIntegrationEvent(values: IntegrationDispatchFormValues) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) => dispatchDemoIntegrationEvent(workspace, values))
      return
    }

    await dispatchIntegrationEvent(access.apiBaseUrl, access.token, values)
    await refreshLiveWorkspace()
  }

  async function retryFailedIntegrationJob(jobId: number) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) => retryDemoIntegrationSyncJob(workspace, jobId))
      return
    }

    await retryIntegrationSyncJob(access.apiBaseUrl, access.token, jobId)
    await refreshLiveWorkspace()
  }

  async function processQueuedIntegrationJob(jobId: number) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) => processDemoIntegrationSyncJob(workspace, jobId))
      return
    }

    await processIntegrationSyncJob(access.apiBaseUrl, access.token, jobId)
    await refreshLiveWorkspace()
  }

  async function logResilienceValidation(values: ResilienceValidationRunFormValues) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) =>
        createDemoResilienceValidationRun(
          workspace,
          values,
          snapshot?.user.name ?? 'Demo operator',
          snapshot?.user.id ?? null,
        ),
      )
      return
    }

    await createResilienceValidationRun(access.apiBaseUrl, access.token, values)
    await refreshLiveWorkspace()
  }

  async function recordReleaseReadinessDecision(values: ReleaseReadinessDecisionFormValues) {
    if (source === 'demo') {
      await persistDemoUpdate((workspace) =>
        createDemoReleaseReadinessDecision(
          workspace,
          values,
          snapshot?.user.name ?? 'Demo operator',
          snapshot?.user.id ?? null,
        ),
      )
      return
    }

    await createReleaseReadinessDecision(access.apiBaseUrl, access.token, values)
    await refreshLiveWorkspace()
  }

  return {
    source,
    data,
    isLoading: source === 'live' ? liveQuery.isLoading : false,
    error: source === 'live' ? (liveQuery.error as Error | null) ?? null : null,
    canViewDocuments,
    canManageDocuments,
    canViewAssets,
    canManageAssets,
    canManageLifecycle,
    canViewIntegrations,
    canManageIntegrations,
    canViewResilience,
    canManageResilience,
    canViewObservability,
    canManageObservability,
    canViewRelease,
    canManageRelease,
    selectedLifecycleType,
    setSelectedLifecycleType: (value: OperationsLifecycleType) => {
      startTransition(() => {
        setSelectedLifecycleType(value)
      })
    },
    selectedLifecycleEmployeeId,
    setSelectedLifecycleEmployeeId: (value: number | null) => {
      startTransition(() => {
        setSelectedLifecycleEmployeeId(value)
      })
    },
    selectedLifecycleStatuses,
    selectedLifecycleTasks,
    isLifecycleLoading: source === 'live' ? lifecycleTaskQuery.isLoading : false,
    lifecycleError: source === 'live' ? (lifecycleTaskQuery.error as Error | null) ?? null : null,
    saveDocumentCategory,
    saveAssetCategory,
    saveAsset,
    assignAssetToEmployee,
    issueAssignedAsset,
    returnAssignedAsset,
    updateLifecycleTask,
    saveIntegrationConnection,
    saveIntegrationSubscription,
    triggerIntegrationEvent,
    retryFailedIntegrationJob,
    processQueuedIntegrationJob,
    logResilienceValidation,
    recordReleaseReadinessDecision,
  }
}

function hasAnyPermission(grantedPermissions: string[], requiredPermissions: string[]) {
  return requiredPermissions.some((permission) => grantedPermissions.includes(permission))
}

function nextId(values: Array<{ id: number }>) {
  return values.reduce((largest, item) => Math.max(largest, item.id), 0) + 1
}

function normalizeDelimitedValues(value: string) {
  return value
    .split(/[\n,]/)
    .map((item) => item.trim())
    .filter(Boolean)
}

function upsertDemoDocumentCategory(
  workspace: OperationsWorkspaceData,
  values: DocumentCategoryFormValues,
  categoryId: number | null,
) {
  const nextCategory = {
    id: categoryId ?? nextId(workspace.documentCategories),
    code: values.code.trim(),
    name: values.name.trim(),
    repository_scope: values.repository_scope,
    default_visibility_scope: values.default_visibility_scope,
    retention_days: values.retention_days.trim() ? Number(values.retention_days) : null,
    allowed_role_names: normalizeDelimitedValues(values.allowed_role_names),
    status: values.status,
    notes: values.notes.trim() || null,
    created_at: categoryId ? workspace.documentCategories.find((category) => category.id === categoryId)?.created_at ?? new Date().toISOString() : new Date().toISOString(),
    updated_at: new Date().toISOString(),
  }

  const documentCategories = categoryId
    ? workspace.documentCategories.map((category) => (category.id === categoryId ? nextCategory : category))
    : [nextCategory, ...workspace.documentCategories]

  const documents = workspace.documents.map((document) =>
    document.document_category_id === nextCategory.id
      ? {
          ...document,
          document_category: {
            id: nextCategory.id,
            code: nextCategory.code,
            name: nextCategory.name,
            default_visibility_scope: nextCategory.default_visibility_scope,
            retention_days: nextCategory.retention_days,
            allowed_role_names: nextCategory.allowed_role_names,
            status: nextCategory.status,
          },
        }
      : document,
  )

  return {
    ...workspace,
    documentCategories,
    documents,
  }
}

function createDemoAssetCategory(workspace: OperationsWorkspaceData, values: AssetCategoryFormValues) {
  const nextCategory = {
    id: nextId(workspace.assetCategories),
    code: values.code.trim(),
    name: values.name.trim(),
    status: values.status,
    notes: values.notes.trim() || null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  }

  return {
    ...workspace,
    assetCategories: [nextCategory, ...workspace.assetCategories],
  }
}

function createDemoAsset(workspace: OperationsWorkspaceData, values: AssetFormValues) {
  const category = workspace.assetCategories.find((record) => record.id === Number(values.asset_category_id))

  const nextAsset: OperationsAssetRecord = {
    id: nextId(workspace.assets),
    asset_category_id: Number(values.asset_category_id),
    asset_category: category
      ? {
          id: category.id,
          code: category.code,
          name: category.name,
          status: category.status,
        }
      : null,
    asset_tag: values.asset_tag.trim(),
    name: values.name.trim(),
    asset_type: values.asset_type,
    serial_number: values.serial_number.trim() || null,
    manufacturer: values.manufacturer.trim() || null,
    model_name: values.model_name.trim() || null,
    purchase_date: values.purchase_date || null,
    status: values.status,
    notes: values.notes.trim() || null,
    current_assignment: null,
    assignment_history: [],
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  }

  return {
    ...workspace,
    assets: [nextAsset, ...workspace.assets],
  }
}

function assignDemoAsset(
  workspace: OperationsWorkspaceData,
  assetId: number,
  values: AssetAssignmentFormValues,
) {
  const employee = workspace.employees.find((record) => record.id === Number(values.employee_id))

  return {
    ...workspace,
    assets: workspace.assets.map((asset) => {
      if (asset.id !== assetId) {
        return asset
      }

      return {
        ...asset,
        status: 'assigned',
        updated_at: new Date().toISOString(),
        current_assignment: {
          id: nextId(asset.assignment_history.concat(asset.current_assignment ? [asset.current_assignment] : [])),
          asset_id: asset.id,
          employee_id: employee?.id ?? Number(values.employee_id),
          employee: employee
            ? {
                id: employee.id,
                employee_code: employee.employee_code,
                full_name: employee.full_name,
                email: employee.email,
              }
            : null,
          status: 'assigned',
          assigned_at: values.assigned_at || new Date().toISOString(),
          issued_at: null,
          expected_return_date: values.expected_return_date || null,
          returned_at: null,
          handover_condition: values.handover_condition.trim() || null,
          return_condition: null,
          assignment_notes: values.assignment_notes.trim() || null,
          issue_notes: null,
          return_notes: null,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        },
      }
    }),
  }
}

function issueDemoAsset(
  workspace: OperationsWorkspaceData,
  assetId: number,
  values: AssetIssueFormValues,
) {
  return {
    ...workspace,
    assets: workspace.assets.map((asset) => {
      if (asset.id !== assetId || !asset.current_assignment) {
        return asset
      }

      return {
        ...asset,
        status: 'issued',
        updated_at: new Date().toISOString(),
        current_assignment: {
          ...asset.current_assignment,
          status: 'issued',
          issued_at: values.issued_at || new Date().toISOString(),
          issue_notes: values.issue_notes.trim() || null,
          updated_at: new Date().toISOString(),
        },
      }
    }),
  }
}

function returnDemoAsset(
  workspace: OperationsWorkspaceData,
  assetId: number,
  values: AssetReturnFormValues,
) {
  return {
    ...workspace,
    assets: workspace.assets.map((asset) => {
      if (asset.id !== assetId || !asset.current_assignment) {
        return asset
      }

      const returnedAssignment = {
        ...asset.current_assignment,
        status: 'returned',
        returned_at: values.returned_at || new Date().toISOString(),
        return_condition: values.return_condition.trim() || null,
        return_notes: values.return_notes.trim() || null,
        updated_at: new Date().toISOString(),
      }

      return {
        ...asset,
        status: 'returned',
        updated_at: new Date().toISOString(),
        current_assignment: null,
        assignment_history: [returnedAssignment, ...asset.assignment_history],
      }
    }),
  }
}

function updateDemoLifecycleTask(
  workspace: OperationsWorkspaceData,
  employeeId: number,
  taskId: number,
  lifecycleType: OperationsLifecycleType,
  status: string,
  notes: string | null,
) {
  const lifecycleTaskDetails = workspace.lifecycleTaskDetails ?? {}
  const employeeTasks = lifecycleTaskDetails[employeeId]
  const currentCollection = employeeTasks?.[lifecycleType]

  if (!currentCollection) {
    return workspace
  }

  const items = currentCollection.items.map((task) => {
    if (task.id !== taskId) {
      return task
    }

    return {
      ...task,
      status,
      notes: notes ?? task.notes,
      completed_at: status === 'completed' ? new Date().toISOString() : null,
      updated_at: new Date().toISOString(),
      due_state: deriveDueState(task.due_date, status),
      approved_at: status === 'completed' && task.requires_approval ? new Date().toISOString() : task.approved_at,
    }
  })
  const nextCollection = summarizeLifecycleCollection({
    ...currentCollection,
    items,
  })

  const nextLifecycleTaskDetails = {
    ...lifecycleTaskDetails,
    [employeeId]: {
      ...employeeTasks,
      [lifecycleType]: nextCollection,
    },
  }

  return {
    ...workspace,
    lifecycleTaskDetails: nextLifecycleTaskDetails,
    lifecycle: {
      onboarding: rebuildLifecycleStatuses(workspace, nextLifecycleTaskDetails, 'onboarding'),
      offboarding: rebuildLifecycleStatuses(workspace, nextLifecycleTaskDetails, 'offboarding'),
    },
  }
}

function createDemoIntegrationConnection(
  workspace: OperationsWorkspaceData,
  values: IntegrationConnectionFormValues,
) {
  const nextConnection = {
    id: nextId(workspace.integrations.connections),
    system_key: values.system_key,
    version: 'v1',
    name: values.name.trim(),
    direction: values.direction,
    status: values.status,
    auth_mode: values.auth_mode,
    endpoint_url: values.endpoint_url.trim() || null,
    description: values.description.trim() || null,
    scopes: values.scopes
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean),
    settings: {},
    active_subscription_count: 0,
    last_synced_at: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  }

  return {
    ...workspace,
    integrations: {
      ...workspace.integrations,
      connections: [nextConnection, ...workspace.integrations.connections],
    },
  }
}

function createDemoIntegrationSubscription(
  workspace: OperationsWorkspaceData,
  values: IntegrationSubscriptionFormValues,
) {
  const connection = workspace.integrations.connections.find(
    (record) => record.id === Number(values.integration_connection_id),
  )
  const customHeaders = parseDemoJsonRecord(values.custom_headers)
  const filterRules = parseDemoJsonRecord(values.filter_rules)
  const nextSubscription = {
    id: nextId(workspace.integrations.subscriptions),
    subscription_key: `sub-demo-${Date.now()}`,
    integration_connection_id: Number(values.integration_connection_id),
    version: 'v1',
    event_key: values.event_key,
    direction: values.direction,
    status: values.status,
    endpoint_url: values.endpoint_url.trim() || null,
    secret_preview: values.secret.trim() ? `••••${values.secret.trim().slice(-4)}` : null,
    custom_headers: customHeaders,
    filter_rules: filterRules,
    connection: connection
      ? {
          id: connection.id,
          system_key: connection.system_key,
          name: connection.name,
          status: connection.status,
        }
      : null,
    last_delivery_at: null,
    last_received_at: null,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  }

  return {
    ...workspace,
    integrations: {
      ...workspace.integrations,
      connections: workspace.integrations.connections.map((record) =>
        record.id === nextSubscription.integration_connection_id
          ? {
              ...record,
              active_subscription_count:
                (record.active_subscription_count ?? 0) + (nextSubscription.status === 'active' ? 1 : 0),
              updated_at: new Date().toISOString(),
            }
          : record,
      ),
      subscriptions: [nextSubscription, ...workspace.integrations.subscriptions],
    },
  }
}

function dispatchDemoIntegrationEvent(
  workspace: OperationsWorkspaceData,
  values: IntegrationDispatchFormValues,
) {
  const payload = parseDemoJsonRecord(values.payload)
  const matchingSubscriptions = workspace.integrations.subscriptions.filter(
    (subscription) =>
      subscription.event_key === values.event_key &&
      subscription.direction === 'outbound' &&
      subscription.status === 'active',
  )

  if (!matchingSubscriptions.length) {
    throw new Error('No active outbound subscription matches this event in the demo workspace.')
  }

  const now = new Date().toISOString()
  const nextJobId = nextId(workspace.integrations.syncJobs)
  const nextJobs = matchingSubscriptions.map((subscription, index) =>
    buildDemoIntegrationJob(
      workspace,
      subscription,
      {
        id: nextJobId + index,
        eventKey: values.event_key,
        entityType: values.entity_type.trim() || null,
        entityId: values.entity_id.trim() || null,
        payload,
        shouldFail: Boolean(payload.simulate_failure),
        occurredAt: now,
      },
    ),
  )

  return {
    ...workspace,
    integrations: {
      ...workspace.integrations,
      subscriptions: workspace.integrations.subscriptions.map((subscription) =>
        matchingSubscriptions.some((candidate) => candidate.id === subscription.id)
          ? {
              ...subscription,
              last_delivery_at: now,
              updated_at: now,
            }
          : subscription,
      ),
      syncJobs: [...nextJobs, ...workspace.integrations.syncJobs].slice(0, 25),
      syncJobMeta: {
        ...workspace.integrations.syncJobMeta,
        total: workspace.integrations.syncJobs.length + nextJobs.length,
      },
    },
  }
}

function createDemoResilienceValidationRun(
  workspace: OperationsWorkspaceData,
  values: ResilienceValidationRunFormValues,
  actorName: string,
  actorId: number | null,
) {
  const scenario =
    workspace.resilience.scenarios.find((record) => record.key === values.scenario_key) ??
    workspace.resilience.scenarios[0]

  if (!scenario) {
    throw new Error('The selected resilience scenario could not be found in the demo workspace.')
  }

  const now = new Date().toISOString()
  const nextRun = {
    id: nextId(workspace.resilience.validation_runs),
    scenario_key: scenario.key,
    scenario_name: scenario.name,
    scenario_type: scenario.scenario_type,
    environment: scenario.environment,
    status: values.status,
    recovery_point_actual_minutes: values.recovery_point_actual_minutes.trim()
      ? Number(values.recovery_point_actual_minutes)
      : null,
    recovery_time_actual_minutes: values.recovery_time_actual_minutes.trim()
      ? Number(values.recovery_time_actual_minutes)
      : null,
    evidence_refs: normalizeDelimitedValues(values.evidence_refs),
    notes: values.notes.trim() || null,
    started_at: now,
    completed_at: values.status === 'in_progress' ? null : now,
    executed_by_user_id: actorId,
    executed_by_name: actorName,
    created_at: now,
    updated_at: now,
  }

  const validationRuns = [nextRun, ...workspace.resilience.validation_runs]

  return {
    ...workspace,
    resilience: buildDemoResilienceOverview(validationRuns),
  }
}

function retryDemoIntegrationSyncJob(
  workspace: OperationsWorkspaceData,
  jobId: number,
) {
  let updatedJob: OperationsIntegrationSyncJobRecord | null = null

  const syncJobs = workspace.integrations.syncJobs.map((job) => {
    if (job.id !== jobId) {
      return job
    }

    const now = new Date().toISOString()
    updatedJob = {
      ...job,
      status: 'completed',
      monitoring_state: 'retried',
      attempts_count: job.attempts_count + 1,
      last_attempt_at: now,
      started_at: now,
      completed_at: now,
      failed_at: null,
      retried_at: now,
      last_error: null,
      can_retry: false,
      response_payload: {
        status_code: 200,
        body: {
          retried: true,
          outcome: 'successful',
        },
      },
      errors: job.errors.map((error) =>
        error.resolved_at
          ? error
          : {
              ...error,
              resolved_at: now,
            },
      ),
      updated_at: now,
    }

    return updatedJob
  })

  if (!updatedJob) {
    throw new Error('The selected demo sync job could not be found.')
  }

  return {
    ...workspace,
    integrations: {
      ...workspace.integrations,
      syncJobs,
    },
  }
}

function processDemoIntegrationSyncJob(
  workspace: OperationsWorkspaceData,
  jobId: number,
) {
  let updatedJob: OperationsIntegrationSyncJobRecord | null = null

  const syncJobs = workspace.integrations.syncJobs.map((job) => {
    if (job.id !== jobId) {
      return job
    }

    if (job.status !== 'queued') {
      throw new Error('Only queued demo sync jobs can be processed.')
    }

    const now = new Date().toISOString()
    updatedJob = {
      ...job,
      status: 'completed',
      monitoring_state: 'completed',
      attempts_count: job.attempts_count + 1,
      last_attempt_at: now,
      started_at: now,
      completed_at: now,
      last_error: null,
      response_payload: {
        status_code: 202,
        body: {
          processed: true,
          outcome: 'queued job completed',
        },
      },
      updated_at: now,
    }

    return updatedJob
  })

  if (!updatedJob) {
    throw new Error('The selected demo sync job could not be found.')
  }

  return {
    ...workspace,
    integrations: {
      ...workspace.integrations,
      syncJobs,
    },
  }
}

function deriveDueState(dueDate: string | null, status: string) {
  if (!dueDate) {
    return 'no_due_date'
  }

  if (status === 'completed' || status === 'skipped') {
    return 'closed'
  }

  const today = new Date().toISOString().slice(0, 10)
  if (dueDate < today) {
    return 'overdue'
  }

  if (dueDate === today) {
    return 'due_today'
  }

  return 'upcoming'
}

function summarizeLifecycleCollection(collection: OperationsLifecycleTaskCollection) {
  const items = collection.items
  const completedCount = items.filter((item) => item.status === 'completed').length
  const skippedCount = items.filter((item) => item.status === 'skipped').length
  const pendingCount = items.filter((item) => item.status === 'pending').length
  const inProgressCount = items.filter((item) => item.status === 'in_progress').length
  const awaitingApprovalCount = items.filter((item) => item.status === 'awaiting_approval').length
  const changesRequestedCount = items.filter((item) => item.status === 'changes_requested').length
  const rejectedCount = items.filter((item) => item.status === 'rejected').length
  const totalCount = items.length
  const incompleteCount =
    pendingCount + inProgressCount + awaitingApprovalCount + changesRequestedCount + rejectedCount

  return {
    ...collection,
    summary: {
      total_count: totalCount,
      completed_count: completedCount,
      skipped_count: skippedCount,
      pending_count: pendingCount,
      in_progress_count: inProgressCount,
      awaiting_approval_count: awaitingApprovalCount,
      changes_requested_count: changesRequestedCount,
      rejected_count: rejectedCount,
      incomplete_count: incompleteCount,
      progress_percentage: totalCount === 0 ? 0 : Math.round(((completedCount + skippedCount) / totalCount) * 100),
      is_complete: totalCount > 0 && incompleteCount === 0,
    },
  }
}

function rebuildLifecycleStatuses(
  workspace: OperationsWorkspaceData,
  lifecycleTaskDetails: OperationsWorkspaceData['lifecycleTaskDetails'],
  lifecycleType: OperationsLifecycleType,
) {
  return workspace.employees
    .map((employee) => {
      const collection = lifecycleTaskDetails?.[employee.id]?.[lifecycleType]
      if (!collection || collection.summary.incomplete_count === 0) {
        return null
      }

      return {
        employee: {
          id: employee.id,
          employee_code: employee.employee_code,
          full_name: employee.full_name,
          email: employee.email,
          date_of_joining: employee.date_of_joining,
          department: employee.department.name,
          designation: employee.designation.name,
        },
        lifecycle_type: lifecycleType,
        summary: {
          total_count: collection.summary.total_count,
          closed_count: collection.summary.completed_count + collection.summary.skipped_count,
          incomplete_count: collection.summary.incomplete_count,
          progress_percentage: collection.summary.progress_percentage,
          is_complete: collection.summary.is_complete,
        },
      }
    })
    .filter((entry): entry is NonNullable<typeof entry> => entry !== null)
}

function parseDemoJsonRecord(value: string) {
  if (!value.trim()) {
    return {}
  }

  const parsed = JSON.parse(value) as unknown
  if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
    throw new Error('JSON fields must contain an object.')
  }

  return parsed as Record<string, unknown>
}

function buildDemoIntegrationJob(
  workspace: OperationsWorkspaceData,
  subscription: OperationsWorkspaceData['integrations']['subscriptions'][number],
  options: {
    id: number
    eventKey: string
    entityType: string | null
    entityId: string | null
    payload: Record<string, unknown>
    shouldFail: boolean
    occurredAt: string
  },
): OperationsIntegrationSyncJobRecord {
  const connection = workspace.integrations.connections.find(
    (record) => record.id === subscription.integration_connection_id,
  )
  const responsePayload = options.shouldFail
    ? {
        status_code: 500,
        body: {
          error: 'Simulated integration outage',
        },
      }
    : {
        status_code: 202,
        body: {
          delivered: true,
        },
      }

  return {
    id: options.id,
    job_uuid: `job-demo-${options.id}`,
    version: 'v1',
    system_key: connection?.system_key ?? 'unknown',
    event_key: options.eventKey,
    direction: 'outbound',
    status: options.shouldFail ? 'failed' : 'completed',
    monitoring_state: options.shouldFail ? 'failed' : 'completed',
    trigger_source: 'manual_event',
    entity_type: options.entityType,
    entity_id: options.entityId,
    request_payload: options.payload,
    response_payload: responsePayload,
    request_headers: {
      'X-PhoenixHRMS-Event': options.eventKey,
    },
    response_headers: {},
    attempts_count: 1,
    last_attempt_at: options.occurredAt,
    queued_at: options.occurredAt,
    started_at: options.occurredAt,
    completed_at: options.shouldFail ? null : options.occurredAt,
    failed_at: options.shouldFail ? options.occurredAt : null,
    retried_at: null,
    last_error: options.shouldFail ? 'Simulated integration outage.' : null,
    can_retry: options.shouldFail,
    connection: connection
      ? {
          id: connection.id,
          system_key: connection.system_key,
          name: connection.name,
          status: connection.status,
        }
      : null,
    subscription: {
      id: subscription.id,
      subscription_key: subscription.subscription_key,
      event_key: subscription.event_key,
      direction: subscription.direction,
      status: subscription.status,
    },
    errors: options.shouldFail
      ? [
          {
            id: options.id + 10_000,
            attempt_number: 1,
            error_code: 'delivery_failed',
            error_message: 'Simulated integration outage.',
            request_payload: options.payload,
            response_payload: responsePayload,
            request_headers: {
              'X-PhoenixHRMS-Event': options.eventKey,
            },
            response_headers: {},
            occurred_at: options.occurredAt,
            resolved_at: null,
          },
        ]
      : [],
    created_at: options.occurredAt,
    updated_at: options.occurredAt,
  }
}
