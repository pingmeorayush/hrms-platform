import { useDeferredValue, useMemo, useState, type ReactNode } from 'react'
import { Link, Navigate } from 'react-router-dom'
import {
  AlertTriangle,
  ArrowUpRight,
  BadgeCheck,
  CalendarDays,
  Clock3,
  FileWarning,
  ShieldCheck,
  TimerReset,
  Users,
} from 'lucide-react'
import {
  applyCommandCenterAlertOverrides,
  useCommandCenterEvents,
} from '../../../app/shell/commandCenterEvents'
import { useShellFavorites } from '../../../app/shell/favorites'
import { getModuleRecentActivity, useShellRecent } from '../../../app/shell/recent'
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
import type {
  LeavePolicyRecord,
  LeaveRequestRecord,
} from '../types'
import { useLeaveRouteWorkspace } from './useLeaveRouteWorkspace'
import { formatDate, formatRequestStatus, requestBadgeVariant } from '../components/leaveWorkspaceUtils'

type LeaveOverviewTab = 'requests' | 'approvals' | 'policies' | 'calendar'
type MetricCardTone = 'neutral' | 'info' | 'success' | 'warning' | 'danger'
const emptyPolicies: LeavePolicyRecord[] = []

export function LeaveOverviewPage() {
  const workspace = useLeaveRouteWorkspace()
  const { isFavorite, toggleFavorite } = useShellFavorites()
  const { recentItems } = useShellRecent()
  const { activityEvents, alertOverrides } = useCommandCenterEvents('leave')
  const {
    data,
    canApproveLeave,
    canManagePolicy,
    reviewScope,
    scopedReviewRequests,
    pendingReviewRequests,
    reviewCalendarEntries,
    source,
    isLoading,
    error,
  } = workspace
  const [activeTabSelection, setActiveTab] = useState<LeaveOverviewTab>('requests')
  const [search, setSearch] = useState('')
  const deferredSearch = useDeferredValue(search)

  const canViewOverview = canApproveLeave || canManagePolicy

  const availableTabs = useMemo(
    () =>
      [
        { id: 'requests', label: 'Requests' },
        { id: 'approvals', label: 'Approvals' },
        ...(canManagePolicy ? [{ id: 'policies', label: 'Policies' }] : []),
        { id: 'calendar', label: 'Calendar' },
      ] as Array<{ id: LeaveOverviewTab; label: string }>,
    [canManagePolicy],
  )

  const activeTab = availableTabs.some((tab) => tab.id === activeTabSelection)
    ? activeTabSelection
    : (availableTabs[0]?.id ?? 'requests')
  const requests = scopedReviewRequests
  const approvedUpcomingRequests = useMemo(
    () => requests.filter((request) => request.status === 'approved' && daysUntil(request.start_date) >= 0),
    [requests],
  )
  const activePolicies = useMemo(
    () => (data?.policies ?? emptyPolicies).filter((record) => record.status === 'active'),
    [data?.policies],
  )
  const activeLeaveTypes = useMemo(
    () => (data?.leaveTypes ?? []).filter((record) => record.status === 'active'),
    [data?.leaveTypes],
  )
  const lowBalanceRecords = useMemo(
    () =>
      (data?.balances ?? []).filter(
        (record) =>
          record.leave_type.status === 'active' &&
          record.available_days > 0 &&
          record.available_days <= 2,
      ),
    [data?.balances],
  )
  const distinctUpcomingEmployees = new Set(approvedUpcomingRequests.map((request) => request.employee.id)).size
  const calendarWindows = reviewCalendarEntries.length
  const pendingChangesRequested = requests.filter((request) => request.status === 'changes_requested').length

  const metricCards: Array<{
    label: string
    value: string
    delta: string
    icon: ReactNode
    tone: MetricCardTone
  }> = [
    {
      label: 'Pending approvals',
      value: String(pendingReviewRequests.length),
      delta: `${reviewScope === 'tenant' ? 'Tenant-wide' : 'Team'} review scope active`,
      icon: <Clock3 className="h-4 w-4" />,
      tone: pendingReviewRequests.length ? 'warning' : 'success',
    },
    {
      label: 'Upcoming approved leave',
      value: String(approvedUpcomingRequests.length),
      delta: `${distinctUpcomingEmployees} employee(s) scheduled for time away`,
      icon: <CalendarDays className="h-4 w-4" />,
      tone: approvedUpcomingRequests.length ? 'info' : 'neutral',
    },
    {
      label: 'Active policies',
      value: String(activePolicies.length),
      delta: `${activeLeaveTypes.length} live leave type(s) available`,
      icon: <ShieldCheck className="h-4 w-4" />,
      tone: activePolicies.length ? 'success' : 'warning',
    },
    {
      label: 'Calendar windows',
      value: String(calendarWindows),
      delta: `${reviewCalendarEntries.filter((request) => request.status === 'pending').length} still pending`,
      icon: <Users className="h-4 w-4" />,
      tone: calendarWindows ? 'info' : 'neutral',
    },
    {
      label: 'Low balances',
      value: String(lowBalanceRecords.length),
      delta: lowBalanceRecords.length ? 'Employees may need policy review soon' : 'No low-balance alerts detected',
      icon: <TimerReset className="h-4 w-4" />,
      tone: lowBalanceRecords.length ? 'warning' : 'success',
    },
    {
      label: 'Changes requested',
      value: String(pendingChangesRequested),
      delta: source === 'demo' ? 'Follow-up requests returned to employees' : 'Returned requests in the active workspace',
      icon: <FileWarning className="h-4 w-4" />,
      tone: pendingChangesRequested ? 'warning' : 'neutral',
    },
  ]

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

    const firstPending = pendingReviewRequests[0]
    if (firstPending) {
      items.push({
        id: 'pending-review',
        path: '/leave/approvals',
        title: `${pendingReviewRequests.length} request(s) need a decision`,
        detail: `${firstPending.employee.full_name} requested ${firstPending.leave_type.name} from ${formatDate(firstPending.start_date)}`,
        meta: 'Open the approvals queue to keep balance and calendar coverage aligned.',
        tone: pendingReviewRequests.length > 2 ? 'danger' : 'warning',
        icon: <Clock3 className="h-4 w-4" />,
      })
    }

    const nextApproved = approvedUpcomingRequests
      .slice()
      .sort((left, right) => left.start_date.localeCompare(right.start_date))[0]
    if (nextApproved) {
      items.push({
        id: 'upcoming-leave',
        path: '/leave/requests',
        title: `${nextApproved.employee.full_name} starts leave in ${Math.max(daysUntil(nextApproved.start_date), 0)} day(s)`,
        detail: `${nextApproved.leave_type.name} · ${formatDate(nextApproved.start_date)} to ${formatDate(nextApproved.end_date)}`,
        meta: `${nextApproved.department.name}${nextApproved.location ? ` · ${nextApproved.location.name}` : ''}`,
        tone: 'info',
        icon: <CalendarDays className="h-4 w-4" />,
      })
    }

    const lowBalanceRecord = lowBalanceRecords[0]
    if (lowBalanceRecord) {
      items.push({
        id: 'low-balance',
        path: '/leave/requests',
        title: `${lowBalanceRecord.employee.full_name} is running low on ${lowBalanceRecord.leave_type.name}`,
        detail: `${lowBalanceRecord.available_days} day(s) available with ${lowBalanceRecord.booked_days} already booked`,
        meta: 'Review balances or policy carry-forward before the next request cycle.',
        tone: 'warning',
        icon: <TimerReset className="h-4 w-4" />,
      })
    }

    const rejectedRequest = requests.find((request) => request.status === 'rejected')
    if (rejectedRequest) {
      items.push({
        id: 'rejection',
        path: '/leave/requests',
        title: `${rejectedRequest.employee.full_name} has a rejected request on record`,
        detail: rejectedRequest.approver_comment ?? rejectedRequest.reason,
        meta: 'Use Requests to follow up if policy or payroll timing needs clarification.',
        tone: 'info',
        icon: <AlertTriangle className="h-4 w-4" />,
      })
    }

    if (!items.length) {
      items.push({
        id: 'healthy',
        path: '/leave/overview',
        title: 'Leave operations look healthy',
        detail: 'No urgent approval backlog, balance warnings, or immediate leave conflicts were detected.',
        meta: 'Use the workspace tables below for planned leave operations.',
        tone: 'success',
        icon: <BadgeCheck className="h-4 w-4" />,
      })
    }

    return items.slice(0, 4)
  }, [approvedUpcomingRequests, lowBalanceRecords, pendingReviewRequests, requests])

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

    requests.forEach((request) => {
      items.push({
        id: `request-${request.id}`,
        title: `${request.employee.full_name} · ${formatRequestStatus(request.status)}`,
        detail: `${request.leave_type.name} · ${formatDate(request.start_date)} to ${formatDate(request.end_date)}`,
        meta: relativeTime(request.updated_at ?? request.created_at),
        timestamp: request.updated_at ?? request.created_at,
        tone:
          request.status === 'approved'
            ? 'success'
            : request.status === 'pending'
              ? 'warning'
              : 'neutral',
      })
    })

    ;(data?.policies ?? emptyPolicies).forEach((policy) => {
      items.push({
        id: `policy-${policy.id}`,
        title: `${policy.leave_type.name} policy updated`,
        detail: `${policy.annual_allowance_days} day(s) annual allowance · ${policy.accrual_frequency} accrual`,
        meta: relativeTime(policy.updated_at ?? policy.created_at),
        timestamp: policy.updated_at ?? policy.created_at,
        tone: 'info',
      })
    })

    return items
      .filter((item) => item.timestamp)
      .sort((left, right) => (right.timestamp ?? '').localeCompare(left.timestamp ?? ''))
      .slice(0, 6)
  }, [data?.policies, requests])

  const activityItems = useMemo(() => {
    if (activityEvents.length) {
      return activityEvents
    }

    const recentActivity = getModuleRecentActivity('leave', recentItems)
    return recentActivity.length ? recentActivity : fallbackActivityItems
  }, [activityEvents, fallbackActivityItems, recentItems])

  const filteredRequests = useMemo(
    () => filterLeaveRequests(requests, deferredSearch),
    [deferredSearch, requests],
  )
  const filteredApprovals = useMemo(
    () => filterLeaveRequests(pendingReviewRequests, deferredSearch),
    [deferredSearch, pendingReviewRequests],
  )
  const filteredPolicies = useMemo(() => {
    const query = deferredSearch.trim().toLowerCase()
    if (!query) {
      return data?.policies ?? emptyPolicies
    }

    return (data?.policies ?? emptyPolicies).filter((record) =>
      [
        record.leave_type.name,
        record.leave_type.code,
        record.accrual_frequency,
        record.applicable_department?.name ?? '',
        record.applicable_location?.name ?? '',
      ]
        .join(' ')
        .toLowerCase()
        .includes(query),
    )
  }, [data?.policies, deferredSearch])
  const filteredCalendar = useMemo(
    () => filterLeaveRequests(reviewCalendarEntries, deferredSearch),
    [deferredSearch, reviewCalendarEntries],
  )

  const collectionCount =
    activeTab === 'requests'
      ? filteredRequests.length
      : activeTab === 'approvals'
        ? filteredApprovals.length
        : activeTab === 'policies'
          ? filteredPolicies.length
          : filteredCalendar.length

  if (!canViewOverview) {
    return <Navigate replace to="/leave/requests" />
  }

  return (
    <WorkspacePage>
      {isLoading ? <p className="workspace-muted">Loading leave operations center...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}
      {!data && !isLoading && !error ? (
        <p className="workspace-muted">No leave workspace is available yet.</p>
      ) : null}

      {data ? (
        <WorkspaceSurface>
          <WorkspaceHeroHeader
            moduleLabel="Leave"
            title="Leave Operations Center"
            description="Monitor approval load, upcoming leave coverage, policy posture, and move directly into leave workflows."
            badge={<Badge variant={source === 'live' ? 'info' : 'warning'}>{source === 'live' ? 'Live contract' : 'Demo contract'}</Badge>}
            context={[canApproveLeave ? `Review scope: ${reviewScope}` : 'Monitoring workspace', canManagePolicy ? 'Policy controls live' : 'Requests workspace']}
            actions={
              <>
                <Button asChild size="xs" variant="secondary">
                  <Link to="/leave/approvals">Open approvals</Link>
                </Button>
                <Button asChild size="xs" variant="primary">
                  <Link to={canManagePolicy ? '/leave/policy-admin' : '/leave/requests'}>
                    {canManagePolicy ? 'Open policy admin' : 'Open requests'}
                  </Link>
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
                />
              ))}
            </CommandCenterMetricGrid>

            <CommandCenterLayout>
              <CommandCenterMain>
                <CommandCenterAttentionStrip
                  title="Needs attention"
                  action={
                    <Button asChild size="xs" variant="ghost">
                      <Link to="/leave/approvals">View approval queue</Link>
                    </Button>
                  }
                >
                  {attentionItems.map((item) => (
                    <CommandCenterAttentionItem
                      key={item.id}
                      title={item.title}
                      detail={item.detail}
                      meta={item.meta}
                      tone={item.tone}
                      icon={item.icon}
                      to={item.path}
                      pinned={item.path ? isFavorite(item.path) : false}
                      onTogglePinned={
                        item.path
                          ? () =>
                              toggleFavorite({
                                path: item.path!,
                                label: item.title,
                                icon: 'leave',
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
                      <CardTitle>Leave workspace</CardTitle>
                    </div>
                    <Badge variant="subtle">{collectionCount} record(s) in view</Badge>
                  </WorkspaceHeader>
                  <WorkspaceContent className="space-y-4">
                    <ConsoleToolbar>
                      <ConsoleToolbarRow>
                        <WorkspaceTabs role="tablist" aria-label="Leave operations collections">
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
                            {activeTab === 'approvals'
                              ? 'Decision queue'
                              : activeTab === 'policies'
                                ? 'Policy controls'
                                : activeTab === 'calendar'
                                  ? 'Coverage calendar'
                                  : 'Leave requests'}
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
                            activeTab === 'requests'
                              ? 'Search employee, leave type, department, location, or reason'
                              : activeTab === 'approvals'
                                ? 'Search pending employee, leave type, department, or reason'
                                : activeTab === 'policies'
                                  ? 'Search leave type, frequency, department, or location'
                                  : 'Search calendar employee, leave type, department, or status'
                          }
                          aria-label="Search leave operations"
                          className="max-w-2xl"
                        />
                      </ConsoleToolbarRow>
                    </ConsoleToolbar>

                    {!collectionCount ? (
                      <WorkspaceEmptyState
                        title="No leave records match the current view"
                        copy="Adjust the search or switch collections to widen the visible leave workspace."
                      />
                    ) : (
                      <WorkspaceTableShell>
                        {activeTab === 'requests' ? renderLeaveRequestsTable(filteredRequests) : null}
                        {activeTab === 'approvals' ? renderLeaveApprovalsTable(filteredApprovals) : null}
                        {activeTab === 'policies' ? renderLeavePoliciesTable(filteredPolicies) : null}
                        {activeTab === 'calendar' ? renderLeaveCalendarTable(filteredCalendar) : null}
                      </WorkspaceTableShell>
                    )}
                  </WorkspaceContent>
                </WorkspaceSurface>

                <CommandCenterInsightGrid>
                  <CommandCenterInsightCard
                    title="Request posture"
                    description="Track the main workflow states that define current leave load and review pressure."
                  >
                    <WorkspaceSummaryRow label="Pending" value={requests.filter((request) => request.status === 'pending').length} />
                    <WorkspaceSummaryRow label="Approved" value={requests.filter((request) => request.status === 'approved').length} />
                    <WorkspaceSummaryRow label="Rejected" value={requests.filter((request) => request.status === 'rejected').length} />
                    <WorkspaceSummaryRow label="Cancelled" value={requests.filter((request) => request.status === 'cancelled').length} />
                  </CommandCenterInsightCard>
                  <CommandCenterInsightCard
                    title="Policy coverage"
                    description="Keep leave types and policy rules aligned before request volume creates friction."
                  >
                    <WorkspaceSummaryRow label="Live leave types" value={activeLeaveTypes.length} />
                    <WorkspaceSummaryRow label="Active policies" value={activePolicies.length} />
                    <WorkspaceSummaryRow label="Policy-managed departments" value={new Set(activePolicies.map((policy) => policy.applicable_department?.id).filter(Boolean)).size} />
                    <WorkspaceSummaryRow label="Policy-managed locations" value={new Set(activePolicies.map((policy) => policy.applicable_location?.id).filter(Boolean)).size} />
                  </CommandCenterInsightCard>
                  <CommandCenterInsightCard
                    title="Balance and coverage"
                    description="Use low-balance alerts and upcoming approved leave to stay ahead of coverage gaps."
                  >
                    <WorkspaceSummaryRow label="Low balance alerts" value={lowBalanceRecords.length} />
                    <WorkspaceSummaryRow label="Upcoming approved leave" value={approvedUpcomingRequests.length} />
                    <WorkspaceSummaryRow label="Employees away soon" value={distinctUpcomingEmployees} />
                    <WorkspaceSummaryRow label="Calendar windows" value={calendarWindows} />
                  </CommandCenterInsightCard>
                </CommandCenterInsightGrid>
              </CommandCenterMain>

              <CommandCenterRail>
              <CommandCenterPanel
                title="Recent activity"
                actions={
                    <Button asChild size="xs" variant="ghost">
                      <Link to={canManagePolicy ? '/leave/policy-admin' : '/leave/requests'}>
                        Open {canManagePolicy ? 'policy admin' : 'requests'}
                      </Link>
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
                                  icon: 'leave',
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
      ) : null}
    </WorkspacePage>
  )
}

function renderLeaveRequestsTable(requests: LeaveRequestRecord[]) {
  return (
    <Table>
      <TableHeader className="bg-panel-soft/55">
        <TableRow>
          <TableHead>Employee</TableHead>
          <TableHead>Leave window</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Commentary</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {requests.map((request) => (
          <TableRow key={request.id}>
            <TableHead scope="row" className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{request.employee.full_name}</span>
                <span className="ui-table-secondary">
                  {request.leave_type.name} · {request.department.name}
                </span>
              </div>
            </TableHead>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">
                  {formatDate(request.start_date)} to {formatDate(request.end_date)}
                </span>
                <span className="ui-table-secondary">
                  {request.total_days} day(s){request.location ? ` · ${request.location.name}` : ''}
                </span>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <Badge variant={requestBadgeVariant(request.status)}>{formatRequestStatus(request.status)}</Badge>
            </TableCell>
            <TableCell className="align-top">
              <span className="ui-table-body-muted">
                {request.approver_comment ?? request.reason}
              </span>
            </TableCell>
            <TableCell className="ui-table-action-cell align-top text-right">
              <Button asChild size="sm" variant="secondary">
                <Link to="/leave/requests">Open requests</Link>
              </Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function renderLeaveApprovalsTable(requests: LeaveRequestRecord[]) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Employee</TableHead>
          <TableHead>Leave window</TableHead>
          <TableHead>Department</TableHead>
          <TableHead>Reason</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {requests.map((request) => (
          <TableRow key={request.id}>
            <TableHead scope="row" className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{request.employee.full_name}</span>
                <span className="ui-table-secondary">
                  {request.leave_type.name} · {request.employee.employee_code}
                </span>
              </div>
            </TableHead>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">
                  {formatDate(request.start_date)} to {formatDate(request.end_date)}
                </span>
                <span className="ui-table-secondary">{request.total_days} day(s)</span>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{request.department.name}</span>
                <span className="ui-table-secondary">{request.location?.name ?? 'Location unassigned'}</span>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <span className="ui-table-body-muted">{request.reason}</span>
            </TableCell>
            <TableCell className="ui-table-action-cell align-top text-right">
              <Button asChild size="sm" variant="secondary">
                <Link to="/leave/approvals">Review</Link>
              </Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function renderLeavePoliciesTable(policies: LeavePolicyRecord[]) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Policy</TableHead>
          <TableHead>Allowance</TableHead>
          <TableHead>Applicability</TableHead>
          <TableHead>Status</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {policies.map((policy) => (
          <TableRow key={policy.id}>
            <TableHead scope="row" className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{policy.leave_type.name}</span>
                <span className="ui-table-secondary">
                  {policy.accrual_frequency} accrual · {policy.min_notice_days} notice day(s)
                </span>
              </div>
            </TableHead>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{policy.annual_allowance_days} day(s) annual allowance</span>
                <span className="ui-table-secondary">
                  {policy.carry_forward_limit_days} carry · {policy.encashment_limit_days} encashment
                </span>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{policy.applicable_department?.name ?? 'All departments'}</span>
                <span className="ui-table-secondary">{policy.applicable_location?.name ?? 'All locations'}</span>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <Badge variant={policy.status === 'active' ? 'success' : 'subtle'}>{policy.status}</Badge>
            </TableCell>
            <TableCell className="ui-table-action-cell align-top text-right">
              <Button asChild size="sm" variant="secondary">
                <Link to="/leave/policy-admin">Open policy admin</Link>
              </Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function renderLeaveCalendarTable(requests: LeaveRequestRecord[]) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Employee</TableHead>
          <TableHead>Coverage window</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Department</TableHead>
          <TableHead className="w-[132px] text-right">Action</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {requests.map((request) => (
          <TableRow key={request.id}>
            <TableHead scope="row" className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{request.employee.full_name}</span>
                <span className="ui-table-secondary">{request.leave_type.name}</span>
              </div>
            </TableHead>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">
                  {formatDate(request.start_date)} to {formatDate(request.end_date)}
                </span>
                <span className="ui-table-secondary">{request.total_days} day(s)</span>
              </div>
            </TableCell>
            <TableCell className="align-top">
              <Badge variant={requestBadgeVariant(request.status)}>{formatRequestStatus(request.status)}</Badge>
            </TableCell>
            <TableCell className="align-top">
              <div className="ui-table-stack">
                <span className="ui-table-primary">{request.department.name}</span>
                <span className="ui-table-secondary">{request.location?.name ?? 'Location unassigned'}</span>
              </div>
            </TableCell>
            <TableCell className="ui-table-action-cell align-top text-right">
              <Button asChild size="sm" variant="secondary">
                <Link to={request.status === 'pending' ? '/leave/approvals' : '/leave/policy-admin'}>
                  {request.status === 'pending' ? 'Review' : 'Open calendar'}
                </Link>
              </Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

function filterLeaveRequests(requests: LeaveRequestRecord[], queryValue: string) {
  const query = queryValue.trim().toLowerCase()
  if (!query) {
    return requests
  }

  return requests.filter((request) =>
    [
      request.employee.full_name,
      request.employee.employee_code,
      request.leave_type.name,
      request.department.name,
      request.location?.name ?? '',
      request.reason,
      request.status,
    ]
      .join(' ')
      .toLowerCase()
      .includes(query),
  )
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
