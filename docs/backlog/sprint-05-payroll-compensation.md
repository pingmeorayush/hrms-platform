# Sprint 05 Backlog: Payroll and Compensation

## Scope Reference

- [Sprint 05 Plan](../sprints/sprint-05-payroll-compensation.md)
- [Payroll Module](../modules/payroll.md)
- [Compensation and Benefits Module](../modules/compensation-benefits.md)
- [Attendance Module](../modules/attendance.md)
- [Leave Management Module](../modules/leave-management.md)

## Epics

### EPIC S05-E1: Compensation Structure Foundation

Delivers the component, structure, and revision model required before payroll runs can be calculated reliably.

### EPIC S05-E2: Payroll Processing and Controls

Delivers payroll periods, payroll inputs, calculation runs, locking, and payslip generation.

### EPIC S05-E3: Payroll UI and Employee Access

Delivers the admin payroll workspace and the employee self-service payslip experience.

### EPIC S05-E4: Contract and Audit Readiness

Delivers published contracts, audit alignment, and quality gates for payroll operations.

## Ticket Index

| ID | Type | Priority | Summary | Depends On |
| --- | --- | --- | --- | --- |
| S05-001 | Story | P0 | Implement payroll periods, calendars, and run prerequisites | Sprint 04 complete |
| S05-002 | Story | P0 | Implement salary components and salary structure management | S05-001 |
| S05-003 | Story | P0 | Implement employee compensation assignment and salary revision history | S05-002, S02-003 |
| S05-004 | Story | P0 | Implement payroll input aggregation from attendance, leave, and manual adjustments | S05-001, S03-004, S04-004 |
| S05-005 | Story | P0 | Implement payroll calculation engine, run lifecycle, locking, and reopen controls | S05-003, S05-004, S01-012 |
| S05-006 | Story | P0 | Implement payslip generation and controlled payslip access | S05-005 |
| S05-007 | Story | P1 | Implement payroll admin run console and exception review workspace | S05-005 |
| S05-008 | Story | P1 | Implement employee payslip and compensation self-service screens | S05-006 |
| S05-009 | Story | P1 | Implement payroll variance, exception, and summary reporting views | S05-005, S05-006 |
| S05-010 | Story | P1 | Publish payroll and compensation OpenAPI contracts | S05-003, S05-005, S05-006, S01-013 |

## Ticket Details

### S05-001: Implement payroll periods, calendars, and run prerequisites

Type: Story  
Priority: P0

Description:

Create the payroll period and run-control baseline that governs when payroll can be prepared, calculated, and closed.

Dependencies:

- Sprint 04 complete

Acceptance Criteria:

- Payroll periods can be opened, prepared, and closed through controlled state transitions
- Duplicate or overlapping payroll runs are blocked by rule
- Run prerequisites are visible and auditable before payroll calculation begins

### S05-002: Implement salary components and salary structure management

Type: Story  
Priority: P0

Description:

Create the compensation configuration model for earnings, deductions, and formula-driven salary structures.

Dependencies:

- S05-001

Acceptance Criteria:

- Earnings and deduction components can be defined with the approved v1 rule fields
- Salary structures can be versioned and assigned consistently
- Formula evaluation inputs are explicit and testable

### S05-003: Implement employee compensation assignment and salary revision history

Type: Story  
Priority: P0

Description:

Attach salary structures to employees and preserve historical revisions for future payroll accuracy.

Dependencies:

- S05-002
- S02-003

Acceptance Criteria:

- Employee compensation records preserve effective dates and prior revisions
- Unauthorized users cannot view or change compensation data
- Compensation changes are auditable and available to payroll processing

### S05-004: Implement payroll input aggregation from attendance, leave, and manual adjustments

Type: Story  
Priority: P0

Description:

Collect the finalized operational inputs that payroll depends on and normalize them into a run-ready dataset.

Dependencies:

- S05-001
- S03-004
- S04-004

Acceptance Criteria:

- Approved attendance and leave outcomes can be consumed as payroll inputs
- Manual adjustments are traceable and permission-controlled
- Input snapshots are stable for a given payroll run attempt

### S05-005: Implement payroll calculation engine, run lifecycle, locking, and reopen controls

Type: Story  
Priority: P0

Description:

Implement the core payroll engine and the run controls that make payroll repeatable and governable.

Dependencies:

- S05-003
- S05-004
- S01-012

Acceptance Criteria:

- Gross-to-net payroll calculation is repeatable for the same inputs
- Locked payroll runs cannot be changed without approved reopen logic
- Payroll run actions are auditable and protected by permission

### S05-006: Implement payslip generation and controlled payslip access

Type: Story  
Priority: P0

Description:

Generate employee payslips from payroll outputs and expose them only through approved channels.

Dependencies:

- S05-005

Acceptance Criteria:

- Payslips can be generated for finalized payroll runs
- Employee access respects tenant, role, and payroll state rules
- Payslip downloads or views are auditable

### S05-007: Implement payroll admin run console and exception review workspace

Type: Story  
Priority: P1

Description:

Create the payroll operations workspace for preparing runs, reviewing failures, and closing payroll periods.

Dependencies:

- S05-005

Acceptance Criteria:

- Payroll admins can view run status, blockers, exceptions, and approval-sensitive actions in one workspace
- Lock, reopen, and rerun actions are permission-aware and clearly guarded in the UI
- Empty, processing, success, and failed-run states are covered in frontend tests

### S05-008: Implement employee payslip and compensation self-service screens

Type: Story  
Priority: P1

Description:

Create the employee-facing experience for secure payslip access and approved compensation visibility.

Dependencies:

- S05-006

Acceptance Criteria:

- Employees can view or download finalized payslips through the web UI
- Sensitive compensation fields remain hidden until payroll state and permission rules allow access
- UI covers no-payslip, finalized, and access-denied states

### S05-009: Implement payroll variance, exception, and summary reporting views

Type: Story  
Priority: P1

Description:

Create the payroll review surfaces that help admins inspect outliers, variances, and payroll summaries before release.

Dependencies:

- S05-005
- S05-006

Acceptance Criteria:

- Payroll admins can review exception counts, variance indicators, and run summaries through filtered screens
- Sensitive values remain permission-controlled
- Report and exception views are consistent with payroll run state and covered by UI tests

### S05-010: Publish payroll and compensation OpenAPI contracts

Type: Story  
Priority: P1

Description:

Publish the contract set for payroll periods, structures, compensation assignments, payroll runs, and payslip APIs.

Dependencies:

- S05-003
- S05-005
- S05-006
- S01-013

Acceptance Criteria:

- Core payroll and compensation endpoints are documented
- Shared schema and error conventions remain aligned with prior sprint contracts
- Contract files are version-controlled and linted in CI
