import type { FormEvent } from 'react'
import { useEffect, useMemo, useState } from 'react'
import { Star } from 'lucide-react'
import { useLocation, useNavigate } from 'react-router-dom'
import {
  pushCommandCenterActivityEvent,
  setCommandCenterAlertOverride,
} from '../../../app/shell/commandCenterEvents'
import { useShellFavorites } from '../../../app/shell/favorites'
import { touchShellRecent } from '../../../app/shell/recent'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
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
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import { useOperationFeedback } from '../../../shared/ui/use-operation-feedback'
import {
  WorkspaceContent,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspacePinButton,
  WorkspaceSurface,
  WorkspaceTableShell,
} from '../../../shared/ui/workspace'
import { useLeaveWorkspace } from '../hooks/useLeaveWorkspace'
import {
  EmptyState,
  Field,
  FormNotice,
  PermissionNotice,
  SelectField,
} from './leaveWorkspaceShared'
import { formatDate, formatRequestStatus, requestBadgeVariant } from './leaveWorkspaceUtils'
import type {
  LeaveReviewDecisionAction,
  LeaveRequestRecord,
  LeaveRequestStatus,
} from '../types'

const calendarStatusOptions: Array<['', string] | [LeaveRequestStatus, string]> = [
  ['', 'Pending and approved'],
  ['pending', 'Pending only'],
  ['approved', 'Approved only'],
]

export function LeaveReviewWorkspace() {
  const workspace = useLeaveWorkspace()

  return <LeaveReviewWorkspaceView workspace={workspace} />
}

export function LeaveReviewWorkspaceView({
  workspace,
}: {
  workspace: ReturnType<typeof useLeaveWorkspace>
}) {
  const location = useLocation()
  const navigate = useNavigate()
  const selectedRequestId = readHashRecordId(location.hash, '#request-')
  const isDecisionModalOpen = selectedRequestId !== null
  const { isFavorite, toggleFavorite } = useShellFavorites()
  const approvalsWorkspaceFavorite = {
    path: '/leave/approvals',
    label: 'Leave approvals',
    icon: 'leave' as const,
    description: 'Pinned leave approval queue workspace',
  }
  const {
    data,
    canApproveLeave,
    reviewScope,
    pendingReviewRequests,
    scopedReviewRequests,
    reviewCalendarEntries,
    isSaving,
    decideLeaveRequest,
  } = workspace
  const [calendarStatus, setCalendarStatus] = useState<'' | 'pending' | 'approved'>('')
  const [availabilityDate, setAvailabilityDate] = useState(todayDate())
  const [searchTerm, setSearchTerm] = useState('')
  const [rawSelectedRequestIds, setRawSelectedRequestIds] = useState<number[]>([])
  const [decisionComment, setDecisionComment] = useState('')
  const [formError, setFormError] = useState<string | null>(null)
  const { runConfirmedAction } = useOperationFeedback()

  const filteredPendingReviewRequests = useMemo(() => {
    const query = searchTerm.trim().toLowerCase()
    if (!query) {
      return pendingReviewRequests
    }

    return pendingReviewRequests.filter((request) => {
      const haystack = [
        request.employee.full_name,
        request.employee.employee_code,
        request.department.name,
        request.leave_type.name,
        request.reason,
        request.location?.name,
      ]
        .filter(Boolean)
        .join(' ')
        .toLowerCase()

      return haystack.includes(query)
    })
  }, [pendingReviewRequests, searchTerm])

  const selectedRequest = useMemo(() => {
    if (selectedRequestId) {
      return (
        pendingReviewRequests.find((request) => request.id === selectedRequestId) ?? null
      )
    }

    return pendingReviewRequests[0] ?? null
  }, [pendingReviewRequests, selectedRequestId])
  const selectedRequestRecentLabel = useMemo(() => {
    if (!selectedRequest) {
      return null
    }

    return `${selectedRequest.employee.full_name} · Leave approval · ${formatRequestStatus(selectedRequest.status)}`
  }, [selectedRequest])

  const openDecisionModal = (requestId: number) => {
    setFormError(null)
    setDecisionComment('')
    navigate(
      {
        pathname: location.pathname,
        hash: `#request-${requestId}`,
      },
      { replace: true, flushSync: true },
    )
  }

  const closeDecisionModal = () => {
    setFormError(null)
    navigate(
      {
        pathname: location.pathname,
        hash: '',
      },
      { replace: true, flushSync: true },
    )
  }

  const filteredCalendarRequests = useMemo(() => {
    const statusFiltered = calendarStatus
      ? reviewCalendarEntries.filter((request) => request.status === calendarStatus)
      : reviewCalendarEntries

    const query = searchTerm.trim().toLowerCase()
    if (!query) {
      return statusFiltered
    }

    return statusFiltered.filter((request) => {
      const haystack = [
        request.employee.full_name,
        request.employee.employee_code,
        request.department.name,
        request.leave_type.name,
        request.reason,
        request.location?.name,
      ]
        .filter(Boolean)
        .join(' ')
        .toLowerCase()

      return haystack.includes(query)
    })
  }, [calendarStatus, reviewCalendarEntries, searchTerm])

  const selectedBalance = selectedRequest
    ? data?.balances.find(
        (record) =>
          record.employee_id === selectedRequest.employee.id &&
          record.leave_type.id === selectedRequest.leave_type.id,
      ) ?? null
    : null

  const selectedEmployeeActiveWindows = useMemo(() => {
    if (!selectedRequest) {
      return 0
    }

    return scopedReviewRequests.filter(
      (request) =>
        request.employee.id === selectedRequest.employee.id &&
        request.id !== selectedRequest.id &&
        (request.status === 'pending' || request.status === 'approved'),
    ).length
  }, [scopedReviewRequests, selectedRequest])

  useEffect(() => {
    if (!isDecisionModalOpen || !selectedRequest?.id) {
      return
    }

    touchShellRecent({
      path: `/leave/approvals#request-${selectedRequest.id}`,
      label: selectedRequestRecentLabel ?? 'Leave approval',
      icon: 'leave',
    })
  }, [isDecisionModalOpen, selectedRequest?.id, selectedRequestRecentLabel])

  const selectedRequestIds = useMemo(
    () =>
      rawSelectedRequestIds.filter((id) =>
        filteredPendingReviewRequests.some((request) => request.id === id),
      ),
    [filteredPendingReviewRequests, rawSelectedRequestIds],
  )
  const selectedRequests = useMemo(
    () => filteredPendingReviewRequests.filter((request) => selectedRequestIds.includes(request.id)),
    [filteredPendingReviewRequests, selectedRequestIds],
  )
  const singleSelectedRequest = selectedRequests.length === 1 ? selectedRequests[0] : null
  const allRequestsSelected =
    filteredPendingReviewRequests.length > 0 && selectedRequestIds.length === filteredPendingReviewRequests.length
  const someRequestsSelected =
    selectedRequestIds.length > 0 && selectedRequestIds.length < filteredPendingReviewRequests.length

  function toggleRequestSelection(requestId: number, checked: boolean) {
    setRawSelectedRequestIds((current) =>
      checked ? (current.includes(requestId) ? current : [...current, requestId]) : current.filter((id) => id !== requestId),
    )
  }

  async function submitDecision(action: LeaveReviewDecisionAction) {
    if (!selectedRequest) {
      return
    }

    setFormError(null)

    if (action !== 'approve' && !decisionComment.trim()) {
      setFormError(
        action === 'request_changes'
          ? 'Add a comment before requesting changes on this leave request.'
          : 'Add a comment before rejecting this leave request.',
      )
      return
    }

    try {
      await runConfirmedAction({
        title:
          action === 'approve'
            ? 'Approve this leave request?'
            : action === 'reject'
              ? 'Reject this leave request?'
              : 'Request changes on this leave request?',
        description:
          action === 'approve'
            ? 'Approving moves the request into the active leave calendar.'
            : action === 'reject'
              ? 'Rejecting restores the employee balance and closes the request.'
              : 'Requesting changes returns the balance reservation and sends the request back for follow-up.',
        confirmLabel:
          action === 'approve'
            ? 'Approve request'
            : action === 'reject'
              ? 'Reject request'
              : 'Request changes',
        tone: action === 'approve' ? 'warning' : action === 'reject' ? 'danger' : 'default',
        successTitle:
          action === 'approve'
            ? 'Leave request approved'
            : action === 'reject'
              ? 'Leave request rejected'
              : 'Changes requested',
        successDescription:
          action === 'approve'
            ? 'The request is now reflected in the scoped leave calendar.'
            : action === 'reject'
              ? 'The request has been closed and the employee balance was restored.'
              : 'The request was returned for follow-up and is no longer blocking the queue.',
        errorTitle: 'Unable to complete leave review',
        action: async () => {
          await decideLeaveRequest(selectedRequest.id, {
            action,
            comment: decisionComment.trim() || null,
          })
          recordLeaveReviewDecision(selectedRequest, action, pendingReviewRequests.length)
          setDecisionComment('')
          closeDecisionModal()
        },
      })
    } catch (caughtError) {
      setFormError((caughtError as Error).message)
    }
  }

  if (!canApproveLeave) {
    return (
      <PermissionNotice copy="Leave approvals require `leave.approve` or equivalent HR leave-review access in the current session." />
    )
  }

  return (
    <WorkspacePage>
      <WorkspaceSurface className="workspace-detail-card">
        <WorkspaceHeader compact>
          <div className="space-y-1">
            <CardTitle>Manager leave review</CardTitle>
            <CardDescription>
              Review pending requests, inspect balance context, and keep leave coverage visible while making decisions.
            </CardDescription>
          </div>
          <WorkspaceHeaderActions>
            <WorkspacePinButton
              pinned={isFavorite(approvalsWorkspaceFavorite.path)}
              onToggle={() => toggleFavorite(approvalsWorkspaceFavorite)}
            />
          </WorkspaceHeaderActions>
        </WorkspaceHeader>
        <WorkspaceContent>
          <ConsoleToolbar>
            <ConsoleToolbarRow>
              <div className="flex min-w-0 flex-1 flex-col gap-3">
                <ConsoleSearchField
                  value={searchTerm}
                  onChange={(event) => setSearchTerm(event.target.value)}
                  placeholder="Search requests by employee, code, leave type, department, or reason"
                  aria-label="Search"
                />
                <ConsoleMetricRow>
                  <ConsoleMetricChip
                    label="Scope"
                    value={reviewScope === 'tenant' ? 'Tenant' : 'Team'}
                    tone="info"
                  />
                  <ConsoleMetricChip
                    label="Pending"
                    value={pendingReviewRequests.length}
                    tone={pendingReviewRequests.length ? 'warning' : 'success'}
                  />
                  <ConsoleMetricChip
                    label="Calendar"
                    value={filteredCalendarRequests.length}
                    tone="neutral"
                  />
                </ConsoleMetricRow>
              </div>

              <form
                className="grid w-full gap-3 md:grid-cols-2 xl:w-auto xl:min-w-[29rem]"
                onSubmit={(event) => event.preventDefault()}
              >
                <Field label="Availability date">
                  <Input
                    type="date"
                    value={availabilityDate}
                    onChange={(event) => setAvailabilityDate(event.target.value)}
                  />
                </Field>
                <SelectField
                  label="Calendar status"
                  value={calendarStatus}
                  onChange={(value) => setCalendarStatus(value as '' | 'pending' | 'approved')}
                  options={calendarStatusOptions}
                />
              </form>
            </ConsoleToolbarRow>
            <p className="ui-type-body text-muted-foreground">
              {reviewScope === 'tenant' ? 'Tenant-wide scope' : 'Team scope'}.
              {' '}
              Keep the pending queue and leave calendar aligned while deciding requests.
            </p>
          </ConsoleToolbar>
        </WorkspaceContent>
      </WorkspaceSurface>

      <WorkspaceSurface className="workspace-detail-card">
        <WorkspaceHeader compact>
          <div>
            <CardTitle>Pending approval queue</CardTitle>
            <CardDescription>
              Review the next actionable request directly from the queue. Detailed decision work stays inside the modal.
            </CardDescription>
          </div>
          <Badge variant={pendingReviewRequests.length ? 'warning' : 'success'}>
            {pendingReviewRequests.length ? `${pendingReviewRequests.length} pending` : 'Queue clear'}
          </Badge>
        </WorkspaceHeader>
        <WorkspaceContent>
          {filteredPendingReviewRequests.length ? (
            <WorkspaceTableShell>
              <Table className="min-w-[72rem]">
                <TableHeader className="bg-panel-soft/55">
                  <TableRow>
                    <TableHead className="w-14 pl-5">
                        <TableSelectionCheckbox
                          checked={allRequestsSelected}
                          indeterminate={someRequestsSelected}
                          onChange={(checked) =>
                            setRawSelectedRequestIds(checked ? filteredPendingReviewRequests.map((request) => request.id) : [])
                          }
                          ariaLabel={allRequestsSelected ? 'Clear visible leave request selection' : 'Select all visible leave requests'}
                        />
                    </TableHead>
                    <TableHead>Employee</TableHead>
                    <TableHead>Request window</TableHead>
                    <TableHead>Balance context</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="w-[132px] pr-5 text-right">Action</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredPendingReviewRequests.map((request) => {
                    const balance = data?.balances.find(
                      (record) =>
                        record.employee_id === request.employee.id &&
                        record.leave_type.id === request.leave_type.id,
                    )

                    return (
                      <TableRow key={request.id} data-state={selectedRequestIds.includes(request.id) ? 'selected' : undefined}>
                        <TableCell className="pl-5 align-top">
                          <TableSelectionCheckbox
                            checked={selectedRequestIds.includes(request.id)}
                            onChange={(checked) => toggleRequestSelection(request.id, checked)}
                            ariaLabel={`Select ${request.employee.full_name}`}
                          />
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-1">
                            <span className="ui-type-body-strong text-foreground">{request.employee.full_name}</span>
                            <span className="ui-type-caption text-muted-foreground">
                              {request.employee.employee_code}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-1">
                            <span className="ui-type-body-strong text-foreground">
                              {formatDate(request.start_date)} to {formatDate(request.end_date)}
                            </span>
                            <span className="ui-type-caption text-muted-foreground">
                              {request.leave_type.name} · {request.total_days} day(s)
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-1">
                            <span className="ui-type-body-strong text-foreground">
                              {balance
                                ? `${balance.available_days} available · ${balance.booked_days} booked`
                                : 'No seeded balance found'}
                            </span>
                            <span className="ui-type-caption text-muted-foreground">{request.reason}</span>
                          </div>
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-1">
                            <Badge variant={requestBadgeVariant(request.status)}>
                              {formatRequestStatus(request.status)}
                            </Badge>
                            <span className="ui-type-caption text-muted-foreground">
                              {request.location?.name ?? request.department.name}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="pr-5 align-top text-right">
                          <div className="flex items-center justify-end gap-2">
                            <Button
                              type="button"
                              size="sm"
                              variant="ghost"
                              aria-label={isFavorite('/leave/approvals') ? 'Unpin approvals workspace' : 'Pin approvals workspace'}
                              onClick={() =>
                                toggleFavorite({
                                  path: '/leave/approvals',
                                  label: 'Leave approvals',
                                  icon: 'leave',
                                  description: 'Pinned leave approval queue workspace',
                                  meta: request.employee.full_name,
                                })
                              }
                            >
                              <Star className={cn('h-4 w-4', isFavorite('/leave/approvals') && 'fill-current')} />
                            </Button>
                            <Button
                              type="button"
                              size="sm"
                              variant="secondary"
                              aria-label={`Review ${request.employee.full_name}`}
                              onClick={() => openDecisionModal(request.id)}
                            >
                              Review
                            </Button>
                          </div>
                        </TableCell>
                      </TableRow>
                    )
                  })}
                </TableBody>
              </Table>
            </WorkspaceTableShell>
          ) : (
            <EmptyState
              title="No pending leave approvals"
              copy="The current scope is clear. Approved and rejected requests remain visible in the team calendar below."
            />
          )}

          {selectedRequests.length ? (
            <ConsoleBulkBar
              summary={
                <>
                  <span className="ui-type-label grid h-8 min-w-8 place-items-center rounded-full bg-white/10 px-2 text-white">
                    {selectedRequests.length}
                  </span>
                  <div className="space-y-0.5">
                    <p className="ui-type-body-strong text-white">Leave selection active</p>
                    <p className="ui-type-caption text-slate-300">
                      {selectedRequests.length === 1
                        ? `${selectedRequests[0].employee.full_name} is ready for review.`
                        : `${selectedRequests.length} leave requests are selected from the current queue.`}
                    </p>
                  </div>
                </>
              }
              actions={
                <>
                  <Button
                    size="sm"
                    variant="secondary"
                    disabled={!singleSelectedRequest}
                    onClick={() => {
                      if (!singleSelectedRequest) return
                      openDecisionModal(singleSelectedRequest.id)
                    }}
                  >
                    Review selected
                  </Button>
                  <Button size="sm" variant="ghost" onClick={() => setRawSelectedRequestIds([])}>
                    Clear selection
                  </Button>
                </>
              }
            />
          ) : null}
        </WorkspaceContent>
      </WorkspaceSurface>

      <WorkspaceSurface className="workspace-detail-card">
        <WorkspaceHeader compact>
          <div>
            <CardTitle>Team leave calendar</CardTitle>
            <CardDescription>
              Approved and pending leave windows stay visible so managers can judge coverage before approving more time away.
            </CardDescription>
          </div>
          <Badge variant="subtle">{filteredCalendarRequests.length} windows shown</Badge>
        </WorkspaceHeader>
        <WorkspaceContent>
          {filteredCalendarRequests.length ? (
            <WorkspaceTableShell>
              <Table className="min-w-[64rem]">
                <TableHeader className="bg-panel-soft/55">
                  <TableRow>
                    <TableHead>Employee</TableHead>
                    <TableHead>Leave window</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Team context</TableHead>
                    <TableHead>Review note</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredCalendarRequests.map((request) => (
                    <TableRow key={request.id}>
                      <TableCell className="align-top">
                        <div className="grid gap-1">
                          <span className="ui-type-body-strong text-foreground">{request.employee.full_name}</span>
                          <span className="ui-type-caption text-muted-foreground">{request.department.name}</span>
                        </div>
                      </TableCell>
                      <TableCell className="align-top">
                        <div className="grid gap-1">
                          <span className="ui-type-body-strong text-foreground">
                            {formatDate(request.start_date)} to {formatDate(request.end_date)}
                          </span>
                          <span className="ui-type-caption text-muted-foreground">
                            {request.leave_type.name} · {request.total_days} day(s)
                          </span>
                        </div>
                      </TableCell>
                      <TableCell className="align-top">
                        <Badge variant={requestBadgeVariant(request.status)}>
                          {formatRequestStatus(request.status)}
                        </Badge>
                      </TableCell>
                      <TableCell className="align-top">
                        <div className="grid gap-1">
                          <span className="ui-type-body-strong text-foreground">
                            {request.location?.name ?? 'No location assigned'}
                          </span>
                          <span className="ui-type-caption text-muted-foreground">{request.reason}</span>
                        </div>
                      </TableCell>
                      <TableCell className="ui-type-body align-top text-muted-foreground">
                        {request.approver_comment ?? 'Awaiting reviewer feedback'}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </WorkspaceTableShell>
          ) : (
            <EmptyState
              title="No team leave windows for the current filter"
              copy="Change the status filter or availability date to widen the team calendar view."
            />
          )}
        </WorkspaceContent>
      </WorkspaceSurface>

      <Modal
        open={isDecisionModalOpen && Boolean(selectedRequest)}
        title="Review leave request"
        description="Capture reviewer context and complete the decision from a focused modal workflow."
        onClose={closeDecisionModal}
      >
        <form
          className="workspace-form"
          onSubmit={(event: FormEvent<HTMLFormElement>) => event.preventDefault()}
        >
          {selectedRequest ? (
            <div className="workspace-stack workspace-stack--tight">
              <div className="employee-record-table">
                <div className="employee-record-table__row">
                  <span>Employee</span>
                  <strong>{selectedRequest.employee.full_name}</strong>
                </div>
                <div className="employee-record-table__row">
                  <span>Leave window</span>
                  <strong>{`${formatDate(selectedRequest.start_date)} to ${formatDate(selectedRequest.end_date)}`}</strong>
                </div>
                <div className="employee-record-table__row">
                  <span>Leave type</span>
                  <strong>{selectedRequest.leave_type.name}</strong>
                </div>
                <div className="employee-record-table__row">
                  <span>Reason</span>
                  <strong>{selectedRequest.reason}</strong>
                </div>
                <div className="employee-record-table__row">
                  <span>Available balance</span>
                  <strong>
                    {selectedBalance
                      ? `${selectedBalance.available_days} day(s)`
                      : 'No seeded balance'}
                  </strong>
                </div>
                <div className="employee-record-table__row">
                  <span>Other open windows</span>
                  <strong>{String(selectedEmployeeActiveWindows)}</strong>
                </div>
              </div>

              <Field label="Manager comment">
                <Textarea
                  rows={4}
                  value={decisionComment}
                  disabled={isSaving}
                  onChange={(event) => setDecisionComment(event.target.value)}
                  placeholder="Capture approval context, rejection reason, or follow-up notes"
                />
              </Field>

              <FormNotice error={formError} message={null} />

              <div className="workspace-actions">
                <Button type="button" disabled={isSaving} onClick={() => void submitDecision('approve')}>
                  Approve request
                </Button>
                <Button
                  type="button"
                  variant="secondary"
                  disabled={isSaving}
                  onClick={() => void submitDecision('request_changes')}
                >
                  Request changes
                </Button>
                <Button
                  type="button"
                  variant="danger"
                  disabled={isSaving}
                  onClick={() => void submitDecision('reject')}
                >
                  Reject request
                </Button>
              </div>
            </div>
          ) : null}
        </form>
      </Modal>
    </WorkspacePage>
  )
}

function todayDate() {
  return new Date().toISOString().slice(0, 10)
}

function readHashRecordId(hash: string, prefix: string) {
  if (!hash.startsWith(prefix)) {
    return null
  }

  const recordId = Number(hash.replace(prefix, ''))
  return Number.isNaN(recordId) ? null : recordId
}

function recordLeaveReviewDecision(
  request: LeaveRequestRecord,
  action: LeaveReviewDecisionAction,
  pendingQueueCount: number,
) {
  const nextPendingCount = Math.max(pendingQueueCount - 1, 0)

  pushCommandCenterActivityEvent({
    module: 'leave',
    path: `/leave/approvals#request-${request.id}`,
    title: `${request.employee.full_name} leave request ${formatLeaveActionVerb(action)}`,
    detail: `${request.leave_type.name} · ${formatDate(request.start_date)} to ${formatDate(request.end_date)}`,
    meta: 'Approval queue updated just now',
    tone: action === 'approve' ? 'success' : action === 'request_changes' ? 'info' : 'warning',
  })

  setCommandCenterAlertOverride({
    id: 'pending-review',
    module: 'leave',
    path: '/leave/approvals',
    title: nextPendingCount > 0 ? `${nextPendingCount} request(s) need a decision` : 'Approval queue clear',
    detail:
      nextPendingCount > 0
        ? `Queue updated after reviewing ${request.employee.full_name}.`
        : 'All pending leave approvals have been processed.',
    meta:
      nextPendingCount > 0
        ? 'Open the approvals queue to continue review.'
        : 'New leave requests will appear here automatically.',
    tone: nextPendingCount > 2 ? 'danger' : nextPendingCount > 0 ? 'warning' : 'success',
  })
}

function formatLeaveActionVerb(action: LeaveReviewDecisionAction) {
  switch (action) {
    case 'approve':
      return 'approved'
    case 'reject':
      return 'rejected'
    case 'request_changes':
    default:
      return 'sent back'
  }
}
