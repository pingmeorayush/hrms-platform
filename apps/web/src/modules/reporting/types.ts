export type ReportingDashboardKey =
  | 'hr_overview'
  | 'manager_overview'
  | 'payroll_overview'
  | 'recruiter_overview'
  | 'leadership_overview'

export type ReportingSectionId =
  | 'overview'
  | 'workforce'
  | 'team'
  | 'payroll'
  | 'recruitment'
  | 'executive'

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

export interface ReportingWorkspaceData {
  dashboards: Partial<Record<ReportingDashboardKey, ReportingDashboardRecord>>
  failures: ReportingDashboardFailure[]
  activity: ReportingActivityItem[]
}
