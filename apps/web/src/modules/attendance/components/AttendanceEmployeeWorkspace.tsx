import type { Dispatch, FormEvent, ReactNode, SetStateAction } from 'react'
import { useMemo, useState } from 'react'
import { NavLink, useLocation } from 'react-router-dom'
import { ApiRequestError } from '../../../shared/api/http'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { CardDescription, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
import { SelectField as AppSelectField } from '../../../shared/ui/select-field'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../shared/ui/table'
import { Textarea } from '../../../shared/ui/textarea'
import { useOperationFeedback } from '../../../shared/ui/use-operation-feedback'
import { WorkspaceSelectionContext } from '../../../shared/ui/workspace-selection-context'
import {
  WorkspaceActionsRow,
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspacePage,
  WorkspacePillRow,
  WorkspaceSurface,
  WorkspaceSummaryRow,
  WorkspaceTableShell,
  WorkspaceTabs,
  WorkspaceToolbar,
  WorkspaceToolbarRow,
  WorkspaceToolbarSummary,
} from '../../../shared/ui/workspace'
import { useAttendanceEmployeeWorkspace } from '../hooks/useAttendanceEmployeeWorkspace'
import type {
  AttendanceCorrection,
  AttendanceCorrectionCreatePayload,
  AttendanceHistoryFilters,
  AttendancePrimaryStatus,
  AttendanceRecord,
} from '../types'

const statusOptions: Array<['', string] | [Exclude<AttendancePrimaryStatus, null>, string]> = [
  ['', 'All statuses'],
  ['present', 'Present'],
  ['half_day', 'Half day'],
  ['absent', 'Absent'],
  ['holiday', 'Holiday'],
  ['weekend', 'Weekend'],
  ['incomplete', 'Incomplete'],
]

const stateOptions: Array<[AttendanceHistoryFilters['state'], string]> = [
  ['', 'All capture states'],
  ['not_captured', 'Not captured'],
  ['checked_in', 'Checked in'],
  ['checked_out', 'Checked out'],
]

type AttendanceSelfServiceSection = 'history' | 'corrections' | 'capture'

const attendanceSelfServiceBasePath = '/attendance/my-attendance'
const attendanceSelfServiceSections: Array<{
  id: AttendanceSelfServiceSection
  label: string
  description: string
}> = [
  {
    id: 'history',
    label: 'History',
    description: 'Review the attendance ledger in a full-width filtered table and launch corrections from row actions.',
  },
  {
    id: 'corrections',
    label: 'Correction requests',
    description: 'Track submitted correction workflow status on a dedicated page instead of mixing it into history.',
  },
  {
    id: 'capture',
    label: 'Check in / out',
    description: 'Use a focused page for today’s capture workflow and current attendance status.',
  },
]

export function AttendanceEmployeeWorkspace() {
  const location = useLocation()
  const defaultFilters = useMemo(() => buildDefaultHistoryFilters(), [])
  const [draftFilters, setDraftFilters] = useState<AttendanceHistoryFilters>(defaultFilters)
  const [filters, setFilters] = useState<AttendanceHistoryFilters>(defaultFilters)
  const [selectedRecordId, setSelectedRecordId] = useState<number | null>(null)
  const [selectedCorrectionId, setSelectedCorrectionId] = useState<number | null>(null)
  const [isCorrectionModalOpen, setIsCorrectionModalOpen] = useState(false)
  const [isCorrectionDetailModalOpen, setIsCorrectionDetailModalOpen] = useState(false)
  const {
    data,
    canCapture,
    canRequestCorrection,
    isLoading,
    error,
    isSaving,
    checkIn,
    checkOut,
    submitCorrection,
  } = useAttendanceEmployeeWorkspace(filters)
  const { runConfirmedAction, toast } = useOperationFeedback()
  const activeSection = resolveAttendanceSelfServiceSection(location.pathname)
  const pendingCorrections = data.corrections.items.filter((item) => item.status === 'pending')
  const latestCompletedRecord = data.history.items.find((record) => record.state === 'checked_out') ?? null
  const selectedRecord = useMemo(() => {
    if (!selectedRecordId) {
      return null
    }

    return (
      data.history.items.find((record) => record.id === selectedRecordId) ??
      (data.todayRecord?.id === selectedRecordId ? data.todayRecord : null)
    )
  }, [data.history.items, data.todayRecord, selectedRecordId])
  const selectedCorrection = useMemo(() => {
    if (selectedCorrectionId) {
      return data.corrections.items.find((item) => item.id === selectedCorrectionId) ?? null
    }

    return null
  }, [data.corrections.items, selectedCorrectionId])

  async function handleCheckIn() {
    try {
      await checkIn({
        channel: 'web',
      })
      toast.success('Check-in recorded', 'Your attendance day is now active.')
    } catch (caughtError) {
      toast.error('Unable to check in', extractErrorMessage(caughtError))
    }
  }

  async function handleCheckOut() {
    try {
      await checkOut({
        channel: 'web',
      })
      toast.success(
        'Check-out recorded',
        'Worked time and daily attendance status were recalculated.',
      )
    } catch (caughtError) {
      toast.error('Unable to check out', extractErrorMessage(caughtError))
    }
  }

  function openCorrectionModal(recordId: number) {
    setSelectedRecordId(recordId)
    setIsCorrectionModalOpen(true)
  }

  function openCorrectionDetailModal(correctionId: number) {
    setSelectedCorrectionId(correctionId)
    setIsCorrectionDetailModalOpen(true)
  }

  const activeSectionMetadata =
    attendanceSelfServiceSections.find((section) => section.id === activeSection) ??
    attendanceSelfServiceSections[0]

  return (
    <WorkspacePage className="gap-4">
      {isLoading ? <p className="workspace-muted">Loading personal attendance history...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}

      <WorkspaceSurface>
        <WorkspaceContent>
          <WorkspaceToolbar>
            <WorkspaceToolbarRow>
              <WorkspaceTabs role="tablist" aria-label="Attendance self-service sections">
                {attendanceSelfServiceSections.map((section) => (
                  <NavLink
                    key={section.id}
                    to={`${attendanceSelfServiceBasePath}/${section.id}`}
                    role="tab"
                    aria-selected={activeSection === section.id}
                    className={({ isActive }) =>
                      isActive
                        ? 'inline-flex items-center justify-center rounded-lg border border-primary bg-primary px-3.5 py-2 text-sm font-semibold text-white shadow-[var(--shadow-sm)]'
                        : 'inline-flex items-center justify-center rounded-lg border border-line bg-panel px-3.5 py-2 text-sm font-semibold text-foreground transition-colors hover:border-line-strong hover:bg-panel-tint'
                    }
                  >
                    {section.label}
                  </NavLink>
                ))}
              </WorkspaceTabs>
              <WorkspaceToolbarSummary className="ml-auto">
                <strong>{activeSectionMetadata.label}</strong>
              </WorkspaceToolbarSummary>
            </WorkspaceToolbarRow>
          </WorkspaceToolbar>
        </WorkspaceContent>
      </WorkspaceSurface>

      {activeSection === 'history' ? (
        <AttendanceHistorySection
          data={data}
          draftFilters={draftFilters}
          setDraftFilters={setDraftFilters}
          setFilters={setFilters}
          canRequestCorrection={canRequestCorrection}
          onOpenCorrectionModal={openCorrectionModal}
          onOpenCorrectionDetailModal={openCorrectionDetailModal}
        />
      ) : null}

      {activeSection === 'corrections' ? (
        <AttendanceCorrectionsSection
          corrections={data.corrections.items}
          selectedCorrectionId={selectedCorrection?.id ?? null}
          onOpenCorrectionDetailModal={openCorrectionDetailModal}
        />
      ) : null}

      {activeSection === 'capture' ? (
        <AttendanceCaptureSection
          data={data}
          latestCompletedRecord={latestCompletedRecord}
          pendingCorrections={pendingCorrections}
          canCapture={canCapture}
          canRequestCorrection={canRequestCorrection}
          isSaving={isSaving}
          onCheckIn={handleCheckIn}
          onCheckOut={handleCheckOut}
          onOpenCorrectionModal={openCorrectionModal}
        />
      ) : null}

      <Modal
        open={isCorrectionModalOpen}
        title="Submit attendance correction"
        description="Review the selected attendance day and submit corrected timestamps without leaving the history workflow."
        onClose={() => setIsCorrectionModalOpen(false)}
      >
        <CorrectionComposer
          key={selectedRecord?.id ?? 'no-record'}
          record={selectedRecord}
          latestCorrection={selectedRecord ? findLatestCorrection(selectedRecord.id, data.corrections.items) : null}
          canRequestCorrection={canRequestCorrection}
          isSaving={isSaving}
          onSubmit={(payload) =>
            runConfirmedAction({
              title: 'Submit correction request?',
              description: 'This sends the attendance change into the review workflow.',
              confirmLabel: 'Submit correction',
              tone: 'warning',
              successTitle: 'Correction request submitted',
              successDescription: 'The request is now visible in your correction queue.',
              errorTitle: 'Unable to submit correction',
              action: async () => {
                await submitCorrection(payload)
                setIsCorrectionModalOpen(false)
              },
            })
          }
        />
      </Modal>

      <Modal
        open={isCorrectionDetailModalOpen}
        title="Correction request details"
        description="Review the submitted correction workflow without leaving the collection page."
        onClose={() => setIsCorrectionDetailModalOpen(false)}
      >
        <CorrectionRequestDetails correction={selectedCorrection} />
      </Modal>
    </WorkspacePage>
  )
}

function AttendanceHistorySection({
  data,
  draftFilters,
  setDraftFilters,
  setFilters,
  canRequestCorrection,
  onOpenCorrectionModal,
  onOpenCorrectionDetailModal,
}: {
  data: ReturnType<typeof useAttendanceEmployeeWorkspace>['data']
  draftFilters: AttendanceHistoryFilters
  setDraftFilters: Dispatch<SetStateAction<AttendanceHistoryFilters>>
  setFilters: Dispatch<SetStateAction<AttendanceHistoryFilters>>
  canRequestCorrection: boolean
  onOpenCorrectionModal: (recordId: number) => void
  onOpenCorrectionDetailModal: (correctionId: number) => void
}) {
  return (
    <WorkspaceSurface className="attendance-self-workspace">
      <WorkspaceHeader>
        <div>
          <CardTitle>Attendance history</CardTitle>
          <CardDescription>
            Keep the ledger full width, filter it inline, and launch correction requests directly from the action column.
          </CardDescription>
        </div>
      </WorkspaceHeader>
      <WorkspaceContent>
        <form
          className="grid gap-3 rounded-xl border border-line bg-panel-soft/70 p-4 lg:grid-cols-[repeat(4,minmax(0,1fr))_auto] lg:items-end"
          onSubmit={(event) => {
            event.preventDefault()
            setFilters(draftFilters)
          }}
        >
          <Field label="Date from" compact>
            <Input
              type="date"
              value={draftFilters.dateFrom}
              onChange={(event) =>
                setDraftFilters((current) => ({ ...current, dateFrom: event.target.value }))
              }
            />
          </Field>
          <Field label="Date to" compact>
            <Input
              type="date"
              value={draftFilters.dateTo}
              onChange={(event) =>
                setDraftFilters((current) => ({ ...current, dateTo: event.target.value }))
              }
            />
          </Field>
          <SelectField
            label="Primary status"
            value={draftFilters.primaryStatus}
            onChange={(value) =>
              setDraftFilters((current) => ({
                ...current,
                primaryStatus: value as AttendanceHistoryFilters['primaryStatus'],
              }))
            }
            options={statusOptions}
          />
          <SelectField
            label="Capture state"
            value={draftFilters.state}
            onChange={(value) =>
              setDraftFilters((current) => ({
                ...current,
                state: value as AttendanceHistoryFilters['state'],
              }))
            }
            options={stateOptions}
          />
          <WorkspaceActionsRow className="lg:justify-end">
            <Button type="submit" variant="secondary">
              Apply filters
            </Button>
            <Button
              type="button"
              variant="ghost"
              onClick={() => {
                const nextFilters = buildDefaultHistoryFilters()
                setDraftFilters(nextFilters)
                setFilters(nextFilters)
              }}
            >
              Reset window
            </Button>
          </WorkspaceActionsRow>
        </form>

        {data.history.items.length ? (
          <WorkspaceTableShell>
            <Table>
              <colgroup>
                <col style={{ width: '16%' }} />
                <col style={{ width: '30%' }} />
                <col style={{ width: '18%' }} />
                <col style={{ width: '18%' }} />
                <col style={{ width: '18%' }} />
              </colgroup>
              <TableHeader>
                <TableRow>
                  <TableHead scope="col">Date</TableHead>
                  <TableHead scope="col">Shift and captures</TableHead>
                  <TableHead scope="col">Outcome</TableHead>
                  <TableHead scope="col">Correction status</TableHead>
                  <TableHead scope="col">Action</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {data.history.items.map((record) => {
                  const latestCorrection = findLatestCorrection(record.id, data.corrections.items)
                  const hasPendingCorrection = latestCorrection?.status === 'pending'
                  const correctionAllowed = canRequestCorrection && canRecordBeCorrected(record)

                  return (
                    <TableRow key={record.id}>
                      <TableHead scope="row" className="align-top">
                        <div className="ui-table-stack">
                          <strong className="ui-table-primary block">{formatDate(record.attendance_date)}</strong>
                          <small className="ui-table-secondary block">{formatRecordState(record.state)}</small>
                        </div>
                      </TableHead>
                      <TableCell className="align-top">
                        <div className="ui-table-stack">
                          <p className="ui-table-body-copy">
                            {record.shift ? `${record.shift.name} (${record.shift.code})` : 'No working shift scheduled'}
                          </p>
                          <small className="ui-table-secondary block">
                            {record.check_in.at ? `In ${formatTime(record.check_in.at)}` : 'No check-in'}
                            {' · '}
                            {record.check_out.at ? `Out ${formatTime(record.check_out.at)}` : 'No check-out'}
                          </small>
                        </div>
                      </TableCell>
                      <TableCell className="align-top">
                        <div className="ui-table-status-stack">
                          <div className="ui-table-badge-row">
                            <Badge variant={statusBadgeVariant(record.calculation.primary_status)}>
                              {formatPrimaryStatus(record.calculation.primary_status)}
                            </Badge>
                          </div>
                          {record.calculation.is_late ? <small className="ui-table-secondary block">{record.calculation.late_minutes}m late</small> : null}
                          {record.calculation.overtime_minutes > 0 ? (
                            <small className="ui-table-secondary block">{record.calculation.overtime_minutes}m overtime</small>
                          ) : null}
                        </div>
                      </TableCell>
                      <TableCell className="align-top">
                        {latestCorrection ? (
                          <div className="ui-table-stack">
                            <strong className="ui-table-primary block">{formatCorrectionStatus(latestCorrection.status)}</strong>
                            <small className="ui-table-secondary block">{latestCorrection.reason}</small>
                          </div>
                        ) : (
                          <div className="ui-table-stack">
                            <strong className="ui-table-primary block">{correctionAllowed ? 'Available' : 'Not applicable'}</strong>
                            <small className="ui-table-secondary block">
                              {correctionAllowed
                                ? 'Ready for modal submission'
                                : 'Weekend and uncaptured days do not need a request'}
                            </small>
                          </div>
                        )}
                      </TableCell>
                      <TableCell className="ui-table-action-cell align-top">
                        {hasPendingCorrection && latestCorrection ? (
                          <div className="ui-table-action-row">
                            <Button
                              type="button"
                              size="sm"
                              variant="secondary"
                              onClick={() => onOpenCorrectionDetailModal(latestCorrection.id)}
                            >
                              View request
                            </Button>
                          </div>
                        ) : correctionAllowed ? (
                          <div className="ui-table-action-row">
                            <Button
                              type="button"
                              size="sm"
                              variant="secondary"
                              onClick={() => onOpenCorrectionModal(record.id)}
                            >
                              Request correction
                            </Button>
                            {latestCorrection ? (
                              <Button
                                type="button"
                                size="sm"
                                variant="ghost"
                                onClick={() => onOpenCorrectionDetailModal(latestCorrection.id)}
                              >
                                View last request
                              </Button>
                            ) : null}
                          </div>
                        ) : (
                          <Button
                            type="button"
                            size="sm"
                            variant="ghost"
                            disabled
                          >
                            No action
                          </Button>
                        )}
                      </TableCell>
                    </TableRow>
                  )
                })}
              </TableBody>
            </Table>
          </WorkspaceTableShell>
        ) : (
          <WorkspaceEmptyState
            title="No attendance records match this window"
            copy="Try widening the date range or clearing the status filters to bring history back into view."
          />
        )}
      </WorkspaceContent>
    </WorkspaceSurface>
  )
}

function AttendanceCorrectionsSection({
  corrections,
  selectedCorrectionId,
  onOpenCorrectionDetailModal,
}: {
  corrections: AttendanceCorrection[]
  selectedCorrectionId: number | null
  onOpenCorrectionDetailModal: (correctionId: number) => void
}) {
  return (
    <WorkspaceSurface className="attendance-self-workspace">
      <WorkspaceHeader>
        <div>
          <CardTitle>Correction requests</CardTitle>
          <CardDescription>
            Review submitted correction workflows on a dedicated page instead of mixing them into the history table.
          </CardDescription>
        </div>
      </WorkspaceHeader>
      <WorkspaceContent>
        {corrections.length ? (
          <WorkspaceTableShell>
            <Table>
              <colgroup>
                <col style={{ width: '18%' }} />
                <col style={{ width: '36%' }} />
                <col style={{ width: '14%' }} />
                <col style={{ width: '16%' }} />
                <col style={{ width: '16%' }} />
              </colgroup>
              <TableHeader>
                <TableRow>
                  <TableHead scope="col">Date</TableHead>
                  <TableHead scope="col">Reason</TableHead>
                  <TableHead scope="col">Status</TableHead>
                  <TableHead scope="col">Updated</TableHead>
                  <TableHead scope="col">Action</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {corrections.map((correction) => {
                  const isSelected = selectedCorrectionId === correction.id

                  return (
                    <TableRow
                      key={correction.id}
                      data-state={isSelected ? 'selected' : undefined}
                    >
                      <TableHead scope="row" className="align-top">
                        <div className="ui-table-stack">
                          <strong className="ui-table-primary block">{formatDate(correction.original_values.attendance_date)}</strong>
                          <small className="ui-table-secondary block">{correction.employee.full_name}</small>
                        </div>
                      </TableHead>
                      <TableCell className="align-top">
                        <p className="ui-table-body-muted">{correction.reason}</p>
                      </TableCell>
                      <TableCell className="align-top">
                        <Badge variant={correctionStatusBadgeVariant(correction.status)}>
                          {formatCorrectionStatus(correction.status)}
                        </Badge>
                      </TableCell>
                      <TableCell className="align-top">
                        <small className="ui-table-secondary block">
                          {formatDate(
                            correction.updated_at ??
                              correction.created_at ??
                              correction.original_values.attendance_date,
                          )}
                        </small>
                      </TableCell>
                      <TableCell className="ui-table-action-cell align-top">
                        <div className="ui-table-action-row">
                          <Button
                            type="button"
                            size="sm"
                            variant="secondary"
                            onClick={() => onOpenCorrectionDetailModal(correction.id)}
                          >
                            View request
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
          <WorkspaceEmptyState
            title="No correction requests yet"
            copy="Any new request you submit from attendance history will appear here with workflow status."
          />
        )}
      </WorkspaceContent>
    </WorkspaceSurface>
  )
}

function CorrectionRequestDetails({
  correction,
}: {
  correction: AttendanceCorrection | null
}) {
  if (!correction) {
    return (
      <EmptyState
        title="No correction request selected"
        copy="Choose a request from the corrections table to review its workflow details in this modal."
      />
    )
  }

  return (
    <div className="space-y-4">
      <WorkspaceSelectionContext
        eyebrow="Correction request"
        title={formatCorrectionStatus(correction.status)}
        copy={correction.reason}
        facts={[
          { label: 'Attendance date', value: formatDate(correction.original_values.attendance_date) },
          {
            label: 'Corrected values',
            value: `${
              correction.corrected_values.check_in_at
                ? `In ${formatTime(correction.corrected_values.check_in_at)}`
                : 'No check-in'
            } / ${
              correction.corrected_values.check_out_at
                ? `Out ${formatTime(correction.corrected_values.check_out_at)}`
                : 'No check-out'
            }`,
          },
          { label: 'Decision comment', value: correction.decision_comment ?? 'No reviewer comment yet' },
          {
            label: 'Workflow',
            value: correction.workflow?.current_task?.stage_name ?? 'Submitted for review',
          },
        ]}
      />
      <div className="space-y-0 rounded-xl border border-line bg-panel-soft/70 px-4 py-2">
        <WorkspaceSummaryRow label="Employee" value={correction.employee.full_name} />
        <WorkspaceSummaryRow label="Requested by" value={correction.requested_by?.name ?? 'Self-service submission'} />
        <WorkspaceSummaryRow
          label="Submitted"
          value={formatDate(correction.created_at ?? correction.original_values.attendance_date)}
        />
        <WorkspaceSummaryRow
          label="Last updated"
          value={formatDate(correction.updated_at ?? correction.created_at ?? correction.original_values.attendance_date)}
        />
      </div>
    </div>
  )
}

function AttendanceCaptureSection({
  data,
  latestCompletedRecord,
  pendingCorrections,
  canCapture,
  canRequestCorrection,
  isSaving,
  onCheckIn,
  onCheckOut,
  onOpenCorrectionModal,
}: {
  data: ReturnType<typeof useAttendanceEmployeeWorkspace>['data']
  latestCompletedRecord: AttendanceRecord | null
  pendingCorrections: AttendanceCorrection[]
  canCapture: boolean
  canRequestCorrection: boolean
  isSaving: boolean
  onCheckIn: () => Promise<void>
  onCheckOut: () => Promise<void>
  onOpenCorrectionModal: (recordId: number) => void
}) {
  const latestTodayCorrection = data.todayRecord
    ? findLatestCorrection(data.todayRecord.id, data.corrections.items)
    : null

  return (
    <WorkspaceSurface>
      <WorkspaceHeader>
        <div>
          <CardTitle>Check in / check out</CardTitle>
          <CardDescription>
            Keep today&apos;s capture workflow on its own page so daily actions are not buried inside history and correction tables.
          </CardDescription>
        </div>
      </WorkspaceHeader>
      <WorkspaceContent className="space-y-4">
        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <MetricCard
            label="Current state"
            value={formatRecordState(data.todayRecord?.state ?? 'not_captured')}
            caption={
              data.todayRecord?.check_in.at
                ? `Checked in ${formatDateTime(data.todayRecord.check_in.at)}`
                : 'No capture yet for today'
            }
          />
          <MetricCard
            label="Latest worked time"
            value={formatMinutes(latestCompletedRecord?.worked_minutes ?? 0, latestCompletedRecord ? '0m' : 'Pending')}
            caption={
              latestCompletedRecord
                ? `${formatPrimaryStatus(latestCompletedRecord.calculation.primary_status)} on ${formatDate(latestCompletedRecord.attendance_date)}`
                : 'A completed attendance day will show here'
            }
          />
          <MetricCard
            label="Correction queue"
            value={String(pendingCorrections.length)}
            caption={
              pendingCorrections.length
                ? 'Pending review with workflow visibility'
                : 'No pending corrections right now'
            }
          />
          <MetricCard
            label="Policy threshold"
            value={formatMinutes(data.policy?.working_hours_minutes ?? 0, 'Not loaded')}
            caption={data.policy ? `${data.policy.name}` : 'Policy details are not available in this session'}
          />
        </div>

        <div className="rounded-xl border border-line bg-panel-soft/70 p-4">
          <div className="space-y-3">
            <p className="text-sm leading-6 text-muted-foreground">
              {canCapture
                ? 'Captures are sent on the web channel with device metadata. Corrections can still be raised later if either timestamp needs review.'
                : 'This session can review attendance history, but today’s capture actions are restricted by permissions.'}
            </p>
            {latestTodayCorrection ? (
              <WorkspacePillRow>
                <Badge variant={correctionStatusBadgeVariant(latestTodayCorrection.status)}>
                  {formatCorrectionStatus(latestTodayCorrection.status)}
                </Badge>
                <Badge variant="subtle">{latestTodayCorrection.reason}</Badge>
              </WorkspacePillRow>
            ) : null}
          </div>
          <div className="mt-4 flex flex-wrap gap-2">
            <Button
              type="button"
              onClick={onCheckIn}
              disabled={!canCapture || isSaving || data.todayRecord?.state !== 'not_captured'}
            >
              Check in
            </Button>
            <Button
              type="button"
              variant="secondary"
              onClick={onCheckOut}
              disabled={!canCapture || isSaving || data.todayRecord?.state !== 'checked_in'}
            >
              Check out
            </Button>
            <Button
              type="button"
              variant="secondary"
              onClick={() => data.todayRecord && onOpenCorrectionModal(data.todayRecord.id)}
              disabled={
                !data.todayRecord ||
                !canRequestCorrection ||
                !canRecordBeCorrected(data.todayRecord) ||
                latestTodayCorrection?.status === 'pending'
              }
            >
              Request today&apos;s correction
            </Button>
          </div>
          {!canCapture ? (
            <p className="mt-3 text-sm text-muted-foreground">
              Attendance capture requires `attendance.create`. History and correction status remain visible.
            </p>
          ) : data.todayRecord?.state === 'checked_in' ? (
            <p className="mt-3 text-sm text-muted-foreground">
              Check-in is already captured for today. Use check-out when the workday is complete.
            </p>
          ) : data.todayRecord?.state === 'checked_out' ? (
            <p className="mt-3 text-sm text-muted-foreground">
              Today&apos;s attendance is complete. Use the correction action if either timestamp needs review.
            </p>
          ) : null}
        </div>
      </WorkspaceContent>
    </WorkspaceSurface>
  )
}

function resolveAttendanceSelfServiceSection(pathname: string): AttendanceSelfServiceSection {
  const segment = pathname.replace(/\/+$/, '').split('/').pop()

  if (segment === 'corrections' || segment === 'capture') {
    return segment
  }

  return 'history'
}

function CorrectionComposer({
  record,
  latestCorrection,
  canRequestCorrection,
  isSaving,
  onSubmit,
}: {
  record: AttendanceRecord | null
  latestCorrection: AttendanceCorrection | null
  canRequestCorrection: boolean
  isSaving: boolean
  onSubmit: (payload: AttendanceCorrectionCreatePayload) => Promise<unknown>
}) {
  const [values, setValues] = useState({
    reason: '',
    check_in_at: toDateTimeInputValue(record?.check_in.at ?? null),
    check_out_at: toDateTimeInputValue(record?.check_out.at ?? null),
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)

    if (!record) {
      setFormError('Select an attendance record from the history table first.')
      return
    }

    if (!values.reason.trim()) {
      setFormError('Tell reviewers why the attendance day needs a correction.')
      return
    }

    try {
      await onSubmit({
        attendance_record_id: record.id,
        reason: values.reason.trim(),
        corrected: {
          check_in_at: values.check_in_at ? toApiDateTimeValue(values.check_in_at) : null,
          check_out_at: values.check_out_at ? toApiDateTimeValue(values.check_out_at) : null,
        },
      })
      setMessage('Correction request submitted. The workflow status will update in the recent-corrections list.')
    } catch (caughtError) {
      setFormError(extractErrorMessage(caughtError))
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      {!record ? (
        <EmptyState
          title="Select a record first"
          copy="Choose a row from personal attendance history to prefill the timestamps that need review."
        />
      ) : null}
      {!canRequestCorrection ? (
        <PermissionNotice copy="Correction requests require `attendance.correct` in the current session." />
      ) : null}
      {latestCorrection ? (
        <p className="workspace-muted">
          Latest request for this day: {formatCorrectionStatus(latestCorrection.status)}.
        </p>
      ) : null}

      <div className="workspace-form-grid">
        <Field label="Attendance date">
          <Input value={record?.attendance_date ?? ''} disabled />
        </Field>
        <Field label="Current status">
          <Input value={record ? formatPrimaryStatus(record.calculation.primary_status) : ''} disabled />
        </Field>
        <Field label="Corrected check-in">
          <Input
            type="datetime-local"
            value={values.check_in_at}
            disabled={!record || !canRequestCorrection || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, check_in_at: event.target.value }))
            }
          />
        </Field>
        <Field label="Corrected check-out">
          <Input
            type="datetime-local"
            value={values.check_out_at}
            disabled={!record || !canRequestCorrection || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, check_out_at: event.target.value }))
            }
          />
        </Field>
      </div>

      <Field label="Reason">
        <Textarea
          rows={5}
          value={values.reason}
          disabled={!record || !canRequestCorrection || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, reason: event.target.value }))}
        />
      </Field>

      <FormNotice error={formError} message={message} />

      <WorkspaceActionsRow>
        <Button type="submit" disabled={!record || !canRequestCorrection || isSaving}>
          Submit correction
        </Button>
      </WorkspaceActionsRow>
    </form>
  )
}

function MetricCard({ label, value, caption }: { label: string; value: string; caption: string }) {
  return (
    <article className="rounded-xl border border-line bg-card px-4 py-4 shadow-[var(--shadow-sm)]">
      <span className="block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-text-subtle">{label}</span>
      <strong className="mt-1 block text-lg font-semibold text-foreground">{value}</strong>
      <small className="mt-2 block text-sm leading-6 text-muted-foreground">{caption}</small>
    </article>
  )
}

function Field({
  label,
  children,
  compact = false,
}: {
  label: string
  children: ReactNode
  compact?: boolean
}) {
  return (
    <WorkspaceField label={label} compact={compact}>
      {children}
    </WorkspaceField>
  )
}

function SelectField({
  label,
  value,
  onChange,
  options,
}: {
  label: string
  value: string
  onChange: (value: string) => void
  options: Array<[string, string]>
}) {
  return <AppSelectField label={label} value={value} onChange={onChange} options={options} compact />
}

function EmptyState({ title, copy }: { title: string; copy: string }) {
  return <WorkspaceEmptyState title={title} copy={copy} />
}

function PermissionNotice({ copy }: { copy: string }) {
  return <p className="workspace-muted">{copy}</p>
}

function FormNotice({ error, message }: { error: string | null; message: string | null }) {
  return (
    <>
      {error ? <p className="workspace-error">{error}</p> : null}
      {message ? <p className="workspace-success">{message}</p> : null}
    </>
  )
}

function buildDefaultHistoryFilters(): AttendanceHistoryFilters {
  return {
    dateFrom: '',
    dateTo: '',
    primaryStatus: '',
    state: '',
    perPage: 14,
  }
}

function findLatestCorrection(attendanceRecordId: number, corrections: AttendanceCorrection[]) {
  const matches = corrections.filter((item) => item.attendance_record_id === attendanceRecordId)

  if (!matches.length) {
    return null
  }

  return matches.sort((left, right) => {
    const leftAt = left.updated_at ?? left.created_at ?? ''
    const rightAt = right.updated_at ?? right.created_at ?? ''

    return rightAt.localeCompare(leftAt)
  })[0]
}

function canRecordBeCorrected(record: AttendanceRecord) {
  if (record.calculation.primary_status === 'weekend' || record.calculation.primary_status === 'holiday') {
    return false
  }

  return record.state !== 'not_captured' || record.calculation.primary_status === 'absent'
}

function formatRecordState(state: AttendanceRecord['state']) {
  switch (state) {
    case 'checked_in':
      return 'Checked in'
    case 'checked_out':
      return 'Checked out'
    case 'not_captured':
    default:
      return 'Not captured'
  }
}

function formatPrimaryStatus(status: AttendancePrimaryStatus) {
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

function formatCorrectionStatus(status: AttendanceCorrection['status']) {
  switch (status) {
    case 'changes_requested':
      return 'Changes requested'
    case 'approved':
      return 'Approved'
    case 'rejected':
      return 'Rejected'
    case 'pending':
    default:
      return 'Pending'
  }
}

function statusBadgeVariant(status: AttendancePrimaryStatus) {
  switch (status) {
    case 'present':
      return 'success'
    case 'half_day':
    case 'incomplete':
      return 'warning'
    case 'absent':
      return 'danger'
    case 'holiday':
    case 'weekend':
    default:
      return 'subtle'
  }
}

function correctionStatusBadgeVariant(status: AttendanceCorrection['status']) {
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

function formatMinutes(value: number | null, fallback = '0m') {
  if (value === null) {
    return fallback
  }

  if (value >= 60) {
    const hours = Math.floor(value / 60)
    const minutes = value % 60

    return minutes ? `${hours}h ${minutes}m` : `${hours}h`
  }

  return `${value}m`
}

function formatDate(value: string | null) {
  if (!value) {
    return 'Not available'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

function formatTime(value: string | null) {
  if (!value) {
    return 'Pending'
  }

  return new Intl.DateTimeFormat('en-IN', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  }).format(new Date(value))
}

function formatDateTime(value: string | null) {
  if (!value) {
    return 'Pending'
  }

  return new Intl.DateTimeFormat('en-IN', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  }).format(new Date(value))
}

function toDateTimeInputValue(value: string | null) {
  if (!value) {
    return ''
  }

  return value.slice(0, 16)
}

function toApiDateTimeValue(value: string) {
  return new Date(value).toISOString()
}

function extractErrorMessage(error: unknown) {
  if (error instanceof ApiRequestError) {
    return error.message
  }

  return (error as Error).message
}
