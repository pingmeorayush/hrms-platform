# Sprint 06: Documents, Assets, ESS, and On/Offboarding

## Objective

Broaden the employee experience and operational completeness around the employee master record.

## Primary Backlog IDs

- `PLAT-007`
- `ESS-001`
- `ASSET-001`
- `DOC-001`
- `EMP-004`

## Module References

- [Employee Management](../modules/employee-management.md)
- [Document Management](../modules/document-management.md)
- [Asset Management](../modules/asset-management.md)

## Scope

- General document repository and secure file handling
- Asset assignment, tracking, and return basics
- Employee self-service profile and document access
- Onboarding and offboarding task flows
- Policy acknowledgements and employee task center basics

## Delivery Items

- Signed file access patterns and retention-aware storage rules
- Asset issue and return workflows
- ESS pages for profile, documents, and assigned assets
- Onboarding and exit checklists with workflow tasks

## Dependencies

- Sprints 01 and 02 foundation
- Workflow, notifications, and audit already operational

## Acceptance Criteria

- Employees can view allowed self-service data without exposing restricted fields
- Assets can be assigned and returned with traceable history
- Onboarding and offboarding tasks can be tracked to completion
- Document access is permission-aware and auditable

## Test Focus

- File upload and download security
- ESS permission boundaries
- Asset state transitions
- Onboarding and offboarding workflow completion

## Risks and Open Questions

- File classification and retention policy should be explicit early
- ESS can sprawl quickly if approval-heavy edits are not scoped tightly
