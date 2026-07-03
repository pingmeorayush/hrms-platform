import { useMemo, useState, type ReactNode } from 'react'
import { Link } from 'react-router-dom'
import {
  AlertTriangle,
  ArrowUpRight,
  BadgeCheck,
  Clock3,
  FileWarning,
  ShieldCheck,
  UserCog,
  UserPlus,
  Users,
} from 'lucide-react'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import { useShellFavorites } from '../../../app/shell/favorites'
import { getModuleRecentActivity, useShellRecent } from '../../../app/shell/recent'
import {
  formatRegionalDate,
  formatRegionalRelativeTimestamp,
} from '../../../shared/regionalization/formatters'
import { buildDemoEmployeeWorkspace } from '../data/demoEmployeeProfiles'
import { useEmployeeDirectory, employeeStatusOptions } from '../hooks/useEmployeeDirectory'
import type { EmployeeDirectoryFilters, EmployeeRecord, EmployeeStatus } from '../types'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardTitle } from '../../../shared/ui/card'
import {
  CommandCenterActivityItem,
  CommandCenterActivityList,
  CommandCenterAttentionItem,
  CommandCenterAttentionStrip,
  CommandCenterInsightCard,
  CommandCenterInsightGrid,
  CommandCenterLayout,
  CommandCenterMain,
  CommandCenterMetricCard,
  CommandCenterMetricGrid,
  CommandCenterPanel,
  CommandCenterRail,
} from '../../../shared/ui/command-center'
import { ConsoleSearchField, ConsoleToolbar, ConsoleToolbarRow } from '../../../shared/ui/console-table'
import { SelectField as AppSelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceFilters,
  WorkspaceHeader,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'

type EmployeeOverviewTab = 'directory' | 'lifecycle' | 'onboarding' | 'documents' | 'audit'

interface EmployeeModuleInsight {
  onboardingProgress: number | null
  onboardingIncompleteCount: number | null
  documentCount: number | null
  expiringDocumentCount: number
  auditCount: number | null
  latestAuditTimestamp: string | null
}

type MetricCardTone = 'neutral' | 'info' | 'success' | 'warning' | 'danger'

const baseInitialFilters: EmployeeDirectoryFilters = {
  search: '',
  employmentStatus: '',
  departmentId: '',
  designationId: '',
  managerId: '',
  page: 1,
  perPage: 50,
}
const emptyPermissions: string[] = []
const emptyEmployeeRecords: EmployeeRecord[] = []

export function EmployeesOverviewPage() {
  const { snapshot } = useAccessSnapshot()
  const { isFavorite, toggleFavorite } = useShellFavorites()
  const { recentItems } = useShellRecent()
  const permissions = snapshot?.user.permissions ?? emptyPermissions
  const canViewAudit = permissions.includes('audit.view')
  const [filters, setFilters] = useState<EmployeeDirectoryFilters>(baseInitialFilters)
  const [activeTabSelection, setActiveTab] = useState<EmployeeOverviewTab>('directory')
  const { directory, departments, managers, isLoading, error, source } = useEmployeeDirectory(filters)

  const availableTabs = useMemo(
    () =>
      [
        { id: 'directory', label: 'Directory' },
        { id: 'lifecycle', label: 'Lifecycle watch' },
        { id: 'onboarding', label: 'Onboarding' },
        { id: 'documents', label: 'Documents' },
        ...(canViewAudit ? [{ id: 'audit', label: 'Audit' }] : []),
      ] as Array<{ id: EmployeeOverviewTab; label: string }>,
    [canViewAudit],
  )

  const activeTab = availableTabs.some((tab) => tab.id === activeTabSelection)
    ? activeTabSelection
    : (availableTabs[0]?.id ?? 'directory')
  const employees = useMemo(() => directory?.items ?? emptyEmployeeRecords, [directory?.items])
  const filteredEmployees = useMemo(
    () => employees.filter((employee) => matchesEmployeeOverviewTab(employee, activeTab)),
    [activeTab, employees],
  )
  const employeeInsights = useMemo(() => {
    return new Map<number, EmployeeModuleInsight>(
      filteredEmployees.map((employee) => [employee.id, getEmployeeModuleInsight(employee, source, snapshot)]),
    )
  }, [filteredEmployees, snapshot, source])

  const totalEmployees = employees.length
  const activeEmployees = employees.filter((employee) => employee.employment_status === 'active').length
  const probationEmployees = employees.filter((employee) => employee.employment_status === 'probation').length
  const noticeEmployees = employees.filter((employee) => employee.employment_status === 'notice_period').length
  const terminatedEmployees = employees.filter((employee) => employee.employment_status === 'terminated').length
  const managerGaps = employees.filter((employee) => employee.manager == null).length
  const onboardingRisk = filteredEmployees.filter((employee) => {
    const insight = employeeInsights.get(employee.id)
    return (insight?.onboardingIncompleteCount ?? 0) > 0
  }).length
  const expiringDocuments = filteredEmployees.reduce(
    (count, employee) => count + (employeeInsights.get(employee.id)?.expiringDocumentCount ?? 0),
    0,
  )

  const metricCards: Array<{
    label: string
    value: string
    delta: string
    icon: ReactNode
    tone: MetricCardTone
    valueSize?: 'stat' | 'compact' | 'long'
  }> = [
    {
      label: 'Visible employees',
      value: String(totalEmployees),
      delta: `${activeEmployees} active in this filtered roster`,
      icon: <Users className="h-4 w-4" />,
      tone: 'info' as const,
    },
    {
      label: 'Probation watch',
      value: String(probationEmployees),
      delta: `${noticeEmployees} notice period · ${terminatedEmployees} terminated`,
      icon: <Clock3 className="h-4 w-4" />,
      tone: probationEmployees || noticeEmployees ? 'warning' : 'success',
    },
    {
      label: 'Manager gaps',
      value: String(managerGaps),
      delta: managerGaps ? 'Reporting lines need attention' : 'All employees have a manager',
      icon: <UserCog className="h-4 w-4" />,
      tone: managerGaps ? 'warning' : 'success',
    },
    {
      label: 'Onboarding risk',
      value: String(onboardingRisk),
      delta:
        source === 'demo'
          ? 'Employees with incomplete onboarding tasks'
          : 'Track onboarding detail in employee workspaces',
      icon: <UserPlus className="h-4 w-4" />,
      tone: onboardingRisk ? 'warning' : 'success',
    },
    {
      label: 'Expiring documents',
      value: String(expiringDocuments),
      delta:
        source === 'demo'
          ? 'Protected files expiring in the next 30 days'
          : 'Document expiry detail loads in employee workspaces',
      icon: <FileWarning className="h-4 w-4" />,
      tone: expiringDocuments ? 'warning' : 'neutral',
    },
    {
      label: 'Audit visibility',
      value: canViewAudit ? 'Enabled' : 'Limited',
      delta: canViewAudit ? 'Protected history is available in this session' : 'Audit route is hidden in this session',
      icon: <ShieldCheck className="h-4 w-4" />,
      tone: canViewAudit ? 'success' : 'neutral',
      valueSize: 'compact',
    },
  ]

  const attentionItems = useMemo(() => {
    const items: Array<{
      id: string
      path?: string
      title: string
      detail: string
      meta: string
      tone: 'warning' | 'danger' | 'success' | 'info'
      icon: ReactNode
    }> = []

    const unmanaged = employees.find((employee) => employee.manager == null)
    if (unmanaged) {
      items.push({
        id: 'manager-gap',
        path: `/employees/${unmanaged.id}/profile`,
        title: `${managerGaps} employee(s) missing a manager`,
        detail: `${unmanaged.full_name} is visible without a reporting line`,
        meta: 'Use the profile workspace to assign reporting ownership.',
        tone: 'warning',
        icon: <UserCog className="h-4 w-4" />,
      })
    }

    const probationEmployee = employees.find((employee) => employee.employment_status === 'probation')
    if (probationEmployee) {
      items.push({
        id: 'probation',
        path: '/employees/lifecycle-watch',
        title: `${probationEmployees} probation record(s) need monitoring`,
        detail: `${probationEmployee.full_name} joined ${formatDate(probationEmployee.date_of_joining)}`,
        meta: 'Review lifecycle watch for next-step planning.',
        tone: 'warning',
        icon: <Clock3 className="h-4 w-4" />,
      })
    }

    const noticeEmployee = employees.find((employee) => employee.employment_status === 'notice_period')
    if (noticeEmployee) {
      items.push({
        id: 'notice',
        path: '/employees/lifecycle-watch',
        title: `${noticeEmployees} employee(s) are in notice period`,
        detail: `${noticeEmployee.full_name} is approaching a controlled exit flow`,
        meta: 'Open lifecycle watch to coordinate transitions and documents.',
        tone: 'danger',
        icon: <AlertTriangle className="h-4 w-4" />,
      })
    }

    const documentEmployee = filteredEmployees.find(
      (employee) => (employeeInsights.get(employee.id)?.expiringDocumentCount ?? 0) > 0,
    )
    if (documentEmployee) {
      const insight = employeeInsights.get(documentEmployee.id)
      items.push({
        id: 'document-expiry',
        path: '/employees/documents',
        title: `${expiringDocuments} expiring document record(s) detected`,
        detail: `${documentEmployee.full_name} has ${insight?.expiringDocumentCount ?? 0} file(s) expiring soon`,
        meta: 'Open the document registry to review protected file coverage.',
        tone: 'warning',
        icon: <FileWarning className="h-4 w-4" />,
      })
    }

    const onboardingEmployee = filteredEmployees.find(
      (employee) => (employeeInsights.get(employee.id)?.onboardingIncompleteCount ?? 0) > 0,
    )
    if (onboardingEmployee) {
      const insight = employeeInsights.get(onboardingEmployee.id)
      items.push({
        id: 'onboarding',
        path: '/employees/onboarding',
        title: `${onboardingRisk} employee(s) still have onboarding work open`,
        detail: `${onboardingEmployee.full_name} has ${insight?.onboardingIncompleteCount ?? 0} task(s) outstanding`,
        meta: 'Use onboarding queues to close setup gaps before the next payroll cycle.',
        tone: 'info',
        icon: <BadgeCheck className="h-4 w-4" />,
      })
    }

    if (!items.length) {
      items.push({
        id: 'healthy',
        path: '/employees/overview',
        title: 'Workforce operations look healthy',
        detail: 'No urgent manager gaps, notice-period issues, or expiring document alerts were detected.',
        meta: 'Use the employee collections below for planned roster work.',
        tone: 'success',
        icon: <ShieldCheck className="h-4 w-4" />,
      })
    }

    return items.slice(0, 4)
  }, [employeeInsights, employees, expiringDocuments, filteredEmployees, managerGaps, noticeEmployees, onboardingRisk, probationEmployees])

  const fallbackActivityItems = useMemo(() => {
    const items: Array<{
      id: string
      path?: string
      title: string
      detail: string
      meta: string
      timestamp: string | null
      tone: 'neutral' | 'info' | 'success' | 'warning'
    }> = []

    employees.forEach((employee) => {
      items.push({
        id: `employee-${employee.id}`,
        title:
          employee.employment_status === 'terminated'
            ? `${employee.full_name} moved out of active workforce`
            : `${employee.full_name} remains in the visible roster`,
        detail: `${employee.department.name} · ${employee.designation.name}`,
        meta: relativeTime(employee.updated_at ?? employee.created_at),
        timestamp: employee.updated_at ?? employee.created_at,
        tone: employee.employment_status === 'terminated' ? 'warning' : 'neutral',
      })

      const insight = employeeInsights.get(employee.id)
      if (insight?.latestAuditTimestamp) {
        items.push({
          id: `audit-${employee.id}`,
          title: `${employee.full_name} has recent protected activity`,
          detail: `${insight.auditCount ?? 0} audit event(s) recorded`,
          meta: relativeTime(insight.latestAuditTimestamp),
          timestamp: insight.latestAuditTimestamp,
          tone: 'info',
        })
      }
    })

    return items
      .filter((item) => item.timestamp)
      .sort((left, right) => (right.timestamp ?? '').localeCompare(left.timestamp ?? ''))
      .slice(0, 6)
  }, [employeeInsights, employees])

  const activityItems = useMemo(() => {
    const recentActivity = getModuleRecentActivity('employees', recentItems)
    return recentActivity.length ? recentActivity : fallbackActivityItems
  }, [fallbackActivityItems, recentItems])

  const collectionCount = filteredEmployees.length
  const filtersDirty = JSON.stringify(filters) !== JSON.stringify(baseInitialFilters)

  return (
    <WorkspacePage>
      {isLoading ? <p className="workspace-muted">Loading employee operations center...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}

      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Employees"
          title="Employees Operations Center"
          description="Monitor workforce health, onboarding risk, document posture, and move directly into employee workspaces."
          context={['Lifecycle watch ready', 'Directory command surface']}
          actions={
            <>
              <Button asChild size="xs" variant="secondary">
                <Link to="/employees/lifecycle-watch">Open lifecycle watch</Link>
              </Button>
              <Button asChild size="xs" variant="primary">
                <Link to="/employees/directory">Open directory</Link>
              </Button>
            </>
          }
        />
        <WorkspaceContent className="space-y-4">
          <CommandCenterMetricGrid>
            {metricCards.map((card) => (
              <CommandCenterMetricCard
                key={card.label}
                label={card.label}
                value={card.value}
                delta={card.delta}
                icon={card.icon}
                tone={card.tone}
                valueSize={card.valueSize}
              />
            ))}
          </CommandCenterMetricGrid>

          <CommandCenterLayout>
            <CommandCenterMain>
              <CommandCenterAttentionStrip
                title="Needs attention"
                action={
                  <Button asChild size="xs" variant="ghost">
                    <Link to="/employees/lifecycle-watch">View queues</Link>
                  </Button>
                }
              >
                {attentionItems.map((item) => (
                  <CommandCenterAttentionItem
                    key={item.id}
                    title={item.title}
                    detail={item.detail}
                    meta={item.meta}
                    icon={item.icon}
                    tone={item.tone}
                    to={item.path}
                    pinned={item.path ? isFavorite(item.path) : false}
                    onTogglePinned={
                      item.path
                        ? () =>
                            toggleFavorite({
                              path: item.path!,
                              label: item.title,
                              icon: 'employees',
                              description: item.detail,
                              meta: item.meta,
                            })
                        : undefined
                    }
                    pinLabel={item.path ? `${isFavorite(item.path) ? 'Unpin' : 'Pin'} ${item.title}` : undefined}
                  />
                ))}
              </CommandCenterAttentionStrip>

              <WorkspaceSurface>
                <WorkspaceHeader compact>
                  <div>
                    <CardTitle>Workforce workspace</CardTitle>
                  </div>
                  <Badge variant="subtle">{collectionCount} employee record(s) in view</Badge>
                </WorkspaceHeader>
                <WorkspaceContent className="space-y-4">
                  <ConsoleToolbar>
                    <ConsoleToolbarRow>
                      <WorkspaceTabs role="tablist" aria-label="Employees operations collections">
                        {availableTabs.map((tab) => (
                          <WorkspaceTabButton
                            key={tab.id}
                            type="button"
                            role="tab"
                            active={activeTab === tab.id}
                            aria-selected={activeTab === tab.id}
                            onClick={() => setActiveTab(tab.id)}
                          >
                            {tab.label}
                          </WorkspaceTabButton>
                        ))}
                      </WorkspaceTabs>
                      <div className="flex flex-wrap items-center gap-2">
                        <Badge variant="subtle">
                          {activeTab === 'audit' ? 'Protected history' : 'Employee operations'}
                        </Badge>
                        <Button size="xs" variant="secondary" onClick={() => setFilters(baseInitialFilters)} disabled={!filtersDirty}>
                          Reset filters
                        </Button>
                      </div>
                    </ConsoleToolbarRow>
                    <ConsoleToolbarRow>
                      <div className="flex min-w-0 flex-1 flex-col gap-3">
                        <ConsoleSearchField
                          value={filters.search}
                          onChange={(event) =>
                            setFilters((current) => ({
                              ...current,
                              page: 1,
                              search: event.target.value,
                            }))
                          }
                          placeholder="Search employees, codes, emails, or managers"
                          aria-label="Search employees"
                          className="max-w-2xl"
                        />
                      </div>
                    </ConsoleToolbarRow>
                    <ConsoleToolbarRow className="items-end">
                      <WorkspaceFilters>
                        <div className="min-w-[11rem] flex-1 xl:max-w-[12rem]">
                          <AppSelectField
                            label="Status"
                            value={filters.employmentStatus}
                            onChange={(value) =>
                              setFilters((current) => ({
                                ...current,
                                page: 1,
                                employmentStatus: value as EmployeeDirectoryFilters['employmentStatus'],
                              }))
                            }
                            compact
                            options={employeeStatusOptions.map((option) => ({
                              value: option.value,
                              label: option.label,
                            }))}
                          />
                        </div>
                        <div className="min-w-[12rem] flex-1 xl:max-w-[14rem]">
                          <AppSelectField
                            label="Department"
                            value={filters.departmentId}
                            onChange={(value) =>
                              setFilters((current) => ({
                                ...current,
                                page: 1,
                                departmentId: value,
                              }))
                            }
                            compact
                            options={[
                              { value: '', label: 'All departments' },
                              ...departments.map((department) => ({
                                value: String(department.id),
                                label: department.name,
                              })),
                            ]}
                          />
                        </div>
                        <div className="min-w-[12rem] flex-1 xl:max-w-[14rem]">
                          <AppSelectField
                            label="Manager"
                            value={filters.managerId}
                            onChange={(value) =>
                              setFilters((current) => ({
                                ...current,
                                page: 1,
                                managerId: value,
                              }))
                            }
                            compact
                            options={[
                              { value: '', label: 'All managers' },
                              ...managers.map((manager) => ({
                                value: String(manager.id),
                                label: manager.full_name,
                              })),
                            ]}
                          />
                        </div>
                      </WorkspaceFilters>
                    </ConsoleToolbarRow>
                  </ConsoleToolbar>

                  {!isLoading && !error && !filteredEmployees.length ? (
                    <WorkspaceEmptyState
                      title="No employees match the current view"
                      copy="Adjust the search or filters to widen the workforce view."
                    />
                  ) : null}

                  {filteredEmployees.length ? (
                    <WorkspaceTableShell>
                      {activeTab === 'directory' ? renderDirectoryTable(filteredEmployees) : null}
                      {activeTab === 'lifecycle' ? renderLifecycleTable(filteredEmployees) : null}
                      {activeTab === 'onboarding' ? renderOnboardingTable(filteredEmployees, employeeInsights) : null}
                      {activeTab === 'documents' ? renderDocumentsTable(filteredEmployees, employeeInsights) : null}
                      {activeTab === 'audit' ? renderAuditTable(filteredEmployees, employeeInsights) : null}
                    </WorkspaceTableShell>
                  ) : null}
                </WorkspaceContent>
              </WorkspaceSurface>

              <CommandCenterInsightGrid>
                <CommandCenterInsightCard
                  title="Employment status mix"
                  description="Keep workforce posture visible across active, probation, notice-period, and terminated states."
                >
                  <WorkspaceSummaryRow label="Active" value={activeEmployees} />
                  <WorkspaceSummaryRow label="Probation" value={probationEmployees} />
                  <WorkspaceSummaryRow label="Notice period" value={noticeEmployees} />
                  <WorkspaceSummaryRow label="Terminated" value={terminatedEmployees} />
                </CommandCenterInsightCard>
                <CommandCenterInsightCard
                  title="Ownership and onboarding"
                  description="Focus on reporting-line gaps and the onboarding tasks still blocking operational readiness."
                >
                  <WorkspaceSummaryRow label="Without manager" value={managerGaps} />
                  <WorkspaceSummaryRow label="Onboarding at risk" value={onboardingRisk} />
                  <WorkspaceSummaryRow label="Visible departments" value={new Set(filteredEmployees.map((employee) => employee.department.id)).size} />
                  <WorkspaceSummaryRow
                    label="Visible managers"
                    value={new Set(filteredEmployees.map((employee) => employee.manager?.id).filter(Boolean)).size}
                  />
                </CommandCenterInsightCard>
                <CommandCenterInsightCard
                  title="Protected document and audit posture"
                  description="Track document expiry pressure and whether the current session can inspect protected history."
                >
                  <WorkspaceSummaryRow label="Expiring docs" value={expiringDocuments} />
                  <WorkspaceSummaryRow
                    label="Employees with document detail"
                    value={filteredEmployees.filter((employee) => (employeeInsights.get(employee.id)?.documentCount ?? 0) > 0).length}
                  />
                  <WorkspaceSummaryRow label="Audit enabled" value={canViewAudit ? 'Yes' : 'No'} />
                  <WorkspaceSummaryRow
                    label="Latest protected activity"
                    value={latestAuditSummary(employeeInsights) ?? 'No protected activity'}
                  />
                </CommandCenterInsightCard>
              </CommandCenterInsightGrid>
            </CommandCenterMain>

            <CommandCenterRail>
              <CommandCenterPanel
                title="Recent activity"
                actions={
                  <Button asChild size="xs" variant="ghost">
                    <Link to={canViewAudit ? '/employees/audit' : '/employees/directory'}>Open {canViewAudit ? 'audit' : 'directory'}</Link>
                  </Button>
                }
              >
                <CommandCenterActivityList>
                  {activityItems.map((item) => (
                    <CommandCenterActivityItem
                      key={item.id}
                      title={item.title}
                      detail={item.detail}
                      meta={item.meta}
                      tone={item.tone}
                      to={item.path}
                      pinned={item.path ? isFavorite(item.path) : false}
                      onTogglePinned={
                        item.path
                          ? () =>
                              toggleFavorite({
                                path: item.path!,
                                label: item.title,
                                icon: 'employees',
                                description: item.detail,
                                meta: item.meta,
                              })
                          : undefined
                      }
                      pinLabel={
                        item.path
                          ? `${isFavorite(item.path) ? 'Unpin' : 'Pin'} ${item.title}`
                          : undefined
                      }
                      icon={<ArrowUpRight className="h-4 w-4" />}
                    />
                  ))}
                </CommandCenterActivityList>
              </CommandCenterPanel>
            </CommandCenterRail>
          </CommandCenterLayout>
        </WorkspaceContent>
      </WorkspaceSurface>
    </WorkspacePage>
  )
}

function renderDirectoryTable(employees: EmployeeRecord[]) {
  return (
    <Table>
      <TableHeader className="bg-panel-soft/55">
        <TableRow>
          <TableHead>Employee</TableHead>
          <TableHead>Assignment</TableHead>
          <TableHead>Manager</TableHead>
          <TableHead>Status</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {employees.map((employee) => (
          <TableRow key={employee.id}>
            <TableHead scope="row" className="align-top">
              <div className="ui-table-stack">
                <Link to={`/employees/${employee.id}/profile`} className="ui-table-link">
                  {employee.full_name}
                </Link>
                <span className="ui-table-secondary">
                  {employee.employee_code} · {employee.email}
                </span>
              </div>
            </TableHead>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">
                  {employee.department.name} · {employee.designation.name}
                </span>
                <span className="ui-table-secondary">
                  {employee.location?.name ?? 'Location unassigned'}
                  {employee.cost_center ? ` · ${employee.cost_center.name}` : ''}
                </span>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{employee.manager?.full_name ?? 'Unassigned'}</span>
                <span className="ui-table-secondary">{employee.manager?.employee_code ?? 'Assign reporting line'}</span>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <div className="ui-table-status-stack">
                <div className="ui-table-badge-row">
                  <Badge variant={statusVariant(employee.employment_status)}>{statusLabel(employee.employment_status)}</Badge>
                  <Badge variant="subtle">{employee.employment_type.replace('_', ' ')}</Badge>
                </div>
                <span className="ui-table-secondary">Joined {formatDate(employee.date_of_joining)}</span>
              </div>
            </TableCell>
            <TableCell className="ui-table-action-cell align-top text-right">
              <Button asChild size="sm" variant="secondary">
                <Link to={`/employees/${employee.id}/profile`}>View details</Link>
              </Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function renderLifecycleTable(employees: EmployeeRecord[]) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Employee</TableHead>
          <TableHead>Assignment</TableHead>
          <TableHead>Lifecycle state</TableHead>
          <TableHead>Focus area</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {employees.map((employee) => (
          <TableRow key={employee.id}>
            <TableHead scope="row" className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{employee.full_name}</span>
                <span className="ui-table-secondary">
                  {employee.employee_code} · Joined {formatDate(employee.date_of_joining)}
                </span>
              </div>
            </TableHead>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">
                  {employee.department.name} · {employee.designation.name}
                </span>
                <span className="ui-table-secondary">
                  {employee.location?.name ?? 'Location unassigned'} · Manager {employee.manager?.full_name ?? 'Unassigned'}
                </span>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <Badge variant={statusVariant(employee.employment_status)}>{statusLabel(employee.employment_status)}</Badge>
            </TableCell>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{lifecycleWatchLabel(employee)}</span>
                <span className="ui-table-secondary">
                  {employee.termination_reason ?? 'Review the lifecycle workspace for the next controlled action.'}
                </span>
              </div>
            </TableCell>
            <TableCell className="ui-table-action-cell align-top text-right">
              <Button asChild size="sm" variant="secondary">
                <Link to={`/employees/${employee.id}/lifecycle`}>Open lifecycle</Link>
              </Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function renderOnboardingTable(employees: EmployeeRecord[], insights: Map<number, EmployeeModuleInsight>) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Employee</TableHead>
          <TableHead>Assignment</TableHead>
          <TableHead>Progress</TableHead>
          <TableHead>Pending work</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {employees.map((employee) => {
          const insight = insights.get(employee.id) ?? emptyEmployeeModuleInsight

          return (
            <TableRow key={employee.id}>
              <TableHead scope="row" className="align-top">
                <div className="ui-table-stack">
                  <span className="ui-table-primary">{employee.full_name}</span>
                  <span className="ui-table-secondary">
                    {employee.employee_code} · Joined {formatDate(employee.date_of_joining)}
                  </span>
                </div>
              </TableHead>
              <TableCell className="align-top">
                <div className="ui-table-stack">
                  <span className="ui-table-primary">
                    {employee.department.name} · {employee.designation.name}
                  </span>
                  <span className="ui-table-secondary">Manager {employee.manager?.full_name ?? 'Unassigned'}</span>
                </div>
              </TableCell>
              <TableCell className="align-top">
                <Badge variant={insight.onboardingIncompleteCount === 0 ? 'success' : 'warning'}>
                  {insight.onboardingProgress === null ? 'Open detail workspace' : `${insight.onboardingProgress}% complete`}
                </Badge>
              </TableCell>
              <TableCell className="align-top">
                <span className="ui-table-body-muted">
                  {insight.onboardingIncompleteCount === null
                    ? 'Track protected onboarding tasks in the employee workspace.'
                    : `${insight.onboardingIncompleteCount} outstanding task${insight.onboardingIncompleteCount === 1 ? '' : 's'}`}
                </span>
              </TableCell>
              <TableCell className="ui-table-action-cell align-top text-right">
                <Button asChild size="sm" variant="secondary">
                  <Link to={`/employees/${employee.id}/onboarding`}>Open onboarding</Link>
                </Button>
              </TableCell>
            </TableRow>
          )
        })}
      </TableBody>
    </Table>
  )
}

function renderDocumentsTable(employees: EmployeeRecord[], insights: Map<number, EmployeeModuleInsight>) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Employee</TableHead>
          <TableHead>Assignment</TableHead>
          <TableHead>Documents</TableHead>
          <TableHead>Risk</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {employees.map((employee) => {
          const insight = insights.get(employee.id) ?? emptyEmployeeModuleInsight

          return (
            <TableRow key={employee.id}>
              <TableHead scope="row" className="align-top">
                <div className="ui-table-stack">
                  <span className="ui-table-primary">{employee.full_name}</span>
                  <span className="ui-table-secondary">
                    {employee.employee_code} · {employee.email}
                  </span>
                </div>
              </TableHead>
              <TableCell className="align-top">
                <div className="ui-table-stack">
                  <span className="ui-table-primary">{employee.department.name}</span>
                  <span className="ui-table-secondary">{employee.designation.name}</span>
                </div>
              </TableCell>
              <TableCell className="align-top">
                <div className="ui-table-stack">
                  <span className="ui-table-primary">
                    {insight.documentCount === null ? 'Protected detail' : `${insight.documentCount} file(s)`}
                  </span>
                  <span className="ui-table-secondary">
                    {insight.expiringDocumentCount
                      ? `${insight.expiringDocumentCount} expiring soon`
                      : 'No expiring files detected'}
                  </span>
                </div>
              </TableCell>
              <TableCell className="align-top">
                <Badge variant={insight.expiringDocumentCount > 0 ? 'warning' : 'success'}>
                  {insight.expiringDocumentCount > 0 ? 'Needs review' : 'Healthy'}
                </Badge>
              </TableCell>
              <TableCell className="ui-table-action-cell align-top text-right">
                <Button asChild size="sm" variant="secondary">
                  <Link to={`/employees/${employee.id}/documents`}>Open documents</Link>
                </Button>
              </TableCell>
            </TableRow>
          )
        })}
      </TableBody>
    </Table>
  )
}

function renderAuditTable(employees: EmployeeRecord[], insights: Map<number, EmployeeModuleInsight>) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Employee</TableHead>
          <TableHead>Assignment</TableHead>
          <TableHead>Protected history</TableHead>
          <TableHead>Last event</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {employees.map((employee) => {
          const insight = insights.get(employee.id) ?? emptyEmployeeModuleInsight

          return (
            <TableRow key={employee.id}>
              <TableHead scope="row" className="align-top">
                <div className="ui-table-stack">
                  <span className="ui-table-primary">{employee.full_name}</span>
                  <span className="ui-table-secondary">
                    {employee.employee_code} · {employee.department.name}
                  </span>
                </div>
              </TableHead>
              <TableCell className="align-top">
                <div className="ui-table-stack">
                  <span className="ui-table-primary">{employee.designation.name}</span>
                  <span className="ui-table-secondary">{employee.location?.name ?? 'Location unassigned'}</span>
                </div>
              </TableCell>
              <TableCell className="align-top">
                <div className="ui-table-stack">
                  <span className="ui-table-primary">
                    {insight.auditCount === null ? 'Protected workspace' : `${insight.auditCount} event(s)`}
                  </span>
                  <span className="ui-table-secondary">
                    {insight.latestAuditTimestamp ? relativeTime(insight.latestAuditTimestamp) : 'No audit event visible'}
                  </span>
                </div>
              </TableCell>
              <TableCell className="align-top">
                <Badge variant={insight.latestAuditTimestamp ? 'info' : 'subtle'}>
                  {insight.latestAuditTimestamp ? 'Recent activity' : 'No recent event'}
                </Badge>
              </TableCell>
              <TableCell className="ui-table-action-cell align-top text-right">
                <Button asChild size="sm" variant="secondary">
                  <Link to={`/employees/${employee.id}/history`}>Open audit</Link>
                </Button>
              </TableCell>
            </TableRow>
          )
        })}
      </TableBody>
    </Table>
  )
}

const emptyEmployeeModuleInsight: EmployeeModuleInsight = {
  onboardingProgress: null,
  onboardingIncompleteCount: null,
  documentCount: null,
  expiringDocumentCount: 0,
  auditCount: null,
  latestAuditTimestamp: null,
}

function matchesEmployeeOverviewTab(employee: EmployeeRecord, tab: EmployeeOverviewTab) {
  switch (tab) {
    case 'lifecycle':
      return employee.employment_status !== 'active'
    case 'onboarding':
      return employee.employment_status !== 'terminated'
    case 'documents':
    case 'audit':
    case 'directory':
    default:
      return true
  }
}

function getEmployeeModuleInsight(
  employee: EmployeeRecord,
  source: 'demo' | 'live',
  snapshot: ReturnType<typeof useAccessSnapshot>['snapshot'],
): EmployeeModuleInsight {
  if (source === 'demo') {
    const workspace = buildDemoEmployeeWorkspace(snapshot, employee.id)
    const documents = workspace?.documents ?? []
    const expiringDocumentCount = documents.filter((document) => isExpiringSoon(document.expiry_date)).length

    return {
      onboardingProgress: workspace?.onboarding.summary.progress_percentage ?? null,
      onboardingIncompleteCount: workspace?.onboarding.summary.incomplete_count ?? null,
      documentCount: documents.length,
      expiringDocumentCount,
      auditCount: workspace?.auditHistory.items.length ?? null,
      latestAuditTimestamp: workspace?.auditHistory.items[0]?.created_at ?? null,
    }
  }

  return {
    onboardingProgress: null,
    onboardingIncompleteCount: null,
    documentCount: null,
    expiringDocumentCount: 0,
    auditCount: null,
    latestAuditTimestamp: null,
  }
}

function isExpiringSoon(expiryDate: string | null) {
  if (!expiryDate) {
    return false
  }

  return daysUntil(expiryDate) <= 30
}

function latestAuditSummary(insights: Map<number, EmployeeModuleInsight>) {
  const latestTimestamp = [...insights.values()]
    .map((insight) => insight.latestAuditTimestamp)
    .filter((value): value is string => Boolean(value))
    .sort((left, right) => right.localeCompare(left))[0]

  return latestTimestamp ? relativeTime(latestTimestamp) : null
}

function lifecycleWatchLabel(employee: EmployeeRecord) {
  switch (employee.employment_status) {
    case 'probation':
      return 'Probation follow-up'
    case 'notice_period':
      return 'Exit planning'
    case 'terminated':
      return 'Historical record'
    case 'inactive':
      return 'Reactivation review'
    default:
      return 'Healthy lifecycle'
  }
}

function statusVariant(value: EmployeeStatus): 'success' | 'warning' | 'subtle' | 'danger' {
  switch (value) {
    case 'active':
      return 'success'
    case 'probation':
    case 'notice_period':
      return 'warning'
    case 'terminated':
      return 'danger'
    case 'inactive':
    default:
      return 'subtle'
  }
}

function statusLabel(value: EmployeeStatus) {
  switch (value) {
    case 'notice_period':
      return 'Notice period'
    default:
      return value.replace('_', ' ')
  }
}

function formatDate(value: string) {
  return formatRegionalDate(value, value)
}

function daysUntil(value: string) {
  const target = new Date(value)
  const today = new Date()
  target.setHours(0, 0, 0, 0)
  today.setHours(0, 0, 0, 0)

  return Math.round((target.getTime() - today.getTime()) / (1000 * 60 * 60 * 24))
}

function relativeTime(value: string | null) {
  return formatRegionalRelativeTimestamp(value, 'No activity time')
}
