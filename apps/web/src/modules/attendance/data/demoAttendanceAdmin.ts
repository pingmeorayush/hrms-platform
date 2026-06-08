import type { AccessSnapshot } from '../../access/types'
import { buildDemoEmployees } from '../../employees/data/demoEmployees'
import { buildDemoOrganizationWorkspace } from '../../organization/data/demoOrganizationWorkspace'
import type { AttendanceAdminWorkspaceData, AttendancePolicy, HolidayCalendar, Shift, ShiftAssignment, ShiftRoster } from '../types'

export function buildDemoAttendanceWorkspace(
  snapshot: AccessSnapshot | null,
): AttendanceAdminWorkspaceData {
  const organization = buildDemoOrganizationWorkspace(snapshot)
  const employees = buildDemoEmployees(snapshot).map((employee) => ({
    id: employee.id,
    employee_code: employee.employee_code,
    full_name: employee.full_name,
    email: employee.email,
  }))
  const bengaluruLocation = organization.locations[0] ?? null
  const remoteLocation = organization.locations[1] ?? null
  const peopleOps = organization.departments.find((record) => record.code === 'PEO') ?? organization.departments[0]
  const engineering = organization.departments.find((record) => record.code === 'ENG') ?? organization.departments[0]
  const generalShift = createShift({
    id: 1,
    code: 'GEN-IND',
    name: 'General office shift',
    description: 'Standard office shift for day operations.',
    start_time: '09:30',
    end_time: '18:30',
    break_duration_minutes: 60,
    grace_minutes: 10,
    working_hours_minutes: 480,
    is_overnight: false,
    status: 'active',
  })
  const earlyShift = createShift({
    id: 2,
    code: 'EARLY-OPS',
    name: 'Early operations shift',
    description: 'Shift used for facilities and reception coverage.',
    start_time: '07:00',
    end_time: '16:00',
    break_duration_minutes: 45,
    grace_minutes: 5,
    working_hours_minutes: 495,
    is_overnight: false,
    status: 'active',
  })
  const nightShift = createShift({
    id: 3,
    code: 'NIGHT-SUP',
    name: 'Night support shift',
    description: 'Overnight support roster for global coverage.',
    start_time: '21:00',
    end_time: '06:00',
    break_duration_minutes: 60,
    grace_minutes: 5,
    working_hours_minutes: 480,
    is_overnight: true,
    status: 'active',
  })

  const calendars: HolidayCalendar[] = [
    {
      id: 1,
      code: 'PHX-IND',
      name: 'Phoenix India',
      description: 'Default national and company holidays for India.',
      location: null,
      department: null,
      is_default: true,
      status: 'active',
      holidays: [
        {
          id: 11,
          holiday_calendar_id: 1,
          name: 'Republic Day',
          holiday_date: '2026-01-26',
          type: 'national',
          is_optional: false,
          description: 'National public holiday.',
          created_at: timestamp(300),
          updated_at: timestamp(300),
        },
        {
          id: 12,
          holiday_calendar_id: 1,
          name: 'Founder Day',
          holiday_date: '2026-08-19',
          type: 'company',
          is_optional: false,
          description: 'Company-wide celebration day.',
          created_at: timestamp(220),
          updated_at: timestamp(220),
        },
      ],
      created_at: timestamp(340),
      updated_at: timestamp(220),
    },
    {
      id: 2,
      code: 'BLR-PEO',
      name: 'Bengaluru People Ops',
      description: 'Regional and team-specific calendar for People Ops.',
      location: bengaluruLocation,
      department: peopleOps,
      is_default: false,
      status: 'active',
      holidays: [
        {
          id: 21,
          holiday_calendar_id: 2,
          name: 'People Ops offsite',
          holiday_date: '2026-07-17',
          type: 'company',
          is_optional: false,
          description: 'Reserved for the annual offsite and planning workshop.',
          created_at: timestamp(120),
          updated_at: timestamp(96),
        },
      ],
      created_at: timestamp(160),
      updated_at: timestamp(96),
    },
  ]

  const assignments: ShiftAssignment[] = [
    {
      id: 1,
      assignment_type: 'employee',
      shift: generalShift,
      employee: employees.find((employee) => employee.id === 1005) ?? employees[0] ?? null,
      department: null,
      location: null,
      effective_from: '2026-05-01',
      effective_to: null,
      notes: 'Cabin crew for people operations needs standard business hours.',
      status: 'active',
      created_at: timestamp(72),
      updated_at: timestamp(48),
    },
    {
      id: 2,
      assignment_type: 'department',
      shift: earlyShift,
      employee: null,
      department: engineering,
      location: null,
      effective_from: '2026-06-01',
      effective_to: null,
      notes: 'Engineering war-room coverage during migration month.',
      status: 'active',
      created_at: timestamp(64),
      updated_at: timestamp(40),
    },
    {
      id: 3,
      assignment_type: 'location',
      shift: nightShift,
      employee: null,
      department: null,
      location: remoteLocation,
      effective_from: '2026-06-10',
      effective_to: '2026-07-31',
      notes: 'Remote location support rotation for APAC handover.',
      status: 'active',
      created_at: timestamp(55),
      updated_at: timestamp(36),
    },
  ]

  const rosters: ShiftRoster[] = [
    {
      id: 1,
      employee: employees.find((employee) => employee.id === 1002) ?? employees[0],
      shift: generalShift,
      work_date: '2026-06-03',
      notes: 'Core office coverage.',
      status: 'scheduled',
      created_at: timestamp(20),
      updated_at: timestamp(20),
    },
    {
      id: 2,
      employee: employees.find((employee) => employee.id === 1003) ?? employees[1] ?? employees[0],
      shift: earlyShift,
      work_date: '2026-06-04',
      notes: 'Facilities overlap coverage for onboarding day.',
      status: 'scheduled',
      created_at: timestamp(18),
      updated_at: timestamp(18),
    },
    {
      id: 3,
      employee: employees.find((employee) => employee.id === 1005) ?? employees[2] ?? employees[0],
      shift: nightShift,
      work_date: '2026-06-05',
      notes: 'Cross-regional handoff coverage.',
      status: 'scheduled',
      created_at: timestamp(16),
      updated_at: timestamp(16),
    },
  ]

  const policy: AttendancePolicy = {
    id: 1,
    name: 'Phoenix standard attendance policy',
    working_hours_minutes: 480,
    grace_minutes: 10,
    late_after_minutes: 11,
    half_day_minutes: 240,
    overtime_eligible: true,
    overtime_after_minutes: 540,
    weekend_rule: {
      non_working_days: [0, 6],
    },
    work_from_home_allowed: true,
    enforce_geofence: true,
    allowed_radius_meters: 150,
    status: 'active',
    created_at: timestamp(480),
    updated_at: timestamp(24),
  }

  return {
    policy,
    holidayCalendars: calendars,
    shifts: [generalShift, earlyShift, nightShift],
    assignments,
    rosters,
    employees,
    departments: organization.departments,
    locations: organization.locations,
  }
}

function createShift(
  shift: Omit<Shift, 'created_at' | 'updated_at'> &
    Partial<Pick<Shift, 'created_at' | 'updated_at'>>,
): Shift {
  return {
    ...shift,
    created_at: shift.created_at ?? timestamp(200),
    updated_at: shift.updated_at ?? timestamp(48),
  }
}

function timestamp(hoursAgo: number) {
  return new Date(Date.now() - hoursAgo * 60 * 60 * 1000).toISOString()
}
