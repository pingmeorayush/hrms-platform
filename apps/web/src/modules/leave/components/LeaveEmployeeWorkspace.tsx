import type { FormEvent } from 'react'
import { useMemo, useState } from 'react'
import { ApiRequestError } from '../../../shared/api/http'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import { useOperationFeedback } from '../../../shared/ui/use-operation-feedback'
import {
  WorkspaceSummaryRow,
  WorkspaceTableShell,
} from '../../../shared/ui/workspace'
import { useLeaveWorkspace } from '../hooks/useLeaveWorkspace'
import {
  EmptyState,
  Field,
  FormNotice,
  MetricCard,
  SelectField,
} from './leaveWorkspaceShared'
import { formatDate, formatRequestStatus, requestBadgeVariant } from './leaveWorkspaceUtils'
import type { LeaveRequestFormValues, LeaveRequestRecord, LeaveTypeRecord } from '../types'

const emptyRequestForm: LeaveRequestFormValues = {
  leave_type_id: '',
  start_date: '',
  end_date: '',
  reason: '',
}

export function LeaveEmployeeWorkspace() {
  const workspace = useLeaveWorkspace()

  return <LeaveEmployeeWorkspaceView workspace={workspace} />
}

export function LeaveEmployeeWorkspaceView({
  workspace,
}: {
  workspace: ReturnType<typeof useLeaveWorkspace>
}) {
  const {
    currentEmployeeBalances,
    currentEmployeeRequests,
    canRequestLeave,
    isSaving,
    submitLeaveRequest,
    cancelLeaveRequest,
  } = workspace

  const upcomingRequest = useMemo(
    () =>
      currentEmployeeRequests.find(
        (request) => request.status === 'pending' || request.status === 'approved',
      ) ?? null,
    [currentEmployeeRequests],
  )
  const requestableLeaveTypes = useMemo(
    () =>
      currentEmployeeBalances
        .filter((balance) => balance.available_days > 0 && balance.leave_type.status === 'active')
        .map((balance) => balance.leave_type),
    [currentEmployeeBalances],
  )
  const totalAvailableDays = useMemo(
    () => currentEmployeeBalances.reduce((total, balance) => total + balance.available_days, 0),
    [currentEmployeeBalances],
  )
  const [isRequestModalOpen, setIsRequestModalOpen] = useState(false)
  const { runConfirmedAction } = useOperationFeedback()

  return (
    <div className="workspace-stack">
      <Card className="workspace-detail-card">
        <CardHeader className="workspace-collection__header workspace-collection__header--compact">
          <div>
            <CardTitle>Submit leave request</CardTitle>
            <CardDescription>
              Review available balances, confirm upcoming time away, and submit a new request from one focused workspace.
            </CardDescription>
          </div>
          <Button variant="primary" size="sm" onClick={() => setIsRequestModalOpen(true)}>
            Request leave
          </Button>
        </CardHeader>
        <CardContent className="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(20rem,0.65fr)]">
          <section className="space-y-3">
            <div className="space-y-1">
              <h3 className="text-base font-semibold text-foreground">Leave balances</h3>
              <p className="text-sm text-muted-foreground">
                Requestable leave types, remaining balance, and booked days for this employee.
              </p>
            </div>
            {currentEmployeeBalances.length ? (
              <div className="organization-metric-grid">
                {currentEmployeeBalances.map((balance) => (
                  <MetricCard
                    key={balance.id}
                    label={balance.leave_type.name}
                    value={`${balance.available_days} day(s)`}
                    caption={`${balance.booked_days} booked · ${balance.used_days} used · ${balance.carry_forward_days} carry`}
                  />
                ))}
              </div>
            ) : (
              <EmptyState
                title="No balances available"
                copy="This employee does not have seeded leave balances in the current workspace."
              />
            )}
          </section>

          <section className="rounded-xl border border-line bg-panel-soft/70 px-4 py-3">
            <div className="space-y-1 pb-2">
              <h3 className="text-base font-semibold text-foreground">Request context</h3>
              <p className="text-sm text-muted-foreground">
                Balance checks and workflow context before submitting leave.
              </p>
            </div>
            <div className="space-y-0">
              <WorkspaceSummaryRow label="Requestable leave types" value={String(requestableLeaveTypes.length)} />
              <WorkspaceSummaryRow label="Total available balance" value={`${totalAvailableDays} day(s)`} />
              <WorkspaceSummaryRow
                label="Upcoming leave"
                value={
                  upcomingRequest
                    ? `${formatDate(upcomingRequest.start_date)} to ${formatDate(upcomingRequest.end_date)}`
                    : 'No upcoming leave'
                }
              />
            </div>
          </section>
        </CardContent>
      </Card>

      <Card className="workspace-detail-card">
        <CardHeader className="workspace-collection__header">
          <div>
            <CardTitle>Leave history</CardTitle>
            <CardDescription>
              Track request outcomes, reviewer comments, and cancellation actions from one full-width history table.
            </CardDescription>
          </div>
        </CardHeader>
        <CardContent>
          {currentEmployeeRequests.length ? (
            <WorkspaceTableShell>
              <Table>
                <colgroup>
                  <col style={{ width: '20%' }} />
                  <col style={{ width: '30%' }} />
                  <col style={{ width: '18%' }} />
                  <col style={{ width: '18%' }} />
                  <col style={{ width: '14%' }} />
                </colgroup>
                <TableHeader>
                  <TableRow>
                    <TableHead scope="col">Leave type</TableHead>
                    <TableHead scope="col">Request window</TableHead>
                    <TableHead scope="col">Status</TableHead>
                    <TableHead scope="col">Comment</TableHead>
                    <TableHead scope="col">Action</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {currentEmployeeRequests.map((request) => (
                    <TableRow key={request.id}>
                      <TableHead scope="row" className="align-top">
                        <strong className="block text-sm font-semibold text-foreground">{request.leave_type.name}</strong>
                        <small className="mt-1 block text-xs text-muted-foreground">{request.total_days} day(s)</small>
                      </TableHead>
                      <TableCell className="align-top text-sm text-muted-foreground">
                        <p>
                          {formatDate(request.start_date)} to {formatDate(request.end_date)}
                        </p>
                        <small className="mt-1 block text-xs text-muted-foreground">{request.reason}</small>
                      </TableCell>
                      <TableCell className="align-top">
                        <Badge variant={requestBadgeVariant(request.status)}>{formatRequestStatus(request.status)}</Badge>
                        <small className="mt-1 block text-xs text-muted-foreground">{formatDate(request.updated_at)}</small>
                      </TableCell>
                      <TableCell className="align-top">
                        <strong className="text-sm font-semibold text-foreground">
                          {request.approver_comment ?? 'Awaiting reviewer feedback'}
                        </strong>
                      </TableCell>
                      <TableCell className="align-top">
                        {request.can_cancel ? (
                          <CancelRequestButton
                            canRequestLeave={canRequestLeave}
                            isSaving={isSaving}
                            request={request}
                            onCancel={cancelLeaveRequest}
                          />
                        ) : (
                          <span className="text-xs text-muted-foreground">No action</span>
                        )}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </WorkspaceTableShell>
          ) : (
            <EmptyState
              title="No leave history yet"
              copy="Submit a leave request to populate the history timeline and the future manager approval queue."
            />
          )}
        </CardContent>
      </Card>

      <Modal
        open={isRequestModalOpen}
        title="Submit leave request"
        description="Complete leave request details in a focused modal flow."
        onClose={() => setIsRequestModalOpen(false)}
      >
        <LeaveRequestEditor
          canRequestLeave={canRequestLeave}
          isSaving={isSaving}
          leaveTypes={requestableLeaveTypes}
          onSave={(values) =>
            runConfirmedAction({
              title: 'Submit leave request?',
              description: 'This will place the request into the review workflow for approval.',
              confirmLabel: 'Submit request',
              tone: 'warning',
              successTitle: 'Leave request submitted',
              successDescription: 'The request is now visible in your leave history and reviewer queue.',
              errorTitle: 'Unable to submit leave request',
              action: async () => {
                await submitLeaveRequest(values)
                setIsRequestModalOpen(false)
              },
            })
          }
        />
      </Modal>
    </div>
  )
}

function LeaveRequestEditor({
  canRequestLeave,
  isSaving,
  leaveTypes,
  onSave,
}: {
  canRequestLeave: boolean
  isSaving: boolean
  leaveTypes: LeaveTypeRecord[]
  onSave: (values: LeaveRequestFormValues) => Promise<unknown>
}) {
  const [values, setValues] = useState<LeaveRequestFormValues>(emptyRequestForm)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})
  const [error, setError] = useState<string | null>(null)
  const [message, setMessage] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError(null)
    setMessage(null)
    setFieldErrors({})

    if (!values.leave_type_id || !values.start_date || !values.end_date || !values.reason.trim()) {
      setError('Leave type, dates, and reason are required.')
      return
    }

    try {
      await onSave(values)
      setMessage('Leave request submitted successfully.')
      setValues(emptyRequestForm)
    } catch (caughtError) {
      const nextError = caughtError as Error
      setError(nextError.message)

      if (nextError instanceof ApiRequestError) {
        setFieldErrors(nextError.fieldErrors)
      }
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      <div className="workspace-form-grid">
        <SelectField
          label="Leave type"
          value={values.leave_type_id}
          disabled={!canRequestLeave || isSaving}
          error={fieldErrors.leave_type_id?.[0]}
          onChange={(value) => setValues((current) => ({ ...current, leave_type_id: value }))}
          options={[
            ['', 'Select leave type'] as [string, string],
            ...leaveTypes.map((record) => [String(record.id), record.name] as [string, string]),
          ]}
        />
        <Field label="Start date" error={fieldErrors.start_date?.[0]}>
          <Input
            type="date"
            value={values.start_date}
            disabled={!canRequestLeave || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, start_date: event.target.value }))}
          />
        </Field>
        <Field label="End date" error={fieldErrors.end_date?.[0]}>
          <Input
            type="date"
            value={values.end_date}
            disabled={!canRequestLeave || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, end_date: event.target.value }))}
          />
        </Field>
      </div>

      <Field label="Reason" error={fieldErrors.reason?.[0]}>
        <Textarea
          rows={4}
          value={values.reason}
          disabled={!canRequestLeave || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, reason: event.target.value }))}
        />
      </Field>

      <FormNotice error={error} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canRequestLeave || isSaving}>
          Submit leave request
        </Button>
      </div>
    </form>
  )
}

function CancelRequestButton({
  canRequestLeave,
  isSaving,
  request,
  onCancel,
}: {
  canRequestLeave: boolean
  isSaving: boolean
  request: LeaveRequestRecord
  onCancel: (requestId: number) => Promise<unknown>
}) {
  const { runConfirmedAction } = useOperationFeedback()

  async function handleCancel() {
    await runConfirmedAction({
      title: 'Cancel this leave request?',
      description: 'Cancellation updates the request history, restores balance reservations where applicable, and removes the request from active review queues.',
      confirmLabel: 'Cancel request',
      tone: 'danger',
      successTitle: 'Leave request cancelled',
      successDescription: 'The request has been removed from the active workflow and timeline.',
      errorTitle: 'Unable to cancel leave request',
      action: async () => {
        await onCancel(request.id)
      },
    })
  }

  return (
    <Button type="button" size="sm" variant="ghost" disabled={!canRequestLeave || isSaving} onClick={() => void handleCancel()}>
      {request.status === 'approved' ? 'Cancel approved leave' : 'Cancel request'}
    </Button>
  )
}
