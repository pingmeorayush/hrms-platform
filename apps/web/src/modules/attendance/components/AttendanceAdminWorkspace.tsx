import type { FormEvent, ReactNode } from 'react'
import { useMemo, useState } from 'react'
import { NavLink, useLocation } from 'react-router-dom'
import { ApiRequestError } from '../../../shared/api/http'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../shared/ui/card'
import { Input } from '../../../shared/ui/input'
import { Modal } from '../../../shared/ui/modal'
import { SelectField as AppSelectField } from '../../../shared/ui/select-field'
import { Textarea } from '../../../shared/ui/textarea'
import { useOperationFeedback } from '../../../shared/ui/use-operation-feedback'
import { WorkspaceSelectionContext } from '../../../shared/ui/workspace-selection-context'
import { WorkspaceEmptyState, WorkspaceField } from '../../../shared/ui/workspace'
import { useAttendanceAdminWorkspace } from '../hooks/useAttendanceAdminWorkspace'
import type {
  AttendanceAssignmentType,
  AttendancePolicy,
  AttendancePolicyUpdatePayload,
  Holiday,
  HolidayCalendar,
  HolidayCalendarPayload,
  HolidayPayload,
  Shift,
  ShiftAssignment,
  ShiftAssignmentPayload,
  ShiftRoster,
  ShiftRosterPayload,
  ShiftRosterStatus,
  ShiftPayload,
  WeekendRule,
} from '../types'

type AttendanceAdminTab = 'policy' | 'calendars' | 'shifts' | 'assignments' | 'rosters'

const attendanceTabs: Array<{ id: AttendanceAdminTab; label: string }> = [
  { id: 'policy', label: 'Policy' },
  { id: 'calendars', label: 'Holiday calendars' },
  { id: 'shifts', label: 'Shifts' },
  { id: 'assignments', label: 'Assignments' },
  { id: 'rosters', label: 'Rosters' },
]

const attendanceAdminBasePath = '/attendance/admin-setup'

const weekendOptions: Array<{ value: number; label: string }> = [
  { value: 0, label: 'Sunday' },
  { value: 1, label: 'Monday' },
  { value: 2, label: 'Tuesday' },
  { value: 3, label: 'Wednesday' },
  { value: 4, label: 'Thursday' },
  { value: 5, label: 'Friday' },
  { value: 6, label: 'Saturday' },
]

export function AttendanceAdminWorkspace() {
  const location = useLocation()
  const {
    data,
    canEditPolicy,
    canManageShift,
    canManageRoster,
    canManageAny,
    isLoading,
    error,
    isSaving,
    savePolicy,
    saveHolidayCalendar,
    saveHoliday,
    saveShift,
    saveAssignment,
    saveRoster,
  } = useAttendanceAdminWorkspace()
  const [selectedCalendarId, setSelectedCalendarId] = useState<number | null>(null)
  const [selectedHolidayId, setSelectedHolidayId] = useState<number | null>(null)
  const [selectedShiftId, setSelectedShiftId] = useState<number | null>(null)
  const [selectedAssignmentId, setSelectedAssignmentId] = useState<number | null>(null)
  const [selectedRosterId, setSelectedRosterId] = useState<number | null>(null)
  const [isPolicyModalOpen, setIsPolicyModalOpen] = useState(false)
  const [isCalendarModalOpen, setIsCalendarModalOpen] = useState(false)
  const [isHolidayModalOpen, setIsHolidayModalOpen] = useState(false)
  const [isShiftModalOpen, setIsShiftModalOpen] = useState(false)
  const [isAssignmentModalOpen, setIsAssignmentModalOpen] = useState(false)
  const [isRosterModalOpen, setIsRosterModalOpen] = useState(false)
  const { runConfirmedAction } = useOperationFeedback()

  const selectedCalendar = useMemo(
    () => data?.holidayCalendars.find((record) => record.id === selectedCalendarId) ?? null,
    [data?.holidayCalendars, selectedCalendarId],
  )
  const selectedHoliday = useMemo(
    () => selectedCalendar?.holidays.find((record) => record.id === selectedHolidayId) ?? null,
    [selectedCalendar?.holidays, selectedHolidayId],
  )
  const selectedShift = useMemo(
    () => data?.shifts.find((record) => record.id === selectedShiftId) ?? null,
    [data?.shifts, selectedShiftId],
  )
  const selectedAssignment = useMemo(
    () => data?.assignments.find((record) => record.id === selectedAssignmentId) ?? null,
    [data?.assignments, selectedAssignmentId],
  )
  const selectedRoster = useMemo(
    () => data?.rosters.find((record) => record.id === selectedRosterId) ?? null,
    [data?.rosters, selectedRosterId],
  )

  const scheduledRosterCount = useMemo(
    () => data?.rosters.filter((record) => record.status === 'scheduled').length ?? 0,
    [data?.rosters],
  )
  const activeTab = resolveAttendanceAdminTab(location.pathname)
  const currentTabLabel = attendanceTabs.find((tab) => tab.id === activeTab)?.label ?? 'Attendance admin'
  const activeSectionCount = useMemo(() => {
    if (!data) {
      return 0
    }

    switch (activeTab) {
      case 'policy':
        return 1
      case 'calendars':
        return data.holidayCalendars.length
      case 'shifts':
        return data.shifts.length
      case 'assignments':
        return data.assignments.length
      case 'rosters':
        return data.rosters.length
      default:
        return 0
    }
  }, [activeTab, data])
  const sectionSummary = useMemo(() => {
    if (!data) {
      return { value: 'Attendance setup', detail: 'Configuration records will appear once the workspace is ready.' }
    }

    switch (activeTab) {
      case 'policy':
        return {
          value: formatMinutes(data.policy.working_hours_minutes),
          detail: `${data.policy.name} · ${data.policy.enforce_geofence ? 'Geofence enforced' : 'Geofence disabled'}`,
        }
      case 'calendars':
        return {
          value: `${data.holidayCalendars.length} calendars`,
          detail: `${data.holidayCalendars.reduce((count, record) => count + record.holidays.length, 0)} holidays configured`,
        }
      case 'shifts':
        return {
          value: `${data.shifts.length} shifts`,
          detail: `${data.shifts.filter((record) => record.status === 'active').length} active definitions`,
        }
      case 'assignments':
        return {
          value: `${data.assignments.length} assignments`,
          detail: 'Effective-dated coverage across employee, department, and location scopes',
        }
      case 'rosters':
        return {
          value: `${scheduledRosterCount} scheduled`,
          detail: `${data.rosters.length - scheduledRosterCount} cancelled or rescheduled`,
        }
      default:
        return {
          value: 'Attendance setup',
          detail: 'Select a setup area to continue.',
        }
    }
  }, [activeTab, data, scheduledRosterCount])

  return (
    <div className="workspace-stack">
      {isLoading ? <p className="workspace-muted">Loading attendance administration...</p> : null}
      {error ? <p className="workspace-error">{error.message}</p> : null}

      {data ? (
        <>
          <Card className="workspace-collection">
            <CardContent className="workspace-collection__content">
              <div className="workspace-collection-toolbar">
                <div className="workspace-collection-toolbar__row">
                  <div className="workspace-collection-tabs" role="tablist" aria-label="Attendance administration sections">
                    {attendanceTabs.map((tab) => (
                      <NavLink
                        key={tab.id}
                        to={`${attendanceAdminBasePath}/${tab.id}`}
                        role="tab"
                        aria-selected={activeTab === tab.id}
                        className={`workspace-collection-tabs__button${
                          activeTab === tab.id ? ' workspace-collection-tabs__button--active' : ''
                        }`}
                      >
                        {tab.label}
                      </NavLink>
                    ))}
                  </div>
                  <div className="workspace-collection-toolbar__status">
                    <Badge variant="info">{activeSectionCount} records in scope</Badge>
                  </div>
                </div>
                <div className="workspace-collection-toolbar__summary">
                  <strong>{currentTabLabel}</strong>
                  <span>{sectionSummary.value}</span>
                  <span>{sectionSummary.detail}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          {!canManageAny ? (
            <Card className="workspace-detail-card">
              <CardHeader>
                <CardTitle>Admin actions are permission restricted</CardTitle>
                <CardDescription>
                  This route is visible to attendance users, but configuration changes require one of `attendance.edit`, `attendance.manage_shift`, or `attendance.manage_roster`.
                </CardDescription>
              </CardHeader>
            </Card>
          ) : null}

          {activeTab === 'policy' ? (
            <Card className="workspace-detail-card">
              <CardHeader className="workspace-collection__header workspace-collection__header--compact">
                <div>
                  <CardTitle>Attendance policy</CardTitle>
                  <CardDescription>
                    Keep one clean policy surface here, then use the modal editor for calculation changes.
                  </CardDescription>
                </div>
                <Button variant="primary" size="sm" onClick={() => setIsPolicyModalOpen(true)}>
                  Edit in modal
                </Button>
              </CardHeader>
              <CardContent className="workspace-stack workspace-stack--tight">
                <WorkspaceSelectionContext
                  eyebrow="Policy baseline"
                  title={data.policy.name}
                  copy="This is the live attendance rule set used by capture, review, and downstream calculations."
                  facts={[
                    { label: 'Working hours', value: formatMinutes(data.policy.working_hours_minutes) },
                    { label: 'Grace window', value: formatMinutes(data.policy.grace_minutes) },
                    { label: 'Weekend rule', value: formatWeekendDays(data.policy.weekend_rule) },
                    { label: 'Geofence', value: data.policy.enforce_geofence ? 'Enabled' : 'Disabled' },
                  ]}
                />
                <div className="organization-metric-grid">
                  <MetricCard label="Half day" value={formatMinutes(data.policy.half_day_minutes)} caption="Derived at this threshold" />
                  <MetricCard
                    label="Late after"
                    value={formatMinutes(data.policy.late_after_minutes)}
                    caption="Marked late after this buffer"
                  />
                  <MetricCard
                    label="Work from home"
                    value={data.policy.work_from_home_allowed ? 'Allowed' : 'Restricted'}
                    caption="Remote attendance eligibility"
                  />
                  <MetricCard
                    label="Geofence radius"
                    value={
                      data.policy.enforce_geofence && data.policy.allowed_radius_meters
                        ? `${data.policy.allowed_radius_meters} m`
                        : 'Not enforced'
                    }
                    caption="Capture radius applied when enabled"
                  />
                </div>
              </CardContent>
            </Card>
          ) : null}

          {activeTab === 'calendars' ? (
            <>
              <Card className="workspace-detail-card">
              <CardHeader className="workspace-collection__header">
                <div>
                  <CardTitle>Holiday calendars</CardTitle>
                    <CardDescription>
                      Use tenant, location, and department-scoped calendars to shape attendance outcomes and non-working days.
                    </CardDescription>
                  </div>
                  <div className="workspace-actions">
                    <Button
                      variant="secondary"
                      size="sm"
                      onClick={() => {
                        setSelectedCalendarId(null)
                        setIsCalendarModalOpen(true)
                      }}
                    >
                      New calendar
                    </Button>
                    <Button
                      variant="primary"
                      size="sm"
                      onClick={() => setIsCalendarModalOpen(true)}
                      disabled={!selectedCalendar}
                    >
                      Edit current
                    </Button>
                  </div>
                </CardHeader>
                <CardContent className="workspace-stack workspace-stack--tight">
                  {data.holidayCalendars.length ? (
                    <SelectorTable
                      headers={['Calendar', 'Scope', 'Status', 'Updated', 'Action']}
                      rows={data.holidayCalendars.map((calendar) => ({
                        id: calendar.id,
                        selected: selectedCalendarId === calendar.id,
                        primary: calendar.name,
                        primaryMeta: calendar.code,
                        secondary: formatHolidayScope(calendar),
                        badges: [calendar.status, `${calendar.holidays.length} holidays`],
                        meta: formatDate(calendar.updated_at),
                        actionLabel:
                          selectedCalendarId === calendar.id ? 'Current calendar' : 'Open holidays',
                        actionVariant: selectedCalendarId === calendar.id ? 'ghost' : 'secondary',
                        onAction: () => {
                          setSelectedCalendarId(calendar.id)
                          setSelectedHolidayId(null)
                        },
                      }))}
                    />
                  ) : (
                    <EmptyState
                      title="No holiday calendars yet"
                      copy="Create the first calendar to define tenant or scoped holidays."
                    />
                  )}
                </CardContent>
              </Card>

              <Card className="workspace-detail-card">
                <CardHeader className="workspace-collection__header">
                  <div>
                    <CardTitle>Calendar holidays</CardTitle>
                    <CardDescription>
                      {selectedCalendar
                        ? `Manage holidays for ${selectedCalendar.name} from one full-width collection.`
                        : 'Select a holiday calendar to unlock its holiday schedule.'}
                    </CardDescription>
                  </div>
                  <div className="workspace-actions">
                    <Button
                      variant="secondary"
                      size="sm"
                      onClick={() => {
                        setSelectedHolidayId(null)
                        setIsHolidayModalOpen(true)
                      }}
                      disabled={!selectedCalendar}
                    >
                      New holiday
                    </Button>
                  </div>
                </CardHeader>
                <CardContent className="workspace-stack workspace-stack--tight">
                  {!selectedCalendar ? (
                    <EmptyState
                      title="Choose a holiday calendar"
                      copy="Selecting a calendar keeps this page focused and avoids an empty side panel."
                    />
                  ) : (
                    <>
                      {selectedCalendar.holidays.length ? (
                        <SelectorTable
                          headers={['Holiday', 'Date', 'Type', 'Updated', 'Action']}
                          rows={selectedCalendar.holidays.map((holiday) => ({
                            id: holiday.id,
                            selected: false,
                            primary: holiday.name,
                            primaryMeta: holiday.is_optional ? 'Optional holiday' : 'Mandatory holiday',
                            secondary: formatDate(holiday.holiday_date),
                            badges: [holiday.type, holiday.is_optional ? 'optional' : 'required'],
                            meta: formatDate(holiday.updated_at),
                            actionLabel: 'Edit in modal',
                            onAction: () => {
                              setSelectedHolidayId(holiday.id)
                              setIsHolidayModalOpen(true)
                            },
                          }))}
                        />
                      ) : (
                        <EmptyState
                          title="No holidays in this calendar"
                          copy="Add the first holiday to make this calendar operational."
                        />
                      )}
                    </>
                  )}
                </CardContent>
              </Card>
            </>
          ) : null}

          {activeTab === 'shifts' ? (
            <Card className="workspace-detail-card">
              <CardHeader className="workspace-collection__header">
                <div>
                  <CardTitle>Shift definitions</CardTitle>
                  <CardDescription>
                    Create standard, early, and overnight shifts with calculation-ready timing and coverage rules.
                  </CardDescription>
                </div>
                <div className="workspace-actions">
                  <Button
                    variant="secondary"
                    size="sm"
                    onClick={() => {
                      setSelectedShiftId(null)
                      setIsShiftModalOpen(true)
                    }}
                    >
                      New shift
                    </Button>
                </div>
              </CardHeader>
              <CardContent className="workspace-stack workspace-stack--tight">
                {data.shifts.length ? (
                  <SelectorTable
                    headers={['Shift', 'Coverage', 'Status', 'Updated', 'Action']}
                    rows={data.shifts.map((shift) => ({
                      id: shift.id,
                      selected: false,
                      primary: shift.name,
                      primaryMeta: shift.code,
                      secondary: `${shift.start_time} to ${shift.end_time} · ${formatMinutes(shift.working_hours_minutes)}`,
                      badges: [shift.status, shift.is_overnight ? 'overnight' : 'day'],
                      meta: formatDate(shift.updated_at),
                      actionLabel: 'Edit in modal',
                      onAction: () => {
                        setSelectedShiftId(shift.id)
                        setIsShiftModalOpen(true)
                      },
                    }))}
                  />
                ) : (
                  <EmptyState title="No shifts configured" copy="Add a shift to begin scheduling assignments and rosters." />
                )}
              </CardContent>
            </Card>
          ) : null}

          {activeTab === 'assignments' ? (
            <Card className="workspace-detail-card">
              <CardHeader className="workspace-collection__header">
                <div>
                  <CardTitle>Shift assignments</CardTitle>
                  <CardDescription>
                    Effective-dated assignments support employee, department, and location scopes with overlap validation.
                  </CardDescription>
                </div>
                <div className="workspace-actions">
                  <Button
                    variant="secondary"
                    size="sm"
                    onClick={() => {
                      setSelectedAssignmentId(null)
                      setIsAssignmentModalOpen(true)
                    }}
                    >
                      New assignment
                    </Button>
                </div>
              </CardHeader>
              <CardContent className="workspace-stack workspace-stack--tight">
                {data.assignments.length ? (
                  <SelectorTable
                    headers={['Assignment', 'Scope', 'Status', 'Updated', 'Action']}
                    rows={data.assignments.map((assignment) => ({
                      id: assignment.id,
                      selected: false,
                      primary: assignment.shift.name,
                      primaryMeta: assignment.shift.code,
                      secondary: `${formatAssignmentScope(assignment)} · ${assignment.effective_from}${assignment.effective_to ? ` to ${assignment.effective_to}` : ''}`,
                      badges: [assignment.assignment_type, assignment.status],
                      meta: formatDate(assignment.updated_at),
                      actionLabel: 'Edit in modal',
                      onAction: () => {
                        setSelectedAssignmentId(assignment.id)
                        setIsAssignmentModalOpen(true)
                      },
                    }))}
                  />
                ) : (
                  <EmptyState
                    title="No assignments yet"
                    copy="Create the first effective-dated assignment to connect shifts to the workforce."
                  />
                )}
              </CardContent>
            </Card>
          ) : null}

          {activeTab === 'rosters' ? (
            <Card className="workspace-detail-card">
              <CardHeader className="workspace-collection__header">
                <div>
                  <CardTitle>Roster schedule</CardTitle>
                  <CardDescription>
                    Use the full width for schedule planning, then open modal flows for create and edit actions.
                  </CardDescription>
                </div>
                <div className="workspace-actions">
                  <Button
                    variant="secondary"
                    size="sm"
                    onClick={() => {
                      setSelectedRosterId(null)
                      setIsRosterModalOpen(true)
                    }}
                    >
                      New roster
                    </Button>
                </div>
              </CardHeader>
              <CardContent className="workspace-stack workspace-stack--tight">
                {data.rosters.length ? (
                  <SelectorTable
                    headers={['Roster', 'Shift', 'Status', 'Updated', 'Action']}
                    rows={data.rosters.map((roster) => ({
                      id: roster.id,
                      selected: false,
                      primary: roster.employee.full_name,
                      primaryMeta: roster.employee.employee_code,
                      secondary: `${roster.shift.name} · ${formatDate(roster.work_date)}`,
                      badges: [roster.status, roster.shift.is_overnight ? 'overnight' : 'day'],
                      meta: formatDate(roster.updated_at),
                      actionLabel: 'Edit in modal',
                      onAction: () => {
                        setSelectedRosterId(roster.id)
                        setIsRosterModalOpen(true)
                      },
                    }))}
                  />
                ) : (
                  <EmptyState title="No roster entries yet" copy="Schedule a roster entry to start operational planning." />
                )}
              </CardContent>
            </Card>
          ) : null}

          <Modal
            open={isPolicyModalOpen}
            title="Edit attendance policy"
            description="Update baseline attendance calculation rules without leaving the collection view."
            size="lg"
            onClose={() => setIsPolicyModalOpen(false)}
          >
            <PolicyEditor
              key={`${data.policy.id}:${data.policy.updated_at ?? 'policy'}`}
              policy={data.policy}
              canManage={canEditPolicy}
              isSaving={isSaving}
              onSave={(payload) =>
                runConfirmedAction({
                  title: 'Save attendance policy?',
                  description: 'This updates the rules used by capture, review, and downstream attendance calculations.',
                  confirmLabel: 'Save policy',
                  tone: 'warning',
                  successTitle: 'Attendance policy updated',
                  successDescription: 'The new policy values are now active in the workspace.',
                  errorTitle: 'Unable to save attendance policy',
                  action: async () => {
                    await savePolicy(payload)
                    setIsPolicyModalOpen(false)
                  },
                })
              }
            />
          </Modal>

          <Modal
            open={isCalendarModalOpen}
            title={selectedCalendar ? `Edit ${selectedCalendar.name}` : 'Create holiday calendar'}
            description="Manage holiday calendar scope and defaults in a focused modal workflow."
            size="lg"
            onClose={() => setIsCalendarModalOpen(false)}
          >
            <HolidayCalendarEditor
              key={`calendar:${selectedCalendarId ?? 'new'}`}
              calendar={selectedCalendar}
              departments={data.departments}
              locations={data.locations}
              canManage={canEditPolicy}
              isSaving={isSaving}
              onSave={(payload) =>
                runConfirmedAction({
                  title: selectedCalendar ? `Save ${selectedCalendar.name}?` : 'Create holiday calendar?',
                  description: selectedCalendar
                    ? 'Review the holiday calendar scope before saving.'
                    : 'Create this holiday calendar for tenant or scoped attendance coverage.',
                  confirmLabel: selectedCalendar ? 'Save calendar' : 'Create calendar',
                  tone: selectedCalendar ? 'warning' : 'default',
                  successTitle: selectedCalendar ? 'Holiday calendar updated' : 'Holiday calendar created',
                  successDescription: 'The calendar is now available in attendance administration.',
                  errorTitle: 'Unable to save holiday calendar',
                  action: async () => {
                    await saveHolidayCalendar(selectedCalendar?.id, payload)
                    setIsCalendarModalOpen(false)
                  },
                })
              }
            />
          </Modal>

          <Modal
            open={isHolidayModalOpen}
            title={selectedHoliday ? `Edit ${selectedHoliday.name}` : 'Create holiday'}
            description="Keep holiday-date changes inside a focused modal workflow."
            onClose={() => setIsHolidayModalOpen(false)}
          >
            <HolidayEditor
              key={`holiday:${selectedCalendar?.id ?? 'none'}:${selectedHolidayId ?? 'new'}`}
              holiday={selectedHoliday}
              selectedCalendar={selectedCalendar}
              canManage={canEditPolicy}
              isSaving={isSaving}
              onSave={(payload) =>
                runConfirmedAction({
                  title: selectedHoliday ? `Save ${selectedHoliday.name}?` : 'Create holiday?',
                  description: selectedHoliday
                    ? 'Review the holiday details and date before saving.'
                    : 'Create this holiday within the selected attendance calendar.',
                  confirmLabel: selectedHoliday ? 'Save holiday' : 'Create holiday',
                  tone: selectedHoliday ? 'warning' : 'default',
                  successTitle: selectedHoliday ? 'Holiday updated' : 'Holiday created',
                  successDescription: 'Holiday coverage is now reflected in the calendar.',
                  errorTitle: 'Unable to save holiday',
                  action: async () => {
                    await saveHoliday(selectedCalendar?.id ?? 0, selectedHoliday?.id, payload)
                    setIsHolidayModalOpen(false)
                  },
                })
              }
            />
          </Modal>

          <Modal
            open={isShiftModalOpen}
            title={selectedShift ? `Edit ${selectedShift.name}` : 'Create shift'}
            description="Manage shift definitions without embedding the full form in the collection view."
            size="lg"
            onClose={() => setIsShiftModalOpen(false)}
          >
            <ShiftEditor
              key={`shift:${selectedShiftId ?? 'new'}`}
              shift={selectedShift}
              canManage={canManageShift}
              isSaving={isSaving}
              onSave={(payload) =>
                runConfirmedAction({
                  title: selectedShift ? `Save ${selectedShift.name}?` : 'Create shift?',
                  description: selectedShift
                    ? 'Review the shift timing and coverage details before saving.'
                    : 'Create this shift for assignment and roster planning.',
                  confirmLabel: selectedShift ? 'Save shift' : 'Create shift',
                  tone: selectedShift ? 'warning' : 'default',
                  successTitle: selectedShift ? 'Shift updated' : 'Shift created',
                  successDescription: 'Shift changes are now available across attendance setup.',
                  errorTitle: 'Unable to save shift',
                  action: async () => {
                    await saveShift(selectedShift?.id, payload)
                    setIsShiftModalOpen(false)
                  },
                })
              }
            />
          </Modal>

          <Modal
            open={isAssignmentModalOpen}
            title={selectedAssignment ? 'Edit assignment' : 'Create assignment'}
            description="Manage effective-dated shift assignments in a focused modal workflow."
            size="lg"
            onClose={() => setIsAssignmentModalOpen(false)}
          >
            <ShiftAssignmentEditor
              key={`assignment:${selectedAssignmentId ?? 'new'}`}
              assignment={selectedAssignment}
              shifts={data.shifts}
              employees={data.employees}
              departments={data.departments}
              locations={data.locations}
              canManage={canManageShift}
              isSaving={isSaving}
              onSave={(payload) =>
                runConfirmedAction({
                  title: selectedAssignment ? 'Save shift assignment?' : 'Create shift assignment?',
                  description: selectedAssignment
                    ? 'Review the scope and effective dates before saving.'
                    : 'Create this assignment for scoped shift coverage.',
                  confirmLabel: selectedAssignment ? 'Save assignment' : 'Create assignment',
                  tone: selectedAssignment ? 'warning' : 'default',
                  successTitle: selectedAssignment ? 'Assignment updated' : 'Assignment created',
                  successDescription: 'Assignment changes are now reflected in the attendance workspace.',
                  errorTitle: 'Unable to save assignment',
                  action: async () => {
                    await saveAssignment(selectedAssignment?.id, payload)
                    setIsAssignmentModalOpen(false)
                  },
                })
              }
            />
          </Modal>

          <Modal
            open={isRosterModalOpen}
            title={selectedRoster ? 'Edit roster entry' : 'Schedule roster entry'}
            description="Keep roster scheduling and edits inside a controlled modal workflow."
            size="lg"
            onClose={() => setIsRosterModalOpen(false)}
          >
            <ShiftRosterEditor
              key={`roster:${selectedRosterId ?? 'new'}`}
              roster={selectedRoster}
              shifts={data.shifts}
              employees={data.employees}
              canManage={canManageRoster}
              isSaving={isSaving}
              onSave={(payload) =>
                runConfirmedAction({
                  title: selectedRoster ? 'Save roster entry?' : 'Schedule roster entry?',
                  description: selectedRoster
                    ? 'Review the roster date and shift before saving.'
                    : 'Schedule this roster entry for the selected employee.',
                  confirmLabel: selectedRoster ? 'Save roster' : 'Schedule roster',
                  tone: selectedRoster ? 'warning' : 'default',
                  successTitle: selectedRoster ? 'Roster updated' : 'Roster scheduled',
                  successDescription: 'Roster changes are now available in operational planning.',
                  errorTitle: 'Unable to save roster entry',
                  action: async () => {
                    await saveRoster(selectedRoster?.id, payload)
                    setIsRosterModalOpen(false)
                  },
                })
              }
            />
          </Modal>
        </>
      ) : null}
    </div>
  )
}

function resolveAttendanceAdminTab(pathname: string): AttendanceAdminTab {
  const segment = pathname.replace(/\/+$/, '').split('/').pop()

  if (segment === 'calendars' || segment === 'shifts' || segment === 'assignments' || segment === 'rosters') {
    return segment
  }

  return 'policy'
}

function PolicyEditor({
  policy,
  canManage,
  isSaving,
  onSave,
}: {
  policy: AttendancePolicy
  canManage: boolean
  isSaving: boolean
  onSave: (payload: AttendancePolicyUpdatePayload) => Promise<unknown>
}) {
  const [values, setValues] = useState({
    name: policy.name,
    working_hours_minutes: String(policy.working_hours_minutes),
    grace_minutes: String(policy.grace_minutes),
    late_after_minutes: String(policy.late_after_minutes),
    half_day_minutes: String(policy.half_day_minutes),
    overtime_eligible: policy.overtime_eligible,
    overtime_after_minutes: String(policy.overtime_after_minutes ?? ''),
    work_from_home_allowed: policy.work_from_home_allowed,
    enforce_geofence: policy.enforce_geofence,
    allowed_radius_meters: String(policy.allowed_radius_meters ?? ''),
    status: policy.status,
    non_working_days: [...policy.weekend_rule.non_working_days],
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)
    setFieldErrors({})

    if (!values.name.trim() || values.non_working_days.length === 0) {
      setFormError('Policy name and at least one non-working day are required.')
      return
    }

    try {
      await onSave({
        name: values.name.trim(),
        working_hours_minutes: Number(values.working_hours_minutes) || 0,
        grace_minutes: Number(values.grace_minutes) || 0,
        late_after_minutes: Number(values.late_after_minutes) || 0,
        half_day_minutes: Number(values.half_day_minutes) || 0,
        overtime_eligible: values.overtime_eligible,
        overtime_after_minutes: values.overtime_eligible ? Number(values.overtime_after_minutes) || null : null,
        weekend_rule: {
          non_working_days: [...values.non_working_days].sort((left, right) => left - right),
        },
        work_from_home_allowed: values.work_from_home_allowed,
        enforce_geofence: values.enforce_geofence,
        allowed_radius_meters: values.enforce_geofence ? Number(values.allowed_radius_meters) || null : null,
        status: values.status,
      })
      setMessage('Attendance policy saved successfully.')
    } catch (caughtError) {
      const nextError = extractErrorState(caughtError)
      setFormError(nextError.message)
      setFieldErrors(nextError.fieldErrors)
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      {!canManage ? <PermissionNotice copy="Attendance policy editing is restricted in this session." /> : null}

      <div className="workspace-form-grid">
        <Field label="Policy name" errors={fieldErrors.name}>
          <Input
            value={values.name}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, name: event.target.value }))}
          />
        </Field>
        <SelectField
          label="Status"
          value={values.status}
          disabled={!canManage || isSaving}
          errors={fieldErrors.status}
          onChange={(value) =>
            setValues((current) => ({
              ...current,
              status: value as AttendancePolicy['status'],
            }))
          }
          options={[
            ['active', 'Active'],
            ['inactive', 'Inactive'],
          ]}
        />
        <Field label="Working hours (minutes)" errors={fieldErrors.working_hours_minutes}>
          <Input
            type="number"
            min="60"
            max="1440"
            value={values.working_hours_minutes}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, working_hours_minutes: event.target.value }))
            }
          />
        </Field>
        <Field label="Grace minutes" errors={fieldErrors.grace_minutes}>
          <Input
            type="number"
            min="0"
            max="180"
            value={values.grace_minutes}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, grace_minutes: event.target.value }))}
          />
        </Field>
        <Field label="Late after minutes" errors={fieldErrors.late_after_minutes}>
          <Input
            type="number"
            min="0"
            max="180"
            value={values.late_after_minutes}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, late_after_minutes: event.target.value }))
            }
          />
        </Field>
        <Field label="Half day minutes" errors={fieldErrors.half_day_minutes}>
          <Input
            type="number"
            min="1"
            value={values.half_day_minutes}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, half_day_minutes: event.target.value }))
            }
          />
        </Field>
        <Field label="Overtime after minutes" errors={fieldErrors.overtime_after_minutes}>
          <Input
            type="number"
            min="1"
            value={values.overtime_after_minutes}
            disabled={!canManage || isSaving || !values.overtime_eligible}
            onChange={(event) =>
              setValues((current) => ({ ...current, overtime_after_minutes: event.target.value }))
            }
          />
        </Field>
        <Field label="Geofence radius (meters)" errors={fieldErrors.allowed_radius_meters}>
          <Input
            type="number"
            min="1"
            value={values.allowed_radius_meters}
            disabled={!canManage || isSaving || !values.enforce_geofence}
            onChange={(event) =>
              setValues((current) => ({ ...current, allowed_radius_meters: event.target.value }))
            }
          />
        </Field>
      </div>

      <div className="pill-row">
        {weekendOptions.map((option) => (
          <label className="employee-checkbox-field" key={option.value}>
            <input
              type="checkbox"
              checked={values.non_working_days.includes(option.value)}
              disabled={!canManage || isSaving}
              onChange={(event) =>
                setValues((current) => ({
                  ...current,
                  non_working_days: event.target.checked
                    ? [...current.non_working_days, option.value]
                    : current.non_working_days.filter((value) => value !== option.value),
                }))
              }
            />
            <span>{option.label}</span>
          </label>
        ))}
      </div>
      {fieldErrors.weekend_rule?.length ? <FieldErrors errors={fieldErrors.weekend_rule} /> : null}

      <div className="pill-row">
        <label className="employee-checkbox-field">
          <input
            type="checkbox"
            checked={values.overtime_eligible}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, overtime_eligible: event.target.checked }))
            }
          />
          <span>Overtime eligible</span>
        </label>
        <label className="employee-checkbox-field">
          <input
            type="checkbox"
            checked={values.work_from_home_allowed}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, work_from_home_allowed: event.target.checked }))
            }
          />
          <span>Allow work from home</span>
        </label>
        <label className="employee-checkbox-field">
          <input
            type="checkbox"
            checked={values.enforce_geofence}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, enforce_geofence: event.target.checked }))
            }
          />
          <span>Enforce geofence</span>
        </label>
      </div>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          Save attendance policy
        </Button>
      </div>
    </form>
  )
}

function HolidayCalendarEditor({
  calendar,
  departments,
  locations,
  canManage,
  isSaving,
  onSave,
}: {
  calendar: HolidayCalendar | null
  departments: Array<{ id: number; name: string }>
  locations: Array<{ id: number; name: string }>
  canManage: boolean
  isSaving: boolean
  onSave: (payload: HolidayCalendarPayload) => Promise<unknown>
}) {
  const [values, setValues] = useState({
    code: calendar?.code ?? '',
    name: calendar?.name ?? '',
    description: calendar?.description ?? '',
    location_id: calendar?.location ? String(calendar.location.id) : '',
    department_id: calendar?.department ? String(calendar.department.id) : '',
    is_default: calendar?.is_default ?? false,
    status: calendar?.status ?? 'active',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)
    setFieldErrors({})

    if (!values.code.trim() || !values.name.trim()) {
      setFormError('Calendar code and name are required.')
      return
    }

    try {
      await onSave({
        code: values.code.trim().toUpperCase(),
        name: values.name.trim(),
        description: values.description.trim() || null,
        location_id: values.location_id ? Number(values.location_id) : null,
        department_id: values.department_id ? Number(values.department_id) : null,
        is_default: values.is_default,
        status: values.status,
      })
      setMessage(calendar ? 'Holiday calendar updated.' : 'Holiday calendar created.')
    } catch (caughtError) {
      const nextError = extractErrorState(caughtError)
      setFormError(nextError.message)
      setFieldErrors(nextError.fieldErrors)
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      {!canManage ? <PermissionNotice copy="Holiday calendar management requires attendance edit access." /> : null}

      <div className="workspace-form-grid">
        <Field label="Code" errors={fieldErrors.code}>
          <Input
            value={values.code}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, code: event.target.value }))}
          />
        </Field>
        <Field label="Name" errors={fieldErrors.name}>
          <Input
            value={values.name}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, name: event.target.value }))}
          />
        </Field>
        <SelectField
          label="Location scope"
          value={values.location_id}
          disabled={!canManage || isSaving}
          errors={fieldErrors.location_id}
          onChange={(value) => setValues((current) => ({ ...current, location_id: value }))}
          options={[
            ['', 'Tenant-wide'],
            ...locations.map((record) => [String(record.id), record.name] as [string, string]),
          ]}
        />
        <SelectField
          label="Department scope"
          value={values.department_id}
          disabled={!canManage || isSaving}
          errors={fieldErrors.department_id}
          onChange={(value) => setValues((current) => ({ ...current, department_id: value }))}
          options={[
            ['', 'All departments'],
            ...departments.map((record) => [String(record.id), record.name] as [string, string]),
          ]}
        />
        <SelectField
          label="Status"
          value={values.status}
          disabled={!canManage || isSaving}
          errors={fieldErrors.status}
          onChange={(value) =>
            setValues((current) => ({ ...current, status: value as HolidayCalendar['status'] }))
          }
          options={[
            ['active', 'Active'],
            ['inactive', 'Inactive'],
          ]}
        />
      </div>

      <Field label="Description" errors={fieldErrors.description}>
        <Textarea
          rows={4}
          value={values.description}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, description: event.target.value }))}
        />
      </Field>

      <label className="employee-checkbox-field">
        <input
          type="checkbox"
          checked={values.is_default}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, is_default: event.target.checked }))}
        />
        <span>Use as the default calendar</span>
      </label>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          {calendar ? 'Save calendar' : 'Create calendar'}
        </Button>
      </div>
    </form>
  )
}

function HolidayEditor({
  holiday,
  selectedCalendar,
  canManage,
  isSaving,
  onSave,
}: {
  holiday: Holiday | null
  selectedCalendar: HolidayCalendar | null
  canManage: boolean
  isSaving: boolean
  onSave: (payload: HolidayPayload) => Promise<unknown>
}) {
  const [values, setValues] = useState({
    name: holiday?.name ?? '',
    holiday_date: holiday?.holiday_date ?? todayDate(),
    type: holiday?.type ?? 'company',
    is_optional: holiday?.is_optional ?? false,
    description: holiday?.description ?? '',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)
    setFieldErrors({})

    if (!selectedCalendar) {
      setFormError('Select a holiday calendar before adding holidays.')
      return
    }

    if (!values.name.trim() || !values.holiday_date) {
      setFormError('Holiday name and date are required.')
      return
    }

    try {
      await onSave({
        name: values.name.trim(),
        holiday_date: values.holiday_date,
        type: values.type,
        is_optional: values.is_optional,
        description: values.description.trim() || null,
      })
      setMessage(holiday ? 'Holiday updated.' : 'Holiday created.')
    } catch (caughtError) {
      const nextError = extractErrorState(caughtError)
      setFormError(nextError.message)
      setFieldErrors(nextError.fieldErrors)
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      {!selectedCalendar ? (
        <EmptyState title="Select a calendar first" copy="Holiday editing becomes available after choosing a calendar." />
      ) : null}
      {!canManage ? <PermissionNotice copy="Holiday editing requires attendance edit access." /> : null}

      <div className="workspace-form-grid">
        <Field label="Holiday name" errors={fieldErrors.name}>
          <Input
            value={values.name}
            disabled={!selectedCalendar || !canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, name: event.target.value }))}
          />
        </Field>
        <Field label="Holiday date" errors={fieldErrors.holiday_date}>
          <Input
            type="date"
            value={values.holiday_date}
            disabled={!selectedCalendar || !canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, holiday_date: event.target.value }))}
          />
        </Field>
        <SelectField
          label="Type"
          value={values.type}
          disabled={!selectedCalendar || !canManage || isSaving}
          errors={fieldErrors.type}
          onChange={(value) => setValues((current) => ({ ...current, type: value as Holiday['type'] }))}
          options={[
            ['national', 'National'],
            ['regional', 'Regional'],
            ['company', 'Company'],
            ['optional', 'Optional'],
          ]}
        />
      </div>

      <Field label="Description" errors={fieldErrors.description}>
        <Textarea
          rows={4}
          value={values.description}
          disabled={!selectedCalendar || !canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, description: event.target.value }))}
        />
      </Field>

      <label className="employee-checkbox-field">
        <input
          type="checkbox"
          checked={values.is_optional}
          disabled={!selectedCalendar || !canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, is_optional: event.target.checked }))}
        />
        <span>Mark this as an optional holiday</span>
      </label>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!selectedCalendar || !canManage || isSaving}>
          {holiday ? 'Save holiday' : 'Create holiday'}
        </Button>
      </div>
    </form>
  )
}

function ShiftEditor({
  shift,
  canManage,
  isSaving,
  onSave,
}: {
  shift: Shift | null
  canManage: boolean
  isSaving: boolean
  onSave: (payload: ShiftPayload) => Promise<unknown>
}) {
  const [values, setValues] = useState({
    code: shift?.code ?? '',
    name: shift?.name ?? '',
    description: shift?.description ?? '',
    start_time: shift?.start_time.slice(0, 5) ?? '09:00',
    end_time: shift?.end_time.slice(0, 5) ?? '18:00',
    break_duration_minutes: String(shift?.break_duration_minutes ?? 60),
    grace_minutes: String(shift?.grace_minutes ?? 10),
    working_hours_minutes: String(shift?.working_hours_minutes ?? 480),
    status: shift?.status ?? 'active',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)
    setFieldErrors({})

    if (!values.code.trim() || !values.name.trim() || !values.start_time || !values.end_time) {
      setFormError('Shift code, name, start time, and end time are required.')
      return
    }

    try {
      await onSave({
        code: values.code.trim().toUpperCase(),
        name: values.name.trim(),
        description: values.description.trim() || null,
        start_time: values.start_time,
        end_time: values.end_time,
        break_duration_minutes: Number(values.break_duration_minutes) || 0,
        grace_minutes: Number(values.grace_minutes) || 0,
        working_hours_minutes: Number(values.working_hours_minutes) || 0,
        status: values.status,
      })
      setMessage(shift ? 'Shift updated.' : 'Shift created.')
    } catch (caughtError) {
      const nextError = extractErrorState(caughtError)
      setFormError(nextError.message)
      setFieldErrors(nextError.fieldErrors)
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      {!canManage ? <PermissionNotice copy="Shift definition changes require shift-management access." /> : null}

      <div className="workspace-form-grid">
        <Field label="Code" errors={fieldErrors.code}>
          <Input
            value={values.code}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, code: event.target.value }))}
          />
        </Field>
        <Field label="Name" errors={fieldErrors.name}>
          <Input
            value={values.name}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, name: event.target.value }))}
          />
        </Field>
        <Field label="Start time" errors={fieldErrors.start_time}>
          <Input
            type="time"
            value={values.start_time}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, start_time: event.target.value }))}
          />
        </Field>
        <Field label="End time" errors={fieldErrors.end_time}>
          <Input
            type="time"
            value={values.end_time}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, end_time: event.target.value }))}
          />
        </Field>
        <Field label="Break duration (minutes)" errors={fieldErrors.break_duration_minutes}>
          <Input
            type="number"
            min="0"
            value={values.break_duration_minutes}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, break_duration_minutes: event.target.value }))
            }
          />
        </Field>
        <Field label="Grace minutes" errors={fieldErrors.grace_minutes}>
          <Input
            type="number"
            min="0"
            value={values.grace_minutes}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, grace_minutes: event.target.value }))}
          />
        </Field>
        <Field label="Working hours (minutes)" errors={fieldErrors.working_hours_minutes}>
          <Input
            type="number"
            min="1"
            value={values.working_hours_minutes}
            disabled={!canManage || isSaving}
            onChange={(event) =>
              setValues((current) => ({ ...current, working_hours_minutes: event.target.value }))
            }
          />
        </Field>
        <SelectField
          label="Status"
          value={values.status}
          disabled={!canManage || isSaving}
          errors={fieldErrors.status}
          onChange={(value) => setValues((current) => ({ ...current, status: value as Shift['status'] }))}
          options={[
            ['active', 'Active'],
            ['inactive', 'Inactive'],
          ]}
        />
      </div>

      <Field label="Description" errors={fieldErrors.description}>
        <Textarea
          rows={4}
          value={values.description}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, description: event.target.value }))}
        />
      </Field>

      <FormNotice
        error={formError}
        message={
          message ??
          (values.end_time <= values.start_time ? 'This shift will be treated as overnight.' : null)
        }
      />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          {shift ? 'Save shift' : 'Create shift'}
        </Button>
      </div>
    </form>
  )
}

function ShiftAssignmentEditor({
  assignment,
  shifts,
  employees,
  departments,
  locations,
  canManage,
  isSaving,
  onSave,
}: {
  assignment: ShiftAssignment | null
  shifts: Shift[]
  employees: Array<{ id: number; full_name: string; employee_code: string }>
  departments: Array<{ id: number; name: string }>
  locations: Array<{ id: number; name: string }>
  canManage: boolean
  isSaving: boolean
  onSave: (payload: ShiftAssignmentPayload) => Promise<unknown>
}) {
  const [values, setValues] = useState({
    shift_id: assignment ? String(assignment.shift.id) : '',
    assignment_type: assignment?.assignment_type ?? 'employee',
    employee_id: assignment?.employee ? String(assignment.employee.id) : '',
    department_id: assignment?.department ? String(assignment.department.id) : '',
    location_id: assignment?.location ? String(assignment.location.id) : '',
    effective_from: assignment?.effective_from ?? todayDate(),
    effective_to: assignment?.effective_to ?? '',
    notes: assignment?.notes ?? '',
    status: assignment?.status ?? 'active',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)
    setFieldErrors({})

    if (!values.shift_id || !values.effective_from) {
      setFormError('Shift and effective-from date are required.')
      return
    }

    if (values.assignment_type === 'employee' && !values.employee_id) {
      setFormError('Select an employee for employee-scoped assignments.')
      return
    }

    if (values.assignment_type === 'department' && !values.department_id) {
      setFormError('Select a department for department-scoped assignments.')
      return
    }

    if (values.assignment_type === 'location' && !values.location_id) {
      setFormError('Select a location for location-scoped assignments.')
      return
    }

    try {
      await onSave({
        shift_id: Number(values.shift_id),
        assignment_type: values.assignment_type,
        employee_id: values.assignment_type === 'employee' ? Number(values.employee_id) : null,
        department_id: values.assignment_type === 'department' ? Number(values.department_id) : null,
        location_id: values.assignment_type === 'location' ? Number(values.location_id) : null,
        effective_from: values.effective_from,
        effective_to: values.effective_to || null,
        notes: values.notes.trim() || null,
        status: values.status,
      })
      setMessage(assignment ? 'Shift assignment updated.' : 'Shift assignment created.')
    } catch (caughtError) {
      const nextError = extractErrorState(caughtError)
      setFormError(nextError.message)
      setFieldErrors(nextError.fieldErrors)
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      {!canManage ? <PermissionNotice copy="Shift assignments require shift-management access." /> : null}

      <div className="workspace-form-grid">
        <SelectField
          label="Shift"
          value={values.shift_id}
          disabled={!canManage || isSaving}
          errors={fieldErrors.shift_id}
          onChange={(value) => setValues((current) => ({ ...current, shift_id: value }))}
          options={[
            ['', 'Select shift'],
            ...shifts.map((record) => [String(record.id), `${record.name} (${record.code})`] as [string, string]),
          ]}
        />
        <SelectField
          label="Assignment type"
          value={values.assignment_type}
          disabled={!canManage || isSaving}
          errors={fieldErrors.assignment_type}
          onChange={(value) =>
            setValues((current) => ({
              ...current,
              assignment_type: value as AttendanceAssignmentType,
              employee_id: value === 'employee' ? current.employee_id : '',
              department_id: value === 'department' ? current.department_id : '',
              location_id: value === 'location' ? current.location_id : '',
            }))
          }
          options={[
            ['employee', 'Employee'],
            ['department', 'Department'],
            ['location', 'Location'],
          ]}
        />
        {values.assignment_type === 'employee' ? (
          <SelectField
            label="Employee"
            value={values.employee_id}
            disabled={!canManage || isSaving}
            errors={fieldErrors.employee_id}
            onChange={(value) => setValues((current) => ({ ...current, employee_id: value }))}
            options={[
              ['', 'Select employee'],
              ...employees.map((record) => [String(record.id), `${record.full_name} (${record.employee_code})`] as [string, string]),
            ]}
          />
        ) : null}
        {values.assignment_type === 'department' ? (
          <SelectField
            label="Department"
            value={values.department_id}
            disabled={!canManage || isSaving}
            errors={fieldErrors.department_id}
            onChange={(value) => setValues((current) => ({ ...current, department_id: value }))}
            options={[
              ['', 'Select department'],
              ...departments.map((record) => [String(record.id), record.name] as [string, string]),
            ]}
          />
        ) : null}
        {values.assignment_type === 'location' ? (
          <SelectField
            label="Location"
            value={values.location_id}
            disabled={!canManage || isSaving}
            errors={fieldErrors.location_id}
            onChange={(value) => setValues((current) => ({ ...current, location_id: value }))}
            options={[
              ['', 'Select location'],
              ...locations.map((record) => [String(record.id), record.name] as [string, string]),
            ]}
          />
        ) : null}
        <Field label="Effective from" errors={fieldErrors.effective_from}>
          <Input
            type="date"
            value={values.effective_from}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, effective_from: event.target.value }))}
          />
        </Field>
        <Field label="Effective to" errors={fieldErrors.effective_to}>
          <Input
            type="date"
            value={values.effective_to}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, effective_to: event.target.value }))}
          />
        </Field>
        <SelectField
          label="Status"
          value={values.status}
          disabled={!canManage || isSaving}
          errors={fieldErrors.status}
          onChange={(value) =>
            setValues((current) => ({ ...current, status: value as ShiftAssignment['status'] }))
          }
          options={[
            ['active', 'Active'],
            ['inactive', 'Inactive'],
          ]}
        />
      </div>

      <Field label="Notes" errors={fieldErrors.notes}>
        <Textarea
          rows={4}
          value={values.notes}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, notes: event.target.value }))}
        />
      </Field>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          {assignment ? 'Save assignment' : 'Create assignment'}
        </Button>
      </div>
    </form>
  )
}

function ShiftRosterEditor({
  roster,
  shifts,
  employees,
  canManage,
  isSaving,
  onSave,
}: {
  roster: ShiftRoster | null
  shifts: Shift[]
  employees: Array<{ id: number; full_name: string; employee_code: string }>
  canManage: boolean
  isSaving: boolean
  onSave: (payload: ShiftRosterPayload & { status: ShiftRosterStatus }) => Promise<unknown>
}) {
  const [values, setValues] = useState({
    employee_id: roster ? String(roster.employee.id) : '',
    shift_id: roster ? String(roster.shift.id) : '',
    work_date: roster?.work_date ?? todayDate(),
    notes: roster?.notes ?? '',
    status: roster?.status ?? 'scheduled',
  })
  const [message, setMessage] = useState<string | null>(null)
  const [formError, setFormError] = useState<string | null>(null)
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({})

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setMessage(null)
    setFormError(null)
    setFieldErrors({})

    if (!values.employee_id || !values.shift_id || !values.work_date) {
      setFormError('Employee, shift, and work date are required.')
      return
    }

    try {
      await onSave({
        employee_id: Number(values.employee_id),
        shift_id: Number(values.shift_id),
        work_date: values.work_date,
        notes: values.notes.trim() || null,
        status: values.status,
      })
      setMessage(roster ? 'Roster entry updated.' : 'Roster entry scheduled.')
    } catch (caughtError) {
      const nextError = extractErrorState(caughtError)
      setFormError(nextError.message)
      setFieldErrors(nextError.fieldErrors)
    }
  }

  return (
    <form className="workspace-form" onSubmit={handleSubmit}>
      {!canManage ? <PermissionNotice copy="Roster scheduling requires roster-management access." /> : null}

      <div className="workspace-form-grid">
        <SelectField
          label="Employee"
          value={values.employee_id}
          disabled={!canManage || isSaving || Boolean(roster)}
          errors={fieldErrors.employee_id}
          onChange={(value) => setValues((current) => ({ ...current, employee_id: value }))}
          options={[
            ['', 'Select employee'],
            ...employees.map((record) => [String(record.id), `${record.full_name} (${record.employee_code})`] as [string, string]),
          ]}
        />
        <SelectField
          label="Shift"
          value={values.shift_id}
          disabled={!canManage || isSaving}
          errors={fieldErrors.shift_id}
          onChange={(value) => setValues((current) => ({ ...current, shift_id: value }))}
          options={[
            ['', 'Select shift'],
            ...shifts.map((record) => [String(record.id), `${record.name} (${record.code})`] as [string, string]),
          ]}
        />
        <Field label="Work date" errors={fieldErrors.work_date}>
          <Input
            type="date"
            value={values.work_date}
            disabled={!canManage || isSaving}
            onChange={(event) => setValues((current) => ({ ...current, work_date: event.target.value }))}
          />
        </Field>
        <SelectField
          label="Status"
          value={values.status}
          disabled={!canManage || isSaving}
          errors={fieldErrors.status}
          onChange={(value) => setValues((current) => ({ ...current, status: value as ShiftRosterStatus }))}
          options={[
            ['scheduled', 'Scheduled'],
            ['cancelled', 'Cancelled'],
          ]}
        />
      </div>

      <Field label="Notes" errors={fieldErrors.notes}>
        <Textarea
          rows={4}
          value={values.notes}
          disabled={!canManage || isSaving}
          onChange={(event) => setValues((current) => ({ ...current, notes: event.target.value }))}
        />
      </Field>

      <FormNotice error={formError} message={message} />

      <div className="workspace-actions">
        <Button type="submit" disabled={!canManage || isSaving}>
          {roster ? 'Save roster entry' : 'Schedule roster entry'}
        </Button>
      </div>
    </form>
  )
}

function SelectorTable({
  headers,
  rows,
}: {
  headers: string[]
  rows: Array<{
    id: number
    selected: boolean
    primary: string
    primaryMeta: string
    secondary: string
    badges: string[]
    meta: string
    actionLabel?: string
    actionVariant?: 'ghost' | 'secondary' | 'primary'
    onAction: () => void
  }>
}) {
  return (
    <div className="overflow-hidden rounded-xl border border-line bg-card shadow-[var(--shadow-sm)]">
      <table className="w-full text-sm">
        <colgroup>
          <col style={{ width: '22%' }} />
          <col style={{ width: '34%' }} />
          <col style={{ width: '20%' }} />
          <col style={{ width: '14%' }} />
          <col style={{ width: '10%' }} />
        </colgroup>
        <thead className="border-b border-line bg-panel-soft/70">
          <tr>
            {headers.map((header) => (
              <th
                scope="col"
                key={header}
                className="px-4 py-3 text-left text-[0.72rem] font-semibold uppercase tracking-[0.12em] text-text-subtle"
              >
                {header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody className="[&_tr:last-child]:border-b-0">
          {rows.map((row) => (
            <tr
              key={row.id}
              className={`border-b border-line-soft transition-colors hover:bg-panel-tint/70${
                row.selected ? ' bg-panel-tint' : ''
              }`}
            >
              <th scope="row" className="px-4 py-4 align-top">
                <strong className="block text-sm font-semibold text-foreground">{row.primary}</strong>
                <small className="mt-1 block text-xs text-muted-foreground">{row.primaryMeta}</small>
              </th>
              <td className="px-4 py-4 align-top text-sm text-muted-foreground">
                <p>{row.secondary}</p>
              </td>
              <td className="px-4 py-4 align-top">
                <div className="flex flex-wrap gap-2">
                  {row.badges.map((badge) => (
                    <Badge key={`${row.id}-${badge}`} variant="neutral">
                      {badge.replace(/_/g, ' ')}
                    </Badge>
                  ))}
                </div>
              </td>
              <td className="px-4 py-4 align-top">
                <small className="text-xs text-muted-foreground">{row.meta}</small>
              </td>
              <td className="px-4 py-4 align-top">
                <div className="flex flex-wrap gap-2">
                  <Button
                    variant={row.actionVariant ?? (row.selected ? 'ghost' : 'secondary')}
                    size="sm"
                    onClick={row.onAction}
                  >
                    {row.actionLabel ?? (row.selected ? 'Selected' : 'Inspect')}
                  </Button>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
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

function Field({
  label,
  children,
  errors,
}: {
  label: string
  children: ReactNode
  errors?: string[]
}) {
  return (
    <WorkspaceField label={label} error={errors?.[0]}>
      {children}
      {errors && errors.length > 1 ? <FieldErrors errors={errors.slice(1)} /> : null}
    </WorkspaceField>
  )
}

function SelectField({
  label,
  value,
  onChange,
  options,
  disabled,
  errors,
}: {
  label: string
  value: string
  onChange: (value: string) => void
  options: Array<[string, string]>
  disabled?: boolean
  errors?: string[]
}) {
  return <AppSelectField label={label} value={value} onChange={onChange} options={options} disabled={disabled} error={errors?.join(' ')} />
}

function FieldErrors({ errors }: { errors?: string[] }) {
  if (!errors?.length) {
    return null
  }

  return <small className="workspace-field__error">{errors.join(' ')}</small>
}

function extractErrorState(error: unknown) {
  if (error instanceof ApiRequestError) {
    return {
      message: error.message,
      fieldErrors: error.fieldErrors,
    }
  }

  return {
    message: (error as Error).message,
    fieldErrors: {},
  }
}

function formatMinutes(value: number) {
  if (value >= 60) {
    const hours = Math.floor(value / 60)
    const minutes = value % 60
    return minutes ? `${hours}h ${minutes}m` : `${hours}h`
  }

  return `${value}m`
}

function formatWeekendDays(rule: WeekendRule) {
  return rule.non_working_days
    .map((day) => weekendOptions.find((option) => option.value === day)?.label ?? String(day))
    .join(', ')
}

function formatHolidayScope(calendar: HolidayCalendar) {
  const parts = []

  if (calendar.location) {
    parts.push(calendar.location.name)
  }

  if (calendar.department) {
    parts.push(calendar.department.name)
  }

  return parts.length ? parts.join(' · ') : 'Tenant-wide'
}

function formatAssignmentScope(assignment: ShiftAssignment) {
  switch (assignment.assignment_type) {
    case 'employee':
      return assignment.employee?.full_name ?? 'Employee scope'
    case 'department':
      return assignment.department?.name ?? 'Department scope'
    case 'location':
      return assignment.location?.name ?? 'Location scope'
    default:
      return 'Scoped assignment'
  }
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

function todayDate() {
  return new Date().toISOString().slice(0, 10)
}
