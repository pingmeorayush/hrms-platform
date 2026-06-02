# DEC-005: Employee Code Policy

## Status

Accepted

## Context

The PRD and user stories disagree on whether employee code is user-supplied or auto-generated.

## Decision

Employee code behavior for v1:

- Default mode: system-generated employee code
- Optional mode: tenant-configurable manual entry for imports or legacy migration
- In all modes, employee code must be unique within the tenant
- Employee code becomes immutable after final record creation unless an elevated correction workflow is explicitly implemented later

## Rationale

- Default generation reduces onboarding friction and avoids inconsistent formatting.
- Tenant-configurable manual mode supports migration from legacy HR systems.
- Immutability supports compliance, traceability, and downstream integration stability.

## Consequences

- Employee creation UI and APIs must support both generated and tenant-approved manual patterns.
- Import jobs must validate format and uniqueness before create.
- Requirements docs should be updated to remove the current contradiction.

## Affected Docs

- [Employee Management](../modules/employee-management.md)
- [Sprint 02](../sprints/sprint-02-employee-organization-management.md)
- `docs/files/PhoenixHRMS PRD.txt`
- `docs/files/PhoenixHRMS User Stories.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
