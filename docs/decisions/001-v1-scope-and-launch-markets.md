# DEC-001: V1 Scope and Launch Markets

## Status

Proposed

## Context

The specification set covers a very broad enterprise platform, including core HR, payroll, recruitment, learning, reporting, AI, mobile, integrations, and multi-country capabilities. Without a hard v1 boundary, delivery risk is high.

## Decision

PhoenixHRMS v1 will include:

- Authentication and MFA
- Multi-tenancy foundation
- RBAC and policy enforcement
- Workflow and approval MVP
- Audit logging and notifications baseline
- Organization management
- Employee management
- Attendance
- Leave
- Payroll
- Document management core
- ESS and MSS core flows
- Operational reporting for employee, attendance, leave, and payroll

PhoenixHRMS v1 will defer or limit:

- Advanced AI automation
- Full recruitment suite beyond planning-stage prototypes
- Full learning suite
- Deep mobile parity
- Integration marketplace
- Broad multi-country payroll
- Predictive analytics beyond operational dashboards

Launch market baseline for v1:

- Primary compliance and payroll design target: India
- Secondary geographies: not in v1 unless explicitly approved through a later expansion decision

## Rationale

- Payroll, leave, attendance, and employee record integrity are the core operational value of the product.
- India appears aligned with the source material, including examples like `IFSC`, `UPI`, `PF`, and `ESI`.
- Restricting geography early prevents rework in payroll, compliance, currency, and document rules.

## Consequences

- Globalization, mobile, AI, integrations, recruitment, and learning remain in the roadmap but are not required for first production release.
- Reporting must focus on operational trust rather than broad predictive ambition in v1.

## Affected Docs

- [Requirements Analysis](../07-requirements-analysis.md)
- [Roadmap](../06-roadmap.md)
- [Sprint 00](../sprints/sprint-00-program-alignment.md)
