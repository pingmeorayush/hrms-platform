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
- Approved leave follows configurable manager and HR approval workflows
- Pending and approved leave can be cancelled directly in the v1 baseline while retaining audit, workflow, and balance history
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

- `GET /api/v1/leave/types`
- `POST /api/v1/leave/types`
- `PATCH /api/v1/leave/types/{leaveTypeId}`
- `GET /api/v1/leave/policies`
- `POST /api/v1/leave/policies`
- `PATCH /api/v1/leave/policies/{leavePolicyId}`
- `POST /api/v1/leave/policies/{leavePolicyId}/accrual-preview`
- `GET /api/v1/leave/balances`
- `GET /api/v1/leave/balances/{employeeId}`
- `GET /api/v1/leave/requests`
- `POST /api/v1/leave/requests`
- `GET /api/v1/leave/requests/{leaveRequestId}`
- `PATCH /api/v1/leave/requests/{leaveRequestId}`
- `apps/api/openapi/sprint-04-leave-manager-workflows.yaml`

## Dependencies

- Employee hierarchy and organization data
- Workflow engine and notifications
- Holiday calendars and attendance integration
- Audit logging

## Implementation Notes

- Sprint 04 web delivery now uses a shared `/leave` route in `apps/web` that exposes the HR admin setup slice for leave types, balance rules, and organization leave-calendar browsing alongside employee leave balances, request submission, cancellation where allowed, manager approval decisions, and team availability in both demo and live API mode.
- Sprint 04 backend delivery has started with tenant-scoped leave type and leave policy configuration APIs, including eligibility-rule storage, audit events, and policy version increments on update.
- Sprint 04 backend now also includes a deterministic accrual-preview baseline that persists tenant-scoped leave accrual snapshots and projected encashment outcomes for the same employee, policy version, and cycle input.
- Sprint 04 backend now also includes policy-aware leave balance snapshots and a ledger-style balance history derived from accrual projections, with self, manager, and HR read scopes on the published balance APIs.
- Sprint 04 backend now also includes leave request submission, overlap and balance validation, direct cancellation, leave booking and release entries in the balance ledger, manager and self request visibility scopes, and deterministic attendance synchronization for auto-approved leave.
- Sprint 04 backend now also includes workflow-backed leave approvals with employee-manager routing, HR final approval, decision comments, generic workflow notifications, and leave-state synchronization from workflow transitions.
- Sprint 04 now also has a published OpenAPI 3.1 contract in `apps/api/openapi/sprint-04-leave-manager-workflows.yaml`, and `npm run openapi:lint` validates it alongside the earlier sprint contracts.
- Live API mode is now enabled for the Sprint 4 leave module across policy setup, balance reads, employee requests, cancellations, and manager approval actions, while the shared `/leave` route continues to derive calendar views from the published request APIs.

## Related Sprints

- [Sprint 04: Leave Management and Manager Workflows](../sprints/sprint-04-leave-manager-workflows.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS Leave Management Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
