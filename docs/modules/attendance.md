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
- Attendance policy and holiday configuration must remain tenant-scoped and auditable
- Default holiday calendars cannot be scoped to a specific location or department
- Shift assignments cannot overlap for the same active employee or organization scope
- Rosters must remain unique per employee and work date
- Overnight shifts are represented explicitly when end time falls before start time
- Recalculation can synthesize `absent`, `holiday`, and `weekend` outcomes for past dates with no captured attendance
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

- `GET /api/v1/attendance/policy`
- `PATCH /api/v1/attendance/policy`
- `GET /api/v1/attendance`
- `GET /api/v1/attendance/{id}`
- `POST /api/v1/attendance/check-in`
- `POST /api/v1/attendance/check-out`
- `POST /api/v1/attendance/recalculate`
- `GET /api/v1/attendance/operational-review`
- `GET /api/v1/attendance/pending-exceptions`
- `GET /api/v1/attendance/holiday-calendars`
- `POST /api/v1/attendance/holiday-calendars`
- `PATCH /api/v1/attendance/holiday-calendars/{id}`
- `POST /api/v1/attendance/holiday-calendars/{id}/holidays`
- `PATCH /api/v1/attendance/holiday-calendars/{id}/holidays/{holidayId}`
- `GET /api/v1/attendance/shifts`
- `POST /api/v1/attendance/shifts`
- `PATCH /api/v1/attendance/shifts/{id}`
- `GET /api/v1/attendance/shift-assignments`
- `POST /api/v1/attendance/shift-assignments`
- `PATCH /api/v1/attendance/shift-assignments/{id}`
- `GET /api/v1/attendance/rosters`
- `POST /api/v1/attendance/rosters`
- `PATCH /api/v1/attendance/rosters/{id}`
- `GET /api/v1/attendance/corrections`
- `POST /api/v1/attendance/corrections`
- `PATCH /api/v1/attendance/corrections/{id}`

## Dependencies

- Employee and organization master data
- Workflow and notification foundation
- Leave integration for leave-derived attendance statuses
- Audit logging and tenant isolation
- Published Sprint 03 contract: `apps/api/openapi/sprint-03-attendance-shift-operations.yaml`

## Implementation Notes

- Sprint 03 currently includes tenant-scoped attendance capture APIs, a daily attendance calculation engine, attendance policy APIs, holiday calendars, shift definitions, effective-dated shift assignments, roster scheduling, correction approvals, operational review surfaces, and a published OpenAPI contract covering the full attendance launch slice.
- The current web module exposes a shared `/attendance` route that combines employee self-service capture and history, manager and HR operational review, correction decisioning, and admin setup tabs for policy, holiday, shift, assignment, and roster management.
- Shift assignments support employee, department, and location scope, with overlap protection for active ranges.
- Roster scheduling supports batch creation and update with conflict detection for duplicate employee and work-date combinations.
- Attendance capture is self-service for linked employees, stores optional device and geolocation metadata on both check-in and check-out, and exposes scoped read access for self, managers, and HR reviewers.
- Daily attendance calculation derives net worked minutes after configured shift breaks, flags late and half-day outcomes, computes overtime eligibility, and preserves holiday and weekend status on non-working days.
- Manual recalculation supports deterministic reprocessing for a date range, updating existing daily outcomes and backfilling past `absent`, `holiday`, or `weekend` records when no raw capture exists.
- Correction requests preserve original attendance values separately from corrected values, route manager then HR approval through the shared workflow engine, and apply approved timestamps back to the daily record with recalculation plus audit history.
- Operational review endpoints expose a current-window or requested-date attendance view for HR and managers, including late, absent, incomplete, and pending-correction exceptions plus the scoped pending correction queue.

## Related Sprints

- [Sprint 03: Attendance and Shift Operations](../sprints/sprint-03-attendance-shift-operations.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS Attendance Management Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
