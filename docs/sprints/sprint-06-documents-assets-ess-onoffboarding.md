# Sprint 06: Documents, Assets, ESS, and On/Offboarding

## Objective

Broaden the employee experience and operational completeness around the employee master record.

## Status

Completed

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

## Backlog Detail

- [Sprint 06 Delivery Backlog](../backlog/sprint-06-documents-assets-ess-onoffboarding.md)

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

## Current Delivery Note

Sprint 06 delivery now includes `S06-001`, `S06-002`, `S06-003`, `S06-004`, `S06-005`, `S06-006`, `S06-007`, and `S06-008`. In `apps/api`, the sprint now has a tenant-scoped general document repository baseline with controlled upload, list, detail, and secure download flows, configurable document categories with category-level access rules and retention governance defaults, the asset-management baseline for category setup, asset registration, assignment, issuance, return, current-holder visibility, and auditable lifecycle history, employee lifecycle task templates and workflow-aware onboarding-offboarding task extensions with approval hooks for exit steps that require authorization, the first self-service policy acknowledgement and employee task-center baseline aggregating policy, lifecycle-task, and active asset items, plus a linked-employee self-service API for profile, allowed documents, and assigned assets. In `apps/web`, Sprint 06 also includes a routed `/self-service` module with profile, documents, and assigned-assets sections, demo/live state wiring, pending versus acknowledged policy-document handling, secure download entry points, and empty-state coverage for sessions without a linked employee profile, plus a new `/operations` module where HR and IT users can manage document categories, track asset handoffs, and monitor onboarding-offboarding progress from one permission-aware workspace. The Sprint 06 source of truth is now published in `apps/api/openapi/sprint-06-documents-assets-ess-onoffboarding.yaml`.
