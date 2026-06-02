# Attendance

## Purpose

The Attendance module records working time, validates presence and exceptions, and produces payroll-ready attendance outcomes.

## Business Value

- Improves timekeeping accuracy
- Reduces manual correction effort
- Enables workforce visibility
- Produces dependable inputs for payroll

## In Scope

- Check-in and check-out
- Shift management and shift assignment
- Rosters and work schedules
- Attendance calculation and policy evaluation
- Work-from-home support
- Attendance corrections and approvals
- Holiday calendars, overtime handling, and attendance analytics

## Out Of Scope

- Payroll processing
- Leave processing

## Primary Actors

- Employee
- Manager
- HR Executive
- HR Manager
- Payroll Officer

## Core Workflows

- Attendance capture through web, mobile, biometric, kiosk, QR, or API channels
- Shift assignment and roster scheduling with effective dates
- Attendance calculation from timestamps, shifts, grace periods, and working-hour rules
- Attendance correction requests routed through manager and HR approval
- Payroll lock after finalized payroll closure

## Key Business Rules

- One check-in per employee per working day unless correction workflow is used
- Check-out requires a valid prior check-in
- Late status depends on shift start and configurable grace period
- Half day depends on configurable worked-hours threshold
- Overtime starts only after required working hours are completed
- Attendance becomes locked after payroll closure

## Core Entities

- `attendance_records`
- `attendance_policies`
- `shifts`
- `shift_assignments`
- `shift_rosters`
- `attendance_corrections`
- `holiday_calendars`

## Primary APIs

- `POST /api/v1/attendance/check-in`
- `POST /api/v1/attendance/check-out`
- `GET /api/v1/attendance`
- `GET /api/v1/attendance/{id}`
- `POST /api/v1/attendance/corrections`

## Dependencies

- Employee and organization master data
- Workflow and notification foundation
- Leave integration for leave-derived attendance statuses
- Audit logging and tenant isolation

## Related Sprints

- [Sprint 03: Attendance and Shift Operations](../sprints/sprint-03-attendance-shift-operations.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS Attendance Management Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
