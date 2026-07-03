import type { EmployeeRecord } from '../employees/types'

export type OperationsLifecycleType = 'onboarding' | 'offboarding'

export interface OperationsPagedMeta {
  page: number
  per_page: number
  total: number
  last_page: number
}

export interface OperationsDocumentCategoryRecord {
  id: number
  code: string
  name: string
  repository_scope: string
  default_visibility_scope: string
  retention_days: number | null
  allowed_role_names: string[]
  status: string
  notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsDocumentRecord {
  id: number
  document_category_id: number | null
  document_category: {
    id: number
    code: string
    name: string
    default_visibility_scope: string
    retention_days: number | null
    allowed_role_names: string[]
    status: string
  } | null
  title: string
  repository_scope: string
  linked_entity_type: string | null
  linked_entity_id: number | null
  visibility_scope: string
  original_file_name: string
  mime_type: string
  file_size_bytes: number
  checksum_sha256: string | null
  retention_until: string | null
  metadata: Record<string, unknown>
  notes: string | null
  download_url: string
  created_at: string | null
  updated_at: string | null
}

export interface OperationsAssetCategoryRecord {
  id: number
  code: string
  name: string
  status: string
  notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsAssetAssignmentRecord {
  id: number
  asset_id: number
  employee_id: number
  employee: {
    id: number
    employee_code: string
    full_name: string
    email: string | null
  } | null
  status: string
  assigned_at: string | null
  issued_at: string | null
  expected_return_date: string | null
  returned_at: string | null
  handover_condition: string | null
  return_condition: string | null
  assignment_notes: string | null
  issue_notes: string | null
  return_notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsAssetRecord {
  id: number
  asset_category_id: number
  asset_category: {
    id: number
    code: string
    name: string
    status: string
  } | null
  asset_tag: string
  name: string
  asset_type: string
  serial_number: string | null
  manufacturer: string | null
  model_name: string | null
  purchase_date: string | null
  status: string
  notes: string | null
  current_assignment: OperationsAssetAssignmentRecord | null
  assignment_history: OperationsAssetAssignmentRecord[]
  created_at: string | null
  updated_at: string | null
}

export interface OperationsLifecycleStatusRecord {
  employee: {
    id: number
    employee_code: string
    full_name: string
    email: string | null
    date_of_joining: string | null
    department: string | null
    designation: string | null
  }
  lifecycle_type: OperationsLifecycleType
  summary: {
    total_count: number
    closed_count: number
    incomplete_count: number
    progress_percentage: number
    is_complete: boolean
  }
}

export interface OperationsLifecycleTaskRecord {
  id: number
  employee_id: number
  lifecycle_type: OperationsLifecycleType
  template_id: number | null
  title: string
  category: string
  task_type: string | null
  assignee_type: string
  assigned_to_user_id: number | null
  assigned_to_user_name: string | null
  requires_approval: boolean
  approval_workflow_key: string | null
  workflow_instance_id: number | null
  status: string
  sort_order: number
  due_date: string | null
  due_state: string
  completed_at: string | null
  completed_by_user_id: number | null
  latest_action_by_user_id: number | null
  approved_at: string | null
  notes: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsLifecycleTaskCollection {
  items: OperationsLifecycleTaskRecord[]
  summary: {
    total_count: number
    completed_count: number
    skipped_count: number
    pending_count: number
    in_progress_count: number
    awaiting_approval_count: number
    changes_requested_count: number
    rejected_count: number
    incomplete_count: number
    progress_percentage: number
    is_complete: boolean
  }
  lifecycle_type: OperationsLifecycleType
}

export interface OperationsIntegrationSystemRecord {
  key: string
  name: string
  description: string
  directions: string[]
}

export interface OperationsIntegrationEventRecord {
  key: string
  name: string
  description: string
  entity_type: string
  directions: string[]
  systems: string[]
}

export interface OperationsIntegrationConnectionRecord {
  id: number
  system_key: string
  version: string
  name: string
  direction: string
  status: string
  auth_mode: string
  endpoint_url: string | null
  description: string | null
  scopes: string[]
  settings: Record<string, unknown>
  active_subscription_count?: number
  last_synced_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsIntegrationSubscriptionRecord {
  id: number
  subscription_key: string
  integration_connection_id: number
  version: string
  event_key: string
  direction: string
  status: string
  endpoint_url: string | null
  secret_preview: string | null
  custom_headers: Record<string, unknown>
  filter_rules: Record<string, unknown>
  connection: {
    id: number | null
    system_key: string | null
    name: string | null
    status: string | null
  } | null
  last_delivery_at: string | null
  last_received_at: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsIntegrationSyncErrorRecord {
  id: number
  attempt_number: number
  error_code: string | null
  error_message: string
  request_payload: Record<string, unknown>
  response_payload: Record<string, unknown>
  request_headers: Record<string, unknown>
  response_headers: Record<string, unknown>
  occurred_at: string | null
  resolved_at: string | null
}

export interface OperationsIntegrationSyncJobRecord {
  id: number
  job_uuid: string
  version: string
  system_key: string
  event_key: string
  direction: string
  status: string
  monitoring_state: string
  trigger_source: string
  entity_type: string | null
  entity_id: string | null
  request_payload: Record<string, unknown>
  response_payload: Record<string, unknown>
  request_headers: Record<string, unknown>
  response_headers: Record<string, unknown>
  attempts_count: number
  last_attempt_at: string | null
  queued_at: string | null
  started_at: string | null
  completed_at: string | null
  failed_at: string | null
  retried_at: string | null
  last_error: string | null
  can_retry: boolean
  connection: {
    id: number | null
    system_key: string | null
    name: string | null
    status: string | null
  } | null
  subscription: {
    id: number | null
    subscription_key: string | null
    event_key: string | null
    direction: string | null
    status: string | null
  } | null
  errors: OperationsIntegrationSyncErrorRecord[]
  created_at: string | null
  updated_at: string | null
}

export interface OperationsReleaseGateCheckRecord {
  key: string
  label: string
  status: string
  command: string
}

export interface OperationsReleaseGateRecord {
  key: string
  name: string
  category: string
  status: string
  blocking: boolean
  owner_role: string
  workflow_name: string
  workflow_path: string
  summary: string
  last_run_at: string | null
  required_for: string[]
  artifact_refs: string[]
  check_count: number
  failing_check_count: number
  checks: OperationsReleaseGateCheckRecord[]
}

export interface OperationsReleaseEnvironmentRecord {
  key: string
  name: string
  status: string
  manual_approval_required: boolean
  required_gate_keys: string[]
  required_gate_count: number
  blocking_gate_count: number
  blocked_reason: string | null
}

export interface OperationsReleasePolicyRecord {
  protected_branch: string
  promotion_rule: string
  required_workflow_names: string[]
  reviewer_roles: string[]
  artifact_paths: string[]
}

export interface OperationsReleaseSummaryRecord {
  total_gate_count: number
  blocking_gate_count: number
  passing_gate_count: number
  pending_gate_count: number
  warning_gate_count: number
  blocked_environment_count: number
  protected_environment_count: number
}

export interface OperationsReleaseReadinessAreaItemRecord {
  key: string
  label: string
  status: string
  owner_role: string
  summary: string
  last_reviewed_at: string | null
  artifact_refs: string[]
}

export interface OperationsReleaseReadinessAreaRecord {
  key: string
  name: string
  status: string
  source: string
  owner_role: string
  summary: string
  evidence_requirements: string[]
  artifact_refs: string[]
  check_count: number
  blocking_item_count: number
  attention_item_count: number
  last_reviewed_at: string | null
  items: OperationsReleaseReadinessAreaItemRecord[]
}

export interface OperationsReleaseReadinessBlockerRecord {
  key: string
  area_key: string | null
  area_name: string | null
  title: string
  status: string
  owner_role: string
  source: string
  summary: string
  artifact_refs: string[]
}

export interface OperationsReleaseReadinessRunbookRecord {
  key: string
  name: string
  path: string
  owner_role: string
  summary: string
  when_to_use: string
}

export interface OperationsReleaseReadinessDecisionBlockerRecord {
  area_key: string | null
  title: string
  owner_role: string
  status: string
  notes: string | null
}

export interface OperationsReleaseReadinessChecklistSnapshotRecord {
  key: string
  name: string
  status: string
  blocking_item_count: number
  attention_item_count: number
  last_reviewed_at: string | null
}

export interface OperationsReleaseReadinessDecisionRecord {
  id: number
  release_window_label: string
  target_environment: string
  decision_status: string
  summary: string
  blockers: OperationsReleaseReadinessDecisionBlockerRecord[]
  artifact_refs: string[]
  checklist_snapshot: OperationsReleaseReadinessChecklistSnapshotRecord[]
  decision_notes: string | null
  decided_at: string | null
  decided_by_user_id: number | null
  decided_by_name: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsReleaseReadinessRecommendationRecord {
  status: string
  summary: string
}

export interface OperationsReleaseReadinessPolicyRecord {
  review_cadence: string
  decision_owner_roles: string[]
  target_environments: string[]
  artifact_refs: string[]
}

export interface OperationsReleaseReadinessSummaryRecord {
  total_area_count: number
  ready_area_count: number
  attention_area_count: number
  blocked_area_count: number
  blocker_count: number
  runbook_count: number
  decision_count: number
  latest_decision_at: string | null
}

export interface OperationsReleaseReadinessOverviewRecord {
  summary: OperationsReleaseReadinessSummaryRecord
  policy: OperationsReleaseReadinessPolicyRecord
  recommendation: OperationsReleaseReadinessRecommendationRecord
  areas: OperationsReleaseReadinessAreaRecord[]
  blockers: OperationsReleaseReadinessBlockerRecord[]
  runbooks: OperationsReleaseReadinessRunbookRecord[]
  latest_decision: OperationsReleaseReadinessDecisionRecord | null
  decision_history: OperationsReleaseReadinessDecisionRecord[]
}

export interface OperationsObservabilitySummaryRecord {
  service_count: number
  healthy_service_count: number
  degraded_service_count: number
  critical_service_count: number
  active_alert_count: number
  routed_alert_count: number
  monitored_workflow_count: number
  monitored_integration_count: number
  release_critical_coverage_count: number
}

export interface OperationsObservabilityTelemetryRecord {
  health_endpoint: string
  default_log_channel: string
  slack_alert_channel: string | null
  dashboard_refresh_minutes: number
  required_release_workflows: string[]
}

export interface OperationsObservabilityServiceMetricRecord {
  key: string
  label: string
  value: number
  threshold: number | null
  unit: string
  status: string
}

export interface OperationsObservabilityServiceRecord {
  key: string
  name: string
  category: string
  owner_role: string
  status: string
  summary: string
  signal_keys: string[]
  alert_count: number
  metric_count: number
  metrics: OperationsObservabilityServiceMetricRecord[]
}

export interface OperationsObservabilitySignalRecord {
  key: string
  name: string
  category: string
  service_key: string
  status: string
  severity: string | null
  owner_role: string
  value: number
  threshold: number | null
  unit: string
  summary: string
  observed_at: string
  route_key: string | null
  route_name: string | null
  route_channels: string[]
  drill_in_label: string
  drill_in_path: string
}

export interface OperationsObservabilityAlertRecord {
  key: string
  title: string
  severity: string
  service_key: string
  service_name: string
  signal_key: string
  status: string
  owner_role: string
  route_key: string | null
  route_name: string | null
  channels: string[]
  summary: string
  started_at: string
}

export interface OperationsObservabilityAlertRouteRecord {
  key: string
  severity: string
  name: string
  owner_team: string
  channels: string[]
  initial_response_minutes: number
  escalation_minutes: number
}

export interface OperationsObservabilityCoverageItemRecord {
  key: string
  name: string
  area: string
  owner_role: string
  coverage_state: string
  monitored_entity_count: number
  issue_count: number
  signal_keys: string[]
  summary: string
}

export interface OperationsObservabilityOverviewRecord {
  summary: OperationsObservabilitySummaryRecord
  telemetry: OperationsObservabilityTelemetryRecord
  services: OperationsObservabilityServiceRecord[]
  signals: OperationsObservabilitySignalRecord[]
  alerts: OperationsObservabilityAlertRecord[]
  alert_routes: OperationsObservabilityAlertRouteRecord[]
  coverage: {
    workflows: OperationsObservabilityCoverageItemRecord[]
    integrations: OperationsObservabilityCoverageItemRecord[]
    release_critical: OperationsObservabilityCoverageItemRecord[]
  }
}

export interface OperationsResilienceSummaryRecord {
  total_scenario_count: number
  ready_scenario_count: number
  attention_scenario_count: number
  failed_scenario_count: number
  overdue_scenario_count: number
  validation_run_count: number
  latest_validation_at: string | null
}

export interface OperationsResiliencePolicyRecord {
  primary_region: string
  secondary_region: string
  backup_cadence: string
  restore_validation_cadence: string
  dr_drill_cadence: string
  retention_policy: string
  encryption_posture: string
  coverage_scope: string
  default_rpo_minutes: number
  default_rto_minutes: number
  artifact_refs: string[]
}

export interface OperationsResilienceRunbookStepRecord {
  key: string
  name: string
  sequence: number
  owner_role: string
  objective: string
  evidence_requirements: string[]
}

export interface OperationsResilienceValidationRunRecord {
  id: number
  scenario_key: string
  scenario_name: string
  scenario_type: string
  environment: string
  status: string
  recovery_point_actual_minutes: number | null
  recovery_time_actual_minutes: number | null
  evidence_refs: string[]
  notes: string | null
  started_at: string | null
  completed_at: string | null
  executed_by_user_id: number | null
  executed_by_name: string | null
  created_at: string | null
  updated_at: string | null
}

export interface OperationsResilienceScenarioRecord {
  key: string
  name: string
  scenario_type: string
  environment: string
  owner_role: string
  cadence_days: number
  recovery_point_objective_minutes: number
  recovery_time_objective_minutes: number
  status: string
  summary: string
  overdue: boolean
  blocked_reason: string | null
  last_validated_at: string | null
  next_validation_due_at: string | null
  evidence_requirements: string[]
  latest_run: OperationsResilienceValidationRunRecord | null
}

export interface OperationsResilienceOverviewRecord {
  summary: OperationsResilienceSummaryRecord
  policy: OperationsResiliencePolicyRecord
  scenarios: OperationsResilienceScenarioRecord[]
  runbook: OperationsResilienceRunbookStepRecord[]
  validation_runs: OperationsResilienceValidationRunRecord[]
}

export interface OperationsWorkspaceData {
  documentCategories: OperationsDocumentCategoryRecord[]
  documents: OperationsDocumentRecord[]
  assetCategories: OperationsAssetCategoryRecord[]
  assets: OperationsAssetRecord[]
  employees: EmployeeRecord[]
  lifecycle: {
    onboarding: OperationsLifecycleStatusRecord[]
    offboarding: OperationsLifecycleStatusRecord[]
  }
  integrations: {
    systems: OperationsIntegrationSystemRecord[]
    events: OperationsIntegrationEventRecord[]
    connections: OperationsIntegrationConnectionRecord[]
    subscriptions: OperationsIntegrationSubscriptionRecord[]
    syncJobs: OperationsIntegrationSyncJobRecord[]
    syncJobMeta: OperationsPagedMeta
  }
  release: {
    summary: OperationsReleaseSummaryRecord
    policy: OperationsReleasePolicyRecord
    gates: OperationsReleaseGateRecord[]
    environments: OperationsReleaseEnvironmentRecord[]
  }
  releaseReadiness: OperationsReleaseReadinessOverviewRecord
  resilience: OperationsResilienceOverviewRecord
  observability: OperationsObservabilityOverviewRecord
  lifecycleTaskDetails?: Partial<
    Record<
      number,
      {
        onboarding?: OperationsLifecycleTaskCollection
        offboarding?: OperationsLifecycleTaskCollection
      }
    >
  >
}

export interface DocumentCategoryFormValues {
  code: string
  name: string
  repository_scope: string
  default_visibility_scope: string
  retention_days: string
  allowed_role_names: string
  status: string
  notes: string
}

export interface AssetCategoryFormValues {
  code: string
  name: string
  status: string
  notes: string
}

export interface AssetFormValues {
  asset_category_id: string
  asset_tag: string
  name: string
  asset_type: string
  serial_number: string
  manufacturer: string
  model_name: string
  purchase_date: string
  status: string
  notes: string
}

export interface AssetAssignmentFormValues {
  employee_id: string
  assigned_at: string
  expected_return_date: string
  handover_condition: string
  assignment_notes: string
}

export interface AssetIssueFormValues {
  issued_at: string
  issue_notes: string
}

export interface AssetReturnFormValues {
  returned_at: string
  return_condition: string
  return_notes: string
}

export interface IntegrationConnectionFormValues {
  system_key: string
  name: string
  direction: string
  status: string
  auth_mode: string
  endpoint_url: string
  description: string
  scopes: string
}

export interface IntegrationSubscriptionFormValues {
  integration_connection_id: string
  event_key: string
  direction: string
  status: string
  endpoint_url: string
  secret: string
  custom_headers: string
  filter_rules: string
}

export interface IntegrationDispatchFormValues {
  event_key: string
  entity_type: string
  entity_id: string
  payload: string
}

export interface ResilienceValidationRunFormValues {
  scenario_key: string
  status: string
  recovery_point_actual_minutes: string
  recovery_time_actual_minutes: string
  evidence_refs: string
  notes: string
}

export interface ReleaseReadinessDecisionFormValues {
  release_window_label: string
  target_environment: string
  decision_status: string
  summary: string
  blockers: string
  artifact_refs: string
  decision_notes: string
}
