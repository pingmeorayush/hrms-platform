import { useMemo, type ReactElement } from 'react'
import { Link } from 'react-router-dom'
import {
  AlertTriangle,
  ArrowRight,
  BarChart3,
  Clock3,
  EyeOff,
  ShieldCheck,
  UsersRound,
} from 'lucide-react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import {
  CommandCenterAttentionItem,
  CommandCenterAttentionStrip,
  CommandCenterLayout,
  CommandCenterMain,
  CommandCenterMetricCard,
  CommandCenterMetricGrid,
  CommandCenterPanel,
  CommandCenterRail,
} from '../../../shared/ui/command-center'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSurface,
} from '../../../shared/ui/workspace'
import { dashboardRouteMap } from '../config'
import { useReportingRouteWorkspace } from './useReportingRouteWorkspace'
import type { ReportingDashboardRecord } from '../types'

function countMaskingRules(dashboard: ReportingDashboardRecord) {
  return dashboard.widgets.reduce((count, widget) => {
    const posture = widget.governance.dataset.masking_posture

    if (Array.isArray(posture)) {
      return count + posture.length
    }

    if (posture && typeof posture === 'object') {
      return count + Object.keys(posture).length
    }

    return count
  }, 0)
}

function formatFreshnessBadge(
  freshness: ReportingDashboardRecord['freshness'] | ReportingDashboardRecord['widgets'][number]['freshness'],
) {
  if (freshness.is_stale) {
    return 'Stale'
  }

  if (freshness.expectation_minutes <= 120) {
    return 'Hot'
  }

  if (freshness.expectation_minutes <= 480) {
    return 'Daily'
  }

  return 'Lagging'
}

function formatPersonaLabel(persona: string) {
  return persona
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase())
}

function widgetTone(widget: ReportingDashboardRecord['widgets'][number]) {
  if (widget.status === 'blocked') {
    return 'danger' as const
  }

  if (/blocked|overdue|exception|pending/i.test(widget.key)) {
    return 'warning' as const
  }

  if (/headcount|locked|active/i.test(widget.key)) {
    return 'success' as const
  }

  return 'info' as const
}

function renderMaskingMessage(maskedRules: number) {
  if (!maskedRules) {
    return 'No masking rules are currently affecting the visible dataset set.'
  }

  return `Masked field rules apply to ${maskedRules} governed field mapping${maskedRules === 1 ? '' : 's'} in this dashboard.`
}

function useResolvedDashboards() {
  const workspace = useReportingRouteWorkspace()

  const dashboards = useMemo(
    () =>
      workspace.accessibleDashboardKeys
        .map((dashboardKey) => workspace.data?.dashboards[dashboardKey] ?? null)
        .filter((dashboard): dashboard is ReportingDashboardRecord => dashboard !== null),
    [workspace.accessibleDashboardKeys, workspace.data?.dashboards],
  )

  return {
    workspace,
    dashboards,
  }
}

export function ReportingOverviewPage() {
  const { workspace, dashboards } = useResolvedDashboards()

  const staleDashboards = dashboards.filter((dashboard) => dashboard.freshness.is_stale)
  const blockedWidgets = dashboards.flatMap((dashboard) =>
    dashboard.widgets.filter((widget) => widget.status === 'blocked'),
  )
  const maskedRuleCount = dashboards.reduce((count, dashboard) => count + countMaskingRules(dashboard), 0)
  const drilldownCount = dashboards.reduce(
    (count, dashboard) => count + dashboard.widgets.filter((widget) => widget.drilldown).length,
    0,
  )
  const cacheHitCount = dashboards.filter((dashboard) => dashboard.snapshot.cache_hit).length
  const firstDashboardRoute = dashboards[0] ? dashboardRouteMap[dashboards[0].dashboard.key] : '/reporting/overview'

  const attentionItems = useMemo(() => {
    const items: Array<{
      id: string
      tone: 'warning' | 'danger' | 'info' | 'success'
      title: string
      detail: string
      meta: string
      path: string
      icon: ReactElement
    }> = []

    const firstStale = staleDashboards[0]
    if (firstStale) {
      items.push({
        id: 'stale-dashboard',
        tone: 'warning',
        title: `${firstStale.dashboard.name} is stale`,
        detail: 'This governed snapshot is past its freshness window and should be refreshed before sharing externally.',
        meta: `${formatPersonaLabel(firstStale.dashboard.persona)} · ${firstStale.freshness.expectation_minutes} minute expectation`,
        path: dashboardRouteMap[firstStale.dashboard.key],
        icon: <Clock3 className="h-4 w-4" />,
      })
    }

    const firstBlockedWidget = blockedWidgets[0]
    if (firstBlockedWidget) {
      const parentDashboard = dashboards.find((dashboard) =>
        dashboard.widgets.some((widget) => widget.key === firstBlockedWidget.key),
      )

      items.push({
        id: 'blocked-widget',
        tone: 'danger',
        title: `${firstBlockedWidget.name} is blocked`,
        detail:
          firstBlockedWidget.blocked_reason?.replace(/_/g, ' ') ??
          'A governed dependency is currently missing for this widget.',
        meta: parentDashboard ? `${parentDashboard.dashboard.name} · blocked widget` : 'Governance dependency',
        path: parentDashboard ? dashboardRouteMap[parentDashboard.dashboard.key] : '/reporting/overview',
        icon: <AlertTriangle className="h-4 w-4" />,
      })
    }

    if (maskedRuleCount > 0) {
      items.push({
        id: 'masked-fields',
        tone: 'info',
        title: 'Masked field rules are active',
        detail: `${maskedRuleCount} governed masking rule(s) apply across the currently visible dashboards.`,
        meta: 'Reporting command center is honoring the same sensitive-field posture as governed report queries.',
        path: '/reporting/team',
        icon: <EyeOff className="h-4 w-4" />,
      })
    }

    if (!items.length) {
      items.push({
        id: 'healthy',
        tone: 'success',
        title: 'Reporting posture looks healthy',
        detail: 'No stale dashboard, blocked widget, or masking surprise is currently surfaced for this session.',
        meta: 'Use the persona dashboards below to inspect governed KPI coverage in detail.',
        path: '/reporting/overview',
        icon: <ShieldCheck className="h-4 w-4" />,
      })
    }

    return items.slice(0, 4)
  }, [blockedWidgets, dashboards, maskedRuleCount, staleDashboards])

  if (workspace.isLoading) {
    return (
      <WorkspaceEmptyState
        title="Loading reporting command center"
        copy="Resolving governed dashboards, freshness posture, and role-aware reporting coverage."
      />
    )
  }

  if (workspace.error) {
    return (
      <WorkspaceEmptyState
        title="Reporting workspace unavailable"
        copy={workspace.error.message || 'The reporting command center could not be loaded.'}
      />
    )
  }

  if (!workspace.canViewReporting) {
    return (
      <WorkspaceEmptyState
        title="Reporting workspace unavailable"
        copy="This session does not currently resolve to governed reporting visibility."
      />
    )
  }

  if (!workspace.data || dashboards.length === 0) {
    return (
      <WorkspaceEmptyState
        title="No dashboards in scope yet"
        copy="This reporting session is active, but no governed dashboards are currently available for the resolved role and data scope."
      />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Reporting"
          title="Reporting Command Center"
          description="Monitor governed dashboard freshness, blocked widgets, masking posture, and persona-specific KPI coverage before deeper report exploration arrives."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo contract' : 'Live contract'}</Badge>}
          context={[
            `${dashboards.length} dashboard${dashboards.length === 1 ? '' : 's'} in scope`,
            `${drilldownCount} governed drilldown${drilldownCount === 1 ? '' : 's'} available`,
          ]}
          actions={
            <Button asChild size="sm">
              <Link to={firstDashboardRoute}>
                Open primary dashboard
                <ArrowRight className="h-4 w-4" />
              </Link>
            </Button>
          }
        />
        <WorkspaceContent className="space-y-4">
          <CommandCenterMetricGrid className="xl:grid-cols-3 2xl:grid-cols-6">
            <CommandCenterMetricCard
              label="Dashboards in scope"
              value={dashboards.length}
              delta={`${workspace.data.failures.length} failed to resolve`}
              icon={<BarChart3 className="h-4 w-4" />}
              tone="info"
            />
            <CommandCenterMetricCard
              label="Stale dashboards"
              value={staleDashboards.length}
              delta="Refresh these before leadership review or export delivery."
              icon={<Clock3 className="h-4 w-4" />}
              tone={staleDashboards.length ? 'warning' : 'success'}
            />
            <CommandCenterMetricCard
              label="Blocked widgets"
              value={blockedWidgets.length}
              delta="Widgets waiting on certified or refreshed governed sources."
              icon={<AlertTriangle className="h-4 w-4" />}
              tone={blockedWidgets.length ? 'danger' : 'success'}
            />
            <CommandCenterMetricCard
              label="Masked data rules"
              value={maskedRuleCount}
              delta="Governed masking posture currently visible in this session."
              icon={<EyeOff className="h-4 w-4" />}
              tone={maskedRuleCount ? 'warning' : 'neutral'}
            />
            <CommandCenterMetricCard
              label="Drilldowns ready"
              value={drilldownCount}
              delta="Cards already map to governed detail views for the next explorer wave."
              icon={<UsersRound className="h-4 w-4" />}
              tone="success"
            />
            <CommandCenterMetricCard
              label="Cache-backed cards"
              value={cacheHitCount}
              delta="Snapshot reuse is active where freshness windows still hold."
              icon={<ShieldCheck className="h-4 w-4" />}
              tone="neutral"
            />
          </CommandCenterMetricGrid>

          <CommandCenterAttentionStrip title="Needs attention">
            {attentionItems.map((item) => (
              <CommandCenterAttentionItem
                key={item.id}
                title={item.title}
                detail={item.detail}
                meta={item.meta}
                tone={item.tone}
                to={item.path}
                icon={item.icon}
              />
            ))}
          </CommandCenterAttentionStrip>

          <CommandCenterLayout>
            <CommandCenterMain>
              <CommandCenterPanel
                title="Accessible dashboards"
                description="Each card reflects a governed persona dashboard with freshness, masking, and drilldown posture."
              >
                <div className="grid gap-3 p-3 xl:grid-cols-2">
                  {dashboards.map((dashboard) => {
                    const maskedRules = countMaskingRules(dashboard)
                    const readyWidgets = dashboard.widgets.filter((widget) => widget.status === 'ready').length

                    return (
                      <Link
                        key={dashboard.dashboard.key}
                        to={dashboardRouteMap[dashboard.dashboard.key]}
                        className="rounded-[1rem] border border-line/80 bg-white/92 p-4 shadow-[0_12px_22px_rgba(15,23,42,0.04)] transition hover:border-line-strong hover:bg-white"
                      >
                        <div className="flex items-center justify-between gap-3">
                          <div>
                            <p className="ui-type-page-eyebrow text-text-subtle">
                              {formatPersonaLabel(dashboard.dashboard.persona)}
                            </p>
                            <h3 className="text-base font-semibold text-foreground">{dashboard.dashboard.name}</h3>
                          </div>
                          <Badge variant={dashboard.freshness.is_stale ? 'warning' : 'success'}>
                            {formatFreshnessBadge(dashboard.freshness)}
                          </Badge>
                        </div>
                        <p className="mt-2 text-sm text-muted-foreground">{dashboard.dashboard.description}</p>
                        <div className="mt-4 grid gap-2 sm:grid-cols-3">
                          <div className="rounded-xl border border-line/70 bg-panel/70 px-3 py-2">
                            <div className="text-xs uppercase tracking-[0.18em] text-text-subtle">Widgets</div>
                            <div className="mt-1 text-lg font-semibold text-foreground">{dashboard.widgets.length}</div>
                          </div>
                          <div className="rounded-xl border border-line/70 bg-panel/70 px-3 py-2">
                            <div className="text-xs uppercase tracking-[0.18em] text-text-subtle">Ready</div>
                            <div className="mt-1 text-lg font-semibold text-foreground">{readyWidgets}</div>
                          </div>
                          <div className="rounded-xl border border-line/70 bg-panel/70 px-3 py-2">
                            <div className="text-xs uppercase tracking-[0.18em] text-text-subtle">Masking</div>
                            <div className="mt-1 text-lg font-semibold text-foreground">{maskedRules}</div>
                          </div>
                        </div>
                      </Link>
                    )
                  })}
                </div>
              </CommandCenterPanel>

              <CommandCenterPanel
                title="Governed KPI coverage"
                description="What the first reporting surface already knows about certified KPI and dataset lineage."
              >
                <div className="divide-y divide-line/70">
                  {dashboards.map((dashboard) => (
                    <div key={dashboard.dashboard.key} className="flex flex-col gap-3 px-4 py-4 lg:flex-row lg:items-start lg:justify-between">
                      <div className="min-w-0">
                        <h3 className="text-sm font-semibold text-foreground">{dashboard.dashboard.name}</h3>
                        <p className="mt-1 text-sm text-muted-foreground">{dashboard.dashboard.description}</p>
                        <div className="mt-2 flex flex-wrap gap-2">
                          <Badge variant="neutral">{dashboard.widgets.length} widgets</Badge>
                          <Badge variant={dashboard.freshness.is_stale ? 'warning' : 'success'}>
                            {dashboard.freshness.is_stale ? 'Refresh needed' : 'Freshness in window'}
                          </Badge>
                          <Badge variant="info">
                            {dashboard.widgets.filter((widget) => widget.drilldown).length} drilldown path(s)
                          </Badge>
                        </div>
                      </div>
                      <Button asChild variant="secondary" size="sm">
                        <Link to={dashboardRouteMap[dashboard.dashboard.key]}>Open dashboard</Link>
                      </Button>
                    </div>
                  ))}
                </div>
              </CommandCenterPanel>
            </CommandCenterMain>

            <CommandCenterRail>
              <CommandCenterPanel
                title="Freshness ledger"
                description="Use this to spot where cached reporting is still valid and where refresh is overdue."
              >
                <div className="space-y-3 p-4">
                  {dashboards.map((dashboard) => (
                    <div key={dashboard.dashboard.key} className="rounded-xl border border-line/70 bg-panel/65 px-3 py-3">
                      <div className="flex items-center justify-between gap-3">
                        <span className="text-sm font-semibold text-foreground">{dashboard.dashboard.name}</span>
                        <Badge variant={dashboard.freshness.is_stale ? 'warning' : 'success'}>
                          {formatFreshnessBadge(dashboard.freshness)}
                        </Badge>
                      </div>
                      <p className="mt-2 text-xs text-muted-foreground">
                        {dashboard.snapshot.cache_hit ? 'Cache-backed snapshot' : 'Fresh snapshot'} · expected every{' '}
                        {dashboard.freshness.expectation_minutes} minutes
                      </p>
                    </div>
                  ))}
                </div>
              </CommandCenterPanel>

              <CommandCenterPanel
                title="Recent governance signals"
                description="Surface trust and delivery posture while deeper export and subscription pages are still ahead."
              >
                <div className="space-y-3 p-4">
                  {workspace.data.activity.length ? (
                    workspace.data.activity.map((item) => (
                      <Link key={item.id} to={item.path} className="block rounded-xl border border-line/70 bg-panel/65 px-3 py-3 transition hover:border-line-strong hover:bg-panel">
                        <div className="flex items-center justify-between gap-2">
                          <span className="text-sm font-semibold text-foreground">{item.title}</span>
                          <Badge variant={item.tone}>{item.tone}</Badge>
                        </div>
                        <p className="mt-2 text-sm text-muted-foreground">{item.detail}</p>
                        <p className="mt-1 text-xs text-text-subtle">{item.meta}</p>
                      </Link>
                    ))
                  ) : (
                    <div className="rounded-xl border border-dashed border-line bg-panel/60 px-3 py-4 text-sm text-muted-foreground">
                      Recent export and subscription delivery signals will appear here as the next reporting wave lands.
                    </div>
                  )}
                </div>
              </CommandCenterPanel>
            </CommandCenterRail>
          </CommandCenterLayout>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function ReportingDashboardSectionPage({
  dashboardKey,
  title,
  description,
  emptyTitle,
}: {
  dashboardKey: keyof typeof dashboardRouteMap
  title: string
  description: string
  emptyTitle: string
}) {
  const { workspace } = useResolvedDashboards()
  const dashboard = workspace.data?.dashboards[dashboardKey] ?? null

  if (workspace.isLoading) {
    return (
      <WorkspaceEmptyState
        title={`Loading ${title.toLowerCase()}`}
        copy="Resolving the governed dashboard snapshot and widget posture for this reporting view."
      />
    )
  }

  if (workspace.error) {
    return <WorkspaceEmptyState title={emptyTitle} copy={workspace.error.message} />
  }

  if (!workspace.canViewReporting) {
    return (
      <WorkspaceEmptyState
        title={emptyTitle}
        copy="This session does not currently resolve to governed reporting visibility."
      />
    )
  }

  if (!workspace.accessibleDashboardKeys.includes(dashboardKey) || !dashboard) {
    return (
      <WorkspaceEmptyState
        title={emptyTitle}
        copy="This persona-specific dashboard is not available in the current reporting scope for this session."
      />
    )
  }

  const blockedWidgets = dashboard.widgets.filter((widget) => widget.status === 'blocked')
  const maskedRules = countMaskingRules(dashboard)

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Reporting"
          title={title}
          description={description}
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo contract' : 'Live contract'}</Badge>}
          context={[
            `${dashboard.widgets.length} widget${dashboard.widgets.length === 1 ? '' : 's'} in scope`,
            dashboard.freshness.is_stale ? 'Refresh overdue' : 'Freshness in window',
          ]}
          actions={
            <Button asChild variant="secondary" size="sm">
              <Link to="/reporting/overview">Back to command center</Link>
            </Button>
          }
        />
        <WorkspaceContent className="space-y-4">
          <CommandCenterMetricGrid className="xl:grid-cols-2 2xl:grid-cols-4">
            {dashboard.widgets.map((widget) => (
              <CommandCenterMetricCard
                key={widget.key}
                label={widget.name}
                value={widget.value ?? 'Blocked'}
                delta={
                  widget.status === 'blocked'
                    ? widget.blocked_reason?.replace(/_/g, ' ')
                    : widget.description
                }
                icon={<BarChart3 className="h-4 w-4" />}
                tone={widgetTone(widget)}
              />
            ))}
          </CommandCenterMetricGrid>

          <CommandCenterAttentionStrip title="Dashboard watch">
            {dashboard.freshness.is_stale ? (
              <CommandCenterAttentionItem
                title="Snapshot freshness has drifted"
                detail="Refresh this governed dashboard before using it for external review or export delivery."
                meta={`${dashboard.freshness.expectation_minutes} minute expectation`}
                tone="warning"
                icon={<Clock3 className="h-4 w-4" />}
              />
            ) : null}
            {blockedWidgets[0] ? (
              <CommandCenterAttentionItem
                title={`${blockedWidgets[0].name} is blocked`}
                detail={
                  blockedWidgets[0].blocked_reason?.replace(/_/g, ' ') ??
                  'A governed dependency is currently missing for this widget.'
                }
                meta="Blocked widgets should be resolved before downstream sharing."
                tone="danger"
                icon={<AlertTriangle className="h-4 w-4" />}
              />
            ) : null}
            {maskedRules ? (
              <CommandCenterAttentionItem
                title="Masked data state is active"
                detail={renderMaskingMessage(maskedRules)}
                meta="This dashboard is honoring governed sensitive-field controls."
                tone="info"
                icon={<EyeOff className="h-4 w-4" />}
              />
            ) : null}
            {!dashboard.freshness.is_stale && !blockedWidgets.length && !maskedRules ? (
              <CommandCenterAttentionItem
                title="Dashboard posture looks healthy"
                detail="No stale snapshots, blocked widgets, or unexpected masking pressure are surfaced right now."
                meta="Use drilldowns for deeper governed detail when the explorer lands."
                tone="success"
                icon={<ShieldCheck className="h-4 w-4" />}
              />
            ) : null}
          </CommandCenterAttentionStrip>

          <CommandCenterLayout>
            <CommandCenterMain>
              <CommandCenterPanel
                title="Widget governance"
                description="Each widget remains anchored to a governed KPI and certified dataset definition."
              >
                <div className="divide-y divide-line/70">
                  {dashboard.widgets.map((widget) => {
                    const sourceCount = widget.governance.kpi.source_references?.length ?? 0
                    const posture = widget.governance.dataset.masking_posture
                    const maskingRuleCount = Array.isArray(posture)
                      ? posture.length
                      : posture && typeof posture === 'object'
                        ? Object.keys(posture).length
                        : 0

                    return (
                      <div key={widget.key} className="flex flex-col gap-3 px-4 py-4 lg:flex-row lg:items-start lg:justify-between">
                        <div className="min-w-0">
                          <div className="flex flex-wrap items-center gap-2">
                            <h3 className="text-sm font-semibold text-foreground">{widget.name}</h3>
                            <Badge variant={widget.status === 'blocked' ? 'danger' : 'success'}>
                              {widget.status === 'blocked' ? 'Blocked' : 'Ready'}
                            </Badge>
                            <Badge variant="neutral">
                              {widget.governance.dataset.certification_status ?? 'uncertified'}
                            </Badge>
                          </div>
                          <p className="mt-1 text-sm text-muted-foreground">{widget.description}</p>
                          <div className="mt-2 flex flex-wrap gap-2">
                            <Badge variant="info">{widget.governance.dataset.domain ?? 'domain pending'}</Badge>
                            <Badge variant="neutral">{sourceCount} lineage ref(s)</Badge>
                            <Badge variant={maskingRuleCount ? 'warning' : 'success'}>
                              {maskingRuleCount ? `${maskingRuleCount} mask rule(s)` : 'No masking'}
                            </Badge>
                            <Badge variant={widget.drilldown ? 'success' : 'neutral'}>
                              {widget.drilldown ? 'Drilldown ready' : 'No drilldown'}
                            </Badge>
                          </div>
                        </div>
                        <div className="min-w-[9rem] text-right">
                          <div className="text-xs uppercase tracking-[0.18em] text-text-subtle">Value</div>
                          <div className="mt-1 text-xl font-semibold text-foreground">
                            {widget.value ?? 'Blocked'}
                          </div>
                          <div className="mt-1 text-xs text-muted-foreground">
                            {widget.status === 'blocked'
                              ? widget.blocked_reason?.replace(/_/g, ' ')
                              : `${widget.freshness.expectation_minutes} minute freshness expectation`}
                          </div>
                        </div>
                      </div>
                    )
                  })}
                </div>
              </CommandCenterPanel>
            </CommandCenterMain>

            <CommandCenterRail>
              <CommandCenterPanel
                title="Governance posture"
                description="Trust cues for certification, freshness, and masked-data awareness."
              >
                <div className="space-y-3 p-4">
                  <div className="rounded-xl border border-line/70 bg-panel/65 px-3 py-3">
                    <div className="text-xs uppercase tracking-[0.18em] text-text-subtle">Freshness</div>
                    <div className="mt-1 text-sm font-semibold text-foreground">
                      {dashboard.freshness.is_stale ? 'Refresh overdue' : 'Within expected window'}
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground">
                      Snapshot expectation: every {dashboard.freshness.expectation_minutes} minutes
                    </p>
                  </div>
                  <div className="rounded-xl border border-line/70 bg-panel/65 px-3 py-3">
                    <div className="text-xs uppercase tracking-[0.18em] text-text-subtle">Masking</div>
                    <div className="mt-1 text-sm font-semibold text-foreground">
                      {maskedRules ? 'Masked fields active' : 'No masking pressure'}
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground">{renderMaskingMessage(maskedRules)}</p>
                  </div>
                  <div className="rounded-xl border border-line/70 bg-panel/65 px-3 py-3">
                    <div className="text-xs uppercase tracking-[0.18em] text-text-subtle">Drilldown readiness</div>
                    <div className="mt-1 text-sm font-semibold text-foreground">
                      {dashboard.widgets.filter((widget) => widget.drilldown).length} governed path(s)
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground">
                      Explorer and saved-view work can build directly on these governed target datasets.
                    </p>
                  </div>
                </div>
              </CommandCenterPanel>
            </CommandCenterRail>
          </CommandCenterLayout>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

export function ReportingWorkforcePage() {
  return (
    <ReportingDashboardSectionPage
      dashboardKey="hr_overview"
      title="Workforce Reporting Dashboard"
      description="Monitor HR-side workforce, attendance, leave, and recruiting posture from one governed dashboard."
      emptyTitle="Workforce reporting unavailable"
    />
  )
}

export function ReportingTeamPage() {
  return (
    <ReportingDashboardSectionPage
      dashboardKey="manager_overview"
      title="Team Reporting Dashboard"
      description="Track team-scoped headcount, attendance, leave approvals, and review follow-up with governed visibility controls."
      emptyTitle="Team reporting unavailable"
    />
  )
}

export function ReportingPayrollPage() {
  return (
    <ReportingDashboardSectionPage
      dashboardKey="payroll_overview"
      title="Payroll Reporting Dashboard"
      description="Review payroll run-state, blocked execution, and release readiness from the governed payroll dashboard."
      emptyTitle="Payroll reporting unavailable"
    />
  )
}

export function ReportingRecruitmentPage() {
  return (
    <ReportingDashboardSectionPage
      dashboardKey="recruiter_overview"
      title="Recruitment Reporting Dashboard"
      description="Watch candidate pipeline depth, interview load, and offer-stage movement from one recruiter-focused dashboard."
      emptyTitle="Recruitment reporting unavailable"
    />
  )
}

export function ReportingExecutivePage() {
  return (
    <ReportingDashboardSectionPage
      dashboardKey="leadership_overview"
      title="Executive Reporting Dashboard"
      description="Track enterprise operating posture across workforce, recruiting, learning, and performance from the leadership dashboard."
      emptyTitle="Executive reporting unavailable"
    />
  )
}
