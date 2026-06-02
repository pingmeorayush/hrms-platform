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

- `GET /api/v1/payroll-periods`
- `POST /api/v1/payroll-periods`
- `POST /api/v1/payroll/run`
- `GET /api/v1/payroll-runs`
- `GET /api/v1/payroll-runs/{id}`
- `GET /api/v1/payslips/{id}`

## Dependencies

- Employee and organization master data
- Attendance and leave completion
- Workflow, permission, and audit foundation
- Compensation and benefits configuration

## Related Sprints

- [Sprint 05: Payroll and Compensation](../sprints/sprint-05-payroll-compensation.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS Payroll Management Module Specification.txt`
- `docs/files/PhoenixHRMS Compensation & Benefits Management Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
