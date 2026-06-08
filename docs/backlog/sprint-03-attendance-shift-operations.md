# Sprint 03 Backlog: Attendance and Shift Operations

## Scope Reference

- [Sprint 03 Plan](../sprints/sprint-03-attendance-shift-operations.md)
- [Attendance Module](../modules/attendance.md)
- [Employee Management Module](../modules/employee-management.md)
- [Platform Foundation](../modules/platform-foundation.md)
- [Frontend Delivery Order for Sprints 02 to 04](./frontend-delivery-order-sprints-02-to-04.md)

## Epics

### EPIC S03-E1: Attendance Capture and Scheduling Foundation

Delivers the tenant-scoped schedule, policy, and time-capture baseline required for reliable attendance records.

### EPIC S03-E2: Attendance Calculation and Exception Control

Delivers payroll-ready status derivation, correction approvals, and auditable exception handling.

### EPIC S03-E3: Attendance Visibility and Contract Publication

Delivers operational review surfaces and the first published attendance API contract set.

### EPIC S03-E4: Attendance UI Operations

Delivers the admin, manager, and employee web experiences required to operate the Sprint 03 attendance baseline.

## Ticket Index

| ID | Type | Priority | Summary | Depends On |
| --- | --- | --- | --- | --- |
| S03-001 | Story | P0 | Implement attendance policy, holiday calendar, and working-day configuration baseline | Sprint 02 complete |
| S03-002 | Story | P0 | Implement shift definitions, assignments, and roster scheduling model | S03-001, S02-001, S02-003 |
| S03-003 | Story | P0 | Implement attendance check-in, check-out, and read APIs with metadata capture | S03-001, S03-002, S02-003 |
| S03-004 | Story | P0 | Implement attendance calculation and status engine MVP | S03-001, S03-002, S03-003 |
| S03-005 | Story | P1 | Implement attendance correction request and approval workflow | S03-003, S03-004, S01-009 |
| S03-006 | Story | P1 | Implement operational attendance review and pending exception views | S03-003, S03-004, S03-005, S02-002 |
| S03-007 | Story | P1 | Publish attendance and shift OpenAPI contracts | S03-001, S03-003, S01-013 |
| S03-008 | Story | P1 | Implement attendance policy, holiday, shift, and roster admin screens | S03-001, S03-002 |
| S03-009 | Story | P1 | Implement employee attendance capture and personal history screens | S03-003, S03-004 |
| S03-010 | Story | P1 | Implement correction queue and operational attendance review workspace | S03-005, S03-006 |

## Ticket Details

### S03-001: Implement attendance policy, holiday calendar, and working-day configuration baseline

Type: Story  
Priority: P0

Description:

Create the tenant-scoped policy layer that controls working hours, grace rules, half-day thresholds, overtime eligibility, weekends, and holiday applicability for attendance processing.

Dependencies:

- Sprint 02 complete

Acceptance Criteria:

- Tenant admins can manage the v1 attendance policy fields required for status calculation
- Holiday calendars can be defined per tenant with location or department applicability where required
- Weekend and working-day rules are represented in a durable, tenant-scoped model
- Policy and calendar changes are auditable

### S03-002: Implement shift definitions, assignments, and roster scheduling model

Type: Story  
Priority: P0

Description:

Create the scheduling structures that determine when employees are expected to work, including effective-dated assignments and roster-driven rotations.

Dependencies:

- S03-001
- S02-001
- S02-003

Acceptance Criteria:

- Shift definitions support start time, end time, break duration, grace minutes, and working-hour expectations
- Effective-dated shift assignments can be created for employees and approved organization scopes where defined
- Rosters support weekly or monthly scheduling with conflict-detection baseline
- Overnight shift handling is represented explicitly in the data model and validation rules

### S03-003: Implement attendance check-in, check-out, and read APIs with metadata capture

Type: Story  
Priority: P0

Description:

Deliver the launch attendance-capture path for employees and authorized reviewers using the sprint-approved channels and metadata policy.

Dependencies:

- S03-001
- S03-002
- S02-003

Acceptance Criteria:

- Active employees can check in and check out through the Sprint 03 launch-supported channels
- Duplicate check-in, missing-prior-check-in, and other invalid capture actions are rejected by rule
- Timestamp, IP, device, and optional geolocation metadata are stored when supplied and auditable
- Tenant-scoped attendance list and detail endpoints exist for self, manager, and HR review paths where authorized

### S03-004: Implement attendance calculation and status engine MVP

Type: Story  
Priority: P0

Description:

Derive payroll-ready attendance outcomes from raw timestamps, schedules, holidays, weekends, and policy thresholds.

Dependencies:

- S03-001
- S03-002
- S03-003

Acceptance Criteria:

- Worked minutes, late status, half day, overtime, holiday, weekend, and absent outcomes are calculated consistently
- Overnight shifts, incomplete attendance, and non-working-day scenarios follow defined fallback behavior
- Recalculation is deterministic for the same attendance inputs and active policy version
- Attendance processing performance remains within the agreed NFR range for the Sprint 03 baseline

### S03-005: Implement attendance correction request and approval workflow

Type: Story  
Priority: P1

Description:

Support auditable attendance corrections without overwriting the original captured record, using the workflow foundation built in Sprint 01.

Dependencies:

- S03-003
- S03-004
- S01-009

Acceptance Criteria:

- Employees can submit attendance correction requests with corrected values and reason
- Original captured values are preserved separately from corrected values
- Manager and HR approval history is retained, visible, and auditable
- Approved corrections trigger attendance recalculation and notifications

### S03-006: Implement operational attendance review and pending exception views

Type: Story  
Priority: P1

Description:

Provide the minimum operational visibility HR and managers need to monitor daily attendance outcomes and correction queues.

Dependencies:

- S03-003
- S03-004
- S03-005
- S02-002

Acceptance Criteria:

- HR users can view present, absent, late, and pending-correction attendance states for the current operational window
- Managers can review scoped team attendance and pending exceptions where hierarchy access applies
- Views remain tenant-scoped and permission-controlled
- Advanced analytics remain out of scope for this sprint and are not required for acceptance

### S03-007: Publish attendance and shift OpenAPI contracts

Type: Story  
Priority: P1

Description:

Publish the contract definitions for attendance capture, correction, shift, and roster APIs so frontend and integration work can proceed against version-controlled schemas.

Dependencies:

- S03-001
- S03-003
- S01-013

Acceptance Criteria:

- Core attendance, correction, shift, and roster endpoints are documented
- Shared schema conventions are applied consistently with prior sprint contracts
- Contract changes are version-controlled, reviewable, and linted in CI

### S03-008: Implement attendance policy, holiday, shift, and roster admin screens

Type: Story  
Priority: P1

Description:

Create the web admin experience for managing attendance policy, holiday calendars, shift definitions, assignments, and roster schedules.

Dependencies:

- S03-001
- S03-002

Acceptance Criteria:

- Authorized admins can manage attendance policies, holidays, shifts, assignments, and rosters through web forms and list views
- Conflict, overlap, and validation errors are represented clearly in the UI
- UI states cover empty, loading, schedule-conflict, and permission-denied scenarios

### S03-009: Implement employee attendance capture and personal history screens

Type: Story  
Priority: P1

Description:

Create the employee-facing attendance capture experience for check-in, check-out, and personal attendance history review.

Dependencies:

- S03-003
- S03-004

Acceptance Criteria:

- Employees can check in and check out through the web UI with clear success and validation feedback
- Personal history views show captured timestamps, derived statuses, and correction availability where applicable
- Attendance capture states, duplicate prevention feedback, and empty-history states are covered in frontend tests

### S03-010: Implement correction queue and operational attendance review workspace

Type: Story  
Priority: P1

Description:

Create the manager and HR workspace for reviewing daily attendance outcomes, pending exceptions, and correction approvals.

Dependencies:

- S03-005
- S03-006

Acceptance Criteria:

- Managers and HR users can review scoped attendance exceptions and correction requests through role-appropriate dashboards
- Approved, rejected, pending, and recalculated states are visible with audit-aware history
- Team-scope and tenant-scope visibility rules are enforced consistently in the UI
