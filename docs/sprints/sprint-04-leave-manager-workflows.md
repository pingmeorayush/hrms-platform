# Sprint 04: Leave Management and Manager Workflows

## Objective

Deliver policy-driven leave operations and the manager workflows needed to keep workforce planning and approvals reliable.

## Status

Completed

Sprint 04 web delivery is implemented in the current workspace with `S04-008`, `S04-006`, and `S04-007` live in `apps/web`. The shared `/leave` route now exposes HR leave policy setup, balance-rule editing, organization leave-calendar administration, employee balances, leave request submission, validation feedback, cancellation where allowed, manager approval decisions, hierarchy-scoped team availability, and routed leave review history in both demo and live API mode. Backend delivery is also implemented in `apps/api` through `S04-001`, `S04-002`, `S04-003`, `S04-004`, `S04-005`, and `S04-009`, which publish tenant-scoped leave type and leave policy configuration APIs, deterministic accrual-preview and projected encashment baselines, policy-aware leave balance plus ledger history APIs with self, manager, and HR read scopes, the leave request plus attendance-sync baseline, workflow-backed manager plus HR approval decisions, and the version-controlled Sprint 04 OpenAPI contract.

## Primary Backlog IDs

- `LEAVE-001`
- `LEAVE-002`
- `LEAVE-003`
- `MSS-001`

## Module References

- [Leave Management](../modules/leave-management.md)
- [Employee Management](../modules/employee-management.md)

## Backlog Detail

- [Sprint 04 Delivery Backlog](../backlog/sprint-04-leave-manager-workflows.md)
- [Frontend Delivery Order for Sprints 02 to 04](../backlog/frontend-delivery-order-sprints-02-to-04.md)

## Scope

- Leave types and policy configuration
- Leave eligibility, accrual, carry forward, and encashment basics
- Leave requests, approvals, rejection, and cancellation
- Manager team visibility and approval queues
- Leave calendar and history views

## Delivery Items

- Leave policy and balance APIs
- Leave request and approval workflow
- Manager self-service approval views
- Calendar and balance experiences for employees and managers

## Dependencies

- Sprint 01 workflow and notifications
- Sprint 02 employee hierarchy
- Sprint 03 holiday and attendance dependencies where applicable

## Acceptance Criteria

- Employees can request leave against policy-controlled balances
- Overlapping and invalid leave requests are rejected correctly
- Managers can approve or reject requests within configured workflows
- Leave approvals update downstream attendance-related states where required

## Test Focus

- Balance validation
- Overlap and date validation
- Multi-level approval flow
- Cancellation and re-approval logic

## Risks and Open Questions

- Sandwich rules and hourly leave can add complexity; keep them configurable and contained
- Leave-to-attendance synchronization must be deterministic
