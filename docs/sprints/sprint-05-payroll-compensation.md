# Sprint 05: Payroll and Compensation

## Objective

Deliver the first production-grade payroll flow using finalized attendance, leave, and employee compensation inputs.

## Status

In Progress

## Primary Backlog IDs

- `PAY-001`
- `PAY-002`
- `PAY-003`
- `PAY-004`
- `PAY-005`
- `PAY-006`

## Module References

- [Payroll](../modules/payroll.md)
- [Compensation and Benefits](../modules/compensation-benefits.md)

## Backlog Detail

- [Sprint 05 Delivery Backlog](../backlog/sprint-05-payroll-compensation.md)

## Scope

- Payroll periods and payroll runs
- Salary structures and salary component definitions
- Formula-driven earnings and deductions
- Attendance and leave payroll inputs
- Payslip generation and payroll reporting baseline
- Compensation and benefits core setup where required for payroll

## Delivery Items

- Payroll engine MVP
- Salary structure and revision management
- Payroll run lifecycle with locking
- Payslip output generation
- Payroll exception and variance visibility for admins

## Dependencies

- Stable outputs from Sprints 02 to 04
- Sprint 01 audit, workflow, and permission controls

## Acceptance Criteria

- Payroll can be calculated for the defined v1 employee population
- Locked payroll periods cannot be modified without approved reopen logic
- Payslips are generated and accessible through controlled channels
- Payroll calculations are repeatable for the same inputs

## Test Focus

- Gross-to-net calculations
- LOP and overtime handling
- Mid-month join and exit logic
- Payroll locking and duplicate-run prevention

## Risks and Open Questions

- Payroll scope must match launch-country compliance only
- Advanced adjustments such as retro pay and final settlement may need partial rollout

## Current Delivery Note

Sprint 05 backend delivery now includes `S05-001` through `S05-006` in `apps/api`, covering payroll calendars, payroll periods, controlled `open`, `prepare`, and `close` transitions, tenant-scoped payroll run visibility, overlap protection, auditable prerequisite snapshots, salary component definitions, versioned salary structures, effective-dated employee compensation assignment history, stable run-linked payroll input snapshots for attendance, leave, and manual adjustments, the payroll calculation lifecycle with repeatable calculation, approval, locking, and reopen controls, and generated payslips with controlled list, view, and download access.
