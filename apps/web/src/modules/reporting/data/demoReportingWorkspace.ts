import type { AccessSnapshot } from '../../access/types'
import { getAccessibleReportingDashboardKeys } from '../config'
import type {
  ReportingActivityItem,
  ReportingDashboardKey,
  ReportingDashboardRecord,
  ReportingDatasetRecord,
  ReportingExportRecord,
  ReportingQueryRow,
  ReportingSavedViewRecord,
  ReportingSubscriptionRecord,
  ReportingWorkspaceData,
} from '../types'

export interface ReportingDemoWorkspaceState {
  workspace: ReportingWorkspaceData
  rowsByDatasetKey: Record<string, ReportingQueryRow[]>
}

function isoMinutesAgo(minutesAgo: number) {
  return new Date(Date.now() - minutesAgo * 60 * 1000).toISOString()
}

function isoMinutesAhead(minutesAhead: number) {
  return new Date(Date.now() + minutesAhead * 60 * 1000).toISOString()
}

function isoDaysAgo(daysAgo: number) {
  return new Date(Date.now() - daysAgo * 24 * 60 * 60 * 1000).toISOString()
}

function isoDaysAhead(daysAhead: number) {
  return new Date(Date.now() + daysAhead * 24 * 60 * 60 * 1000).toISOString()
}

function maskEmail(email: string) {
  const [localPart, domain] = email.split('@')
  const prefix = localPart.slice(0, 1)
  return `${prefix}${'*'.repeat(Math.max(localPart.length - 1, 3))}@${domain}`
}

function hasAnyPermission(snapshot: AccessSnapshot | null, permissions: string[]) {
  const granted = snapshot?.user.permissions ?? []
  return permissions.some((permission) => granted.includes(permission))
}

function canViewDatasetDomain(snapshot: AccessSnapshot | null, domain: string) {
  const granted = snapshot?.user.permissions ?? []
  const canReport = granted.some((permission) =>
    ['reporting.view', 'reporting.manage', 'reporting.certify'].includes(permission),
  )

  if (!canReport) {
    return false
  }

  switch (domain) {
    case 'workforce':
      return granted.some((permission) =>
        ['employee.view', 'employee.manage', 'organization.view', 'organization.manage'].includes(
          permission,
        ),
      )
    case 'attendance':
      return granted.some((permission) =>
        ['attendance.analytics.view', 'attendance.view', 'attendance.edit', 'attendance.approve'].includes(
          permission,
        ),
      )
    case 'leave':
      return granted.some((permission) =>
        ['leave.view', 'leave.approve', 'leave.manage_balance', 'leave.manage_policy'].includes(
          permission,
        ),
      )
    case 'payroll':
      return granted.some((permission) =>
        ['payroll.view', 'compensation.view', 'payroll.process', 'payroll.approve'].includes(permission),
      )
    case 'recruitment':
      return granted.some((permission) =>
        ['recruitment.view', 'recruitment.manage', 'recruitment.approve', 'recruitment.interview'].includes(
          permission,
        ),
      )
    case 'performance':
      return granted.some((permission) =>
        ['performance.view', 'performance.review', 'performance.manage', 'performance.calibrate'].includes(
          permission,
        ),
      )
    case 'learning':
      return granted.some((permission) =>
        ['learning.view', 'learning.complete', 'learning.assign', 'learning.manage'].includes(permission),
      )
    case 'operations':
      return granted.some((permission) =>
        ['document.view', 'asset.view', 'employee.view', 'employee.manage'].includes(permission),
      )
    case 'cross_domain':
      return granted.some((permission) => ['reporting.manage', 'reporting.certify'].includes(permission))
    default:
      return false
  }
}

function canSeeSensitiveWorkforceFields(snapshot: AccessSnapshot | null) {
  return hasAnyPermission(snapshot, ['reporting.manage', 'reporting.certify', 'employee.manage', 'organization.manage'])
}

function widget(
  key: string,
  name: string,
  value: number,
  description: string,
  options: {
    domain: string
    kpiKey: string
    datasetKey: string
    certificationStatus?: string
    freshnessMinutes?: number
    drilldownKey?: string | null
    maskingPosture?: Record<string, unknown>
    blockedReason?: string | null
  },
) {
  const freshnessMinutes = options.freshnessMinutes ?? 60

  return {
    key,
    name,
    widget_type: 'metric',
    description,
    status: options.blockedReason ? ('blocked' as const) : ('ready' as const),
    blocked_reason: options.blockedReason ?? null,
    value: options.blockedReason ? null : value,
    unit: 'count',
    drilldown: options.drilldownKey
      ? {
          key: options.drilldownKey,
          label: 'Open governed detail',
          target_dataset_key: options.datasetKey,
          description: 'Drill into the governed dataset view.',
        }
      : null,
    governance: {
      kpi: {
        key: options.kpiKey,
        name,
        formula: 'Governed aggregate',
        version: 2,
        certification_status: options.certificationStatus ?? 'certified',
        source_references: [`dataset:${options.datasetKey}`],
      },
      dataset: {
        key: options.datasetKey,
        name: options.datasetKey.replace(/_/g, ' '),
        domain: options.domain,
        version: 3,
        certification_status: options.certificationStatus ?? 'certified',
        freshness_expectation_minutes: freshnessMinutes,
        masking_posture: options.maskingPosture ?? {},
      },
    },
    freshness: {
      generated_at: isoMinutesAgo(14),
      expires_at: isoMinutesAhead(freshnessMinutes),
      expectation_minutes: freshnessMinutes,
      is_stale: false,
    },
  }
}

function buildDashboardFixtures(): Record<ReportingDashboardKey, ReportingDashboardRecord> {
  return {
    hr_overview: {
      dashboard: {
        key: 'hr_overview',
        name: 'HR overview',
        persona: 'hr',
        description: 'Operational HR dashboard for workforce, attendance, leave, and recruitment posture.',
      },
      snapshot: {
        id: 801,
        cache_hit: true,
        generated_at: isoMinutesAgo(12),
        expires_at: isoMinutesAhead(48),
        scope_signature: 'hr-overview-scope',
        source_signature: 'hr-overview-source',
      },
      freshness: {
        generated_at: isoMinutesAgo(12),
        expires_at: isoMinutesAhead(48),
        expectation_minutes: 60,
        is_stale: false,
      },
      widgets: [
        widget('active_headcount_card', 'Active headcount', 428, 'Current active workforce count.', {
          domain: 'workforce',
          kpiKey: 'active_headcount',
          datasetKey: 'workforce_headcount_snapshot',
          drilldownKey: 'employee_profile',
          maskingPosture: { employee_email: 'masked' },
        }),
        widget('attendance_exceptions_card', 'Attendance exceptions today', 17, 'Today’s attendance exceptions.', {
          domain: 'attendance',
          kpiKey: 'attendance_exceptions_today',
          datasetKey: 'attendance_daily_register',
        }),
        widget('pending_leave_requests_card', 'Pending leave requests', 9, 'Open leave approvals.', {
          domain: 'leave',
          kpiKey: 'pending_leave_requests',
          datasetKey: 'leave_request_register',
          drilldownKey: 'leave_request_detail',
        }),
        widget('active_candidates_card', 'Active candidates', 23, 'Current recruitment pipeline volume.', {
          domain: 'recruitment',
          kpiKey: 'active_candidates',
          datasetKey: 'recruitment_candidate_pipeline',
        }),
      ],
    },
    manager_overview: {
      dashboard: {
        key: 'manager_overview',
        name: 'Manager overview',
        persona: 'manager',
        description: 'Team-scoped dashboard for people managers across headcount, attendance, leave, and reviews.',
      },
      snapshot: {
        id: 802,
        cache_hit: true,
        generated_at: isoMinutesAgo(18),
        expires_at: isoMinutesAhead(42),
        scope_signature: 'manager-overview-scope',
        source_signature: 'manager-overview-source',
      },
      freshness: {
        generated_at: isoMinutesAgo(18),
        expires_at: isoMinutesAhead(42),
        expectation_minutes: 60,
        is_stale: false,
      },
      widgets: [
        widget('team_headcount_card', 'Team headcount', 14, 'Active employees within the visible team scope.', {
          domain: 'workforce',
          kpiKey: 'active_headcount',
          datasetKey: 'workforce_headcount_snapshot',
          drilldownKey: 'employee_profile',
          maskingPosture: {
            employee_email: 'masked',
            compensation_band: 'hidden',
          },
        }),
        widget('team_attendance_exceptions_card', 'Team attendance exceptions today', 3, 'Attendance exceptions within the team scope.', {
          domain: 'attendance',
          kpiKey: 'attendance_exceptions_today',
          datasetKey: 'attendance_daily_register',
        }),
        widget('team_pending_leave_requests_card', 'Pending team leave requests', 2, 'Leave approvals still in the manager queue.', {
          domain: 'leave',
          kpiKey: 'pending_leave_requests',
          datasetKey: 'leave_request_register',
          drilldownKey: 'leave_request_detail',
        }),
        widget('open_team_reviews_card', 'Open performance reviews', 5, 'Reviews still in motion for the visible team.', {
          domain: 'performance',
          kpiKey: 'open_performance_reviews',
          datasetKey: 'performance_review_status',
        }),
      ],
    },
    payroll_overview: {
      dashboard: {
        key: 'payroll_overview',
        name: 'Payroll overview',
        persona: 'payroll',
        description: 'Run-state dashboard for payroll operations and close readiness.',
      },
      snapshot: {
        id: 803,
        cache_hit: true,
        generated_at: isoMinutesAgo(20),
        expires_at: isoMinutesAhead(100),
        scope_signature: 'payroll-overview-scope',
        source_signature: 'payroll-overview-source',
      },
      freshness: {
        generated_at: isoMinutesAgo(20),
        expires_at: isoMinutesAhead(100),
        expectation_minutes: 120,
        is_stale: false,
      },
      widgets: [
        widget('payroll_runs_in_progress_card', 'Runs in progress', 2, 'Payroll runs currently in preparation, calculation, or approval.', {
          domain: 'payroll',
          kpiKey: 'active_payroll_runs',
          datasetKey: 'payroll_run_register',
        }),
        widget('payroll_runs_locked_card', 'Locked payroll runs', 1, 'Payroll runs locked and ready for downstream release.', {
          domain: 'payroll',
          kpiKey: 'locked_payroll_runs',
          datasetKey: 'payroll_run_register',
        }),
        widget('payroll_runs_blocked_card', 'Blocked payroll runs', 1, 'Payroll runs blocked or failed and needing intervention.', {
          domain: 'payroll',
          kpiKey: 'blocked_payroll_runs',
          datasetKey: 'payroll_run_register',
        }),
      ],
    },
    recruiter_overview: {
      dashboard: {
        key: 'recruiter_overview',
        name: 'Recruiter overview',
        persona: 'recruiter',
        description: 'Recruitment pipeline dashboard for active sourcing, interview, and offer movement.',
      },
      snapshot: {
        id: 804,
        cache_hit: true,
        generated_at: isoMinutesAgo(24),
        expires_at: isoMinutesAhead(156),
        scope_signature: 'recruiter-overview-scope',
        source_signature: 'recruiter-overview-source',
      },
      freshness: {
        generated_at: isoMinutesAgo(24),
        expires_at: isoMinutesAhead(156),
        expectation_minutes: 180,
        is_stale: false,
      },
      widgets: [
        widget('recruiter_active_candidates_card', 'Active candidates', 23, 'Candidates active in the visible sourcing pipeline.', {
          domain: 'recruitment',
          kpiKey: 'active_candidates',
          datasetKey: 'recruitment_candidate_pipeline',
        }),
        widget('interview_stage_candidates_card', 'Interview stage candidates', 8, 'Candidates currently in interview rounds.', {
          domain: 'recruitment',
          kpiKey: 'interview_stage_candidates',
          datasetKey: 'recruitment_candidate_pipeline',
        }),
        widget('offer_stage_candidates_card', 'Offer stage candidates', 4, 'Candidates currently progressing through offer closure.', {
          domain: 'recruitment',
          kpiKey: 'offer_stage_candidates',
          datasetKey: 'recruitment_candidate_pipeline',
        }),
      ],
    },
    leadership_overview: {
      dashboard: {
        key: 'leadership_overview',
        name: 'Leadership overview',
        persona: 'leadership',
        description: 'Executive operating dashboard across workforce, recruitment, learning, and performance.',
      },
      snapshot: {
        id: 805,
        cache_hit: true,
        generated_at: isoMinutesAgo(325),
        expires_at: isoMinutesAgo(45),
        scope_signature: 'leadership-overview-scope',
        source_signature: 'leadership-overview-source',
      },
      freshness: {
        generated_at: isoMinutesAgo(325),
        expires_at: isoMinutesAgo(45),
        expectation_minutes: 240,
        is_stale: true,
      },
      widgets: [
        widget('leadership_headcount_card', 'Enterprise active headcount', 428, 'Company-wide active headcount.', {
          domain: 'workforce',
          kpiKey: 'active_headcount',
          datasetKey: 'workforce_headcount_snapshot',
          drilldownKey: 'employee_profile',
        }),
        widget('leadership_active_candidates_card', 'Enterprise active candidates', 23, 'Company-wide active recruitment pipeline volume.', {
          domain: 'recruitment',
          kpiKey: 'active_candidates',
          datasetKey: 'recruitment_candidate_pipeline',
        }),
        widget('leadership_open_reviews_card', 'Open performance reviews', 41, 'Company-wide performance reviews in progress.', {
          domain: 'performance',
          kpiKey: 'open_performance_reviews',
          datasetKey: 'performance_review_status',
        }),
        widget('leadership_learning_overdue_card', 'Overdue learning assignments', 16, 'Mandatory learning items that are past due.', {
          domain: 'learning',
          kpiKey: 'overdue_learning_assignments',
          datasetKey: 'learning_assignment_targets',
          blockedReason: 'dataset_refresh_overdue',
        }),
      ],
    },
  }
}

function buildDatasets(): ReportingDatasetRecord[] {
  return [
    {
      id: 4001,
      key: 'workforce_headcount_snapshot',
      name: 'Workforce Headcount Snapshot',
      domain: 'workforce',
      description: 'Employee-level workforce roster for governed headcount and hierarchy reporting.',
      source_references: [{ module: 'employees', entity: 'employees', field: null, notes: 'Employee master source.' }],
      grain: 'employee',
      approved_fields: [
        { key: 'employee_code', label: 'Employee Code', type: 'string', description: 'Stable employee code.', sensitive: false, masking_strategy: null },
        { key: 'employee_name', label: 'Employee Name', type: 'string', description: 'Employee full name.', sensitive: false, masking_strategy: null },
        { key: 'employee_email', label: 'Employee Email', type: 'string', description: 'Primary work email.', sensitive: true, masking_strategy: 'partial' },
        { key: 'department_name', label: 'Department', type: 'string', description: 'Department name.', sensitive: false, masking_strategy: null },
        { key: 'designation_name', label: 'Designation', type: 'string', description: 'Designation name.', sensitive: false, masking_strategy: null },
        { key: 'manager_name', label: 'Manager', type: 'string', description: 'Reporting manager name.', sensitive: false, masking_strategy: null },
        { key: 'employment_status', label: 'Employment Status', type: 'status', description: 'Current employment status.', sensitive: false, masking_strategy: null },
        { key: 'date_of_joining', label: 'Date of Joining', type: 'date', description: 'Join date.', sensitive: false, masking_strategy: null },
      ],
      approved_filters: [
        { key: 'employee_code', label: 'Employee Code', type: 'string', required: false, operators: ['eq', 'contains'] },
        { key: 'department_name', label: 'Department', type: 'string', required: false, operators: ['eq', 'contains'] },
        { key: 'employment_status', label: 'Employment Status', type: 'status', required: false, operators: ['eq', 'in'] },
      ],
      drilldown_paths: [
        {
          key: 'employee_profile',
          label: 'Employee profile',
          target_dataset_key: 'workforce_headcount_snapshot',
          description: 'Re-open the employee-focused workforce lens.',
          allowed_filter_keys: ['employee_code', 'employment_status'],
        },
      ],
      masking_posture: {
        default_strategy: 'partial',
        sensitive_field_keys: ['employee_email'],
        notes: 'Mask employee email outside elevated workforce reporting roles.',
      },
      freshness_expectation_minutes: 60,
      governance: {
        version: 3,
        certification_status: 'certified',
        review_notes: 'Certified for workforce reporting.',
        reviewed_at: isoDaysAgo(10),
        certified_at: isoDaysAgo(7),
      },
      created_at: isoDaysAgo(30),
      updated_at: isoDaysAgo(7),
    },
    {
      id: 4002,
      key: 'attendance_daily_register',
      name: 'Attendance Daily Register',
      domain: 'attendance',
      description: 'Daily attendance capture and exception state by employee and date.',
      source_references: [{ module: 'attendance', entity: 'attendance_records', field: null, notes: 'Attendance transactional source.' }],
      grain: 'employee_day',
      approved_fields: [
        { key: 'employee_code', label: 'Employee Code', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'employee_name', label: 'Employee Name', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'attendance_date', label: 'Attendance Date', type: 'date', description: null, sensitive: false, masking_strategy: null },
        { key: 'primary_status', label: 'Primary Status', type: 'status', description: null, sensitive: false, masking_strategy: null },
        { key: 'worked_minutes', label: 'Worked Minutes', type: 'number', description: null, sensitive: false, masking_strategy: null },
        { key: 'late_minutes', label: 'Late Minutes', type: 'number', description: null, sensitive: false, masking_strategy: null },
        { key: 'department_name', label: 'Department', type: 'string', description: null, sensitive: false, masking_strategy: null },
      ],
      approved_filters: [
        { key: 'attendance_date', label: 'Attendance Date', type: 'date', required: false, operators: ['eq', 'gte', 'lte'] },
        { key: 'primary_status', label: 'Primary Status', type: 'status', required: false, operators: ['eq', 'in'] },
        { key: 'department_name', label: 'Department', type: 'string', required: false, operators: ['eq', 'contains'] },
      ],
      drilldown_paths: [],
      masking_posture: { default_strategy: 'none', sensitive_field_keys: [], notes: null },
      freshness_expectation_minutes: 90,
      governance: {
        version: 2,
        certification_status: 'certified',
        review_notes: 'Certified for attendance reporting.',
        reviewed_at: isoDaysAgo(8),
        certified_at: isoDaysAgo(8),
      },
      created_at: isoDaysAgo(28),
      updated_at: isoDaysAgo(8),
    },
    {
      id: 4003,
      key: 'leave_request_register',
      name: 'Leave Request Register',
      domain: 'leave',
      description: 'Leave requests by employee, type, and approval posture.',
      source_references: [{ module: 'leave', entity: 'leave_requests', field: null, notes: 'Leave request source.' }],
      grain: 'leave_request',
      approved_fields: [
        { key: 'employee_code', label: 'Employee Code', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'employee_name', label: 'Employee Name', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'leave_type_name', label: 'Leave Type', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'status', label: 'Status', type: 'status', description: null, sensitive: false, masking_strategy: null },
        { key: 'start_date', label: 'Start Date', type: 'date', description: null, sensitive: false, masking_strategy: null },
        { key: 'end_date', label: 'End Date', type: 'date', description: null, sensitive: false, masking_strategy: null },
        { key: 'total_days', label: 'Total Days', type: 'number', description: null, sensitive: false, masking_strategy: null },
        { key: 'department_name', label: 'Department', type: 'string', description: null, sensitive: false, masking_strategy: null },
      ],
      approved_filters: [
        { key: 'status', label: 'Status', type: 'status', required: false, operators: ['eq', 'in'] },
        { key: 'leave_type_name', label: 'Leave Type', type: 'string', required: false, operators: ['eq', 'contains'] },
        { key: 'start_date', label: 'Start Date', type: 'date', required: false, operators: ['eq', 'gte', 'lte'] },
      ],
      drilldown_paths: [
        {
          key: 'leave_request_detail',
          label: 'Leave request detail',
          target_dataset_key: 'leave_request_register',
          description: 'Focus the register on a specific leave request.',
          allowed_filter_keys: ['status'],
        },
      ],
      masking_posture: { default_strategy: 'none', sensitive_field_keys: [], notes: null },
      freshness_expectation_minutes: 120,
      governance: {
        version: 2,
        certification_status: 'certified',
        review_notes: 'Certified for leave operations.',
        reviewed_at: isoDaysAgo(9),
        certified_at: isoDaysAgo(9),
      },
      created_at: isoDaysAgo(28),
      updated_at: isoDaysAgo(9),
    },
    {
      id: 4004,
      key: 'payroll_run_register',
      name: 'Payroll Run Register',
      domain: 'payroll',
      description: 'Payroll run lifecycle, payout posture, and release readiness register.',
      source_references: [{ module: 'payroll', entity: 'payroll_runs', field: null, notes: 'Payroll run source.' }],
      grain: 'payroll_run',
      approved_fields: [
        { key: 'run_name', label: 'Run Name', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'period_name', label: 'Period', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'status', label: 'Status', type: 'status', description: null, sensitive: false, masking_strategy: null },
        { key: 'employee_count', label: 'Employee Count', type: 'number', description: null, sensitive: false, masking_strategy: null },
        { key: 'net_payroll', label: 'Net Payroll', type: 'currency', description: null, sensitive: true, masking_strategy: 'aggregate_only' },
        { key: 'blocked_items_count', label: 'Blocked Items', type: 'number', description: null, sensitive: false, masking_strategy: null },
        { key: 'payslip_status', label: 'Payslip Status', type: 'status', description: null, sensitive: false, masking_strategy: null },
      ],
      approved_filters: [
        { key: 'status', label: 'Status', type: 'status', required: false, operators: ['eq', 'in'] },
        { key: 'period_name', label: 'Period', type: 'string', required: false, operators: ['eq', 'contains'] },
      ],
      drilldown_paths: [],
      masking_posture: {
        default_strategy: 'aggregate_only',
        sensitive_field_keys: ['net_payroll'],
        notes: 'Hide net payroll from non-payroll reporting sessions.',
      },
      freshness_expectation_minutes: 180,
      governance: {
        version: 2,
        certification_status: 'certified',
        review_notes: 'Certified for payroll reporting.',
        reviewed_at: isoDaysAgo(6),
        certified_at: isoDaysAgo(6),
      },
      created_at: isoDaysAgo(22),
      updated_at: isoDaysAgo(6),
    },
    {
      id: 4005,
      key: 'recruitment_candidate_pipeline',
      name: 'Recruitment Candidate Pipeline',
      domain: 'recruitment',
      description: 'Candidate pipeline state across requisitions, stages, and offers.',
      source_references: [{ module: 'recruitment', entity: 'candidates', field: null, notes: 'Candidate pipeline source.' }],
      grain: 'candidate',
      approved_fields: [
        { key: 'candidate_code', label: 'Candidate Code', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'candidate_name', label: 'Candidate Name', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'requisition_code', label: 'Requisition', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'current_stage', label: 'Current Stage', type: 'status', description: null, sensitive: false, masking_strategy: null },
        { key: 'status', label: 'Status', type: 'status', description: null, sensitive: false, masking_strategy: null },
        { key: 'recruiter_name', label: 'Recruiter', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'offer_status', label: 'Offer Status', type: 'status', description: null, sensitive: false, masking_strategy: null },
      ],
      approved_filters: [
        { key: 'current_stage', label: 'Stage', type: 'status', required: false, operators: ['eq', 'in'] },
        { key: 'status', label: 'Status', type: 'status', required: false, operators: ['eq', 'in'] },
        { key: 'requisition_code', label: 'Requisition', type: 'string', required: false, operators: ['eq', 'contains'] },
      ],
      drilldown_paths: [],
      masking_posture: { default_strategy: 'none', sensitive_field_keys: [], notes: null },
      freshness_expectation_minutes: 180,
      governance: {
        version: 2,
        certification_status: 'certified',
        review_notes: 'Certified for recruitment analytics.',
        reviewed_at: isoDaysAgo(5),
        certified_at: isoDaysAgo(5),
      },
      created_at: isoDaysAgo(20),
      updated_at: isoDaysAgo(5),
    },
    {
      id: 4006,
      key: 'performance_review_status',
      name: 'Performance Review Status',
      domain: 'performance',
      description: 'Performance review progress across cycles, managers, and publication state.',
      source_references: [{ module: 'performance', entity: 'performance_reviews', field: null, notes: 'Performance review runtime source.' }],
      grain: 'review',
      approved_fields: [
        { key: 'employee_code', label: 'Employee Code', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'employee_name', label: 'Employee Name', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'cycle_name', label: 'Cycle', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'review_status', label: 'Review Status', type: 'status', description: null, sensitive: false, masking_strategy: null },
        { key: 'manager_name', label: 'Manager', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'due_on', label: 'Due On', type: 'date', description: null, sensitive: false, masking_strategy: null },
        { key: 'calibration_status', label: 'Calibration', type: 'status', description: null, sensitive: false, masking_strategy: null },
      ],
      approved_filters: [
        { key: 'review_status', label: 'Review Status', type: 'status', required: false, operators: ['eq', 'in'] },
        { key: 'cycle_name', label: 'Cycle', type: 'string', required: false, operators: ['eq', 'contains'] },
      ],
      drilldown_paths: [],
      masking_posture: { default_strategy: 'none', sensitive_field_keys: [], notes: null },
      freshness_expectation_minutes: 240,
      governance: {
        version: 2,
        certification_status: 'certified',
        review_notes: 'Certified for performance reporting.',
        reviewed_at: isoDaysAgo(4),
        certified_at: isoDaysAgo(4),
      },
      created_at: isoDaysAgo(18),
      updated_at: isoDaysAgo(4),
    },
    {
      id: 4007,
      key: 'learning_assignment_targets',
      name: 'Learning Assignment Targets',
      domain: 'learning',
      description: 'Learning due-state and renewal posture by employee target.',
      source_references: [{ module: 'learning', entity: 'learning_assignment_targets', field: null, notes: 'Learning target source.' }],
      grain: 'assignment_target',
      approved_fields: [
        { key: 'employee_code', label: 'Employee Code', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'employee_name', label: 'Employee Name', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'item_title', label: 'Learning Item', type: 'string', description: null, sensitive: false, masking_strategy: null },
        { key: 'due_on', label: 'Due On', type: 'date', description: null, sensitive: false, masking_strategy: null },
        { key: 'due_state', label: 'Due State', type: 'status', description: null, sensitive: false, masking_strategy: null },
        { key: 'renewal_posture', label: 'Renewal', type: 'status', description: null, sensitive: false, masking_strategy: null },
        { key: 'evidence_present', label: 'Evidence Present', type: 'boolean', description: null, sensitive: false, masking_strategy: null },
        { key: 'department_name', label: 'Department', type: 'string', description: null, sensitive: false, masking_strategy: null },
      ],
      approved_filters: [
        { key: 'due_state', label: 'Due State', type: 'status', required: false, operators: ['eq', 'in'] },
        { key: 'renewal_posture', label: 'Renewal Posture', type: 'status', required: false, operators: ['eq', 'in'] },
        { key: 'department_name', label: 'Department', type: 'string', required: false, operators: ['eq', 'contains'] },
      ],
      drilldown_paths: [],
      masking_posture: { default_strategy: 'none', sensitive_field_keys: [], notes: null },
      freshness_expectation_minutes: 240,
      governance: {
        version: 2,
        certification_status: 'certified',
        review_notes: 'Certified for learning compliance reporting.',
        reviewed_at: isoDaysAgo(4),
        certified_at: isoDaysAgo(4),
      },
      created_at: isoDaysAgo(18),
      updated_at: isoDaysAgo(4),
    },
  ]
}

function buildRowsByDatasetKey(snapshot: AccessSnapshot | null): Record<string, ReportingQueryRow[]> {
  const showSensitiveWorkforceFields = canSeeSensitiveWorkforceFields(snapshot)

  return {
    workforce_headcount_snapshot: [
      {
        employee_code: 'EMP-M001',
        employee_name: 'Karan Manager',
        employee_email: showSensitiveWorkforceFields ? 'karan.manager@example.com' : maskEmail('karan.manager@example.com'),
        department_name: 'Operations',
        designation_name: 'Manager',
        manager_name: 'No manager',
        employment_status: 'active',
        date_of_joining: '2023-02-11',
        drilldowns: [
          {
            key: 'employee_profile',
            label: 'Employee profile',
            target_dataset_key: 'workforce_headcount_snapshot',
            description: 'Focus on the selected employee.',
            filters: { employee_code: 'EMP-M001', employment_status: 'active' },
          },
        ],
      },
      {
        employee_code: 'EMP-R001',
        employee_name: 'Diya Report',
        employee_email: showSensitiveWorkforceFields ? 'diya.report@example.com' : maskEmail('diya.report@example.com'),
        department_name: 'Operations',
        designation_name: 'Senior Analyst',
        manager_name: 'Karan Manager',
        employment_status: 'active',
        date_of_joining: '2024-01-08',
        drilldowns: [
          {
            key: 'employee_profile',
            label: 'Employee profile',
            target_dataset_key: 'workforce_headcount_snapshot',
            description: 'Focus on the selected employee.',
            filters: { employee_code: 'EMP-R001', employment_status: 'active' },
          },
        ],
      },
      {
        employee_code: 'EMP-O001',
        employee_name: 'Omar Other',
        employee_email: showSensitiveWorkforceFields ? 'omar.other@example.com' : maskEmail('omar.other@example.com'),
        department_name: 'Shared Services',
        designation_name: 'Specialist',
        manager_name: 'Leena Shah',
        employment_status: 'active',
        date_of_joining: '2022-10-18',
        drilldowns: [
          {
            key: 'employee_profile',
            label: 'Employee profile',
            target_dataset_key: 'workforce_headcount_snapshot',
            description: 'Focus on the selected employee.',
            filters: { employee_code: 'EMP-O001', employment_status: 'active' },
          },
        ],
      },
    ],
    attendance_daily_register: [
      {
        employee_code: 'EMP-M001',
        employee_name: 'Karan Manager',
        attendance_date: '2026-06-26',
        primary_status: 'present',
        worked_minutes: 511,
        late_minutes: 0,
        department_name: 'Operations',
        drilldowns: [],
      },
      {
        employee_code: 'EMP-R001',
        employee_name: 'Diya Report',
        attendance_date: '2026-06-26',
        primary_status: 'late',
        worked_minutes: 462,
        late_minutes: 17,
        department_name: 'Operations',
        drilldowns: [],
      },
      {
        employee_code: 'EMP-O001',
        employee_name: 'Omar Other',
        attendance_date: '2026-06-26',
        primary_status: 'present',
        worked_minutes: 488,
        late_minutes: 0,
        department_name: 'Shared Services',
        drilldowns: [],
      },
    ],
    leave_request_register: [
      {
        employee_code: 'EMP-R001',
        employee_name: 'Diya Report',
        leave_type_name: 'Casual Leave',
        status: 'pending',
        start_date: '2026-06-29',
        end_date: '2026-06-29',
        total_days: 1,
        department_name: 'Operations',
        drilldowns: [
          {
            key: 'leave_request_detail',
            label: 'Leave request detail',
            target_dataset_key: 'leave_request_register',
            description: 'Focus on this leave request posture.',
            filters: { status: 'pending' },
          },
        ],
      },
      {
        employee_code: 'EMP-M001',
        employee_name: 'Karan Manager',
        leave_type_name: 'Privilege Leave',
        status: 'approved',
        start_date: '2026-06-20',
        end_date: '2026-06-20',
        total_days: 1,
        department_name: 'Operations',
        drilldowns: [
          {
            key: 'leave_request_detail',
            label: 'Leave request detail',
            target_dataset_key: 'leave_request_register',
            description: 'Focus on this leave request posture.',
            filters: { status: 'approved' },
          },
        ],
      },
    ],
    payroll_run_register: [
      {
        run_name: 'June 2026 Monthly Run',
        period_name: 'Jun 2026',
        status: 'locked',
        employee_count: 428,
        net_payroll: hasAnyPermission(snapshot, ['payroll.view', 'compensation.view', 'payroll.process', 'payroll.approve']) ? 18450000 : null,
        blocked_items_count: 0,
        payslip_status: 'generated',
        drilldowns: [],
      },
      {
        run_name: 'July 2026 Monthly Run',
        period_name: 'Jul 2026',
        status: 'blocked',
        employee_count: 431,
        net_payroll: hasAnyPermission(snapshot, ['payroll.view', 'compensation.view', 'payroll.process', 'payroll.approve']) ? 18790000 : null,
        blocked_items_count: 3,
        payslip_status: 'not_generated',
        drilldowns: [],
      },
    ],
    recruitment_candidate_pipeline: [
      {
        candidate_code: 'CAND-401',
        candidate_name: 'Priya Nair',
        requisition_code: 'REQ-2401',
        current_stage: 'interview',
        status: 'active',
        recruiter_name: 'Sonia Menon',
        offer_status: 'draft',
        drilldowns: [],
      },
      {
        candidate_code: 'CAND-402',
        candidate_name: 'Rahul Sethi',
        requisition_code: 'REQ-2401',
        current_stage: 'offer',
        status: 'active',
        recruiter_name: 'Sonia Menon',
        offer_status: 'sent',
        drilldowns: [],
      },
      {
        candidate_code: 'CAND-403',
        candidate_name: 'Maya Kulkarni',
        requisition_code: 'REQ-2402',
        current_stage: 'screening',
        status: 'active',
        recruiter_name: 'Sonia Menon',
        offer_status: 'draft',
        drilldowns: [],
      },
    ],
    performance_review_status: [
      {
        employee_code: 'EMP-R001',
        employee_name: 'Diya Report',
        cycle_name: 'FY26 Mid-Year Review',
        review_status: 'manager_review',
        manager_name: 'Karan Manager',
        due_on: '2026-07-05',
        calibration_status: 'not_started',
        drilldowns: [],
      },
      {
        employee_code: 'EMP-O001',
        employee_name: 'Omar Other',
        cycle_name: 'FY26 Mid-Year Review',
        review_status: 'self_assessment',
        manager_name: 'Leena Shah',
        due_on: '2026-07-03',
        calibration_status: 'not_started',
        drilldowns: [],
      },
    ],
    learning_assignment_targets: [
      {
        employee_code: 'EMP-R001',
        employee_name: 'Diya Report',
        item_title: 'Information Security Refresher',
        due_on: '2026-06-25',
        due_state: 'overdue',
        renewal_posture: 'overdue',
        evidence_present: false,
        department_name: 'Operations',
        drilldowns: [],
      },
      {
        employee_code: 'EMP-M001',
        employee_name: 'Karan Manager',
        item_title: 'Leadership Essentials',
        due_on: '2026-07-02',
        due_state: 'upcoming',
        renewal_posture: 'current',
        evidence_present: true,
        department_name: 'Operations',
        drilldowns: [],
      },
    ],
  }
}

function buildSavedViews(
  datasets: ReportingDatasetRecord[],
  snapshot: AccessSnapshot | null,
): ReportingSavedViewRecord[] {
  const datasetByKey = new Map(datasets.map((dataset) => [dataset.key, dataset]))
  const userId = snapshot?.user.id ?? 0
  const roles = snapshot?.user.roles ?? []
  const canManageAll = hasAnyPermission(snapshot, ['reporting.manage', 'reporting.certify'])

  const views: ReportingSavedViewRecord[] = [
    {
      id: 9201,
      view_uuid: 'view-team-workforce',
      name: 'Team Active Workforce',
      description: 'Shared manager lens for active workforce coverage.',
      status: 'active',
      share: { scope: 'roles', shared_role_names: ['manager'] },
      dataset: datasetByKey.get('workforce_headcount_snapshot')
        ? {
            id: datasetByKey.get('workforce_headcount_snapshot')!.id,
            key: 'workforce_headcount_snapshot',
            name: datasetByKey.get('workforce_headcount_snapshot')!.name,
            domain: 'workforce',
          }
        : null,
      owner: { id: 2, name: 'Tenant Administrator', email: 'tenant.admin@phoenixhrms.test' },
      query: {
        filters: { employment_status: 'active', department_name: 'Operations' },
        filter_operators: { employment_status: 'eq', department_name: 'eq' },
        sort_by: 'employee_code',
        sort_direction: 'asc',
        drilldown_path: 'employee_profile',
      },
      presentation_preferences: { visible_columns: ['employee_code', 'employee_name', 'employee_email', 'department_name'] },
      validation: { status: 'valid', reason: null },
      created_at: isoDaysAgo(5),
      updated_at: isoDaysAgo(1),
    },
    {
      id: 9202,
      view_uuid: 'view-attendance-exceptions',
      name: 'Attendance Exceptions Today',
      description: 'Current day attendance exception queue.',
      status: 'active',
      share: { scope: 'private', shared_role_names: [] },
      dataset: datasetByKey.get('attendance_daily_register')
        ? {
            id: datasetByKey.get('attendance_daily_register')!.id,
            key: 'attendance_daily_register',
            name: datasetByKey.get('attendance_daily_register')!.name,
            domain: 'attendance',
          }
        : null,
      owner: { id: 3, name: 'Manager Reviewer', email: 'manager@phoenixhrms.test' },
      query: {
        filters: { primary_status: 'late', attendance_date: '2026-06-26' },
        filter_operators: { primary_status: 'eq', attendance_date: 'eq' },
        sort_by: 'attendance_date',
        sort_direction: 'desc',
        drilldown_path: null,
      },
      presentation_preferences: { visible_columns: ['employee_code', 'employee_name', 'attendance_date', 'primary_status', 'late_minutes'] },
      validation: { status: 'valid', reason: null },
      created_at: isoDaysAgo(3),
      updated_at: isoDaysAgo(1),
    },
    {
      id: 9203,
      view_uuid: 'view-payroll-close-watch',
      name: 'Payroll Close Watch',
      description: 'Company payroll release readiness watchlist.',
      status: 'active',
      share: { scope: 'company', shared_role_names: [] },
      dataset: datasetByKey.get('payroll_run_register')
        ? {
            id: datasetByKey.get('payroll_run_register')!.id,
            key: 'payroll_run_register',
            name: datasetByKey.get('payroll_run_register')!.name,
            domain: 'payroll',
          }
        : null,
      owner: { id: 2, name: 'Tenant Administrator', email: 'tenant.admin@phoenixhrms.test' },
      query: {
        filters: { status: 'blocked' },
        filter_operators: { status: 'eq' },
        sort_by: 'period_name',
        sort_direction: 'desc',
        drilldown_path: null,
      },
      presentation_preferences: { visible_columns: ['run_name', 'period_name', 'status', 'blocked_items_count', 'payslip_status'] },
      validation: { status: 'blocked', reason: 'Dataset certification drifted after the last saved-view update.' },
      created_at: isoDaysAgo(9),
      updated_at: isoDaysAgo(2),
    },
  ]

  return views.filter((view) => {
    if (!view.dataset?.key || !datasetByKey.has(view.dataset.key)) {
      return false
    }

    if (canManageAll || view.owner?.id === userId) {
      return true
    }

    if (view.share.scope === 'company') {
      return true
    }

    if (view.share.scope === 'roles') {
      return view.share.shared_role_names.some((roleName) => roles.includes(roleName))
    }

    return false
  })
}

function buildExports(
  datasets: ReportingDatasetRecord[],
  snapshot: AccessSnapshot | null,
): ReportingExportRecord[] {
  const datasetByKey = new Map(datasets.map((dataset) => [dataset.key, dataset]))
  const userId = snapshot?.user.id ?? 0
  const canManageAll = hasAnyPermission(snapshot, ['reporting.manage', 'reporting.certify'])

  const exports: ReportingExportRecord[] = [
    {
      id: 9301,
      export_uuid: 'exp-team-workforce-complete',
      status: 'completed',
      format: 'json',
      execution_mode: 'sync',
      delivery_target: 'requestor_download',
      dataset: datasetByKey.get('workforce_headcount_snapshot')
        ? {
            id: datasetByKey.get('workforce_headcount_snapshot')!.id,
            key: 'workforce_headcount_snapshot',
            name: datasetByKey.get('workforce_headcount_snapshot')!.name,
            domain: 'workforce',
          }
        : null,
      requested_by: { id: 3, name: 'Manager Reviewer', email: 'manager@phoenixhrms.test' },
      query: {
        filters: { employment_status: 'active', department_name: 'Operations' },
        filter_operators: { employment_status: 'eq', department_name: 'eq' },
        sort_by: 'employee_code',
        sort_direction: 'asc',
        drilldown_path: 'employee_profile',
      },
      counts: { estimated_row_count: 2, exported_row_count: 2 },
      visibility: {
        masked_field_keys: canSeeSensitiveWorkforceFields(snapshot) ? [] : ['employee_email'],
        hidden_field_keys: [],
        drilldown_keys: ['employee_profile'],
      },
      freshness: { generated_at: isoMinutesAgo(40), expectation_minutes: 60 },
      file: {
        name: 'team-active-workforce.json',
        size_bytes: 3862,
        checksum_sha256: 'demo-checksum-workforce',
        download_available: true,
        download_url: '#demo-reporting-export-workforce',
      },
      retention: { expires_at: isoDaysAhead(2), is_expired: false },
      requested_at: isoMinutesAgo(45),
      started_at: isoMinutesAgo(44),
      completed_at: isoMinutesAgo(43),
      failed_at: null,
      notified_at: isoMinutesAgo(43),
      last_error: null,
      created_at: isoMinutesAgo(45),
      updated_at: isoMinutesAgo(43),
    },
    {
      id: 9302,
      export_uuid: 'exp-attendance-queued',
      status: 'queued',
      format: 'csv',
      execution_mode: 'async',
      delivery_target: 'requestor_download',
      dataset: datasetByKey.get('attendance_daily_register')
        ? {
            id: datasetByKey.get('attendance_daily_register')!.id,
            key: 'attendance_daily_register',
            name: datasetByKey.get('attendance_daily_register')!.name,
            domain: 'attendance',
          }
        : null,
      requested_by: { id: 2, name: 'Tenant Administrator', email: 'tenant.admin@phoenixhrms.test' },
      query: {
        filters: { attendance_date: '2026-06-26' },
        filter_operators: { attendance_date: 'eq' },
        sort_by: 'attendance_date',
        sort_direction: 'desc',
        drilldown_path: null,
      },
      counts: { estimated_row_count: 3, exported_row_count: null },
      visibility: { masked_field_keys: [], hidden_field_keys: [], drilldown_keys: [] },
      freshness: { generated_at: isoMinutesAgo(16), expectation_minutes: 90 },
      file: { name: null, size_bytes: null, checksum_sha256: null, download_available: false, download_url: null },
      retention: { expires_at: null, is_expired: false },
      requested_at: isoMinutesAgo(16),
      started_at: null,
      completed_at: null,
      failed_at: null,
      notified_at: null,
      last_error: null,
      created_at: isoMinutesAgo(16),
      updated_at: isoMinutesAgo(16),
    },
    {
      id: 9303,
      export_uuid: 'exp-payroll-expired',
      status: 'expired',
      format: 'csv',
      execution_mode: 'sync',
      delivery_target: 'requestor_download',
      dataset: datasetByKey.get('payroll_run_register')
        ? {
            id: datasetByKey.get('payroll_run_register')!.id,
            key: 'payroll_run_register',
            name: datasetByKey.get('payroll_run_register')!.name,
            domain: 'payroll',
          }
        : null,
      requested_by: { id: 2, name: 'Tenant Administrator', email: 'tenant.admin@phoenixhrms.test' },
      query: {
        filters: { status: 'locked' },
        filter_operators: { status: 'eq' },
        sort_by: 'period_name',
        sort_direction: 'desc',
        drilldown_path: null,
      },
      counts: { estimated_row_count: 1, exported_row_count: 1 },
      visibility: { masked_field_keys: [], hidden_field_keys: ['net_payroll'], drilldown_keys: [] },
      freshness: { generated_at: isoDaysAgo(3), expectation_minutes: 180 },
      file: {
        name: 'payroll-close-ready.csv',
        size_bytes: 1152,
        checksum_sha256: 'demo-checksum-payroll',
        download_available: false,
        download_url: null,
      },
      retention: { expires_at: isoMinutesAgo(90), is_expired: true },
      requested_at: isoDaysAgo(3),
      started_at: isoDaysAgo(3),
      completed_at: isoDaysAgo(3),
      failed_at: null,
      notified_at: isoDaysAgo(3),
      last_error: null,
      created_at: isoDaysAgo(3),
      updated_at: isoDaysAgo(3),
    },
    {
      id: 9304,
      export_uuid: 'exp-workforce-blocked',
      status: 'failed',
      format: 'json',
      execution_mode: 'async',
      delivery_target: 'requestor_download',
      dataset: datasetByKey.get('workforce_headcount_snapshot')
        ? {
            id: datasetByKey.get('workforce_headcount_snapshot')!.id,
            key: 'workforce_headcount_snapshot',
            name: datasetByKey.get('workforce_headcount_snapshot')!.name,
            domain: 'workforce',
          }
        : null,
      requested_by: { id: 2, name: 'Tenant Administrator', email: 'tenant.admin@phoenixhrms.test' },
      query: {
        filters: { employment_status: 'active' },
        filter_operators: { employment_status: 'eq' },
        sort_by: 'employee_code',
        sort_direction: 'asc',
        drilldown_path: 'employee_profile',
      },
      counts: { estimated_row_count: 3, exported_row_count: null },
      visibility: { masked_field_keys: ['employee_email'], hidden_field_keys: [], drilldown_keys: ['employee_profile'] },
      freshness: { generated_at: isoMinutesAgo(24), expectation_minutes: 60 },
      file: { name: null, size_bytes: null, checksum_sha256: null, download_available: false, download_url: null },
      retention: { expires_at: null, is_expired: false },
      requested_at: isoMinutesAgo(24),
      started_at: isoMinutesAgo(22),
      completed_at: null,
      failed_at: isoMinutesAgo(21),
      notified_at: null,
      last_error: 'blocked_by_certification_drift',
      created_at: isoMinutesAgo(24),
      updated_at: isoMinutesAgo(21),
    },
  ]

  return exports.filter((record) => {
    if (!record.dataset?.key || !datasetByKey.has(record.dataset.key)) {
      return false
    }

    if (canManageAll) {
      return true
    }

    return record.requested_by?.id === userId
  })
}

function buildSubscriptions(
  datasets: ReportingDatasetRecord[],
  savedViews: ReportingSavedViewRecord[],
  snapshot: AccessSnapshot | null,
): ReportingSubscriptionRecord[] {
  const datasetByKey = new Map(datasets.map((dataset) => [dataset.key, dataset]))
  const savedViewById = new Map(savedViews.map((view) => [view.id, view]))
  const userId = snapshot?.user.id ?? 0
  const canManageAll = hasAnyPermission(snapshot, ['reporting.manage', 'reporting.certify'])

  const subscriptions: ReportingSubscriptionRecord[] = [
    {
      id: 9401,
      subscription_uuid: 'sub-team-weekly',
      name: 'Weekly Team Workforce',
      description: 'Deliver the manager workforce lens every Monday morning.',
      status: 'active',
      owner: { id: 3, name: 'Manager Reviewer', email: 'manager@phoenixhrms.test' },
      source: {
        dataset: datasetByKey.get('workforce_headcount_snapshot')
          ? {
              id: datasetByKey.get('workforce_headcount_snapshot')!.id,
              key: 'workforce_headcount_snapshot',
              name: datasetByKey.get('workforce_headcount_snapshot')!.name,
              domain: 'workforce',
            }
          : null,
        saved_view: savedViewById.get(9201)
          ? {
              id: 9201,
              view_uuid: 'view-team-workforce',
              name: 'Team Active Workforce',
              status: 'active',
            }
          : null,
      },
      delivery: { channel: 'in_app_notification', target: 'owner_only', export_format: 'json' },
      schedule: {
        frequency: 'weekly',
        timezone: 'Asia/Kolkata',
        config: { time_of_day: '09:30', weekday: 1 },
        next_delivery_at: isoDaysAhead(3),
      },
      query: {
        filters: { employment_status: 'active', department_name: 'Operations' },
        filter_operators: { employment_status: 'eq', department_name: 'eq' },
        sort_by: 'employee_code',
        sort_direction: 'asc',
        drilldown_path: 'employee_profile',
      },
      validation: { status: 'valid', reason: null },
      last_delivery: {
        status: 'completed',
        error: null,
        delivered_at: isoDaysAgo(2),
        report_export_id: 9301,
      },
      created_at: isoDaysAgo(8),
      updated_at: isoDaysAgo(2),
    },
    {
      id: 9402,
      subscription_uuid: 'sub-payroll-blocked',
      name: 'Monthly Payroll Close Watch',
      description: 'Notify payroll leadership after each monthly close-prep window.',
      status: 'blocked',
      owner: { id: 2, name: 'Tenant Administrator', email: 'tenant.admin@phoenixhrms.test' },
      source: {
        dataset: datasetByKey.get('payroll_run_register')
          ? {
              id: datasetByKey.get('payroll_run_register')!.id,
              key: 'payroll_run_register',
              name: datasetByKey.get('payroll_run_register')!.name,
              domain: 'payroll',
            }
          : null,
        saved_view: savedViewById.get(9203)
          ? {
              id: 9203,
              view_uuid: 'view-payroll-close-watch',
              name: 'Payroll Close Watch',
              status: 'active',
            }
          : null,
      },
      delivery: { channel: 'in_app_notification', target: 'owner_only', export_format: 'csv' },
      schedule: {
        frequency: 'monthly',
        timezone: 'Asia/Kolkata',
        config: { time_of_day: '10:00', day_of_month: 1 },
        next_delivery_at: isoDaysAhead(5),
      },
      query: {
        filters: { status: 'blocked' },
        filter_operators: { status: 'eq' },
        sort_by: 'period_name',
        sort_direction: 'desc',
        drilldown_path: null,
      },
      validation: { status: 'blocked', reason: 'The source dataset is no longer certified for general reporting delivery.' },
      last_delivery: {
        status: 'blocked',
        error: 'dataset_certification_drift',
        delivered_at: null,
        report_export_id: null,
      },
      created_at: isoDaysAgo(14),
      updated_at: isoDaysAgo(1),
    },
    {
      id: 9403,
      subscription_uuid: 'sub-attendance-revoked',
      name: 'Attendance Exceptions Digest',
      description: 'Former attendance exception digest retained for audit visibility.',
      status: 'revoked',
      owner: { id: 2, name: 'Tenant Administrator', email: 'tenant.admin@phoenixhrms.test' },
      source: {
        dataset: datasetByKey.get('attendance_daily_register')
          ? {
              id: datasetByKey.get('attendance_daily_register')!.id,
              key: 'attendance_daily_register',
              name: datasetByKey.get('attendance_daily_register')!.name,
              domain: 'attendance',
            }
          : null,
        saved_view: null,
      },
      delivery: { channel: 'in_app_notification', target: 'owner_only', export_format: 'csv' },
      schedule: {
        frequency: 'daily',
        timezone: 'Asia/Kolkata',
        config: { time_of_day: '18:00' },
        next_delivery_at: null,
      },
      query: {
        filters: { primary_status: 'late' },
        filter_operators: { primary_status: 'eq' },
        sort_by: 'attendance_date',
        sort_direction: 'desc',
        drilldown_path: null,
      },
      validation: { status: 'valid', reason: null },
      last_delivery: {
        status: 'completed',
        error: null,
        delivered_at: isoDaysAgo(6),
        report_export_id: 9302,
      },
      created_at: isoDaysAgo(20),
      updated_at: isoDaysAgo(6),
    },
  ]

  return subscriptions.filter((subscription) => {
    if (!subscription.source.dataset?.key || !datasetByKey.has(subscription.source.dataset.key)) {
      return false
    }

    if (canManageAll) {
      return true
    }

    return subscription.owner?.id === userId
  })
}

function buildActivity(
  accessibleDatasets: ReportingDatasetRecord[],
  exports: ReportingExportRecord[],
  subscriptions: ReportingSubscriptionRecord[],
): ReportingActivityItem[] {
  const accessibleDatasetKeys = new Set(accessibleDatasets.map((dataset) => dataset.key))

  return [
    {
      id: 'stale-executive',
      title: 'Leadership dashboard freshness has drifted',
      detail: 'The executive snapshot is past its freshness window and should be refreshed before board review.',
      meta: 'Leadership · cache window breached',
      tone: 'warning',
      path: '/reporting/executive',
    },
    {
      id: 'manager-mask',
      title: 'Team dashboard is honoring masked data rules',
      detail: 'Manager drilldowns are visible, but workforce-sensitive fields remain partially masked.',
      meta: 'Manager · governed visibility',
      tone: 'info',
      path: '/reporting/team',
    },
    {
      id: 'blocked-widget',
      title: 'Learning overdue card is blocked in executive view',
      detail: 'One widget is waiting on a fresh certified dataset before the metric can be trusted again.',
      meta: 'Executive · blocked widget',
      tone: 'danger',
      path: '/reporting/executive',
    },
    exports.some((record) => record.status === 'queued')
      ? {
          id: 'queued-export',
          title: 'A governed export is still queued',
          detail: 'Large reporting delivery is waiting on async processing before download becomes available.',
          meta: 'Exports · queued lifecycle',
          tone: 'warning',
          path: '/reporting/exports',
        }
      : null,
    subscriptions.some((record) => record.status === 'blocked')
      ? {
          id: 'blocked-subscription',
          title: 'A subscription is blocked by governance drift',
          detail: 'At least one recurring report delivery is paused until certification or scope posture is restored.',
          meta: 'Subscriptions · blocked state',
          tone: 'danger',
          path: '/reporting/subscriptions',
        }
      : null,
  ].filter((item): item is ReportingActivityItem => {
    if (!item) {
      return false
    }

    if (item.path === '/reporting/executive') {
      return accessibleDatasetKeys.has('learning_assignment_targets') || accessibleDatasetKeys.has('workforce_headcount_snapshot')
    }

    if (item.path === '/reporting/team') {
      return accessibleDatasetKeys.has('workforce_headcount_snapshot')
    }

    return true
  })
}

export function buildDemoReportingWorkspaceState(snapshot: AccessSnapshot | null): ReportingDemoWorkspaceState {
  const dashboards = buildDashboardFixtures()
  const accessibleDashboardKeys = getAccessibleReportingDashboardKeys(snapshot?.user.permissions ?? [])
  const allDatasets = buildDatasets()
  const accessibleDatasets = allDatasets.filter((dataset) => canViewDatasetDomain(snapshot, dataset.domain))
  const accessibleDatasetKeySet = new Set(accessibleDatasets.map((dataset) => dataset.key))
  const allRowsByDatasetKey = buildRowsByDatasetKey(snapshot)
  const rowsByDatasetKey = Object.fromEntries(
    Object.entries(allRowsByDatasetKey).filter(([datasetKey]) => accessibleDatasetKeySet.has(datasetKey)),
  )
  const savedViews = buildSavedViews(accessibleDatasets, snapshot)
  const exports = buildExports(accessibleDatasets, snapshot)
  const subscriptions = buildSubscriptions(accessibleDatasets, savedViews, snapshot)
  const activity = buildActivity(accessibleDatasets, exports, subscriptions)

  return {
    workspace: {
      dashboards: accessibleDashboardKeys.reduce<ReportingWorkspaceData['dashboards']>((current, key) => {
        current[key] = dashboards[key]
        return current
      }, {}),
      failures: [],
      activity,
      datasets: accessibleDatasets,
      savedViews,
      exports,
      subscriptions,
    },
    rowsByDatasetKey,
  }
}

export function buildDemoReportingWorkspace(snapshot: AccessSnapshot | null): ReportingWorkspaceData {
  return buildDemoReportingWorkspaceState(snapshot).workspace
}
