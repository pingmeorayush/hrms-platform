import { useMemo, useState } from 'react'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import { formatCurrency, formatDate } from '../utils'
import { usePayrollRouteWorkspace } from './usePayrollRouteWorkspace'

type PayrollSelfServiceTab = 'payslips' | 'compensation'

export function PayrollSelfServicePage() {
  const workspace = usePayrollRouteWorkspace()
  const [activeTab, setActiveTab] = useState<PayrollSelfServiceTab>('payslips')
  const [downloadingPayslipId, setDownloadingPayslipId] = useState<number | null>(null)

  const availableTabs = useMemo(() => {
    const tabs: Array<{ id: PayrollSelfServiceTab; label: string }> = []

    if (workspace.canViewPayslips) {
      tabs.push({ id: 'payslips', label: 'Payslips' })
    }

    if (workspace.canViewCompensation || workspace.canAccessPayrollSelfService) {
      tabs.push({ id: 'compensation', label: 'Compensation' })
    }

    return tabs
  }, [workspace.canAccessPayrollSelfService, workspace.canViewCompensation, workspace.canViewPayslips])

  const resolvedActiveTab = availableTabs.some((tab) => tab.id === activeTab)
    ? activeTab
    : (availableTabs[0]?.id ?? 'payslips')

  const latestPayslip = workspace.latestCurrentEmployeePayslip
  const currentAssignment = workspace.currentEmployeeCompensation?.current_assignment ?? null
  const compensationHistory = workspace.currentEmployeeCompensation?.history ?? []
  const compensationAccessLabel = workspace.compensationUnlocked
    ? 'Visible'
    : workspace.canViewCompensation
      ? 'Pending payroll release'
      : 'Hidden'

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader compact>
          <div className="min-w-0 space-y-1">
            <CardTitle>My pay</CardTitle>
            <CardDescription className="max-w-3xl">
              Access finalized payslips, review release history, and check whether compensation detail is available for this profile.
            </CardDescription>
          </div>
          <WorkspaceHeaderActions>
            <Badge variant={workspace.source === 'demo' ? 'info' : 'neutral'}>
              {workspace.source === 'demo' ? 'Demo payroll profile' : 'Live payroll profile'}
            </Badge>
            {latestPayslip ? (
              <Button
                size="xs"
                onClick={() => void handlePayslipDownload(latestPayslip.id)}
                disabled={downloadingPayslipId === latestPayslip.id}
              >
                {downloadingPayslipId === latestPayslip.id ? 'Downloading...' : 'Download latest payslip'}
              </Button>
            ) : null}
          </WorkspaceHeaderActions>
        </WorkspaceHeader>

        <WorkspaceContent className="space-y-3.5">
          <div className="organization-metric-grid">
            <MetricCard
              label="Current profile"
              value={workspace.currentEmployee?.full_name ?? 'Payroll profile pending'}
              caption={workspace.currentEmployee?.employee_code ?? 'No linked payroll employee resolved'}
            />
            <MetricCard
              label="Latest finalized payslip"
              value={latestPayslip ? formatDate(latestPayslip.payroll_date) : 'Pending'}
              caption={latestPayslip ? latestPayslip.slip_number : 'No finalized payroll artifacts are available yet'}
            />
            <MetricCard
              label="Net pay"
              value={latestPayslip ? formatCurrency(latestPayslip.net_salary) : 'Pending'}
              caption={latestPayslip ? `${formatCurrency(latestPayslip.total_deductions)} total deductions` : 'Visible after payroll is locked and published'}
            />
            <MetricCard
              label="Compensation visibility"
              value={compensationAccessLabel}
              caption={
                workspace.compensationUnlocked
                  ? 'The current assignment and history are available below.'
                  : workspace.canViewCompensation
                    ? 'Compensation unlocks after a finalized payroll release is available.'
                    : 'Sensitive compensation fields are hidden in this session.'
              }
            />
          </div>

          <div className="grid gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(20rem,0.9fr)]">
            <WorkspaceSurface>
              <WorkspaceContent>
                <WorkspaceSummaryRow
                  label="Resolved employee"
                  value={
                    workspace.currentEmployee
                      ? `${workspace.currentEmployee.full_name} · ${workspace.currentEmployee.employee_code}`
                      : 'No payroll employee resolved'
                  }
                />
                <WorkspaceSummaryRow
                  label="Published payslips"
                  value={String(workspace.currentEmployeePayslips.length)}
                />
                <WorkspaceSummaryRow
                  label="Compensation access"
                  value={compensationAccessLabel}
                />
                <WorkspaceSummaryRow
                  label="Current salary structure"
                  value={
                    currentAssignment
                      ? `${currentAssignment.salary_structure.code} v${currentAssignment.salary_structure.version}`
                      : 'No visible assignment'
                  }
                />
              </WorkspaceContent>
            </WorkspaceSurface>

            <WorkspaceSurface>
              <WorkspaceContent>
                {latestPayslip ? (
                  <>
                    <WorkspaceSummaryRow label="Payslip period" value={`${formatDate(latestPayslip.start_date)} to ${formatDate(latestPayslip.end_date)}`} />
                    <WorkspaceSummaryRow label="Gross pay" value={formatCurrency(latestPayslip.gross_salary)} />
                    <WorkspaceSummaryRow label="Total earnings" value={formatCurrency(latestPayslip.total_earnings)} />
                    <WorkspaceSummaryRow label="Employer cost" value={formatCurrency(latestPayslip.employer_cost)} />
                  </>
                ) : (
                  <WorkspaceEmptyState
                    title="No finalized payslips yet"
                    copy="This profile does not have a locked payroll artifact yet, so downloadable payslips are not available."
                  />
                )}
              </WorkspaceContent>
            </WorkspaceSurface>
          </div>

          <WorkspaceTabs aria-label="Payroll self-service sections">
            {availableTabs.map((tab) => (
              <WorkspaceTabButton
                key={tab.id}
                active={resolvedActiveTab === tab.id}
                aria-selected={resolvedActiveTab === tab.id}
                role="tab"
                onClick={() => setActiveTab(tab.id)}
              >
                {tab.label}
              </WorkspaceTabButton>
            ))}
          </WorkspaceTabs>

          {resolvedActiveTab === 'payslips' ? (
            workspace.currentEmployeePayslips.length ? (
              <WorkspaceTableShell>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Payslip</TableHead>
                      <TableHead>Payroll window</TableHead>
                      <TableHead>Net pay</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {workspace.currentEmployeePayslips.map((payslip) => (
                      <TableRow key={payslip.id}>
                        <TableCell>
                          <div className="space-y-1">
                            <p className="font-medium text-foreground">{payslip.slip_number}</p>
                            <p className="text-xs text-muted-foreground">{payslip.file_name}</p>
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="space-y-1">
                            <p className="text-sm text-foreground">
                              {formatDate(payslip.start_date)} to {formatDate(payslip.end_date)}
                            </p>
                            <p className="text-xs text-muted-foreground">
                              Released {formatDate(payslip.generated_at)}
                            </p>
                          </div>
                        </TableCell>
                        <TableCell>{formatCurrency(payslip.net_salary)}</TableCell>
                        <TableCell>
                          <Badge variant="success">Finalized</Badge>
                        </TableCell>
                        <TableCell>
                          <Button
                            size="sm"
                            variant="secondary"
                            onClick={() => void handlePayslipDownload(payslip.id)}
                            disabled={downloadingPayslipId === payslip.id}
                          >
                            {downloadingPayslipId === payslip.id ? 'Downloading...' : 'Download'}
                          </Button>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </WorkspaceTableShell>
            ) : (
              <WorkspaceEmptyState
                title="No finalized payslips yet"
                copy="Payslips appear here once payroll is locked, published, and released for this employee."
              />
            )
          ) : workspace.compensationUnlocked && currentAssignment ? (
            <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(20rem,0.9fr)]">
              <WorkspaceSurface>
                <WorkspaceContent className="space-y-3.5">
                  <div className="organization-metric-grid">
                    <MetricCard
                      label="Annual CTC"
                      value={formatCurrency(currentAssignment.annual_ctc_amount)}
                      caption={`Effective ${formatDate(currentAssignment.effective_from)}`}
                    />
                    <MetricCard
                      label="Gross monthly"
                      value={formatCurrency(currentAssignment.gross_salary_amount)}
                      caption={currentAssignment.salary_structure.name ?? currentAssignment.salary_structure.code}
                    />
                    <MetricCard
                      label="Net monthly"
                      value={formatCurrency(currentAssignment.net_salary_amount)}
                      caption={currentAssignment.salary_structure.pay_frequency}
                    />
                  </div>

                  <WorkspaceTableShell>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Component</TableHead>
                          <TableHead>Category</TableHead>
                          <TableHead>Formula input</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {currentAssignment.component_snapshot.map((component) => (
                          <TableRow key={`${component.salary_component_id ?? component.code ?? component.display_order}`}>
                            <TableCell>
                              <div className="space-y-1">
                                <p className="font-medium text-foreground">{component.name ?? component.code ?? 'Compensation component'}</p>
                                <p className="text-xs text-muted-foreground">{component.code ?? 'Component code pending'}</p>
                              </div>
                            </TableCell>
                            <TableCell className="capitalize">{component.category ?? 'Unclassified'}</TableCell>
                            <TableCell>{describeFormula(component)}</TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </WorkspaceTableShell>
                </WorkspaceContent>
              </WorkspaceSurface>

              <WorkspaceSurface>
                <WorkspaceContent>
                  <WorkspaceSummaryRow label="Current structure" value={`${currentAssignment.salary_structure.code} v${currentAssignment.salary_structure.version}`} />
                  <WorkspaceSummaryRow label="Revision reason" value={currentAssignment.revision_reason} />
                  <WorkspaceSummaryRow label="Revision date" value={formatDate(currentAssignment.revision_date)} />
                  <WorkspaceSummaryRow label="History entries" value={String(compensationHistory.length)} />
                  <WorkspaceSummaryRow label="Notes" value={currentAssignment.notes ?? 'No notes recorded'} />
                </WorkspaceContent>
              </WorkspaceSurface>
            </div>
          ) : (
            <WorkspaceEmptyState
              title={
                workspace.canViewCompensation
                  ? 'Compensation unlocks after finalized payroll'
                  : 'Sensitive compensation fields are hidden'
              }
              copy={
                workspace.canViewCompensation
                  ? 'This profile can view compensation, but the amounts stay hidden until a finalized payroll release exists for the employee.'
                  : 'This session does not include compensation visibility. Payroll administrators can still release finalized payslips without exposing sensitive compensation figures here.'
              }
            />
          )}
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )

  async function handlePayslipDownload(payslipId: number) {
    const record = workspace.currentEmployeePayslips.find((item) => item.id === payslipId)
    if (!record) {
      return
    }

    setDownloadingPayslipId(payslipId)

    try {
      await workspace.downloadPayslip(record)
    } finally {
      setDownloadingPayslipId((current) => (current === payslipId ? null : current))
    }
  }
}

function MetricCard({
  label,
  value,
  caption,
}: {
  label: string
  value: string
  caption: string
}) {
  return (
    <div className="rounded-xl border border-line/80 bg-panel-soft/70 px-4 py-3">
      <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">{label}</p>
      <p className="mt-2 text-2xl font-semibold tracking-tight text-foreground">{value}</p>
      <p className="mt-1 text-sm text-muted-foreground">{caption}</p>
    </div>
  )
}

function describeFormula(component: NonNullable<ReturnType<typeof usePayrollRouteWorkspace>['currentEmployeeCompensation']>['history'][number]['component_snapshot'][number]) {
  const inputs = component.resolved_formula_inputs

  if (inputs.calculation_type === 'flat_amount' && inputs.flat_amount !== null) {
    return formatCurrency(inputs.flat_amount)
  }

  if (inputs.calculation_type === 'percentage' && inputs.percentage_value !== null) {
    const basis = inputs.percentage_basis_component_codes.length
      ? ` of ${inputs.percentage_basis_component_codes.join(', ')}`
      : ''

    return `${inputs.percentage_value}%${basis}`
  }

  if (inputs.expression_formula) {
    return inputs.expression_formula
  }

  return 'Formula detail pending'
}
