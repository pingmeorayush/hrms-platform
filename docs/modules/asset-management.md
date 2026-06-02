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
- `GET /api/v1/assets/{id}`
- `POST /api/v1/assets/{id}/assign`
- `POST /api/v1/assets/{id}/return`

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
