# Sprint 01: Auth, RBAC, and Tenant Foundation

## Objective

Build the shared security and tenancy foundation required by all later business modules.

## Status

Completed and verified in the current workspace implementation.

## Primary Backlog IDs

- `PLAT-001`
- `PLAT-002`
- `PLAT-003`
- `PLAT-004`
- `PLAT-005`
- `PLAT-006`
- `PLAT-008`

## Module References

- [Platform Foundation](../modules/platform-foundation.md)

## Backlog Detail

- [Sprint 01 Delivery Backlog](../backlog/sprint-01-auth-rbac-tenant-foundation.md)

## Scope

- Authentication flows: login, logout, forgot password, reset password, MFA
- Session management, account lockout, timeout, and audit events
- Tenant resolution and tenant context loading
- Role, permission, policy, and data-scope enforcement
- Workflow engine MVP for approval-driven modules
- Notification framework for email and in-app events
- Audit logging baseline
- OpenAPI scaffolding for platform endpoints

## Delivery Items

- Auth and MFA APIs
- Permission model and seed roles
- Tenant-aware middleware and data access pattern
- Initial workflow templates for employee and leave approvals
- Notification templates and event-driven dispatch hooks
- Immutable audit events for security and admin actions

## Verification Notes

- Backend tests, frontend tests, lint, and contract validation are wired into CI.
- The UI visibility contract is implemented and covered in frontend and backend tests.
- Workflow stage `sla_hours` is available as the Sprint 1 placeholder for future escalation automation.
- Automated delegation and escalation behavior remains intentionally deferred beyond the Sprint 1 MVP.

## Dependencies

- Sprint 00 decisions complete

## Acceptance Criteria

- Authenticated users can only access their tenant
- Permission checks are enforced on protected APIs and UI actions
- Approval workflows can be triggered and tracked
- Critical auth and admin actions generate audit records

## Test Focus

- Auth flows and MFA
- Tenant isolation
- Permission enforcement
- Workflow happy paths and rejection paths
- Audit event generation

## Risks and Open Questions

- Weak tenant scoping here will create systemic leakage risk later
- Overcomplicating the first workflow engine cut may delay downstream modules
