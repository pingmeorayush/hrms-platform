# Asset Management

## Purpose

The Asset Management module governs organizational assets from request and registration through assignment, maintenance, return, and disposal.

## Business Value

- Reduces asset loss and recovery gaps
- Improves ownership and lifecycle visibility
- Supports exit clearance and compliance controls
- Enables software license tracking and utilization analysis

## In Scope

- Asset catalog and inventory registry
- Asset assignment, requests, transfers, and returns
- Maintenance and warranty tracking
- Software license management
- Asset audits, recovery, disposal, and analytics
- Exit clearance integration

## Out Of Scope

- Procurement purchasing
- Accounting ledger posting

## Primary Actors

- IT Administrator
- HR
- Manager
- Employee
- Asset Custodian

## Core Workflows

- Asset request, approval, assignment, and acknowledgment
- Asset transfer between employees, departments, or locations
- Asset return with inspection, accessories check, and verification
- Exit clearance block until pending assets are returned
- Audit and maintenance cycles for physical and digital assets

## Key Business Rules

- Asset tag must be unique
- Asset status changes are workflow-controlled
- Pending assets block exit clearance
- Software license utilization and expiry should be tracked
- Asset state changes and assignments must be auditable

## Core Entities

- `assets`
- `asset_categories`
- `asset_assignments`
- `asset_requests`
- `asset_transfers`
- `asset_returns`
- `asset_maintenance`
- `software_licenses`

## Primary APIs

- `GET /api/v1/assets`
- `POST /api/v1/assets`
- `GET /api/v1/assets/categories`
- `POST /api/v1/assets/categories`
- `PATCH /api/v1/assets/categories/{assetCategoryId}`
- `GET /api/v1/assets/{assetId}`
- `POST /api/v1/assets/{assetId}/assign`
- `POST /api/v1/assets/{assetId}/issue`
- `POST /api/v1/assets/{assetId}/return`

## Implementation Notes

- Sprint 06 backend delivery now includes the `S06-003` asset-management baseline in `apps/api`.
- The current slice supports tenant-scoped asset category setup, asset registration, current-holder visibility, assignment, issuance, and return workflows with auditable lifecycle events.
- Asset history is preserved through assignment records that capture who held the asset, when it was assigned, when it was issued, when it was returned, and the recorded handover or return condition.
- Asset tags remain unique per tenant, active employee validation is enforced at assignment time, and invalid lifecycle transitions are rejected instead of silently mutating state.
- Sprint 06 now also includes `S06-006`, exposing a linked-employee assigned-assets view in the `/self-service/assets` workspace so employees can review the current equipment they hold, return expectations, and handover context without opening broader asset-operations controls.
- Sprint 06 now also includes `S06-007`, where `/operations/assets` in `apps/web` gives HR and IT operators one routed inventory view for assignment, issuance, return, overdue follow-up, and blocked maintenance or handoff states.
- Sprint 06 now also includes `S06-008`, publishing the asset catalog, assignment, issuance, return, and self-service asset schemas in `apps/api/openapi/sprint-06-documents-assets-ess-onoffboarding.yaml` as the shared integration contract for this sprint.

## Dependencies

- Employee and organization master data
- Workflow and notification services
- Document support for attachments and proofs
- Audit logging and offboarding workflows

## Related Sprints

- [Sprint 06: Documents, Assets, ESS, and On/Offboarding](../sprints/sprint-06-documents-assets-ess-onoffboarding.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS Asset Management Module Specification.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
