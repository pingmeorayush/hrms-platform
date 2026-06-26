import type { AccessSnapshot } from '../../access/types'
import { getAccessibleReportingDashboardKeys } from '../config'
import type {
  ReportingActivityItem,
  ReportingDashboardKey,
  ReportingDashboardRecord,
  ReportingWorkspaceData,
} from '../types'

function isoMinutesAgo(minutesAgo: number) {
  return new Date(Date.now() - minutesAgo * 60 * 1000).toISOString()
}

function isoMinutesAhead(minutesAhead: number) {
  return new Date(Date.now() + minutesAhead * 60 * 1000).toISOString()
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

function buildActivity(): ReportingActivityItem[] {
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
  ]
}

export function buildDemoReportingWorkspace(snapshot: AccessSnapshot | null): ReportingWorkspaceData {
  const grantedPermissions = snapshot?.user.permissions ?? []
  const fixtures = buildDashboardFixtures()
  const accessibleKeys = getAccessibleReportingDashboardKeys(grantedPermissions)
  const activity = buildActivity().filter((item) => {
    if (item.path === '/reporting/executive') {
      return accessibleKeys.includes('leadership_overview')
    }

    if (item.path === '/reporting/team') {
      return accessibleKeys.includes('manager_overview')
    }

    return true
  })

  return {
    dashboards: accessibleKeys.reduce<ReportingWorkspaceData['dashboards']>((current, key) => {
      current[key] = fixtures[key]
      return current
    }, {}),
    failures: [],
    activity,
  }
}
