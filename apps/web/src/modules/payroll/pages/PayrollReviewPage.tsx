import { useDeferredValue, useMemo, useState, type ReactNode } from 'react'
import { Link } from 'react-router-dom'
import {
  AlertTriangle,
  ArrowUpRight,
  BadgeCheck,
  FileWarning,
  Lock,
  TrendingUp,
} from 'lucide-react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { ConsoleSearchField, ConsoleToolbar, ConsoleToolbarRow } from '../../../shared/ui/console-table'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceHeader,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSplit,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import type {
  PayrollCalculationSummary,
  PayrollRunRecord,
  PayrollRunStatus,
} from '../types'
import { formatCurrency, formatDate, formatRelativeTimestamp, formatRunStatus, runStatusBadgeVariant } from '../utils'
import { usePayrollRouteWorkspace } from './usePayrollRouteWorkspace'

type ReviewTab = 'summary' | 'exceptions' | 'variances'

type ReviewFilter = 'all' | 'blocked' | 'failed' | 'ready' | 'locked'

type ExceptionSeverity = 'critical' | 'warning' | 'info'

type VarianceSignal = 'baseline' | 'stable' | 'watch' | 'critical' | 'pending'

interface SummaryRow {
  runId: number
  runName: string
  periodLabel: string
  status: PayrollRunStatus
  employeeCount: number
  exceptionCount: number
  payslipCoverage: string
  netPayroll: number | null
  totalDeductions: number | null
  varianceSignal: VarianceSignal
}

interface ExceptionEntry {
  id: string
  runId: number
  runName: string
  status: PayrollRunStatus
  severity: ExceptionSeverity
  category: 'Prerequisite' | 'Validation' | 'Release'
  subject: string
  detail: string
}

interface VarianceEntry {
  runId: number
  runName: string
  status: PayrollRunStatus
  baselineLabel: string
  signal: VarianceSignal
  summary: string
  netDelta: number | null
  netDeltaPercent: number | null
  deductionDelta: number | null
  unpaidDaysDelta: number | null
  overtimeDelta: number | null
}

const reviewFilters: Array<{ id: ReviewFilter; label: string }> = [
  { id: 'all', label: 'All runs' },
  { id: 'blocked', label: 'Blocked' },
  { id: 'failed', label: 'Failed' },
  { id: 'ready', label: 'Ready' },
  { id: 'locked', label: 'Locked' },
]

export function PayrollReviewPage() {
  const workspace = usePayrollRouteWorkspace()
  const [activeTab, setActiveTab] = useState<ReviewTab>('summary')
  const [statusFilter, setStatusFilter] = useState<ReviewFilter>('all')
  const [search, setSearch] = useState('')
  const deferredSearch = useDeferredValue(search.trim().toLowerCase())

  const runs = useMemo(() => workspace.data?.runs ?? [], [workspace.data?.runs])
  const payslips = useMemo(() => workspace.data?.payslips ?? [], [workspace.data?.payslips])

  const runsByFilter = useMemo(
    () =>
      runs.filter((run) => {
        if (statusFilter === 'all') {
          return true
        }

        return run.status === statusFilter
      }),
    [runs, statusFilter],
  )

  const rawVarianceEntries = useMemo(() => buildVarianceEntries(runsByFilter), [runsByFilter])
  const varianceEntries = useMemo(
    () =>
      rawVarianceEntries.filter((entry) =>
        matchesSearch(deferredSearch, [
          entry.runName,
          entry.baselineLabel,
          entry.summary,
          describeVarianceSignal(entry.signal),
        ]),
      ),
    [deferredSearch, rawVarianceEntries],
  )
  const varianceSignalsByRunId = useMemo(
    () => new Map(rawVarianceEntries.map((entry) => [entry.runId, entry.signal] as const)),
    [rawVarianceEntries],
  )
  const summaryRows = useMemo(
    () =>
      runsByFilter
        .map((run) => createSummaryRow(run, payslips, varianceSignalsByRunId.get(run.id) ?? 'pending'))
        .filter((row) =>
          matchesSearch(deferredSearch, [
            row.runName,
            row.periodLabel,
            formatRunStatus(row.status),
            row.payslipCoverage,
          ]),
        ),
    [deferredSearch, payslips, runsByFilter, varianceSignalsByRunId],
  )

  const exceptionEntries = useMemo(
    () =>
      buildExceptionEntries(runsByFilter, payslips).filter((entry) =>
        matchesSearch(deferredSearch, [
          entry.runName,
          formatRunStatus(entry.status),
          entry.category,
          entry.subject,
          entry.detail,
        ]),
      ),
    [deferredSearch, payslips, runsByFilter],
  )

  const visibleRunIds = new Set(summaryRows.map((row) => row.runId))
  const focusedRun =
    runs.find((run) => run.id === workspace.selectedRun?.id && visibleRunIds.has(run.id)) ??
    runsByFilter.find((run) => visibleRunIds.has(run.id)) ??
    null
  const focusedSummary = summaryRows.find((row) => row.runId === focusedRun?.id) ?? null
  const focusedVariance = varianceEntries.find((entry) => entry.runId === focusedRun?.id) ?? null
  const focusedPayslipCount = payslips.filter((record) => record.payroll_run_id === focusedRun?.id).length
  const canViewAmounts = workspace.canViewPayrollAmounts
  const publishedPayslipCount = summaryRows.reduce((total, row) => total + Number(row.payslipCoverage.split('/')[0] ?? 0), 0)
  const expectedPayslipCount = summaryRows.reduce((total, row) => total + row.employeeCount, 0)

  const metrics = [
    {
      label: 'Runs in view',
      value: String(summaryRows.length),
      caption: `${runsByFilter.length} run(s) match the active status filter`,
      icon: <Lock className="h-4 w-4" />,
    },
    {
      label: 'Exceptions flagged',
      value: String(exceptionEntries.length),
      caption: `${exceptionEntries.filter((entry) => entry.severity === 'critical').length} critical issue(s) need action`,
      icon: <AlertTriangle className="h-4 w-4" />,
    },
    {
      label: 'Variance watchlist',
      value: String(
        varianceEntries.filter((entry) => entry.signal === 'watch' || entry.signal === 'critical').length,
      ),
      caption: `${varianceEntries.filter((entry) => entry.signal === 'pending').length} run(s) still need calculation data`,
      icon: <TrendingUp className="h-4 w-4" />,
    },
    {
      label: 'Payslip coverage',
      value: `${publishedPayslipCount}/${expectedPayslipCount}`,
      caption: 'Generated payslips compared with employee payroll items in scope',
      icon: <BadgeCheck className="h-4 w-4" />,
    },
    {
      label: 'Net payroll in scope',
      value: canViewAmounts
        ? formatCurrency(summaryRows.reduce((total, row) => total + (row.netPayroll ?? 0), 0))
        : 'Restricted',
      caption: canViewAmounts
        ? 'Combined net payroll across the visible runs'
        : 'Monetary totals appear only for payroll-view sessions.',
      icon: <FileWarning className="h-4 w-4" />,
    },
  ]

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Payroll Review"
          title="Payroll Review"
          description="Inspect payroll summaries, flagged exceptions, and variance signals before releasing finalized payroll artifacts."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo review surface' : 'Live review surface'}</Badge>}
          context={[
            `${summaryRows.length} run(s) in scope`,
            `${exceptionEntries.filter((entry) => entry.severity === 'critical').length} critical issue(s)`,
            canViewAmounts ? 'Amount visibility enabled' : 'Amount visibility restricted',
          ]}
          actions={
            <Button asChild size="xs">
              <Link to="/payroll/run-console">
                Open run console
                <ArrowUpRight className="h-4 w-4" />
              </Link>
            </Button>
          }
        />

        <WorkspaceContent className="space-y-3.5">
          <ConsoleToolbar>
            <ConsoleToolbarRow>
              <ConsoleSearchField
                value={search}
                onChange={(event) => setSearch(event.target.value)}
                placeholder="Search payroll runs, exceptions, or variance notes"
              />
            </ConsoleToolbarRow>
            <WorkspaceTabs aria-label="Payroll review status filters">
              {reviewFilters.map((filter) => (
                <WorkspaceTabButton
                  key={filter.id}
                  active={statusFilter === filter.id}
                  aria-selected={statusFilter === filter.id}
                  role="tab"
                  onClick={() => setStatusFilter(filter.id)}
                >
                  {filter.label}
                </WorkspaceTabButton>
              ))}
            </WorkspaceTabs>
          </ConsoleToolbar>

          <div className="organization-metric-grid">
            {metrics.map((metric) => (
              <ReviewMetricCard
                key={metric.label}
                label={metric.label}
                value={metric.value}
                caption={metric.caption}
                icon={metric.icon}
              />
            ))}
          </div>

          <WorkspaceTabs aria-label="Payroll review sections">
            <WorkspaceTabButton
              active={activeTab === 'summary'}
              aria-selected={activeTab === 'summary'}
              role="tab"
              onClick={() => setActiveTab('summary')}
            >
              Run summaries
            </WorkspaceTabButton>
            <WorkspaceTabButton
              active={activeTab === 'exceptions'}
              aria-selected={activeTab === 'exceptions'}
              role="tab"
              onClick={() => setActiveTab('exceptions')}
            >
              Exceptions
            </WorkspaceTabButton>
            <WorkspaceTabButton
              active={activeTab === 'variances'}
              aria-selected={activeTab === 'variances'}
              role="tab"
              onClick={() => setActiveTab('variances')}
            >
              Variances
            </WorkspaceTabButton>
          </WorkspaceTabs>

          <WorkspaceSplit className="xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)]">
            <div className="space-y-3.5">
              {activeTab === 'summary' ? (
                summaryRows.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Run</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead>Employees</TableHead>
                          <TableHead>Exceptions</TableHead>
                          <TableHead>Payslips</TableHead>
                          <TableHead>Net payroll</TableHead>
                          <TableHead>Variance</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {summaryRows.map((row) => (
                          <TableRow
                            key={row.runId}
                            className={row.runId === focusedRun?.id ? 'bg-primary/[0.06]' : undefined}
                            onClick={() => workspace.selectRun(row.runId)}
                          >
                            <TableCell>
                              <div className="space-y-1">
                                <p className="font-medium text-foreground">{row.runName}</p>
                                <p className="text-xs text-muted-foreground">{row.periodLabel}</p>
                              </div>
                            </TableCell>
                            <TableCell>
                              <Badge variant={runStatusBadgeVariant(row.status)}>{formatRunStatus(row.status)}</Badge>
                            </TableCell>
                            <TableCell>{row.employeeCount}</TableCell>
                            <TableCell>{row.exceptionCount}</TableCell>
                            <TableCell>{row.payslipCoverage}</TableCell>
                            <TableCell>{formatSensitiveAmount(row.netPayroll, canViewAmounts)}</TableCell>
                            <TableCell>
                              <Badge variant={varianceBadgeVariant(row.varianceSignal)}>
                                {describeVarianceSignal(row.varianceSignal)}
                              </Badge>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No payroll summaries match this view"
                    copy="Broaden the search or switch the run-status filter to inspect other payroll summaries."
                  />
                )
              ) : null}

              {activeTab === 'exceptions' ? (
                exceptionEntries.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Severity</TableHead>
                          <TableHead>Run</TableHead>
                          <TableHead>Category</TableHead>
                          <TableHead>Issue</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {exceptionEntries.map((entry) => (
                          <TableRow
                            key={entry.id}
                            className={entry.runId === focusedRun?.id ? 'bg-primary/[0.06]' : undefined}
                            onClick={() => workspace.selectRun(entry.runId)}
                          >
                            <TableCell>
                              <Badge variant={exceptionBadgeVariant(entry.severity)}>
                                {entry.severity === 'critical'
                                  ? 'Critical'
                                  : entry.severity === 'warning'
                                    ? 'Warning'
                                    : 'Info'}
                              </Badge>
                            </TableCell>
                            <TableCell>
                              <div className="space-y-1">
                                <p className="font-medium text-foreground">{entry.runName}</p>
                                <p className="text-xs text-muted-foreground">{formatRunStatus(entry.status)}</p>
                              </div>
                            </TableCell>
                            <TableCell>{entry.category}</TableCell>
                            <TableCell>
                              <div className="space-y-1">
                                <p className="font-medium text-foreground">{entry.subject}</p>
                                <p className="text-xs text-muted-foreground">{entry.detail}</p>
                              </div>
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No payroll exceptions match this view"
                    copy="This review slice is clear for the active filter and search terms."
                  />
                )
              ) : null}

              {activeTab === 'variances' ? (
                varianceEntries.length ? (
                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Run</TableHead>
                          <TableHead>Baseline</TableHead>
                          <TableHead>Signal</TableHead>
                          <TableHead>Net delta</TableHead>
                          <TableHead>Deductions delta</TableHead>
                          <TableHead>Summary</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {varianceEntries.map((entry) => (
                          <TableRow
                            key={entry.runId}
                            className={entry.runId === focusedRun?.id ? 'bg-primary/[0.06]' : undefined}
                            onClick={() => workspace.selectRun(entry.runId)}
                          >
                            <TableCell>
                              <div className="space-y-1">
                                <p className="font-medium text-foreground">{entry.runName}</p>
                                <p className="text-xs text-muted-foreground">{formatRunStatus(entry.status)}</p>
                              </div>
                            </TableCell>
                            <TableCell>{entry.baselineLabel}</TableCell>
                            <TableCell>
                              <Badge variant={varianceBadgeVariant(entry.signal)}>
                                {describeVarianceSignal(entry.signal)}
                              </Badge>
                            </TableCell>
                            <TableCell>{formatDeltaCurrency(entry.netDelta, canViewAmounts)}</TableCell>
                            <TableCell>{formatDeltaCurrency(entry.deductionDelta, canViewAmounts)}</TableCell>
                            <TableCell>{entry.summary}</TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                ) : (
                  <WorkspaceEmptyState
                    title="No payroll variances match this view"
                    copy="Variance indicators appear here once the active run slice has comparable calculation output."
                  />
                )
              ) : null}
            </div>

            <WorkspaceSurface>
              <WorkspaceHeader compact>
                <div className="min-w-0 space-y-1">
                  <h2 className="text-base font-semibold text-foreground">
                    {focusedRun?.name ?? 'Select a payroll run'}
                  </h2>
                  <p className="text-sm text-muted-foreground">
                    {focusedRun
                      ? 'Review release readiness, exception posture, and variance indicators for the selected payroll run.'
                      : 'Choose a summary, exception, or variance row to inspect run-level detail.'}
                  </p>
                </div>
                {focusedRun ? (
                  <Badge variant={runStatusBadgeVariant(focusedRun.status)}>
                    {formatRunStatus(focusedRun.status)}
                  </Badge>
                ) : null}
              </WorkspaceHeader>
              <WorkspaceContent>
                {focusedRun && focusedSummary ? (
                  <>
                    <WorkspaceSummaryRow
                      label="Period window"
                      value={`${formatDate(focusedRun.start_date)} to ${formatDate(focusedRun.end_date)}`}
                    />
                    <WorkspaceSummaryRow
                      label="Employee payroll items"
                      value={String(focusedSummary.employeeCount)}
                    />
                    <WorkspaceSummaryRow
                      label="Exception count"
                      value={String(focusedSummary.exceptionCount)}
                    />
                    <WorkspaceSummaryRow
                      label="Payslip coverage"
                      value={`${focusedPayslipCount}/${focusedSummary.employeeCount}`}
                    />
                    <WorkspaceSummaryRow
                      label="Net payroll"
                      value={formatSensitiveAmount(focusedSummary.netPayroll, canViewAmounts)}
                    />
                    <WorkspaceSummaryRow
                      label="Total deductions"
                      value={formatSensitiveAmount(focusedSummary.totalDeductions, canViewAmounts)}
                    />
                    <WorkspaceSummaryRow
                      label="Variance signal"
                      value={focusedVariance ? describeVarianceSignal(focusedVariance.signal) : 'Pending'}
                    />
                    <WorkspaceSummaryRow
                      label="Last updated"
                      value={formatRelativeTimestamp(focusedRun.updated_at)}
                    />
                    <WorkspaceSummaryRow
                      label="Review note"
                      value={buildRunReviewNote(focusedRun, focusedVariance)}
                    />
                    {!canViewAmounts ? (
                      <WorkspaceEmptyState
                        title="Sensitive payroll values are restricted"
                        copy="This session can review run posture and exception counts, but monetary payroll totals stay hidden until payroll-view permissions are present."
                      />
                    ) : null}
                  </>
                ) : (
                  <WorkspaceEmptyState
                    title="No payroll run selected"
                    copy="Choose a row from the current review table to inspect the run summary and release posture."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>
          </WorkspaceSplit>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function createSummaryRow(
  run: PayrollRunRecord,
  payslips: Array<{ payroll_run_id: number }>,
  varianceSignal: VarianceSignal,
): SummaryRow {
  const itemCount = Number(run.calculation_summary.item_count ?? run.items.length ?? 0)
  const payslipCount = payslips.filter((record) => record.payroll_run_id === run.id).length
  const exceptionCount =
    run.items.filter((item) => item.validation_errors.length).length +
    run.prerequisite_snapshot.checks.filter((check) => check.status !== 'passed').length

  return {
    runId: run.id,
    runName: run.name,
    periodLabel: `${formatDate(run.start_date)} to ${formatDate(run.end_date)}`,
    status: run.status,
    employeeCount: itemCount || Number(run.input_summary.employee_count ?? 0),
    exceptionCount,
    payslipCoverage: itemCount ? `${payslipCount}/${itemCount}` : `${payslipCount}/0`,
    netPayroll: readNumber(run.calculation_summary.net_salary_total),
    totalDeductions: readNumber(run.calculation_summary.total_deductions),
    varianceSignal,
  }
}

function buildExceptionEntries(
  runs: PayrollRunRecord[],
  payslips: Array<{ payroll_run_id: number }>,
): ExceptionEntry[] {
  return runs.flatMap((run) => {
    const prerequisiteEntries = run.prerequisite_snapshot.checks
      .filter((check) => check.status !== 'passed')
      .map((check, index) => ({
        id: `prerequisite-${run.id}-${check.code}-${index}`,
        runId: run.id,
        runName: run.name,
        status: run.status,
        severity: (check.status === 'blocked' ? 'critical' : 'warning') as ExceptionSeverity,
        category: 'Prerequisite' as const,
        subject: check.title,
        detail: check.detail,
      }))

    const validationEntries = run.items
      .filter((item) => item.validation_errors.length)
      .flatMap((item, itemIndex) =>
        item.validation_errors.map((message, errorIndex) => ({
          id: `validation-${run.id}-${item.id}-${itemIndex}-${errorIndex}`,
          runId: run.id,
          runName: run.name,
          status: run.status,
          severity: 'critical' as const,
          category: 'Validation' as const,
          subject: item.employee?.full_name ?? `Payroll item #${item.id}`,
          detail: message,
        })),
      )

    const releaseEntries =
      run.status === 'locked' &&
      Number(run.calculation_summary.item_count ?? run.items.length ?? 0) > 0 &&
      !payslips.some((record) => record.payroll_run_id === run.id)
        ? [
            {
              id: `release-${run.id}`,
              runId: run.id,
              runName: run.name,
              status: run.status,
              severity: 'warning' as const,
              category: 'Release' as const,
              subject: 'Locked run is missing published payslips',
              detail: 'Generate finalized payslips before releasing this payroll run to employees.',
            },
          ]
        : []

    return [...prerequisiteEntries, ...validationEntries, ...releaseEntries]
  })
}

function buildVarianceEntries(runs: PayrollRunRecord[]): VarianceEntry[] {
  const comparableRuns = [...runs].sort((left, right) => left.end_date.localeCompare(right.end_date))

  return comparableRuns.map((run, index) => {
    const previousComparableRun = comparableRuns
      .slice(0, index)
      .reverse()
      .find((candidate) => hasCalculationOutput(candidate.calculation_summary))

    const currentNet = readNumber(run.calculation_summary.net_salary_total)
    const currentDeductions = readNumber(run.calculation_summary.total_deductions)
    const currentUnpaidDays = readNumber(run.calculation_summary.total_unpaid_days)
    const currentOvertime = readNumber(run.calculation_summary.total_overtime_earnings)

    const previousNet = readNumber(previousComparableRun?.calculation_summary.net_salary_total)
    const previousDeductions = readNumber(previousComparableRun?.calculation_summary.total_deductions)
    const previousUnpaidDays = readNumber(previousComparableRun?.calculation_summary.total_unpaid_days)
    const previousOvertime = readNumber(previousComparableRun?.calculation_summary.total_overtime_earnings)

    const netDelta = currentNet !== null && previousNet !== null ? currentNet - previousNet : null
    const deductionDelta =
      currentDeductions !== null && previousDeductions !== null ? currentDeductions - previousDeductions : null
    const unpaidDaysDelta =
      currentUnpaidDays !== null && previousUnpaidDays !== null ? currentUnpaidDays - previousUnpaidDays : null
    const overtimeDelta =
      currentOvertime !== null && previousOvertime !== null ? currentOvertime - previousOvertime : null

    const netDeltaPercent =
      netDelta !== null && previousNet && previousNet !== 0 ? (netDelta / previousNet) * 100 : null

    const signal = deriveVarianceSignal(run, netDeltaPercent, deductionDelta, unpaidDaysDelta)

    return {
      runId: run.id,
      runName: run.name,
      status: run.status,
      baselineLabel: previousComparableRun?.name ?? 'No prior comparable run',
      signal,
      summary: buildVarianceSummary(signal, run, netDeltaPercent, unpaidDaysDelta),
      netDelta,
      netDeltaPercent,
      deductionDelta,
      unpaidDaysDelta,
      overtimeDelta,
    }
  })
}

function deriveVarianceSignal(
  run: PayrollRunRecord,
  netDeltaPercent: number | null,
  deductionDelta: number | null,
  unpaidDaysDelta: number | null,
): VarianceSignal {
  if (run.prerequisite_summary.blocking_count > 0 || Number(run.calculation_summary.error_count ?? 0) > 0) {
    return 'critical'
  }

  if (!hasCalculationOutput(run.calculation_summary)) {
    return 'pending'
  }

  if (netDeltaPercent === null) {
    return 'baseline'
  }

  if (
    Math.abs(netDeltaPercent) >= 12 ||
    Math.abs(deductionDelta ?? 0) >= 5000 ||
    Math.abs(unpaidDaysDelta ?? 0) >= 1
  ) {
    return 'watch'
  }

  return 'stable'
}

function buildVarianceSummary(
  signal: VarianceSignal,
  run: PayrollRunRecord,
  netDeltaPercent: number | null,
  unpaidDaysDelta: number | null,
) {
  if (signal === 'critical') {
    if (run.prerequisite_summary.blocking_count > 0) {
      return `${run.prerequisite_summary.blocking_count} blocker(s) still prevent a clean payroll release.`
    }

    return `${Number(run.calculation_summary.error_count ?? 0)} calculation exception(s) are still unresolved.`
  }

  if (signal === 'pending') {
    return 'Awaiting calculation data before variance comparison can be trusted.'
  }

  if (signal === 'baseline') {
    return 'This is the first comparable run with calculation output in the current review slice.'
  }

  if (signal === 'watch') {
    return `${formatSignedPercent(netDeltaPercent)} net change with ${formatSignedNumber(unpaidDaysDelta)} unpaid-day movement versus the prior comparable run.`
  }

  return `${formatSignedPercent(netDeltaPercent)} net change versus the prior comparable run.`
}

function buildRunReviewNote(run: PayrollRunRecord, variance: VarianceEntry | null) {
  if (run.prerequisite_summary.blocking_count > 0) {
    return 'Resolve prerequisite blockers before calculating or releasing this payroll run.'
  }

  if (Number(run.calculation_summary.error_count ?? 0) > 0) {
    return 'Review failed payroll items before rerunning or moving into approval.'
  }

  if (run.status === 'locked') {
    return 'Run is locked; release posture depends on generated payslip coverage and close-readiness.'
  }

  if (variance) {
    return variance.summary
  }

  return 'No review note is currently available for this payroll run.'
}

function hasCalculationOutput(summary: Partial<PayrollCalculationSummary>) {
  return Number(summary.item_count ?? 0) > 0 || readNumber(summary.net_salary_total) !== null
}

function matchesSearch(search: string, fields: Array<string | null | undefined>) {
  if (!search) {
    return true
  }

  return fields
    .filter(Boolean)
    .join(' ')
    .toLowerCase()
    .includes(search)
}

function readNumber(value: number | string | null | undefined) {
  if (value === null || value === undefined || value === '') {
    return null
  }

  const parsed = typeof value === 'string' ? Number(value) : value
  return Number.isNaN(parsed) ? null : parsed
}

function formatSensitiveAmount(value: number | null, canViewAmounts: boolean) {
  if (!canViewAmounts) {
    return 'Restricted'
  }

  if (value === null) {
    return 'Pending'
  }

  return formatCurrency(value)
}

function formatDeltaCurrency(value: number | null, canViewAmounts: boolean) {
  if (!canViewAmounts) {
    return 'Restricted'
  }

  if (value === null) {
    return 'Pending'
  }

  return `${value >= 0 ? '+' : ''}${formatCurrency(value)}`
}

function formatSignedPercent(value: number | null) {
  if (value === null) {
    return 'Pending'
  }

  return `${value >= 0 ? '+' : ''}${value.toFixed(1)}%`
}

function formatSignedNumber(value: number | null) {
  if (value === null) {
    return '0.0'
  }

  return `${value >= 0 ? '+' : ''}${value.toFixed(1)}`
}

function describeVarianceSignal(signal: VarianceSignal) {
  switch (signal) {
    case 'critical':
      return 'Needs review'
    case 'watch':
      return 'Watch'
    case 'baseline':
      return 'Baseline'
    case 'pending':
      return 'Pending'
    case 'stable':
    default:
      return 'Stable'
  }
}

function exceptionBadgeVariant(severity: ExceptionSeverity) {
  switch (severity) {
    case 'critical':
      return 'danger' as const
    case 'warning':
      return 'warning' as const
    case 'info':
    default:
      return 'info' as const
  }
}

function varianceBadgeVariant(signal: VarianceSignal) {
  switch (signal) {
    case 'critical':
      return 'danger' as const
    case 'watch':
      return 'warning' as const
    case 'baseline':
      return 'info' as const
    case 'pending':
      return 'neutral' as const
    case 'stable':
    default:
      return 'success' as const
  }
}

function ReviewMetricCard({
  label,
  value,
  caption,
  icon,
}: {
  label: string
  value: string
  caption: string
  icon: ReactNode
}) {
  return (
    <WorkspaceSurface>
      <WorkspaceContent className="space-y-2">
        <div className="flex items-center justify-between gap-3">
          <p className="text-xs font-semibold uppercase tracking-[0.22em] text-muted-foreground">{label}</p>
          <span className="text-muted-foreground">{icon}</span>
        </div>
        <p className="text-3xl font-semibold tracking-tight text-foreground">{value}</p>
        <p className="text-sm text-muted-foreground">{caption}</p>
      </WorkspaceContent>
    </WorkspaceSurface>
  )
}
