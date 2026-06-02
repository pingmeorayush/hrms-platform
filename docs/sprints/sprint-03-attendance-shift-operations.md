# Sprint 03: Attendance and Shift Operations

## Objective

Deliver payroll-ready attendance operations with trusted time capture, shift control, and correction workflows.

## Status

Planned

Sprint 03 analysis is now normalized into an implementation-ready backlog. The recommended v1 baseline limits launch time-capture channels to web and authenticated API flows, keeps geo, IP, and device metadata capture in scope, and defers biometric sync, kiosk, QR, mobile offline capture, timesheets, and advanced attendance analytics until later delivery.

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

## Test Focus

- Check-in and check-out rules
- Shift edge cases and overnight scenarios
- Geo or device metadata capture if enabled
- Correction approvals and audit history

## Risks and Open Questions

- Overtime policy complexity can grow quickly if not constrained for v1
- Manager-versus-HR correction approval depth should stay aligned with the existing workflow MVP rather than introducing a parallel approval engine
- Attendance calculation output must remain deterministic before Sprint 05 payroll work begins
