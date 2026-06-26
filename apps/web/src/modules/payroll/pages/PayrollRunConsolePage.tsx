import { useDeferredValue, useMemo, useState } from 'react'
import { CheckCircle2, Lock, PlayCircle, RotateCcw, ShieldCheck } from 'lucide-react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
import { ConsoleSearchField, ConsoleToolbar, ConsoleToolbarRow } from '../../../shared/ui/console-table'
import { Input } from '../../../shared/ui/input'
import { SelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSplit,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import { usePayrollRunAdjustments } from '../hooks/usePayrollRunAdjustments'
import type { PayrollAdjustmentRecord, PayrollRunRecord, PayrollRunStatus } from '../types'
import {
  formatCurrency,
  formatDate,
  formatPeriodStatus,
  formatRelativeTimestamp,
  formatRunStatus,
  periodStatusBadgeVariant,
  runStatusBadgeVariant,
} from '../utils'
import { usePayrollRouteWorkspace } from './usePayrollRouteWorkspace'

const runStatusFilters: Array<{ id: 'all' | PayrollRunStatus; label: string }> = [
  { id: 'all', label: 'All runs' },
  { id: 'ready', label: 'Ready' },
  { id: 'blocked', label: 'Blocked' },
  { id: 'failed', label: 'Failed' },
  { id: 'locked', label: 'Locked' },
]

export function PayrollRunConsolePage() {
  const workspace = usePayrollRouteWorkspace()
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState<'all' | PayrollRunStatus>('all')
  const [approveComment, setApproveComment] = useState('')
  const [reopenReason, setReopenReason] = useState('')
  const deferredSearch = useDeferredValue(search.trim().toLowerCase())

  const periods = useMemo(() => workspace.data?.periods ?? [], [workspace.data?.periods])
  const runs = useMemo(() => workspace.data?.runs ?? [], [workspace.data?.runs])
  const filteredPeriods = useMemo(
    () =>
      periods.filter((period) => {
        if (!deferredSearch) {
          return true
        }

        return [period.name, period.status, period.latest_run?.name ?? '']
          .join(' ')
          .toLowerCase()
          .includes(deferredSearch)
      }),
    [deferredSearch, periods],
  )
  const filteredRuns = useMemo(
    () =>
      runs.filter((run) => {
        if (statusFilter !== 'all' && run.status !== statusFilter) {
          return false
        }

        if (!deferredSearch) {
          return true
        }

        return [run.name, run.status, run.start_date, run.end_date]
          .join(' ')
          .toLowerCase()
          .includes(deferredSearch)
      }),
    [deferredSearch, runs, statusFilter],
  )

  const selectedRun = workspace.selectedRun
  const selectedPeriod = workspace.selectedPeriod
  const selectedRunErrors = selectedRun?.items.filter((item) => item.validation_errors.length) ?? []
  const selectedRunPayslips = workspace.payslipsForSelectedRun
  const runReadyToClose = selectedPeriod?.status === 'prepared' && selectedRun?.status === 'locked'
  const canOpenPeriod = workspace.canProcessPayroll && selectedPeriod?.status === 'draft'
  const canPreparePeriod =
    workspace.canProcessPayroll &&
    !!selectedPeriod &&
    ['open', 'prepared'].includes(selectedPeriod.status) &&
    !(selectedRun && ['approved', 'locked'].includes(selectedRun.status))
  const canCalculateRun = workspace.canProcessPayroll && selectedRun?.status === 'ready'
  const canApproveRun = workspace.canApprovePayroll && selectedRun?.status === 'calculated'
  const canLockRun = workspace.canLockPayroll && selectedRun?.status === 'approved'
  const canReopenRun = workspace.canReopenPayroll && !!selectedRun && ['approved', 'locked'].includes(selectedRun.status)
  const canGeneratePayslips =
    (workspace.canLockPayroll || workspace.canProcessPayroll) &&
    selectedRun?.status === 'locked' &&
    selectedRun.items.length > 0

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Payroll Console"
          title="Payroll Run Console"
          description="Prepare periods, calculate payroll, inspect exceptions, and move locked runs through release controls."
          badge={<Badge variant={workspace.source === 'demo' ? 'warning' : 'info'}>{workspace.source === 'demo' ? 'Demo console surface' : 'Live console surface'}</Badge>}
          context={[
            `${filteredPeriods.length} period(s) in scope`,
            `${filteredRuns.length} run(s) in scope`,
            selectedPeriod?.name ?? 'No period selected',
          ]}
          actions={
            <>
              {workspace.pendingActionLabel ? <Badge variant="info">{workspace.pendingActionLabel}</Badge> : null}
              {workspace.lastActionMessage ? <Badge variant="success">{workspace.lastActionMessage}</Badge> : null}
              {workspace.actionError ? <Badge variant="danger">{workspace.actionError}</Badge> : null}
            </>
          }
        />

        <WorkspaceContent className="space-y-3.5">
          <ConsoleToolbar>
            <ConsoleToolbarRow>
              <ConsoleSearchField
                value={search}
                onChange={(event) => setSearch(event.target.value)}
                placeholder="Search payroll periods, runs, or statuses"
              />
            </ConsoleToolbarRow>
            <WorkspaceTabs aria-label="Payroll run status filters">
              {runStatusFilters.map((filter) => (
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

          {!filteredPeriods.length ? (
            <WorkspaceEmptyState
              title="No payroll periods match this view"
              copy="Try another search term or switch the run-status filter to inspect a different slice of payroll operations."
            />
          ) : (
            <WorkspaceSplit className="xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)]">
              <div className="space-y-3.5">
                <WorkspaceTableShell>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Period</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Latest run</TableHead>
                        <TableHead>Payroll date</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {filteredPeriods.map((period) => {
                        const isSelected = period.id === selectedPeriod?.id
                        const visibleRun =
                          period.latest_run && filteredRuns.some((run) => run.id === period.latest_run?.id)
                            ? period.latest_run
                            : period.latest_run

                        return (
                          <TableRow
                            key={period.id}
                            className={isSelected ? 'bg-primary/[0.06]' : undefined}
                            onClick={() => workspace.selectPeriod(period.id)}
                          >
                            <TableCell>
                              <div className="space-y-1">
                                <p className="font-medium text-foreground">{period.name}</p>
                                <p className="text-xs text-muted-foreground">
                                  {formatDate(period.start_date)} to {formatDate(period.end_date)}
                                </p>
                              </div>
                            </TableCell>
                            <TableCell>
                              <Badge variant={periodStatusBadgeVariant(period.status)}>
                                {formatPeriodStatus(period.status)}
                              </Badge>
                            </TableCell>
                            <TableCell>
                              {visibleRun ? (
                                <button
                                  type="button"
                                  className="flex flex-col items-start gap-1 text-left"
                                  onClick={(event) => {
                                    event.stopPropagation()
                                    workspace.selectRun(visibleRun.id)
                                  }}
                                >
                                  <span className="font-medium text-foreground">{visibleRun.name}</span>
                                  <Badge variant={runStatusBadgeVariant(visibleRun.status)}>
                                    {formatRunStatus(visibleRun.status)}
                                  </Badge>
                                </button>
                              ) : (
                                <span className="text-xs text-muted-foreground">No run created yet</span>
                              )}
                            </TableCell>
                            <TableCell>{formatDate(period.payroll_date)}</TableCell>
                          </TableRow>
                        )
                      })}
                    </TableBody>
                  </Table>
                </WorkspaceTableShell>

                {selectedRun ? (
                  <WorkspaceTableShell>
                    {selectedRunErrors.length ? (
                      <Table>
                        <TableHeader>
                          <TableRow>
                            <TableHead>Exception employee</TableHead>
                            <TableHead>Net salary</TableHead>
                            <TableHead>Issue</TableHead>
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          {selectedRunErrors.map((item) => (
                            <TableRow key={item.id}>
                              <TableCell>
                                <div className="space-y-1">
                                  <p className="font-medium text-foreground">{item.employee?.full_name}</p>
                                  <p className="text-xs text-muted-foreground">{item.employee?.employee_code}</p>
                                </div>
                              </TableCell>
                              <TableCell>{formatCurrency(item.net_salary)}</TableCell>
                              <TableCell>{item.validation_errors[0]}</TableCell>
                            </TableRow>
                          ))}
                        </TableBody>
                      </Table>
                    ) : selectedRun.items.length ? (
                      <Table>
                        <TableHeader>
                          <TableRow>
                            <TableHead>Employee</TableHead>
                            <TableHead>Gross</TableHead>
                            <TableHead>Deductions</TableHead>
                            <TableHead>Net</TableHead>
                            <TableHead>Status</TableHead>
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          {selectedRun.items.map((item) => (
                            <TableRow key={item.id}>
                              <TableCell>
                                <div className="space-y-1">
                                  <p className="font-medium text-foreground">{item.employee?.full_name}</p>
                                  <p className="text-xs text-muted-foreground">{item.employee?.employee_code}</p>
                                </div>
                              </TableCell>
                              <TableCell>{formatCurrency(item.gross_salary)}</TableCell>
                              <TableCell>{formatCurrency(item.total_deductions)}</TableCell>
                              <TableCell>{formatCurrency(item.net_salary)}</TableCell>
                              <TableCell>
                                <Badge variant={item.status === 'error' ? 'danger' : 'success'}>
                                  {item.status === 'error' ? 'Exception' : 'Calculated'}
                                </Badge>
                              </TableCell>
                            </TableRow>
                          ))}
                        </TableBody>
                      </Table>
                    ) : (
                      <WorkspaceEmptyState
                        title="Run details are not calculated yet"
                        copy="Calculate this run to inspect gross-to-net results, exception items, and payroll totals."
                      />
                    )}
                  </WorkspaceTableShell>
                ) : null}
              </div>

              <div className="space-y-3.5">
                <WorkspaceSurface>
                  <WorkspaceHeader compact>
                    <div className="min-w-0 space-y-1">
                      <CardTitle>{selectedPeriod?.name ?? 'Select a payroll period'}</CardTitle>
                      <CardDescription>
                        {selectedRun
                          ? `${selectedRun.name} · ${formatRunStatus(selectedRun.status)}`
                          : 'Choose a period or run to inspect actions, blockers, and release state.'}
                      </CardDescription>
                    </div>
                    {selectedRun ? (
                      <Badge variant={runStatusBadgeVariant(selectedRun.status)}>
                        {formatRunStatus(selectedRun.status)}
                      </Badge>
                    ) : selectedPeriod ? (
                      <Badge variant={periodStatusBadgeVariant(selectedPeriod.status)}>
                        {formatPeriodStatus(selectedPeriod.status)}
                      </Badge>
                    ) : null}
                  </WorkspaceHeader>
                  <WorkspaceContent>
                    {selectedPeriod ? (
                      <>
                        <WorkspaceSummaryRow label="Period window" value={`${formatDate(selectedPeriod.start_date)} to ${formatDate(selectedPeriod.end_date)}`} />
                        <WorkspaceSummaryRow label="Payroll date" value={formatDate(selectedPeriod.payroll_date)} />
                        <WorkspaceSummaryRow label="Period status" value={formatPeriodStatus(selectedPeriod.status)} />
                        <WorkspaceSummaryRow
                          label="Latest update"
                          value={formatRelativeTimestamp(selectedRun?.updated_at ?? selectedPeriod.updated_at)}
                        />
                        <WorkspaceSummaryRow
                          label="Employees staged"
                          value={String(selectedRun?.input_summary.employee_count ?? selectedRun?.calculation_summary.employee_count ?? 0)}
                        />
                        <WorkspaceSummaryRow
                          label="Exception items"
                          value={String(selectedRun?.calculation_summary.error_count ?? 0)}
                        />
                        <WorkspaceSummaryRow
                          label="Generated payslips"
                          value={String(selectedRunPayslips.length)}
                        />
                      </>
                    ) : (
                      <WorkspaceEmptyState
                        title="No payroll period selected"
                        copy="Pick a period from the table to unlock run actions and release context."
                      />
                    )}
                  </WorkspaceContent>
                </WorkspaceSurface>

                {selectedRun?.status === 'blocked' ? (
                  <WorkspaceSurface>
                    <WorkspaceHeader compact>
                      <div className="space-y-1">
                        <CardTitle>Blocking checks</CardTitle>
                        <CardDescription>Resolve these checks before payroll calculation can begin.</CardDescription>
                      </div>
                    </WorkspaceHeader>
                    <WorkspaceContent>
                      {selectedRun.prerequisite_snapshot.checks.map((check) => (
                        <WorkspaceSummaryRow
                          key={check.code}
                          label={
                            <span className="flex items-center gap-2">
                              <Badge
                                variant={
                                  check.status === 'blocked'
                                    ? 'danger'
                                    : check.status === 'warning'
                                      ? 'warning'
                                      : 'success'
                                }
                              >
                                {check.status}
                              </Badge>
                              {check.title}
                            </span>
                          }
                          value={check.detail}
                        />
                      ))}
                    </WorkspaceContent>
                  </WorkspaceSurface>
                ) : null}

                <PayrollAdjustmentsPanel key={selectedRun?.id ?? 'no-run'} selectedRun={selectedRun} />

                <WorkspaceSurface>
                  <WorkspaceHeader compact>
                    <div className="space-y-1">
                      <CardTitle>Run actions</CardTitle>
                      <CardDescription>Action buttons only unlock when the current payroll state and session permissions allow them.</CardDescription>
                    </div>
                  </WorkspaceHeader>
                  <WorkspaceContent className="space-y-3.5">
                    {canApproveRun ? (
                      <WorkspaceField label="Approval comment" compact>
                        <Textarea
                          value={approveComment}
                          onChange={(event) => setApproveComment(event.target.value)}
                          placeholder="Optional reviewer note for the payroll approval trail"
                        />
                      </WorkspaceField>
                    ) : null}

                    {canReopenRun ? (
                      <WorkspaceField label="Reopen reason" compact>
                        <Textarea
                          value={reopenReason}
                          onChange={(event) => setReopenReason(event.target.value)}
                          placeholder="Required before reopening a finalized payroll run"
                        />
                      </WorkspaceField>
                    ) : null}

                    <div className="flex flex-wrap gap-2">
                      <Button
                        size="sm"
                        onClick={() => void workspace.runAction('open-period', { periodId: selectedPeriod?.id })}
                        disabled={!canOpenPeriod || workspace.isSaving || !selectedPeriod}
                      >
                        <PlayCircle className="h-4 w-4" />
                        Open period
                      </Button>
                      <Button
                        size="sm"
                        onClick={() => void workspace.runAction('prepare-period', { periodId: selectedPeriod?.id })}
                        disabled={!canPreparePeriod || workspace.isSaving || !selectedPeriod}
                      >
                        <ShieldCheck className="h-4 w-4" />
                        Prepare payroll
                      </Button>
                      <Button
                        size="sm"
                        onClick={() => void workspace.runAction('calculate-run', { runId: selectedRun?.id })}
                        disabled={!canCalculateRun || workspace.isSaving || !selectedRun}
                      >
                        <PlayCircle className="h-4 w-4" />
                        Calculate payroll
                      </Button>
                      <Button
                        size="sm"
                        onClick={() => void workspace.runAction('approve-run', { runId: selectedRun?.id, comment: approveComment })}
                        disabled={!canApproveRun || workspace.isSaving || !selectedRun}
                      >
                        <CheckCircle2 className="h-4 w-4" />
                        Approve run
                      </Button>
                      <Button
                        size="sm"
                        onClick={() => void workspace.runAction('lock-run', { runId: selectedRun?.id })}
                        disabled={!canLockRun || workspace.isSaving || !selectedRun}
                      >
                        <Lock className="h-4 w-4" />
                        Lock run
                      </Button>
                      <Button
                        size="sm"
                        onClick={() => void workspace.runAction('generate-payslips', { runId: selectedRun?.id })}
                        disabled={!canGeneratePayslips || workspace.isSaving || !selectedRun}
                      >
                        <ShieldCheck className="h-4 w-4" />
                        Generate payslips
                      </Button>
                      <Button
                        size="sm"
                        variant="danger"
                        onClick={() => void workspace.runAction('reopen-run', { runId: selectedRun?.id, reason: reopenReason })}
                        disabled={!canReopenRun || workspace.isSaving || !selectedRun || reopenReason.trim().length === 0}
                      >
                        <RotateCcw className="h-4 w-4" />
                        Reopen run
                      </Button>
                      <Button
                        size="sm"
                        variant="secondary"
                        onClick={() => void workspace.runAction('close-period', { periodId: selectedPeriod?.id })}
                        disabled={!runReadyToClose || workspace.isSaving || !selectedPeriod}
                      >
                        <CheckCircle2 className="h-4 w-4" />
                        Close period
                      </Button>
                    </div>

                    {!selectedRun ? (
                      <p className="text-sm text-muted-foreground">Select a run to unlock payroll actions.</p>
                    ) : null}
                    {selectedRun?.status === 'failed' ? (
                      <p className="text-sm text-[color:var(--warning)]">
                        Review failed items before rerunning or reopening this payroll set.
                      </p>
                    ) : null}
                    {selectedRun?.status === 'ready' ? (
                      <p className="text-sm text-primary">Run ready for calculation. Inputs and prerequisites are currently green.</p>
                    ) : null}
                    {selectedRun?.status === 'locked' && !selectedRunPayslips.length ? (
                      <p className="text-sm text-muted-foreground">
                        Payslips have not been generated for this locked run yet.
                      </p>
                    ) : null}
                    {selectedRunPayslips.length ? (
                      <div className="rounded-xl border border-line/80 bg-panel-soft/70 p-3">
                        <p className="text-sm font-medium text-foreground">Published payslips</p>
                        <p className="mt-1 text-sm text-muted-foreground">
                          {selectedRunPayslips.length} payslip artifact(s) are attached to this run.
                        </p>
                      </div>
                    ) : null}
                  </WorkspaceContent>
                </WorkspaceSurface>
              </div>
            </WorkspaceSplit>
          )}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

type AdjustmentCategory = 'earning' | 'deduction' | 'reimbursement' | 'bonus' | 'custom'

interface AdjustmentFormState {
  employee_id: string
  adjustment_code: string
  name: string
  category: AdjustmentCategory
  amount: string
  effective_date: string
  status: 'active' | 'cancelled'
  notes: string
}

function PayrollAdjustmentsPanel({ selectedRun }: { selectedRun: PayrollRunRecord | null }) {
  const adjustmentsWorkspace = usePayrollRunAdjustments(selectedRun)
  const defaultEmployeeId = adjustmentsWorkspace.employeeOptions[0]?.id ?? null
  const [selectedAdjustmentId, setSelectedAdjustmentId] = useState<number | null>(null)
  const [form, setForm] = useState<AdjustmentFormState>(() => createDefaultAdjustmentForm(selectedRun, defaultEmployeeId))
  const selectedAdjustment =
    adjustmentsWorkspace.adjustments.find((adjustment) => adjustment.id === selectedAdjustmentId) ?? null
  const resolvedEmployeeId = form.employee_id || (defaultEmployeeId ? String(defaultEmployeeId) : '')
  const canEditAdjustments =
    adjustmentsWorkspace.canManageAdjustments && selectedRun?.status === 'ready'

  return (
    <WorkspaceSurface>
      <WorkspaceHeader compact>
        <div className="space-y-1">
          <CardTitle>Manual adjustments</CardTitle>
          <CardDescription>
            Add reimbursements, bonus corrections, or deduction offsets before calculation so the input snapshot stays auditable.
          </CardDescription>
        </div>
        <WorkspaceHeaderActions>
          {adjustmentsWorkspace.pendingActionLabel ? <Badge variant="info">{adjustmentsWorkspace.pendingActionLabel}</Badge> : null}
          {adjustmentsWorkspace.lastActionMessage ? <Badge variant="success">{adjustmentsWorkspace.lastActionMessage}</Badge> : null}
          {adjustmentsWorkspace.actionError ? <Badge variant="danger">{adjustmentsWorkspace.actionError}</Badge> : null}
        </WorkspaceHeaderActions>
      </WorkspaceHeader>
      <WorkspaceContent className="space-y-3.5">
        {!selectedRun ? (
          <WorkspaceEmptyState
            title="No run selected"
            copy="Select a payroll run to review or manage its manual adjustments."
          />
        ) : (
          <>
            <WorkspaceSummaryRow label="Selected run" value={selectedRun.name} />
            <WorkspaceSummaryRow
              label="Current status"
              value={formatRunStatus(selectedRun.status)}
            />
            <WorkspaceSummaryRow
              label="Adjustments in view"
              value={String(adjustmentsWorkspace.adjustments.length)}
            />
            <WorkspaceSummaryRow
              label="Edit state"
              value={
                canEditAdjustments
                  ? 'Ready for create and update'
                  : 'View only until the run is ready again'
              }
            />

            {adjustmentsWorkspace.adjustments.length ? (
              <WorkspaceTableShell>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Adjustment</TableHead>
                      <TableHead>Employee</TableHead>
                      <TableHead>Amount</TableHead>
                      <TableHead>Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {adjustmentsWorkspace.adjustments.map((adjustment) => (
                      <TableRow
                        key={adjustment.id}
                        className={adjustment.id === selectedAdjustmentId ? 'bg-primary/[0.06]' : undefined}
                        onClick={() => {
                          setSelectedAdjustmentId(adjustment.id)
                          setForm(mapAdjustmentToForm(adjustment))
                        }}
                      >
                        <TableCell>
                          <div className="space-y-1">
                            <p className="font-medium text-foreground">{adjustment.name}</p>
                            <p className="text-xs text-muted-foreground">{adjustment.adjustment_code}</p>
                          </div>
                        </TableCell>
                        <TableCell>{adjustment.employee?.full_name ?? `Employee ${adjustment.employee_id}`}</TableCell>
                        <TableCell>{formatCurrency(adjustment.amount)}</TableCell>
                        <TableCell>
                          <Badge variant={adjustment.status === 'active' ? 'success' : 'neutral'}>
                            {adjustment.status}
                          </Badge>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </WorkspaceTableShell>
            ) : (
              <WorkspaceEmptyState
                title="No adjustments recorded"
                copy="This run does not have any stored manual adjustments yet. Add one below if the run is ready."
              />
            )}

            <form className="space-y-3.5" onSubmit={(event) => void handleSubmit(event)}>
              <SelectField
                label="Employee"
                value={resolvedEmployeeId}
                options={[
                  { value: '', label: 'Select employee' },
                  ...adjustmentsWorkspace.employeeOptions.map((employee) => ({
                    value: String(employee.id),
                    label: `${employee.full_name} · ${employee.employee_code}`,
                  })),
                ]}
                onChange={(value) => setForm((current) => ({ ...current, employee_id: value }))}
                disabled={!canEditAdjustments || adjustmentsWorkspace.isSaving}
              />
              <div className="grid gap-3 sm:grid-cols-2">
                <WorkspaceField label="Adjustment code" compact>
                  <Input
                    value={form.adjustment_code}
                    onChange={(event) => setForm((current) => ({ ...current, adjustment_code: event.target.value.toUpperCase() }))}
                    placeholder="AUG_BONUS"
                    disabled={!canEditAdjustments || adjustmentsWorkspace.isSaving}
                  />
                </WorkspaceField>
                <WorkspaceField label="Adjustment name" compact>
                  <Input
                    value={form.name}
                    onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))}
                    placeholder="Retention bonus"
                    disabled={!canEditAdjustments || adjustmentsWorkspace.isSaving}
                  />
                </WorkspaceField>
                <SelectField
                  label="Category"
                  value={form.category}
                  options={[
                    { value: 'earning', label: 'Earning' },
                    { value: 'deduction', label: 'Deduction' },
                    { value: 'reimbursement', label: 'Reimbursement' },
                    { value: 'bonus', label: 'Bonus' },
                    { value: 'custom', label: 'Custom' },
                  ]}
                  onChange={(value) => setForm((current) => ({ ...current, category: value as AdjustmentCategory }))}
                  disabled={!canEditAdjustments || adjustmentsWorkspace.isSaving}
                />
                <WorkspaceField label="Amount" compact>
                  <Input
                    type="number"
                    step="0.01"
                    min="0.01"
                    value={form.amount}
                    onChange={(event) => setForm((current) => ({ ...current, amount: event.target.value }))}
                    placeholder="2500"
                    disabled={!canEditAdjustments || adjustmentsWorkspace.isSaving}
                  />
                </WorkspaceField>
                <WorkspaceField label="Effective date" compact>
                  <Input
                    type="date"
                    value={form.effective_date}
                    onChange={(event) => setForm((current) => ({ ...current, effective_date: event.target.value }))}
                    disabled={!canEditAdjustments || adjustmentsWorkspace.isSaving}
                  />
                </WorkspaceField>
                <SelectField
                  label="Status"
                  value={form.status}
                  options={[
                    { value: 'active', label: 'Active' },
                    { value: 'cancelled', label: 'Cancelled' },
                  ]}
                  onChange={(value) => setForm((current) => ({ ...current, status: value as AdjustmentFormState['status'] }))}
                  disabled={!canEditAdjustments || adjustmentsWorkspace.isSaving}
                />
              </div>
              <WorkspaceField label="Notes" compact>
                <Textarea
                  value={form.notes}
                  onChange={(event) => setForm((current) => ({ ...current, notes: event.target.value }))}
                  placeholder="Explain why this adjustment should land in the payroll input snapshot."
                  disabled={!canEditAdjustments || adjustmentsWorkspace.isSaving}
                />
              </WorkspaceField>
              <div className="flex flex-wrap gap-2">
                <Button
                  size="sm"
                  type="submit"
                  disabled={!canEditAdjustments || !canSubmitAdjustmentForm(form, resolvedEmployeeId) || adjustmentsWorkspace.isSaving}
                >
                  {selectedAdjustment ? 'Save adjustment' : 'Create adjustment'}
                </Button>
                <Button
                  size="sm"
                  variant="secondary"
                  onClick={() => {
                    setSelectedAdjustmentId(null)
                    setForm(createDefaultAdjustmentForm(selectedRun, defaultEmployeeId))
                    adjustmentsWorkspace.clearFeedback()
                  }}
                  disabled={adjustmentsWorkspace.isSaving}
                >
                  {selectedAdjustment ? 'Start new adjustment' : 'Reset form'}
                </Button>
              </div>
            </form>

            {!canEditAdjustments && selectedRun.status !== 'ready' ? (
              <p className="text-sm text-muted-foreground">
                Adjustments become editable when the run is in the ready state. Locked, approved, failed, and blocked runs stay view-only here.
              </p>
            ) : null}
          </>
        )}
      </WorkspaceContent>
    </WorkspaceSurface>
  )

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault()

    if (!selectedRun || !canSubmitAdjustmentForm(form, resolvedEmployeeId)) {
      return
    }

    await adjustmentsWorkspace.saveAdjustment(selectedAdjustmentId, {
      employee_id: Number(resolvedEmployeeId),
      adjustment_code: form.adjustment_code.trim().toUpperCase(),
      name: form.name.trim(),
      category: form.category,
      amount: form.amount.trim(),
      effective_date: form.effective_date,
      status: form.status,
      notes: normalizeNullableText(form.notes),
    })

    setSelectedAdjustmentId(null)
    setForm(createDefaultAdjustmentForm(selectedRun, defaultEmployeeId))
  }
}

function createDefaultAdjustmentForm(
  selectedRun: PayrollRunRecord | null,
  defaultEmployeeId: number | null,
): AdjustmentFormState {
  return {
    employee_id: defaultEmployeeId ? String(defaultEmployeeId) : '',
    adjustment_code: '',
    name: '',
    category: 'bonus',
    amount: '',
    effective_date: selectedRun?.end_date ?? '',
    status: 'active',
    notes: '',
  }
}

function mapAdjustmentToForm(adjustment: PayrollAdjustmentRecord): AdjustmentFormState {
  return {
    employee_id: String(adjustment.employee_id),
    adjustment_code: adjustment.adjustment_code,
    name: adjustment.name,
    category: adjustment.category,
    amount: adjustment.amount,
    effective_date: adjustment.effective_date,
    status: adjustment.status,
    notes: adjustment.notes ?? '',
  }
}

function canSubmitAdjustmentForm(form: AdjustmentFormState, resolvedEmployeeId: string) {
  return (
    resolvedEmployeeId !== '' &&
    form.adjustment_code.trim().length > 0 &&
    form.name.trim().length > 0 &&
    form.amount.trim().length > 0 &&
    form.effective_date !== ''
  )
}

function normalizeNullableText(value: string) {
  const text = value.trim()
  return text ? text : null
}
