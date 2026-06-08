# Payroll

## Purpose

The Payroll module calculates, validates, approves, locks, and distributes employee compensation using trusted employee, attendance, leave, and policy inputs.

## Business Value

- Automates salary processing
- Improves financial accuracy and repeatability
- Supports statutory compliance and auditability
- Produces employee-facing payslip outputs

## In Scope

- Payroll periods and payroll runs
- Salary structures and salary components
- Earnings, deductions, employer contributions, and tax handling
- Attendance and leave payroll inputs
- LOP, overtime, incentives, reimbursements, and final outputs
- Payslips, payroll reports, and payroll analytics

## Out Of Scope

- Accounting ledger posting
- Bank disbursement processing

## Primary Actors

- Payroll Officer
- HR Manager
- Finance Manager
- Employee

## Core Workflows

- Payroll period setup and run lifecycle
- Salary structure assignment and revision management
- Attendance and leave import into payroll inputs
- Calculation, validation, approval, lock, and payslip generation
- Controlled handling for adjustments, arrears, and final settlement

## Key Business Rules

- Completed payroll periods become immutable when locked
- Supported frequencies include monthly, weekly, biweekly, and custom cycles
- Payroll calculation depends on finalized attendance and approved leave
- LOP is policy-driven for unauthorized absence or insufficient leave
- Mid-month join and exit require proration
- Duplicate payroll for the same employee and period is prohibited
- Finalized payroll must generate downloadable payslips

## Core Entities

- `payroll_periods`
- `payroll_runs`
- `salary_structures`
- `salary_components`
- `payroll_inputs`
- `payslips`
- `payroll_adjustments`

## Primary APIs

- `GET /api/v1/payroll/calendars`
- `POST /api/v1/payroll/calendars`
- `PATCH /api/v1/payroll/calendars/{payrollCalendarId}`
- `GET /api/v1/payroll/periods`
- `POST /api/v1/payroll/periods`
- `GET /api/v1/payroll/periods/{payrollPeriodId}`
- `POST /api/v1/payroll/periods/{payrollPeriodId}/open`
- `POST /api/v1/payroll/periods/{payrollPeriodId}/prepare`
- `POST /api/v1/payroll/periods/{payrollPeriodId}/close`
- `GET /api/v1/payroll/runs`
- `GET /api/v1/payroll/runs/{payrollRunId}`
- `POST /api/v1/payroll/runs/{payrollRunId}/calculate`
- `POST /api/v1/payroll/runs/{payrollRunId}/approve`
- `POST /api/v1/payroll/runs/{payrollRunId}/lock`
- `POST /api/v1/payroll/runs/{payrollRunId}/reopen`
- `POST /api/v1/payroll/runs/{payrollRunId}/generate-payslips`
- `GET /api/v1/payroll/runs/{payrollRunId}/inputs`
- `GET /api/v1/payroll/runs/{payrollRunId}/adjustments`
- `POST /api/v1/payroll/runs/{payrollRunId}/adjustments`
- `PATCH /api/v1/payroll/runs/{payrollRunId}/adjustments/{payrollAdjustmentId}`
- `GET /api/v1/payroll/payslips`
- `GET /api/v1/payroll/payslips/{payslipId}`
- `GET /api/v1/payroll/payslips/{payslipId}/download`
- `GET /api/v1/payroll/salary-components`
- `POST /api/v1/payroll/salary-components`
- `PATCH /api/v1/payroll/salary-components/{salaryComponentId}`
- `GET /api/v1/payroll/salary-structures`
- `POST /api/v1/payroll/salary-structures`
- `PATCH /api/v1/payroll/salary-structures/{salaryStructureId}`
- `GET /api/v1/payroll/compensations`
- `POST /api/v1/payroll/compensations`
- `GET /api/v1/payroll/compensations/{employeeId}`

## Dependencies

- Employee and organization master data
- Attendance and leave completion
- Workflow, permission, and audit foundation
- Compensation and benefits configuration

## Implementation Notes

- Sprint 05 backend delivery has now started with tenant-scoped payroll calendars, payroll periods, and payroll preparation runs under `apps/api`.
- The current `S05-001` baseline exposes payroll calendar CRUD, payroll period creation plus `open`, `prepare`, and `close` transitions, and payroll run visibility with stored prerequisite snapshots.
- Preparation snapshots currently audit and surface active employee coverage, attendance finalization, pending leave blockers, and compensation-assignment readiness so payroll operations can see blockers before gross-to-net calculation exists.
- Compensation assignment readiness intentionally remains a blocking prerequisite until active employees have effective-dated compensation assignments on record for the payroll period being prepared.
- Sprint 05 now also includes salary component configuration, versioned salary structure management, and immutable employee compensation assignment history with structure-component snapshots that later payroll calculation can consume directly.
- Sprint 05 now also includes run-linked payroll input snapshots and payroll adjustments, so prepared runs can persist stable attendance, leave, and manual-adjustment inputs instead of recalculating them ad hoc during every read.
- Sprint 05 now also includes the `S05-005` payroll calculation lifecycle: prepared runs can be calculated repeatably from stored inputs and compensation snapshots, approved, locked for period close, and reopened with an audited reason before recalculation.
- Payroll calculation currently persists per-employee `payroll_items` with gross salary, deductions, net salary, overtime earnings, employer cost, validation errors, and the input snapshot used for repeatable reruns and exception review.
- Sprint 05 now also includes generated `payslips` with frozen HTML snapshots, list/show/download APIs, self-service access for linked employees, and tenant-wide payroll visibility for payroll-authorized roles.
- Reopening a locked payroll run now revokes previously generated payslips for that run, so stale finalized artifacts cannot remain accessible after payroll is intentionally moved back to an editable state.

## Related Sprints

- [Sprint 05: Payroll and Compensation](../sprints/sprint-05-payroll-compensation.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS Payroll Management Module Specification.txt`
- `docs/files/PhoenixHRMS Compensation & Benefits Management Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
