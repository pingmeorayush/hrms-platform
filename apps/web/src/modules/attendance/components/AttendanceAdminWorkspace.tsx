import { useMemo, useState } from 'react'
import { NavLink, useLocation } from 'react-router-dom'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../shared/ui/card'
import { Modal } from '../../../shared/ui/modal'
import { useOperationFeedback } from '../../../shared/ui/use-operation-feedback'
import { WorkspaceSelectionContext } from '../../../shared/ui/workspace-selection-context'
import {
  EmptyState,
  HolidayCalendarEditor,
  HolidayEditor,
  MetricCard,
  PolicyEditor,
  SelectorTable,
  ShiftAssignmentEditor,
  ShiftEditor,
  ShiftRosterEditor,
} from './AttendanceAdminEditors'
import { useAttendanceAdminWorkspace } from '../hooks/useAttendanceAdminWorkspace'
import type {
  HolidayCalendar,
  ShiftAssignment,
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

const attendanceWeekendOptions: Array<{ value: number; label: string }> = [
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
    .map((day) => attendanceWeekendOptions.find((option) => option.value === day)?.label ?? String(day))
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
