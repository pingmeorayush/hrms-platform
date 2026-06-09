import { useDeferredValue, useMemo, useState, type ReactNode } from 'react'
import { Link } from 'react-router-dom'
import {
  AlertTriangle,
  ArrowUpRight,
  BadgeCheck,
  CircleOff,
  FileText,
  Layers3,
  Lock,
  PlayCircle,
} from 'lucide-react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
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
import { ConsoleSearchField, ConsoleToolbar, ConsoleToolbarRow } from '../../../shared/ui/console-table'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import {
  formatCurrency,
  formatDate,
  formatRelativeTimestamp,
  formatRunStatus,
  periodStatusBadgeVariant,
  runStatusBadgeVariant,
} from '../utils'
import { usePayrollRouteWorkspace } from './usePayrollRouteWorkspace'

type PayrollOverviewTab = 'runs' | 'periods' | 'payslips'

export function PayrollOverviewPage() {
  const workspace = usePayrollRouteWorkspace()
  const [activeTab, setActiveTab] = useState<PayrollOverviewTab>('runs')
  const [search, setSearch] = useState('')
  const deferredSearch = useDeferredValue(search.trim().toLowerCase())

  const periods = useMemo(() => workspace.data?.periods ?? [], [workspace.data?.periods])
  const runs = useMemo(() => workspace.data?.runs ?? [], [workspace.data?.runs])
  const payslips = useMemo(() => workspace.data?.payslips ?? [], [workspace.data?.payslips])
  const blockedRuns = runs.filter((run) => run.status === 'blocked')
  const failedRuns = runs.filter((run) => run.status === 'failed')
  const readyRuns = runs.filter((run) => run.status === 'ready')
  const calculatedRuns = runs.filter((run) => run.status === 'calculated')
  const lockedRuns = runs.filter((run) => run.status === 'locked')
  const periodsReadyToClose = periods.filter(
    (period) => period.status === 'prepared' && period.latest_run?.status === 'locked',
  )

  const metricCards: Array<{
    label: string
    value: string
    delta: string
    icon: ReactNode
    tone: 'neutral' | 'info' | 'success' | 'warning' | 'danger'
    valueSize?: 'stat' | 'compact' | 'long'
  }> = [
    {
      label: 'Periods in flight',
      value: String(periods.filter((period) => period.status !== 'closed').length),
      delta: `${periodsReadyToClose.length} prepared period(s) ready to close`,
      icon: <Layers3 className="h-4 w-4" />,
      tone: periodsReadyToClose.length ? 'warning' : 'info',
    },
    {
      label: 'Blocked runs',
      value: String(blockedRuns.length),
      delta: `${blockedRuns.reduce((count, run) => count + run.prerequisite_summary.blocking_count, 0)} blocker(s) need resolution`,
      icon: <CircleOff className="h-4 w-4" />,
      tone: blockedRuns.length ? 'danger' : 'success',
    },
    {
      label: 'Ready to calculate',
      value: String(readyRuns.length),
      delta: `${calculatedRuns.length} run(s) are awaiting approval`,
      icon: <PlayCircle className="h-4 w-4" />,
      tone: readyRuns.length ? 'info' : 'neutral',
    },
    {
      label: 'Failed runs',
      value: String(failedRuns.length),
      delta: `${failedRuns.reduce((count, run) => count + (run.calculation_summary.error_count ?? 0), 0)} exception item(s) surfaced`,
      icon: <AlertTriangle className="h-4 w-4" />,
      tone: failedRuns.length ? 'warning' : 'success',
    },
    {
      label: 'Locked runs',
      value: String(lockedRuns.length),
      delta: `${payslips.length} payslip artifact(s) currently published`,
      icon: <Lock className="h-4 w-4" />,
      tone: lockedRuns.length ? 'success' : 'neutral',
    },
    {
      label: 'Net payroll ready',
      value: formatCurrency(
        runs.reduce((sum, run) => sum + Number(run.calculation_summary.net_salary_total ?? 0), 0),
      ),
      delta: 'Combined net from calculated, approved, and locked runs',
      icon: <BadgeCheck className="h-4 w-4" />,
      tone: 'info',
      valueSize: 'compact',
    },
  ]

  const attentionItems = useMemo(() => {
    const items: Array<{
      id: string
      path: string
      title: string
      detail: string
      meta: string
      tone: 'warning' | 'danger' | 'success' | 'info'
      icon: ReactNode
    }> = []

    const firstBlocked = blockedRuns[0]
    if (firstBlocked) {
      items.push({
        id: 'blocked-run',
        path: '/payroll/run-console',
        title: `${firstBlocked.name} is blocked`,
        detail: `${firstBlocked.prerequisite_summary.blocking_count} blocker(s) prevent calculation.`,
        meta: firstBlocked.prerequisite_snapshot.checks
          .filter((check) => check.status === 'blocked')
          .map((check) => check.title)
          .join(' · '),
        tone: 'danger',
        icon: <CircleOff className="h-4 w-4" />,
      })
    }

    const firstFailed = failedRuns[0]
    if (firstFailed) {
      items.push({
        id: 'failed-run',
        path: '/payroll/run-console',
        title: `${firstFailed.name} needs exception review`,
        detail: `${firstFailed.calculation_summary.error_count ?? 0} payroll item(s) failed validation during calculation.`,
        meta: firstFailed.items.find((item) => item.validation_errors.length)?.validation_errors[0] ?? 'Review exception details in the run console.',
        tone: 'warning',
        icon: <AlertTriangle className="h-4 w-4" />,
      })
    }

    const firstReady = readyRuns[0]
    if (firstReady) {
      items.push({
        id: 'ready-run',
        path: '/payroll/run-console',
        title: `${firstReady.name} is ready for calculation`,
        detail: `${firstReady.input_summary.employee_count ?? 0} employee(s) are staged with ${firstReady.input_summary.input_count ?? 0} payroll input row(s).`,
        meta: 'Open the run console to calculate, approve, and lock this run.',
        tone: 'info',
        icon: <PlayCircle className="h-4 w-4" />,
      })
    }

    const missingPayslipsRun = lockedRuns.find(
      (run) => !payslips.some((payslip) => payslip.payroll_run_id === run.id),
    )
    if (missingPayslipsRun) {
      items.push({
        id: 'missing-payslips',
        path: '/payroll/run-console',
        title: `${missingPayslipsRun.name} is locked without payslips`,
        detail: 'Generate payslips before releasing the employee-facing payroll package.',
        meta: 'Run console actions stay permission-aware and audit-ready.',
        tone: 'warning',
        icon: <FileText className="h-4 w-4" />,
      })
    }

    if (!items.length) {
      items.push({
        id: 'healthy',
        path: '/payroll/overview',
        title: 'Payroll posture looks healthy',
        detail: 'No blocked runs, failed calculations, or missing locked-run artifacts are currently flagged.',
        meta: 'Use the tables below to inspect periods, runs, and payslips in more detail.',
        tone: 'success',
        icon: <BadgeCheck className="h-4 w-4" />,
      })
    }

    return items.slice(0, 4)
  }, [blockedRuns, failedRuns, lockedRuns, payslips, readyRuns])

  const filteredRuns = useMemo(
    () =>
      filterBySearch(runs, deferredSearch, (run) => [
        run.name,
        formatRunStatus(run.status),
        run.start_date,
        run.end_date,
      ]),
    [deferredSearch, runs],
  )
  const filteredPeriods = useMemo(
    () =>
      filterBySearch(periods, deferredSearch, (period) => [
        period.name,
        period.status,
        period.start_date,
        period.end_date,
      ]),
    [deferredSearch, periods],
  )
  const filteredPayslips = useMemo(
    () =>
      filterBySearch(payslips, deferredSearch, (payslip) => [
        payslip.slip_number,
        payslip.employee?.full_name ?? '',
        payslip.employee?.employee_code ?? '',
      ]),
    [deferredSearch, payslips],
  )

  return (
    <WorkspacePage>
      <CommandCenterMetricGrid>
        {metricCards.map((card) => (
          <CommandCenterMetricCard key={card.label} {...card} />
        ))}
      </CommandCenterMetricGrid>

      <CommandCenterAttentionStrip title="Needs attention">
        {attentionItems.map((item) => (
          <CommandCenterAttentionItem
            key={item.id}
            title={item.title}
            detail={item.detail}
            meta={item.meta}
            tone={item.tone}
            icon={item.icon}
            to={item.path}
          />
        ))}
      </CommandCenterAttentionStrip>

      <CommandCenterLayout>
        <CommandCenterMain>
          <WorkspaceSurface>
            <WorkspaceHeader compact>
              <div className="min-w-0 space-y-1">
                <CardTitle>Payroll operations center</CardTitle>
                <CardDescription className="max-w-3xl">
                  Track run readiness, exception pressure, and payslip posture before payroll release.
                </CardDescription>
              </div>
              <WorkspaceHeaderActions>
                <Button asChild variant="secondary" size="xs">
                  <Link to="/payroll/run-console">
                    Open run console
                    <ArrowUpRight className="h-4 w-4" />
                  </Link>
                </Button>
              </WorkspaceHeaderActions>
            </WorkspaceHeader>
            <WorkspaceContent className="space-y-3.5">
              <ConsoleToolbar>
                <ConsoleToolbarRow>
                  <ConsoleSearchField
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder="Search periods, runs, slips, employees, or statuses"
                  />
                </ConsoleToolbarRow>
              </ConsoleToolbar>

              <WorkspaceTabs aria-label="Payroll overview tabs">
                {[
                  { id: 'runs' as const, label: 'Runs' },
                  { id: 'periods' as const, label: 'Periods' },
                  { id: 'payslips' as const, label: 'Payslips' },
                ].map((tab) => (
                  <WorkspaceTabButton
                    key={tab.id}
                    active={activeTab === tab.id}
                    aria-selected={activeTab === tab.id}
                    role="tab"
                    onClick={() => setActiveTab(tab.id)}
                  >
                    {tab.label}
                  </WorkspaceTabButton>
                ))}
              </WorkspaceTabs>

              {activeTab === 'runs' ? (
                <WorkspaceTableShell>
                  {filteredRuns.length ? (
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Run</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Employees</TableHead>
                          <TableHead>Net payroll</TableHead>
                          <TableHead>Updated</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {filteredRuns.map((run) => (
                          <TableRow key={run.id}>
                            <TableCell>
                              <div className="space-y-1">
                                <p className="font-medium text-foreground">{run.name}</p>
                                <p className="text-xs text-muted-foreground">
                                  {formatDate(run.start_date)} to {formatDate(run.end_date)}
                                </p>
                              </div>
                            </TableCell>
                            <TableCell>
                              <Badge variant={runStatusBadgeVariant(run.status)}>{formatRunStatus(run.status)}</Badge>
                            </TableCell>
                            <TableCell>{run.calculation_summary.employee_count ?? run.input_summary.employee_count ?? 0}</TableCell>
                            <TableCell>{formatCurrency(run.calculation_summary.net_salary_total ?? 0)}</TableCell>
                            <TableCell>{formatRelativeTimestamp(run.updated_at)}</TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  ) : (
                    <WorkspaceEmptyState
                      title="No payroll runs match this search"
                      copy="Broaden the search to inspect other periods, statuses, or employee payroll runs."
                    />
                  )}
                </WorkspaceTableShell>
              ) : null}

              {activeTab === 'periods' ? (
                <WorkspaceTableShell>
                  {filteredPeriods.length ? (
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Period</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Payroll date</TableHead>
                          <TableHead>Latest run</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {filteredPeriods.map((period) => (
                          <TableRow key={period.id}>
                            <TableCell>
                              <div className="space-y-1">
                                <p className="font-medium text-foreground">{period.name}</p>
                                <p className="text-xs text-muted-foreground">
                                  {formatDate(period.start_date)} to {formatDate(period.end_date)}
                                </p>
                              </div>
                            </TableCell>
                            <TableCell>
                              <Badge variant={periodStatusBadgeVariant(period.status)}>{period.status}</Badge>
                            </TableCell>
                            <TableCell>{formatDate(period.payroll_date)}</TableCell>
                            <TableCell>
                              {period.latest_run ? (
                                <Badge variant={runStatusBadgeVariant(period.latest_run.status)}>
                                  {formatRunStatus(period.latest_run.status)}
                                </Badge>
                              ) : (
                                <span className="text-xs text-muted-foreground">No run yet</span>
                              )}
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  ) : (
                    <WorkspaceEmptyState
                      title="No payroll periods match this search"
                      copy="Try another period name, date window, or status keyword."
                    />
                  )}
                </WorkspaceTableShell>
              ) : null}

              {activeTab === 'payslips' ? (
                <WorkspaceTableShell>
                  {filteredPayslips.length ? (
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Payslip</TableHead>
                          <TableHead>Employee</TableHead>
                          <TableHead>Run</TableHead>
                          <TableHead>Net salary</TableHead>
                          <TableHead>Generated</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {filteredPayslips.map((payslip) => (
                          <TableRow key={payslip.id}>
                            <TableCell>
                              <div className="space-y-1">
                                <p className="font-medium text-foreground">{payslip.slip_number}</p>
                                <p className="text-xs text-muted-foreground">{payslip.file_name}</p>
                              </div>
                            </TableCell>
                            <TableCell>
                              <div className="space-y-1">
                                <p className="font-medium text-foreground">{payslip.employee?.full_name}</p>
                                <p className="text-xs text-muted-foreground">{payslip.employee?.employee_code}</p>
                              </div>
                            </TableCell>
                            <TableCell>Run #{payslip.payroll_run_id}</TableCell>
                            <TableCell>{formatCurrency(payslip.net_salary)}</TableCell>
                            <TableCell>{formatRelativeTimestamp(payslip.generated_at)}</TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  ) : (
                    <WorkspaceEmptyState
                      title="No payslips match this search"
                      copy="Generate payslips from a locked run in the run console, or refine the search to find an existing slip."
                    />
                  )}
                </WorkspaceTableShell>
              ) : null}
            </WorkspaceContent>
          </WorkspaceSurface>
        </CommandCenterMain>

        <CommandCenterRail>
          <CommandCenterPanel
            title="Release posture"
            description="A quick read on what payroll can safely move next."
          >
            <div className="divide-y divide-line/80 px-4 py-3">
              <div className="space-y-1 py-2">
                <p className="text-xs font-semibold uppercase tracking-[0.12em] text-text-subtle">Next ready step</p>
                <p className="text-sm text-foreground">
                  {readyRuns[0]
                    ? `${readyRuns[0].name} is ready to calculate`
                    : calculatedRuns[0]
                      ? `${calculatedRuns[0].name} is ready for approval`
                      : periodsReadyToClose[0]
                        ? `${periodsReadyToClose[0].name} is ready to close`
                        : 'No high-priority payroll action is pending.'}
                </p>
              </div>
              <div className="space-y-1 py-2">
                <p className="text-xs font-semibold uppercase tracking-[0.12em] text-text-subtle">Blocked exposure</p>
                <p className="text-sm text-foreground">
                  {blockedRuns.length
                    ? `${blockedRuns.length} blocked run(s) still need attendance, leave, or adjustment cleanup.`
                    : 'No blocked runs are currently holding payroll.'}
                </p>
              </div>
              <div className="space-y-1 py-2">
                <p className="text-xs font-semibold uppercase tracking-[0.12em] text-text-subtle">Payout artifacts</p>
                <p className="text-sm text-foreground">
                  {payslips.length
                    ? `${payslips.length} payslip artifact(s) are already published for locked payroll.`
                    : 'No payslips have been generated yet for the current payroll set.'}
                </p>
              </div>
            </div>
          </CommandCenterPanel>
        </CommandCenterRail>
      </CommandCenterLayout>
    </WorkspacePage>
  )
}

function filterBySearch<T>(records: T[], query: string, extractor: (record: T) => string[]) {
  if (!query) {
    return records
  }

  return records.filter((record) =>
    extractor(record).some((value) => value.toLowerCase().includes(query)),
  )
}
