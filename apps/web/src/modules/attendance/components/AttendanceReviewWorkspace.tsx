import { useEffect, useMemo, useState } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import { Star } from 'lucide-react'
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
  WorkspaceActionsRow,
  WorkspaceContent,
  WorkspaceEmptyState,
  WorkspaceField,
  WorkspaceHeader,
  WorkspaceHeaderActions,
  WorkspacePage,
  WorkspacePinButton,
  WorkspaceSurface,
  WorkspaceTabButton,
  WorkspaceTableShell,
  WorkspaceTabs,
} from '../../../shared/ui/workspace'
import { useAttendanceReviewWorkspace } from '../hooks/useAttendanceReviewWorkspace'
import type {
  AttendanceCorrection,
  AttendanceCorrectionDecisionAction,
  AttendanceOperationalRecord,
} from '../types'

type CorrectionStatusTab = 'all' | 'pending' | 'approved' | 'rejected' | 'changes_requested'

const correctionTabs: Array<{ id: CorrectionStatusTab; label: string }> = [
  { id: 'all', label: 'All decisions' },
  { id: 'pending', label: 'Pending' },
  { id: 'approved', label: 'Approved' },
  { id: 'rejected', label: 'Rejected' },
  { id: 'changes_requested', label: 'Changes requested' },
]

export function AttendanceReviewWorkspace() {
  const location = useLocation()
  const navigate = useNavigate()
  const initialHash = location.hash
  const initialExceptionRecordId = readHashRecordId(initialHash, '#exception-')
  const initialCorrectionId = readHashRecordId(initialHash, '#correction-')
  const [windowDate, setWindowDate] = useState(todayDate())
  const [activePanel, setActivePanel] = useState<'decisions' | 'exceptions'>(() => {
    return initialHash === '#exceptions' || initialExceptionRecordId !== null ? 'exceptions' : 'decisions'
  })
  const [selectedStatusTab, setSelectedStatusTab] = useState<CorrectionStatusTab>(() =>
    initialCorrectionId !== null ? 'all' : 'pending',
  )
  const [searchTerm, setSearchTerm] = useState('')
  const [selectedCorrectionIds, setSelectedCorrectionIds] = useState<number[]>([])
  const [selectedExceptionIds, setSelectedExceptionIds] = useState<number[]>([])
  const [selectedCorrectionId, setSelectedCorrectionId] = useState<number | null>(initialCorrectionId)
  const [selectedExceptionRecordId, setSelectedExceptionRecordId] = useState<number | null>(initialExceptionRecordId)
  const [isCorrectionModalOpen, setIsCorrectionModalOpen] = useState(initialCorrectionId !== null)
  const [isExceptionModalOpen, setIsExceptionModalOpen] = useState(initialExceptionRecordId !== null)
  const {
    data,
    canReview,
    isLoading,
    error,
    isSaving,
    decideCorrection,
  } = useAttendanceReviewWorkspace(windowDate)
  const { isFavorite, toggleFavorite } = useShellFavorites()
  const workspaceFavorite =
    activePanel === 'exceptions'
      ? {
          path: '/attendance/operational-review#exceptions',
          label: 'Attendance exceptions',
          icon: 'attendance' as const,
          description: 'Pinned attendance exception review workspace',
        }
      : {
          path: '/attendance/operational-review#decisions',
          label: 'Attendance review queue',
          icon: 'attendance' as const,
          description: 'Pinned attendance correction decision queue',
        }

  useEffect(() => {
    const hash = location.hash
    const exceptionRecordId = readHashRecordId(hash, '#exception-')
    const correctionId = readHashRecordId(hash, '#correction-')

    if (exceptionRecordId !== null) {
      setActivePanel('exceptions')
      setSelectedExceptionRecordId(exceptionRecordId)
      setSelectedCorrectionId(null)
      setIsCorrectionModalOpen(false)
      setIsExceptionModalOpen(true)
      return
    }

    if (correctionId !== null) {
      setActivePanel('decisions')
      setSelectedCorrectionId(correctionId)
      setSelectedExceptionRecordId(null)
      setSelectedStatusTab('all')
      setIsExceptionModalOpen(false)
      setIsCorrectionModalOpen(true)
      return
    }

    const nextPanel = hash === '#exceptions' ? 'exceptions' : 'decisions'
    setActivePanel((current) => (current === nextPanel ? current : nextPanel))
    setSelectedCorrectionId(null)
    setSelectedExceptionRecordId(null)
    setIsCorrectionModalOpen(false)
    setIsExceptionModalOpen(false)
  }, [location.hash])

  const setReviewPanel = (panel: 'decisions' | 'exceptions') => {
    setActivePanel(panel)
    setSelectedCorrectionId(null)
    setSelectedExceptionRecordId(null)
    setIsCorrectionModalOpen(false)
    setIsExceptionModalOpen(false)
    navigate(
      {
        pathname: location.pathname,
        hash: panel === 'exceptions' ? '#exceptions' : '#decisions',
      },
      { replace: true },
    )
  }

  const openExceptionRecord = (recordId: number) => {
    setSelectedExceptionRecordId(recordId)
    setIsExceptionModalOpen(true)
    navigate(
      {
        pathname: location.pathname,
        hash: `#exception-${recordId}`,
      },
      { replace: true },
    )
  }

  const closeExceptionRecord = () => {
    setIsExceptionModalOpen(false)
    setSelectedExceptionRecordId(null)
    navigate(
      {
        pathname: location.pathname,
        hash: '#exceptions',
      },
      { replace: true },
    )
  }

  const openCorrectionReview = (correctionId: number) => {
    setSelectedCorrectionId(correctionId)
    setIsCorrectionModalOpen(true)
    navigate(
      {
        pathname: location.pathname,
        hash: `#correction-${correctionId}`,
      },
      { replace: true },
    )
  }

  const closeCorrectionReview = () => {
    setIsCorrectionModalOpen(false)
    setSelectedCorrectionId(null)
    navigate(
      {
        pathname: location.pathname,
        hash: '#decisions',
      },
      { replace: true },
    )
  }

  const baseCorrections = useMemo(() => {
    if (selectedStatusTab === 'all') {
      return data.corrections.items
    }

    return data.corrections.items.filter((item) => item.status === selectedStatusTab)
  }, [data.corrections.items, selectedStatusTab])

  const filteredCorrections = useMemo(() => {
    const query = searchTerm.trim().toLowerCase()
    if (!query) {
      return baseCorrections
    }

    return baseCorrections.filter((item) => {
      const haystack = [
        item.employee.full_name,
        item.employee.employee_code,
        item.reason,
        item.status,
        item.original_values.attendance_date,
      ]
        .filter(Boolean)
        .join(' ')
        .toLowerCase()

      return haystack.includes(query)
    })
  }, [baseCorrections, searchTerm])

  const filteredExceptionRecords = useMemo(() => {
    const query = searchTerm.trim().toLowerCase()
    if (!query) {
      return data.pendingExceptions.attendance_items
    }

    return data.pendingExceptions.attendance_items.filter((record) => {
      const haystack = [
        record.employee.full_name,
        record.employee.employee_code,
        record.attendance_date,
        record.exception_types.join(' '),
        record.shift?.name,
        record.shift?.code,
      ]
        .filter(Boolean)
        .join(' ')
        .toLowerCase()

      return haystack.includes(query)
    })
  }, [data.pendingExceptions.attendance_items, searchTerm])

  const selectedCorrection = useMemo(() => {
    if (!selectedCorrectionId) {
      return null
    }

    return (
      filteredCorrections.find((item) => item.id === selectedCorrectionId) ??
      data.corrections.items.find((item) => item.id === selectedCorrectionId) ??
      null
    )
  }, [data.corrections.items, filteredCorrections, selectedCorrectionId])
  const selectedExceptionRecord = useMemo(() => {
    if (!selectedExceptionRecordId) {
      return null
    }

    return data.pendingExceptions.attendance_items.find((item) => item.id === selectedExceptionRecordId) ?? null
  }, [data.pendingExceptions.attendance_items, selectedExceptionRecordId])
  const linkedCorrectionForSelectedException = useMemo(() => {
    if (!selectedExceptionRecord) {
      return null
    }

    return (
      data.pendingExceptions.correction_items.find(
        (item) => item.attendance_record_id === selectedExceptionRecord.id,
      ) ?? null
    )
  }, [data.pendingExceptions.correction_items, selectedExceptionRecord])

  useEffect(() => {
    if (!isCorrectionModalOpen || !selectedCorrection?.id) {
      return
    }

    touchShellRecent({
      path: `/attendance/operational-review#correction-${selectedCorrection.id}`,
      label: `${selectedCorrection.employee.full_name} · Attendance correction · ${formatCorrectionStatus(selectedCorrection.status)}`,
      icon: 'attendance',
    })
  }, [isCorrectionModalOpen, selectedCorrection?.employee.full_name, selectedCorrection?.id])

  useEffect(() => {
    if (!isExceptionModalOpen || !selectedExceptionRecord?.id) {
      return
    }

    touchShellRecent({
      path: `/attendance/operational-review#exception-${selectedExceptionRecord.id}`,
      label: `${selectedExceptionRecord.employee.full_name} · Attendance exception · ${formatRecentExceptionSummary(selectedExceptionRecord)}`,
      icon: 'attendance',
    })
  }, [
    isExceptionModalOpen,
    selectedExceptionRecord?.employee.full_name,
    selectedExceptionRecord?.exception_types.join(','),
    selectedExceptionRecord?.id,
  ])

  const pendingQueueCount = data.corrections.items.filter((item) => item.status === 'pending').length
  const exceptionSummary = data.pendingExceptions.summary
  const scopeLabel = data.scope === 'tenant' ? 'Tenant-wide scope' : 'Team scope'
  const scopeCopy =
    data.scope === 'tenant'
      ? 'HR and admins can review the full attendance window.'
      : 'Managers only see their direct-report exception queue.'
  const selectedCorrections = useMemo(
    () => filteredCorrections.filter((item) => selectedCorrectionIds.includes(item.id)),
    [filteredCorrections, selectedCorrectionIds],
  )
  const selectedExceptions = useMemo(
    () => filteredExceptionRecords.filter((item) => selectedExceptionIds.includes(item.id)),
    [filteredExceptionRecords, selectedExceptionIds],
  )
  const singleSelectedCorrection = selectedCorrections.length === 1 ? selectedCorrections[0] : null
  const singleSelectedException = selectedExceptions.length === 1 ? selectedExceptions[0] : null
  const allCorrectionsSelected =
    filteredCorrections.length > 0 && selectedCorrectionIds.length === filteredCorrections.length
  const someCorrectionsSelected =
    selectedCorrectionIds.length > 0 && selectedCorrectionIds.length < filteredCorrections.length
  const allExceptionsSelected =
    filteredExceptionRecords.length > 0 && selectedExceptionIds.length === filteredExceptionRecords.length
  const someExceptionsSelected =
    selectedExceptionIds.length > 0 && selectedExceptionIds.length < filteredExceptionRecords.length

  function toggleCorrectionSelection(correctionId: number, checked: boolean) {
    setSelectedCorrectionIds((current) =>
      checked ? (current.includes(correctionId) ? current : [...current, correctionId]) : current.filter((id) => id !== correctionId),
    )
  }

  function toggleExceptionSelection(recordId: number, checked: boolean) {
    setSelectedExceptionIds((current) =>
      checked ? (current.includes(recordId) ? current : [...current, recordId]) : current.filter((id) => id !== recordId),
    )
  }

  useEffect(() => {
    setSelectedCorrectionIds((current) => {
      const next = current.filter((id) => filteredCorrections.some((item) => item.id === id))
      return arraysEqual(current, next) ? current : next
    })
  }, [filteredCorrections])

  useEffect(() => {
    setSelectedExceptionIds((current) => {
      const next = current.filter((id) => filteredExceptionRecords.some((item) => item.id === id))
      return arraysEqual(current, next) ? current : next
    })
  }, [filteredExceptionRecords])

  return (
    <WorkspacePage className="gap-4">
      {isLoading ? <p className="workspace-muted">Loading attendance review queues...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}

      <WorkspaceSurface className="attendance-review-console">
        <WorkspaceHeader compact>
          <div className="space-y-1">
            <CardTitle>Operational review</CardTitle>
            <CardDescription>
              Review correction requests, inspect exception records, and clear the active attendance queue.
            </CardDescription>
          </div>
          <WorkspaceHeaderActions>
            <WorkspacePinButton
              pinned={isFavorite(workspaceFavorite.path)}
              onToggle={() => toggleFavorite(workspaceFavorite)}
            />
          </WorkspaceHeaderActions>
        </WorkspaceHeader>
        <WorkspaceContent className="space-y-4">
          <ConsoleToolbar>
            <ConsoleToolbarRow>
              <div className="flex min-w-0 flex-1 flex-col gap-3">
                <ConsoleSearchField
                  value={searchTerm}
                  onChange={(event) => setSearchTerm(event.target.value)}
                  placeholder={
                    activePanel === 'decisions'
                      ? 'Search corrections by employee, code, reason, or day'
                      : 'Search exceptions by employee, code, shift, or exception type'
                  }
                  aria-label="Search"
                />
                <ConsoleMetricRow>
                  <ConsoleMetricChip label="Scope" value={scopeLabel.replace(' scope', '')} tone="info" />
                  <ConsoleMetricChip label="Pending" value={pendingQueueCount} tone="warning" />
                  <ConsoleMetricChip
                    label="Exceptions"
                    value={exceptionSummary.exception_record_count}
                    tone="neutral"
                  />
                  <ConsoleMetricChip
                    label="Linked requests"
                    value={exceptionSummary.pending_correction_request_count}
                    tone="success"
                  />
                </ConsoleMetricRow>
              </div>

              <div className="flex flex-wrap items-end gap-3 xl:justify-end">
                <WorkspaceField label="Window date" compact className="min-w-[12rem]">
                  <Input
                    type="date"
                    value={windowDate}
                    onChange={(event) => setWindowDate(event.target.value)}
                  />
                </WorkspaceField>
              </div>
            </ConsoleToolbarRow>

            <ConsoleToolbarRow className="items-center">
              <WorkspaceTabs role="tablist" aria-label="Attendance review workspaces">
                  <WorkspaceTabButton
                  type="button"
                  role="tab"
                  active={activePanel === 'decisions'}
                  aria-selected={activePanel === 'decisions'}
                  onClick={() => setReviewPanel('decisions')}
                >
                  Decision queue
                </WorkspaceTabButton>
                <WorkspaceTabButton
                  type="button"
                  role="tab"
                  active={activePanel === 'exceptions'}
                  aria-selected={activePanel === 'exceptions'}
                  onClick={() => setReviewPanel('exceptions')}
                >
                  Exception records
                </WorkspaceTabButton>
              </WorkspaceTabs>

              <div className="min-w-0 xl:text-right">
                <strong className="ui-type-body-strong block text-foreground">
                  {activePanel === 'decisions'
                    ? `${filteredCorrections.length} correction request${filteredCorrections.length === 1 ? '' : 's'}`
                    : `${filteredExceptionRecords.length} exception record${filteredExceptionRecords.length === 1 ? '' : 's'}`}
                </strong>
                <span className="ui-type-body block text-muted-foreground">{scopeCopy}</span>
              </div>
            </ConsoleToolbarRow>

            {activePanel === 'decisions' ? (
              <ConsoleToolbarRow className="items-center">
                <WorkspaceTabs role="tablist" aria-label="Correction decision filters">
                  {correctionTabs.map((tab) => (
                    <WorkspaceTabButton
                      key={tab.id}
                      type="button"
                      role="tab"
                      active={selectedStatusTab === tab.id}
                      aria-selected={selectedStatusTab === tab.id}
                      onClick={() => {
                        setSelectedStatusTab(tab.id)
                      }}
                    >
                      {tab.label}
                    </WorkspaceTabButton>
                  ))}
                </WorkspaceTabs>

                <div className="min-w-0 xl:text-right">
                  <strong className="ui-type-body-strong block text-foreground">
                    {filteredCorrections.length} requests in view
                  </strong>
                  <span className="ui-type-body block text-muted-foreground">
                    {selectedStatusTab === 'all'
                      ? `${pendingQueueCount} requests still need a decision.`
                      : getCorrectionTabSummary(selectedStatusTab, filteredCorrections.length)}
                  </span>
                </div>
              </ConsoleToolbarRow>
            ) : (
              <div className="min-w-0">
                <strong className="ui-type-body-strong block text-foreground">
                  {filteredExceptionRecords.length} exception records in view
                </strong>
                <span className="ui-type-body block text-muted-foreground">
                  {exceptionSummary.pending_correction_record_count} linked to open correction workflows.
                </span>
              </div>
            )}
          </ConsoleToolbar>

          {activePanel === 'exceptions' ? (
            filteredExceptionRecords.length ? (
              <WorkspaceTableShell>
                <Table className="min-w-[74rem]">
                  <TableHeader className="bg-panel-soft/55">
                    <TableRow>
                      <TableHead className="w-14 pl-5">
                        <TableSelectionCheckbox
                          checked={allExceptionsSelected}
                          indeterminate={someExceptionsSelected}
                          onChange={(checked) =>
                            setSelectedExceptionIds(checked ? filteredExceptionRecords.map((record) => record.id) : [])
                          }
                          ariaLabel={allExceptionsSelected ? 'Clear visible exception selection' : 'Select all visible exception records'}
                        />
                      </TableHead>
                      <TableHead>Employee</TableHead>
                      <TableHead>Attendance day</TableHead>
                      <TableHead>Capture window</TableHead>
                      <TableHead>Exception state</TableHead>
                      <TableHead className="w-[132px] pr-5 text-right">Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredExceptionRecords.map((record) => (
                      <TableRow
                        key={record.id}
                        data-state={selectedExceptionIds.includes(record.id) ? 'selected' : undefined}
                      >
                        <TableCell className="pl-5 align-top">
                          <TableSelectionCheckbox
                            checked={selectedExceptionIds.includes(record.id)}
                            onChange={(checked) => toggleExceptionSelection(record.id, checked)}
                            ariaLabel={`Select ${record.employee.full_name}`}
                          />
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-1">
                            <span className="ui-type-body-strong text-foreground">{record.employee.full_name}</span>
                            <span className="ui-type-caption text-muted-foreground">
                              {record.employee.employee_code}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-1">
                            <span className="ui-type-body-strong text-foreground">
                              {formatDate(record.attendance_date)}
                            </span>
                            <span className="ui-type-caption text-muted-foreground">
                              {record.shift ? `${record.shift.name} (${record.shift.code})` : 'No shift scheduled'}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-1">
                            <span className="ui-type-body-strong text-foreground">
                              {record.check_in.at ? `In ${formatTime(record.check_in.at)}` : 'No check-in'}
                              {' · '}
                              {record.check_out.at ? `Out ${formatTime(record.check_out.at)}` : 'No check-out'}
                            </span>
                            <span className="ui-type-caption text-muted-foreground">
                              {record.calculation.is_late
                                ? `${record.calculation.late_minutes} minutes late`
                                : 'No lateness detected'}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-2">
                            <div className="flex flex-wrap items-center gap-2">
                              <Badge variant={statusBadgeVariant(record.calculation.primary_status)}>
                                {formatPrimaryStatus(record.calculation.primary_status)}
                              </Badge>
                              {record.exception_types.map((type) => (
                                <Badge key={`${record.id}-${type}`} variant="subtle">
                                  {formatExceptionType(type)}
                                </Badge>
                              ))}
                            </div>
                            <span className="ui-type-caption text-muted-foreground">
                              {record.has_pending_correction
                                ? `${record.pending_corrections.length} linked correction request`
                                : 'No open correction request'}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="pr-5 align-top text-right">
                          <div className="flex items-center justify-end gap-2">
                            <Button
                              type="button"
                              size="sm"
                              variant="ghost"
                              aria-label={isFavorite('/attendance/operational-review#exceptions') ? 'Unpin exception workspace' : 'Pin exception workspace'}
                              onClick={() =>
                                toggleFavorite({
                                  path: '/attendance/operational-review#exceptions',
                                  label: 'Attendance exceptions',
                                  icon: 'attendance',
                                  description: 'Pinned attendance exception review workspace',
                                  meta: record.employee.full_name,
                                })
                              }
                            >
                              <Star className={cn('h-4 w-4', isFavorite('/attendance/operational-review#exceptions') && 'fill-current')} />
                            </Button>
                            <Button
                              type="button"
                              size="sm"
                              variant="secondary"
                              aria-label={`Inspect ${record.employee.full_name}`}
                              onClick={() => openExceptionRecord(record.id)}
                            >
                              Inspect
                            </Button>
                          </div>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </WorkspaceTableShell>
            ) : (
              <WorkspaceEmptyState
                title="No exception records in this scope"
                copy="Try another review date if you want to inspect a different attendance window."
              />
            )
          ) : (
            filteredCorrections.length ? (
              <WorkspaceTableShell>
                <Table className="min-w-[72rem]">
                  <TableHeader className="bg-panel-soft/55">
                    <TableRow>
                      <TableHead className="w-14 pl-5">
                        <TableSelectionCheckbox
                          checked={allCorrectionsSelected}
                          indeterminate={someCorrectionsSelected}
                          onChange={(checked) =>
                            setSelectedCorrectionIds(checked ? filteredCorrections.map((correction) => correction.id) : [])
                          }
                          ariaLabel={allCorrectionsSelected ? 'Clear visible correction selection' : 'Select all visible corrections'}
                        />
                      </TableHead>
                      <TableHead>Employee</TableHead>
                      <TableHead>Attendance day</TableHead>
                      <TableHead>Requested change</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead className="w-[132px] pr-5 text-right">Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredCorrections.map((correction) => (
                      <TableRow
                        key={correction.id}
                        data-state={selectedCorrectionIds.includes(correction.id) ? 'selected' : undefined}
                      >
                        <TableCell className="pl-5 align-top">
                          <TableSelectionCheckbox
                            checked={selectedCorrectionIds.includes(correction.id)}
                            onChange={(checked) => toggleCorrectionSelection(correction.id, checked)}
                            ariaLabel={`Select ${correction.employee.full_name}`}
                          />
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-1">
                            <span className="ui-type-body-strong text-foreground">
                              {correction.employee.full_name}
                            </span>
                            <span className="ui-type-caption text-muted-foreground">
                              {correction.employee.employee_code}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-1">
                            <span className="ui-type-body-strong text-foreground">
                              {formatDate(correction.original_values.attendance_date)}
                            </span>
                            <span className="ui-type-caption text-muted-foreground">
                              {formatCorrectionSnapshot(correction.original_values)}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-1.5">
                            <span className="ui-type-body-strong text-foreground">
                              {formatCorrectionSnapshot(correction.corrected_values)}
                            </span>
                            <span className="ui-type-caption text-muted-foreground">
                              {truncateCopy(correction.reason, 120)}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="align-top">
                          <div className="grid gap-1.5">
                            <div className="flex flex-wrap items-center gap-2">
                              <Badge variant={correctionBadgeVariant(correction.status)}>
                                {formatCorrectionStatus(correction.status)}
                              </Badge>
                              {correction.applied_values ? (
                                <Badge variant="success">Recalculated</Badge>
                              ) : null}
                            </div>
                            <span className="ui-type-caption text-muted-foreground">
                              {correction.applied_values
                                ? 'Updated values already applied to the attendance day.'
                                : 'Awaiting reviewer decision.'}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="pr-5 align-top text-right">
                          <div className="flex items-center justify-end gap-2">
                            <Button
                              type="button"
                              size="sm"
                              variant="ghost"
                              aria-label={isFavorite('/attendance/operational-review#decisions') ? 'Unpin review queue workspace' : 'Pin review queue workspace'}
                              onClick={() =>
                                toggleFavorite({
                                  path: '/attendance/operational-review#decisions',
                                  label: 'Attendance review queue',
                                  icon: 'attendance',
                                  description: 'Pinned attendance correction decision queue',
                                  meta: correction.employee.full_name,
                                })
                              }
                            >
                              <Star className={cn('h-4 w-4', isFavorite('/attendance/operational-review#decisions') && 'fill-current')} />
                            </Button>
                            <Button
                              type="button"
                              size="sm"
                              variant="secondary"
                              aria-label={`${
                                correction.status === 'pending' ? 'Review' : 'Inspect'
                              } ${correction.employee.full_name}`}
                              onClick={() => openCorrectionReview(correction.id)}
                            >
                              {correction.status === 'pending' ? 'Review' : 'Inspect'}
                            </Button>
                          </div>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </WorkspaceTableShell>
            ) : (
              <WorkspaceEmptyState
                title="No corrections in this filter"
                copy="Switch to another decision state to inspect a different slice of the queue."
              />
            )
          )}

          {activePanel === 'decisions' && selectedCorrections.length ? (
            <ConsoleBulkBar
              summary={
                <>
                  <span className="ui-type-label grid h-8 min-w-8 place-items-center rounded-full bg-white/10 px-2 text-white">
                    {selectedCorrections.length}
                  </span>
                  <div className="space-y-0.5">
                    <p className="ui-type-body-strong text-white">Correction selection active</p>
                    <p className="ui-type-caption text-slate-300">
                      {selectedCorrections.length === 1
                        ? `${selectedCorrections[0].employee.full_name} is ready for review.`
                        : `${selectedCorrections.length} corrections are selected from the current queue.`}
                    </p>
                  </div>
                </>
              }
              actions={
                <>
                  <Button
                    size="sm"
                    variant="secondary"
                    disabled={!singleSelectedCorrection}
                    onClick={() => {
                      if (!singleSelectedCorrection) return
                      openCorrectionReview(singleSelectedCorrection.id)
                    }}
                  >
                    {singleSelectedCorrection?.status === 'pending' ? 'Review selected' : 'Inspect selected'}
                  </Button>
                  <Button size="sm" variant="ghost" onClick={() => setSelectedCorrectionIds([])}>
                    Clear selection
                  </Button>
                </>
              }
            />
          ) : null}

          {activePanel === 'exceptions' && selectedExceptions.length ? (
            <ConsoleBulkBar
              summary={
                <>
                  <span className="ui-type-label grid h-8 min-w-8 place-items-center rounded-full bg-white/10 px-2 text-white">
                    {selectedExceptions.length}
                  </span>
                  <div className="space-y-0.5">
                    <p className="ui-type-body-strong text-white">Exception selection active</p>
                    <p className="ui-type-caption text-slate-300">
                      {selectedExceptions.length === 1
                        ? `${selectedExceptions[0].employee.full_name} is ready for inspection.`
                        : `${selectedExceptions.length} exception records are selected from the current window.`}
                    </p>
                  </div>
                </>
              }
              actions={
                <>
                  <Button
                    size="sm"
                    variant="secondary"
                    disabled={!singleSelectedException}
                    onClick={() => {
                      if (!singleSelectedException) return
                      openExceptionRecord(singleSelectedException.id)
                    }}
                  >
                    Inspect selected
                  </Button>
                  <Button size="sm" variant="ghost" onClick={() => setSelectedExceptionIds([])}>
                    Clear selection
                  </Button>
                </>
              }
            />
          ) : null}
        </WorkspaceContent>
      </WorkspaceSurface>

      <Modal
        open={isCorrectionModalOpen && Boolean(selectedCorrection)}
        title="Review attendance correction"
        description="Inspect the correction details and complete the workflow from a focused modal."
        size="lg"
        onClose={closeCorrectionReview}
      >
        <CorrectionDecisionPanel
          key={selectedCorrection?.id ?? 'empty'}
          correction={selectedCorrection}
          canReview={canReview}
          isSaving={isSaving}
          pendingQueueCount={pendingQueueCount}
          onComplete={closeCorrectionReview}
          onDecide={(correctionId, action, comment) =>
            decideCorrection(correctionId, {
              action,
              comment,
            })
          }
        />
      </Modal>

      <Modal
        open={isExceptionModalOpen && Boolean(selectedExceptionRecord)}
        title="Inspect attendance exception"
        description="Review the exception record and jump into the linked correction workflow when needed."
        size="lg"
        onClose={closeExceptionRecord}
      >
        <ExceptionInspectorPanel
          record={selectedExceptionRecord}
          linkedCorrection={linkedCorrectionForSelectedException}
          onOpenLinkedCorrection={() => {
            if (!linkedCorrectionForSelectedException) {
              return
            }

            closeExceptionRecord()
            setActivePanel('decisions')
            setSelectedStatusTab('pending')
            openCorrectionReview(linkedCorrectionForSelectedException.id)
          }}
        />
      </Modal>
    </WorkspacePage>
  )
}

function CorrectionDecisionPanel({
  correction,
  canReview,
  isSaving,
  pendingQueueCount,
  onComplete,
  onDecide,
}: {
  correction: AttendanceCorrection | null
  canReview: boolean
  isSaving: boolean
  pendingQueueCount: number
  onComplete?: () => void
  onDecide: (
    correctionId: number,
    action: AttendanceCorrectionDecisionAction,
    comment: string | null,
  ) => Promise<void>
}) {
  const [comment, setComment] = useState('')
  const [error, setError] = useState<string | null>(null)
  const { runConfirmedAction } = useOperationFeedback()

  async function submitDecision(action: AttendanceCorrectionDecisionAction) {
    if (!correction) {
      return
    }

    setError(null)

    if (action !== 'approve' && !comment.trim()) {
      setError('Add a reviewer comment before requesting changes or rejecting a correction.')
      return
    }

    try {
      await runConfirmedAction({
        title:
          action === 'approve'
            ? 'Approve this correction?'
            : action === 'request_changes'
              ? 'Request changes on this correction?'
              : 'Reject this correction?',
        description:
          action === 'approve'
            ? 'Approving applies the corrected values to the attendance record.'
            : action === 'request_changes'
              ? 'This sends the request back to the employee with reviewer notes.'
              : 'Rejecting closes the request without applying the correction.',
        confirmLabel:
          action === 'approve'
            ? 'Approve correction'
            : action === 'request_changes'
              ? 'Request changes'
              : 'Reject correction',
        tone: action === 'approve' ? 'warning' : 'danger',
        successTitle:
          action === 'approve'
            ? 'Correction approved'
            : action === 'request_changes'
              ? 'Changes requested'
              : 'Correction rejected',
        successDescription:
          action === 'approve'
            ? 'The attendance record was recalculated with the corrected values.'
            : action === 'request_changes'
              ? 'Reviewer feedback was sent back to the employee.'
              : 'The correction workflow has been closed.',
        errorTitle: 'Unable to complete correction review',
        action: async () => {
          await onDecide(correction.id, action, comment.trim() || null)
          recordAttendanceReviewDecision(correction, action, pendingQueueCount)
          setComment('')
          onComplete?.()
        },
      })
    } catch (caughtError) {
      setError((caughtError as Error).message)
    }
  }

  return (
    <div className="attendance-review-panel__detail">
      {!correction ? (
        <WorkspaceEmptyState
          title="Select a correction request"
          copy="Pick a queue item to inspect original values, corrected timestamps, workflow history, and decision actions."
        />
      ) : (
        <>
          <div className="attendance-review-detail__header">
            <div>
              <strong>{correction.employee.full_name}</strong>
              <p>{correction.reason}</p>
            </div>
            <div className="pill-row">
              <Badge variant={correctionBadgeVariant(correction.status)}>
                {formatCorrectionStatus(correction.status)}
              </Badge>
              {correction.applied_values ? <Badge variant="success">Recalculated</Badge> : null}
            </div>
          </div>

          <div className="employee-record-table">
            <div className="employee-record-table__row">
              <span>Attendance date</span>
              <strong>{formatDate(correction.original_values.attendance_date)}</strong>
            </div>
            <div className="employee-record-table__row">
              <span>Original values</span>
              <strong>{formatCorrectionSnapshot(correction.original_values)}</strong>
            </div>
            <div className="employee-record-table__row">
              <span>Corrected values</span>
              <strong>{formatCorrectionSnapshot(correction.corrected_values)}</strong>
            </div>
            <div className="employee-record-table__row">
              <span>Applied values</span>
              <strong>
                {correction.applied_values
                  ? formatCorrectionSnapshot(correction.applied_values)
                  : 'Not applied yet'}
              </strong>
            </div>
            <div className="employee-record-table__row">
              <span>Decision comment</span>
              <strong>{correction.decision_comment ?? 'No reviewer comment yet'}</strong>
            </div>
          </div>

          <div className="attendance-workflow-history">
            <h3 className="attendance-workflow-history__title">Workflow history</h3>
            {correction.workflow?.approval_history.length ? (
              correction.workflow.approval_history.map((task) => (
                <article className="attendance-workflow-history__item" key={task.id}>
                  <strong>{task.stage_name}</strong>
                  <span>{task.decision ? formatDecision(task.decision) : 'Open step'}</span>
                  <small>
                    {task.acted_at ? `${formatDateTime(task.acted_at)} by ${task.actor?.name ?? 'Reviewer'}` : 'Awaiting decision'}
                  </small>
                </article>
              ))
            ) : (
              <p className="workspace-muted">No completed workflow actions yet.</p>
            )}
          </div>

          {!canReview ? (
            <PermissionNotice copy="Attendance correction decisions require `attendance.approve` in the current session." />
          ) : null}

          {correction.status === 'pending' && canReview ? (
            <>
              <WorkspaceField label="Reviewer comment">
                <Textarea
                  rows={4}
                  value={comment}
                  disabled={!canReview || isSaving || correction.status !== 'pending'}
                  onChange={(event) => setComment(event.target.value)}
                />
              </WorkspaceField>

              <FormNotice error={error} message={null} />

              <div className="attendance-review-actions">
                <Button
                  type="button"
                  disabled={!canReview || isSaving || correction.status !== 'pending'}
                  onClick={() => void submitDecision('approve')}
                >
                  Approve correction
                </Button>
                <Button
                  type="button"
                  variant="secondary"
                  disabled={!canReview || isSaving || correction.status !== 'pending'}
                  onClick={() => void submitDecision('request_changes')}
                >
                  Request changes
                </Button>
                <Button
                  type="button"
                  variant="danger"
                  disabled={!canReview || isSaving || correction.status !== 'pending'}
                  onClick={() => void submitDecision('reject')}
                >
                  Reject correction
                </Button>
              </div>
            </>
          ) : null}
        </>
      )}
    </div>
  )
}

function recordAttendanceReviewDecision(
  correction: AttendanceCorrection,
  action: AttendanceCorrectionDecisionAction,
  pendingQueueCount: number,
) {
  const nextPendingCount = Math.max(pendingQueueCount - 1, 0)

  pushCommandCenterActivityEvent({
    module: 'attendance',
    path: `/attendance/operational-review#correction-${correction.id}`,
    title: `${correction.employee.full_name} correction ${formatActionVerb(action)}`,
    detail: `${formatDate(correction.original_values.attendance_date)} · ${formatCorrectionStatus(actionToCorrectionStatus(action))}`,
    meta: 'Operational review updated just now',
    tone: action === 'approve' ? 'success' : 'warning',
  })

  setCommandCenterAlertOverride({
    id: 'pending-corrections',
    module: 'attendance',
    path: '/attendance/operational-review#decisions',
    title:
      nextPendingCount > 0
        ? `${nextPendingCount} correction request(s) need a decision`
        : 'Review queue clear',
    detail:
      nextPendingCount > 0
        ? `Queue updated after reviewing ${correction.employee.full_name}.`
        : 'All pending attendance corrections have been processed.',
    meta:
      nextPendingCount > 0
        ? 'Open the decision queue to continue review.'
        : 'New correction requests will appear here automatically.',
    tone: nextPendingCount > 2 ? 'danger' : nextPendingCount > 0 ? 'warning' : 'success',
  })
}

function ExceptionInspectorPanel({
  record,
  linkedCorrection,
  onOpenLinkedCorrection,
}: {
  record: AttendanceOperationalRecord | null
  linkedCorrection: AttendanceCorrection | null
  onOpenLinkedCorrection: () => void
}) {
  if (!record) {
    return (
      <WorkspaceEmptyState
        title="No exception selected"
        copy="Choose an exception row to inspect the day and move into the decision queue when needed."
      />
    )
  }

  return (
    <div className="attendance-review-panel__detail">
      <div className="attendance-review-detail__header">
        <div>
          <strong>{record.employee.full_name}</strong>
          <p>{record.exception_types.map(formatExceptionType).join(', ')}</p>
        </div>
        <div className="pill-row">
          <Badge variant={statusBadgeVariant(record.calculation.primary_status)}>
            {formatPrimaryStatus(record.calculation.primary_status)}
          </Badge>
          {linkedCorrection ? (
            <Badge variant={correctionBadgeVariant(linkedCorrection.status)}>
              {formatCorrectionStatus(linkedCorrection.status)}
            </Badge>
          ) : null}
        </div>
      </div>

      <div className="employee-record-table">
        <div className="employee-record-table__row">
          <span>Attendance date</span>
          <strong>{formatDate(record.attendance_date)}</strong>
        </div>
        <div className="employee-record-table__row">
          <span>Captures</span>
          <strong>
            {record.check_in.at ? `In ${formatTime(record.check_in.at)}` : 'No check-in'}
            {' / '}
            {record.check_out.at ? `Out ${formatTime(record.check_out.at)}` : 'No check-out'}
          </strong>
        </div>
        <div className="employee-record-table__row">
          <span>Derived outcome</span>
          <strong>{formatPrimaryStatus(record.calculation.primary_status)}</strong>
        </div>
        <div className="employee-record-table__row">
          <span>Linked request</span>
          <strong>
            {linkedCorrection
              ? formatCorrectionStatus(linkedCorrection.status)
              : 'No pending request'}
          </strong>
        </div>
      </div>

      <WorkspaceActionsRow>
        <Button
          type="button"
          size="sm"
          variant="secondary"
          disabled={!linkedCorrection}
          onClick={onOpenLinkedCorrection}
        >
          Open linked request
        </Button>
      </WorkspaceActionsRow>
    </div>
  )
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

function formatExceptionType(type: AttendanceOperationalRecord['exception_types'][number]) {
  switch (type) {
    case 'half_day':
      return 'Half day'
    case 'pending_correction':
      return 'Pending correction'
    default:
      return type.charAt(0).toUpperCase() + type.slice(1)
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

function formatRecentExceptionSummary(record: AttendanceOperationalRecord) {
  if (record.exception_types.length === 1) {
    return formatExceptionType(record.exception_types[0])
  }

  if (record.exception_types.length > 1) {
    return `${formatExceptionType(record.exception_types[0])} +${record.exception_types.length - 1}`
  }

  return formatPrimaryStatus(record.calculation.primary_status)
}

function correctionBadgeVariant(status: AttendanceCorrection['status']) {
  switch (status) {
    case 'approved':
      return 'success'
    case 'pending':
      return 'warning'
    case 'rejected':
      return 'danger'
    case 'changes_requested':
      return 'info'
    default:
      return 'subtle'
  }
}

function statusBadgeVariant(status: AttendanceOperationalRecord['calculation']['primary_status']) {
  switch (status) {
    case 'present':
      return 'success'
    case 'half_day':
    case 'incomplete':
      return 'warning'
    case 'absent':
      return 'danger'
    default:
      return 'subtle'
  }
}

function formatCorrectionSnapshot(values: AttendanceCorrection['original_values']) {
  const checkIn = values.check_in_at ? formatTime(values.check_in_at) : 'No check-in'
  const checkOut = values.check_out_at ? formatTime(values.check_out_at) : 'No check-out'
  const status = formatPrimaryStatus(values.primary_status)

  return `${checkIn} · ${checkOut} · ${status}`
}

function formatDecision(decision: string) {
  switch (decision) {
    case 'request_changes':
      return 'Requested changes'
    case 'approve':
      return 'Approved'
    case 'reject':
      return 'Rejected'
    default:
      return decision
  }
}

function actionToCorrectionStatus(action: AttendanceCorrectionDecisionAction): AttendanceCorrection['status'] {
  switch (action) {
    case 'approve':
      return 'approved'
    case 'reject':
      return 'rejected'
    case 'request_changes':
    default:
      return 'changes_requested'
  }
}

function formatActionVerb(action: AttendanceCorrectionDecisionAction) {
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

function getCorrectionTabSummary(tab: CorrectionStatusTab, count: number) {
  switch (tab) {
    case 'pending':
      return `${count} requests still need a reviewer decision.`
    case 'approved':
      return `${count} requests were approved and recalculated.`
    case 'rejected':
      return `${count} requests were closed without applying the change.`
    case 'changes_requested':
      return `${count} requests were returned to employees for updates.`
    case 'all':
    default:
      return `${count} requests available in the current review window.`
  }
}

function truncateCopy(copy: string, maxLength: number) {
  if (copy.length <= maxLength) {
    return copy
  }

  return `${copy.slice(0, maxLength - 1).trimEnd()}…`
}

function arraysEqual(left: number[], right: number[]) {
  return left.length === right.length && left.every((value, index) => value === right[index])
}

function readHashRecordId(hash: string, prefix: string) {
  if (!hash.startsWith(prefix)) {
    return null
  }

  const recordId = Number(hash.replace(prefix, ''))
  return Number.isNaN(recordId) ? null : recordId
}

function todayDate() {
  return new Date().toISOString().slice(0, 10)
}
