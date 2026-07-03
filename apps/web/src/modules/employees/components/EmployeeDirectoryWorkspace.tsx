import { MoreHorizontal, Star } from 'lucide-react'
import { useMemo, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import type { AccessSnapshot } from '../../access/types'
import { useShellFavorites } from '../../../app/shell/favorites'
import {
  formatRegionalDate,
  formatRegionalDateTime,
} from '../../../shared/regionalization/formatters'
import { buildDemoEmployeeWorkspace } from '../data/demoEmployeeProfiles'
import { useEmployeeDirectory, employeeStatusOptions } from '../hooks/useEmployeeDirectory'
import type { EmployeeDirectoryFilters, EmployeeRecord, EmployeeStatus } from '../types'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardTitle } from '../../../shared/ui/card'
import { cn } from '../../../shared/ui/cn'
import {
  ConsoleBulkBar,
  ConsoleMetricChip,
  ConsoleMetricRow,
  ConsoleSearchField,
  ConsoleToolbar,
  ConsoleToolbarRow,
  TableSelectionCheckbox,
} from '../../../shared/ui/console-table'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '../../../shared/ui/dropdown-menu'
import { Modal } from '../../../shared/ui/modal'
import { SelectField as AppSelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceFilters,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePinButton,
  WorkspacePage,
  WorkspaceSurface,
  WorkspaceTableShell,
} from '../../../shared/ui/workspace'

const baseInitialFilters: EmployeeDirectoryFilters = {
  search: '',
  employmentStatus: '',
  departmentId: '',
  designationId: '',
  managerId: '',
  page: 1,
  perPage: 50,
}
const emptyEmployeeRecords: EmployeeRecord[] = []

export type EmployeeDirectoryWorkspaceView =
  | 'directory'
  | 'lifecycle'
  | 'onboarding'
  | 'documents'
  | 'audit'

interface EmployeeWorkspaceViewConfig {
  title: string
  emptyTitle: string
  emptyCopy: string
  showStatusFilter: boolean
  showDesignationFilter: boolean
}

const employeeWorkspaceViewConfig: Record<EmployeeDirectoryWorkspaceView, EmployeeWorkspaceViewConfig> = {
  directory: {
    title: 'Employee directory',
    emptyTitle: 'No employees match the current filters',
    emptyCopy: 'Adjust the search or filters to widen the visible roster.',
    showStatusFilter: true,
    showDesignationFilter: true,
  },
  lifecycle: {
    title: 'Lifecycle watch',
    emptyTitle: 'No lifecycle records need attention',
    emptyCopy: 'The current workspace does not have probation, notice, inactive, or terminated records in this filtered view.',
    showStatusFilter: false,
    showDesignationFilter: true,
  },
  onboarding: {
    title: 'Onboarding queue',
    emptyTitle: 'No onboarding records match the current filters',
    emptyCopy: 'Use search, department, or manager filters to reopen the onboarding queue.',
    showStatusFilter: false,
    showDesignationFilter: false,
  },
  documents: {
    title: 'Document registry',
    emptyTitle: 'No employee document records match the current filters',
    emptyCopy: 'Adjust the search, department, or manager filters to widen the document view.',
    showStatusFilter: false,
    showDesignationFilter: false,
  },
  audit: {
    title: 'Audit registry',
    emptyTitle: 'No employee audit records match the current filters',
    emptyCopy: 'Adjust the search, department, or manager filters to widen the protected audit view.',
    showStatusFilter: false,
    showDesignationFilter: false,
  },
}

interface EmployeeModuleInsight {
  onboardingProgress: number | null
  onboardingIncompleteCount: number | null
  documentCount: number | null
  expiringDocumentCount: number
  auditCount: number | null
  latestAuditTimestamp: string | null
}

export function EmployeeDirectoryWorkspace({
  view = 'directory',
}: {
  view?: EmployeeDirectoryWorkspaceView
}) {
  return <EmployeeDirectoryWorkspaceContent key={view} view={view} />
}

function EmployeeDirectoryWorkspaceContent({
  view,
}: {
  view: EmployeeDirectoryWorkspaceView
}) {
  const navigate = useNavigate()
  const { isFavorite, toggleFavorite } = useShellFavorites()
  const [filters, setFilters] = useState<EmployeeDirectoryFilters>(baseInitialFilters)
  const [selectedEmployeeIdsState, setSelectedEmployeeIds] = useState<number[]>([])
  const [quickViewEmployeeId, setQuickViewEmployeeId] = useState<number | null>(null)
  const { directory, departments, designations, managers, isLoading, error, canManage, source, snapshot } =
    useEmployeeDirectory(filters)
  const config = employeeWorkspaceViewConfig[view]
  const workspaceFavorite = useMemo(() => {
    if (view === 'lifecycle') {
      return {
        path: '/employees/lifecycle-watch',
        label: 'Employee lifecycle watch',
        icon: 'employees' as const,
        description: 'Pinned employee lifecycle watch workspace',
      }
    }

    if (view === 'onboarding') {
      return {
        path: '/employees/onboarding',
        label: 'Employee onboarding queue',
        icon: 'employees' as const,
        description: 'Pinned employee onboarding workspace',
      }
    }

    if (view === 'documents') {
      return {
        path: '/employees/documents',
        label: 'Employee document registry',
        icon: 'employees' as const,
        description: 'Pinned employee document workspace',
      }
    }

    if (view === 'audit') {
      return {
        path: '/employees/audit',
        label: 'Employee audit registry',
        icon: 'employees' as const,
        description: 'Pinned employee audit workspace',
      }
    }

    return {
      path: '/employees/directory',
      label: 'Employee directory',
      icon: 'employees' as const,
      description: 'Pinned workforce directory',
    }
  }, [view])

  const scopedEmployees = useMemo(() => {
    const employees = directory?.items ?? emptyEmployeeRecords
    return employees.filter((employee) => matchesEmployeeWorkspaceView(employee, view))
  }, [directory?.items, view])

  const recordCount = view === 'directory' ? directory?.meta.total ?? 0 : scopedEmployees.length
  const filtersDirty = JSON.stringify(filters) !== JSON.stringify(baseInitialFilters)
  const selectedEmployeeIds = useMemo(
    () =>
      selectedEmployeeIdsState.filter((employeeId) =>
        scopedEmployees.some((employee) => employee.id === employeeId),
      ),
    [scopedEmployees, selectedEmployeeIdsState],
  )
  const selectedEmployees = useMemo(
    () => scopedEmployees.filter((employee) => selectedEmployeeIds.includes(employee.id)),
    [scopedEmployees, selectedEmployeeIds],
  )
  const quickViewEmployee =
    scopedEmployees.find((employee) => employee.id === quickViewEmployeeId) ?? null
  const directoryMetrics = useMemo(() => {
    if (view !== 'directory') {
      return []
    }

    const active = scopedEmployees.filter((employee) => employee.employment_status === 'active').length
    const probation = scopedEmployees.filter((employee) => employee.employment_status === 'probation').length
    const notice = scopedEmployees.filter((employee) => employee.employment_status === 'notice_period').length
    const unassigned = scopedEmployees.filter((employee) => employee.manager == null).length

    return [
      { label: 'Visible', value: scopedEmployees.length, tone: 'info' as const },
      { label: 'Active', value: active, tone: 'success' as const },
      { label: 'Probation', value: probation, tone: 'warning' as const },
      { label: 'Without manager', value: unassigned, tone: 'neutral' as const },
      ...(notice > 0 ? [{ label: 'Notice', value: notice, tone: 'warning' as const }] : []),
    ]
  }, [scopedEmployees, view])
  const singleSelectedEmployee = selectedEmployees.length === 1 ? selectedEmployees[0] : null

  const allVisibleSelected = scopedEmployees.length > 0 && selectedEmployeeIds.length === scopedEmployees.length
  const someVisibleSelected =
    selectedEmployeeIds.length > 0 && selectedEmployeeIds.length < scopedEmployees.length

  function toggleEmployeeSelection(employeeId: number, checked: boolean) {
    setSelectedEmployeeIds((current) => {
      if (checked) {
        return current.includes(employeeId) ? current : [...current, employeeId]
      }

      return current.filter((value) => value !== employeeId)
    })
  }

  function toggleAllVisibleEmployees(checked: boolean) {
    setSelectedEmployeeIds(checked ? scopedEmployees.map((employee) => employee.id) : [])
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface>
        <WorkspaceHeader compact>
          <div className="space-y-1">
            <CardTitle>{config.title}</CardTitle>
            <p className="text-sm text-muted-foreground">
              {view === 'directory'
                ? 'Manage the live roster, assignments, and employee access points from one operational registry.'
                : 'Review this employee queue from one focused collection surface.'}
            </p>
          </div>
          <WorkspaceHeaderActions>
            <WorkspacePinButton
              pinned={isFavorite(workspaceFavorite.path)}
              onToggle={() => toggleFavorite(workspaceFavorite)}
            />
          </WorkspaceHeaderActions>
        </WorkspaceHeader>
        <WorkspaceContent>
          <ConsoleToolbar>
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
                  aria-label="Search"
                />
                {directoryMetrics.length ? (
                  <ConsoleMetricRow>
                    {directoryMetrics.map((metric) => (
                      <ConsoleMetricChip
                        key={metric.label}
                        label={metric.label}
                        value={metric.value}
                        tone={metric.tone}
                      />
                    ))}
                  </ConsoleMetricRow>
                ) : null}
              </div>

              <div className="flex flex-wrap items-center gap-2 xl:justify-end">
                <span className="text-sm text-muted-foreground">
                  {recordCount} employee{recordCount === 1 ? '' : 's'}
                </span>
                <Button variant="secondary" onClick={() => setFilters(baseInitialFilters)} disabled={!filtersDirty}>
                  Reset filters
                </Button>
              </div>
            </ConsoleToolbarRow>

            <ConsoleToolbarRow className="items-end">
              <WorkspaceFilters>
                {config.showStatusFilter ? (
                  <div className="min-w-[11rem] flex-1 xl:max-w-[12rem]">
                    <SelectField
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
                ) : null}

                <div className="min-w-[12rem] flex-1 xl:max-w-[14rem]">
                  <SelectField
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

                {config.showDesignationFilter ? (
                  <div className="min-w-[12rem] flex-1 xl:max-w-[14rem]">
                    <SelectField
                      label="Designation"
                      value={filters.designationId}
                      onChange={(value) =>
                        setFilters((current) => ({
                          ...current,
                          page: 1,
                          designationId: value,
                        }))
                      }
                      compact
                      options={[
                        { value: '', label: 'All designations' },
                        ...designations.map((designation) => ({
                          value: String(designation.id),
                          label: designation.name,
                        })),
                      ]}
                    />
                  </div>
                ) : null}

                <div className="min-w-[12rem] flex-1 xl:max-w-[14rem]">
                  <SelectField
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

          {isLoading ? <p className="workspace-muted">Loading employee workspace...</p> : null}
          {error ? <p className="workspace-error">{error.message}</p> : null}

          {!isLoading && !error && directory && scopedEmployees.length === 0 ? (
            <WorkspaceEmptyState title={config.emptyTitle} copy={config.emptyCopy} />
          ) : null}

          {scopedEmployees.length ? (
            <WorkspaceTableShell>
              {view === 'directory'
                ? renderDirectoryTable({
                    employees: scopedEmployees,
                    canManage,
                    selectedEmployeeIds,
                    allVisibleSelected,
                    someVisibleSelected,
                    onToggleEmployeeSelection: toggleEmployeeSelection,
                    onToggleAllVisibleEmployees: toggleAllVisibleEmployees,
                    onOpenQuickView: setQuickViewEmployeeId,
                    isFavorite,
                    onToggleFavorite: toggleFavorite,
                  })
                : null}
              {view === 'lifecycle' ? renderLifecycleTable(scopedEmployees, canManage) : null}
              {view === 'onboarding' ? renderOnboardingTable(scopedEmployees, source, snapshot) : null}
              {view === 'documents' ? renderDocumentsTable(scopedEmployees, source, snapshot) : null}
              {view === 'audit' ? renderAuditTable(scopedEmployees, source, snapshot) : null}
            </WorkspaceTableShell>
          ) : null}

          {view === 'directory' && selectedEmployees.length ? (
            <ConsoleBulkBar
              summary={
                <>
                  <span className="grid h-8 min-w-8 place-items-center rounded-full bg-white/10 px-2 text-xs font-semibold">
                    {selectedEmployees.length}
                  </span>
                  <div className="space-y-0.5">
                    <p className="text-sm font-semibold text-white">Employee selection active</p>
                    <p className="text-xs text-slate-300">
                      {selectedEmployees.length === 1
                        ? `${selectedEmployees[0].full_name} is ready for quick actions.`
                        : `${selectedEmployees.length} employees are selected from the current filtered roster.`}
                    </p>
                  </div>
                </>
              }
              actions={
                <>
                  <Button
                    size="sm"
                    variant="secondary"
                    disabled={!singleSelectedEmployee}
                    onClick={() => {
                      if (singleSelectedEmployee) {
                        navigate(`/employees/${singleSelectedEmployee.id}/profile`)
                      }
                    }}
                  >
                    Open profile
                  </Button>
                  <Button
                    size="sm"
                    variant="secondary"
                    disabled={!singleSelectedEmployee}
                    onClick={() => {
                      if (singleSelectedEmployee) {
                        navigate(`/employees/${singleSelectedEmployee.id}/lifecycle`)
                      }
                    }}
                  >
                    Lifecycle
                  </Button>
                  <Button
                    size="sm"
                    variant="secondary"
                    disabled={!singleSelectedEmployee}
                    onClick={() => {
                      if (singleSelectedEmployee) {
                        navigate(`/employees/${singleSelectedEmployee.id}/documents`)
                      }
                    }}
                  >
                    Documents
                  </Button>
                  <Button size="sm" variant="ghost" onClick={() => setSelectedEmployeeIds([])}>
                    Clear selection
                  </Button>
                </>
              }
            />
          ) : null}
        </WorkspaceContent>
      </WorkspaceSurface>

      <Modal
        open={Boolean(quickViewEmployee)}
        onClose={() => setQuickViewEmployeeId(null)}
        title={quickViewEmployee ? quickViewEmployee.full_name : 'Employee quick view'}
        description={
          quickViewEmployee
            ? `${quickViewEmployee.employee_code} · ${quickViewEmployee.department.name} · ${quickViewEmployee.designation.name}`
            : undefined
        }
        size="md"
        footer={
          quickViewEmployee ? (
            <>
              <Button variant="ghost" onClick={() => setQuickViewEmployeeId(null)}>
                Close
              </Button>
              <Button asChild variant="secondary">
                <Link to={`/employees/${quickViewEmployee.id}/lifecycle`}>Open lifecycle</Link>
              </Button>
              <Button asChild variant="primary">
                <Link to={`/employees/${quickViewEmployee.id}/profile`}>Open profile</Link>
              </Button>
            </>
          ) : null
        }
      >
        {quickViewEmployee ? <EmployeeQuickView employee={quickViewEmployee} /> : null}
      </Modal>
    </WorkspacePage>
  )
}

function renderDirectoryTable({
  employees,
  canManage,
  selectedEmployeeIds,
  allVisibleSelected,
  someVisibleSelected,
  onToggleEmployeeSelection,
  onToggleAllVisibleEmployees,
  onOpenQuickView,
  isFavorite,
  onToggleFavorite,
}: {
  employees: EmployeeRecord[]
  canManage: boolean
  selectedEmployeeIds: number[]
  allVisibleSelected: boolean
  someVisibleSelected: boolean
  onToggleEmployeeSelection: (employeeId: number, checked: boolean) => void
  onToggleAllVisibleEmployees: (checked: boolean) => void
  onOpenQuickView: (employeeId: number) => void
  isFavorite: (path: string) => boolean
  onToggleFavorite: (favorite: {
    path: string
    label: string
    icon: 'employees'
    description: string
    meta?: string
  }) => boolean
}) {
  return (
    <Table className="min-w-[64rem]">
      <TableHeader className="bg-panel-soft/55">
        <TableRow>
          <TableHead scope="col" className="w-14 pl-5">
            <TableSelectionCheckbox
              checked={allVisibleSelected}
              indeterminate={someVisibleSelected}
              onChange={onToggleAllVisibleEmployees}
              ariaLabel={allVisibleSelected ? 'Clear visible employee selection' : 'Select all visible employees'}
            />
          </TableHead>
          <TableHead scope="col">Employee</TableHead>
          <TableHead scope="col">Assignment</TableHead>
          <TableHead scope="col">Manager</TableHead>
          <TableHead scope="col">Status</TableHead>
          <TableHead scope="col" className="pr-5 text-right">
            Actions
          </TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {employees.map((employee) => {
          const isSelected = selectedEmployeeIds.includes(employee.id)
          const profilePath = `/employees/${employee.id}/profile`
          const profilePinned = isFavorite(profilePath)

          return (
            <TableRow key={employee.id} data-state={isSelected ? 'selected' : undefined} className="group">
              <TableCell className="pl-5 align-top">
                <TableSelectionCheckbox
                  checked={isSelected}
                  onChange={(checked) => onToggleEmployeeSelection(employee.id, checked)}
                  ariaLabel={`Select ${employee.full_name}`}
                />
              </TableCell>
              <TableHead scope="row" className="align-top">
                <div className="flex items-start gap-3">
                  <span className="ui-table-avatar">
                    {employeeInitials(employee.full_name)}
                  </span>
                  <div className="ui-table-stack">
                    <div className="ui-table-badge-row">
                      <Link
                        to={`/employees/${employee.id}/profile`}
                        className="ui-table-link truncate"
                      >
                        {employee.full_name}
                      </Link>
                    </div>
                    <small className="ui-table-secondary block">
                      {employee.employee_code} · {employee.email}
                    </small>
                  </div>
                </div>
              </TableHead>
              <TableCell className="align-top">
                <div className="ui-table-stack">
                  <p className="ui-table-primary">
                    {employee.department.name} · {employee.designation.name}
                  </p>
                  <small className="ui-table-secondary">
                    {employee.location?.name ?? 'Location unassigned'}
                    {employee.cost_center ? ` · ${employee.cost_center.name}` : ''}
                  </small>
                </div>
              </TableCell>
              <TableCell className="align-top">
                <div className="ui-table-stack">
                  <p className="ui-table-primary">{employee.manager?.full_name ?? 'Unassigned'}</p>
                  <small className="ui-table-secondary">
                    {employee.manager?.employee_code ?? 'Assign a reporting line in the employee workspace'}
                  </small>
                </div>
              </TableCell>
              <TableCell className="align-top">
                <div className="ui-table-status-stack">
                  <div className="ui-table-badge-row">
                    <Badge variant={statusVariant(employee.employment_status)}>{statusLabel(employee.employment_status)}</Badge>
                    <Badge variant="subtle">{employee.employment_type.replace('_', ' ')}</Badge>
                  </div>
                  <small className="ui-table-secondary">
                    Joined {formatDate(employee.date_of_joining)}
                  </small>
                </div>
              </TableCell>
              <TableCell className="ui-table-action-cell pr-5 align-top">
                <div className="ui-table-action-row">
                  <Button asChild size="sm" variant="secondary">
                    <Link to={`/employees/${employee.id}/profile`}>{canManage ? 'Open' : 'Detail'}</Link>
                  </Button>
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <Button size="sm" variant="ghost" aria-label={`More actions for ${employee.full_name}`}>
                        <MoreHorizontal className="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-56">
                      <DropdownMenuLabel>Employee actions</DropdownMenuLabel>
                      <DropdownMenuSeparator />
                      <DropdownMenuItem onSelect={() => onOpenQuickView(employee.id)}>
                        Quick view
                      </DropdownMenuItem>
                      <DropdownMenuItem
                        onSelect={() =>
                          onToggleFavorite({
                            path: profilePath,
                            label: `${employee.full_name} profile`,
                            icon: 'employees',
                            description: 'Pinned employee profile workspace',
                            meta: employee.employee_code,
                          })
                        }
                      >
                        <Star className={cn('h-4 w-4', profilePinned && 'fill-current')} />
                        {profilePinned ? 'Unpin profile workspace' : 'Pin profile workspace'}
                      </DropdownMenuItem>
                      <DropdownMenuItem asChild>
                        <Link to={`/employees/${employee.id}/lifecycle`}>Open lifecycle</Link>
                      </DropdownMenuItem>
                      <DropdownMenuItem asChild>
                        <Link to={`/employees/${employee.id}/onboarding`}>Open onboarding</Link>
                      </DropdownMenuItem>
                      <DropdownMenuItem asChild>
                        <Link to={`/employees/${employee.id}/documents`}>Open documents</Link>
                      </DropdownMenuItem>
                      <DropdownMenuItem asChild>
                        <Link to={`/employees/${employee.id}/history`}>Open audit history</Link>
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
              </TableCell>
            </TableRow>
          )
        })}
      </TableBody>
    </Table>
  )
}

function renderLifecycleTable(employees: EmployeeRecord[], canManage: boolean) {
  return (
    <Table>
      <colgroup>
        <col style={{ width: '24%' }} />
        <col style={{ width: '32%' }} />
        <col style={{ width: '18%' }} />
        <col style={{ width: '14%' }} />
        <col style={{ width: '12%' }} />
      </colgroup>
      <TableHeader>
        <TableRow>
          <TableHead scope="col">Employee</TableHead>
          <TableHead scope="col">Assignment</TableHead>
          <TableHead scope="col">Lifecycle state</TableHead>
          <TableHead scope="col">Focus area</TableHead>
          <TableHead scope="col">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {employees.map((employee) => (
          <TableRow key={employee.id}>
            <TableHead scope="row" className="align-top">
              <div className="ui-table-stack">
                <strong className="ui-table-primary block">{employee.full_name}</strong>
                <small className="ui-table-secondary block">
                  {employee.employee_code} · Joined {formatDate(employee.date_of_joining)}
                </small>
              </div>
            </TableHead>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <p className="ui-table-body-copy">
                  {employee.department.name} · {employee.designation.name}
                </p>
                <small className="ui-table-secondary block">
                  {employee.location?.name ?? 'Location unassigned'}
                  · Manager {employee.manager?.full_name ?? 'Unassigned'}
                </small>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <div className="ui-table-status-stack">
                <div className="ui-table-badge-row">
                  <Badge variant={statusVariant(employee.employment_status)}>{statusLabel(employee.employment_status)}</Badge>
                </div>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <p className="ui-table-body-copy">{lifecycleWatchLabel(employee)}</p>
                <small className="ui-table-secondary block">
                  {employee.termination_reason ?? 'Review the lifecycle workspace for the next controlled action.'}
                </small>
              </div>
            </TableCell>
            <TableCell className="ui-table-action-cell align-top">
              <Button asChild size="sm" variant="secondary">
                <Link to={`/employees/${employee.id}/lifecycle`}>{canManage ? 'Open' : 'View'}</Link>
              </Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function renderOnboardingTable(
  employees: EmployeeRecord[],
  source: 'demo' | 'live',
  snapshot: AccessSnapshot | null,
) {
  return (
    <Table>
      <colgroup>
        <col style={{ width: '24%' }} />
        <col style={{ width: '28%' }} />
        <col style={{ width: '18%' }} />
        <col style={{ width: '18%' }} />
        <col style={{ width: '12%' }} />
      </colgroup>
      <TableHeader>
        <TableRow>
          <TableHead scope="col">Employee</TableHead>
          <TableHead scope="col">Assignment</TableHead>
          <TableHead scope="col">Progress</TableHead>
          <TableHead scope="col">Pending work</TableHead>
          <TableHead scope="col">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {employees.map((employee) => {
          const insight = getEmployeeModuleInsight(employee, source, snapshot)

          return (
            <TableRow key={employee.id}>
              <TableHead scope="row" className="align-top">
                <div className="ui-table-stack">
                  <strong className="ui-table-primary block">{employee.full_name}</strong>
                  <small className="ui-table-secondary block">
                    {employee.employee_code} · Joined {formatDate(employee.date_of_joining)}
                  </small>
                </div>
              </TableHead>
              <TableCell className="align-top">
                <div className="ui-table-stack">
                  <p className="ui-table-body-copy">
                    {employee.department.name} · {employee.designation.name}
                  </p>
                  <small className="ui-table-secondary block">
                    Manager {employee.manager?.full_name ?? 'Unassigned'}
                  </small>
                </div>
              </TableCell>
              <TableCell className="align-top">
                <Badge variant={insight.onboardingIncompleteCount === 0 ? 'success' : 'warning'}>
                  {insight.onboardingProgress === null ? 'Tracked in detail workspace' : `${insight.onboardingProgress}% complete`}
                </Badge>
              </TableCell>
              <TableCell className="align-top">
                <p className="ui-table-body-muted">
                  {insight.onboardingIncompleteCount === null
                    ? 'Protected task details open in the employee workspace.'
                    : `${insight.onboardingIncompleteCount} outstanding task${
                        insight.onboardingIncompleteCount === 1 ? '' : 's'
                      }`}
                </p>
              </TableCell>
              <TableCell className="ui-table-action-cell align-top">
                <Button asChild size="sm" variant="secondary">
                  <Link to={`/employees/${employee.id}/onboarding`}>Open</Link>
                </Button>
              </TableCell>
            </TableRow>
          )
        })}
      </TableBody>
    </Table>
  )
}

function renderDocumentsTable(
  employees: EmployeeRecord[],
  source: 'demo' | 'live',
  snapshot: AccessSnapshot | null,
) {
  return (
    <Table>
      <colgroup>
        <col style={{ width: '24%' }} />
        <col style={{ width: '28%' }} />
        <col style={{ width: '18%' }} />
        <col style={{ width: '18%' }} />
        <col style={{ width: '12%' }} />
      </colgroup>
      <TableHeader>
        <TableRow>
          <TableHead scope="col">Employee</TableHead>
          <TableHead scope="col">Assignment</TableHead>
          <TableHead scope="col">Records</TableHead>
          <TableHead scope="col">Expiry posture</TableHead>
          <TableHead scope="col">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {employees.map((employee) => {
          const insight = getEmployeeModuleInsight(employee, source, snapshot)

          return (
            <TableRow key={employee.id}>
              <TableHead scope="row" className="align-top">
                <div className="ui-table-stack">
                  <strong className="ui-table-primary block">{employee.full_name}</strong>
                  <small className="ui-table-secondary block">
                    {employee.employee_code} · {employee.email}
                  </small>
                </div>
              </TableHead>
              <TableCell className="align-top">
                <div className="ui-table-stack">
                  <p className="ui-table-body-copy">
                    {employee.department.name} · {employee.designation.name}
                  </p>
                  <small className="ui-table-secondary block">
                    {employee.location?.name ?? 'Location unassigned'}
                  </small>
                </div>
              </TableCell>
              <TableCell className="align-top">
                <Badge variant="subtle">
                  {insight.documentCount === null ? 'Protected file cabinet' : `${insight.documentCount} file${insight.documentCount === 1 ? '' : 's'}`}
                </Badge>
              </TableCell>
              <TableCell className="align-top">
                <p className="ui-table-body-muted">
                  {insight.documentCount === null
                    ? 'Open the employee workspace to inspect protected file details.'
                    : insight.expiringDocumentCount > 0
                      ? `${insight.expiringDocumentCount} file${insight.expiringDocumentCount === 1 ? '' : 's'} expiring soon`
                      : 'No upcoming expiries'}
                </p>
              </TableCell>
              <TableCell className="ui-table-action-cell align-top">
                <Button asChild size="sm" variant="secondary">
                  <Link to={`/employees/${employee.id}/documents`}>Open</Link>
                </Button>
              </TableCell>
            </TableRow>
          )
        })}
      </TableBody>
    </Table>
  )
}

function renderAuditTable(
  employees: EmployeeRecord[],
  source: 'demo' | 'live',
  snapshot: AccessSnapshot | null,
) {
  return (
    <Table>
      <colgroup>
        <col style={{ width: '24%' }} />
        <col style={{ width: '28%' }} />
        <col style={{ width: '18%' }} />
        <col style={{ width: '18%' }} />
        <col style={{ width: '12%' }} />
      </colgroup>
      <TableHeader>
        <TableRow>
          <TableHead scope="col">Employee</TableHead>
          <TableHead scope="col">Assignment</TableHead>
          <TableHead scope="col">Recent events</TableHead>
          <TableHead scope="col">Latest activity</TableHead>
          <TableHead scope="col">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {employees.map((employee) => {
          const insight = getEmployeeModuleInsight(employee, source, snapshot)

          return (
            <TableRow key={employee.id}>
              <TableHead scope="row" className="align-top">
                <div className="ui-table-stack">
                  <strong className="ui-table-primary block">{employee.full_name}</strong>
                  <small className="ui-table-secondary block">
                    {employee.employee_code} · {employee.email}
                  </small>
                </div>
              </TableHead>
              <TableCell className="align-top">
                <div className="ui-table-stack">
                  <p className="ui-table-body-copy">
                    {employee.department.name} · {employee.designation.name}
                  </p>
                  <small className="ui-table-secondary block">
                    Manager {employee.manager?.full_name ?? 'Unassigned'}
                  </small>
                </div>
              </TableCell>
              <TableCell className="align-top">
                <Badge variant="subtle">
                  {insight.auditCount === null ? 'Protected stream' : `${insight.auditCount} event${insight.auditCount === 1 ? '' : 's'}`}
                </Badge>
              </TableCell>
              <TableCell className="align-top">
                <p className="ui-table-body-muted">
                  {insight.latestAuditTimestamp
                    ? formatDateTime(insight.latestAuditTimestamp)
                    : 'Open the employee workspace to inspect the protected event log.'}
                </p>
              </TableCell>
              <TableCell className="ui-table-action-cell align-top">
                <Button asChild size="sm" variant="secondary">
                  <Link to={`/employees/${employee.id}/history`}>Open</Link>
                </Button>
              </TableCell>
            </TableRow>
          )
        })}
      </TableBody>
    </Table>
  )
}

function SelectField({
  label,
  value,
  onChange,
  options,
  compact = false,
}: {
  label: string
  value: string
  onChange: (value: string) => void
  options: Array<{ value: string; label: string }>
  compact?: boolean
}) {
  return <AppSelectField label={label} value={value} onChange={onChange} options={options} compact={compact} />
}

function EmployeeQuickView({ employee }: { employee: EmployeeRecord }) {
  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center gap-2">
        <Badge variant={statusVariant(employee.employment_status)}>{statusLabel(employee.employment_status)}</Badge>
        <Badge variant="subtle">{employee.employment_type.replace('_', ' ')}</Badge>
        {employee.location?.name ? <Badge variant="subtle">{employee.location.name}</Badge> : null}
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        <QuickViewField label="Work email" value={employee.email} />
        <QuickViewField label="Phone" value={employee.phone ?? 'Not recorded'} />
        <QuickViewField label="Department" value={employee.department.name} />
        <QuickViewField label="Designation" value={employee.designation.name} />
        <QuickViewField label="Manager" value={employee.manager?.full_name ?? 'Unassigned'} />
        <QuickViewField label="Date of joining" value={formatDate(employee.date_of_joining)} />
        <QuickViewField label="Location" value={employee.location?.name ?? 'Location unassigned'} />
        <QuickViewField label="Cost center" value={employee.cost_center?.name ?? 'Not assigned'} />
      </div>
    </div>
  )
}

function QuickViewField({
  label,
  value,
}: {
  label: string
  value: string
}) {
  return (
    <div className="rounded-xl border border-line bg-panel-soft/50 px-4 py-3">
      <p className="text-[0.68rem] font-semibold uppercase tracking-[0.14em] text-text-subtle">{label}</p>
      <p className="mt-1 text-sm font-medium text-foreground">{value}</p>
    </div>
  )
}

function matchesEmployeeWorkspaceView(
  employee: EmployeeRecord,
  view: EmployeeDirectoryWorkspaceView,
) {
  switch (view) {
    case 'lifecycle':
      return ['probation', 'notice_period', 'inactive', 'terminated'].includes(employee.employment_status)
    case 'onboarding':
      return ['active', 'probation', 'notice_period'].includes(employee.employment_status)
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
  snapshot: AccessSnapshot | null,
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
    latestAuditTimestamp: employee.updated_at ?? employee.created_at,
  }
}

function lifecycleWatchLabel(employee: EmployeeRecord) {
  switch (employee.employment_status) {
    case 'probation':
      return 'Probation review pending'
    case 'notice_period':
      return 'Exit planning active'
    case 'inactive':
      return 'Reactivation or archive review'
    case 'terminated':
      return 'Closed employment record'
    default:
      return 'Open lifecycle workspace'
  }
}

function isExpiringSoon(value: string | null) {
  if (!value) {
    return false
  }

  const expiryDate = new Date(value)
  const warningDate = new Date()
  warningDate.setDate(warningDate.getDate() + 45)

  return expiryDate <= warningDate
}

function formatDate(value: string | null) {
  return formatRegionalDate(value, 'Not available')
}

function formatDateTime(value: string | null) {
  return formatRegionalDateTime(value, 'Not available')
}

function employeeInitials(fullName: string) {
  return fullName
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('')
}

function statusLabel(value: EmployeeStatus) {
  switch (value) {
    case 'notice_period':
      return 'Notice period'
    default:
      return value.charAt(0).toUpperCase() + value.slice(1)
  }
}

function statusVariant(value: EmployeeStatus): 'success' | 'warning' | 'subtle' | 'danger' {
  switch (value) {
    case 'active':
      return 'success'
    case 'probation':
    case 'notice_period':
      return 'warning'
    case 'inactive':
      return 'subtle'
    case 'terminated':
      return 'danger'
    default:
      return 'subtle'
  }
}
