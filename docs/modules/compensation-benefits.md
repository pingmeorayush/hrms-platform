# Compensation and Benefits

## Purpose

The Compensation and Benefits module supports salary structures, compensation cycles, merit reviews, bonuses, incentives, benefits administration, rewards, and total rewards visibility.

## Business Value

- Improves fairness and transparency in reward programs
- Supports performance-linked compensation decisions
- Provides planning and budgeting controls
- Extends payroll with richer compensation context

## In Scope

- Compensation structures, grades, bands, and ranges
- Compensation planning and review cycles
- Merit increases, promotions, bonuses, incentives, and variable pay
- Benefits, insurance, retirement, flexible benefits, and recognition
- ESOP and equity tracking
- Compensation analytics and total rewards statements

## Out Of Scope

- Detailed payroll execution itself
- External market-data platforms unless integrated explicitly

## Primary Actors

- Compensation Team
- HR
- Manager
- Finance
- Leadership
- Employee

## Core Workflows

- Compensation cycle planning, review, approval, communication, and implementation
- Salary review and merit-increase evaluation
- Bonus and incentive approval and payout tracking
- Benefits enrollment and utilization tracking
- Recognition and total rewards statement delivery

## Key Rules

- Pay governance should align with grade, band, level, country, and range
- Compensation changes require approval controls and audit logging
- Sensitive salary data requires field-level security
- Retention and compliance reporting apply to compensation records

## Core Entities

- `compensation_structures`
- `pay_grades`
- `salary_ranges`
- `employee_compensation`
- `compensation_cycles`
- `salary_reviews`
- `benefit_plans`
- `benefit_enrollments`

## Primary APIs

- `GET /compensation`
- `POST /compensation`
- `GET /salary-reviews`
- `POST /salary-reviews`
- `GET /benefits`
- `POST /benefits/enrollments`

## Dependencies

- Employee, performance, payroll, and reporting modules
- Workflow, permission, and audit foundation

## Implementation Notes

- Sprint 05 backend delivery now includes tenant-scoped salary component definitions and versioned salary structures in `apps/api`.
- The current baseline supports earning, deduction, and employer-contribution components with fixed, percentage, and expression formula styles, plus explicit formula inputs for structure lines.
- Salary structure updates create a new version record and supersede the prior version so employee compensation history can later reference immutable structure revisions.
- Sprint 05 now also includes employee compensation assignment records with effective dates, revision reasons, prior-revision links, and component snapshots so payroll can consume historical compensation safely.
- Those compensation snapshots now directly feed locked-run payslip generation, so finalized payroll artifacts can be reproduced from the same effective-dated structure data that payroll calculation used.

## Related Sprints

- [Sprint 05: Payroll and Compensation](../sprints/sprint-05-payroll-compensation.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS Compensation & Benefits Management Module Specification.txt`
