# Document Management

## Purpose

The Document Management module provides centralized storage, versioning, access control, approval workflows, digital signatures, retention, and search for organizational documents.

## Business Value

- Centralizes employee and company documents
- Improves discoverability and compliance readiness
- Replaces paper-based and ad hoc file handling
- Supports secure sharing, retention, and audit requirements

## In Scope

- Document repository and folder structures
- Employee, company, compliance, and payroll documents
- Metadata, categories, tags, and version control
- Approval workflows and digital signatures
- OCR, keyword search, metadata search, and AI-assisted search
- Retention policies, legal hold, expiry tracking, analytics, and reporting

## Out Of Scope

- Full enterprise content management replacement
- External legal matter systems

## Primary Actors

- Employee
- HR
- Manager
- Compliance Reviewer
- Document Approver

## Core Workflows

- Upload, classify, review, approve, publish, archive, and dispose documents
- Version updates with history and rollback support
- Signature request, signer routing, and signed-document archival
- Expiry tracking, legal hold, and retention enforcement

## Key Rules

- Sensitive documents must be encrypted
- Access must be permission-aware and tenant-aware
- Updated documents create new versions
- Legal hold suspends retention-based deletion
- Document access events must be auditable

## Core Entities

- `documents`
- `document_versions`
- `document_categories`
- `folders`
- `document_metadata`
- `document_approvals`
- `signature_requests`

## Primary APIs

- `GET /api/v1/documents`
- `POST /api/v1/documents`
- `GET /api/v1/documents/categories`
- `POST /api/v1/documents/categories`
- `PATCH /api/v1/documents/categories/{documentCategoryId}`
- `GET /api/v1/documents/{documentId}`
- `GET /api/v1/documents/{documentId}/download`
- `GET /api/v1/self-service/workspace`
- `GET /api/v1/self-service/repository-documents/{documentId}/download`

## Implementation Notes

- Sprint 06 backend delivery has started with a tenant-scoped general document repository baseline under `apps/api`.
- The current `S06-001` slice supports controlled upload, list, detail, and secure download flows for tenant documents beyond employee-master attachments.
- Repository records now preserve scope, linked-entity metadata, visibility scope, checksum, and retention metadata so later category and retention governance can build on a stable storage baseline.
- Document access remains private-storage based, permission-aware, tenant-aware, and auditable for list, view, upload, and download actions.
- Sprint 06 now also includes `S06-002`, adding configurable document categories with repository-scope defaults, category-level role access rules, retention-day governance, filtered retention queries, and auditable category-management events.
- Sprint 06 now also includes `S06-005`, allowing policy-scope repository documents to be assigned for employee acknowledgement and securely downloaded through self-service acknowledgement flows without opening the broader repository to all employees.
- Sprint 06 now also includes `S06-006`, where linked employees can open the new `/self-service/documents` workspace in `apps/web`, review approved employee and repository files, acknowledge assigned policy items, and download only the allowed document subset while sensitive repository files remain hidden by self-service access rules.
- Sprint 06 now also includes `S06-007`, exposing `/operations/documents` in `apps/web` so HR and IT operators can manage category defaults, review repository visibility posture, and keep retention-sensitive document groups visible without opening employee self-service routes.
- Sprint 06 now also includes `S06-008`, publishing the reviewed contract in `apps/api/openapi/sprint-06-documents-assets-ess-onoffboarding.yaml` so frontend, QA, and integration work can treat the document repository, policy acknowledgement, and self-service document APIs as one version-controlled source of truth.

## Dependencies

- Employee, payroll, asset, and compliance consumers
- Workflow, notification, and audit foundation
- Secure object storage

## Related Sprints

- [Sprint 06: Documents, Assets, ESS, and On/Offboarding](../sprints/sprint-06-documents-assets-ess-onoffboarding.md)
- [Sprint 08: Reporting and Analytics](../sprints/sprint-08-reporting-analytics.md)

## Source Specs

- `docs/files/PhoenixHRMS Document Management Module Specification.txt`
- `docs/files/PhoenixHRMS Business Rules.txt`
- `docs/files/PhoenixHRMS OpenAPI Specification.txt`
