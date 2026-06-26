import { useDeferredValue, useMemo, useState, type ReactNode } from 'react'
import { Link, Navigate } from 'react-router-dom'
import {
  AlertTriangle,
  ArrowUpRight,
  CalendarDays,
  ClipboardList,
  Clock3,
  MoonStar,
  ShieldCheck,
  Users,
} from 'lucide-react'
import { useAccessSnapshot } from '../../access/hooks/useAccessSnapshot'
import {
  applyCommandCenterAlertOverrides,
  useCommandCenterEvents,
} from '../../../app/shell/commandCenterEvents'
import { useShellFavorites } from '../../../app/shell/favorites'
import { getModuleRecentActivity, useShellRecent } from '../../../app/shell/recent'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
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
import {
  ConsoleSearchField,
  ConsoleToolbar,
  ConsoleToolbarRow,
} from '../../../shared/ui/console-table'
import { CardTitle } from '../../../shared/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import {
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceHeader,
  WorkspaceHeroHeader,
  WorkspacePage,
  WorkspaceSummaryRow,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import { useAttendanceAdminWorkspace } from '../hooks/useAttendanceAdminWorkspace'
import { useAttendanceReviewWorkspace } from '../hooks/useAttendanceReviewWorkspace'
import type {
  AttendancePolicy,
  AttendanceCorrection,
  HolidayCalendar,
  AttendanceOperationalRecord,
  Shift,
  ShiftAssignment,
  ShiftRoster,
} from '../types'

type AttendanceOverviewTab = 'assignments' | 'shifts' | 'rosters' | 'review'
const emptyPermissions: string[] = []
const emptyAssignments: ShiftAssignment[] = []
const emptyShifts: Shift[] = []
const emptyRosters: ShiftRoster[] = []
const emptyCalendars: HolidayCalendar[] = []

export function AttendanceOverviewPage() {
  const { snapshot } = useAccessSnapshot()
  const { isFavorite, toggleFavorite } = useShellFavorites()
  const permissions = snapshot?.user.permissions ?? emptyPermissions
  const canManageAny =
    permissions.includes('attendance.edit') ||
    permissions.includes('attendance.manage_shift') ||
    permissions.includes('attendance.manage_roster')
  const canReview = permissions.includes('attendance.approve')

  const adminWorkspace = useAttendanceAdminWorkspace({ enabled: canManageAny })
  const reviewWorkspace = useAttendanceReviewWorkspace(todayDate())
  const { recentItems } = useShellRecent()
  const { activityEvents, alertOverrides } = useCommandCenterEvents('attendance')

  const [activeTabSelection, setActiveTab] = useState<AttendanceOverviewTab>('assignments')
  const [search, setSearch] = useState('')
  const deferredSearch = useDeferredValue(search)

  const overviewTabs = useMemo(() => {
    const tabs: Array<{ id: AttendanceOverviewTab; label: string }> = []

    if (canManageAny) {
      tabs.push(
        { id: 'assignments', label: 'Assignments' },
        { id: 'shifts', label: 'Shifts' },
        { id: 'rosters', label: 'Rosters' },
      )
    }

    if (canReview) {
      tabs.push({ id: 'review', label: 'Review queue' })
    }

    return tabs
  }, [canManageAny, canReview])
  const activeTab = overviewTabs.some((tab) => tab.id === activeTabSelection)
    ? activeTabSelection
    : (overviewTabs[0]?.id ?? 'review')

  const isLoading =
    adminWorkspace.isLoading || (canReview ? reviewWorkspace.isLoading : false)
  const error = adminWorkspace.error ?? (canReview ? reviewWorkspace.error : null)

  const policy: AttendancePolicy | null = adminWorkspace.data?.policy ?? null
  const assignments = adminWorkspace.data?.assignments ?? emptyAssignments
  const shifts = adminWorkspace.data?.shifts ?? emptyShifts
  const rosters = adminWorkspace.data?.rosters ?? emptyRosters
  const calendars = adminWorkspace.data?.holidayCalendars ?? emptyCalendars
  const todaySummary = reviewWorkspace.data.operationalReview.summary
  const exceptionSummary = reviewWorkspace.data.pendingExceptions.summary
  const pendingCorrections = useMemo(
    () => reviewWorkspace.data.corrections.items.filter((item) => item.status === 'pending'),
    [reviewWorkspace.data.corrections.items],
  )
  const expiringAssignments = useMemo(
    () =>
      assignments.filter((record) => {
        if (!record.effective_to) {
          return false
        }

        const days = daysUntil(record.effective_to)
        return days >= 0 && days <= 30
      }),
    [assignments],
  )
  const activeShiftCount = useMemo(
    () => shifts.filter((record) => record.status === 'active').length,
    [shifts],
  )
  const overnightShiftCount = useMemo(
    () => shifts.filter((record) => record.is_overnight).length,
    [shifts],
  )
  const activeAssignmentCount = useMemo(
    () => assignments.filter((record) => record.status === 'active').length,
    [assignments],
  )
  const scheduledRosterCount = useMemo(
    () => rosters.filter((record) => record.status === 'scheduled').length,
    [rosters],
  )
  const overnightRosterCount = useMemo(
    () => rosters.filter((record) => record.shift.is_overnight).length,
    [rosters],
  )
  const holidayCount = useMemo(
    () => calendars.reduce((total, calendar) => total + calendar.holidays.length, 0),
    [calendars],
  )

  const metricCards = useMemo(() => {
    const cards: Array<{
      label: string
      value: string
      delta: string
      icon: ReactNode
      tone: 'neutral' | 'info' | 'success' | 'warning' | 'danger'
    }> = []

    if (canManageAny) {
      cards.push(
        {
          label: 'Active policies',
          value: policy?.status === 'active' ? '1' : '0',
          delta: `${policy?.name ?? 'Policy not loaded'}`,
          icon: <ShieldCheck className="h-4 w-4" />,
          tone: 'info' as const,
        },
        {
          label: 'Active shifts',
          value: String(activeShiftCount),
          delta: `${overnightShiftCount} overnight coverage pattern(s)`,
          icon: <Clock3 className="h-4 w-4" />,
          tone: 'info' as const,
        },
        {
          label: 'Live assignments',
          value: String(activeAssignmentCount),
          delta: `${expiringAssignments.length} expiring in the next 30 days`,
          icon: <Users className="h-4 w-4" />,
          tone: expiringAssignments.length ? 'warning' : 'success',
        },
        {
          label: 'Scheduled rosters',
          value: String(scheduledRosterCount),
          delta: `${rosters.length} roster entries in this workspace`,
          icon: <CalendarDays className="h-4 w-4" />,
          tone: 'success' as const,
        },
      )
    }

    if (canReview) {
      cards.push(
        {
          label: 'Pending reviews',
          value: String(pendingCorrections.length),
          delta: `${exceptionSummary.pending_correction_request_count} linked request(s)`,
          icon: <ClipboardList className="h-4 w-4" />,
          tone: pendingCorrections.length ? 'warning' : 'success',
        },
        {
          label: 'Exception records',
          value: String(exceptionSummary.exception_record_count),
          delta: `${todaySummary.late_count} late · ${todaySummary.incomplete_count} incomplete`,
          icon: <AlertTriangle className="h-4 w-4" />,
          tone: exceptionSummary.exception_record_count ? 'warning' : 'success',
        },
        {
          label: 'Checked out today',
          value: String(todaySummary.checked_out_count),
          delta: `${todaySummary.total_records} records in the active review window`,
          icon: <Clock3 className="h-4 w-4" />,
          tone: 'neutral' as const,
        },
        {
          label: 'Late today',
          value: String(todaySummary.late_count),
          delta: `${todaySummary.absent_count} absent · ${todaySummary.half_day_count} half day`,
          icon: <AlertTriangle className="h-4 w-4" />,
          tone: todaySummary.late_count ? 'warning' : 'success',
        },
      )
    } else if (canManageAny) {
      cards.push({
        label: 'Holiday calendars',
        value: String(calendars.length),
        delta: `${holidayCount} holidays configured`,
        icon: <CalendarDays className="h-4 w-4" />,
        tone: 'neutral' as const,
      })
    }

    return cards.slice(0, 6)
  }, [
    activeAssignmentCount,
    activeShiftCount,
    canManageAny,
    canReview,
    calendars.length,
    exceptionSummary.exception_record_count,
    exceptionSummary.pending_correction_request_count,
    expiringAssignments.length,
    holidayCount,
    pendingCorrections.length,
    overnightShiftCount,
    policy?.name,
    policy?.status,
    rosters.length,
    scheduledRosterCount,
    todaySummary.total_records,
    todaySummary.absent_count,
    todaySummary.checked_out_count,
    todaySummary.half_day_count,
    todaySummary.incomplete_count,
    todaySummary.late_count,
  ])

  const baseAttentionItems = useMemo(() => {
    const items: Array<{
      id: string
      path?: string
      title: string
      detail: string
      meta: string
      tone: 'warning' | 'danger' | 'success' | 'info'
      icon: ReactNode
    }> = []

    if (expiringAssignments.length) {
      const nextExpiry = expiringAssignments
        .slice()
        .sort((left, right) => (left.effective_to ?? '').localeCompare(right.effective_to ?? ''))[0]

      if (nextExpiry?.effective_to) {
        items.push({
          id: 'assignment-expiry',
          path: '/attendance/admin-setup/assignments',
          title: `${labelAssignment(nextExpiry)} expires in ${Math.max(daysUntil(nextExpiry.effective_to), 0)} day(s)`,
          detail: scopeLabel(nextExpiry),
          meta: `Effective through ${formatDate(nextExpiry.effective_to)}`,
          tone: 'warning',
          icon: <AlertTriangle className="h-4 w-4" />,
        })
      }
    }

    if (pendingCorrections.length) {
      items.push({
        id: 'pending-corrections',
        path: '/attendance/operational-review#decisions',
        title: `${pendingCorrections.length} correction request(s) need a decision`,
        detail: `${reviewWorkspace.data.scope === 'tenant' ? 'Tenant-wide' : 'Team'} review window is active for ${formatDate(reviewWorkspace.data.windowDate)}`,
        meta: `${exceptionSummary.pending_correction_request_count} linked request(s) across exception records`,
        tone: pendingCorrections.length > 2 ? 'danger' : 'warning',
        icon: <ClipboardList className="h-4 w-4" />,
      })
    }

    if (exceptionSummary.incomplete_record_count) {
      items.push({
        id: 'incomplete-records',
        path: '/attendance/operational-review#exceptions',
        title: `${exceptionSummary.incomplete_record_count} incomplete attendance day(s) detected`,
        detail: `${todaySummary.checked_in_count} employee(s) are still checked in for the active window`,
        meta: `Review the queue before day-close reconciliation`,
        tone: 'warning',
        icon: <Clock3 className="h-4 w-4" />,
      })
    }

    if (canManageAny && overnightShiftCount > 0) {
      items.push({
        id: 'overnight-coverage',
        path: '/attendance/admin-setup/rosters',
        title: `${overnightShiftCount} overnight shift pattern(s) are active`,
        detail: 'Check rostering and assignment overlap before the next publish cycle.',
        meta: `${overnightRosterCount} upcoming overnight roster(s) scheduled`,
        tone: 'info',
        icon: <MoonStar className="h-4 w-4" />,
      })
    }

    if (!items.length) {
      items.push({
        id: 'healthy',
        path: '/attendance/overview',
        title: 'Attendance operations are stable',
        detail: 'No expiring assignments, blocked reviews, or incomplete attendance days need urgent action.',
        meta: 'Use the workspace table below for planned setup work.',
        tone: 'success',
        icon: <ShieldCheck className="h-4 w-4" />,
      })
    }

    return items.slice(0, 4)
  }, [
    canManageAny,
    exceptionSummary.incomplete_record_count,
    exceptionSummary.pending_correction_request_count,
    expiringAssignments,
    overnightRosterCount,
    overnightShiftCount,
    pendingCorrections.length,
    reviewWorkspace.data.scope,
    reviewWorkspace.data.windowDate,
    todaySummary.checked_in_count,
  ])

  const attentionItems = useMemo(
    () => applyCommandCenterAlertOverrides(baseAttentionItems, alertOverrides),
    [alertOverrides, baseAttentionItems],
  )

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

    if (policy?.updated_at) {
      items.push({
        id: 'policy',
        title: 'Attendance policy updated',
        detail: policy.name,
        meta: relativeTime(policy.updated_at),
        timestamp: policy.updated_at,
        tone: 'info',
      })
    }

    assignments.forEach((record) => {
      items.push({
        id: `assignment-${record.id}`,
        title: `${labelAssignment(record)} assigned`,
        detail: `${record.shift.name} · ${scopeLabel(record)}`,
        meta: relativeTime(record.updated_at ?? record.created_at),
        timestamp: record.updated_at ?? record.created_at,
        tone: 'success',
      })
    })

    rosters.forEach((record) => {
      items.push({
        id: `roster-${record.id}`,
        title: `${record.shift.name} roster scheduled`,
        detail: `${record.employee.full_name} · ${formatDate(record.work_date)}`,
        meta: relativeTime(record.updated_at ?? record.created_at),
        timestamp: record.updated_at ?? record.created_at,
        tone: 'neutral',
      })
    })

    pendingCorrections.slice(0, 4).forEach((correction) => {
      items.push({
        id: `correction-${correction.id}`,
        title: 'Correction submitted for review',
        detail: `${correction.employee.full_name} · ${formatCorrectionStatus(correction.status)}`,
        meta: relativeTime(correction.updated_at ?? correction.created_at),
        timestamp: correction.updated_at ?? correction.created_at,
        tone: 'warning',
      })
    })

    return items
      .filter((item) => item.timestamp)
      .sort((left, right) => (right.timestamp ?? '').localeCompare(left.timestamp ?? ''))
      .slice(0, 6)
  }, [assignments, pendingCorrections, policy, rosters])

  const activityItems = useMemo(() => {
    if (activityEvents.length) {
      return activityEvents
    }

    const recentActivity = getModuleRecentActivity('attendance', recentItems)
    return recentActivity.length ? recentActivity : fallbackActivityItems
  }, [activityEvents, fallbackActivityItems, recentItems])

  const filteredAssignments = useMemo(() => {
    const query = deferredSearch.trim().toLowerCase()

    if (!query) {
      return assignments
    }

    return assignments.filter((record) =>
      [record.shift.name, record.shift.code, scopeLabel(record), record.notes ?? '', record.effective_from, record.effective_to ?? '']
        .join(' ')
        .toLowerCase()
        .includes(query),
    )
  }, [assignments, deferredSearch])

  const filteredShifts = useMemo(() => {
    const query = deferredSearch.trim().toLowerCase()

    if (!query) {
      return shifts
    }

    return shifts.filter((record) =>
      [record.name, record.code, record.description ?? '', record.start_time, record.end_time]
        .join(' ')
        .toLowerCase()
        .includes(query),
    )
  }, [deferredSearch, shifts])

  const filteredRosters = useMemo(() => {
    const query = deferredSearch.trim().toLowerCase()

    if (!query) {
      return rosters
    }

    return rosters.filter((record) =>
      [record.employee.full_name, record.employee.employee_code, record.shift.name, record.shift.code, record.notes ?? '', record.work_date]
        .join(' ')
        .toLowerCase()
        .includes(query),
    )
  }, [deferredSearch, rosters])

  const filteredReviewRows = useMemo(() => {
    const query = deferredSearch.trim().toLowerCase()

    if (!query) {
      return pendingCorrections
    }

    return pendingCorrections.filter((record) =>
      [record.employee.full_name, record.employee.employee_code, record.reason, record.original_values.attendance_date ?? '']
        .join(' ')
        .toLowerCase()
        .includes(query),
    )
  }, [deferredSearch, pendingCorrections])

  const collectionCount =
    activeTab === 'assignments'
      ? filteredAssignments.length
      : activeTab === 'shifts'
        ? filteredShifts.length
        : activeTab === 'rosters'
          ? filteredRosters.length
          : filteredReviewRows.length

  if (!canManageAny && !canReview) {
    return <Navigate replace to="/attendance/my-attendance/history" />
  }

  return (
    <WorkspacePage>
      {isLoading ? <p className="workspace-muted">Loading attendance operations center...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}

      <WorkspaceSurface>
        <WorkspaceHeroHeader
          moduleLabel="Attendance"
          title="Attendance Operations Center"
          description="Monitor assignment coverage, review live exceptions, and move directly into scheduling or decision work."
          context={[canManageAny ? 'Scheduling controls live' : 'Operational visibility', canReview ? 'Review queue active' : 'Monitoring only']}
          actions={
            <>
              {canReview ? (
                <Button asChild size="xs" variant="secondary">
                  <Link to="/attendance/operational-review">Open review queue</Link>
                </Button>
              ) : null}
              {canManageAny ? (
                <Button asChild size="xs" variant="primary">
                  <Link to="/attendance/admin-setup/assignments">New assignment</Link>
                </Button>
              ) : null}
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
              />
            ))}
          </CommandCenterMetricGrid>

          <CommandCenterLayout>
            <CommandCenterMain>
              <CommandCenterAttentionStrip
                title="Needs attention"
                action={
                  canReview ? (
                    <Button asChild size="xs" variant="ghost">
                      <Link to="/attendance/operational-review">View review workspace</Link>
                    </Button>
                  ) : undefined
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
                              icon: 'attendance',
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
                    <CardTitle>Operations workspace</CardTitle>
                  </div>
                  <Badge variant="subtle">{collectionCount} record(s) in view</Badge>
                </WorkspaceHeader>
                <WorkspaceContent className="space-y-4">
                  <ConsoleToolbar>
                    <ConsoleToolbarRow>
                      <WorkspaceTabs role="tablist" aria-label="Attendance operations collections">
                        {overviewTabs.map((tab) => (
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
                          {activeTab === 'review' ? 'Decision queue' : 'Setup registry'}
                        </Badge>
                        <Button size="xs" variant="secondary" onClick={() => setSearch('')} disabled={!search.length}>
                          Clear search
                        </Button>
                      </div>
                    </ConsoleToolbarRow>
                    <ConsoleToolbarRow>
                      <ConsoleSearchField
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder={
                          activeTab === 'assignments'
                            ? 'Search shifts, scopes, notes, or dates'
                            : activeTab === 'shifts'
                              ? 'Search shift code, name, or schedule'
                              : activeTab === 'rosters'
                                ? 'Search employee, shift, or work date'
                                : 'Search employee, reason, or attendance date'
                        }
                        aria-label="Search attendance operations"
                        className="max-w-2xl"
                      />
                    </ConsoleToolbarRow>
                  </ConsoleToolbar>

                  {activeTab === 'assignments' ? (
                    filteredAssignments.length ? (
                      <WorkspaceTableShell>
                        <Table>
                          <TableHeader>
                            <TableRow>
                              <TableHead>Assignment</TableHead>
                              <TableHead>Scope</TableHead>
                              <TableHead>Coverage</TableHead>
                              <TableHead>Health</TableHead>
                              <TableHead>Effective</TableHead>
                              <TableHead className="w-[132px] text-right">Action</TableHead>
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {filteredAssignments.map((record) => (
                              <TableRow key={record.id}>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">{record.shift.name}</span>
                                    <span className="ui-table-secondary">{record.shift.code}</span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">{scopeLabel(record)}</span>
                                    <span className="ui-table-secondary">{record.assignment_type} scope</span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">{record.notes ?? 'Effective-dated shift coverage'}</span>
                                    <span className="ui-table-secondary">
                                      {record.effective_to
                                        ? `Runs through ${formatDate(record.effective_to)}`
                                        : 'No end date configured'}
                                    </span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <Badge variant={assignmentHealthVariant(record)}>
                                    {assignmentHealthLabel(record)}
                                  </Badge>
                                </TableCell>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">{formatDate(record.effective_from)}</span>
                                    <span className="ui-table-secondary">
                                      {record.effective_to ? formatDate(record.effective_to) : 'Open-ended'}
                                    </span>
                                  </div>
                                </TableCell>
                                <TableCell className="ui-table-action-cell align-top text-right">
                                  <Button asChild size="sm" variant="secondary">
                                    <Link to="/attendance/admin-setup/assignments">View details</Link>
                                  </Button>
                                </TableCell>
                              </TableRow>
                            ))}
                          </TableBody>
                        </Table>
                      </WorkspaceTableShell>
                    ) : (
                      <WorkspaceEmptyState
                        title="No assignments match the current search"
                        copy="Clear the search or open the assignment registry to widen the active view."
                      />
                    )
                  ) : null}

                  {activeTab === 'shifts' ? (
                    filteredShifts.length ? (
                      <WorkspaceTableShell>
                        <Table>
                          <TableHeader>
                            <TableRow>
                              <TableHead>Shift</TableHead>
                              <TableHead>Schedule</TableHead>
                              <TableHead>Rules</TableHead>
                              <TableHead>Status</TableHead>
                              <TableHead className="w-[132px] text-right">Action</TableHead>
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {filteredShifts.map((record) => (
                              <TableRow key={record.id}>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">{record.name}</span>
                                    <span className="ui-table-secondary">{record.code}</span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">
                                      {record.start_time} - {record.end_time}
                                    </span>
                                    <span className="ui-table-secondary">
                                      {record.is_overnight ? 'Overnight coverage' : 'Same-day coverage'}
                                    </span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">
                                      {formatMinutes(record.working_hours_minutes)} working window
                                    </span>
                                    <span className="ui-table-secondary">
                                      {formatMinutes(record.break_duration_minutes)} break · {formatMinutes(record.grace_minutes)} grace
                                    </span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <Badge variant={record.status === 'active' ? 'success' : 'subtle'}>
                                    {record.status === 'active' ? 'Active' : 'Inactive'}
                                  </Badge>
                                </TableCell>
                                <TableCell className="ui-table-action-cell align-top text-right">
                                  <Button asChild size="sm" variant="secondary">
                                    <Link to="/attendance/admin-setup/shifts">View details</Link>
                                  </Button>
                                </TableCell>
                              </TableRow>
                            ))}
                          </TableBody>
                        </Table>
                      </WorkspaceTableShell>
                    ) : (
                      <WorkspaceEmptyState
                        title="No shifts match the current search"
                        copy="Clear the search or open the shift registry to review the full setup catalog."
                      />
                    )
                  ) : null}

                  {activeTab === 'rosters' ? (
                    filteredRosters.length ? (
                      <WorkspaceTableShell>
                        <Table>
                          <TableHeader>
                            <TableRow>
                              <TableHead>Employee</TableHead>
                              <TableHead>Shift</TableHead>
                              <TableHead>Work date</TableHead>
                              <TableHead>Status</TableHead>
                              <TableHead className="w-[132px] text-right">Action</TableHead>
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {filteredRosters.map((record) => (
                              <TableRow key={record.id}>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">{record.employee.full_name}</span>
                                    <span className="ui-table-secondary">{record.employee.employee_code}</span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">{record.shift.name}</span>
                                    <span className="ui-table-secondary">{record.shift.code}</span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">{formatDate(record.work_date)}</span>
                                    <span className="ui-table-secondary">{record.notes ?? 'No notes'}</span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <Badge variant={record.status === 'scheduled' ? 'success' : 'subtle'}>
                                    {record.status === 'scheduled' ? 'Scheduled' : 'Cancelled'}
                                  </Badge>
                                </TableCell>
                                <TableCell className="ui-table-action-cell align-top text-right">
                                  <Button asChild size="sm" variant="secondary">
                                    <Link to="/attendance/admin-setup/rosters">View details</Link>
                                  </Button>
                                </TableCell>
                              </TableRow>
                            ))}
                          </TableBody>
                        </Table>
                      </WorkspaceTableShell>
                    ) : (
                      <WorkspaceEmptyState
                        title="No rosters match the current search"
                        copy="Clear the search or open roster scheduling to widen the visible coverage window."
                      />
                    )
                  ) : null}

                  {activeTab === 'review' ? (
                    filteredReviewRows.length ? (
                      <WorkspaceTableShell>
                        <Table>
                          <TableHeader>
                            <TableRow>
                              <TableHead>Employee</TableHead>
                              <TableHead>Issue</TableHead>
                              <TableHead>Window</TableHead>
                              <TableHead>Status</TableHead>
                              <TableHead className="w-[132px] text-right">Action</TableHead>
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {filteredReviewRows.map((record) => (
                              <TableRow key={record.id}>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">{record.employee.full_name}</span>
                                    <span className="ui-table-secondary">{record.employee.employee_code}</span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">{record.reason}</span>
                                    <span className="ui-table-secondary">
                                      {record.corrected_values.primary_status
                                        ? `Requested status ${formatPrimaryStatus(record.corrected_values.primary_status)}`
                                        : 'Timestamp correction requested'}
                                    </span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <div className="ui-table-stack">
                                    <span className="ui-table-primary">
                                      {formatDate(record.original_values.attendance_date ?? '')}
                                    </span>
                                    <span className="ui-table-secondary">
                                      {record.corrected_values.check_in_at
                                        ? `Check-in ${formatTime(record.corrected_values.check_in_at)}`
                                        : 'Check-in unchanged'}
                                      {' · '}
                                      {record.corrected_values.check_out_at
                                        ? `Check-out ${formatTime(record.corrected_values.check_out_at)}`
                                        : 'Check-out unchanged'}
                                    </span>
                                  </div>
                                </TableCell>
                                <TableCell className="align-top">
                                  <Badge variant={correctionStatusVariant(record.status)}>
                                    {formatCorrectionStatus(record.status)}
                                  </Badge>
                                </TableCell>
                                <TableCell className="ui-table-action-cell align-top text-right">
                                  <Button asChild size="sm" variant="secondary">
                                    <Link to="/attendance/operational-review">Review</Link>
                                  </Button>
                                </TableCell>
                              </TableRow>
                            ))}
                          </TableBody>
                        </Table>
                      </WorkspaceTableShell>
                    ) : (
                      <WorkspaceEmptyState
                        title="No review items match the current search"
                        copy="Clear the search or open the full operational review queue to inspect a broader window."
                      />
                    )
                  ) : null}
                </WorkspaceContent>
              </WorkspaceSurface>

              <CommandCenterInsightGrid>
                <CommandCenterInsightCard
                  title="Assignment footprint"
                  description="Track how scheduling logic is being applied across direct employees, departments, and locations."
                >
                  <WorkspaceSummaryRow label="Employee assignments" value={assignments.filter((record) => record.assignment_type === 'employee').length} />
                  <WorkspaceSummaryRow label="Department assignments" value={assignments.filter((record) => record.assignment_type === 'department').length} />
                  <WorkspaceSummaryRow label="Location assignments" value={assignments.filter((record) => record.assignment_type === 'location').length} />
                  <WorkspaceSummaryRow label="Expiring soon" value={expiringAssignments.length} />
                </CommandCenterInsightCard>
                <CommandCenterInsightCard
                  title="Shift footprint"
                  description="Use this to keep overnight patterns and holiday calendar coverage visible before publish cycles."
                >
                  <WorkspaceSummaryRow label="Overnight shifts" value={shifts.filter((record) => record.is_overnight).length} />
                  <WorkspaceSummaryRow label="Average working window" value={averageMinutes(shifts.map((record) => record.working_hours_minutes))} />
                  <WorkspaceSummaryRow label="Holiday calendars" value={calendars.length} />
                  <WorkspaceSummaryRow label="Configured holidays" value={calendars.reduce((total, calendar) => total + calendar.holidays.length, 0)} />
                </CommandCenterInsightCard>
                <CommandCenterInsightCard
                  title="Review pressure"
                  description="Keep correction demand and exception types visible before the end-of-day review pass."
                >
                  <WorkspaceSummaryRow label="Pending corrections" value={pendingCorrections.length} />
                  <WorkspaceSummaryRow label="Late records" value={todaySummary.late_count} />
                  <WorkspaceSummaryRow label="Incomplete records" value={exceptionSummary.incomplete_record_count} />
                  <WorkspaceSummaryRow label="Half days" value={exceptionSummary.half_day_record_count} />
                </CommandCenterInsightCard>
              </CommandCenterInsightGrid>
            </CommandCenterMain>

            <CommandCenterRail>
              <CommandCenterPanel
                title="Recent activity"
                actions={
                  canManageAny ? (
                    <Button asChild size="sm" variant="ghost">
                      <Link to="/attendance/admin-setup/assignments">Open setup</Link>
                    </Button>
                  ) : undefined
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
                                icon: 'attendance',
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

function todayDate() {
  return new Date().toISOString().slice(0, 10)
}

function formatDate(value: string) {
  if (!value) {
    return 'Not scheduled'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

function formatTime(value: string) {
  return new Intl.DateTimeFormat('en-IN', {
    hour: 'numeric',
    minute: '2-digit',
  }).format(new Date(value))
}

function formatMinutes(value: number) {
  if (value >= 60) {
    const hours = Math.floor(value / 60)
    const minutes = value % 60
    return minutes ? `${hours}h ${minutes}m` : `${hours}h`
  }

  return `${value}m`
}

function averageMinutes(values: number[]) {
  if (!values.length) {
    return '0m'
  }

  const total = values.reduce((sum, value) => sum + value, 0)
  return formatMinutes(Math.round(total / values.length))
}

function daysUntil(value: string) {
  const target = new Date(value)
  const today = new Date()
  target.setHours(0, 0, 0, 0)
  today.setHours(0, 0, 0, 0)

  return Math.round((target.getTime() - today.getTime()) / (1000 * 60 * 60 * 24))
}

function relativeTime(value: string | null) {
  if (!value) {
    return 'No activity time'
  }

  const now = Date.now()
  const then = new Date(value).getTime()
  const diffHours = Math.max(1, Math.round((now - then) / (1000 * 60 * 60)))

  return diffHours < 24 ? `${diffHours}h ago` : `${Math.round(diffHours / 24)}d ago`
}

function scopeLabel(record: ShiftAssignment) {
  if (record.employee) {
    return record.employee.full_name
  }

  if (record.department) {
    return record.department.name
  }

  if (record.location) {
    return record.location.name
  }

  return 'Unscoped assignment'
}

function labelAssignment(record: ShiftAssignment) {
  if (record.employee) {
    return record.employee.full_name
  }

  if (record.department) {
    return `${record.department.name} assignment`
  }

  if (record.location) {
    return `${record.location.name} assignment`
  }

  return record.shift.name
}

function assignmentHealthVariant(record: ShiftAssignment) {
  if (record.status !== 'active') {
    return 'subtle' as const
  }

  if (record.effective_to) {
    const days = daysUntil(record.effective_to)

    if (days <= 7) {
      return 'danger' as const
    }

    if (days <= 30) {
      return 'warning' as const
    }
  }

  return 'success' as const
}

function assignmentHealthLabel(record: ShiftAssignment) {
  if (record.status !== 'active') {
    return 'Inactive'
  }

  if (record.effective_to) {
    const days = daysUntil(record.effective_to)

    if (days <= 7) {
      return 'Requires action'
    }

    if (days <= 30) {
      return 'Expiring soon'
    }
  }

  return 'Healthy'
}

function correctionStatusVariant(status: AttendanceCorrection['status']) {
  switch (status) {
    case 'approved':
      return 'success'
    case 'pending':
      return 'warning'
    case 'changes_requested':
      return 'info'
    case 'rejected':
      return 'danger'
    default:
      return 'subtle'
  }
}

function formatCorrectionStatus(status: AttendanceCorrection['status']) {
  switch (status) {
    case 'approved':
      return 'Approved'
    case 'changes_requested':
      return 'Changes requested'
    case 'rejected':
      return 'Rejected'
    case 'pending':
    default:
      return 'Pending'
  }
}

function formatPrimaryStatus(status: AttendanceOperationalRecord['calculation']['primary_status']) {
  switch (status) {
    case 'present':
      return 'Present'
    case 'half_day':
      return 'Half day'
    case 'absent':
      return 'Absent'
    case 'holiday':
      return 'Holiday'
    case 'weekend':
      return 'Weekend'
    case 'incomplete':
      return 'Incomplete'
    default:
      return 'Pending'
  }
}
