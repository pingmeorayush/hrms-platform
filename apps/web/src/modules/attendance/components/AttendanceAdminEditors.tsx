import type { FormEvent, ReactNode } from 'react'
import { useState } from 'react'
import { ApiRequestError } from '../../../shared/api/http'
import { Badge } from '../../../shared/ui/badge'
import { Button } from '../../../shared/ui/button'
import { Input } from '../../../shared/ui/input'
import { SelectField as AppSelectField } from '../../../shared/ui/select-field'
import { Textarea } from '../../../shared/ui/textarea'
import { WorkspaceEmptyState, WorkspaceField } from '../../../shared/ui/workspace'
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
} from '../types'

type SelectorRow = {
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
}

type NamedRecord = {
  id: number
  name: string
}

type EmployeeOption = {
  id: number
  full_name: string
  employee_code: string
}

const attendanceWeekendOptions: Array<{ value: number; label: string }> = [
  { value: 0, label: 'Sunday' },
  { value: 1, label: 'Monday' },
  { value: 2, label: 'Tuesday' },
  { value: 3, label: 'Wednesday' },
  { value: 4, label: 'Thursday' },
  { value: 5, label: 'Friday' },
  { value: 6, label: 'Saturday' },
]

export function PolicyEditor({
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
        {attendanceWeekendOptions.map((option) => (
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

export function HolidayCalendarEditor({
  calendar,
  departments,
  locations,
  canManage,
  isSaving,
  onSave,
}: {
  calendar: HolidayCalendar | null
  departments: NamedRecord[]
  locations: NamedRecord[]
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

export function HolidayEditor({
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

export function ShiftEditor({
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

export function ShiftAssignmentEditor({
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
  employees: EmployeeOption[]
  departments: NamedRecord[]
  locations: NamedRecord[]
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

export function ShiftRosterEditor({
  roster,
  shifts,
  employees,
  canManage,
  isSaving,
  onSave,
}: {
  roster: ShiftRoster | null
  shifts: Shift[]
  employees: EmployeeOption[]
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

export function SelectorTable({
  headers,
  rows,
}: {
  headers: string[]
  rows: SelectorRow[]
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

export function MetricCard({ label, value, caption }: { label: string; value: string; caption: string }) {
  return (
    <article className="rounded-xl border border-line bg-card px-4 py-4 shadow-[var(--shadow-sm)]">
      <span className="block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-text-subtle">{label}</span>
      <strong className="mt-1 block text-lg font-semibold text-foreground">{value}</strong>
      <small className="mt-2 block text-sm leading-6 text-muted-foreground">{caption}</small>
    </article>
  )
}

export function EmptyState({ title, copy }: { title: string; copy: string }) {
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
  return (
    <AppSelectField
      label={label}
      value={value}
      onChange={onChange}
      options={options}
      disabled={disabled}
      error={errors?.join(' ')}
    />
  )
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

function todayDate() {
  return new Date().toISOString().slice(0, 10)
}
