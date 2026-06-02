# Leave Management

## Purpose

The Leave Management module manages policy-driven leave planning, accrual, requests, approvals, balances, and compliance-sensitive exceptions.

## Business Value

- Reduces manual leave administration
- Enforces policy consistently
- Improves planning visibility for managers and HR
- Feeds approved outcomes into attendance and payroll

## In Scope

- Leave types and leave policies
- Eligibility, accrual, carry forward, and encashment
- Leave balances and real-time availability
- Leave request workflows and cancellations
- Holiday integration, sandwich rules, hourly leave, and leave analytics

## Out Of Scope

- Payroll calculation
- Attendance capture

## Primary Actors

- Employee
- Manager
- Department Head
- HR

## Core Workflows

- Policy creation and assignment
- Accrual generation and balance updates
- Leave request submission and multi-level approval
- Cancellation and approval-controlled reversal
- Leave outcome propagation to attendance and payroll consumers

## Key Business Rules

- Employees cannot request leave beyond available balance
- Leave start date cannot be after end date
- Overlapping leave requests are not allowed
- Approved leave follows configurable approval workflows
- Pending leave can be cancelled directly; approved leave requires cancellation approval
- Carry-forward and annual reset behavior are tenant configurable

## Core Entities

- `leave_types`
- `leave_policies`
- `leave_balances`
- `leave_requests`
- `leave_accruals`
- `leave_workflows`
- `leave_holidays`

## Primary APIs

- `GET /api/v1/leave-requests`
- `POST /api/v1/leave-requests`
- `GET /api/v1/leave-requests/{id}`
- `PATCH /api/v1/leave-requests/{id}`
- `POST /api/v1/leave-requests/{id}/approve`
- `POST /api/v1/leave-requests/{id}/reject`

## Dependencies

- Employee hierarchy and organization data
- Workflow engine and notifications
- Holiday calendars and attendance integration
- Audit logging

## Related Sprints

- [Sprint 04: Leave Management and Manager Workflows](../sprints/sprint-04-leave-manager-workflows.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS Leave Management Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
