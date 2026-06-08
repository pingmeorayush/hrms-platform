# Sprint 03: Attendance and Shift Operations

## Objective

Deliver payroll-ready attendance operations with trusted time capture, shift control, and correction workflows.

## Status

Backend slice completed and verified. Sprint 03 web delivery is also completed in the current workspace.

Sprint 03 is now complete end to end for the current launch slice. The backend has `S03-001` through `S03-007` in place, and the frontend now has `S03-008` through `S03-010` implemented in `apps/web`. The routed attendance module covers attendance policy management, holiday calendars, shift definitions, effective-dated assignments, roster scheduling, employee check-in and check-out, personal attendance history, correction entry points, manager and HR operational review, and scoped correction decision queues. The recommended v1 baseline still limits launch time-capture channels to web and authenticated API flows, keeps geo, IP, and device metadata capture in scope, and defers biometric sync, kiosk, QR, mobile offline capture, timesheets, and advanced attendance analytics until later delivery.

## Primary Backlog IDs

- `ATT-001`
- `ATT-002`
- `ATT-003`
- `ATT-004`

## Module References

- [Attendance](../modules/attendance.md)
- [Employee Management](../modules/employee-management.md)

## Backlog Detail

- [Sprint 03 Delivery Backlog](../backlog/sprint-03-attendance-shift-operations.md)
- [Frontend Delivery Order for Sprints 02 to 04](../backlog/frontend-delivery-order-sprints-02-to-04.md)

## Scope

- Attendance check-in and check-out
- Attendance policy, holiday-calendar, and working-day configuration baseline
- Shift definitions and assignments
- Roster scheduling
- Attendance status calculation
- Late, half-day, holiday, weekend, and overtime rules
- Attendance corrections and approvals

## Delivery Items

- Attendance capture APIs
- Shift, roster, and policy management APIs and screens
- Attendance calculation engine MVP
- Correction workflow with original-value preservation
- Operational attendance review views for HR and managers

## Dependencies

- Sprint 02 employee and organization data
- Sprint 01 workflow and notification foundation

## Acceptance Criteria

- Active employees can record attendance through supported channels
- Duplicate or invalid attendance actions are blocked by rule
- Attendance statuses are calculated consistently from policy
- Correction requests retain original and corrected values with full audit trail

## Planning Notes

- Sprint 03 launch-supported capture channels should be web and authenticated API only
- Geo, IP, and device metadata capture is in scope; hard geofence blocking should remain policy-driven and can begin as a flag or exception state rather than a universal hard stop
- Holiday-calendar and weekend-rule support are part of the Sprint 03 baseline because later leave and payroll flows depend on them
- Timesheets, break management, biometric ingestion, and advanced attendance analytics are intentionally deferred from the Sprint 03 implementation baseline

## Progress Notes

- `S03-001` is implemented in the backend with attendance policy and holiday-configuration endpoints, audit coverage, automated tests, and an initial Sprint 03 OpenAPI contract.
- `S03-002` is implemented in the backend with shift definition, assignment, and roster endpoints, including overnight-shift handling, active-assignment overlap validation, roster conflict detection, audit coverage, and contract publication.
- `S03-003` is implemented in the backend with check-in, check-out, attendance list/detail endpoints, linked-employee validation, metadata capture for IP, user agent, device, and optional geolocation, plus self, manager, and HR review scopes.
- `S03-004` is implemented in the backend with deterministic daily attendance calculation, net worked-minute derivation after shift breaks, late and half-day rules, overtime calculation, overnight-shift support, holiday and weekend fallback outcomes, and manual recalculation for past dates.
- `S03-005` is implemented in the backend with employee correction submission, original-versus-corrected value preservation, manager then HR workflow approval, approval-history visibility, notification hooks, and approved-correction recalculation.
- `S03-006` is implemented in the backend with operational attendance review endpoints for the selected attendance window, scoped manager team views, tenant-wide HR visibility, pending exception queues, and explicit audit events for the review surfaces.
- `S03-007` is implemented with a version-controlled Sprint 03 OpenAPI 3.1 contract covering attendance capture, correction, operational review, policy, holiday, shift, assignment, and roster APIs, plus inventory and lint workflow alignment in CI.
- `S03-008` is implemented in the frontend with a routed `/attendance` admin workspace for attendance policy, holiday calendars, shift definitions, effective-dated assignments, and roster scheduling, including demo-mode conflict feedback and live API wiring.
- `S03-009` is implemented in the frontend with employee self-service check-in and check-out actions, personal attendance history, derived status visibility, correction entry points, empty-state handling, and route-level coverage inside the shared attendance workspace.
- `S03-010` is implemented in the frontend with manager and HR operational review dashboards, scoped pending-exception queues, correction decision history, approve or reject or request-changes actions, and demo plus live wiring inside the shared attendance workspace.

## Test Focus

- Check-in and check-out rules
- Shift edge cases and overnight scenarios
- Geo or device metadata capture if enabled
- Correction approvals and audit history

## Risks and Open Questions

- Overtime policy complexity can grow quickly if not constrained for v1
- Manager-versus-HR correction approval depth should stay aligned with the existing workflow MVP rather than introducing a parallel approval engine
- Attendance calculation output must remain deterministic before Sprint 05 payroll work begins
