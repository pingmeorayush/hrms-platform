export type ReportingDashboardKey =
  | 'hr_overview'
  | 'manager_overview'
  | 'payroll_overview'
  | 'recruiter_overview'
  | 'leadership_overview'

export type ReportingSectionId =
  | 'overview'
  | 'explorer'
  | 'exports'
  | 'subscriptions'
  | 'workforce'
  | 'team'
  | 'payroll'
  | 'recruitment'
  | 'executive'

export type ReportingSavedViewShareScope = 'private' | 'roles' | 'company'
export type ReportingSavedViewStatus = 'active' | 'archived'
export type ReportingExportFormat = 'csv' | 'json'
export type ReportingExportExecutionMode = 'auto' | 'sync' | 'async'
export type ReportingExportStatus = 'queued' | 'processing' | 'completed' | 'failed' | 'expired'
export type ReportingSubscriptionStatus = 'active' | 'paused' | 'blocked' | 'revoked'
export type ReportingSubscriptionFrequency = 'daily' | 'weekly' | 'monthly'
export type ReportingDeliveryChannel = 'in_app_notification'
export type ReportingDeliveryTarget = 'owner_only'

export interface ReportingUserReference {
  id: number
  name: string
  email: string | null
}

export interface ReportingDrilldown {
  key: string
  label: string
  target_dataset_key: string | null
  description: string | null
}

export interface ReportingGovernedKpi {
  key: string | null
  name?: string | null
  formula?: string | null
  version?: number | null
  certification_status?: string | null
  source_references?: string[]
}

export interface ReportingGovernedDataset {
  key: string | null
  name?: string | null
  domain?: string | null
  version?: number | null
  certification_status?: string | null
  freshness_expectation_minutes?: number | null
  masking_posture?: Record<string, unknown> | Array<Record<string, unknown>>
}

export interface ReportingWidgetGovernance {
  kpi: ReportingGovernedKpi
  dataset: ReportingGovernedDataset
}

export interface ReportingWidgetFreshness {
  generated_at: string
  expires_at: string
  expectation_minutes: number
  is_stale: boolean
}

export interface ReportingDashboardWidget {
  key: string
  name: string
  widget_type: string
  description: string
  status: 'ready' | 'blocked'
  blocked_reason: string | null
  value: number | null
  unit: string
  drilldown: ReportingDrilldown | null
  governance: ReportingWidgetGovernance
  freshness: ReportingWidgetFreshness
}

export interface ReportingDashboardMeta {
  key: ReportingDashboardKey
  name: string
  persona: string
  description: string
}

export interface ReportingDashboardSnapshot {
  id: number | null
  cache_hit: boolean
  generated_at: string
  expires_at: string
  scope_signature: string
  source_signature: string
}

export interface ReportingDashboardFreshness {
  generated_at: string
  expires_at: string
  expectation_minutes: number
  is_stale: boolean
}

export interface ReportingDashboardRecord {
  dashboard: ReportingDashboardMeta
  snapshot: ReportingDashboardSnapshot
  freshness: ReportingDashboardFreshness
  widgets: ReportingDashboardWidget[]
}

export interface ReportingDashboardFailure {
  key: ReportingDashboardKey
  message: string
}

export interface ReportingActivityItem {
  id: string
  title: string
  detail: string
  meta: string
  tone: 'info' | 'warning' | 'danger' | 'success'
  path: string
}

export interface ReportingApprovedField {
  key: string
  label: string
  type: 'string' | 'number' | 'currency' | 'percentage' | 'date' | 'datetime' | 'boolean' | 'status'
  description: string | null
  sensitive: boolean
  masking_strategy: 'none' | 'redact' | 'partial' | 'aggregate_only' | null
}

export interface ReportingApprovedFilter {
  key: string
  label: string
  type: 'string' | 'number' | 'currency' | 'percentage' | 'date' | 'datetime' | 'boolean' | 'status' | 'entity'
  required: boolean
  operators: string[]
}

export interface ReportingDrilldownPathDefinition {
  key: string
  label: string
  target_dataset_key: string | null
  description: string | null
  allowed_filter_keys: string[]
}

export interface ReportingGovernanceState {
  version: number
  certification_status: string
  review_notes: string | null
  reviewed_by?: ReportingUserReference | null
  reviewed_at: string | null
  certified_by?: ReportingUserReference | null
  certified_at: string | null
}

export interface ReportingDatasetRecord {
  id: number
  key: string
  name: string
  domain: string
  description: string | null
  source_references: Array<Record<string, unknown>>
  grain: string
  approved_fields: ReportingApprovedField[]
  approved_filters: ReportingApprovedFilter[]
  drilldown_paths: ReportingDrilldownPathDefinition[]
  masking_posture: Record<string, unknown>
  freshness_expectation_minutes: number | null
  governance: ReportingGovernanceState
  created_at: string | null
  updated_at: string | null
}

export interface ReportingAppliedFilterState {
  operator: string
  value: unknown
}

export interface ReportingQueryRowDrilldown {
  key: string
  label: string
  target_dataset_key: string | null
  description: string | null
  filters: Record<string, unknown>
}

export type ReportingQueryRow = Record<string, unknown> & {
  drilldowns: ReportingQueryRowDrilldown[]
}

export interface ReportingQueryMeta {
  page: number
  per_page: number
  total: number
  last_page: number
  sort_by: string
  sort_direction: 'asc' | 'desc'
  drilldown_path: string | null
}

export interface ReportingQueryFreshness {
  generated_at: string | null
  expectation_minutes: number | null
}

export interface ReportingQueryVisibility {
  masked_field_keys: string[]
  hidden_field_keys: string[]
  drilldown_keys: string[]
}

export interface ReportingQueryResult {
  dataset: ReportingDatasetRecord
  items: ReportingQueryRow[]
  meta: ReportingQueryMeta
  filters: {
    available: ReportingApprovedFilter[]
    applied: Record<string, ReportingAppliedFilterState>
  }
  freshness: ReportingQueryFreshness
  visibility: ReportingQueryVisibility
}

export interface ReportingSavedViewRecord {
  id: number
  view_uuid: string
  name: string
  description: string | null
  status: ReportingSavedViewStatus
  share: {
    scope: ReportingSavedViewShareScope
    shared_role_names: string[]
  }
  dataset?: {
    id: number | null
    key: string | null
    name: string | null
    domain: string | null
  } | null
  owner?: ReportingUserReference | null
  query: {
    filters: Record<string, unknown>
    filter_operators: Record<string, string>
    sort_by: string | null
    sort_direction: 'asc' | 'desc' | null
    drilldown_path: string | null
  }
  presentation_preferences: Record<string, unknown>
  validation: {
    status: string
    reason: string | null
  }
  created_at: string | null
  updated_at: string | null
}

export interface ReportingExportRecord {
  id: number
  export_uuid: string
  status: ReportingExportStatus
  format: ReportingExportFormat
  execution_mode: ReportingExportExecutionMode
  delivery_target: string
  dataset?: {
    id: number | null
    key: string | null
    name: string | null
    domain: string | null
  } | null
  requested_by?: ReportingUserReference | null
  query: {
    filters: Record<string, unknown>
    filter_operators: Record<string, string>
    sort_by: string | null
    sort_direction: 'asc' | 'desc' | null
    drilldown_path: string | null
  }
  counts: {
    estimated_row_count: number | null
    exported_row_count: number | null
  }
  visibility: ReportingQueryVisibility
  freshness: ReportingQueryFreshness
  file: {
    name: string | null
    size_bytes: number | null
    checksum_sha256: string | null
    download_available: boolean
    download_url: string | null
  }
  retention: {
    expires_at: string | null
    is_expired: boolean
  }
  requested_at: string | null
  started_at: string | null
  completed_at: string | null
  failed_at: string | null
  notified_at: string | null
  last_error: string | null
  created_at: string | null
  updated_at: string | null
}

export interface ReportingSubscriptionRecord {
  id: number
  subscription_uuid: string
  name: string
  description: string | null
  status: ReportingSubscriptionStatus
  owner?: ReportingUserReference | null
  source: {
    dataset?: {
      id: number | null
      key: string | null
      name: string | null
      domain: string | null
    } | null
    saved_view?: {
      id: number | null
      view_uuid: string | null
      name: string | null
      status: string | null
    } | null
  }
  delivery: {
    channel: ReportingDeliveryChannel
    target: ReportingDeliveryTarget
    export_format: ReportingExportFormat
  }
  schedule: {
    frequency: ReportingSubscriptionFrequency
    timezone: string
    config: Record<string, unknown>
    next_delivery_at: string | null
  }
  query: {
    filters: Record<string, unknown>
    filter_operators: Record<string, string>
    sort_by: string | null
    sort_direction: 'asc' | 'desc' | null
    drilldown_path: string | null
  }
  validation: {
    status: string
    reason: string | null
  }
  last_delivery: {
    status: string | null
    error: string | null
    delivered_at: string | null
    report_export_id: number | null
  }
  created_at: string | null
  updated_at: string | null
}

export interface ReportingWorkspaceData {
  dashboards: Partial<Record<ReportingDashboardKey, ReportingDashboardRecord>>
  failures: ReportingDashboardFailure[]
  activity: ReportingActivityItem[]
  datasets: ReportingDatasetRecord[]
  savedViews: ReportingSavedViewRecord[]
  exports: ReportingExportRecord[]
  subscriptions: ReportingSubscriptionRecord[]
}

export interface ReportingExplorerQueryInput {
  datasetKey: string
  filters?: Record<string, unknown>
  filterOperators?: Record<string, string>
  sortBy?: string | null
  sortDirection?: 'asc' | 'desc' | null
  drilldownPath?: string | null
  page?: number
  perPage?: number
}

export interface ReportingSavedViewInput {
  dataset_key: string
  name: string
  description?: string | null
  share_scope?: ReportingSavedViewShareScope
  shared_role_names?: string[]
  filters?: Record<string, unknown>
  filter_operators?: Record<string, string>
  sort_by?: string | null
  sort_direction?: 'asc' | 'desc' | null
  drilldown_path?: string | null
  presentation_preferences?: Record<string, unknown>
}

export interface ReportingExportRequestInput {
  dataset_key: string
  format?: ReportingExportFormat
  execution_mode?: ReportingExportExecutionMode
  delivery_target?: 'requestor_download'
  filters?: Record<string, unknown>
  filter_operators?: Record<string, string>
  sort_by?: string | null
  sort_direction?: 'asc' | 'desc' | null
  drilldown_path?: string | null
}

export interface ReportingSubscriptionInput {
  dataset_key?: string
  saved_report_view_id?: number
  name: string
  description?: string | null
  delivery_channel?: ReportingDeliveryChannel
  delivery_target?: ReportingDeliveryTarget
  export_format?: ReportingExportFormat
  frequency: ReportingSubscriptionFrequency
  timezone: string
  schedule_config: {
    time_of_day: string
    weekday?: number
    day_of_month?: number
  }
  filters?: Record<string, unknown>
  filter_operators?: Record<string, string>
  sort_by?: string | null
  sort_direction?: 'asc' | 'desc' | null
  drilldown_path?: string | null
}

export interface ReportingSubscriptionUpdateInput extends Partial<ReportingSubscriptionInput> {
  status?: ReportingSubscriptionStatus
}
