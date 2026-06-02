# Sprint 04: Leave Management and Manager Workflows

## Objective

Deliver policy-driven leave operations and the manager workflows needed to keep workforce planning and approvals reliable.

## Primary Backlog IDs

- `LEAVE-001`
- `LEAVE-002`
- `LEAVE-003`
- `MSS-001`

## Module References

- [Leave Management](../modules/leave-management.md)
- [Employee Management](../modules/employee-management.md)

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
